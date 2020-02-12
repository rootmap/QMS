<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_report extends MY_Controller
{
    var $current_page = "survey_report";
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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Survey Report List View'));
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        // set page specific variables
        $page_info['title'] = 'Survey Report'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_report_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();
        $no_of_answer = 0;
        $total_assigned = 0;

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

        $record_result = $this->survey_report_model->get_survey($per_page, $page_offset, $filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        //print_r(expression);

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_report";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            $this->session->set_userdata('records', $records);
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Title'),
                '1' => array('data'=> 'Total Question', 'class' => 'center'),
                '2' => array('data'=> 'Total Question Weight', 'class' => 'center'),
                '3' => array('data'=> 'Total Assign Attendies', 'class' => 'center'),
                '4' => array('data'=> 'Survey Attendies', 'class' => 'center'),
                '5' => array('data'=> 'User Report', 'class' => 'center'),
                '6' => array('data'=> 'Action', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);
            $no_of_answer = 0;
            $total_assigned = 0;
            $percentage = 0;
            for ($i = 0; $i<count($records); $i++) {

                        $stat_str = '<a href="'. base_url('administrator/survey_report/summary/'. $records[$i]->id) .'" title="View Survey Summary"><span class="label label-success">Survey Summary</span></a>';
                        $stat_str_download = '<a href="'. base_url('administrator/survey_report/download_data/'. $records[$i]->id) .'" title="Download Survey Report" class="btn btn-info"><span><i class="icon-download-alt"></i> Download</span></a>';

                        $tbl_row = array(
                            '0' => array('data'=> $records[$i]->survey_title),
                            '1' => array('data'=> $records[$i]->total_question, 'class' => 'center', 'width' => '100'),
                            '2' => array('data'=> $records[$i]->total_weight, 'class' => 'center', 'width' => '100'),
                            '3' => array('data'=> $records[$i]->total_assign_user, 'class' => 'center', 'width' => '100'),
                            '4' => array('data'=> $records[$i]->survey_attendies, 'class' => 'center', 'width' => '100'),
                            '5' => array('data'=> $stat_str, 'class' => 'center', 'width' => '100'),
                            '6' => array('data'=> $stat_str_download, 'class' => 'center', 'width' => '130')
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

    public function index_bk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Survey Report List View'));
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        // set page specific variables
        $page_info['title'] = 'Survey Report'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_report_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();
        $no_of_answer = 0;
        $total_assigned = 0;

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

        $record_result = $this->survey_report_model->get_survey($per_page, $page_offset, $filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        //print_r(expression);

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_report";
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
                '3' => array('data'=> 'Question Choice', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'No. of Answer', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Percentage', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);
            $no_of_answer = 0;
            $total_assigned = 0;
            $percentage = 0;
            for ($i = 0; $i<count($records); $i++) {
                if($records[$i]->ques_type == 'option_based'){
                    $question_choices = maybe_unserialize($records[$i]->ques_choices);
                    for($j=0; $j<count($question_choices); $j++){
                        $no_of_answer = (int)$this->survey_report_model->get_option_answer_count($records[$i]->survey_id, $records[$i]->question_id, $question_choices[$j]['text']);
                        $total_assigned = (int)$this->survey_report_model->get_total_assigned($records[$i]->survey_id, $records[$i]->question_id);
                        //print_r_pre($no_of_answer);
                         
                        if($no_of_answer!=0){
                            $percentage = ($no_of_answer / $total_assigned) * 100;
                        }
                        //$percentage = ($no_of_answer / $total_assigned) * 100;
                        $percentage = sprintf ("%.2f", $percentage);
                        $tbl_row = array(
                            '0' => array('data'=> $records[$i]->survey_title),
                            '1' => array('data'=> $records[$i]->ques_text),
                            '2' => array('data'=> $records[$i]->ques_type, 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '3' => array('data'=> $question_choices[$j]['text'], 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '4' => array('data'=> $no_of_answer, 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '5' => array('data'=> $percentage.' %', 'class' => 'center', 'width' => '100', 'width' => '120')
                        ); 
                        $this->table->add_row($tbl_row);
                    }
                }else{
                    $no_of_answer = (int)$this->survey_report_model->get_descriptive_answer_count($records[$i]->survey_id, $records[$i]->question_id);
                    if($no_of_answer){
                        $total_assigned = (int)$this->survey_report_model->get_total_assigned($records[$i]->survey_id, $records[$i]->question_id);
                        $percentage = ($no_of_answer / $total_assigned) * 100;
                        $percentage = sprintf ("%.2f", $percentage);
                        $tbl_row = array(
                            '0' => array('data'=> $records[$i]->survey_title),
                            '1' => array('data'=> $records[$i]->ques_text),
                            '2' => array('data'=> $records[$i]->ques_type, 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '3' => array('data'=> '', 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '4' => array('data'=> $no_of_answer, 'class' => 'center', 'width' => '100', 'width' => '120'),
                            '5' => array('data'=> $percentage.' %', 'class' => 'center', 'width' => '100', 'width' => '120')
                        );
                        $this->table->add_row($tbl_row);
                    }
                    
                }
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


    public function summary($survey_id='')
    {

        //echo $survey_id; die();

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Survey Summary Report View'));
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        // set page specific variables
        $page_info['title'] = 'Survey Report'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_summary_report_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['survey_id'] = $survey_id;
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

        if($survey_id>0)
        {
            $filter['survey_id']['field'] = 'survey_id_filter';
            $filter['survey_id']['value'] = $survey_id;
        }

        $page_info['filter'] = $filter;

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->survey_report_model->get_survey_summary($per_page, $page_offset, $filter);

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
                '4' => array('data'=> 'Weight'),
                '5' => array('data'=> 'User')
            );

            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );

            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) { 
                //print_r_pre($records[$i]); die();
                $quesValue=unserialize($records[$i]->ques_choices); 
                //$quesValue=unserialize($records[$i]->ques_choices);
                //print_r_pre($quesValue); die();
                //die(); 
                //;

                $rowWW=0;
                if($quesValue)
                {
                    if(count($quesValue)>0)
                    {
                        foreach ($quesValue as $opdes) 
                        {
                            if(isset($opdes['text']))
                            {
                                if($records[$i]->answer==$opdes['text'])
                                {
                                    if(isset($opdes['marks']))
                                    {
                                        $rowWW=$opdes['marks'];
                                    }
                                }
                            }
                            
                        }
                    }
                }
                

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->survey_title),
                    '1' => array('data'=> html_entity_decode($records[$i]->ques_text)),
                    '2' => array('data'=> $records[$i]->ques_type, 'class' => 'center', 'width' => '100', 'width' => '120'),
                    '3' => array('data'=> $records[$i]->answer),
                    '4' => array('data'=> $rowWW),
                    '5' => array('data'=> $records[$i]->user_first_name.' ('.$records[$i]->user_login.')')
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

        redirect('administrator/survey_report');
    }
    
    public function download_data()
    {

        $survey_id=$this->uri->segment(4); 
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Download Survey Report ('.$survey_id.')'));


        $survey=$this->survey_model->get_survey($survey_id);
        $sql_tauts=$this->survey_model->get_survey_user_report($survey_id);
        $total_assign_user_to_survey=$sql_tauts->total;

        $sql_tsuas=$this->survey_model->get_survey_user_attend_in_survey($survey_id);
        $total_user_attend_in_survey=$sql_tsuas->total;
        







        //print_r_pre($survey);

                $this->excel->createSheet(0);
                $this->excel->setActiveSheetIndex(0);

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Option Based');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey No.');
                $this->excel->getActiveSheet()->setCellValue('D1', $survey->id);
                $this->excel->getActiveSheet()->setCellValue('B2', 'Survey Name');
                $this->excel->getActiveSheet()->setCellValue('D2', $survey->survey_title);
                $this->excel->getActiveSheet()->setCellValue('B3', 'Total no. of assigned participants');
                $this->excel->getActiveSheet()->setCellValue('B4', 'Total participants Attended');
                $this->excel->getActiveSheet()->setCellValue('B5', 'Survey Completion Percentage');
                $this->excel->getActiveSheet()->setCellValue('D3', $total_assign_user_to_survey);
                $this->excel->getActiveSheet()->setCellValue('D4', $total_user_attend_in_survey);
                $this->excel->getActiveSheet()->setCellValue('D5', '=D4/D3');
                $this->excel->getActiveSheet()->setCellValue('B6', 'Start Date');
                $this->excel->getActiveSheet()->setCellValue('D6', $survey->survey_added);
                $this->excel->getActiveSheet()->setCellValue('B7', 'End Date');
                $this->excel->getActiveSheet()->setCellValue('D7', $survey->survey_expired);
                $this->excel->setActiveSheetIndex(0)->mergeCells('B1:C1');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B2:C2');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B3:C3');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B4:C4');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B5:C5');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B5:C5');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B6:C6');
                $this->excel->setActiveSheetIndex(0)->mergeCells('B7:C7');

                $this->excel->getActiveSheet()->getStyle('D5')->getNumberFormat()->setFormatCode('#,##0.00');

                $this->excel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A3:D3')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A4:D4')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A5:D5')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A6:D6')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A7:D7')->getFont()->setBold(true);

                for($i=1; $i<=7; $i++)
                {
                    $this->excel->getActiveSheet()
                            ->getStyle('B'.$i.':D'.$i)
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);


                    $this->excel->getActiveSheet()
                            ->getStyle('C'.$i)
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }



                //second table start
                $this->excel->getActiveSheet()->setCellValue('A10', 'Sl No.');
                $this->excel->getActiveSheet()->setCellValue('B10', 'Category');
                $this->excel->getActiveSheet()->setCellValue('C10', 'Sub Category');
                $this->excel->getActiveSheet()->setCellValue('D10', 'Sub 2 Category');
                $this->excel->getActiveSheet()->setCellValue('E10', 'Sub 3 Category');
                $this->excel->getActiveSheet()->setCellValue('F10', 'Sub 4 Category');
                $this->excel->getActiveSheet()->setCellValue('G10', 'Description');
                $this->excel->getActiveSheet()->setCellValue('H10', 'Count');
                $this->excel->getActiveSheet()->setCellValue('I10', 'Response % (100)');
                $this->excel->getActiveSheet()->setCellValue('J10', 'Question Weight (%)');
                $this->excel->getActiveSheet()->setCellValue('K10', 'Option Weight');

                

                $this->excel->getActiveSheet()->getStyle('A10:K10')->getFont()->setBold(true);
                
                $this->excel->getActiveSheet()
                        ->getStyle('A10:K10')
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('2E75B6');


                foreach (range('A', 'K') as $char) {
                    //echo $char . "\n";

                    $this->excel->getActiveSheet()
                            ->getStyle($char.'10')
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }


                $serial = 0;
                $col = 10;
                $sql_allQuestionChoice=$this->survey_model->get_survey_all_choice_questions($survey_id);
                if(count($sql_allQuestionChoice)>0)
                {
                    foreach($sql_allQuestionChoice as $row)
                    {
                        $serial++;
                        $col++;
                       // echo "<pre>";
                        //print_r(unserialize($row->ques_choices)).'<br>';

                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $col, $serial);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $col, $row->category);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $col, $row->sub_category);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $col, $row->sub_two_category);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $col, $row->sub_three_category);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $col, $row->sub_four_category);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $col, $row->ques_text);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $col, '');
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $col, '');
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $col, $row->survey_weight);
                         $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $col, '');

                         $question_id=$row->question_id;

                         $unSeriChoice=unserialize($row->ques_choices);
                         if(count($unSeriChoice)>0)
                         {

                            $totalinCountChoice=count($unSeriChoice);

                            $totalHStartPoint='$H$'.($col+1).':$H$'.(($col+1)+$totalinCountChoice);

                            //echo $totalHStartPoint; die();

                            foreach($unSeriChoice as $cmk)
                            {

                                $answer=$cmk['text'];
                                $sqlGetCountAttendiesAns=$this->survey_model->get_survey_choice_user_count($survey_id,$question_id,$answer);
                                $countAttendiesAns=$sqlGetCountAttendiesAns->total;

                                $col++;
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $col, '');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $col, $answer);
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $col, $countAttendiesAns);
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $col, '=H'.$col.'/SUM('.$totalHStartPoint.')*100');
                                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $col, '');
                                if(isset($cmk['value']))
                                {
                                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $col, $cmk['value']);
                                }
                                else
                                {
                                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $col, '');
                                }
                                
                                
                            }
                         }

                    }
                }


                //sheet descriptive start 
                //$this->excel->createSheet(1);
                $this->excel->setActiveSheetIndex(1);

                $this->excel->getActiveSheet()->setTitle('Descriptive');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey No.');
                $this->excel->getActiveSheet()->setCellValue('D1', $survey->id);
                $this->excel->getActiveSheet()->setCellValue('B2', 'Survey Name');
                $this->excel->getActiveSheet()->setCellValue('D2', $survey->survey_title);
                $this->excel->getActiveSheet()->setCellValue('B3', 'Total no. of assigned participants');
                $this->excel->getActiveSheet()->setCellValue('B4', 'Total participants Attended');
                $this->excel->getActiveSheet()->setCellValue('B5', 'Survey Completion Percentage');
                $this->excel->getActiveSheet()->setCellValue('D3', $total_assign_user_to_survey);
                $this->excel->getActiveSheet()->setCellValue('D4', $total_user_attend_in_survey);
                $this->excel->getActiveSheet()->setCellValue('D5', '=D4/D3');
                $this->excel->getActiveSheet()->setCellValue('B6', 'Start Date');
                $this->excel->getActiveSheet()->setCellValue('D6', $survey->survey_added);
                $this->excel->getActiveSheet()->setCellValue('B7', 'End Date');
                $this->excel->getActiveSheet()->setCellValue('D7', $survey->survey_expired);
                $this->excel->setActiveSheetIndex(1)->mergeCells('B1:C1');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B2:C2');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B3:C3');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B4:C4');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B5:C5');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B5:C5');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B6:C6');
                $this->excel->setActiveSheetIndex(1)->mergeCells('B7:C7');

                $this->excel->getActiveSheet()->getStyle('D5')->getNumberFormat()->setFormatCode('#,##0.00');

                $this->excel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A3:D3')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A4:D4')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A5:D5')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A6:D6')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A7:D7')->getFont()->setBold(true);

                for($i=1; $i<=7; $i++)
                {
                    $this->excel->getActiveSheet()
                            ->getStyle('B'.$i.':D'.$i)
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('B'.$i.':D'.$i)
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);


                    $this->excel->getActiveSheet()
                            ->getStyle('C'.$i)
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle('C'.$i)
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }



                //second table start
                $this->excel->getActiveSheet()->setCellValue('A10', 'Sl No.');
                $this->excel->getActiveSheet()->setCellValue('B10', 'Category');
                $this->excel->getActiveSheet()->setCellValue('C10', 'Sub Category');
                $this->excel->getActiveSheet()->setCellValue('D10', 'Sub 2 Category');
                $this->excel->getActiveSheet()->setCellValue('E10', 'Sub 3 Category');
                $this->excel->getActiveSheet()->setCellValue('F10', 'Sub 4 Category');
                $this->excel->getActiveSheet()->setCellValue('G10', 'Description');
                $this->excel->getActiveSheet()->setCellValue('H10', 'Responded by');
                $this->excel->getActiveSheet()->setCellValue('I10', 'Department');
                $this->excel->getActiveSheet()->setCellValue('J10', 'Division');

                

                $this->excel->getActiveSheet()->getStyle('A10:J10')->getFont()->setBold(true);
                
                $this->excel->getActiveSheet()
                        ->getStyle('A10:J10')
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('2E75B6');


                foreach (range('A', 'J') as $char) {
                    //echo $char . "\n";

                    $this->excel->getActiveSheet()
                            ->getStyle($char.'10')
                            ->getBorders()
                            ->getTop()
                            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getLeft()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getRight()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $this->excel->getActiveSheet()
                                ->getStyle($char.'10')
                                ->getBorders()
                                ->getBottom()
                                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                }


                $serial = 0;
                $col = 10;
                $sql_allQuestionDescriptive=$this->survey_model->get_survey_all_descriptive_questions($survey_id);

   
                if(is_array($sql_allQuestionDescriptive))
                {
                    if(count($sql_allQuestionDescriptive)>0)
                    {
                        foreach($sql_allQuestionDescriptive as $row)
                        {
                            //print_r_pre($row);
                            //echo 1; die();
                            $serial++;
                            $col++;
                           // echo "<pre>";
                            //print_r(unserialize($row->ques_choices)).'<br>';

                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $col, $serial);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $col, $row->category);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $col, $row->sub_category);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $col, $row->sub_two_category);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $col, $row->sub_three_category);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $col, $row->sub_four_category);
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $col, strip_tags(html_entity_decode($row->ques_text)));
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $col, '');
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $col, '');
                             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $col, '');

                             $question_id=$row->question_id;

                             $sqlGetCountAttendiesAns=$this->survey_model->get_survey_all_descriptive_answers($survey_id,$question_id);

                             if(is_array($sqlGetCountAttendiesAns))
                             {
                                    if(count($sqlGetCountAttendiesAns)>0)
                                    {
                                        foreach($sqlGetCountAttendiesAns as $rk)
                                        {
                                            //print_r_pre($rk);
                                            $col++;
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $col, '');
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $col, $rk->answer);
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $col, $rk->user_full_name);
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $col, $rk->department);
                                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $col, $rk->user_type);
                                        }
                                    }
                             }

                            // print_r_pre($sqlGetCountAttendiesAns);

                        }
                    }


                }

                //print_r_pre($sql_allQuestionChoice);



                // fill data
                /*$serial = 0;
                $row = 1;
                $percentage = 0;
                $no_of_answer = 0;
                $total_assigned = 0;
                if($records){
                    for ($i=0; $i<count($records); $i++) {
                        
                        
                        $serial++;
                        $row++;
                        
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->total_question);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->total_weight);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $records[$i]->total_assign_user);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $records[$i]->survey_attendies);
       
                    }
                }
                */

                $filename = 'Survey Report Download '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            /*} else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_report');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/survey_report');
        }*/
    }

    public function export_data()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Survey Report'));
        if($this->session->userdata('records')){
            $records = $this->session->userdata('records');
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey Title');
                $this->excel->getActiveSheet()->setCellValue('C1', 'Total Questions');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Question Weight');
                $this->excel->getActiveSheet()->setCellValue('E1', 'Assign Attendies');
                $this->excel->getActiveSheet()->setCellValue('F1', 'Survey Attendies');

                $this->excel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

                // fill data
                $serial = 0;
                $row = 1;
                $percentage = 0;
                $no_of_answer = 0;
                $total_assigned = 0;
                if($records){
                    for ($i=0; $i<count($records); $i++) {
                        
                        
                        $serial++;
                        $row++;
                        
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->total_question);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->total_weight);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $records[$i]->total_assign_user);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $records[$i]->survey_attendies);
       
                    }
                }
                

                $filename = 'Survey Report '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_report');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/survey_report');
        }
    }

    public function export_summary_data()
    {
        $survey_id = $this->input->post('survey_id'); 
        //echo $survey_id;
        //die();

        $filter = array();
        if($survey_id>0)
        {
            $filter['survey_id']['field'] = 'survey_id_filter';
            $filter['survey_id']['value'] = $survey_id;
        }

        $page_info['filter'] = $filter;

        $record_result = $this->survey_report_model->get_survey_summary(0,0,$filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        //dd($records);

        //$update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            //'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Survey Detail Report'));
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey Title');
                $this->excel->getActiveSheet()->setCellValue('C1', 'Questions');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Question Type');
                $this->excel->getActiveSheet()->setCellValue('E1', 'User Answer');
                $this->excel->getActiveSheet()->setCellValue('F1', 'Weight');
                $this->excel->getActiveSheet()->setCellValue('G1', 'User');

                $this->excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

                // fill data
                $serial = 0;
                $row = 1;
                $percentage = 0;
                $no_of_answer = 0;
                $total_assigned = 0;
                if($records){
                    for ($i=0; $i<count($records); $i++) {
                        
                        
                        $serial++;
                        $row++;

                        $quesValue=unserialize($records[$i]->ques_choices); 
                        $rowWW=0;
                        if($quesValue)
                        {
                            if(count($quesValue)>0)
                            {
                                foreach ($quesValue as $opdes) 
                                {
                                    if(isset($opdes['text']))
                                    {
                                        if($records[$i]->answer==$opdes['text'])
                                        {
                                            if(isset($opdes['marks']))
                                            {
                                                $rowWW=$opdes['marks'];
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        }
                        
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, strip_tags(htmlspecialchars_decode($records[$i]->ques_text)));
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->ques_type);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $records[$i]->answer);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $rowWW);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $records[$i]->user_first_name);
       
                    }
                }
                
                
                $filename = 'Survey Detail Report '. date('Y-m-d') .'.xls';
                
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');
                
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');
                
            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_report/summary/'.$survey_id);
            }
        
    }

    public function export_data_bk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Survey Report'));
        if($this->session->userdata('records')){
            $records = $this->session->userdata('records');
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey Title');
                $this->excel->getActiveSheet()->setCellValue('C1', 'Questions');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Question Type');
                $this->excel->getActiveSheet()->setCellValue('E1', 'Question Choices');
                $this->excel->getActiveSheet()->setCellValue('F1', 'No. of Answer');
                $this->excel->getActiveSheet()->setCellValue('G1', 'Percentage');

                $this->excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

                // fill data
                $serial = 0;
                $row = 1;
                $percentage = 0;
                $no_of_answer = 0;
                $total_assigned = 0;
                if($records){
                    for ($i=0; $i<count($records); $i++) {
                    if($records[$i]->ques_type == 'option_based'){
                        $question_choices = maybe_unserialize($records[$i]->ques_choices);
                        for($j=0; $j<count($question_choices); $j++){
                            
                            $serial++;
                            $row++;
                            $no_of_answer = (int)@$this->survey_report_model->get_option_answer_count($records[$i]->survey_id, $records[$i]->question_id, $question_choices[$j]['text']);
                            $total_assigned = (int)$this->survey_report_model->get_total_assigned($records[$i]->survey_id, $records[$i]->question_id);
                            if($no_of_answer!=0){
                                $percentage = ($no_of_answer / $total_assigned) * 100;
                            }
                            
                            $percentage = sprintf ("%.2f", $percentage);
                                        
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->ques_text);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->ques_type);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $question_choices[$j]['text']);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $no_of_answer);
                            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $percentage.' %');
                        }
                    }else{             
                        
                        $serial++;
                        $row++;
                        $no_of_answer = (int)$this->survey_report_model->get_descriptive_answer_count($records[$i]->survey_id, $records[$i]->question_id);
                        $total_assigned = (int)$this->survey_report_model->get_total_assigned($records[$i]->survey_id, $records[$i]->question_id);
                        $percentage = ($no_of_answer / $total_assigned) * 100;
                        $percentage = sprintf ("%.2f", $percentage);
                        
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->ques_text);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->ques_type);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $question_choices[$j]['text']);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $no_of_answer);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $percentage.' %');
       
                    }
                }
                }
                

                $filename = 'Survey Report '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_report');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/survey_report');
        }
    }
    
    // set empty default form field values
    private function _set_fields()
    {
	@$this->form_data->filter_survey = '';
        $this->form_data->filter_question = '';
    }
}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */