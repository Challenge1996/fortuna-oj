<?php

// For Paysapi

class Payment extends CI_Model{

	function __construct(){
		parent::__construct();
	}

	function get_item($itemid){
		return $this->db->get_where('PayItem', array('itemid' => $itemid))->row();
	}

	function get_items_list(){
		return $this->db->get('PayItem')->result();
	}

	function set_item($itemid, $itemDescription, $price, $type, $timeInt){
		$item = array(
			'itemDescription' => $itemDescription,
			'price' => $price,
			'type' => $type,
			'timeInt' => $timeInt
		);
		if (isset($itemid) && $itemid > 0)
			$this->db->where('itemid', $itemid)->update('PayItem', $item);
		else
			$this->db->insert('PayItem', $item);
	}

	function delete_item($itemid){
		$this->db->where('itemid', $itemid)->delete('PayItem');
	}

	function get_expiration($type, $timeInt){
		$now = time();
		switch ($type){
			case 0:
				$expiration = $now + $timeInt;
				break;
			case 1:
				$expiration = $timeInt;
		}
		return $expiration;
	}
	
	function new_order($uid, $name, $item, $method){
		$expiration = $this->get_expiration($item->type, $item->timeInt);
		
		$last_orderid = $this->db->order_by('orderid', 'DESC')
								->select('orderid')
								->get_where('Orders', array('uid' => $uid, 'status' => 2), 1, 0)
								->row();
		if (isset($last_orderid))
			return -2;	// Last order is reviewing

		$last_orderid = $this->db->order_by('orderid', 'DESC')
								->select('orderid')
								->get_where('Orders', array('uid' => $uid, 'status' => 0), 1, 0)
								->row();
		if (isset($last_orderid)){
			$last_orderid = $last_orderid->orderid;
			$order = array(
				'itemDescription' => $item->itemDescription,
				'expiration' => date('Y-m-d H:i:s', $expiration),
				'price' => $item->price,
				'method' => $method
			);
			$this->db->where('orderid', $last_orderid)->update('Orders', $order);
			return $last_orderid;
		}
		
		$orderid = $this->db->order_by('id', 'DESC')
							->select('orderid')
							->limit(1)
							->get('Orders')
							->row();

		if (isset($orderid) && substr($orderid->orderid, 0, 8) == date('Ymd')){
			$nextid = (string)(intval(substr($orderid->orderid, 8)) + 1);
			for ($i = strlen($nextid); $i < 4; ++$i) $nextid = '0'.$nextid;
			$orderid = date('Ymd').$nextid;
		}
		else
			$orderid = date('Ymd').'0001';
		// $orderid = '1f65er15g6s1';	// Debug only
		$order = array(
			'orderid' => $orderid,
			'uid' => $uid,
			'name' => $name,
			'itemDescription' => $item->itemDescription,
			'expiration' => date('Y-m-d H:i:s', $expiration),
			'price' => $item->price,
			'method' => $method,
			'createTime' => date('Y-m-d H:i:s')
		);
		$this->db->insert('Orders', $order);
		return $orderid;
	}

	function finish_order($payid, $orderid, $realprice){
		$order = array(
			'payid' => $payid,
			'realprice' => $realprice,
			'finishTime' => date('Y-m-d H:i:s'),
			'status' => ($this->config->item('payment_auto_finish') && $realprice > 0) ? 1 : 2
		);
		$this->db->where('orderid', $orderid)->update('Orders', $order);
	}

	function review_order($orderid, $expiration){
		$order = array(
			'expiration' => $expiration,
			'status' => 1
		);
		$this->db->where('orderid', $orderid)->update('Orders', $order);
	}

	function reject_order($orderid){
		$order = array(
			'status' => -1
		);
		$this->db->where('orderid', $orderid)->update('Orders', $order);
	}

	function get_order($orderid){
		return $this->db->where('orderid', $orderid)->get('Orders')->row();
	}

	function get_orders_list(){
		return $this->db->get('Orders')->result();
	}

}

// End of file: Payment.php

