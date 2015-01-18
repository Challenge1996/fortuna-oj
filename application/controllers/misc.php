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
		
		$allowed_methods = array('reset_password', 'push_data', 'push_submission', 'serverstatus');
		if ($this->user->is_logged_in() || in_array($method, $allowed_methods))
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

	function reset_password()
	{
		$this->load->model('user');
		$this->load->helper('email');
		$name = $this->input->get('name', TRUE);
		if (!$name || !$this->user->username_check($name)) exit('No such user');
		$password = '';
		for ($i=0; $i<10; $i++) $password .= chr(mt_rand(97,122));
		$address = $this->user->load_email($name);
		if (!$address || !valid_email($address)) exit('You did not leave us a valid email address');

		$query = array
			(
				'to' => $this->user->load_email($name),
				'toname' => $name,
				'subject' => 'Reset Your JZOJ Password',
				'html' => "Your password for JZOJ account <i>$name</i> is now <i>$password</i>. Change your password after you log in.",
				'from' => $this->config->item('admin_email'),
				'fromname' => $this->config->item('admin_email_name'),
				'api_user' => $this->config->item('sendgrid_api_user'),
				'api_key' => $this->config->item('sendgrid_api_key')
			);
		$handle = curl_init("https://sendgrid.com/api/mail.send.json");
		if (!$handle) exit('Error (0)');
		if (!curl_setopt($handle, CURLOPT_POST, 1)) { curl_close($handle); exit('Error (1)'); }
		if (!curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($query))) { curl_close($handle); exit('Error (2)'); }
		if (!curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE)) { curl_close($handle); exit('Error (3)'); }
		$result = curl_exec($handle);
		curl_close($handle);
		$result = json_decode($result);
		if (!isset($result->message) || $result->message != "success") exit('Error (4)');

		
		$password = md5(md5($password) . $this->config->item('password_suffix'));
		$this->user->save_password($this->user->load_uid($name), $password);
		exit("OK. An Email is on the way to $address. It may take some time to process.");
	}
	
	public function testdata($pid) {
		if ( ! $this->user->permission('testdata') && ! $this->user->is_admin()) {
			$this->load->view('error', array('message' => 'You do not have permission to download!'));
		} else {
			$path = $this->config->item('data_path');
			$command = "zip /tmp/$pid.zip $path/$pid/* -9 -j -D > /dev/null 2>&1";
			system($command);

			$this->load->view('main/download', array('file' => "/tmp/$pid.zip", 'filename' => "$pid.zip", 'filetype' => 'application/zip'));

			$command = "rm /tmp/$pid.zip > /dev/null 2>&1";
			system($command);
		}
	}
	
	public function push_data($pid)
	{
		ignore_user_abort(true);
		set_time_limit(0);
		$this->load->model('problems');
		$this->load->model('network');

		$makefile = "SPJ : yauj_judge ";
		$path = $this->config->item('data_path') . $pid;
		$files = scandir($path);
		foreach ($files as $file)
		{
			if (! is_file("$path/$file")) continue;
			$file_parts = pathinfo($file);
			$extenssion = isset($file_parts['extension'])? $extension = $file_parts['extension']: '';
			$filename = $file_parts['filename'];
			if (in_array($extension, array('c','cpp','pas'))) $makefile .= " $filename";
		}
		file_put_contents("$path/makefile","$makefile\ninclude /home/judge/resource/makefile");
		
		$servers = $this->config->item('servers');
		foreach ($servers as $server)
		{
			$old = array();
			$new = $this->problems->load_pushed($pid);
			if (!isset($new['version'])) exit('no data');
			if (!isset($new[$server])) $new[$server] = 'unsynced';
			while (true)
			{
				$old = $new;
				if ($old[$server] == $old['version']) break;
				$result = $this->network->jsonrpc_call($server,'sync',array('pid'=>(int)$pid));
				if (!isset($result)) $result = 'connecton failed';
				echo "$server is $result";
				if ($result != 'success') break;
				$old[$server] = $old['version'];
				$new = $this->problems->load_pushed($pid);
				if (!isset($new['version'])) exit('no data');
				if (!isset($new[$server])) $new[$server] = 'unsynced';
				if ($new['version'] == $old['version'])
				{
					if ($new[$server] != $old[$server])
					{
						$new[$server] = $old[$server];
						$this->problems->save_pushed($pid,$new);
					}
					break;
				}
			}
		}
	}

	public function push_submission()
	{
		ignore_user_abort(true);
		set_time_limit(0);
		if ($this->input->post('passwd') != $this->config->item('local_passwd')) exit('passwd wrong');
		$get = $this->input->get();
		$server = $get['server'];
		$this->load->model('submission');
		$this->load->model('network');
		if ($this->submission->load_pushTime($get['sid'])!=$get['push_time'])
		{
			$this->network->jsonrpc_call($server,'cancel',array('key'=>(int)$get['key']));
			exit('canceled');
		}
		$this->submission->upd_status($get['sid'],-2);
		$get['submission'] = str_replace('c  ','c++',$get['submission']);

		$params = array(
			'pid' => (int)$get['pid'],
			'sid' => (int)$get['sid'],
			'key' => (int)$get['key'],
			'submission' => json_decode($get['submission'])
		);
		$ret = $this->network->jsonrpc_call($server,'run',$params);
		//var_dump($ret);

		$time = $memory = $codeLength = $language = $status = null;
		$score = array();
		$sum = 0;
		foreach (json_decode($get['submission']) as $file)
			if (!isset($language))
				$language = $file->language;
			else if ($language != $file->language)
				$language = 'multiple';
		// error should be handled in the detailed status page.
		if (isset($ret) && !isset($ret->error))
		{
			foreach ($ret as $id => &$result)
			{
				if (!isset($result->message)) $result->message = '';
				$result->message = $result->status . ' ' . $result->message;
				if (isset($result->score) && (!isset($score[$id]) || $result->score > $score[id]))
					$score[$id] = $result->score;
				if (isset($result->time))
					foreach ($result->time as &$t)
					{
						if (isset($t) && $t<0) unset($t);
						if (isset($t) && (!isset($time) || $t > $time)) $time = $t;
					}
				if (isset($result->memory))
					foreach ($result->memory as &$m)
					{
						if (isset($m) && $m<0) unset($m);
						if (isset($m) && (!isset($memory) || $m > $memory)) $memory = $m;
					}
				if (isset($result->codeLength))
					foreach ($result->codeLength as &$l)
					{
						if (isset($l) && $l<0) unset($l);
						if (isset($l) && (!isset($codeLength) || $l > $codeLength)) $codeLength = $l;
					}
				if (isset($result->status) && (!isset($status) || $this->submission->status_id($status)==0 && $this->submission->status_id($result->status)!=0))
					$status = $result->status;
			}
			foreach ($score as $case)
				$sum += $case;
		}
		if ($status === null) $status = 'internal error';
		$this->submission->judge_done($get['sid'],$get['pid'],array(
			'language' => ucfirst($language),
			'status' => $this->submission->status_id($status),
			'judgeResult' => json_encode($ret),
			'time' => $time,
			'memory' => $memory,
			'score' => $sum,
			'codeLength' => $codeLength,
			'pushTime' => $get['push_time']
		));
	}

	public function serverstatus($pid)
	{
		$this->load->model('problems');
		echo json_encode($this->problems->load_pushed($pid));
	}
}
