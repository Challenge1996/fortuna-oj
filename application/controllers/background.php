<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Background extends CI_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			call_user_func_array(array($this, $method), $params);
	}

	public function _remap($method, $params = array()){
		set_time_limit(0);
		$this->load->model('user');
		
		if ($this->user->is_logged_in()) $this->_redirect_page($method, $params);
		else sleep(10);
	}

	function push($force = 0) {
		$count = 0;
		while (true) {
			$data['c'] = $this->user->running_contest_count();
			$data['m'] = $this->user->unread_mail_count($this->user->uid());

			$res = json_encode($data);
			if ($force || $this->session->userdata('push') != $res) {
				$this->session->set_userdata('push', $res);
				$this->load->view('information', array('data' => $res));
				return;
			}

			if (++$count > 20) return;
			sleep(3);
		}
	}
}
