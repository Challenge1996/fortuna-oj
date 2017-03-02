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
				if ($method != 'index' && $method != 'result' && $method != 'fullresult' && $method != 'reply' && $method != 'start'){
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
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);

		$this->load->view('contest/index', array('data' => $data));
	}

	public function home($cid){
		$data = $this->contests->load_contest_status($cid);
		$uid = $this->user->uid();

		if ($data == FALSE) {
			$this->load->view("error", array('message' => 'Contest NOT exist!'));
		} else {
			$custom = $this->contests->load_template_contest_status($cid, $uid);
			$this->load->view("contest/home", array('data' => $data, 'uid' => $uid, 'custom' => $custom));
		}
	}
	
	public function problems($cid){
		if ($this->contests->is_template_contest($cid))
			$info = $this->contests->load_template_contest_status($cid, $this->user->uid());
		else
			$info = $this->contests->load_contest_status($cid);

		if ($info) {
			$data = $this->contests->load_contest_problemset($cid);
			foreach ($data as $row){
				$row->problem = $this->contests->load_contest_problem_name($row->pid);
				if ($row->title == '') $row->title = $row->problem->title;
				$row->statistic = $this->contests->load_contest_problem_statistic($cid, $row->pid);
			}
		}


		if ($info == FALSE) {
			if ($this->contests->is_template_contest($cid))
				$this->load->view("information", array('data' => 'Contest NOT start!'));
			else
				$this->load->view("error", array('message' => 'Contest NOT exist!'));
		} else {
			if (strtotime($info->startTime) > strtotime('now') && !$this->user->is_admin()) {
				$this->load->view("information", array('data' => 'Contest NOT start!'));
			} else {
				$this->load->view("contest/problems", array('data' => $data, 'info' => $info, 'cid' => $cid));
			}
		}
	}

	public function status($cid, $page = 1){
		$submission_per_page = 20;
		
		$this->load->model('submission');
		$this->load->model('user');

		if ($this->contests->is_template_contest($cid))
			$info = $this->contests->load_template_contest_status($cid, $this->user->uid());
		else
			$info = $this->contests->load_contest_status($cid);

		if ($info != FALSE){
			$row_begin = ($page - 1) * $submission_per_page;
			$count = $this->contests->load_contest_submission_count($cid);
			$data = $this->contests->load_contest_submission($cid, $row_begin, $submission_per_page,
												$info->running, $this->user->username(), $this->user->is_admin());
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
			$config['first_url'] = $config['base_url'] . '1';
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

		if ($this->contests->is_template_contest($cid))
			$info = $this->contests->load_template_contest_status($cid, $this->user->uid());
		else
			$info = $this->contests->load_contest_status($cid);

		if ($info != FALSE){
			$pid = $this->contests->load_contest_pid($cid, $id);
			if ($pid != FALSE){
				$data = null;
				try {	
					$data = $this->problems->load_problem($pid);
				} catch (MyException $e) {
					$this->load->view('error', array('message'=>$e->getMessage()));
					return;
				}	
				if ($data != FALSE){
					$data->filemode = json_decode($data->confCache);
					unset($data->confCache);

					$noTime = $noMemory = false;
					if (isset($data->filemode[4]))
						foreach ($data->filemode[4] as $executable => $conf)
						{
							if (!$noTime && isset($conf->time))
								foreach ($conf->time as $time)
									if (!isset($data->timeLimit) || $data->timeLimit == $time)
										$data->timeLimit = $time;
									else
									{
										unset($data->timeLimit);
										$noTime = true;
										break;
									}
							if (!$noMemory && isset($conf->memory))
								foreach ($conf->memory as $memory)
									if (!isset($data->memoryLimit) || $data->memoryLimit == $memory)
										$data->memoryLimit = $memory;
									else
									{
										unset($data->memoryLimit);
										$noMemory = true;
										break;
									}
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
	
	public function standing($cid) {
		if ($this->contests->is_template_contest($cid))
			$info = $this->contests->load_template_contest_status($cid, $this->user->uid());
		else
			$info = $this->contests->load_contest_status($cid);

		if ($info != FALSE) {
			if ($info->contestMode == 'ACM')
				$data = $this->contests->load_contest_ranklist_ACM($cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional')
				$data = $this->contests->load_contest_ranklist_OI($cid, $info);
		} else {
			$this->load->view("error", array('message' => 'Contest NOT start!'));
			return;
		}

		$est = $this->contests->load_estimate($cid);

		if  (strtotime($info->startTime) > strtotime('now') && !$this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else $this->load->view('contest/standing', array('data' => $data, 'info' => $info, 'est' => $est));
	}
	
	public function statistic($cid) {
		if ($this->contests->is_template_contest($cid))
			$info = $this->contests->load_template_contest_status($cid, $this->user->uid());
		else
			$info = $this->contests->load_contest_status($cid);

		if ($info != FALSE) {
			if ($info->contestMode == 'ACM')
				$data = $this->contests->load_contest_statistic_ACM($cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional')
				$data = $this->contests->load_contest_statistic_OI($cid, $info);
		} else {
			$this->load->view("error", array('message' => 'Contest NOT start!'));
			return;
		}

		if  (strtotime($info->startTime) > strtotime('now') && !$this->user->is_admin())
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

	private function merge($a, $b) {
		if ($a == False) return $b;

		$data = array();
		foreach ($a as $row) {
			if (property_exists($row, 'penalty'))
				$row->penalty = '-';
			if (property_exists($row, 'rank'))
				$row->rank = '-';
			$data[$row->name] = $row;
		}

		foreach ($b as $row) {
			if (array_key_exists($row->name, $data)) {
				$data[$row->name]->score += $row->score;
				$data[$row->name]->attempt = $data[$row->name]->attempt + $row->attempt;
				$data[$row->name]->acList = $data[$row->name]->acList + $row->acList;
			} else {
				if (property_exists($row, 'penalty'))
					$row->penalty = '-';
				if (property_exists($row, 'rank'))
					$row->rank = '-';
				$data[$row->name] = $row;
			}
		}

		return $data;
	}
	
	public function result(){
		$cids = func_get_args();
		$infos = array();
		foreach ($cids as $cid) {
			$info = $this->contests->load_contest_status($cid);
			if (count($infos) > 0 && $infos[0]->contestMode != $info->contestMode)
				$this->load->view("information", array('data' => 'Contest should be of the same mode!'));
			$infos[] = $info;
		}

		$info_all = (object) array(
						'startTime' => $infos[0]->startTime,
						'contestMode' => $infos[0]->contestMode,
						'problemset' => array());
		$data = False;
		foreach ($infos as $info) {
			if ($info->contestMode == 'ACM')
				$d = $this->contests->load_contest_ranklist_ACM($info->cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
				$d = $this->contests->load_contest_ranklist_OI($info->cid, $info);
			}
			$info_all->problemset = array_merge($info_all->problemset, $info->problemset);

			$data = $this->merge($data, $d);
		}
		$info = $info_all;
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else if ($data != FALSE) $this->load->view('contest/result', array('data' => $data, 'info' => $info));
	}
	
	public function fullresult(){
		$cids = func_get_args();
		$infos = array();
		foreach ($cids as $cid) {
			$info = $this->contests->load_contest_status($cid);
			if (count($infos) > 0 && $infos[0]->contestMode != $info->contestMode)
				$this->load->view("information", array('data' => 'Contest should be of the same mode!'));
			$infos[] = $info;
		}

		$info_all = (object) array(
						'startTime' => $infos[0]->startTime,
						'contestMode' => $infos[0]->contestMode,
						'problemset' => array());
		$data = False;
		foreach ($infos as $info) {
			if ($info->contestMode == 'ACM')
				$d = $this->contests->load_contest_statistic_ACM($info->cid);
			else if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
				$d = $this->contests->load_contest_statistic_OI($info->cid, $info);
			}
			$info_all->problemset = array_merge($info_all->problemset, $info->problemset);
			$data = $this->merge($data, $d);
		}
		$info = $info_all;
		
		if  (strtotime($info->startTime) > strtotime('now') && ! $this->user->is_admin())
			$this->load->view("information", array('data' => 'Contest NOT start!'));
		else if ($data != FALSE) $this->load->view('contest/result', array('data' => $data, 'info' => $info));
	}

	public function forum($cid)
	{
		$this->load->model('text');
		if (! $this->config->item('allow_forum'))
		{
			$this->load->view('error', array('message' => 'Forum has been shut off by administrator'));
			return;
		}
		$del = $this->input->get('del');
		$post = $this->input->get('post');
		$mdfy = $this->input->get('mdfy');
		$title = $this->text->bb2html($this->input->post('title'));
		$content = $this->text->bb2html($this->input->post('content'));
		if ($del)
			$this->contests->del_post($del);
		else if ($post)
		{
			if (!$content)
			{
				$this->load->view('error', array('message' => 'Cannot post an empty post'));
				return;
			}
			$this->contests->add_post($cid,$title,$content);
		} else if ($mdfy)
		{
			if (!$content)
			{
				$this->load->view('error', array('message' => 'Cannot post an empty post'));
				return;
			}
			$this->contests->modify_post($mdfy,$title,$content);
		}
		
		$data = $this->contests->load_forum($cid);
		$this->load->view('contest/forum', array('data' => $data));
	}

	public function reply($cid, $id, $to=-1)
	{
		$this->load->model('text');
		if ($to == -1) $to = $id;
		$del = $this->input->get('del');
		$post = $this->input->get('post');
		$mdfy = $this->input->get('mdfy');
		$content = $this->text->bb2html($this->input->post('content'));
		if ($del)
			$this->contests->del_post($del);
		else if ($post)
		{
			if (!$content)
			{
				$this->load->view('error', array('message' => 'Cannot post an empty post'));
				return;
			}
			$this->contests->add_post($cid,'',$content,$to);
		} else if ($mdfy)
		{
			if (!$content)
			{
				$this->load->view('error', array('message' => 'Cannot post an empty post'));
				return;
			}
			$this->contests->modify_post($mdfy,'',$content);
		}
		
		$data = $this->contests->load_reply($id);
		$this->load->view('contest/reply', array('data' => $data, 'id' => $id));
	}
	
	public function estimate($cid, $pid, $score = 0)
	{
		$this->contests->upd_estimate($cid, $pid, $score);
		$this->load->view('success');
	}

	public function start($cid)
	{
		if ($this->contests->start_contest($cid, $this->user->uid())) {
			$this->load->view('success');
			return;
		}
		$this->load->view('error', array('message' => "Can't start contest!"));
	}
}
