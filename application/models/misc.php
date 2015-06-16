<?php

function task_cmp($a, $b) {
	if ($a[0] < $b[0]) return 1;
	if ($a[0] > $b[0]) return -1;
	return 0;
}

class Misc extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function load_ranklist($row_begin, $count){
		return $this->db->query("SELECT name, description, acCount, solvedCount, submitCount FROM User
								ORDER BY acCount DESC, solvedCount / submitCount DESC, submitCount LIMIT ?,?",
								array($row_begin, $count))->result();
	}
	
	function load_categorization(){
		$result = $this->db->query("SELECT * FROM Category")->result();
		$data = array();
		foreach ($result as $row) $data[$row->idCategory] = $row->name;
		return $data;
	}
	
	function load_problem_category($pid, &$categorization){
		$result = $this->db->query("SELECT idCategory FROM Categorization WHERE pid=?", array($pid))->result();
		$data = array();
		foreach ($result as $row) $data[$row->idCategory] = $categorization[$row->idCategory];
		return $data;
	}
	
	function is_accepted($uid, $pid){
		$count = $this->db->query("SELECT COUNT(*) AS count FROM Submission WHERE status=0 AND uid=? AND pid=?",
									array($uid, $pid))->row()->count;
		return $count > 0;
	}
	
	function add_categorization($pid, $id){
		$sql = $this->db->insert_string('Categorization', array('pid' => $pid, 'idCategory' => $id));
		$this->db->query($sql);
	}
	
	function delete_categorization($pid, $id){
		$sql = $this->db->query("DELETE FROM Categorization WHERE pid=? AND idCategory=?", array($pid, $id));
	}
	
	function load_groups($uid){
		$result = $this->db->query("SELECT * FROM `Group` ORDER BY gid")->result();
		$data = array();
		foreach ($result as $row){
			$data[$row->gid] = $row;
			$result = $this->db->query("SELECT * FROM Group_has_User WHERE gid=? AND uid=?", array($row->gid, $uid));
			if ($result->num_rows() == 0) $data[$row->gid]->status = 'stranger';
			else if ($result->row()->isAccepted == 0) $data[$row->gid]->status = 'pending';
			else if ($result->row()->priviledge == 'admin') $data[$row->gid]->status = 'admin';
			else $data[$row->gid]->status = 'user';
		}
		return $data;
	}
	
	function load_grouping($gid){
		return $this->db->query("SELECT gid, User.uid AS uid, name, isAccepted FROM Group_has_User
								LEFT JOIN User ON Group_has_User.uid=User.uid WHERE gid=?", array($gid))->result();		
	}
	
	function save_group_settings($post, $gid = 0){
		if ($gid == 0){
			$post['count'] = 1;
			$sql = $this->db->insert_string('Group', $post);
			$this->db->query($sql);
			$gid = $this->db->insert_id();
			$uid = $this->session->userdata('uid');
			$data = array('uid' => $uid, 'gid' => $gid, 'priviledge' => 'admin', 'isAccepted' => 1);
			$sql = $this->db->insert_string('Group_has_User', $data);
			$this->db->query($sql);
		} else {
			$sql = $this->db->update_string('Group', $post, "gid=$gid");
			$this->db->query($sql);
		}
		return $gid;
	}
	
	function load_invitation_code($gid){
		return $this->db->query("SELECT invitationCode FROM `Group` WHERE gid=?", array($gid))->row()->invitationCode;
	}
	
	function get_random_code($length){
		$output = '';
		for ($i = 0; $i < $length; $i++){
			$c = rand(0, 35);
			$output .= chr($c < 26 ? 65 + $c : 22 + $c);
		}
		return $output;
	}
	
	function delete_group($gid){
		$this->db->query("DELETE FROM `Group` WHERE gid=?", array($gid));
		$count = (int)$this->db->query("SELECT MAX(gid) AS gid FROM `Group`")->row()->gid + 1;
		$this->db->query("ALTER TABLE `Group` AUTO_INCREMENT=?", array($count));
	}
	
	function search_group_by_code($code){
		$result = $this->db->query("SELECT gid FROM `Group` WHERE invitationCode=?", array($code));
		if ($result->num_rows() == 0) return FALSE;
		return $result->row()->gid;
	}
	
	function group_join($gid, $uid){
		$sql = $this->db->insert_string('Group_has_User', array('gid' => $gid, 'uid' => $uid));
		$this->db->query($sql);
	}
	
	function group_member_accept($gid, $uid){
		$this->db->query("UPDATE Group_has_User SET isAccepted=1 WHERE gid=? AND uid=?", array($gid, $uid));
		$this->db->query("UPDATE `Group` SET count=count+1 WHERE gid=?", array($gid));
	}
	
	function group_member_decline($gid, $uid){
		$this->db->query("DELETE FROM Group_has_User WHERE gid=? AND uid=?", array($gid, $uid));
	}
	
	function group_member_delete($gid, $uid){
		$this->db->query("DELETE FROM Group_has_User WHERE gid=? AND uid=?", array($gid, $uid));
		$this->db->query("UPDATE `Group` SET count=count-1 WHERE gid=?", array($gid));
	}
	
	function is_in_group($uid, $gid) {
		return $this->db->query("SELECT COUNT(*) AS count FROM Group_has_User WHERE uid=? AND gid=?",
								array($uid, $gid))->row()->count > 0;
	}
	
	function is_group_admin($gid){
		$uid = $this->session->userdata('uid');
		$result = $this->db->query("SELECT priviledge FROM Group_has_User WHERE gid=? AND uid=?", array($gid, $uid));
		if ($result->num_rows() == 0 || $result->row()->priviledge != 'admin') return false;
		return true;
	}
	
	function load_task_problems($tid){
		return $this->db->query("SELECT * FROM Task_has_ProblemSet WHERE tid=?", array($tid))->result();
	}
	
	function load_group_tasks($gid, $detailed = TRUE){
		$result = $this->db->query("SELECT gid, T.tid AS tid, T.title AS new_title, Task.title AS title, startTime, endTime, description FROM
									(SELECT * FROM Group_has_Task WHERE gid=?)T INNER JOIN Task ON T.tid=Task.tid", array($gid))->result();
		if ($detailed){
			foreach ($result as $task)
				$task->problems = $this->load_task_problems($task->tid);
		}
		return $result;
	}
	
	function load_tasks_submissions($uid){
		$result = $this->db->query("SELECT tid, pid, score FROM Submission WHERE uid=? AND tid IS NOT NULL", array($uid))->result();
		$data = array();
		foreach ($result as $row){
			if ( ! isset($data[$row->tid][$row->pid])) $data[$row->tid][$row->pid] = (double)$row->score;
			else $data[$row->tid][$row->pid] = max($data[$row->tid][$row->pid], (double)$row->score);
		}
		return $data;
	}
	
	function load_task_info($gid, $tid){
		$data = $this->db->query("SELECT * FROM Group_has_Task WHERE gid=? AND tid=?", array($gid, $tid))->row();
		$data->language = $this->db->query("SELECT language FROM Task WHERE tid=?", array($tid))->row()->language;
		return $data;
	}
	
	function load_task($tid){
		$data = (array)$this->db->query('SELECT * FROM Task WHERE tid=?', array($tid))->row();
		$problems = $this->db->query('SELECT * FROM Task_has_ProblemSet WHERE tid=?', array($tid));
		for ($i = 0; $i < $problems->num_rows(); $i++)
			$data['problems'][] = $problems->row($i);
		return $data;
	}
	
	function add_task($raw, $tid = 0){
		foreach ($raw['languages'] as $lang)
			if (isset($languages)) $languages .= ',' . $lang;
			else $languages = $lang;
			
		$data = array(
			'title' => $raw['task_title'],
			'description' => $raw['description'],
			'language' => $languages
		);
		if ($tid != 0) $sql = $sql = $this->db->update_string('Task', $data, "tid=$tid");
		else $sql = $this->db->insert_string('Task', $data);
		$this->db->query($sql);
		if ($tid == 0) $tid = $this->db->insert_id();
		
		if (isset($raw['pid'])){
			$this->db->query("DELETE FROM Task_has_ProblemSet WHERE tid=?", array($tid));
			$cnt = count($raw['pid']);
			for ($now = 0; $now < $cnt; $now++){
				if (isset($problem)) unset($problem);
				$problem['tid'] = $tid;
				$pid = $problem['pid'] = $raw['pid'][$now];
				if ($raw['title'][$now] != '') $problem['title'] = $raw['title'][$now];
				else $problem['title'] = $this->db->query("SELECT title FROM ProblemSet WHERE pid=?", array($pid))->row()->title;
				
				$sql = $this->db->insert_string('Task_has_ProblemSet', $problem);
				$this->db->query($sql);	
			}
		}
	}
	
	function delete_task($tid) {
		$this->db->query("DELETE FROM Task WHERE tid=?", array($tid));
		$cnt = $this->db->query('SELECT MAX(tid) AS cnt FROM Task')->row()->cnt + 1;
		if ($cnt < 1) $cnt = 1;
		$this->db->query('ALTER TABLE Task AUTO_INCREMENT=?',
						array($cnt));
	}
	
	function count_tasks(){
		return $this->db->query("SELECT COUNT(*) AS count FROM Task")->row()->count;
	}
	
	function load_all_task(){
		return $this->db->query("SELECT * FROM Task")->result();
	}	
	
	function load_task_list($row_begin, $tasks_per_page){
		return $this->db->query("SELECT * FROM Task ORDER BY tid DESC LIMIT ?,?", array($row_begin, $tasks_per_page))->result();
	}
	
	function group_add_tasks($gid, $tasks){
		foreach ($tasks as $task){
			$sql = $this->db->insert_string('Group_has_Task',
				array('gid' => $gid, 'tid' => $task->tid, 'startTime' => $task->startTime, 'endTime' => $task->endTime));
			$this->db->query($sql);
		}
	}
	
	function load_group_task_configuration($gid, $tid){
		return $this->db->query("SELECT * FROM Group_has_Task WHERE gid=? AND tid=?", array($gid, $tid))->row();
	}
	
	function save_group_task_configuration($gid, $tid, $raw){
		$data['title'] = $raw['title'];
		$data['startTime'] = $raw['start_date'] . ' ' . $raw['start_time'];
		$data['endTime'] = $raw['end_date'] . ' ' . $raw['end_time'];
		$sql = $this->db->update_string('Group_has_Task', $data, "gid=$gid AND tid=$tid");
		$this->db->query($sql);
	}
	
	function group_delete_task($gid, $tid){
		$this->db->query("DELETE FROM Group_has_Task WHERE gid=? AND tid=?", array($gid, $tid));
	}
	
	function load_task_statistic($gid, $tid) {
		$result = $this->db->query("SELECT uid, name, pid, score, submitTime FROM Submission WHERE gid=? AND tid=?",
								array($gid, $tid));
		if ($result->num_rows() == 0) return FALSE;
		else $result = $result->result();
		
		foreach ($result as $submission) {
			if ( ! isset($data[$submission->uid][$submission->pid]) )
				$data[$submission->uid][$submission->pid] = $submission;
			else if ($data[$submission->uid][$submission->pid]->score < $submission->score)
				$data[$submission->uid][$submission->pid] = $submission;
		}
		
		foreach ($data as $user) {
			$score = 0;
			foreach ($user as $problem) {
				$score += $problem->score; 
				$uid = $problem->uid;
				$data[$uid][1] = $problem->name;
			}
			$data[$uid][0] = $score;
		}
		uasort($data, "task_cmp");
		
		return $data;
	}

	function add_allowing($uid, $pid)
	{
		if (! $this->db->query("SELECT COUNT(*) AS cnt FROM Allowed_Problem WHERE uid=? AND pid=?", array($uid, $pid))->row()->cnt)
			$this->db->query("INSERT INTO Allowed_Problem (uid, pid) VALUES (?, ?)", array($uid, $pid));
	}

	function del_allowing($id)
	{
		$this->db->query("DELETE FROM Allowed_Problem WHERE id=?", array($id));
	}

	function load_allowing($uid)
	{
		return $this->db->query("
				SELECT Allowed_Problem.id, Allowed_Problem.pid, ProblemSet.title, ProblemSet.source
				FROM Allowed_Problem
				INNER JOIN ProblemSet ON Allowed_Problem.pid=ProblemSet.pid WHERE Allowed_Problem.uid=?
			", array($uid))->result();
	}
}
