<?php

class Submission extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function rejudge($sid){
		$data = $this->db->query("SELECT cid, pid, uid, status, score FROM Submission WHERE sid=?",
									array($sid))->row();
									
		if ($data->status != -1 && is_null($data->cid)) {
			$this->db->query("UPDATE ProblemSet SET scoreSum=scoreSum-? WHERE pid=?",
				array($data->score, $data->pid));
		}
	
		if ($data->status == 0 && is_null($data->cid)){
			$this->db->query("UPDATE ProblemSet SET solvedCount=solvedCount-1 WHERE pid=?",
								array($data->pid));
		
			$this->db->query("UPDATE User SET solvedCount=solvedCount-1 WHERE uid=?",
								array($data->uid));
		}
		
		$this->db->query("UPDATE Submission SET score=0,status=-1,time=0, memory=0,judgeResult='' WHERE sid=?",
							array($sid));
	}
	
	function change_status($sid){
		$this->db->query("UPDATE Submission SET isShowed=1-isShowed WHERE sid=?", array($sid));
	}
	
	function change_access($sid){
		if ($this->db->query("SELECT sid FROM Submission WHERE sid=?", array($sid))->num_rows() == 0)
			return;
		$this->db->query("UPDATE Submission SET private=1-private WHERE sid=?", array($sid));
	}
	
	function save_submission($data){
		$sql = $this->db->insert_string('Submission', $data);
		$this->db->query($sql);
		$sid = $this->db->insert_id();
		$this->db->query("UPDATE ProblemSet SET submitCount=submitCount+1 WHERE pid=?", array($data['pid']));
		return $sid;
	}
	
	function format_data(&$data){
		foreach ($data as $row){
			switch ($row->status){
				case -4: $row->result = '<span class="label">' . lang('output_not_found') . '</span>'; break;
				case -3: $row->result = '<span class="label label-success">' . lang('partially_accepted') . '</span>'; break;
				case -2: $row->result = '<span class="label label-important">' . lang('running') . '</span>'; break;
				case -1: $row->result = '<span class="label">' . lang('pending') . '</span>'; break;
				case 0: $row->result = '<span class="label label-success">' . lang('accepted') . '</span>'; break;
				case 1: $row->result = '<span class="label label-important">' . lang('presentation_error') . '</span>'; break;
				case 2: $row->result = '<span class="label label-important">' . lang('wrong_answer') . '</span>'; break;
				case 3: $row->result = '<span class="label label-info">' . lang('checker_error') . '</span>'; break;
				case 4: $row->result = '<span class="label label-warning">' . lang('output_limit_exceeded') . '</span>'; break;
				case 5: $row->result = '<span class="label label-warning">' . lang('memory_limit_exceeded') . '</span>'; break;
				case 6: $row->result = '<span class="label label-warning">' . lang('time_limit_exceeded') . '</span>'; break;
				case 7: $row->result = '<span class="label label-important">' . lang('runtime_error') . '</span>'; break;
				case 8: $row->result = '<span class="label">' . lang('compile_error') . '</span>'; break;
				case 9: $row->result = '<span class="label">' . lang('internal_error') . '</span>'; break;
				default: $row->result = 'Nothing Happened';
			}

			if (isset($row->codeLength)) $row->codeLength = $row->codeLength . ' bytes';

			if (isset($row->time)) $row->time = $row->time . ' ms';

			if (isset($row->memory)){
				if ($row->memory >= 1048576) $row->memory = number_format($row->memory / 1048576, 2) . ' GB';
				else if ($row->memory >= 1024) $row->memory = number_format($row->memory / 1024, 2) . ' MB';
				else $row->memory = $row->memory . ' KB';
			}
			
		}
	}
	
	function statistic_count($pid){
		if ($this->user->is_admin()) {
			return $this->db->query("SELECT COUNT(DISTINCT uid) AS count
						FROM Submission WHERE pid=? AND (status>=0 OR status<=-3)",
						array($pid))->row()->count;
		} else {
			return $this->db->query("SELECT COUNT(DISTINCT uid) AS count
						FROM Submission WHERE pid=? AND (status>=0 OR status<=-3) AND isShowed=1",
						array($pid))->row()->count;
		}
	}
	
	function load_statistic($pid, $row_begin, $count){
		if ($this->user->is_admin()) {
			return $this->db->query("SELECT *, COUNT(DISTINCT A.uid) FROM
							(SELECT sid, uid, status, name, score, time, memory, codeLength, submitTime, language, private, isShowed, 
							-score*100000000000000+time*10000000000+memory*100000+sid val FROM Submission
							WHERE pid=? AND (status>=0 OR status<=-3)) A
						INNER JOIN
							(SELECT uid, min(-score*100000000000000+time*10000000000+memory*100000+sid) eval, COUNT(*) AS count
							 FROM Submission WHERE pid=? AND (status>=0 OR status<=-3) GROUP BY uid) B
						ON A.val=B.eval AND A.uid=B.uid GROUP BY A.uid ORDER BY A.val LIMIT ?,?;",
							array($pid, $pid, $row_begin, $count))->result();
		} else {
			return $this->db->query("SELECT *, COUNT(DISTINCT A.uid) FROM
							(SELECT sid, uid, status, name, score, time, memory, codeLength, submitTime, language, private, isShowed, 
							-score*100000000000000+time*10000000000+memory*100000+sid val FROM Submission
							WHERE pid=? AND (status>=0 OR status<=-3) AND isShowed=1) A
						INNER JOIN
							(SELECT uid, min(-score*100000000000000+time*10000000000+memory*100000+sid) eval, COUNT(*) AS count
							 FROM Submission WHERE pid=? AND (status>=0 OR status<=-3) AND isShowed=1 GROUP BY uid) B
						ON A.val=B.eval AND A.uid=B.uid GROUP BY A.uid ORDER BY A.val LIMIT ?,?;",
							array($pid, $pid, $row_begin, $count))->result();
	
		}
	}
	
	private function filter_to_string($filter){
		$conditions = '';
		if (isset($filter['problems'])){
			$conditions .= ' AND pid IN (';
			foreach ($filter['problems'] as $pid) $conditions .= $pid . ',';
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['users'])){
			$conditions .= ' AND name IN (';
			foreach ($filter['users'] as $name) $conditions .= "'$name',";
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['status'])){
			$conditions .= ' AND status IN (';
			foreach ($filter['status'] as $status) $conditions .= $status . ',';
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['languages'])){
			$conditions .= ' AND language IN (';
			foreach ($filter['languages'] as $language) $conditions .= "'$language',";
			$conditions[strlen($conditions) - 1] = ')';
		}
		return $conditions;
	}
	
	function count($filter = NULL){
		$conditions = self::filter_to_string($filter);
		return $this->db->query("SELECT COUNT(*) AS count FROM Submission WHERE cid IS NULL $conditions")->row()->count;
	}
	
	function load_status($row_begin, $count, $filter = NULL){
		$conditions = self::filter_to_string($filter);
		$result = $this->db->query("SELECT sid, uid, gid, tid, name, pid, status, score, time, memory,
							codeLength, submitTime, language, isShowed, private, sim 
						FROM Submission WHERE cid IS NULL $conditions ORDER BY sid DESC LIMIT ?, ?",
					array($row_begin, $count));
		return $result->result();
	}
	
	function load_code($sid){
		$result = $this->db->query("SELECT uid, pid, code, language, private FROM Submission WHERE sid=?", array($sid));
		if ($result->num_rows() == 0) return FALSE; else $result = $result->row();
		$uid = $this->session->userdata('uid');
		$accepted = $this->db->query("SELECT * FROM Submission WHERE pid=? AND uid=? AND status=0", array($result->pid, $uid))->num_rows() > 0;
		if ($this->db->query("SELECT pid FROM ProblemSet WHERE pid=? AND isShowed=1", array($result->pid))->num_rows() == 0)
			$accepted = FALSE;
		if ($result->uid == $uid || $this->session->userdata('priviledge') == 'admin' || $result->private == 0 || $accepted) 
			return $result;
		return FALSE;
	}
	
	function load_result($sid){
		$result = $this->db->query("SELECT uid, pid, cid, judgeResult AS result
									FROM Submission
									WHERE sid=?",
									array($sid));
									
		if ($result->num_rows() == 0) return FALSE;
		
		$result = $result->row();
		if ($result->uid == $this->session->userdata('uid') || $this->session->userdata('priviledge') == 'admin') {
			if ( ! is_null($result->cid)) {
				$this->load->model('contests');
				$info = $this->contests->load_contest_status($result->cid);
				if ($this->session->userdata('priviledge') == 'admin') return $result;
				else if ($info->contestMode == 'Codeforces' || $info->contestMode == 'OI') return $result;
				else if (strtotime($info->endTime) < strtotime('now')) return $result;
				else return FALSE;
			} else return $result;
		}
		return FALSE;
	}
	
	function load_uid($sid){
		$result = $this->db->query("SELECT uid FROM Submission WHERE sid=?", array($sid));
		if ($result->num_rows() == 0) return FALSE;
		return $result->row()->uid;		
	}
	
	function is_private($sid) {
		$result = $this->db->query("SELECT private FROM Submission WHERE sid=?", array($sid))->row();
		return $result->private == 1;
	}
}
