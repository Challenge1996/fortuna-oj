<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Session extends CI_Session
{
	private function try_session_start()
	{
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	}

	public function &get_userdata()
	{
		self::try_session_start();
		return parent::get_userdata();
	}

	public function userdata($key = NULL)
	{
		self::try_session_start();
		return parent::userdata($key);
	}

	public function set_userdata($data, $value = NULL)
	{
		self::try_session_start();
		parent::set_userdata($data, $value);
	}

	public function unset_userdata($key)
	{
		self::try_session_start();
		parent::unset_userdata($key);
	}

	public function all_userdata()
	{
		self::try_session_start();
		return parent::all_userdata();
	}

	public function has_userdata($key)
	{
		self::try_session_start();
		return parent::has_userdata($key);
	}
}

