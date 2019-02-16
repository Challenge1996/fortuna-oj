<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group extends MY_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method)){
			call_user_func_array(array($this, $method), $params);
		}else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		
		if ($this->user->is_logged_in())
			$this->_redirect_page($method, $params);
		else
			$this->login();
	}
	
	function group_list(){
		$this->load->model('misc');
		$this->load->model('user');
		$groups = $this->misc->load_groups($this->user->uid());
		$this->load->view('group/group_list', array('groups' => $groups));
	}
	
	function group_view($gid = 0){
		$this->load->model('misc');
		$this->load->model('user');
		$groups = $this->misc->load_groups($this->user->uid());
		
		$grouping = $groups[$gid];
		$grouping->members = $this->misc->load_grouping($gid);
		$grouping->tasks = $this->misc->load_group_tasks($gid);
		$this->load->view('group/group_view', array('grouping' => $grouping));
	}
	
	function group_setting($gid = 0){
		$this->load->model('misc');
		$this->load->model('user');
		if (($gid > 0 && ! $this->misc->is_group_admin($gid)) || ($gid == 0 && ! $this->user->is_admin())) return;
		$groups = $this->misc->load_groups($this->user->uid());
		
		if ($gid > 0){
			$grouping = $groups[$gid];
			$grouping->members = $this->misc->load_grouping($gid);
			$grouping->tasks = $this->misc->load_group_tasks($gid);
			$this->load->view('group/group_setting', array('grouping' => $grouping));
		} else {
			$this->load->view('group/group_setting');
		}
	}
	
	function save_group_settings($gid){
		$this->load->model('misc');
		$post = $this->input->post(NULL, TRUE);
		if ($gid == 0){
			if ($this->user->is_admin()){
				$code = $this->misc->get_random_code(16);
				$post['invitationCode'] = (string)$code;
				$gid = $this->misc->save_group_settings($post);
			} else $gid = '';
		}else{
			if ($this->misc->is_group_admin($gid))
				$gid = $this->misc->save_group_settings($post, $gid);
		}
		$this->load->view('information', array('data' => $gid));
	}
	
	function delete_group($gid){
		$this->load->model('misc');
		if ($this->user->is_admin())
			$this->misc->delete_group($gid);
	}
	
	function group_join($gid){
		$this->load->model('misc');
		$this->load->model('user');
		$this->misc->group_join($gid, $this->user->uid());
	}
	
	function group_apply($code){
		$this->load->model('misc');
		$this->load->model('user');
		$gid = $this->misc->search_group_by_code($code);
		if ($gid != FALSE){
			$this->misc->group_join($gid, $this->user->uid());
			$this->load->view('success');
		}
	}
	
	function group_member_accept($gid, $uid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid))
			$this->misc->group_member_accept($gid, $uid);
	}
	
	function group_member_decline($gid, $uid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid))
			$this->misc->group_member_decline($gid, $uid);
	}
	
	function group_member_delete($gid, $uid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid))
			$this->misc->group_member_delete($gid, $uid);
	}
	
	function add_task($gid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid)){
			$all_tasks = $this->misc->load_all_task();
			$current = $this->misc->load_group_tasks($gid, FALSE);
			$tasks = $list = array();
			
			foreach ($current as $row) $list[$row->tid] = TRUE;
			foreach ($all_tasks as $task)
				if ( ! isset($list[$task->tid])) $tasks[] = $task;
			
			$this->load->view('group/modal_add_tasks', array('tasks' => $tasks));
		}
	}
	
	function task_config($gid, $tid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid)){
			$data = $this->misc->load_group_task_configuration($gid, $tid);
			
			$this->load->view('group/modal_task_config', $data);
		}
	}
	
	function group_task_configuration($gid, $tid){
		$this->load->model('misc');
		if ($this->misc->is_group_admin($gid))
			$this->misc->save_group_task_configuration($gid, $tid, $this->input->post(NULL, TRUE));
	}
	
	function group_add_tasks($gid){
		$this->load->model('misc');
		if ( ! $this->misc->is_group_admin($gid)) return;
		
		$data = $this->input->post(NULL, TRUE);
		$tasks = array();
		foreach ($data['tid'] as $tid){
			$task = new stdClass();
			$task->tid = $tid;
			$task->startTime = $data['start_date'][$tid] . ' ' . $data['start_time'][$tid];
			$task->endTime = $data['end_date'][$tid] . ' ' . $data['end_time'][$tid];
			$tasks[] = $task;
			unset($task);
		}

		$this->misc->group_add_tasks($gid, $tasks);
	}
	
	function group_delete_task($gid, $tid){
		$this->load->model('misc');
		if ( ! $this->misc->is_group_admin($gid)) return;
		
		$this->misc->group_delete_task($gid, $tid);
	}
}
