<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		
		$allowed_methods = array('addproblem', 'problemset');
		$restricted_methods = array('delete_problem', 'dataconf', 'scan', 'upload', 'change_problem_status');
		
		if ($this->user->is_logged_in()){
			if ($this->user->is_admin() || in_array($method, $allowed_methods)) $this->_redirect_page($method, $params);
			else if (in_array($method, $restricted_methods)){
				$this->load->model('problems');
				if (isset($params[0]) && $this->problems->uid($params[0]) == $this->user->uid())
					$this->_redirect_page($method, $params);
				else
					$this->load->view('error', array('message' => '<h5 class="alert">Operation not permitted!</h5>'));
			}else
				$this->load->view('error', array('message' => '<h5 class="alert">You are not administrators!</h5>'));
		}else
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
	
	public function index(){
		$this->load->view('admin/index');
	}
	
	public function addproblem($pid = 0){
		$this->load->model('problems');
		if ($pid > 0 && ! $this->user->is_admin() && $this->problems->uid($pid) != $this->user->uid()) {
			$this->load->view('error', array('message' => 'You are not allowed to edit this problem!'));
			return;
		}

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('problemDescription', 'Problem Description', 'required');
		$this->form_validation->set_rules('inputDescription', 'Input Description', 'required');
		$this->form_validation->set_rules('outputDescription', 'Output Description', 'required');
		$this->form_validation->set_rules('inputSample', 'Sample Input', 'required');
		$this->form_validation->set_rules('outputSample', 'Sample Output', 'required');
		//$this->form_validation->set_rules('dataConstraint', 'Data Constraint', 'required');
		
		if ($this->form_validation->run() == FALSE){
			if ($pid > 0){
				$data = (array)$this->db->query("SELECT * FROM ProblemSet WHERE pid=?", array($pid))->row();
				$data['pid'] = $pid;

				$path = $this->config->item('problem_path') . $pid;
				$data['problemDescription']   = file_get_contents("$path/problemDescription.html");
				$data['inputDescription']     = file_get_contents("$path/inputDescription.html");
				$data['outputDescription']    = file_get_contents("$path/outputDescription.html");
				$data['inputSample']          = file_get_contents("$path/inputSample.html");
				$data['outputSample']         = file_get_contents("$path/outputSample.html");
				$data['dataConstraint']       = file_get_contents("$path/dataConstraint.html");
				$data['hint']                 = file_get_contents("$path/hint.html");

			}else $data = NULL;

			$this->load->view("admin/addproblem", $data);
		}else{
			$data = $this->input->post(NULL);
			//$data['isShowed'] = 0;
			if ($pid == 0){
				$new = TRUE;
				$pid = $this->problems->add($data);
				$this->problems->save_dataconf($pid, '{IOMode:0, cases:[]}', null, null);
			}else{
				$new = FALSE;
				$this->problems->add($data, $pid);
			}
			
			$target_path = $this->config->item('data_path') . '/' . $pid . '/';
			if (! is_dir($target_path)) mkdir($target_path);
			
			if ($new) $this->load->view('information', array('data' => 'success' . $pid));
			else $this->load->view('success');
		}
	}
	
	function delete_problem($pid){
		$this->load->model('problems');
		$this->problems->delete($pid);
	}
	
	public function change_problem_status($pid){
		$this->load->model('problems');
		$this->problems->change_status($pid);
	}

	public function change_problem_nosubmit($pid){
		$this->load->model('problems');
		$this->problems->change_nosubmit($pid);
	}
	
	public function problemset($page = 1){
		$problems_per_page = 20;
		$uid = FALSE;
		if ( ! $this->user->is_admin()) $uid = $this->user->uid();
	
		$this->load->model('problems');
		$count = $this->problems->count($uid, TRUE);
		if ($count > 0 && ($count + $problems_per_page - 1) / $problems_per_page < $page)
			$page = ($count + $problems_per_page - 1) / $problems_per_page;
		$row_begin = ($page - 1) * $problems_per_page;
		$data = $this->problems->load_problemset($row_begin, $problems_per_page, TRUE, $uid, TRUE);
		foreach ($data as $row)
		{
			$row->isShowed=($row->isShowed?'<span class="label label-success">Showed</span>':'<span class="label label-important">Hidden</span>');
			$row->noSubmit=($row->noSubmit?'<span class="label label-important">Disallowing</span>':'<span class="label label-success">Allowing</span>');
		}

		$this->load->library('pagination');
		$config['base_url'] = '#admin/problemset/';
		$config['total_rows'] = $count;
		$config['per_page'] = $problems_per_page;
		$config['cur_page'] = $page;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);

		$this->load->view('admin/problemset', array('data' => $data, 'page' => $page));
	}

	public function dataconf($pid){
		$this->load->library('form_validation');
		$this->load->model('problems');
		$this->form_validation->set_error_delimiters('<div class="alert">', '</div>');
		
		$this->form_validation->set_rules('script-init', 'Initialization Part', 'required');
		$this->form_validation->set_rules('script-run', 'Running Part', 'required');
		$this->form_validation->set_rules('group', 'Manual Setting of Data Grouping', 'required');
		
		$datapath = $this->config->item('data_path').$pid;
		
		$post = $this->input->post(NULL, FALSE);
		try
		{
			if ($this->form_validation->run() == FALSE) throw new MyException();
			$confCache = $this->problems->save_script($pid, $post["script-init"], $post["script-run"]);
			$this->problems->mark_update($pid);
			$this->problems->save_dataconf($pid, $post["traditional"], $post["group"], $confCache);
			
			$this->load->view('success');

		} catch (MyException $e)
		{
			$data = $this->problems->load_dataconf($pid);
			if (!$data)
			{
				$this->load->view('error',array('message'=>'No such a problem'));
				return;
			}
			$pass = array();
			$pass['title'] = $data->title;
			$pass['pid'] = $pid;
			$pass['traditional'] = isset($post['traditional'])?$post['traditional']:$data->dataConfiguration;
			$pass['group'] = isset($post['group'])?$post['group']:$data->dataGroup;
			$pass['init'] = isset($post['script-init'])?$post['script-init']:file_exists($datapath.'/init.src')?file_get_contents($datapath.'/init.src'):'';
			$pass['run'] = isset($post['script-run'])?$post['script-run']:file_exists($datapath.'/run.src')?file_get_contents($datapath.'/run.src'):'';
			$pass['errmsg'] = $e->getMessage();

			$this->load->view('admin/dataconf', $pass);
		}
	}

	public function upload($pid){
		if ( !isset($_FILES['files'])) return;
		$count = count($_FILES['files']['tmp_name']);

		$target_path = $this->config->item('data_path') . $pid . '/';
		if (! is_dir($target_path)) mkdir($target_path,0777,true);
		//$cwd = getcwd();
		//chdir($target_path);
		//$makefile = "SPJ :";
		
		for ($i = 0; $i < $count; $i++) {
			$temp_file = $_FILES['files']['tmp_name'][$i];
			$target_file = $target_path . $_FILES['files']['name'][$i];
			//$file_types = array('c', 'cpp', 'pas', 'dpr');
			//$file_parts = pathinfo($_FILES['files']['name'][$i]);
			//$basename = $file_parts['basename'];
			//$filename = $file_parts['filename'];
			if (isset($file_parts['extension'])) $extension = $file_parts['extension'];
			else $extension = '';
		
		//	if (in_array($file_parts['extension'],$file_types))
			//if ( ! is_executable($temp_file))
			move_uploaded_file($temp_file, $target_file);
			
			//if (in_array($extension, $file_types)){
				/*chdir($target_path);
				if ($extension == 'c')
					exec("gcc $basename -o $filename");
				if ($extension == 'cpp')
					exec("g++ $basename -o $filename");
				if ($extension == 'pas' || $extension == 'dpr')
					exec("fpc $basename -o$filename");*/
				//$makefile .= " $filename";
			//}
			
			if (in_array($extension, array('tar', 'tar.gz', 'zip', 'rar', '7z', 'bz2', 'gz')))
				exec("extract.sh $basename");
		}
		//$handle = fopen("spj.makefile","w");
		//fwrite($handle, $makefile);
		//fclose($handle);
		//chdir($cwd);
		$this->load->model('problems');
		$this->problems->mark_update($pid);
	}
		
	public function wipedata($pid){
		$target_path = $this->config->item('data_path') . $pid . '/';
		echo $target_path;
		exec("rm -f $target_path*");
	}

	public function scan($pid){
		$this->load->model('problems');
		$target_path = $this->config->item('data_path') . '/' . $pid . '/';
		chdir($target_path);
		$dir = (scandir('.'));
		natsort($dir);
		$data = json_decode($this->problems->load_dataconf($pid)->dataConfiguration,true);
		$hash = array();
		$hash['input'] = $hash['output'] = array();
		
		$input_pattern = $this->input->post('input_file');
		$output_pattern = $this->input->post('output_file');
		
		if (isset($data['cases'])) {
			foreach ($data['cases'] as $cid => &$case) {
				foreach ($case->tests as $tid => $test){
					if (file_exists($test->input) && file_exists($test->output)) {
						$hash['input'][$test->input] = true;
						$hash['output'][$test->output] = true;
					} else {
						$case->tests = array_diff_key($case->tests,array($tid=>null)); // do not use unset.
					}
				}
				if (count($data['cases'][$cid]->tests) == 0)
					$data['cases'] = array_diff_key($data['cases'],array($cid=>null)); // do not use unset.
			}
		}
		
		$name_array = array();
		if ($input_pattern == '' || $output_pattern == '') {
			foreach ($dir as $file){
				if (is_file($file)){
					$info = pathinfo('./' . $file);
					$infile = $info['basename'];
					if (strpos($infile, '.in')===false) continue;
					$outfile1 = str_ireplace('.in', '.out', $infile);
					$outfile2 = str_ireplace('.in', '.ans', $infile);
					$outfile3 = str_ireplace('.in', '.ou', $infile);
					$outfile4 = str_ireplace('.in', '.sol', $infile);
					$outfile5 = str_ireplace('.in', '.std', $infile);
					$outfile = '';
					if (file_exists($outfile = $outfile1) || file_exists($outfile = $outfile2) ||
						file_exists($outfile = $outfile3) || file_exists($outfile = $outfile4) ||
						file_exists($outfile = $outfile5)) {
							if (array_key_exists($infile, $hash['input']) && array_key_exists($outfile, $hash['output'])) continue;
							$name_array[] = $infile;
						}
				}
			}
			
			usort($name_array, "strnatcmp");

			foreach ($name_array as $infile){
					$outfile1 = str_ireplace('.in', '.out', $infile);
					$outfile2 = str_ireplace('.in', '.ans', $infile);
					$outfile3 = str_ireplace('.in', '.ou', $infile);
					$outfile4 = str_ireplace('.in', '.sol', $infile);
					$outfile5 = str_ireplace('.in', '.std', $infile);
					$outfile = '';
					
					if (file_exists($outfile = $outfile1) || file_exists($outfile = $outfile2) ||
						file_exists($outfile = $outfile3) || file_exists($outfile = $outfile4) ||
						file_exists($outfile = $outfile5)){
						if (isset($test)) unset($test);
						if (isset($case)) unset($case);
						$test['input'] = $infile;
						$test['output'] = $outfile;
						$case['tests'][] = $test;
						$data['cases'][] = $case;
					}
			}
		} else {
			$input_pattern = '/' . str_replace('*', "(?P<var>\w+)", $input_pattern) . '/';

			foreach ($dir as $file) {
				if (preg_match($input_pattern, $file, $matches)) {
					$infile = $matches[0];
					$outfile = str_replace("*", $matches['var'], $output_pattern);
					
					if (file_exists($outfile)) {
						if (array_key_exists($infile, $hash['input']) && array_key_exists($outfile, $hash['output'])) continue;
						$name_array[] = $infile;
					}
				}
			}
			
			usort($name_array, "strnatcmp");

			foreach ($name_array as $infile){
					preg_match($input_pattern, $infile, $matches);
					$outfile = str_replace("*", $matches['var'], $output_pattern);
					
					if (file_exists($outfile)) {
						if (isset($test)) unset($test);
						if (isset($case)) unset($case);
						$test['input'] = $infile;
						$test['output'] = $outfile;
						$case['tests'][] = $test;
						$data['cases'][] = $case;
					}
			}
		}
		
		$num = count($data['cases']);
		foreach ($data['cases'] as &$case)
		{
			if (!isset($cases['score'])) $case['score']=100/$num;
			foreach ($case['tests'] as &$test)
			{
				if (!isset($test['timeLimit'])) $test['timeLimit']=1000;
				if (!isset($test['memoryLimit'])) $test['memoryLimit']=262144;
			}
		}

		if (!isset($data["IOMode"])) $data["IOMode"]=0;
		
		echo json_encode($data);
	}
	
	public function contestlist($page = 1){
		$contests_per_page = 20;
	
		$this->load->model('contests');
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
		$config['base_url'] = '#admin/contestlist/';
		$config['total_rows'] = $count;
		$config['per_page'] = $contests_per_page;
		$config['cur_page'] = $page;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);

		$this->load->view('admin/contestlist', array('data' => $data));
	}

	public function newcontest($cid = 0){
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		
		$this->form_validation->set_rules('contest_title', 'Title', 'required');
		$this->form_validation->set_rules('start_date', 'Start Date', 'required');
		$this->form_validation->set_rules('start_time', 'Start Time', 'required');
		$this->form_validation->set_rules('submit_date', 'Submit Date', 'required');
		$this->form_validation->set_rules('submit_time', 'Submit Time', 'required');
		$this->form_validation->set_rules('end_date', 'End Date', 'required');
		$this->form_validation->set_rules('end_time', 'End Time', 'required');
		$this->form_validation->set_rules('teamMode', 'Team Mode', 'required');
		$this->form_validation->set_rules('contestMode', 'Contest Mode', 'required');
		$this->form_validation->set_rules('contestType', 'Contest Type', 'required');
		$this->form_validation->set_rules('ex_start_date', 'Start Date', 'required');
		$this->form_validation->set_rules('ex_start_time', 'Start Time', 'required');
		$this->form_validation->set_rules('ex_submit_date', 'Submit Date', 'required');
		$this->form_validation->set_rules('ex_submit_time', 'Submit Time', 'required');
		$this->form_validation->set_rules('ex_end_date', 'End Date', 'required');
		$this->form_validation->set_rules('ex_end_time', 'End Time', 'required');
		
		$this->load->model('contests');
		if ($this->form_validation->run() == FALSE){
			if ($cid > 0) $data = $this->contests->load_contest_configuration($cid);
			else $data = NULL;

			$this->load->view('admin/newcontest', $data);
		}else{
			$data = $this->input->post(NULL, TRUE);
			if (!isset($data['isTemplate'])) $data['isTemplate'] = '0';
			$data['isShowed'] = 1;

			if ($cid == 0) $this->contests->add($data);
			else $this->contests->add($data, $cid);
			
			$this->load->view('success');
		}
	}
	
	function delete_contest($cid){
		$this->load->model('contests');
		$this->contests->delete($cid);
	}
	
	function users(){
		$this->load->model('misc');
		$data = $this->user->load_users_list();
		$groups = $this->misc->load_groups($this->session->userdata('uid'));
		foreach ($data as $row){
			$row->groups = $this->user->load_user_groups($row->uid, $groups);
		}
		$this->load->view('admin/users', array('data' => $data));
	}
	
	function change_user_status($uid){
		$this->user->change_status($uid);
	}

	function change_user_priviledge($uid, $priviledge)
	{
		$this->user->change_priviledge($uid, $priviledge);
	}
	
	function delete_user($uid){
		$this->user->delete($uid);
	}

	function setallowing($uid){
		$this->load->model('misc');
		$add = $this->input->get('add');
		$del = $this->input->get('del');
		if (isset($add) && $add)
			$this->misc->add_allowing($uid, $add);
		if (isset($del) && $del)
			$this->misc->del_allowing($del);
		$data = $this->misc->load_allowing($uid);
		$this->load->view('admin/setallowing', array('data' => $data, 'uid' => $uid));
	}

	function setallowings(){
		$this->load->model('misc');
		$this->load->model('user');
		$names = $this->input->post('users');
		$probs = $this->input->post('probs');
		if (!$names) $names = array();
		if (!$probs) $probs = array();
		$alter_user = $this->input->post('alter_user');
		$alter_prob = $this->input->post('alter_prob');
		$all = $this->input->post('all');
		$users = array();
		foreach ($names as $id => $name)
		{
			$uid = $this->user->load_uid($name);
			if ($uid === false || $this->user->load_priviledge($uid)!='restricted')
				unset($names[$id]);
			else
				$users[] = $uid;
		}
		if ($alter_user) $alter_user = $this->user->load_uid($alter_user);
		if ($alter_user && $alter_prob)
			$this->misc->alter_allowing($alter_user, $alter_prob);
		$data = $this->misc->load_allowings($users, $probs);
		foreach ($names as $id => $user)
			foreach ($probs as $prob)
			{
				if ($all == 'add' && !isset($data[$user][$prob]))
					$this->misc->add_allowing($users[$id],$prob);
				if ($all == 'del' && isset($data[$user][$prob]))
					$this->misc->del_allowing($data[$user][$prob]);
			}
		if ($all == 'add' || $all == 'del')
			$data = $this->misc->load_allowings($users, $probs);
		$this->load->view('admin/setallowings', array(
			'data' => $data,
			'users' => $names,
			'probs' => $probs
		));
	}

	function new_task($tid = 0){
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		
		$this->form_validation->set_rules('task_title', 'Title', 'required');
		$this->form_validation->set_rules('description', 'Description', '');
		
		$this->load->model('misc');
		if ($this->form_validation->run() == FALSE){
			if ($tid > 0) $data = $this->misc->load_task($tid);
			else $data = NULL;

			$this->load->view('task/new_task', $data);
		}else{
			$data = $this->input->post(NULL, TRUE);
			$this->misc->add_task($data, $tid);
			
			$this->load->view('success');
		}
	}
	
	function delete_task($tid) {
		$this->load->model('misc');
		$this->misc->delete_task($tid);
	}
	
	function task_list($page = 1){
		$tasks_per_page = 20;
		
		$this->load->model('misc');
		
		$begin = ($page - 1) * $tasks_per_page;
		$count = $this->misc->count_tasks();
		$tasks = $this->misc->load_task_list($begin, $tasks_per_page);
		
		$this->load->library('pagination');
		$config['base_url'] = '#admin/task_list/';
		$config['total_rows'] = $count;
		$config['per_page'] = $tasks_per_page;
		$config['cur_page'] = $page;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);
		
		$this->load->view('admin/task_list', array('tasks' => $tasks));
	}

	function change_submission_status($sid){
		$this->load->model('submission');
		$this->submission->change_status($sid);
	}
	
	function rejudge(){
		$this->load->library('form_validation');
		$this->load->model('problems');
		$this->load->model('submission');
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		
		$this->form_validation->set_rules('type', 'Type', 'required');
		$this->form_validation->set_rules('id', 'ID', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->load->view('admin/rejudge');
		else{
			$data = $this->input->post(NULL, TRUE);
			if ($data['type'] == 'submission'){
				$this->submission->rejudge($data['id']);
			}else{
				$data = $this->problems->load_problem_submission($data['id']);
				foreach ($data as $row)
					$this->submission->rejudge($row->sid);
			}
			$this->load->view('success');
		}
	}

	function always_true() {
		return TRUE;
	}
	
	function statistic() {
		$this->load->library('form_validation');
		$this->load->model('problems');
		$this->load->model('contests');
		$this->load->model('submission');
		
		$this->form_validation->set_rules('user', '', 'callback_always_true');
		
		if ($this->form_validation->run() == FALSE)
			$this->load->view('admin/statistic');
		else{
			$data = $this->input->post(NULL, TRUE);
			
			$uids = $pids = '';
	
			if (isset($data['problem']) && $data['problem'] != '') {
				$pid_array = explode(',', $data['problem']);
				foreach ($pid_array as $pid)
					$pids .= "$pid,";
			}
			
			if (isset($data['contest']) && $data['contest'] != '') {
				$cid_array = explode(',', $data['contest']);
				foreach ($cid_array as $cid) {
					$result = $this->contests->load_contest_problemset($cid);
					foreach ($result as $row)
						$pids .= "$row->pid,";
				}
			}
			
			if (isset($data['task']) && $data['task'] != '') {
				$tid_array = explode(',', $data['task']);
				foreach ($tid_array as $tid) {
					$result = $this->contests->load_task_problems($tid);
					foreach ($result as $row)
						$pids .= "$row->pid,";
				}
			}
			
			$pids = rtrim($pids, ',');
			
			if (isset($data['user']) && $data['user'] != '') {
				$name_array = explode(',', $data['user']);
				foreach ($name_array as $name) {
					$uid = $this->user->load_uid($name);
					$uids .= "$uid,";
				}
			}
			
			if ($uids == '') $uids = FALSE;
			else $uids = rtrim($uids, ',');
			
			if (isset($data['group']) && $data['group'] != '') {
				
			}
			
			$data = $this->contests->load_statistic_OI($pids, $uids);
			$this->load->view('admin/standing', array('data' => $data, 'pids' => $pids));
		}
	}
	
	function contest_to_task($cid) {
		$this->load->model('contests');
		
		$this->contests->contest_to_task($cid);
	}

	function functions_check() {
		$data = $this->input->post(NULL);
		if ($data['name'] != '' && $data['date'] != '' && $data['time'] != '') return TRUE;
		if ($data['reset_pwd_username'] != '' && $data['reset_password'] != '') return TRUE;
		return FALSE;
	}

	function functions() {
		$this->load->library('form_validation');

		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('name', 'Username', 'callback_functions_check');
		//$this->form_validation->set_rules('date', 'Date', 'required');
		//$this->form_validation->set_rules('time', 'Time', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('admin/functions');
		} else {
			$data = $this->input->post(NULL);
			if ($data['name'] != '' && $data['date'] != '' && $data['time'] != '') { 
				$permission = $this->input->post('permission');
				$time = $this->input->post('date') . ' ' . $this->input->post('time');
				$time = strtotime($time);

				$per = array();
				if ($permission != FALSE) {
					foreach ($permission as $row)
						$per[$row] = $time;
				}
				$this->user->set_permission($per, $this->user->load_uid($this->input->post('name')));
			}
			
			if ($data['reset_pwd_username'] != '' && $data['reset_password'] != '') { 
				$uid = $this->user->load_uid($data['reset_pwd_username']);
				$this->user->save_password($uid, md5(md5($data['reset_password']) . $this->config->item('password_suffix')));
			}

			$this->load->view('success');
		}
	}





	// temp

	/*public function rejudgeall()
	{
		ignore_user_abort(true);
		set_time_limit(0);
		$this->load->model('submission');
		for ($i=1000; $i<=86511; $i++)
		{
			if (!$this->db->query("SELECT COUNT(*) AS cnt FROM Submission WHERE sid=$i")->row()->cnt) continue;
			$this->submission->rejudge($i);
		}
	}

	public function rejudgeerror()
	{
		ignore_user_abort(true);
		set_time_limit(0);
		$this->load->model('submission');
		for ($i=1000; $i<=86511; $i++)
		{
			if (!$this->db->query("SELECT COUNT(*) AS cnt FROM Submission WHERE sid=$i && status=9")->row()->cnt) continue;
			$this->submission->rejudge($i);
		}
	}*/
}

// End of file admin.php
