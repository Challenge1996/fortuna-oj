<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Session_redis_driver extends CI_Session_redis_driver
{
	public function close()
	{
		$ret = parent::close();
		$this->_lock_key = NULL;
		return $ret;
	}
}

