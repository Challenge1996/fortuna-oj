<?php

class User extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function is_logged_in(){
		if ($this->session->userdata('uid') != FALSE)
		{
			if (!self::uid_check($this->session->userdata('uid'))) return FALSE;
			return TRUE;
		}
		if ($this->input->cookie('identifier') != FALSE){
			$identifier = $this->input->cookie('identifier', TRUE);
			if (($username = self::identifier_check($identifier)) != FALSE){
				if (!self::username_check($username)) return FALSE;
				self::login_success(array('username' => $username, 'remember' => 1));
				return TRUE;
			}
		}
		return FALSE;
	}

	function uid() {
		return $this->session->userdata('uid');
	}

	function username() {
		return $this->session->userdata('username');
	}

	function load_uid($name) {
		$ret = $this->db->query("SELECT uid FROM User WHERE name=?", array($name));
		if (! $ret->num_rows()) return false;
		return $ret->row()->uid;
	}

	function load_username($uid) {
		return $this->db->query("SELECT name FROM User WHERE uid=?", array($uid))->row()->name;
	}
	
	function load_priviledge($uid){
		return $this->db->query("SELECT priviledge FROM User WHERE uid=?", array($uid))->row()->priviledge;
	}

	function is_admin() {
		return $this->session->userdata('priviledge') == 'admin';
	}

	function permission($method) {
		$permission = $this->db->query("SELECT permission FROM User WHERE uid=?",
						array($this->user->uid()))->row()->permission;
		$permission = json_decode($permission, TRUE);
		
		if (isset($permission[$method])) {
			if ((int)$permission[$method] >= time()) return TRUE;
			else {
				unset($permission[$method]);
				$permission = json_encode($permission);
				$this->db->query("UPDATE User SET permission=? WHERE uid=?",
						array($permission, $this->user->uid()));
				return FALSE;
			}
		} else return FALSE;
	}

	function set_permission($permission, $uid) {
		$permission = json_encode($permission);
		$this->db->query("UPDATE User SET permission=? WHERE uid=?",
				array($permission, $uid));
	}

	function uid_check($uid){
		$result = $this->db->query("SELECT isEnabled FROM User
									WHERE uid=?",
									array($uid));
								
		if ($result->num_rows() == 0 || ! $result->row()->isEnabled) return FALSE;
		return TRUE;
	}
	
	function username_check($username){
		$result = $this->db->query("SELECT isEnabled FROM User
									WHERE name=?",
									array($username));
								
		if ($result->num_rows() == 0 || ! $result->row()->isEnabled) return FALSE;
		return TRUE;
	}
	
	function password_check($username, $password){
		$result = $this->db->query("SELECT priviledge, password FROM User
									WHERE name=?",
									array($username));
								
		if ($result->num_rows() == 0) return FALSE;
		if ($result->row()->password == $password) return $result; else return FALSE;
	}
	
	function login_check($username, $password, $admin = FALSE){
		$result = self::password_check($username, $password);
		if ($result == FALSE || ($admin && $query->row()->priviledge != 'admin')) return FALSE;
		return TRUE;
	}
	
	function identifier_check($identifier) {
		$result = $this->db->query("SELECT name FROM User
									WHERE identifier=?",
									array($identifier));
									
		if ($result->num_rows() == 0) return FALSE;
		return $result->row()->name;
	}
	
	function set_data($data) {
		$this->session->set_userdata('show_category', $data['showCategory']);
		$this->session->set_userdata('problems_per_page', $data['problemsPerPage']);
		$this->session->set_userdata('submission_per_page', $data['submissionPerPage']);
	}
	
	function login_success($post = array()){
		$result = $this->db->query("SELECT priviledge, uid, showCategory,
									problemsPerPage, submissionPerPage FROM User
									WHERE name=?",
									array($post['username']));
									
		$this->session->set_userdata('username', $post['username']);
		$this->session->set_userdata('uid', $result->row()->uid);
		$this->session->set_userdata('priviledge', $result->row()->priviledge);
		self::set_data($result->row_array());
		
		$this->input->set_cookie(array('name' => 'priviledge',
									'value' => $result->row()->priviledge,
									'expire' => '86400'));
									
		if (isset($post['remember']) && (int)$post['remember'] == 1){
			$identifier = md5(rand() + $result->row()->uid);
			$this->db->query("UPDATE User SET identifier=?
							WHERE uid=?",
							array($identifier, $result->row()->uid));
							
			$this->input->set_cookie(array('name' => 'identifier',
										'value' => $identifier,
										'expire' => '2592000'));
		} else {
			//$this->db->query("UPDATE User SET identifier='' WHERE uid=?", array($result->row()->uid));
			$this->input->set_cookie(array('name' => 'identifier', 'value' => '', 'expire' => '0'));
		}
		
		$this->db->query("UPDATE User SET lastLogin=now(), lastIP=?
						WHERE uid=?",
						array($this->input->ip_address(), (int)$result->row()->uid));
	}
	
	function registion_success($post = array()){
		$data = array(
			'name' => $post['username'],
			'password' => md5(md5($post['password']) . $this->config->item('password_suffix')),
			'email' => $post['email'],
			'priviledge' => 'user',
		);
		if (isset($post['school'])) $data['school'] = $post['school'];
		if (isset($post['description'])) $data['description'] = $post['description'];
		
		$sql = $this->db->insert_string('User', $data);
		$this->db->query($sql);
	}
	
	function logout(){
		$this->input->set_cookie(array('name' => 'priviledge', 'value' => '', 'expire' => '0'));
		$this->input->set_cookie(array('name' => 'identifier', 'value' => '', 'expire' => '0'));
		
		$this->session->unset_userdata('username');
		$this->session->unset_userdata('uid');
		$this->session->unset_userdata('priviledge');
		$this->session->unset_userdata('show_category');
		$this->session->unset_userdata('problems_per_page');
		$this->session->unset_userdata('submission_per_page');
		$this->session->unset_userdata('download');
		$this->session->unset_userdata('push');
	}
	
	function load_last_page($uid){
		return $this->db->query("SELECT lastPage FROM User
								WHERE uid=?",
								array($uid))->row()->lastPage;
	}
	
	function save_last_page($uid, $page){
		$this->db->query("UPDATE User SET lastPage=?
						WHERE uid=?",
						array($page, $uid));
	}
	
	function save_language($uid, $language){
		$this->db->query("UPDATE User SET language=?
						WHERE uid=?",
						array($language, $uid));
	}
	
	function count(){
		return $this->db->query("SELECT COUNT(*) AS count FROM User")->row()->count;
	}
	
	function load_user($uname){
		return $this->db->query("SELECT uid, email, description, school,
								acCount, submitCount, solvedCount, avatar, verificationKey FROM User
								WHERE name=?",
								array($uname))->row();
	}
	
	function load_accepted($uid){
		if ($this->is_admin())
			return $this->db->query("
			SELECT DISTINCT pid FROM Submission 
			WHERE uid=? AND status=0 ORDER BY pid
			",array($uid))->result();
		else
			return $this->db->query("
			SELECT DISTINCT pid FROM Submission 
			WHERE uid=? AND status=0 AND (ISNULL(cid) OR cid NOT IN (
				SELECT cid FROM Contest WHERE endTime>NOW()
			)) ORDER BY pid
			",array($uid))->result();
	}
	
	function load_unaccepted($uid){
		if ($this->is_admin())
			return $this->db->query("
			SELECT pid FROM (
				SELECT min(status) AS verdict, pid FROM Submission
				WHERE status>=0 AND uid=? GROUP BY pid
			)T
			WHERE verdict>0
			",array($uid))->result();
		else
			return $this->db->query("
			SELECT pid FROM (
				SELECT min(status) AS verdict, pid, cid FROM Submission
				WHERE status>=0 AND uid=? GROUP BY pid
			)T
			WHERE verdict>0 AND (ISNULL(cid) OR cid NOT IN (
				SELECT cid FROM Contest WHERE endTime>NOW()
			))
			",array($uid))->result();
	}
	
	function load_rank($uid){
		$result = $this->db->query("SELECT acCount, solvedCount, submitCount FROM User
									WHERE uid=?",
									array($uid))
									->row();
									
		if ($result->submitCount == 0) $rate = 0;
		else $rate = $result->solvedCount / $result->submitCount;
		
		return $this->db->query("SELECT count(*) AS rank FROM User
								WHERE acCount>? OR (acCount=? AND solvedCount/submitCount>?)",
								array($result->acCount, $result->acCount, $rate))
								->row()->rank + 1;
	}
	
	function load_configuration($uid){
		return $this->db->query('SELECT showCategory, email, School, blogURL, description, submissionPerPage, problemsPerPage FROM User
								WHERE uid=?', array($uid))
								->row();
	}
	
	function save_configuration($uid, $config){
		if (count($config) == 0) return;
		$sql = $this->db->update_string('User', $config, "uid=$uid");
		$this->session->set_userdata('show_category', $config['showCategory']);
		$this->db->query($sql);
		self::set_data($config);
	}
	
	function save_password($uid, $password){
		$sql = $this->db->update_string('User', array('password' => $password), "uid=$uid");
		$this->db->query($sql);
	}
	
	function load_users_list(){
		return $this->db->query("SELECT uid, name, school, isEnabled, priviledge, lastIP, lastLogin FROM User
								ORDER BY uid DESC")
								->result();
	}
	
	function load_user_groups($uid, &$groups){
		$data = $this->db->query("SELECT gid FROM Group_has_User
								WHERE uid=?", array($uid))
								->result();
		foreach ($data as $row) $row->name = $groups[$row->gid]->name;
		return $data;
	}
	
	function change_status($uid) {
		$this->db->query("UPDATE User SET isEnabled=1-isEnabled
						WHERE uid=?",
						array($uid));
	}

	function change_priviledge($uid, $priviledge) {
		$this->db->query("UPDATE User SET priviledge=? WHERE uid=?", array($priviledge, $uid));
	}
	
	function delete($uid) {
		$this->db->query("DELETE FROM User
						WHERE uid=?",
						array($uid));
	}
	
	function load_statistic($uid) {
		if ($this->is_admin())
			$result = $this->db->query("SELECT status, COUNT(*) AS count FROM Submission 
									WHERE uid=? GROUP BY status",
									array($uid))->result();
		else
			$result = $this->db->query("SELECT status, COUNT(*) AS count FROM Submission
									WHERE uid=? AND (ISNULL(cid) OR cid NOT IN (SELECT cid FROM Contest WHERE endTime>NOW()))
									GROUP BY status",
									array($uid))->result();
		for ($i = -2; $i <= 9; $i++) $data[$i] = 0;
		foreach ($result as $row) $data[$row->status] = $row->count;
		return $data;
	}
	
	function load_categories_statistic($uid) {
		$categorization = $this->misc->load_categorization();
		$result = $this->db->query("SELECT idCategory, COUNT(*) AS count FROM 
										(Categorization INNER JOIN 
											(SELECT DISTINCT pid FROM Submission 
											WHERE status=0 AND uid=?)AC 
										ON Categorization.pid=AC.pid) 
									GROUP BY idCategory",
									array($uid))->result();
		foreach ($result as $row)
			$row->name = $categorization[$row->idCategory];
		return $result;
	}
	
	function load_userPicture($uid) {
		return $this->db->query("SELECT userPicture FROM User
								WHERE uid=?",
								array($uid))
								->row()->userPicture;
	}
	
	function load_avatar($uid) {
		if ( ! $uid) return;
		return $this->db->query("SELECT avatar FROM User
								WHERE uid=?",
								array($uid))
								->row()->avatar;
	}
	
	function save_user_picture($uid, $pic) {
		$this->db->query("UPDATE User SET userPicture=?
						WHERE uid=?",
						array($pic, $uid));
	}
	
	function save_avatar($uid, $avatar) {
		$this->db->query("UPDATE User SET avatar=?
						WHERE uid=?",
						array($avatar, $uid));
	}

	function in_mail_count($uid) {
		return $this->db->query("SELECT COUNT(*) as count FROM Mail WHERE to_uid=?",
					array($uid))->row()->count;
	}

	function mail_count($uid) {
		$result = $this->db->query("SELECT COUNT(*) as count FROM
					(SELECT from_uid as uid FROM Mail WHERE to_uid=?
					 UNION
					 SELECT to_uid as uid FROM Mail WHERE from_uid=?)T",
					array($uid, $uid));
		if ($result->num_rows() == 0) return 0;
		else return $result->row()->count;
	}

	function load_mail_list($uid, $row_begin, $count) {
		return $this->db->query("SELECT * FROM (SELECT idMail, from_uid, to_uid, from_user, to_user, title, sendTime FROM Mail
					WHERE idMail in
						(SELECT max(idMail) FROM Mail WHERE from_uid=? OR to_uid=?
						GROUP BY (if (from_uid>to_uid, to_uid, from_uid)*100000+if (from_uid>to_uid, from_uid, to_uid))))
					AS T1 
				LEFT JOIN 
					(SELECT from_uid AS val, isRead FROM Mail
					WHERE idMail in
						(SELECT max(idMail) FROM Mail
						WHERE to_uid=?
						GROUP BY from_uid)
					) AS T2 
				ON if(T1.to_uid=?, T1.from_uid, T1.to_uid)=T2.val
				ORDER BY idMail DESC",
					array($uid, $uid, $uid, $uid))->result();
	}

	function unread_mail_count($uid) {
		return $this->db->query("SELECT COUNT(*) as count FROM Mail WHERE to_uid=? AND isRead=0",
					array($uid))->row()->count;
	}

	function load_mail($uid) {
//		$mail = $this->db->query("SELECT * FROM Mail WHERE idMail=?",
//					array($idMail))->row();
//		if ($mail->to_uid == $uid || $mail->from_uid == $uid) return $mail;
//		else return FALSE;
		$cuid = $this->user->uid();
		return $this->db->query("SELECT * FROM Mail 
					WHERE (from_uid=? AND to_uid=?) OR (from_uid=? AND to_uid=?)
					ORDER BY idMail",
					array($uid, $cuid, $cuid, $uid))->result();
	}

	function set_mail_read($uid) {
		$this->db->query("UPDATE Mail SET isRead=1 WHERE to_uid=? AND isRead=0", array($uid));
	}

	function save_mail($data) {
		$sql = $this->db->insert_string('Mail', $data);
 		$this->db->query($sql);
	}

	function running_contest_count() {
		return $this->db->query("SELECT COUNT(*) AS count FROM Contest
					WHERE unix_timestamp(startTime)<=unix_timestamp() AND unix_timestamp(endTime)>=unix_timestamp()")->row()->count;
	}

	function load_email($name)
	{
		$ret = $this->db->query("SELECT email FROM User WHERE name=?", array($name));
		if (!$ret->num_rows()) return FALSE;
		return $ret->row()->email;
	}

	function load_online_users()
	{
		$ret = array();
		$redis = new Redis();
		$redis->connect($this->config->item('redis_host'), $this->config->item('redis_port'));
		$redis->setOption(Redis::OPT_PREFIX, 'online_users:'.$this->config->item('oj_name').':'); 
		$uids = $redis->keys('*');
		foreach ($uids as $uid)
		{
			$exploded = explode(':', $uid);
			$uid = array_pop($exploded);
			$ret[$uid] = $redis->get($uid);
		}
		$redis->close();
		return $ret;
	}

	function set_verification_key($uid, $key) {
		$this->db->query("UPDATE User SET verificationKey=? WHERE uid=?", array($key, $uid));
	}

	function load_blog_url($uid) {
		$ret = $this->db->query("SELECT blogURL FROM User WHERE uid=?", array($uid));
		if (!$ret->num_rows()) return FALSE;
		return $ret->row()->blogURL;
	}
}
