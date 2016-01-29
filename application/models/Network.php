<?php

class NetWork extends CI_Model
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

}
