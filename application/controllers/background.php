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
		$uid = $this->user->uid();
		$username = $this->user->username();

		$redis = new Redis();
		$redis->connect($this->config->item('redis_host'), $this->config->item('redis_port'));
		$redis->setOption(Redis::OPT_PREFIX, 'online_users:'.$this->config->item('oj_name').':'); 
		$redis->set($uid, $username, array('ex'=>180));
		$redis->close();

		$count = 0;
		while (true) {
			$data['c'] = $this->user->running_contest_count();
			$data['m'] = $this->user->unread_mail_count($uid);

			$res = json_encode($data);
			if ($force || $this->session->userdata('push') != $res) {
				$this->session->set_userdata('push', $res);
				$this->load->view('information', array('data' => $res));
				return;
			}

			if (++$count > 10) return;
			sleep(6);
		}
	}
}
