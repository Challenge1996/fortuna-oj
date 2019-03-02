<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Session_redis_driver extends CI_Session_redis_driver
{

	/**
	 * Class constructor (with UNIX socket support)
	 *
	 * @param	array	$params	Configuration parameters
	 * @return	void
	 */
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

	/**
	 * Open (with UNIX socket support)
	 *
	 * Sanitizes save_path and initializes connection.
	 *
	 * @param	string	$save_path	Server path
	 * @param	string	$name		Session cookie name, unused
	 * @return	bool
	 */
	public function open($save_path, $name)
	{
		if (empty($this->_config['save_path']))
		{
			return $this->_fail();
		}

		$redis = new Redis();
		if ($this->_config['save_path']['type'] == 'unix')
		{
			if ( ! $redis->connect($this->_config['save_path']['path'], NULL, $this->_config['save_path']['timeout']))
			{
				log_message('error', 'Session: Unable to connect to Redis with the configured settings.');
			}
			else
			{
				$this->_redis = $redis;
				return $this->_success;
			}
		}
		elseif ( ! $redis->connect($this->_config['save_path']['host'], $this->_config['save_path']['port'], $this->_config['save_path']['timeout']))
		{
			log_message('error', 'Session: Unable to connect to Redis with the configured settings.');
		}
		elseif (isset($this->_config['save_path']['password']) && ! $redis->auth($this->_config['save_path']['password']))
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
			return $this->_success;
		}

		$this->php5_validate_id();

		return $this->_fail();
	}

	/**
	 * Get lock (patched for lock race condition)
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param	string	$session_id	Session ID
	 * @return	bool
	 */
	protected function _get_lock($session_id)
	{
		// PHP 7 reuses the SessionHandler object on regeneration,
		// so we need to check here if the lock key is for the
		// correct session ID.
		if ($this->_lock_key === $this->_key_prefix.$session_id.':lock')
		{
			return $this->_redis->setTimeout($this->_lock_key, 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->_key_prefix.$session_id.':lock';
		$attempt = 0;
		do
		{
			if (($ttl = $this->_redis->ttl($lock_key)) > 0)
			{
				sleep(1);
				continue;
			}

			// Patch begin
            $result = $this->_redis->set($lock_key, time(), array('nx', 'ex' => 300));
            if (! $result)
            {
                usleep(100000);
                continue;
			}
			// Patch end

			$this->_lock_key = $lock_key;
			break;
		}
		while (++$attempt < 30);

		if ($attempt === 30)
		{
			log_message('error', 'Session: Unable to obtain lock for '.$this->_key_prefix.$session_id.' after 30 attempts, aborting.');
			return FALSE;
		}
		elseif ($ttl === -1)
		{
			log_message('debug', 'Session: Lock for '.$this->_key_prefix.$session_id.' had no TTL, overriding.');
		}

		$this->_lock = TRUE;
		return TRUE;
	}
}
