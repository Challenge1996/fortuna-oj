<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		
		$allowed_methods = array('index', 'register', 'userinfo', 'logout');
		if ($this->user->is_logged_in() || in_array($method, $allowed_methods))
			$this->_redirect_page($method, $params);
		else
			$this->login();
	}
	
	public function logout(){
		$this->user->logout();
		$this->load->view('main/home');
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
		$this->form_validation->set_message('username_check', 'User Error!');
		$this->form_validation->set_message('password_check', 'Password Error!');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('login');
		}else{
			$this->user->login_success($this->input->post(NULL, TRUE));
			
			$this->load->view('success');
		}
	}
	
	public function userinfo(){
		$user = $this->session->userdata('username');
		$avatar = $this->user->load_avatar($this->session->userdata('uid'));
		$this->load->view('userinfo', array('user' => $user, 'avatar' => $avatar));
	}
	
	public function register(){
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="alert add-on alert-error">', '</span>');
		
		$this->form_validation->set_rules('username', 'Username', 'required|is_unique[User.name]');
		$this->form_validation->set_rules('password', 'Password', 'required|matches[confirm_password]');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[User.email]');

		$this->form_validation->set_message('is_unique', '%s not available!');
		$this->form_validation->set_message('required', '%s is required!');
		
		if ($this->form_validation->run() == FALSE)
			$this->load->view('register');
		else {
			$this->user->registion_success($this->input->post(NULL, TRUE));
			
			$this->load->view('success');
		}
	}

	public function index(){
		$theme = $this->input->cookie('theme');
		if ( ! $theme) $theme = 'default';
		$this->load->view("$theme/framework", array('logged_in' => $this->user->is_logged_in()));
	}
	
	public function home(){
		$this->load->view('main/home');
	}
	
	function submit_check($pid){
		$this->load->model('problems');
		//$code_size_limit = $this->problems->load_code_size_limit($pid);
		//if ($code_size_limit == FALSE) return FALSE;
		//if ($code_size_limit > 0 && $code_size_limit < strlen($this->input->post('texteditor'))) return FALSE;
		return TRUE;
	}
	
	static function _convert_status($status){
		switch ($status){
			case -3: return '<span class="label label-success">PAC</span>';
			case -2: return '<i class="icon-time"></i>';
			case -1: return '<i class="icon-time"></i>';
			case 0: return '<span class="label label-success">AC</span>';
			case 1: return '<span class="label label-important">PE</span>';
			case 2: return '<span class="label label-important">WA</span>';
			case 3: return '<span class="label">Err</span>';
			case 4: return '<span class="label label-warning">OLE</span>';
			case 5: return '<span class="label label-warning">MLE</span>';
			case 6: return '<span class="label label-warning">TLE</span>';
			case 7: return '<span class="label label-important">RE</span>';
			case 8: return '<span class="label">CE</span>';
			case 9: return '<span class="label">Err</span>';
			default: return $status;
		}
	}

	public function problemset($page = 0){
		$problems_per_page = (int)$this->session->userdata('problems_per_page');
		if ( ! $problems_per_page) $problems_per_page = 20;
		
		$uid = $this->session->userdata('uid');
		
		$this->load->model('user');
		$this->load->model('problems');
		$this->load->model('misc');
		
		if (! ($keyword = $this->input->get('search', TRUE))){
			if (! ($filter = $this->input->get('filter', TRUE))){
				if ($page == 0) $page = $this->user->load_last_page($uid);
				else $this->user->save_last_page($uid, $page);
			} else if ($page == 0) $page = 1;
		} else if ($page == 0) $page = 1;

		if ($keyword){
			$count = $this->problems->search_count($keyword);
			if ($count > 0 && ($count + $problems_per_page - 1) / $problems_per_page < $page)
				$page = ($count + $problems_per_page - 1) / $problems_per_page;
				
			$row_begin = ($page - 1) * $problems_per_page;
			$data = $this->problems->load_search_problemset($keyword, $row_begin, $problems_per_page);
	
			//$result = $this->problems->load_search_problemset_status($uid, $keyword);
			
		} else if ($filter) {
			$count = $this->problems->filter_count($filter);
			if ($count > 0 && ($count + $problems_per_page - 1) / $problems_per_page < $page)
				$page = ($count + $problems_per_page - 1) / $problems_per_page;
				
			$row_begin = ($page - 1) * $problems_per_page;
			$data = $this->problems->load_filter_problemset($filter, $row_begin, $problems_per_page);
	
			//$result = $this->problems->load_filter_problemset_status($uid, $filter);
			
		} else {
			$count = $this->problems->count();
			if ($count > 0 && ($count + $problems_per_page - 1) / $problems_per_page < $page)
				$page = ($count + $problems_per_page - 1) / $problems_per_page;
				
			$row_begin = ($page - 1) * $problems_per_page;
			$data = $this->problems->load_problemset($row_begin, $problems_per_page);
			
			/*if (count($data) != 0) {
				$start = $data[0]->pid;
				$end = $data[count($data) - 1]->pid;
				$result = $this->problems->load_problemset_status($uid, $start, $end);
			}*/
		}
		
		$pids='';
		foreach ($data as $row)
			$pids.="$row->pid,";
		$pids=rtrim($pids,',');
		$pids="($pids)";
		
		$status_result=$this->problems->load_status($uid,$pids);
		$bookmark_result=$this->problems->load_bookmark($uid,$pids);
		
		if ( ! isset($filter)) $filter = false;

		if (isset($status_result)) {
			foreach ($status_result as $row)
				$status["$row->pid"] = $row->status;
		}

		if (isset($bookmark_result)) {
			foreach ($bookmark_result as $row)
				$bookmark["$row->pid"] = (object) array('starred'=>$row->starred, 'note'=>$row->note);
		}

		$categorization = $this->misc->load_categorization();
		foreach ($data as $row){
			if ($row->submitCount > 0) $row->average = $row->average / $row->submitCount;
			
			$row->category = $this->misc->load_problem_category($row->pid, $categorization);
			$row->average = number_format($row->average, 2);
			$row->status = '';
			$row->ac = FALSE;
			
			if (isset($status["$row->pid"])){
				$row->status = self::_convert_status($status["$row->pid"]);
				if ($status["$row->pid"] == 0) $row->ac = TRUE;
			}

			if (isset($bookmark["$row->pid"]))
				$row->bookmark=$bookmark["$row->pid"];
		}

		$this->load->library('pagination');
		$config['base_url'] = '#main/problemset/';
		$config['total_rows'] = $count;
		$config['per_page'] = $problems_per_page;
		$config['cur_page'] = $page;
		if ($keyword) $config['suffix'] = '?search=' . $this->input->get('search');
		if ($filter) $config['suffix'] = '?filter=' . $filter;
		$this->pagination->initialize($config);

		$this->load->view('main/problemset',
						array('data' => $data,
							'category' => $categorization,
							'keyword' => $keyword,
							'filter' => $filter));
	}

	public function show($pid){
		$this->load->model('problems');
		$this->load->model('misc');
		
		$data = $this->problems->load_problem($pid);
		if ($data != FALSE && ($data->isShowed || $this->user->is_admin())) {
			$data->data = json_decode($data->dataConfiguration);
			
			if ($data->data->IOMode != 2) {
				$data->timeLimit = $data->memoryLimit = 0;
				
				if (isset($data->data->cases)) {
					foreach ($data->data->cases as $case) {
						foreach ($case->tests as $test){
						
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
					
				}
				
				if ($data->timeLimit <= 0){
					unset($data->timeLimit);
					unset($data->memoryLimit);
				}
			}
			
			$categorization = $this->misc->load_categorization();
			$data->category = $this->misc->load_problem_category($pid, $categorization);
			$data->solutions = $this->problems->load_solutions($pid);
		} else $data = FALSE;
	
		if ($data == FALSE)
			$this->load->view('error', array('message' => 'Problem not available!'));
		else
			$this->load->view('main/show', array('data' => $data, 'category' => $categorization));
	}
	
	public function download($pid, $filename = 'data.zip', $dir = '') {
		$filename = rawurldecode($filename);
		$file = $this->config->item('data_path') . $pid . "/$dir/$filename";
		
		if (! strstr($this->session->userdata('download'), $filename))
			$this->load->view('error', array('message' => 'You are not allowed to download this file!'));
		else if ( ! is_file($file))
			$this->load->view('error', array('message' => 'File Not Found!'));
		else if ($dir != 'submission')
			$this->load->view('main/download', array('file' => $file, 'filename' => $filename));
		else {
			$filename = str_replace('compressed', '', $filename);
			$type = system("type.sh $file");
			$filename .= $type;
			
			$this->load->view('main/download', array('file' => $file, 'filename' => $filename));
		}
	}

	public function datadownload($pid) {
		$this->load->library('form_validation');

		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('pid', 'Problem ID', 'required');
		$this->form_validation->set_rules('authcode', 'Authentication Code', 'required');

		
	}
	
	public function limits($pid){
		$this->load->model('problems');
		
		$data = $this->problems->load_limits($pid);
		if ($data != FALSE){
			$data->data = json_decode($data->dataConfiguration);
			
			if ($data->data->IOMode != 2) {
				$data->timeLimit = $data->memoryLimit = 0;
				foreach ($data->data->cases as $case){
					foreach ($case->tests as $test){
					
						if ($data->timeLimit == 0){
							$data->timeLimit = $test->timeLimit;
							$data->memoryLimit = $test->memoryLimit;
							
						} elseif ($data->timeLimit != $test->timeLimit || $data->memoryLimit != $test->memoryLimit)
							$data->timeLimit = -1;
							
						if ($data->timeLimit < 0) break;
					}
					if ($data->timeLimit < 0) break;
				}
			
				if ($data->timeLimit < 0){
					unset($data->timeLimit);
					unset($data->memoryLimit);
				}
			}
		}
	
		if ($data == FALSE)
			$this->load->view('error', array('message' => 'Problem not available!'));
		else
			$this->load->view('main/limits', array('data' => $data));
	}
	
	public function addtag($pid){
		$id = $this->input->get('tag', TRUE);
		if (!$id) return;
		
		$this->load->model('misc');
		if ($this->misc->is_accepted($this->user->uid(), $pid) || $this->user->is_admin())
			$this->misc->add_categorization($pid, $id);
	}
	
	public function deltag($pid, $id){
		$this->load->model('misc');
		if ($this->misc->is_accepted($this->user->uid(), $pid) || $this->user->is_admin())
			$this->misc->delete_categorization($pid, $id);		
	}

	public function submit($pid = 0, $cid = 0, $gid = 0, $tid = 0){
		$this->load->library('form_validation');
		$this->load->helper('cookie');
		
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('pid', 'Problem ID', 'required|callback_submit_check');
		
		if ($this->form_validation->run() == FALSE){
			$this->load->model('problems');
			$data = array(
				'pid' => $pid,
				'language' => $this->input->cookie('language'),
				'code' => '',
				'IOMode'=> $this->problems->load_IO_mode($pid)
			);
			if ($cid > 0) {
				$data['cid'] = $cid;
				$this->load->model('contests');
				$data['submitTime'] = $this->contests->load_contest_status($cid)->submitTime;
			}
			if ($tid > 0) $data['tid'] = $tid;
			if ($gid > 0) $data['gid'] = $gid;
			$this->load->view('main/submit', $data);
			
		} else {
			$language = $this->input->post('language');
			$this->input->set_cookie(array('name' => 'language', 'value' => $language, 'expire' => '10000000'));
			$uid = $this->session->userdata('uid');
			$this->user->save_language($uid, $language);

			$data = array(
				'uid'	=>	$uid,
				'name'	=>	$this->session->userdata('username'),
				'pid'	=>	$this->input->post('pid', TRUE),
				'code'	=>	$this->input->post('texteditor'),
				'codeLength'	=>	strlen($this->input->post('texteditor')),
				'language'	=>	$language,
				'submitTime'	=>	date("Y-m-d H:i:s")
			);
			$data['code'] = html_entity_decode($data['code']);
			
			if ($this->input->post('cid') != '') $data['cid'] = $this->input->post('cid');
			if ($this->input->post('gid') != '') $data['gid'] = $this->input->post('gid');
			if ($this->input->post('tid') != '') $data['tid'] = $this->input->post('tid');			
			
			if (isset($data['tid'])){
				$this->load->model('misc');
				$info = $this->misc->load_task_info($data['gid'], $data['tid']);
				if (strtotime($info->startTime) > time() || strtotime($info->endTime) < time()) return;
				//unset($data['gid']);
				$languages = explode(',', $info->language);
				if ( ! in_array($language, $languages)) return;
			}
			
			else if (isset($data['cid'])){
				$this->load->model('contests');
				$info = $this->contests->load_contest_status($data['cid']);
				if (max(strtotime($info->startTime), strtotime($info->submitTime)) > time() || strtotime($info->endTime) < time()) return;		
				$languages = explode(',', $info->language);
				if ( ! in_array($language, $languages)) return;
			}			
			
			else {
				$this->load->model('problems');
				$showed = $this->problems->is_showed($data['pid']);
				if ($showed == 0){
					if ($this->user->is_admin()) $data['isShowed'] = 0;
					else return;
				}
			}

			$this->load->model('submission');
			$this->submission->save_submission($data);
			$this->user->submit();
			
			$this->load->view('success');
		}
	}
	
	public function upload($pid = 0, $cid = 0, $gid = 0, $tid = 0){
		$this->load->library('form_validation');
		$this->load->helper('cookie');
		
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('pid', 'Problem ID', 'required');
		
		if ($this->form_validation->run() == FALSE){
			$data = array(
				'pid' => $pid,
				'language' => $this->input->cookie('language'),
				'code' => ''
			);
			if ($cid > 0) $data['cid'] = $cid;
			if ($tid > 0) $data['tid'] = $tid;
			if ($gid > 0) $data['gid'] = $gid;
			$this->load->view('main/upload', $data);
			
		} else {
			$uid = $this->session->userdata('uid');

			$data = array(
				'uid'	=>	$uid,
				'name'	=>	$this->session->userdata('username'),
				'pid'	=>	$this->input->post('pid', TRUE),
				'code'	=>	'output file',
				'codeLength'	=>	0,
				'submitTime'	=>	date("Y-m-d H:i:s")
			);
			if ($this->input->post('cid') != '') $data['cid'] = $this->input->post('cid');
			if ($this->input->post('gid') != '') $data['gid'] = $this->input->post('gid');
			if ($this->input->post('tid') != '') $data['tid'] = $this->input->post('tid');
			
			$this->load->model('problems');
			$dataconf = json_decode($this->problems->load_dataconf($data['pid'])->dataConfiguration);
			if ($dataconf->IOMode != 2) {
				$this->load->view('error', array('message' => 'Uploading for this problem is not allowed!'));
				return;
			}
			
			if (isset($data['tid'])){
				$this->load->model('misc');
				$info = $this->misc->load_task_info($data['gid'], $data['tid']);
				if (strtotime($info->startTime) > time() || strtotime($info->endTime) < time()) return;
			} 
			
			else if (isset($data['cid'])){
				$this->load->model('contests');
				$info = $this->contests->load_contest_status($data['cid']);
				if (strtotime($info->startTime) > time() || strtotime($info->endTime) < time()) return;
			}
			
			else {
				$showed = $this->problems->is_showed($data['pid']);
				if ($showed == 0){
					if ($this->user->is_admin()) $data['isShowed'] = 0;
					else return;
				}
			}
			
			if ( !isset($_FILES['file'])) return;
			
			$this->load->model('submission');
			$sid = $this->submission->save_submission($data);
			//$this->user->submit();
			
			$temp_file = $_FILES['file']['tmp_name'];
			$target_path = $this->config->item('data_path') . $pid . '/submission';
			if (! is_dir($target_path)) mkdir($target_path);
			$target_file = $target_path . "/$sid.compressed";
			if ( ! is_executable($temp_file))
				move_uploaded_file($temp_file, $target_file);
			
			$this->load->view('success');
		}
	}

	public function statistic($pid, $page = 1){
		$this->load->model('problems');
		if ( ! $this->problems->is_showed($pid) && ! $this->user->is_admin()) {
			$this->load->view('error', array('message' => 'You have NO priviledge to see the statistic.'));
			return;
		}
	
		$users_per_page = 20;
		
		$this->load->model('submission');
		$row_begin = ($page - 1) * $users_per_page;
		$count = $this->submission->statistic_count($pid);
		$data = $this->submission->load_statistic($pid, $row_begin, $users_per_page);
		$this->submission->format_data($data);
		
		$this->load->library('pagination');
		$config['base_url'] = "#main/statistic/$pid/";
		$config['total_rows'] = $count;
		$config['per_page'] = $users_per_page;
		$config['cur_page'] = $page;
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);
		
		$this->load->view('main/statistic', array('data' => $data, 'pid' => $pid));
	}

	public function status($page = 1){
		$submission_per_page = (int)$this->session->userdata('submission_per_page');
		if ( ! $submission_per_page) $submission_per_page = 20;
		
		$filter = (array)$this->input->get(NULL, TRUE);
		
		$this->load->model('submission');
		$row_begin = ($page - 1) * $submission_per_page;
		$count = $this->submission->count($filter);
		$data = $this->submission->load_status($row_begin, $submission_per_page, $filter);
		$this->submission->format_data($data);
		
		$this->load->library('pagination');
		$config['base_url'] = '#main/status/';
		$config['total_rows'] = $count;
		$config['per_page'] = $submission_per_page;
		$config['first_link'] = 'Top';
		$config['last_link'] = FALSE;
		$this->pagination->initialize($config);

		$url = uri_string() . ($_SERVER['QUERY_STRING'] == '' ? '' : ('?' . $_SERVER['QUERY_STRING']));
		$filter = array_merge(array('status' => array(), 'languages' => array(), 'url' => $url), $filter);
		$this->load->view('main/status', array('data'	=>	$data, 'filter' => $filter));
	}
	
	public function submission_change_access($sid){
		$this->load->model('submission');
		
		if ($this->session->userdata('priviledge') == 'admin' ||
			$this->session->userdata('uid') == $this->submission->load_uid($sid))
			$this->submission->change_access($sid);
	}

	public function code($sid){
		$this->load->model('submission');
		$data = $this->submission->load_code($sid);
		
		if ($data == FALSE)
			$this->load->view('error', array('message' => 'You have NO priviledge to see this code.'));
		else
			$this->load->view('main/code', $data);
	}
	
	public function result($sid){
		$this->load->model('submission');
		$this->load->model('problems');
		
		$data = $this->submission->load_result($sid);

		if ($data == FALSE){
			$this->load->view('error', array('message' => 'You have NO priviledge to see this result.'));
		} else {
			$dataconf = json_decode($this->problems->load_dataconf($data->pid)->dataConfiguration);
			$result = json_decode($data->result);
			
			if ($result->compileStatus) {
				foreach ($result->cases as $row => $value)
					$this->submission->format_data($value->tests);
			}
			
			$this->load->view('main/result', array('result' => $result, 'pid' => $data->pid, 'dataconf' => $dataconf));
		}
	}
	
	public function ranklist($page = 1){
		$users_per_page = 20;
		
		$this->load->model('user');
		$this->load->model('misc');
		
		$count = $this->user->count();
		if ($count > 0 && ($count + $users_per_page - 1) / $users_per_page < $page)
			$page = ($count + $users_per_page - 1) / $users_per_page;
			
		$row_begin = ($page - 1) * $users_per_page;
		$data = $this->misc->load_ranklist($row_begin, $users_per_page);
		$rank = $row_begin;
		
		foreach ($data as $row){
			$row->rank = ++$rank;
			$row->rate = 0.00;
			if ($row->submitCount > 0) $row->rate = $row->solvedCount / $row->submitCount * 100;
			$row->rate = number_format($row->rate, 2);
		}
		
		$this->load->library('pagination');
		$config['base_url'] = '#main/ranklist/';
		$config['total_rows'] = $count;
		$config['per_page'] = $users_per_page;
		$this->pagination->initialize($config);

		$this->load->view('main/ranklist', array('data' => $data));
	}
	
	function addsolution($pid) {
		$this->load->model('misc');
		$this->load->model('problems');
		
		$is_accepted = $this->misc->is_accepted($this->session->userdata('uid'), $pid);
		//if ( ! $is_accepted && ! $this->user->is_admin()) return;
		
		if ( !isset($_FILES['solution'])) return;
		
		$temp_file = $_FILES['solution']['tmp_name'];
		$target_path = $this->config->item('data_path') . $pid . '/solution/';
		if (! is_dir($target_path)) mkdir($target_path);
		$target_file = $target_path . $_FILES['solution']['name'];
		
		if (file_exists($target_file)) return;
		
		if (! is_executable($temp_file)) {
			move_uploaded_file($temp_file, $target_file);
			$this->problems->add_solution($pid, $_FILES['solution']['name']);
			
			$this->load->view('success');
		}
	}
	
	function deletesolution($idSolution) {
		$this->load->model('problems');
		
		if ($this->user->is_admin() || $this->problems->load_solution_uid($idSolution) == $this->user->uid()) {
			$this->problems->delete_solution($idSolution);
		}
	}

	function upd_bookmark($pid)
	{
		$this->load->model('problems');
		$this->problems->update_bookmark($pid);
	}
}

// End of file main.php
