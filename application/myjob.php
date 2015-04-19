<?php

class myjob
{
	public function jsonrpc($server, $method, $params)
	{
		// this is from model/network/jsonrpc_call
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
		if ($result === false) syslog(LOG_WARNING, "jsonrpc failed: ".curl_error($handle));
		curl_close($handle);
		if ($result === false) return null;
		$result = json_decode($result);
		if (!isset($result) || !isset($result->result)) return null;
		return $result->result;
	}
	
	public function local($page, $timeoutms = 0)
	{
		$oj_name = $this->args['oj_name'];
		$ch = curl_init("http://127.0.0.1/$oj_name/index.php/$page");
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('passwd' => $this->args['passwd']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if ($timeoutms > 0) curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeoutms);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
	
	public function perform()
	{
		set_time_limit(0);
		$pid = $this->args['pid'];
		$sid = $this->args['sid'];
		$lang = $this->args['lang'];
		$servers = $this->args['servers'];
		$pushTime = urlencode($this->args['pushTime']);
		while (true)
		{
			//echo "attempt\n";
			$serverstatus = json_decode($this->local("misc/serverstatus/$pid"), true);
			$version = $serverstatus['version'];
			unset($serverstatus['version']);
			foreach ($servers as $server)
			{
				$status = 'unsynced';
				if (isset($serverstatus[$server])) $status = $serverstatus[$server];
				if ($status != $version)
					$this->local("misc/push_data/$pid", 2500);
				else
				{
					//echo "hit\n";
					$key = $this->jsonrpc($server, 'preserve', array('sid' => $sid));
					if ($key === null || $key == -1) continue;
					$ser = urlencode($server);
					//echo "preserved $key\n";
					$msg = trim($this->local("misc/push_submission/?pid=$pid&sid=$sid&key=$key&submission=$lang&server=$ser&push_time=$pushTime", 2500));
					if ($msg) syslog(LOG_INFO, "msg = $msg");
					return;
				}
			}
			usleep(100000);
		}
	}
}
