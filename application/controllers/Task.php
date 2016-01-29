<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Task extends CI_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method)){
			call_user_func_array(array($this, $method), $params);
		}else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		$this->load->model('misc');
		
		if ($this->user->is_logged_in())
			$this->_redirect_page($method, $params);
		else
			$this->login();
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

	public function task_list(){
		$uid = $this->user->uid();
		
		$groups = $this->misc->load_groups($uid);
		$data = $this->user->load_user_groups($uid, $groups);
		$submissions = $this->misc->load_tasks_submissions($uid);
		foreach ($data as $group){
			$group->tasks = $this->misc->load_group_tasks($group->gid);
			foreach ($group->tasks as $task)
				foreach ($task->problems as $problem)
					if (isset($submissions[$task->tid][$problem->pid])) $problem->status = $submissions[$task->tid][$problem->pid];
		}
		
		$this->load->view('task/task_list', array('data' => $data));
	}
	
	public function show($pid, $gid, $tid){
		$this->load->model('problems');
		$this->load->model('misc');

		if (! $this->misc->is_in_group($this->user->uid(), $gid)) {
			$this->load->view('error', array('message' => 'You are not in this group!'));
			return;
		}
		
		$data = null;
		try {	
			$data = $this->problems->load_problem($pid);
		} catch (MyException $e) {
			$this->load->view('error', array('message'=>$e->getMessage()));
			return;
		}	
		if ($data != FALSE) {
			
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
		$data->tid = $tid;
		$data->gid = $gid;

		$endTime = $this->misc->load_task_info($gid, $tid)->endTime;
		$data->timeout = strtotime($endTime) < time();

		if ($data == FALSE)
			$this->load->view('error', array('message' => 'Problem not available!'));
		else
			$this->load->view('task/show', array(
				'data' => $data,
				'noSubmit' => $this->problems->no_submit($pid)
			));
	}

	function statistic($gid, $tid) {
		$data = $this->misc->load_task_statistic($gid, $tid);
		$problems = $this->misc->load_task_problems($tid);
		$info = $this->misc->load_task_info($gid, $tid);
		
		$this->load->view('task/statistic', array('data' => $data, 'info' => $info, 'problems' => $problems));
	}
}
