<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey extends MY_Controller
{
    var $current_page = "survey";
    var $question_list = array();
    var $survey_status_list_filter = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();

        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_model');
        $this->load->model('category_model');
        $this->load->model('survey_question_model');
        $this->load->model('global/select_global_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $open_questions = $this->survey_question_model->get_available_questions();

        $this->question_list[] = 'Select Questions';
        if ($open_questions) {
            for ($i=0; $i<count($open_questions); $i++) {
                $this->question_list[$open_questions[$i]->id] = $open_questions[$i]->ques_text;
            }
        }

        $this->survey_status_list_filter[] = 'Any status';
        $this->survey_status_list_filter['open'] = 'Open';
        $this->survey_status_list_filter['closed'] = 'Closed';

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
     * Display paginated list of exams
     * @return void
     */
    public function index()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Surveys View'));
        // set page specific variables
        $page_info['title'] = 'Manage Surveys'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_survey_title')) {
            $this->session->keep_flashdata('filter_survey_title');
            $filter_survey_title = $this->session->flashdata('filter_survey_title');
            $this->form_data->filter_survey_title = $filter_survey_title;
            $filter['filter_survey_title']['field'] = 'survey_title';
            $filter['filter_survey_title']['value'] = $filter_survey_title;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'survey_status';
            $filter['filter_status']['value'] = $filter_status;
        }

        if ($this->session->flashdata('filter_approval_status')) {
            $this->session->keep_flashdata('filter_approval_status');
            $filter_approval_status = $this->session->flashdata('filter_approval_status');
            $this->form_data->filter_approval_status = $filter_approval_status;
            $filter['filter_status']['field'] = 'survey_approve';
            $filter['filter_status']['value'] = $filter_approval_status;
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->survey_model->get_paged_surveys($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Survey ID'),
                '1' => array('data'=> 'Survey Title'),
                '2' => array('data'=> 'Description', 'class' => 'center', 'width' => '120'),
                '3' => array('data'=> 'No of Questions', 'class' => 'center', 'width' => '80'),
                '4' => array('data'=> 'Status', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Created By', 'class' => 'center', 'width' => '100'),
                '6' => array('data'=> 'Creation Time', 'class' => 'center', 'width' => '100'),
                '7' => array('data'=> 'Approval Status', 'class' => 'center', 'width' => '100'),
                '8' => array('data'=> 'Action', 'class' => 'center', 'width' => '120')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = $this->survey_model->get_number_of_questions($records[$i]->id);
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $status_str = '';
                if ($records[$i]->survey_status == 'open') {
                    $status_str = '<span class="label label-success">OPEN</span>';
                } elseif ($records[$i]->survey_status == 'closed') {
                    $status_str = '<span class="label label-important">CLOSED</span>';
                }

  /*              $userInfoData=$this->Select_global_model->FlyQuery(array("SELECT user_login FROM exm_users WHERE id=".$records[$i]->created_by));

                $created_by=*/

                $action_str = '';
                if(!isSystemAuditor())
                {
                    $action_str .= anchor('administrator/survey/printpreview/'. $records[$i]->id, '<i class="icon-eye-open"></i>', 'title="Edit" class="btn btn-info"');
                    $action_str .= " ".anchor('administrator/survey/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit" class="btn btn-primary"');
                }

                $status='';

                if(!isSystemAuditor())
                {
                    if($records[$i]->survey_approve==1 || $records[$i]->survey_approve==0){
                        $status = '<a href="'. base_url('administrator/survey/approval/2:'. $records[$i]->id) .'"><span class="label label-success">Approve</span></a>&nbsp;&nbsp;<a href="'. base_url('administrator/survey/approval/3:'. $records[$i]->id) .'"><span class="label label-important">Reject</span></a>';
                    }
                    elseif($records[$i]->survey_approve==2){
                        $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->survey_approve==3){
                        $status = '<span class="label label-important">Reject</span>';
                    }
                }
                else
                {
                    if($records[$i]->survey_approve==1 || $records[$i]->survey_approve==0){
                        $status = '<span class="label label-default">Pending</span>';
                    }
                    elseif($records[$i]->survey_approve==2){
                        $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->survey_approve==3){
                        $status = '<span class="label label-important">Reject</span>';
                    }
                }
                
                
                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $records[$i]->survey_title),
                    '2' => array('data'=> $records[$i]->survey_description),
                    '3' => array('data'=> $noof_questions_str, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $status_str, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $records[$i]->login_pin, 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> $records[$i]->survey_added, 'class' => 'center', 'width' => '100'),
                    '7' => array('data'=> $status, 'class' => 'center', 'width' => '100', 'width' => '80'),
                    '8' => array('data'=> $action_str, 'class' => 'center', 'width' => '120', 'width' => '80')
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

    public function approval()
    {
        $createdid = $this->session->userdata('logged_in_user');
        $userID=$createdid->id;
        if ($this->uri->segment(4) === FALSE)
        {
            $this->session->set_flashdata('message_error', 'Failed to load survey approval ID, Please try again.');
            redirect('administrator/survey');
        }
        else
        {
            $approvalHash=$this->uri->segment(4); 
            $parseApproval=explode(":", $approvalHash);
            $approval_status_id=$parseApproval[0];
            $survey_id=$parseApproval[1];

            $data = array(
                'id' =>$survey_id,
                'survey_approve' => $approval_status_id,
                'approval_action_by' => $userID
            );

            $res = (int)$this->survey_model->updatesurvey_approval($data);


            if($res)
            {
                $this->session->set_flashdata('message_success', 'Survey approval status updated successfully.');
                redirect('administrator/survey');
            }
            else
            {
                $this->session->set_flashdata('message_error', 'Failed to load survey approval ID, Please try again.');
                redirect('administrator/survey');
            }
        }
    }



    public function filter()
    {
        $filter_survey_title = $this->input->post('filter_survey_title');
        $filter_status = $this->input->post('filter_status');
        $filter_approval_status = $this->input->post('filter_approval_status');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_survey_title != '') {
                $this->session->set_flashdata('filter_survey_title', $filter_survey_title);
            }
            if ($filter_status != '') {
                $this->session->set_flashdata('filter_status', $filter_status);
            }

            if ($filter_approval_status != '') {
                $this->session->set_flashdata('filter_approval_status', $filter_approval_status);
            }
        } else {
            $this->session->unset_userdata('filter_survey_title');
            $this->session->unset_userdata('filter_status');
            $this->session->unset_userdata('filter_approval_status');
        }

        redirect('administrator/survey');
    }

    /**
     * Display add exam form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Create New Survey View'));
        // set page specific variables
        $page_info['title'] = 'Create New Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        //$page_info['cat_survey_lists'] = $this->cat_survey_lists;
        //echo "<pre>";
        ///print_r($this->cat_survey_lists); die();

        $this->_set_fields();
        $this->_set_rules();
        $page_info['catList'] = $this->select_global_model->Select_array('exm_survey_categories');
        //$page_info['catList'] = $this->question_list;

        //print_r_pre($page_info['catList']);
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

    public function add_survey()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Survey'));
        $page_info['title'] = 'Create New Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        $createdid = $this->session->userdata('logged_in_user');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $survey_title = $this->input->post('survey_title');
            $survey_description = $this->input->post('survey_description');
            $survey_anms = $this->input->post('survey_anms');
            $survey_status = $this->input->post('survey_status');
            $survey_added = date('Y-m-d H:i:s');
            $survey_expiry_date = $this->input->post('survey_expiry_date');

            $question_ids = $this->input->post('question_ids');

            if ($survey_status == '') { $survey_status = 'open'; }

            if ($survey_expiry_date == '') {
                $survey_expiry_date = '';
            } else {
                $day = substr($survey_expiry_date, 0, 2);
                $month = substr($survey_expiry_date, 3, 2);
                $year = substr($survey_expiry_date, 6, 4);
                $survey_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $survey_expiry_date && $survey_expiry_date != '' && $survey_expiry_date != '0000-00-00 00:00:00') {
                $survey_status = 'closed';
            }
                            
            if ($question_ids) {
                $question_ids = array_unique($question_ids);
            }
            
            $data = array(
                'survey_title' => $survey_title,
                'survey_description' => $survey_description,
                'survey_status' => $survey_status,
                'survey_anms' => $survey_anms,
                'survey_added' => $survey_added,
                'survey_expired' => $survey_expiry_date,
                'question_ids' => $question_ids,
                'created_by' => $createdid->id
            );

            $res = (int)$this->survey_model->add_survey($data);

            if ($res > 0) {                
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/survey/edit/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey View'));
        // set page specific variables
        $page_info['title'] = 'Edit Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;
        $page_info['catList'] = $this->select_global_model->Select_array('exm_survey_categories');
        // prefill form values
        $survey_id = (int)$this->uri->segment(4);
	    $survey = $this->survey_model->get_survey($survey_id);

       //print_r_pre($survey); die();

        $this->_set_rules();
        //print_r($survey->survey_expired); die();
        @$this->form_data->survey_id = $survey->id;

        //$this->form_data->survey_title = $survey->survey_title;

        $this->form_data->survey_title = $survey->survey_title;
        $this->form_data->survey_description = $survey->survey_description;
        $this->form_data->survey_status = $survey->survey_status;
        $this->form_data->survey_anms = $survey->survey_anms;

        if ($survey->survey_expired == '0000-00-00 00:00:00' || $survey->survey_expired == '') {
            $this->form_data->survey_expiry_date = '';
        } else {
            $this->form_data->survey_expiry_date = date('d/m/Y', strtotime($survey->survey_expired));
        }
        $question_ids = $this->survey_model->get_survey_questions($survey->id);
        $whereID="";
        if($question_ids){

            foreach($question_ids as $k=>$v){
                if($k==0)
                {
                    $whereID .=$v->question_id;
                }
                else
                {
                    $whereID .=",".$v->question_id;
                }


                $this->form_data->question_ids[] = $v->question_id;
            }
        }

        $whereString="";
        if(strlen($whereID)>0)
        {
            $whereString=" WHERE exm_survey_questions.id IN (".$whereID.")";
        }



        $json=$this->select_global_model->FlyQuery(array("SELECT exm_survey_questions.*,(SELECT exm_survey_categories.cat_name FROM exm_survey_categories WHERE exm_survey_categories.id=exm_survey_questions.category_id) as cat FROM exm_survey_questions ".$whereString.""));

        $page_info['modify_question']=$json;

        //print_r_pre($json);
        
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	   $this->load->view('administrator/layouts/default', $page_info);
    }

    public function printpreview()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey View'));

        ob_start();

      $this->load->helper('pdf_helper');
        // set page specific variables
        $page_info['title'] = 'Edit Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;
        $page_info['catList'] = $this->select_global_model->Select_array('exm_survey_categories');
        // prefill form values
        $survey_id = (int)$this->uri->segment(4);

        //echo $survey_id; die();

        $survey = $this->survey_model->get_survey($survey_id);

        

        $survey_type=$survey->survey_anms;
        if($survey_type=='yes')
        {
            $survey_type="Annonymous";
        }
        else
        {
            $survey_type="Normal";
        }


        
        $question_ids = $this->survey_model->get_survey_questions($survey->id);
        $whereID="";
        if($question_ids){

            foreach($question_ids as $k=>$v){
                if($k==0)
                {
                    $whereID .=$v->question_id;
                }
                else
                {
                    $whereID .=",".$v->question_id;
                }


                $this->form_data->question_ids[] = $v->question_id;
            }
        }

        $whereString="";
        if(strlen($whereID)>0)
        {
            $whereString=" WHERE exm_survey_questions.id IN (".$whereID.")";
        }



        $json=$this->select_global_model->FlyQuery(array("SELECT exm_survey_questions.*,(SELECT exm_survey_categories.cat_name FROM exm_survey_categories WHERE exm_survey_categories.id=exm_survey_questions.category_id) as cat FROM exm_survey_questions ".$whereString.""));



        //$page_info['modify_question']=$json;
        $dataCatAr=[];
        if(count($json)>0)
        {
            foreach($json as $row)
            {
                $dataNewSumArray=['id'=>$row['category_id'],'name'=>$row['cat'],'mark'=>$row['survey_weight']];
                $dataKey=$row['category_id'];
                if(array_key_exists($dataKey, $dataCatAr))
                {
                    $dataNewSumArray=$dataCatAr[$dataKey];
                    $currentMark=$dataNewSumArray['mark']+$row['survey_weight'];
                    $dataCatAr[$dataKey]['mark']=$currentMark;
                }
                else
                {
                    $dataCatAr[$dataKey]=$dataNewSumArray;
                }
            }
        }

        //print_r_pre($dataCatAr);

        //print_r_pre($json);
        
        //pdf out start


        tcpdf();

      //ob_start();
      $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
      $obj_pdf->SetCreator(PDF_CREATOR);
      $title = $survey->survey_title;
      $obj_pdf->SetTitle($title);
      //$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '');

      //$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
      $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
      $obj_pdf->SetDefaultMonospacedFont('helvetica');
      //$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      //$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
      //$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$obj_pdf->SetFont('RaviPrakash-Regular', '', 12);
      $obj_pdf->SetFont('helvetica', '', 10);
      $obj_pdf->setFontSubsetting(false);
      $obj_pdf->AddPage();
      //print_r_pre($survey); die();
     // echo $survey->survey_expired; die(); 

      $strHtml='';
$strHtml .='
<html>
   <head><title>PDF</title>
   
   </head>

   <body>
      ';

        $new_exam_time=date('d/m/Y',strtotime($survey->survey_expired));
        
        $survey_type="Normal";
        $survey_type_str=$survey->survey_anms;
        if($survey_type_str=="yes")
        {
            $survey_type="Anonymous";
        }

        $strHtml .='
        <div id="running-exam">


    <div class="exam-info">

        <!-- custom form start -->
        <table border="0" cellpadding="5" width="100%" align="center">
            <tbody>
            <tr>
                <td width="33%"></td>
                <td width="33%" align="center"><img  height="100" src="'.base_url("assets/images/brac_bank.png").'"></td>
                <td width="33%" align="right"></td>
            </tr>
            </tbody>
        </table>
        <h3 style="border: 3px #ccc solid; padding-top: 10px; padding-bottom: 10px;" align="center">
            DO NOT OPEN THE QUESTIONNAIRE UNTIL YOU ARE DIRECTED TO DO SO
        </h3>
        <table border="0">
    <tr style="line-height: 20px;" > 
    <td></td>
    </tr>
    </table>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left"><b>Expire Date :</b> '.$new_exam_time.'</td>
                <td width="50%" align="right"></td>
            </tr>
            </tbody>
        </table>
        <h4 style="padding-top: 5px; padding-bottom: 5px;" align="center">
            Survey for '.$title.'
        </h4>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left" valign="middle">
                <br><br>
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="50%">
                                <strong>Survey Name</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"><strong>'.$title.'</strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                              <strong>Survey ID</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"><strong>'.$survey->id.'</strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Survey Type</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"><strong>'.$survey_type.'</strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Survey Created</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"><strong>'.date('d/m/Y',strtotime($survey->survey_added)).'</strong></td>
                        </tr>
                        
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Date</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"></td>
                        </tr>
                        </tbody>
                    </table>



                    <table cellpadding="5" cellspacing="0" width="90%" align="center">
                        <tbody>
                        <tr>
                            <td  valign="middle" align="center">
                                <br><br><br><br>
                              <strong>For Official Use Only</strong>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </td>
                <td width="50%" align="right" valign="top">
                    <table cellpadding="5" cellspacing="1" width="100%" align="center">
                        <tbody>
                        <tr>
                            <td width="40%">
                            </td>
                            <td width="35%" valign="middle" align="center">';

                                  $dataQrcCodeString="";
                                  $dataQrcCodeString .="Survey ID: ".$survey->id;
                                  $dataQrcCodeString .=", Survey Name: ".$survey->survey_title;
                                  $dataQrcCodeString .=", Survey Start From: ".date('d-m-Y');
                                  $dataQrcCodeString .=", Survey Closed on: ".date('d-m-Y');
                                  $dataQrcCodeString .=", Survey Method: Online/Paper";
                                  $dataQrcCodeString .=", Survey Type: Training / Employee Feedback, etc.";

                                  $dataQrcCodeString .=", Survey Identification: ".$survey_type;
                                  $dataQrcCodeString .=", Survey Population: All / Specific Traget Group, etc.";
                                  $dataQrcCodeString .=", Survey Maker ID: ".$survey->created_by;

                                  $UpFilePath=base_url().'/assets/qrcode/index.php?data='.urlencode($dataQrcCodeString);

                                 // $UpFilePath=base_url().'/assets/qrcode/index.php?data='.substr($exam->exam_title,0,2).$exam->id;
                                    $GenFileQR=file_get_contents($UpFilePath);
                                    $filePath=base_url().'/assets/qrcode/'.$GenFileQR;
                                

                                $strHtml .= '<img src ="'.$filePath.'"   height="250">
                            </td>
                            <td width="30%" style=" height: 100px;" valign="middle" align="center">';

                                
                                    $filePath=base_url().'/assets/images/avatar.png';
                                

                                $strHtml .= '<img src ="'.$filePath.'"  width="125" height="120">
                            </td>
                            <td width="3%">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    
    <table border="0">
    <tr style="line-height: 20px;" > 
    <td></td>
    </tr>
    </table>
                    
                    <table cellpadding="5" cellspacing="1" width="100%" style="padding:20px;" align="center">
                        <tbody>
                        <tr>
                        <td  width="10%">
                        </td>
                            <td style="border: 1px #ccc solid; padding:20px;" width="90%">
                                <p>
                                    <strong>
                                        <u>Instructions to Candidates</u>
                                    </strong>
                                </p>
                                ';

                                    $surveyIns="->Write your name, contact no. and signature on the space given and check all your details carefully
->Use of Calculator is strictly prohibited
->for incorrect MCQ
->Use of cell phones are strictly prohibited during the time of exam
->The mark allocation is indicated at the beginning of each segment
->You are not permitted to leave the examination room early without the prior consent of the invigilator";
                                
                                    if(!empty(trim($surveyIns)))
                                    {
                                        $exam_instructions=explode('->',$surveyIns);
                                        if(substr(trim($surveyIns),0,2)=="->")
                                        {
                                            $exam_instructions=explode('->',substr(trim($surveyIns),2,200000));
                                        }

                                        

                                        $strHtml .= '<ul>';
                                       
                                            foreach($exam_instructions as $ei):
                                              $strHtml .= '<li>'.$ei.'</li>';
                                            endforeach;
                                            
                                        $strHtml .= '</ul>';

                                        
                                    }
                                    else
                                    {
                                        $strHtml .= 'Not Mention.';
                                    }
                                  

                                    $strHtml .= '
                                
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        
        <table border="0">
          <tr style="line-height: 20px;" > 
            <td></td>
          </tr>
        </table>
        <table  cellspacing="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="80%">
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="10%">
                                <strong>SL.</strong>
                            </td>
                            <td style="border: 1px #ccc solid;" width="29%">
                                <strong>Segment</strong>
                            </td>
                            <td  style="border: 1px #ccc solid;" width="29%">
                                <strong>Allocated Marks</strong>
                            </td>
                        </tr>';

                        //echo $strHtml; die();

                        //print_r_pre($examSetInfo);

                        $totalSetMark=0;
                        if(isset($dataCatAr) && !empty($dataCatAr))
                        {
                            foreach ($dataCatAr as $key=>$sinfo) 
                            {
                                //print_r_pre($sinfo);
                                $strHtml .='<tr>
                                    <td  style="border: 1px #ccc solid;">'.
                                        ($key+1)
                                    .'</td>
                                    <td style="border: 1px #ccc solid;">'.
                                        $sinfo['name'].'
                                    </td>
                                    <td  style="border: 1px #ccc solid;">'.
                                        $sinfo['mark'].
                                    '</td>
                                </tr>';

                                $totalSetMark+=$sinfo['mark'];
                            }
                        }

                        $strHtml .='<tr>
                                    <td  style="border: 1px #ccc solid;"></td>
                                    <td style="border: 1px #ccc solid;"><b>Total</b>
                                    </td>
                                    <td  style="border: 1px #ccc solid;">'.
                                        $totalSetMark.
                                    '</td>
                                </tr>';



            $strHtml .='</tbody>
                    </table>
                </td>
                <td width="20%" style="border: 1px #ccc solid;">

                </td>
            </tr>
            <tr>
                <td>
                    <table   cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td align="center" width="39%">
                                <strong></strong>
                            </td>
                            <td width="29%">
                                <strong></strong>
                            </td>
                            <td width="29%">

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td  align="center" width="20%">
                    <b>Invigilator&#39;s PIN &amp; Signature</b>
                </td>
            </tr>
            </tbody>
        </table>

        

    


    </div>';




            //echo $strHtml; die();


            $strHtml .='</div>

        </div>

<div class="mask-layer"></div>
<!--running-exam ends-->

';

        $jsonQuestion=$dataCatAr;

      $strHtml .= '
      <h4 align="center">Questions Paper </h4><br><br>';
      //questionsCateWithMark
      $slqsPart=1;
      foreach($jsonQuestion as $index=>$catMark):

            if($slqsPart>1)
            {
                $strHtml .='<div style="page-break-before:always"></div>';
            }

      $strHtml .='<table border="0" width="100%">
                    <tbody>
                      <tr>
                        <td width="90%" style="border-bottom:2px #000 solid;">
                          <b>'.$slqsPart.'. '.$catMark['name'].'</b>
                        </td>
                        <td>
                          <b>'.number_format($catMark['mark'],2).'</b>
                        </td>
                      </tr>
                    </tbody>    

                  </table><br><br>';


        $questions= $this->select_global_model->FlyQuery(array("SELECT exm_survey_questions.*,(SELECT exm_survey_categories.cat_name FROM exm_survey_categories WHERE exm_survey_categories.id=exm_survey_questions.category_id) as cat FROM exm_survey_questions WHERE exm_survey_questions.category_id='".$catMark['id']."'"));



        //print_r_pre($questions);
        $strHtml .='<table border="0" width="100%">
                    <tbody>';
        for($i=0; $i<count($questions); $i++){

            //print_r_pre($questions[$i]);

            $carTextStr=html_entity_decode($questions[$i]['ques_text']);

            $quesNo=$i+1;
            $strHtml .=  '<tr><td valign="middle" width="3%">'.$quesNo. ') </td><td valign="middle" width="80%" align="left">' .$carTextStr . '</td><td valign="middle">('.$questions[$i]['survey_weight'].')</td></tr>';
            //$strHtml .='<br>';
            //$strHtml .='<tr><td colspan="3"></td></tr>';
            $strHtml .='<tr><td colspan="3">';

            $char = 'A';
            if($questions[$i]['ques_type']=='option_based') {
                //echo 1; die();
                //print_r_pre(unserialize($questions[$i]['ques_choices']));

              $strHtml .='<br>';
              foreach (unserialize($questions[$i]['ques_choices']) as $vaue) {
                $strHtml .='&nbsp;&nbsp;&nbsp;' . $char . ') ';
                $strHtml .=$vaue['text'];
                $strHtml .='<br>';
                $char++;
              }
              $strHtml .='<br>';
              $strHtml .='<br>';
            }
            else
            {
              $strHtml .='<div style="page-break-before:always"></div>';
            }

            $strHtml .='</td></tr>';

          }

          $strHtml .='</tbody></table>';

          $slqsPart++;
      endforeach;



      

      $strHtml .='</div></body></html>';



      //echo $strHtml; die();
       // var_dump($strHtml);die;

      $obj_pdf->writeHTML($strHtml, true, false, true, false, '');
      $obj_pdf->SetDisplayMode('fullpage');
      ob_end_clean();
      $obj_pdf->Output(str_replace(' ','_',trim($title)).'_set_'.time().'.pdf', 'I');

        //pdf out end
    }

    public function update_survey()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Survey'));
        // set page specific variables
        $page_info['title'] = 'Edit Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;
        $createdid = $this->session->userdata('logged_in_user');

        $survey_id = (int)$this->input->post('survey_id');
        
        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->survey_id = $survey_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $survey_title = $this->input->post('survey_title');
            $survey_description = $this->input->post('survey_description');
            $survey_status = $this->input->post('survey_status');
            $survey_added = date('Y-m-d H:i:s');
            $survey_anms = $this->input->post('survey_anms');
            $survey_expiry_date = $this->input->post('survey_expiry_date');

            $question_ids = $this->input->post('question_ids');

            if ($survey_status == '') { $survey_status = 'open'; }

            if ($survey_expiry_date == '') {
                $survey_expiry_date = '';
            } else {
                $day = substr($survey_expiry_date, 0, 2);
                $month = substr($survey_expiry_date, 3, 2);
                $year = substr($survey_expiry_date, 6, 4);
                $survey_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $survey_expiry_date && $survey_expiry_date != '' && $survey_expiry_date != '0000-00-00 00:00:00') {
                $survey_status = 'closed';
            }
                            
            if ($question_ids) {
                $question_ids = array_unique($question_ids);
            }

            $data = array(
                'survey_title' => $survey_title,
                'survey_description' => $survey_description,
                'survey_status' => $survey_status,
                'survey_added' => $survey_added,
                'survey_anms' => $survey_anms,
                'survey_expired' => $survey_expiry_date,
                'question_ids' => $question_ids,
                'created_by' => $createdid->id
            );

            if ($this->survey_model->update_survey($survey_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->exam_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/survey/edit/'. $survey_id);
        }
    }
  
    public function getQes($value=''){
        //echo $value;
        $getData = $this->select_global_model->Select_array('exm_survey_questions',array('category_id'=>$value));
        echo json_encode($getData);
    }

    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->survey_id = 0;
        $this->form_data->survey_title = '';
        $this->form_data->survey_description = '';
        $this->form_data->survey_status = 'open';
        $this->form_data->survey_anms = 'yes';
        $this->form_data->survey_expiry_date = '';
        $this->form_data->question_ids = array();

        $this->form_data->filter_survey_title = '';
        $this->form_data->filter_status = '';
        $this->form_data->filter_approval_status = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('survey_title', 'Survey Title', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_description', 'Survey Description', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_status', 'Survey Status', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('question_ids[]', 'Questions', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_expiry_date', 'Survey Expiry Date', 'required|trim|xss_clean|strip_tags');
    }

}

/* End of file exam.php */
/* Location: ./application/controllers/administrator/exam.php */