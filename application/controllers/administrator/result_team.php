<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Result_team extends MY_Controller
{
    var $current_page = "result";
    var $exam_list = array();
    var $competence_list = array();
    var $user_group_list = array();
    var $user_team_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library('excel');
        $this->load->library('table');
        $this->load->library('pagination');
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('user_group_model');
        $this->load->model('user_team_model');
        $this->load->model('user_model');
        $this->load->model('category_model');
        $this->load->model('exam_model');
        $this->load->model('result_model');

        $this->load->model('global/insert_global_model');
        $this->load->model('global/Select_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $this->competence_list = $this->user_model->get_user_competency();

        // pre-load lists
        $open_exams = $this->exam_model->get_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[] = 'Select an User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
            }
        }

        $user_teams = $this->user_team_model->get_user_teams();
        $this->user_team_list[] = 'Select an User Team';
        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->user_team_list[$user_teams[$i]->id] = $user_teams[$i]->team_name;
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
                redirect('home');
            }
        }
    }

    public function index()
    {

       // print_r_pre($this->competence_list);
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Exam Results View'));
        // set page specific variables
        $page_info['title'] = 'Exam Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/result_team_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $records = '';
        $records_paged = '';
        $this->_set_fields();

        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $records = $this->session->flashdata('team_records');
            $this->session->keep_flashdata('team_records');
        }

        if ($records) {

            $exam_id = (int)$this->form_data->exam_id;
            $group_id = (int)$this->form_data->group_id;
            $team_id = (int)$this->form_data->team_id;
            $start_date = $this->session->flashdata('team_date_from');
            $end_date = $this->session->flashdata('team_date_to');

            $attendee_number = (int)$this->result_model->get_attendee_list_count($exam_id, $group_id, $team_id, $start_date, $end_date);
            //var_dump($this->db->last_query());die;
            $nonattendee_number = (int)$this->result_model->get_non_attendee_list_count($exam_id, $group_id, $team_id, $start_date, $end_date);
            $page_info['attendee'] = $attendee_number;
            $page_info['non_attendee'] = $nonattendee_number;
            if ( ($attendee_number + $nonattendee_number) > 0 ) {
                $page_info['response_rate'] = floor( ($attendee_number / ($attendee_number + $nonattendee_number)) * 100);
            } else {
                $page_info['response_rate'] = 0;
            }

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/result_team";
            $config["total_rows"] = count($records);
            $per_page = $this->config->item('per_page');
            $uri_segment = $this->config->item('uri_segment');
            $page_offset = $this->uri->segment($uri_segment);

            $this->pagination->initialize($config);
            $page_info['pagin_links'] = $this->pagination->create_links();
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            $records_paged = array_slice($records, $page_offset, $per_page);
            
            $tbl_heading = array(
                '0' => array('data'=> 'Name'),
                '1' => array('data'=> 'ID'),
                '2' => array('data'=> 'Team'),
                '3' => array('data'=> 'Correct'),
                '4' => array('data'=> 'Incorrect'),
                '5' => array('data'=> 'Pass'),
                '6' => array('data'=> 'Total attempt'),
                '7' => array('data'=> 'Exam Score'),
                '8' => array('data'=> 'Point'),
                '9' => array('data'=> 'Score %'),
                '10' => array('data'=> 'Competency level'),
                '11' => array('data'=> 'Start Time'),
                '12' => array('data'=> 'End Time'),
                '13' => array('data'=> '', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);
            
            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);
            $records=$records_paged;
            for ($i = 0; $i<count($records_paged); $i++) {




                $exam_row_total=0;
                $total_subject_mark=0;
                $exam_total_questions=0;
                $exam_subjecttive_answer=0;
                if(isset(unserialize($records[$i]->result_exam_state)->exam_questions))
                {
                    $defineExamCatResultArray=unserialize($records[$i]->result_exam_state)->exam_questions;
                }
                else
                {
                    $defineExamCatResultArray="";
                }
                
                $pushResultScoreKey=array();
                $pushResultScore=[];
                if(count($defineExamCatResultArray)>0)
                {
                    foreach ($defineExamCatResultArray as $row) {
                        $getRowCatInfoVal=$this->Select_global_model->FlyQuery(array("SELECT cat_name FROM exm_categories WHERE id='".$row->question->category_id."'"));



                        $res = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $getRowCatInfoVal[0]['cat_name']));


                        $ques_type=$row->question->ques_type;
                        $ques_answer_type=$row->question->ques_answer_type; //correct
                        $ques_score=$row->question->ques_score; //correct
                        $ques_user_score=$row->question->ques_user_score; //correct
                        //$ques_type=$row->question->ques_type;
                        $total_subject_mark+=$ques_score;
                        $exam_total_questions+=1;
                        

                        if(!in_array($res, $pushResultScoreKey))
                        {
                            array_push($pushResultScoreKey, $res);
                        }

                        if(isset($pushResultScore["$res"]))
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $exam_row_total+=$ques_score;
                                $pushResultScore["$res"]+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type=="wrong")
                            {
                                $pushResultScore["$res"]+=0;
                            }
                            else
                            {
                                $pushResultScore["$res"]+=$ques_user_score;
                                $exam_row_total+=$ques_user_score;
                                if($ques_user_score>0)
                                {
                                    $exam_subjecttive_answer+=1;
                                }
                            }
                        }
                        else
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $pushResultScore["$res"]=$ques_score;
                                $exam_row_total+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type=="wrong")
                            {
                                $pushResultScore["$res"]=0;
                            }
                            else
                            {
                                $pushResultScore["$res"]=$ques_user_score;
                                $exam_row_total+=$ques_user_score;
                                if($ques_user_score>0)
                                {
                                    $exam_subjecttive_answer+=1;
                                }
                            }
                        }
                    }
                }

                $exam_score = (int)$total_subject_mark;

                //print_r_pre($exam_score);
                //$exam_total_questions = (int)$records[$i]->exam_total_questions;

               // print_r_pre($exam_total_questions);



                /**
                 * result information
                 ********************************************************************************/
                    $total_answered = (int)$records[$i]->result_total_answered+$exam_subjecttive_answer;
                    $correct_answers = (int)$records[$i]->result_total_correct+$exam_subjecttive_answer;
                    $wrong_answers = (int)$records[$i]->result_total_wrong;
                    $dontknow_answers = (int)$records[$i]->result_total_dontknow;
                    $correct_answer_percent = number_format(($correct_answers * 100) / $exam_total_questions, 0);
        

                $user_score = number_format((float)$exam_row_total, 2);
                $user_score_percent = number_format((float)(($user_score / $exam_score) * 100), 2);


                $user_name = $records[$i]->user_name;
                $user_login = $records[$i]->user_login;
                $team_name = $records[$i]->team_name;
                $phone = $records[$i]->phone;
                $correct = $correct_answers;
                $wrong = $records[$i]->result_total_wrong;
                $dontknow = $records[$i]->result_total_dontknow;
                $answered = $total_answered;
                $user_score = $exam_row_total;
                $user_score_percent = $user_score_percent;
                //$competency_level = $records[$i]->result_competency_level;
                $result_start_time = date('n/d/Y g:i A', strtotime($records[$i]->result_start_time));
                $result_end_time = date('n/d/Y g:i A', strtotime($records[$i]->result_end_time)); 

                $competency_level = $this->result_model->calculate_competency_level($user_score_percent, $this->competence_list);

                if(empty($phone))
                {
                    $getUserInfo=$this->Select_global_model->FlyQuery(array("SELECT * FROM exm_users WHERE user_login='".$user_login."'"));
                    if(!empty($getUserInfo))
                    {
                        $phone=$getUserInfo[0]['phone'];
                    }
                }





                $user_exam_id = (int)$records_paged[$i]->user_exam_id;
                //$user_name = $records_paged[$i]->user_name;
               // $user_login = $records_paged[$i]->user_login;
                //$team_name = $records_paged[$i]->team_name;
                //$correct = (int)$records_paged[$i]->result_total_correct;
                //$wrong = (int)$records_paged[$i]->result_total_wrong;
                //$dontknow = (int)$records_paged[$i]->result_total_dontknow;
                //$answered = (int)$records_paged[$i]->result_total_answered;
                //$user_score = (float)$records_paged[$i]->result_user_score;
                //$user_score_percent = $records_paged[$i]->result_user_score_percent .'%';
                //$//competency_level = $records_paged[$i]->result_competency_level;
                //$result_start_time = date('d-m-Y, g:ia', strtotime($records_paged[$i]->result_start_time));
                //$result_end_time = date('d-m-Y, g:ia', strtotime($records_paged[$i]->result_end_time));

                $action_str = '';
                if(!empty($records_paged[$i]->result_exam_state))
                {
                    $action_str = anchor(base_url('administrator/result_team/review/'. $user_exam_id), '<i class="icon-check"></i>', array('title' => 'Review Answers'));
                }
                else
                {
                    $action_str = 'Manual Upload';
                }
                
                
                $tbl_row = array(
                    '0' => array('data'=> $user_name),
                    '1' => array('data'=> $user_login),
                    '2' => array('data'=> $team_name),
                    '3' => array('data'=> $correct),
                    '4' => array('data'=> $wrong),
                    '5' => array('data'=> $dontknow),
                    '6' => array('data'=> $answered),
                    '7' => array('data'=> $exam_score),
                    '8' => array('data'=> $user_score),
                    '9' => array('data'=> $user_score_percent),
                    '10' => array('data'=> $competency_level),
                    '11' => array('data'=> $result_start_time),
                    '12' => array('data'=> $result_end_time),
                    '13' => array('data'=> $action_str, 'class' => 'center', 'width' => '50px')
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();

        } else {
            //$page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No results found.</div>';
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

    public function show_results()
    {

        $exam_id = (int)$this->input->post('exam_id');
        $group_id = (int)$this->input->post('group_id');
        $team_id = (int)$this->input->post('team_id');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        if(empty($date_from))
        {
            $date_from=date('d/m/Y');
        }

        if(empty($date_to))
        {
            $date_to=date('d/m/Y');
        }

        if ($exam_id > 0) {

            $this->session->set_flashdata('exam_id', $exam_id);
            $this->session->set_flashdata('group_id', $group_id);
            $this->session->set_flashdata('team_id', $team_id);

            $date_from = $this->convert_date_format($date_from);
            $this->session->set_flashdata('team_date_from', $date_from);

            $date_to = $this->convert_date_format($date_to);
            $this->session->set_flashdata('team_date_to', $date_to);

            $results = $this->result_model->get_results_by_team_id($exam_id, $group_id, $team_id, $date_from, $date_to);

            //var_dump($this->db->last_query());die;
            //print_r_pre($results);die;
            $this->session->set_flashdata('team_records', $results);

        } else {
            $this->session->set_flashdata('message_error', 'Please select an exam.' );
        }

        redirect('administrator/result_team');
    }

    public function export()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Team Result'));
        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $records = $this->session->flashdata('team_records');
            $this->session->keep_flashdata('team_records');
        }


        
        $records = $this->session->flashdata('team_records');

        

        if (is_array($records) && count($records) > 0) {

            $exam_title = $this->exam_list[$this->session->flashdata('exam_id')];

            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle('Sheet1');

            // set result column header
            $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
            $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
            $this->excel->getActiveSheet()->setCellValue('C1', 'ID');
            $this->excel->getActiveSheet()->setCellValue('D1', 'Phone');
            $this->excel->getActiveSheet()->setCellValue('E1', 'Team');
            $this->excel->getActiveSheet()->setCellValue('F1', 'Correct');
            $this->excel->getActiveSheet()->setCellValue('G1', 'Incorrect');
            $this->excel->getActiveSheet()->setCellValue('H1', 'Pass');
            $this->excel->getActiveSheet()->setCellValue('I1', 'Total Attempt');
            $this->excel->getActiveSheet()->setCellValue('J1', 'Point');
            $this->excel->getActiveSheet()->setCellValue('K1', 'Score %');
            $this->excel->getActiveSheet()->setCellValue('L1', 'Competency Level');
            $this->excel->getActiveSheet()->setCellValue('M1', 'Start Time');
            $this->excel->getActiveSheet()->setCellValue('N1', 'End Time');


            $defColumn=array();

            foreach ($records as $key => $row) {
                $defineExamCatArray=unserialize($row->result_exam_state)->exam_questions;
                if(count($defineExamCatArray)>0)
                {
                    foreach($defineExamCatArray as $dcat)
                    {
                        $getRowCatInfo=$this->Select_global_model->FlyQuery(array("SELECT cat_name FROM exm_categories WHERE id='".$dcat->question->category_id."'"));
                        array_push($defColumn,$getRowCatInfo[0]['cat_name']);
                    }
                }
            }
            



            $uniqueExcelColumn=array_unique($defColumn);


            if(count($uniqueExcelColumn)>0)
            {
                    $kk=79;
                    foreach($uniqueExcelColumn as $vd)
                    {
                        $this->excel->getActiveSheet()->setCellValue(chr($kk).'1', $vd);
                        $kk++;
                    }
            }

            $this->excel->getActiveSheet()->setCellValue(chr(($kk+1)).'1', 'Total');

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

            if(count($uniqueExcelColumn)>0)
            {
                    $kk=79;
                    foreach($uniqueExcelColumn as $vd)
                    {
                        $this->excel->getActiveSheet()->getColumnDimension(chr($kk))->setAutoSize(true);
                        $kk++;
                    }
            }

            $this->excel->getActiveSheet()->getColumnDimension(chr(($kk+1)))->setAutoSize(true);

            //print_r_pre($uniqueExcelColumn);die;

            // fill data
            for ($i=0; $i<count($records); $i++) {

                $serial = $i+1;

                $exam_row_total=0;
                $total_subject_mark=0;
                $exam_total_questions=0;
                $exam_subjecttive_answer=0;

                $defineExamCatResultArray=unserialize($records[$i]->result_exam_state)->exam_questions;
                $pushResultScoreKey=array();
                $pushResultScore=[];
                if(count($defineExamCatResultArray)>0)
                {
                    foreach ($defineExamCatResultArray as $row) {
                        $getRowCatInfoVal=$this->Select_global_model->FlyQuery(array("SELECT cat_name FROM exm_categories WHERE id='".$row->question->category_id."'"));



                        $res = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $getRowCatInfoVal[0]['cat_name']));


                        $ques_type=$row->question->ques_type;
                        $ques_answer_type=$row->question->ques_answer_type; //correct
                        $ques_score=$row->question->ques_score; //correct
                        $ques_user_score=$row->question->ques_user_score; //correct
                        //$ques_type=$row->question->ques_type;
                        $total_subject_mark+=$ques_score;
                        $exam_total_questions+=1;
                        

                        if(!in_array($res, $pushResultScoreKey))
                        {
                            array_push($pushResultScoreKey, $res);
                        }

                        if(isset($pushResultScore["$res"]))
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $exam_row_total+=$ques_score;
                                $pushResultScore["$res"]+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type=="wrong")
                            {
                                $pushResultScore["$res"]+=0;
                            }
                            else
                            {
                                $pushResultScore["$res"]+=$ques_user_score;
                                $exam_row_total+=$ques_user_score;
                                if($ques_user_score>0)
                                {
                                    $exam_subjecttive_answer+=1;
                                }
                            }
                        }
                        else
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $pushResultScore["$res"]=$ques_score;
                                $exam_row_total+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type=="wrong")
                            {
                                $pushResultScore["$res"]=0;
                            }
                            else
                            {
                                $pushResultScore["$res"]=$ques_user_score;
                                $exam_row_total+=$ques_user_score;
                                if($ques_user_score>0)
                                {
                                    $exam_subjecttive_answer+=1;
                                }
                            }
                        }
                    }
                }

                $exam_score = (int)$total_subject_mark;
                //$exam_total_questions = (int)$records[$i]->exam_total_questions;

               // print_r_pre($exam_total_questions);



                /**
                 * result information
                 ********************************************************************************/
                    $total_answered = (int)$records[$i]->result_total_answered+$exam_subjecttive_answer;
                    $correct_answers = (int)$records[$i]->result_total_correct+$exam_subjecttive_answer;
                    $wrong_answers = (int)$records[$i]->result_total_wrong;
                    $dontknow_answers = (int)$records[$i]->result_total_dontknow;
                    $correct_answer_percent = number_format(($correct_answers * 100) / $exam_total_questions, 0);
        

                $user_score = number_format((float)$exam_row_total, 2);
                $user_score_percent = number_format((float)(($user_score / $exam_score) * 100), 2);


                $user_name = $records[$i]->user_name;
                $user_login = $records[$i]->user_login;
                $team_name = $records[$i]->team_name;
                $phone = $records[$i]->phone;
                $correct = $correct_answers;
                $wrong = $records[$i]->result_total_wrong;
                $dontknow = $records[$i]->result_total_dontknow;
                $answered = $total_answered;
                $point = $exam_row_total;
                $score = $user_score_percent;
                //$competency_level = $records[$i]->result_competency_level;
                $start_time = date('n/d/Y g:i A', strtotime($records[$i]->result_start_time));
                $end_time = date('n/d/Y g:i A', strtotime($records[$i]->result_end_time)); 

                $competency_level = $this->result_model->calculate_competency_level($user_score_percent, $this->competence_list);

                if(empty($phone))
                {
                    $getUserInfo=$this->Select_global_model->FlyQuery(array("SELECT * FROM exm_users WHERE user_login='".$user_login."'"));
                    if(!empty($getUserInfo))
                    {
                        $phone=$getUserInfo[0]['phone'];
                    }
                }

                //print_r_pre($pushResultScore);

                $row = $i + 2;

                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $phone);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $team_name);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $correct);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $wrong);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $dontknow);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $answered);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $point);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $score);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $competency_level);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $start_time);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $end_time);


                if(count($pushResultScore)>0)
                {
                    $rowIncre=14;
                    foreach($pushResultScore as $prs)
                    {
                        //print_r_pre($prs);
                        $this->excel->getActiveSheet()->setCellValueByColumnAndRow($rowIncre, $row, $prs);
                        $rowIncre++;
                    }
                }

                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(($rowIncre+1), $row,$point);

            }

            $filename = 'Exam Result ('. $exam_title .') '. date('Y-m-d') .'.xls';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'. $filename. '"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save('php://output');

        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/result_team');
        }
    }

    public function update_state($user_exam_id=0,$result_id=0){

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update User Result'));
        $save_button = $this->input->post('save_button');
        $publish_button = $this->input->post('publish_button');

        if ($save_button) {



            $exam = $this->session->userdata('exam');
            $score = $this->input->post('user_score');
            $index_value = $this->input->post('index_value');

            //print_r_pre($index_value);

            foreach ($index_value as $key => $value) {
                $exam->exam_questions[$value]->question->ques_user_score= $score[$key];
            }



            //print_r_pre($exam);
            $totalUserScore=0;
            if(count($exam->exam_questions)>0)
            {
                foreach($exam->exam_questions as $rr)
                {
                    $totalUserScore+=$rr->question->ques_user_score;
                    
                }
            }

            $exam->result_user_score=$totalUserScore;

            //print_r_pre($totalUserScore);

            if ($index_value) {

                //var_dump($result_id);die;
                //print_r_pre($exam);

                $update = $this->result_model->update_result_exam_state($result_id,$exam,$totalUserScore);
                if($update)
                {
                    $this->session->set_flashdata('message_success', 'Marks updating is successful.' );
                    redirect('administrator/result_user/review/'. $user_exam_id);

                }
                else{
                    //var_dump($this->db->last_query());die;
                    $this->session->set_flashdata('message_error', 'Marks updating is failed.' );
                    redirect('administrator/result_user/review/'. $user_exam_id);

                }




            } else {
                $this->session->set_flashdata('message_error', 'No questions to be updated.');
                redirect('administrator/result_user/review/'. $user_exam_id);
            }

        }


        else{

            $update2 = $this->result_model->update_result_exam_status($result_id);
            if($update2)
            {
                $this->session->set_flashdata('message_success', 'Result publishing is successful.' );
                redirect('administrator/result_user/review/'. $user_exam_id);

            }
            else{
                //var_dump($this->db->last_query());die;
                $this->session->set_flashdata('message_error', 'Result publishing is failed.' );
                redirect('administrator/result_user/review/'. $user_exam_id);

            }



        }




    }

    public function export_attendee_list()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Attendee List'));
        $this->_set_fields();
        
        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $this->session->keep_flashdata('team_records');
        }

        $exam_title = $this->exam_list[$this->session->flashdata('exam_id')];

        $attendee_list = $this->result_model->get_attendee_list($this->form_data->exam_id, $this->form_data->group_id, $this->form_data->team_id, $this->session->flashdata('team_date_from'), $this->session->flashdata('team_date_to'));
        //var_dump($this->db->last_query());
        //print_r_pre($attendee_list);die;
        $non_attendee_list = $this->result_model->get_non_attendee_list($this->form_data->exam_id, $this->form_data->group_id, $this->form_data->team_id, $this->session->flashdata('team_date_from'), $this->session->flashdata('team_date_to'));


        // --------------------------------------------------------------------
        // preparing attendee sheet
        // --------------------------------------------------------------------
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Attendee List');

        // set result column header
        $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
        $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
        $this->excel->getActiveSheet()->setCellValue('C1', 'Phone');
        $this->excel->getActiveSheet()->setCellValue('D1', 'ID');
        $this->excel->getActiveSheet()->setCellValue('E1', 'Team');
        $this->excel->getActiveSheet()->setCellValue('F1', 'Assigned Start Time');
        $this->excel->getActiveSheet()->setCellValue('G1', 'Assigned End Time');
        $this->excel->getActiveSheet()->setCellValue('H1', 'Status');

        $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

        // fill data
        for ($i=0; $i<count($attendee_list); $i++) {

            $serial = $i+1;

            $user_name = $attendee_list[$i]->user_name;
            $phone = $attendee_list[$i]->phone;
            $user_login = $attendee_list[$i]->user_login;
            $team_name = $attendee_list[$i]->team_name;
            $start_time = date('n/d/Y g:i A', strtotime($attendee_list[$i]->ue_start_date));
            $end_time = date('n/d/Y g:i A', strtotime($attendee_list[$i]->ue_end_date));

            $ue_status = $attendee_list[$i]->ue_status;
            $attStatus="Absent";
            if($ue_status=="complete")
            {
                $attStatus="Present";
            }
            $row = $i + 2;

             $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $phone);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $user_login);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $team_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $start_time);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $end_time);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $attStatus);
        }


        // --------------------------------------------------------------------
        // preparing non attendee sheet
        // --------------------------------------------------------------------
        /*$this->excel->createSheet(1);
        $this->excel->setActiveSheetIndex(1);
        $this->excel->getActiveSheet()->setTitle('Non Attendee List');

        // set result column header
        $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
        $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
        $this->excel->getActiveSheet()->setCellValue('C1', 'Phone');
        $this->excel->getActiveSheet()->setCellValue('D1', 'ID');
        $this->excel->getActiveSheet()->setCellValue('E1', 'Team');
        $this->excel->getActiveSheet()->setCellValue('F1', 'Assigned Start Time');
        $this->excel->getActiveSheet()->setCellValue('G1', 'Assigned End Time');

        $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);


        // fill data
        for ($i=0; $i<count($non_attendee_list); $i++) {

            $serial = $i+1;

            $user_name = $non_attendee_list[$i]->user_name;
            $user_login = $non_attendee_list[$i]->user_login;
            $phone = $attendee_list[$i]->phone;
            $team_name = $non_attendee_list[$i]->team_name;
            $start_time = date('n/d/Y g:i A', strtotime($non_attendee_list[$i]->ue_start_date));
            $end_time = date('n/d/Y g:i A', strtotime($non_attendee_list[$i]->ue_end_date));

            $row = $i + 2;

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $phone);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $user_login);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $team_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $start_time);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $end_time);
        }*/

        $this->excel->setActiveSheetIndex(0);
        $filename = 'Attendee, Non-Attendee List ('. $exam_title .') '. date('Y-m-d') .'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename. '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');

    }

    public function review($user_exam_id = 0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Review Results view'));
        // set page specific variables
        $page_info['title'] = 'Review Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/review_result_view';
        $page_info['back_link'] = site_url('administrator/result_team');
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        
        $user_exam_id = (int)$user_exam_id;

        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $this->session->keep_flashdata('team_records');
        }

        $result = $this->result_model->get_result_by_user_exam_id($user_exam_id);
        $exam = maybe_unserialize($result->result_exam_state);
        if(!isset($exam->exam_title))
        {

            $this->db->where('id',$result->user_exam_id); 
            $query = $this->db->get("user_exams"); 
            $row = $query->row();
            //print_r_pre($row);
             $exam = maybe_unserialize($row->ue_state);
        }


        $this->db->where('id',$result->user_exam_id); 
        $this->db->select('id,user_id,dbo.get_user_full_name_by_id(user_id) as candidate_name,dbo.get_user_login_name_by_id(user_id) as login_pin'); 
        $queryR = $this->db->get("user_exams"); 
        $examCandOInfo = $queryR->row();

        $page_info['examCandOInfo'] = $examCandOInfo;

        //print_r_pre($examCandOInfo);

        //$question_set_id=$exam->exam_questions[0]->qus_set; // die();

        //$examSetInfo = $this->exam_model->get_Set_Info($question_set_id);
        //print_r_pre($exam);
        $subJectData=[];
        if(isset($exam->exam_questions))
        {
            $examQues=$exam->exam_questions;
            if(count($examQues)>0)
            {
               // print_r_pre($examQues);
                foreach($examQues as $rr)
                {
                    if(isset($rr->question))
                    {
                        $quesData=$rr->question;
                        $category_id=$quesData->category_id; 
                        $ques_type=$quesData->ques_type; 
                        $ques_answer_type=$quesData->ques_answer_type; //correct
                        $ques_score=$quesData->ques_score; //correct fix
                        $ques_user_score=$quesData->ques_user_score; //correct get sc

                        if (!array_key_exists($category_id,$subJectData)) {
                            $subJectData[$category_id]=$this->category_model->get_category($category_id);
                        }

                        if(!isset($subJectData[$category_id]->marks))
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $subJectData[$category_id]->marks=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type!="correct")
                            {
                                $subJectData[$category_id]->marks=0;
                            }
                            else
                            {
                                $subJectData[$category_id]->marks=$quesData->ques_user_score;
                            }
                        }
                        else
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $subJectData[$category_id]->marks+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type!="correct")
                            {
                                $subJectData[$category_id]->marks+=0;
                            }
                            else
                            {
                                $subJectData[$category_id]->marks+=$quesData->ques_user_score;
                            }
                        }

                        if(!isset($subJectData[$category_id]->subject_marks))
                        {
                            $subJectData[$category_id]->subject_marks=$ques_score;
                        }
                        else
                        {
                            $subJectData[$category_id]->subject_marks+=$ques_score;
                        }

                        if($quesData->ques_answer_type=="wrong")
                        {
                            $subJectData[$category_id]->marks-=$quesData->neg_mark;
                        }
                        
                        
                        //print_r_pre($rr->question);
                    }
                }
                
            }
            
            
        }

        /*die(); 

        $strHtml="";

        $totalSetMark=0;
        if(isset($examSetInfo) && !empty($examSetInfo))
        {
            foreach ($examSetInfo as $key=>$sinfo) 
            {

                $strHtml .='<tr>
                    <td  style="border: 1px #ccc solid;">'.
                        ($key+1)
                    .'</td>
                    <td style="border: 1px #ccc solid;">'.
                        $sinfo['category_id'].'-'.
                        $sinfo['cat_name'].'
                    </td>
                    <td  style="border: 1px #ccc solid;">'.
                        $sinfo['summary_row'].
                    '</td>
                    <td  style="border: 1px #ccc solid;">

                    </td>
                </tr><br>';

                $totalSetMark+=$sinfo['total_mark'];
            }
        }

        echo  $strHtml; die();*/

        //print_r_pre($subJectData);

        $page_info['subject_wise'] = $subJectData;
        $page_info['user_exam_id'] = $user_exam_id;
        $page_info['result'] = $result;
        $page_info['exam'] = $exam;
        $page_info['result_id'] = $result->id;
        //print_r_pre($result); die();
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

    private function convert_date_format($date_ddmmyyyy = '')
    {
        $date = '';
        if ($date_ddmmyyyy == '') {
            $date = '';
        } else {
            $day = substr($date_ddmmyyyy, 0, 2);
            $month = substr($date_ddmmyyyy, 3, 2);
            $year = substr($date_ddmmyyyy, 6, 4);
            $date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        }
        return $date;
    }

    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
        $this->form_data->exam_id = '0';
        $this->form_data->group_id = '0';
        $this->form_data->team_id = '0';
        $this->form_data->date_from = '';
        $this->form_data->date_to = '';
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('exam_id', 'Exam', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('group_id', 'User Group', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('team_id', 'User Team', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_from', 'Date From', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_to', 'Date To', 'trim|xss_clean|strip_tags');
	}
}

/* End of file result_team.php */
/* Location: ./application/controllers/administrator/result_team.php */