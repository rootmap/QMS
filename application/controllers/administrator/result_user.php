<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Result_user extends MY_Controller
{
    var $current_page = "result";
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library('excel');
        $this->load->library('table');
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('result_model');
        $this->load->model('exam_model');
        $this->load->model('category_model');
        $this->load->model('user_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/Select_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $this->competence_list = $this->user_model->get_user_competency();
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'User Result View'));
        // set page specific variables
        $page_info['title'] = 'User Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/result_user_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $records = '';
        $this->_set_fields();

        if ($this->session->flashdata('user_login')) {
            $this->form_data->user_login = $this->session->flashdata('user_login');
            $this->session->keep_flashdata('user_login');
        }
        if ($this->session->flashdata('date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('date_from')));
            $this->session->keep_flashdata('date_from');
        }
        if ($this->session->flashdata('date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('date_to')));
            $this->session->keep_flashdata('date_to');
        }
        if ($this->session->flashdata('records')) {
            $records = $this->session->flashdata('records');
            $this->session->keep_flashdata('records');
        }


        if ($this->session->flashdata('records')) {
            $records = $this->session->flashdata('records');
            $page_info['records'] = $records;
            $this->session->keep_flashdata('records');
        }
        //print_r_pre($records);
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Exam Title'),
                '1' => array('data'=> 'Correct'),
                '2' => array('data'=> 'Incorrect'),
                '3' => array('data'=> 'Pass'),
                '4' => array('data'=> 'Total attempt'),
                '5' => array('data'=> 'Point'),
                '6' => array('data'=> 'Score %'),
                '7' => array('data'=> 'Competency level'),
                '8' => array('data'=> 'Start Time'),
                '9' => array('data'=> 'End Time'),
                '10' => array('data'=> '', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) { 


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


               // $user_name = $records[$i]->user_name;
               // $user_login = $records[$i]->user_login;
               // $team_name = $records[$i]->team_name;
                //$phone = $records[$i]->phone;
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
/*
                if(empty($phone))
                {
                    $getUserInfo=$this->Select_global_model->FlyQuery(array("SELECT * FROM exm_users WHERE user_login='".$user_login."'"));
                    if(!empty($getUserInfo))
                    {
                        $phone=$getUserInfo[0]['phone'];
                    }
                }*/





                $user_exam_id = (int)$records[$i]->user_exam_id;
                $usID = (int)$records[$i]->user_id;
                $EXID = (int)$records[$i]->exam_id;
                $exam_title = $records[$i]->exam_title;
                //$correct = (int)$records[$i]->result_total_correct;
                //$wrong = (int)$records[$i]->result_total_wrong;
                //$dontknow = (int)$records[$i]->result_total_dontknow;
                //$answered = (int)$records[$i]->result_total_answered;
                //$user_score = (float)$records[$i]->result_user_score;
                //$user_score_percent = $records[$i]->result_user_score_percent .'%';
                //$competency_level = $records[$i]->result_competency_level;
                //$result_start_time = date('d-m-Y, g:ia', strtotime($records[$i]->result_start_time));
                //$result_end_time = date('d-m-Y, g:ia', strtotime($records[$i]->result_end_time));

                $action_str = '';
                if(!empty($records[$i]->result_exam_state))
                {
                    $action_str = anchor(base_url('administrator/result_user/review/'. $user_exam_id), '<i class="icon-check"></i>', array('title' => 'Review Answers'));
                }
                else
                {
                    $action_str = 'Manual Upload';
                }

                $anch = anchor(base_url('administrator/result_user/review/'. $user_exam_id), $exam_title, array('title' => 'click here to view details'));;
                
                
                $tbl_row = array(
                    '0' => array('data'=> $anch),
                    '1' => array('data'=> $correct),
                    '2' => array('data'=> $wrong),
                    '3' => array('data'=> $dontknow),
                    '4' => array('data'=> $answered),
                    '5' => array('data'=> $user_score),
                    '6' => array('data'=> $user_score_percent),
                    '7' => array('data'=> $competency_level),
                    '8' => array('data'=> $result_start_time),
                    '9' => array('data'=> $result_end_time),
                    '10' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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

    public function ViewQns($value='')
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'User Categorywise Result View'));
        $page_info['title'] = 'User Categorywise Results'. $this->site_name;
        $page_info['view_page'] = 'user/categoryResult';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['Datassss'] = $this->result_model->getCategoryResult($value);
        $page_info['otherDatas'] = $this->result_model->getSubjectResult($value);
        
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        $this->load->view('administrator/layouts/default', $page_info);
    }

    public function show_results()
    {
        $user_login = $this->input->post('user_login');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');
        
        if ($user_login != '') {

            $this->session->set_flashdata('user_login', $user_login);
            $user = $this->user_model->get_user_by_login($user_login);

            $date_from = $this->convert_date_format($date_from);
            $this->session->set_flashdata('date_from', $date_from);

            $date_to = $this->convert_date_format($date_to);
            $this->session->set_flashdata('date_to', $date_to);

            if ($user) {

                //var_dump($user->id);die;

                $results = $this->result_model->get_results_by_user_id($user->id, $date_from, $date_to);

               // print_r_pre($results);

                //var_dump($results);die;
                $this->session->set_flashdata('records', $results);

            } else {
                $this->session->set_flashdata('message_error', 'No user found with \''. $user_login .'\' User ID. Please try a different User ID.' );
            }

        } else {
            $this->session->set_flashdata('message_error', 'User ID can\'t be empty' );
        }

        redirect('administrator/result_user');
    }

    public function export()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'User Result Export'));
        if ($this->session->flashdata('user_login')) {
            $this->form_data->user_login = $this->session->flashdata('user_login');
            $this->session->keep_flashdata('user_login');
        }
        if ($this->session->flashdata('date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('date_from')));
            $this->session->keep_flashdata('date_from');
        }
        if ($this->session->flashdata('date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('date_to')));
            $this->session->keep_flashdata('date_to');
        }
        if ($this->session->flashdata('records')) {
            $records = $this->session->flashdata('records');
            $this->session->keep_flashdata('records');
        }
        
        $records = $this->session->flashdata('records');

        if (is_array($records) && count($records) > 0) {

            $user = $this->user_model->get_user_by_login($this->session->flashdata('user_login'));

            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle('Sheet1');

            // set result column header
            $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
            $this->excel->getActiveSheet()->setCellValue('B1', 'Exam');
            $this->excel->getActiveSheet()->setCellValue('C1', 'Correct');
            $this->excel->getActiveSheet()->setCellValue('D1', 'Incorrect');
            $this->excel->getActiveSheet()->setCellValue('E1', 'Pass');
            $this->excel->getActiveSheet()->setCellValue('F1', 'Total Attempt');
            $this->excel->getActiveSheet()->setCellValue('G1', 'Point');
            $this->excel->getActiveSheet()->setCellValue('H1', 'Score %');
            $this->excel->getActiveSheet()->setCellValue('I1', 'Competency Level');
            $this->excel->getActiveSheet()->setCellValue('J1', 'Start Time');
            $this->excel->getActiveSheet()->setCellValue('K1', 'End Time');

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

            // fill data
            for ($i=0; $i<count($records); $i++) {



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


               // $user_name = $records[$i]->user_name;
               // $user_login = $records[$i]->user_login;
               // $team_name = $records[$i]->team_name;
                //$phone = $records[$i]->phone;
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
/*              
                if(empty($phone))
                {
                    $getUserInfo=$this->Select_global_model->FlyQuery(array("SELECT * FROM exm_users WHERE user_login='".$user_login."'"));
                    if(!empty($getUserInfo))
                    {
                        $phone=$getUserInfo[0]['phone'];
                    }
                }*/







                $serial = $i+1;
                $exam = $records[$i]->exam_title;
                //$correct = $records[$i]->result_total_correct;
                //$wrong = $records[$i]->result_total_wrong;
                //$dontknow = $records[$i]->result_total_dontknow;
                //$answered = $records[$i]->result_total_answered;
               // $point = $records[$i]->result_user_score;
               // $score = $records[$i]->result_user_score_percent;
                //$competency_level = $records[$i]->result_competency_level;
               // $start_time = date('n/d/Y g:i A', strtotime($records[$i]->result_start_time));
                //$end_time = date('n/d/Y g:i A', strtotime($records[$i]->result_end_time));

                $row = $i + 2;

                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $exam);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $correct);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $wrong);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $dontknow);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $answered);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $point);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $score);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $competency_level);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $start_time);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $end_time);
            }

            $filename = 'User Result ('. $user->user_login .') '. date('Y-m-d') .'.xls';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'. $filename. '"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save('php://output');

        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/result_user');
        }
    }


    public function review($user_exam_id = 0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Review Result View'));
        // set page specific variables
        $page_info['title'] = 'Review Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/review_result_view';
        $page_info['back_link'] = site_url('administrator/result_user');
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        
        $user_exam_id = (int)$user_exam_id;

        if ($this->session->flashdata('user_login')) {
            $this->form_data->user_login = $this->session->flashdata('user_login');
            $this->session->keep_flashdata('user_login');
        }
        if ($this->session->flashdata('date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('date_from')));
            $this->session->keep_flashdata('date_from');
        }
        if ($this->session->flashdata('date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('date_to')));
            $this->session->keep_flashdata('date_to');
        }
        if ($this->session->flashdata('records')) {
            $records = $this->session->flashdata('records');
            $this->session->keep_flashdata('records');
        }

        $result = $this->result_model->get_result_by_user_exam_id($user_exam_id);
        //print_r_pre($result);die;
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


        
        $subJectData=[];
        $totlaExamMark=0;
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
                                $totlaExamMark+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type!="correct")
                            {
                                $subJectData[$category_id]->marks=0;
                            }
                            else
                            {
                                $subJectData[$category_id]->marks=$quesData->ques_user_score;
                                $totlaExamMark+=$quesData->ques_user_score;
                            }
                        }
                        else
                        {
                            if($ques_type=="mcq" && $ques_answer_type=="correct")
                            {
                                $subJectData[$category_id]->marks+=$ques_score;
                                $totlaExamMark+=$ques_score;
                            }
                            elseif($ques_type=="mcq" && $ques_answer_type!="correct")
                            {
                                $subJectData[$category_id]->marks+=0;
                            }
                            else
                            {
                                $subJectData[$category_id]->marks+=$quesData->ques_user_score;
                                $totlaExamMark+=$quesData->ques_user_score;
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

        //print_r_pre($totlaExamMark);

        $competency_level = $this->result_model->calculate_competency_level($totlaExamMark, $this->competence_list);


        //print_r_pre($competency_level);

        $page_info['subject_wise'] = $subJectData;
        $page_info['competency_level'] = $competency_level;
        
        $page_info['user_exam_id'] = $user_exam_id;

        $page_info['result'] = $result;
        //var_dump($result);die;
        $page_info['exam'] = $exam;
        $page_info['result_id'] = $result->id;

        $this->session->set_userdata('exam', $exam);


        // determine messages
        if ($this->session->flashdata('message_error')) { $page_info['message_error'] = $this->session->flashdata('message_error'); }
        if ($this->session->flashdata('message_success')) { $page_info['message_success'] = $this->session->flashdata('message_success'); }
        
        // load view
		$this->load->view('administrator/layouts/default', $page_info);
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
        $this->form_data->user_login = '';
        $this->form_data->date_from = '';
        $this->form_data->date_to = '';
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('user_login', 'User ID', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_from', 'Date From', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_to', 'Date To', 'trim|xss_clean|strip_tags');
	}
}

/* End of file result_user.php */
/* Location: ./application/controllers/administrator/result_user.php */