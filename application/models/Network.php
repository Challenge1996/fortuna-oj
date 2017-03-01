<?php

class Network extends CI_Model
{
	function __construct() { 
		parent::__construct();
	}

	function jsonrpc_call($server, $method, $params)
	{
		$handle = curl_init($server);
		$value = array(
			'id' => 0,
			'jsonrpc' => '2.0',
			'method' => $method,
			'params' => $params
		);
		$value = json_encode($value);
		//echo $value;
		curl_setopt($handle, CURLOPT_POSTFIELDS, $value);
		curl_setopt($handle, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($value)
		));
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($handle);
		//echo $result;
		if ($result === false) echo curl_error($handle);
		curl_close($handle);
		if ($result === false) return null;
		$result = utf8_encode($result);
		//$reslut = iconv(mb_detect_encoding($result,mb_detect_order(),true),"ASCII//IGNORE",$result);
		$result = json_decode($result);
		if (!isset($result) || !isset($result->result)) return null;
		return $result->result;
	}

	private function sendgrid_sender($to, $toname, $subject, $html, $from, $fromname) // return 0 on success, message otherwise
	{
		$query = array
			(
				'to' => $to,
				'toname' => $toname,
				'subject' => $subject,
				'html' => $html,
				'from' => $from,
				'fromname' => $fromname,
				'api_user' => $this->config->item('sendgrid_api_user'),
				'api_key' => $this->config->item('sendgrid_api_key')
			);
		$handle = curl_init("https://sendgrid.com/api/mail.send.json");
		if (!$handle) return 'Error (0)';
		if (!curl_setopt($handle, CURLOPT_POST, 1)) { curl_close($handle); return 'Error (1)'; }
		if (!curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($query))) { curl_close($handle); return 'Error (2)'; }
		if (!curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE)) { curl_close($handle); return 'Error (3)'; }
		$result = curl_exec($handle);
		curl_close($handle);
		$result = json_decode($result);
		if (!isset($result->message) || $result->message != "success") return 'Error (4)';
		return 0;
	}

	private function smtp_sender($to, $toname, $subject, $html, $from, $fromname) // return 0 on success, message otherwise
	{
		$this->load->library('email');
		$config = array();
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = $this->config->item('smtp_host');
		$config['smtp_user'] = $this->config->item('smtp_user');
		$config['smtp_pass'] = $this->config->item('smtp_password');
		$config['smtp_crypto'] = 'tls';
		$config['mailtype'] = 'html';
		$config['crlf'] = "\r\n";
		$config['newline'] = "\r\n";
		$this->email->initialize($config);
		$this->email->to($to);
		$this->email->from($from, $fromname);
		$this->email->subject($subject);
		$this->email->message($html);
		if ($this->email->send(false))
			return 0;
		else
			return $this->email->print_debugger();
	}

	function send_mail($to, $toname, $subject, $html)
	{
		$from = $this->config->item('admin_email');
		$fromname = $this->config->item('admin_email_name');
		switch ($this->config->item('mail_method'))
		{
		case 'sendgrid':
			return self::sendgrid_sender($to, $toname, $subject, $html, $from, $fromname);
		case 'smtp':
			return self::smtp_sender($to, $toname, $subject, $html, $from, $fromname);
		default:
			return 'Mail function not enabled';
		}
	}
}

