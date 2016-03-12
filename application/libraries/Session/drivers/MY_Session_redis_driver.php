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

	public function __construct(&$params)
	{
		CI_Session_driver::__construct($params);

		if (empty($this->_config['save_path']))
		{
			log_message('error', 'Session: No Redis save path configured.');
		}
		elseif (preg_match('#^unix://([^\?]+)(\?.+)?$#', $this->_config['save_path'], $matches))
		{
			isset($matches[2]) OR $matches[2] = '';
			$this->_config['save_path'] = array(
					'type' => 'unix',
					'path' => $matches[1],
					'password' => preg_match('#auth=([^\s&]+)#', $matches[2], $match) ? $match[1] : NULL,
					'database' => preg_match('#database=(\d+)#', $matches[2], $match) ? (int) $match[1] : NULL,
					'timeout' => preg_match('#timeout=(\d+\.\d+)#', $matches[2], $match) ? (float) $match[1] : NULL
			);

			preg_match('#prefix=([^\s&]+)#', $matches[2], $match) && $this->_key_prefix = $match[1];
		}
		elseif (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->_config['save_path'], $matches))
		{
			isset($matches[3]) OR $matches[3] = '';
			$this->_config['save_path'] = array(
				'type' => 'tcp',
				'host' => $matches[1],
				'port' => empty($matches[2]) ? NULL : $matches[2],
				'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : NULL,
				'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : NULL,
				'timeout' => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : NULL
			);

			preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->_key_prefix = $match[1];
		}
		else
		{
			log_message('error', 'Session: Invalid Redis save path format: '.$this->_config['save_path']);
		}

		if ($this->_config['match_ip'] === TRUE)
		{
			$this->_key_prefix .= $_SERVER['REMOTE_ADDR'].':';
		}
	}

	public function open($save_path, $name)
	{
		if (empty($this->_config['save_path']))
		{
			return FALSE;
		}

		$connected = TRUE;
		$redis = new Redis();
		if ($this->_config['save_path']['type'] == 'unix')
		{
			if ( ! $redis->connect($this->_config['save_path']['path']))
			{
				log_message('error', 'Session: Unable to connect to Redis with the configured settings.');
				$connected = FALSE;
			}
		}
		elseif ( ! $redis->connect($this->_config['save_path']['host'], $this->_config['save_path']['port'], $this->_config['save_path']['timeout']))
		{
			log_message('error', 'Session: Unable to connect to Redis with the configured settings.');
			$connected = FALSE;
		}

		if ($connected) 
		{
			if (isset($this->_config['save_path']['password']) && ! $redis->auth($this->_config['save_path']['password']))
			{
				log_message('error', 'Session: Unable to authenticate to Redis instance.');
			}
			elseif (isset($this->_config['save_path']['database']) && ! $redis->select($this->_config['save_path']['database']))
			{
				log_message('error', 'Session: Unable to select Redis database with index '.$this->_config['save_path']['database']);
			}
			else
			{
				$this->_redis = $redis;
				return TRUE;
			}
		}

		return FALSE;
	}

}
