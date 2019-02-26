<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	function username_check($username){
		return $this->user->username_check($username);
	}

	function password_check($password){
		$saltl = $this->session->userdata('saltl');
		$saltr = $this->session->userdata('saltr');
		$this->session->unset_userdata('saltl');
		$this->session->unset_userdata('saltr');

		$lenfull = strlen($password);
		$lenl = strlen($saltl);
		$lenr = strlen($saltr);

		if (strpos($password, $saltl) !== 0 || strrpos($password, $saltr) + $lenr !== $lenfull) return false;
		$password = substr($password, $lenl, $lenfull - $lenl - $lenr);

		$password = md5(md5($password) . $this->config->item('password_suffix'));
		return $this->user->login_check($this->input->post('username', TRUE), $password);
	}
	
	public function login(){
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters('<span class="add-on alert alert-error">', '</span>');
			
		$this->form_validation->set_rules('username', 'Username', 'required|callback_username_check');
		$this->form_validation->set_rules('password', 'Password', 'required|callback_password_check');
			
		$this->form_validation->set_message('required', "%s is required");
		$this->form_validation->set_message('username_check', 'User Error!');
		$this->form_validation->set_message('password_check', 'Password Error!');

		if ($this->form_validation->run() == FALSE){
			$this->load->helper('string');
			$saltl = random_string('alnum', rand(5, 10));
			$saltr = random_string('alnum', rand(5, 10));
			$this->session->set_userdata('saltl', $saltl);
			$this->session->set_userdata('saltr', $saltr);

			$this->load->view('login', array('saltl' => $saltl, 'saltr' => $saltr));
		}else{
			$this->user->login_success($this->input->post(NULL, TRUE));
			
			$this->load->view('success');
		}
	}

}

// End of file: MY_Controller.php
