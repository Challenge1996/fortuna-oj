<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contest extends CI_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		$this->load->model('problems');
		$this->load->model('contests');
		
		if ($this->user->is_logged_in()){
			if ($method == 'index' || (isset($params[0]) && $this->contests->is_valid($params[0]))){
				if ($method != 'index' && $method != 'result'){
					$declaration_count = $this->contests->declaration_count($params[0]);
					$this->load->view('contest/navigation', array('cid' => $params[0], 'declaration_count' => $declaration_count));
				}
				$this->_redirect_page($method, $params);
			}else
				$this->load->view('error', array('message' => 'You are NOT participant of this contest'));
		}
		else
			$this->login();
	}
	
	function username_check($username){
		return $this->user->username_check($username);
	}

	function password_check($password){
		$password = md5(md5($password) . $this->config->item('password_suffix'));
		return $this->user->login_check($this->input->post('username', TRUE), $password);
	}
	
	function login(){
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<span class="add-on alert alert-error">', '</span>');
			
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check');
		$this->form_validation->set_rules('password', 'Password', 'required|callback_password_check');
			
		$this->form_validation->set_message('required', "%s is required");
		$this->form_validation->set_message('username_check', 'User NOT exist or DISABLED!');
		$this->form_validation->set_message('password_check', 'Password Error!');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('login');
		}else{
			$this->user->login_success($this->input->post(NULL, TRUE));
			
			$this->load->view('success');
		}
	}
	
	public function index($page = 1){
		$contests_per_page = 20;
	
		$count = $this->contests->count();
		if ($count > 0 && ($count + $contests_per_page - 1) / $contests_per_page < $page)
			$page = ($count + $contests_per_page - 1) / $contests_per_page;
		$row_begin = ($page - 1) * $contests_per_page;
		$data = $this->contests->load_contests_list($row_begin, $contests_per_page);
		foreach ($data as $row){
			$startTime = strtotime($row->startTime);
			$endTime = strtotime($row->endTime);
			$now = strtotime('now');
			if ($now > $endTime) $row->status = '<span class="label label-success">Ended</span>';
			else if ($now < $startTime) $row->status = '<span class="label label-info">Scheduled</span>';
			else{
				$row->status = '<span class="label label-important">Running</span>';
				$row->running = TRUE;
			}
			
			$row->count = $this->contests->load_contest_teams_count($row->cid);
		}

		$this->load->library('pagination');
		$config['base_url'] = '#contest/index/';
		$config['total_rows'] = $count;
		$config['per_page'] = $contests_per_page;
		$config['cur_page'] = $page;
		$this->pagination->initialize($config);

		$this->load->view('contest/index', array('data' => $data));
	}

	public function home($cid){
		$data = $this->contests->load_contest_status($cid);
		
		if ($data == FALSE)
			$this->load->view("error", array('message' => 'Contest NOT exist!'));
		else 
			$this->load->view("contest/home", array('data' => $data));
	}
	
	public function problems($cid){
		if ($info = $this->contests->load_contest_status($cid)){
			$data = $this->contests->load_contest_problemset($cid);
			foreach ($data as $row){
				$row->problem = $this->contests->load_contest_problem_name($row->pid);
				if ($row->title == '') $row->title = $row->problem->title;
				$row->statistic = $this->contests->load_contest_problem_statistic($cid, $row->pid);
			}
		}

		if ($info == FALSE){
			$this->load->view("error", array('message' => 'Contest NOT exist!'));
		} else if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin()) {
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		} else {
			$this->load->view("contest/problems", array('data' => $data, 'info' => $info, 'cid' => $cid));
		}
	}

	public function status($cid, $page = 1){
		$submission_per_page = 20;
		
		$this->load->model('submission');
		$info = $this->contests->load_contest_status($cid);
		if ($info != FALSE){
			$row_begin = ($page - 1) * $submission_per_page;
			$count = $this->contests->load_contest_submission_count($cid);
			$data = $this->contests->load_contest_submission($cid, $row_begin, $submission_per_page,
												$info->running, $this->session->userdata('username'), $this->user->is_admin());
			$this->submission->format_data($data);
			foreach ($data as $row)
				foreach ($info->problemset as $problem)
					if ($row->pid == $problem->pid){
						$row->id = $problem->id;
						break;
					}

			$this->load->library('pagination');
			$config['base_url'] = "#contest/status/$cid/";
			$config['total_rows'] = $count;
			$config['per_page'] = $submission_per_page;
			$config['uri_segment'] = 4;
			$this->pagination->initialize($config);
		}

		if ($info == FALSE){
			$this->load->view("error", array('message' => 'Contest NOT exist!'));
		} else if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else
			$this->load->view('contest/status', array('data' => $data, 'info' => $info, 'is_admin' => $this->user->is_admin()));
	}
	
	public function show($cid, $id = 0){
		$this->load->model('problems');
		$info = $this->contests->load_contest_status($cid);
		if ($info != FALSE){
			$pid = $this->contests->load_contest_pid($cid, $id);
			if ($pid != FALSE){
				$data = $this->problems->load_problem($pid);
				if ($data != FALSE){
					$data->data = json_decode($data->dataConfiguration);
					
					$data->timeLimit = $data->memoryLimit = 0;
					foreach ($data->data->cases as $case) {
						foreach ($case->tests as $test) {
							if ( ! isset($test->timeLimit)) continue;
						
							if ($data->timeLimit == 0) {
								$data->timeLimit = $test->timeLimit;
								$data->memoryLimit = $test->memoryLimit;
							} elseif ($data->timeLimit != $test->timeLimit || $data->memoryLimit != $test->memoryLimit)
								$data->timeLimit = -1;
								
							if ($data->timeLimit < 0) break;
						}
						if ($data->timeLimit < 0) break;
					}
					if ($data->timeLimit <= 0){
						unset($data->timeLimit);
						unset($data->memoryLimit);
					}
				}
				$data->id = $id;
				if ($info->contestMode == 'ACM') $data->id += 1000;
				if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional') $data->id++;
				
				$this->contests->modify_problem($data, $cid);
			}
		}

		if ($info == FALSE || $pid == FALSE || $data == FALSE) {
			$this->load->view('error', array('message' => 'Problem not available!'));
		} else if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin()) {
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		} else {
			$this->load->view('contest/show', array('data' => $data, 'info' => $info, 'cid' => $cid));
		}
	}
	
	public function standing($cid){
		$info = $this->contests->load_contest_status($cid);
		if ($info != FALSE){
			if ($info->contestMode == 'ACM')
				$data = $this->contests->load_contest_ranklist_ACM($cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
				$data = $this->contests->load_contest_ranklist_OI($cid, $info);
			}
		}
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else $this->load->view('contest/standing', array('data' => $data, 'info' => $info));
	}
	
	public function statistic($cid){
		$info = $this->contests->load_contest_status($cid);
		if ($info != FALSE){
			if ($info->contestMode == 'ACM')
				$data = $this->contests->load_contest_statistic_ACM($cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
				$data = $this->contests->load_contest_statistic_OI($cid, $info);
			}
		}
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else $this->load->view('contest/standing', array('data' => $data, 'info' => $info, 'startTime' => $this->contests->load_contest_start_time($cid)));
	}
	
	public function declaration_list($cid){
		$info = $this->contests->load_contest_status($cid);
		$data = $this->contests->load_declaration_list($cid);
		foreach ($data as $row)
			$row->prob = $this->problems->load_title($row->pid);
		if ($info->contestMode == 'ACM'){
			foreach ($data as $row) $row->id += 1000;
		}
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else
			$this->load->view('contest/declaration_list', array('data' => $data, 'cid' => $cid));
	}
	
	public function declaration($cid, $id){
		$data = $this->contests->load_declaration($cid, $id);
		$this->load->view('contest/declaration', array('data' => $data));
	}

	public function add_declaration($cid)
	{
		$title = $this->input->post('title');
		$prob = $this->input->post('prob');
		$decl = $this->input->post('declaration');
		$pid = $this->contests->id_in_contest_to_pid($cid, $prob);
		$this->contests->add_declaration($cid, $pid, $title, $decl);
	}
	
	public function result($cid){
		$info = $this->contests->load_contest_status($cid);
		if ($info != FALSE){
			if ($info->contestMode == 'ACM')
				$data = $this->contests->load_contest_ranklist_ACM($cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
				$data = $this->contests->load_contest_ranklist_OI($cid, $info);
			}
		}
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else if ($data != FALSE) $this->load->view('contest/result', array('data' => $data, 'info' => $info));
	}

	public function forum($cid)
	{
		$del = $this->input->get('del');
		if ($del)
			$this->contests->del_post($del);
		else
		{
			$title = $this->input->post('title');
			$content = $this->input->post('content');
			if ($content)
				$this->contests->add_post($cid,$title,$content);
		}
		
		$data = $this->contests->load_forum($cid);
		$this->load->view('contest/forum', array('data' => $data));
	}
}
