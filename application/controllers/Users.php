<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends MY_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		$params[0] = rawurldecode($params[0]);
		$user = $this->user->load_user($params[0]);
		if (! isset($user)){
			$this->load->view('error', array('message' => 'User does not exist'));
			return;
		}
		$user->name = $params[0];
		
		$params[0] = $user;
		$allowed_method = array('index', 'statistic');
		if ($this->user->is_logged_in() && ($this->user->uid() == $user->uid || in_array($method, $allowed_method)))
			$this->_redirect_page($method, $params);
		else
			$this->login();
	}

	public function index($user) {
		if ($user->submitCount == 0) $user->rate = 0;
		else $user->rate = number_format($user->solvedCount / $user->submitCount * 100, 2);
		
		$user->rank = $this->user->load_rank($user->uid);
		$user->count = $this->user->load_statistic($user->uid);
		$user->userPicture = $this->user->load_userPicture($user->uid);
		$user->blogURL = $this->user->load_blog_url($user->uid);
		
		$this->load->view('user/index', array('data' => $user));
	}
	
	public function settings($user){
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="alert alert-error">', '</span>');
			
		$this->form_validation->set_rules('old_password', 'Old Password', 'callback_password_check');
		$this->form_validation->set_rules('show_category', 'Show Category', '');
		$this->form_validation->set_rules('email', 'Email', 'valid_email');
		$this->form_validation->set_rules('blog_url', 'Blog URL', 'max_length[256]|prep_url');
		$this->form_validation->set_rules('problems_per_page', 'Problems', 'required');
		$this->form_validation->set_rules('submission_per_page', 'Submission', 'required');
		
		$this->form_validation->set_message('password_check', 'Wrong Old Password!');
		
		if ($this->form_validation->run() == FALSE){
			$config = $this->user->load_configuration($this->user->uid());
			
			$this->load->view('user/settings', array('user' => $user, 'config' => $config));
		}else{
			$raw = $this->input->post(NULL, TRUE);
			
			if (isset($raw['show_category'])) $config['showCategory'] = 1;
			else $config['showCategory'] = 0;
			
			if (isset($raw['email'])) $config['email'] = $raw['email'];
			if (isset($raw['school'])) $config['School'] = $raw['school'];
			if (isset($raw['blog_url'])) $config['blogURL']=$raw['blog_url'];
			if (isset($raw['description'])) $config['description'] = htmlspecialchars(mb_substr($raw['description'], 0, 50));
			
			$config['problemsPerPage'] = min(100, max(1, (int)$raw['problems_per_page']));
			$config['submissionPerPage'] = min(100, max(1, (int)$raw['submission_per_page']));
			
			$this->user->save_configuration($this->user->uid(), $config);
			
			if (isset($raw['old_password']) && isset($raw['new_password']) && $raw['old_password'] != '' && $raw['new_password'] != '') {
				$this->user->save_password($this->user->uid(),
										md5(md5($raw['new_password']) . $this->config->item('password_suffix')));
			}

			$this->load->view('success');
		}
	}
	
	function statistic($user) {
		$this->load->model('contests');
		$this->load->model('misc');
		$this->load->model('submission');
		
		$categorization = $this->misc->load_categorization();
		
		$statistic = new stdClass();
		$statistic->verdict = $this->user->load_statistic($user->uid);
		$statistic->categories = $this->user->load_categories_statistic($user->uid);
		$statistic->accepted = $this->user->load_accepted($user->uid);
		$statistic->unaccepted = $this->user->load_unaccepted($user->uid);
		$statistic->accepted_in_contests = $this->contests->load_problems_in_contests($statistic->accepted);
		$statistic->unaccepted_in_contests = $this->contests->load_problems_in_contests($statistic->unaccepted);
		
		$this->load->view('user/statistic', array('categorization' => $categorization, 'statistic' => $statistic));
	}
	
	function avatar_upload($user) {
		if ( !isset($_FILES['avatar'])) return;
		$temp_file = $_FILES['avatar']['tmp_name'];
		$target_path = 'images/avatar/';
		//if (! is_dir($target_path)) mkdir($target_path);
		
		if (stristr($_FILES['avatar']['type'], 'image') === false) return;
		
		$file_parts = pathinfo($this->security->sanitize_filename($_FILES['avatar']['name']));
		$extension = $file_parts['extension'];
		$target_file = $target_path . $user->uid . '.' . $extension;
		move_uploaded_file($temp_file, $target_file);
		
		$this->user->save_user_picture($user->uid, $user->uid . '.' . $extension);
		
		$this->load->model('images');
		$this->user->save_avatar($user->uid, $this->images->genThumbnail($target_file));
	}

} 
