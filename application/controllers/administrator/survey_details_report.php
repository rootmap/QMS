<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_details_report extends MY_Controller
{
    var $current_page = "survey_details_report";
    var $survey_list = array();
    var $question_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_report_model');
        $this->load->model('survey_model');
        $this->load->model('survey_question_model');
        $this->load->library('excel');

        $this->load->model('global/insert_global_model');
        $this->load->model('global/Select_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');
     
        $survey = $this->survey_model->get_surveys();

        $this->survey_list[] = 'Select a Survey';
        if ($survey) {
            for ($i=0; $i<count($survey); $i++) {
                $this->survey_list[$survey[$i]->id] = $survey[$i]->survey_title;
            }
        }
        
        $question = $this->survey_question_model->get_questions();

        $this->question_list[] = 'Select a Question';
        if ($question) {
            for ($i=0; $i<count($question); $i++) {
                $this->question_list[$question[$i]->id] = $question[$i]->ques_text;
            }
        }

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('landing');
            }
        }
    }

    /**
     * Display paginated list of categories
     * @return void
     */
    public function index()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Survey Report View'));
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        // set page specific variables
        $page_info['title'] = 'Survey Report'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_details_report_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_survey')) {
            $this->session->keep_flashdata('filter_survey');
            $filter_survey = $this->session->flashdata('filter_survey');
            $this->form_data->filter_survey = $filter_survey;
            $filter['filter_survey']['field'] = 'survey_id';
            $filter['filter_survey']['value'] = $filter_survey;
        }
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_survey = $filter_question;
            $filter['filter_question']['field'] = 'question_id';
            $filter['filter_question']['value'] = $filter_question;
        }
        $page_info['filter'] = $filter;

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->survey_report_model->get_survey_details($per_page, $page_offset, $filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_details_report";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            $this->session->set_userdata('records', $records);
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Title'),
                '1' => array('data'=> 'Questions'),
                '2' => array('data'=> 'Question Type', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'User Answer'),
                '4' => array('data'=> 'User')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->survey_title),
                    '1' => array('data'=> html_entity_decode($records[$i]->ques_text)),
                    '2' => array('data'=> $records[$i]->ques_type, 'class' => 'center', 'width' => '100', 'width' => '120'),
                    '3' => array('data'=> $records[$i]->answer),
                    '4' => array('data'=> $records[$i]->user_first_name.' ('.$records[$i]->user_login.')')
                ); 
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function filter()
    {

        $filter_survey = $this->input->post('filter_survey');
        $filter_question = $this->input->post('filter_question');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_survey != '') {
                $this->session->set_flashdata('filter_survey', $filter_survey);
            }
            if ($filter_question != '') {
                $this->session->set_flashdata('filter_question', $filter_question);
            }
        } else {
            $this->session->unset_userdata('filter_survey');
            $this->session->unset_userdata('filter_question');
        }

        redirect('administrator/survey_details_report');
    }
    
    public function export_data()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Survey Report'));
        if($this->session->userdata('records')){



            $records = $this->session->userdata('records');
            //print_r_pre($records);
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey ID');
                $this->excel->getActiveSheet()->setCellValue('C1', 'Survey Title');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Questions');
                $this->excel->getActiveSheet()->setCellValue('E1', 'Question Type');
                $this->excel->getActiveSheet()->setCellValue('F1', 'User Answer');
                $this->excel->getActiveSheet()->setCellValue('G1', 'User Login');
                $this->excel->getActiveSheet()->setCellValue('H1', 'User Name');
                $this->excel->getActiveSheet()->setCellValue('I1', 'Category');
                $this->excel->getActiveSheet()->setCellValue('J1', 'Sub Category');
                $this->excel->getActiveSheet()->setCellValue('K1', 'Sub 2 Category');
                $this->excel->getActiveSheet()->setCellValue('L1', 'Sub 3 Category');
                $this->excel->getActiveSheet()->setCellValue('M1', 'Sub 4 Category');
                $this->excel->getActiveSheet()->setCellValue('N1', 'Questions Weight');
                $this->excel->getActiveSheet()->setCellValue('O1', 'Option weight');
                $this->excel->getActiveSheet()->setCellValue('P1', 'Date of Expire');
                $this->excel->getActiveSheet()->setCellValue('Q1', 'Created By');
                $this->excel->getActiveSheet()->setCellValue('R1', 'Creation Time');
                $this->excel->getActiveSheet()->setCellValue('S1', 'No of Questions');
                $this->excel->getActiveSheet()->setCellValue('T1', 'Survey Status');
                $this->excel->getActiveSheet()->setCellValue('U1', 'Total Assign Attendies');
                $this->excel->getActiveSheet()->setCellValue('V1', 'Survey Attendies');
                $this->excel->getActiveSheet()->setCellValue('W1', 'Not Attended (Number)');

                $this->excel->getActiveSheet()->getStyle('A1:W1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);

                // fill data
                $serial = 0;
                $row = 1;
                for ($i=0; $i<count($records); $i++) {                            
                    $serial++;
                    $row++;

                    //print_r_pre($records[$i]);

                    $sqlGetSurveyInfos=$this->Select_global_model->FlyQuery(array("SELECT 
                    (SELECT a.cat_name FROM exm_survey_categories AS a WHERE a.id=d.category_id) AS category,
                    (SELECT a.cat_name FROM exm_survey_sub_categories AS a WHERE a.id=d.sub_category_id) AS sub_category,
                    (SELECT a.cat_name FROM exm_survey_sub_two_categories AS a WHERE a.id=d.sub_two_category_id) AS sub_two_category,
                    (SELECT a.cat_name FROM exm_survey_sub_three_categories AS a WHERE a.id=d.sub_three_category_id) AS sub_three_category,
                    (SELECT a.cat_name FROM exm_survey_sub_four_categories AS a WHERE a.id=d.sub_four_category_id) AS sub_four_category,
                    d.survey_weight AS question_weight,
                    d.ques_expiry_date AS expire_date,
                    dbo.get_user_login_name_by_id(d.created_by) AS creator,
                    d.created_time AS created_at,
                    (SELECT COUNT(a.question_id) FROM exm_surveys_questions a WHERE a.survey_id ='".$records[$i]->survey_id."') AS total_question,
                    (SELECT a.survey_status FROM exm_surveys a WHERE a.id ='".$records[$i]->survey_id."') AS survey_status,
                    (SELECT COUNT(id) FROM exm_surveys_users a WHERE a.survey_id ='".$records[$i]->survey_id."') AS total_assigne_survey,
                    (SELECT COUNT(id) FROM exm_surveys_users a WHERE a.survey_id ='".$records[$i]->survey_id."' AND a.status='completed') AS total_attendies,
                    (SELECT COUNT(id) FROM exm_surveys_users a WHERE a.survey_id ='".$records[$i]->survey_id."' AND a.status!='completed') AS total_non_attendies
                    FROM exm_survey_questions AS d WHERE id='".$records[$i]->question_id."'"));

                    //print_r_pre($sqlGetSurveyInfos);

                    if(count($sqlGetSurveyInfos)>0)
                    {
                        $survey_option_weight=$sqlGetSurveyInfos[0]['question_weight'];
                    }
                    else
                    {
                        $survey_option_weight=$records[$i]->answer;
                    }

                    if($records[$i]->ques_type=="option_based")
                    {
                        $choicesAr=unserialize($records[$i]->ques_choices);
                        if(count($choicesAr))
                        {
                            foreach($choicesAr as $rr)
                            {
                                if($rr['text']==$records[$i]->answer)
                                {
                                    if(isset($rr['marks']))
                                    {
                                        $survey_option_weight=$rr['marks'];
                                    }
                                }
                            }
                        }
                    }

                    
                    

                    
                    
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_id);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->survey_title);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, strip_tags(htmlspecialchars_decode($records[$i]->ques_text)));
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $records[$i]->ques_type);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $records[$i]->answer);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $records[$i]->user_login);                     
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $records[$i]->user_first_name);      


                    if(count($sqlGetSurveyInfos)>0)
                    {
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $sqlGetSurveyInfos[0]['category']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $sqlGetSurveyInfos[0]['sub_category']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $sqlGetSurveyInfos[0]['sub_two_category']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $sqlGetSurveyInfos[0]['sub_three_category']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $sqlGetSurveyInfos[0]['sub_four_category']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $sqlGetSurveyInfos[0]['question_weight']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $survey_option_weight);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $sqlGetSurveyInfos[0]['expire_date']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $sqlGetSurveyInfos[0]['creator']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $sqlGetSurveyInfos[0]['created_at']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(18, $row, $sqlGetSurveyInfos[0]['total_question']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(19, $row, $sqlGetSurveyInfos[0]['survey_status']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(20, $row, $sqlGetSurveyInfos[0]['total_assigne_survey']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(21, $row, $sqlGetSurveyInfos[0]['total_attendies']);                     
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(22, $row, $sqlGetSurveyInfos[0]['total_non_attendies']); 
                    }
                    else
                    {
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $survey_option_weight);     
                    }                    
                }

                $filename = 'Survey Details Report '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_details_report');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/survey_details_report');
        }
    }
    
    // set empty default form field values
    private function _set_fields()
    {
	    @$this->form_data->filter_survey = '';
        @$this->form_data->filter_question = '';
    }
}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */