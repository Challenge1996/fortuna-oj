<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Misc extends CI_Controller {

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
	}

	function mailbox($page = 1) {
		$mails_per_page = 20;

		$row_begin = ($page - 1) * $mails_per_page;
		$count = $this->user->mail_count($this->user->uid());
		$mails = $this->user->load_mail_list($this->user->uid(), $row_begin, $mails_per_page);

		$this->load->view('misc/mailbox', array('mails' => $mails));
	}

	function mail($user) {
		$uid = $this->user->load_uid($user);
		$mails = $this->user->load_mail($uid);
		$this->user->set_mail_read($this->user->uid());

		$this->load->view('misc/mail', array('mails' => $mails, 'user' => $user));
	}

	function newmail($username = '') {
		$this->load->library('form_validation');

		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('to_user', 'Send To', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('misc/newmail', array('username' => $username));
		} else {
			$data = $this->input->post(NULL, TRUE);
			$data['from_uid'] = $this->user->uid();
			$data['from_user'] = $this->session->userdata('username');
			$data['to_uid'] = $this->user->load_uid($data['to_user']);
			$data['sendTime'] = date("Y-m-d H:i:s");
			$this->user->save_mail($data);

			$this->load->view('success');
		}
	}

	public function testdata($pid) {
		if ( ! $this->user->permission('testdata') && ! $this->user->is_admin()) {
			$this->load->view('error', array('message' => 'You do not have permission to download!'));
		} else {
			$path = $this->config->item('data_path');
			$command = "zip /tmp/$pid.zip $path/$pid/* -9 -j -D > /dev/null 2>&1";
			system($command);

			$this->load->view('main/download', array('file' => "/tmp/$pid.zip", 'filename' => "$pid.zip"));

			$command = "rm /tmp/$pid.zip > /dev/null 2>&1";
			system($command);
		}
	}

}
