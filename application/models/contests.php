<?php

class Participant{
	var $score, $penalty, $rank, $name, $isFormal, $attempt, $acList, $submitTime;
}

function team_cmp_ACM($a, $b){
	if ($a->score == $b->score){
		if ($a->penalty < $b->penalty) return -1;
		if ($a->penalty > $b->penalty) return 1;
		return 0;
	} else {
		if ($a->score > $b->score) return -1; else return 1;
	}
}

function team_cmp_OI($a, $b){
	if ($a->score > $b->score) return -1; else return 1;
}

class Contests extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function is_valid($cid){
		if ($this->db->query("SELECT * FROM Contest
							WHERE cid=? AND private=0",
							array($cid))
							->num_rows() > 0) 
			return TRUE;
			
		$uid = $this->session->userdata('uid');
		$result = $this->db->query("SELECT * FROM Team
								WHERE (idParticipant0=? OR idParticipant1=? OR idParticipant2=?) AND cid=?",
								array($uid, $uid, $uid, $cid));
		
		if ($result->num_rows() > 0) return TRUE;
		return FALSE;
	}
	
	function load_contest_status($cid){
		$result = $this->db->query("SELECT * FROM Contest
									WHERE cid=?",
									array($cid));
		if ($result->num_rows() == 0) return FALSE;
		else{
			$data = $result->row();
			$startTime = strtotime($data->startTime);
			$endTime = strtotime($data->endTime);
			$data->now = strtotime('now');
			$data->running = FALSE;
			if ($data->now > $endTime) $data->status = '<span class="label label-success">Ended</span>';
			else if ($data->now < $startTime) $data->status = '<span class="label label-info">Pending</span>';
			else{
				$data->status = '<span class="label label-important">Running</span>';
				$data->running = TRUE;
			}
			$data->problemset = $this->db->query("SELECT * FROM Contest_has_ProblemSet
												WHERE cid=? ORDER BY id",
												array($cid));
			$data->count = $data->problemset->num_rows();
			$data->problemset = $data->problemset->result();
			return $data;
		}
	}
	
	function count(){
		return $this->db->query("SELECT COUNT(*) AS count FROM Contest
								WHERE isShowed = 1;")
								->row()->count;
	}
	
	function load_contests_list($row_begin, $count){
		return $this->db->query("SELECT cid, title, startTime, submitTime, endTime, contestMode, private FROM Contest
								WHERE isShowed=1 ORDER BY cid DESC LIMIT ?,?",
								array($row_begin, $count))
								->result();
	}
	
	function load_contest_teams_count($cid){
		return $this->db->query("SELECT COUNT(*) AS count FROM Team
								WHERE cid=?",
								array($cid))
								->row()->count;
	}
	
	function load_contest_problemset($cid){
		return $this->db->query("SELECT * FROM Contest_has_ProblemSet
								WHERE cid=? ORDER BY id",
								array($cid))
								->result();
	}
	
	function load_contest_problem_name($pid){
		return $this->db->query("SELECT * FROM ProblemSet
								WHERE pid=?",
								array($pid))
								->row();
	}
	
	function modify_problem(&$data, $cid){
		$data->submitCount = $this->db->query("SELECT COUNT(sid) AS count FROM Submission
											WHERE cid=? AND pid=?",
											array($cid, $data->pid))
											->row()->count;
		$data->solvedCount = $this->db->query("SELECT COUNT(sid) AS count FROM Submission
											WHERE cid=? AND pid=? AND status=0",
											array($cid, $data->pid))
											->row()->count;
	}
	
	function load_contest_problem_statistic($cid, $pid){
		$data = new stdClass();
		$data->submitCount = $this->db->query("SELECT COUNT(sid) AS count FROM Submission
											WHERE cid=? AND pid=?",
											array($cid, $pid))
											->row()->count;
		$data->solvedCount = $this->db->query("SELECT COUNT(sid) AS count FROM Submission
											WHERE cid=? AND pid=? AND status=0",
											array($cid, $pid))
											->row()->count;
		return $data;
	}
	
	function load_contest_submission_count($cid){
		return $this->db->query("SELECT COUNT(*) AS count FROM Submission
								WHERE cid=?",
								array($cid))
								->row()->count;
	}
	
	function load_contest_submission($cid, $row_begin, $count, $running, $username, $is_admin){
		if ($is_admin || ! $running){
			return $this->db->query("SELECT isShowed, uid, private, sid, name, pid, status, score,
								time, memory, codeLength, submitTime, language FROM Submission
								WHERE cid=? ORDER BY sid DESC LIMIT ?, ?",
								array($cid, $row_begin, $count))
								->result();
		}else{
			return $this->db->query("SELECT isShowed, uid, private, sid, name, pid, status, score,
								time, memory, codeLength, submitTime, language FROM Submission
								WHERE cid=? AND name=? ORDER BY sid DESC LIMIT ?, ?",
								array($cid, $username, $row_begin, $count))
								->result();
		}
	}

	function load_contest_pid($cid, $id){
		$result = $this->db->query("SELECT pid FROM Contest_has_ProblemSet
								WHERE cid=? AND id=?",
								array($cid, $id));
		if ($result->num_rows() == 0) return FALSE;
		return $result->row()->pid;
	}
	
	function load_contest_ranklist_ACM($cid){
		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();
		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode == TRUE){
			$data = $this->db->query("SELECT * FROM Team
									WHERE cid=? ORDER BY score DESC, penalty DESC",
									array($cid))
									->result();
			foreach ($data as $row){
				$result[$row->idTeam]->name = $row->name;
				$result[$row->idTeam]->score = $row->score;
				$result[$row->idTeam]->penalty = $row->penalty;
				$result[$row->idTeam]->isFormal = $row->isFormal;
			}
			
		}else {
			$data = $this->db->query("SELECT uid, name, pid, submitTime, status FROM Submission
									  WHERE cid=? ORDER BY sid",
									  array($cid))
									  ->result();
			//var_dump($data);
			foreach ($data as $row){
				if ( ! isset($result[$row->uid])){
					$result[$row->uid] = new Participant;
					$result[$row->uid]->name = $row->name;
					$result[$row->uid]->penalty = 0;
					$result[$row->uid]->score = 0;
					$result[$row->uid]->isFormal = TRUE;
				}
				if (isset($result[$row->uid]->acList[$row->pid])) continue;
				
				if ( ! isset($result[$row->uid]->attempt[$row->pid])) $result[$row->uid]->attempt[$row->pid] = 0;
				if ($row->status == 0){
					$result[$row->uid]->acList[$row->pid] = round((strtotime($row->submitTime) - $startTime) / 60, 0);
					$result[$row->uid]->score++;
					$result[$row->uid]->penalty += $result[$row->uid]->acList[$row->pid];
					$result[$row->uid]->penalty += $result[$row->uid]->attempt[$row->pid] * 20;
					$result[$row->uid]->attempt[$row->pid]++;
				}else{
					$result[$row->uid]->attempt[$row->pid]++;
				}
			}
			if (isset($result)) usort($result, "team_cmp_ACM");
		}
		
		if (isset($result)){
			$rank = $cnt = $score = $penalty = 0;
			foreach ($result as $row){
				if ( ! $row->isFormal) continue;
				if ($score == $row->score && $penalty == $row->penalty) $cnt++;
				else{
					$rank += $cnt + 1;
					$cnt = 0;
				}
				$row->rank = $rank;
				$score = $row->score;
				$penalty = $row->penalty;
			}
			return $result;
		}
		return NULL;
	}
	
	function load_contest_ranklist_OI($cid, $info){
		$now = strtotime('now');
		if ($now <= strtotime($info->endTime) && ! $this->user->is_admin() && $info->contestMode == 'OI Traditional') return FALSE;
		
		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();
		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode == TRUE){
			$data = $this->db->query("SELECT * FROM Team
									WHERE cid=? ORDER BY score DESC",
									array($cid))
									->result();
			foreach ($data as $row){
				$result[$row->idTeam]->name = $row->name;
				$result[$row->idTeam]->score = $row->score;
				$result[$row->idTeam]->penalty = $row->penalty;
				$result[$row->idTeam]->isFormal = $row->isFormal;
			}
			
		}else {
			$data = $this->db->query("SELECT sid, uid, name, pid, score FROM Submission
									  WHERE cid=? ORDER BY sid DESC",
									  array($cid))
									  ->result();
			foreach ($data as $row){
				if ( ! isset($result[$row->uid])){
					$result[$row->uid] = new Participant;
					$result[$row->uid]->name = $row->name;
					$result[$row->uid]->score = 0;
					$result[$row->uid]->isFormal = TRUE;
				}
				if (isset($result[$row->uid]->attempt[$row->pid])) continue;

				$result[$row->uid]->attempt[$row->pid] = $row->sid;
				$result[$row->uid]->acList[$row->pid] = round($row->score, 1);
				$result[$row->uid]->score += round($row->score, 1);
			}
			if (isset($result)) usort($result, "team_cmp_OI");
		}
		
		if (isset($result)){
			$rank = $cnt = $score = 0;
			foreach ($result as $row){
				if ( ! $row->isFormal) continue;
				if ($score == $row->score) $cnt++;
				else{
					$rank += $cnt + 1;
					$cnt = 0;
				}
				$row->rank = $rank;
				$score = $row->score;
			}
			return $result;
		}
		return NULL;
	}
	

	function load_contest_statistic_ACM($cid){
		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();
		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode == TRUE){
			$data = $this->db->query("SELECT * FROM Team
									WHERE cid=? ORDER BY score DESC, penalty DESC",
									array($cid))
									->result();
			foreach ($data as $row){
				$result[$row->idTeam]->name = $row->name;
				$result[$row->idTeam]->score = $row->score;
				$result[$row->idTeam]->penalty = $row->penalty;
				$result[$row->idTeam]->isFormal = $row->isFormal;
			}
			
		}else {
			$result_pids = $this->db->query("SELECT pid FROM Contest_has_ProblemSet WHERE cid=?",
									array($cid))->result_array();
			$pids = array();
			foreach ($result_pids as $pid) $pids[] = $pid['pid'];
			if (!$pids) return NULL;
			$pids = implode(',', $pids);
			if ($this->session->userdata('priviledge') == 'admin')
				$data = $this->db->query("SELECT sid, uid, name, pid, score, status, submitTime FROM Submission WHERE pid in ($pids) ORDER BY sid DESC")->result();
			else
				$data = $this->db->query("SELECT sid, uid, name, pid, score, status, submitTime FROM Submission WHERE isShowed=1 AND pid in ($pids) ORDER BY sid DESC")->result();
			foreach ($data as $row){
				if ( ! isset($result[$row->uid])){
					$result[$row->uid] = new Participant;
					$result[$row->uid]->name = $row->name;
					$result[$row->uid]->penalty = 0;
					$result[$row->uid]->score = 0;
					$result[$row->uid]->isFormal = TRUE;
					$result[$row->uid]->submitTime = strtotime($row->submitTime); // it should be ORDER BY sid DESC up.
				}
				if (isset($result[$row->uid]->acList[$row->pid])) continue;
				
				if ( ! isset($result[$row->uid]->attempt[$row->pid])) $result[$row->uid]->attempt[$row->pid] = 0;
				if ($row->status == 0){
					$result[$row->uid]->acList[$row->pid] = round((strtotime($row->submitTime) - $startTime) / 60, 0);
					$result[$row->uid]->score++;
					$result[$row->uid]->penalty += $result[$row->uid]->acList[$row->pid];
					$result[$row->uid]->penalty += $result[$row->uid]->attempt[$row->pid] * 20;
					$result[$row->uid]->attempt[$row->pid]++;
				}else{
					$result[$row->uid]->attempt[$row->pid]++;
				}
			}
			if (isset($result)) usort($result, "team_cmp_ACM");
		}
		
		if (isset($result)){
			$rank = $cnt = $score = $penalty = 0;
			foreach ($result as $row){
				if ( ! $row->isFormal) continue;
				if ($score == $row->score && $penalty == $row->penalty) $cnt++;
				else{
					$rank += $cnt + 1;
					$cnt = 0;
				}
				$row->rank = $rank;
				$score = $row->score;
				$penalty = $row->penalty;
			}
			return $result;
		}
		return NULL;
	}
	
	function load_contest_statistic_OI($cid, $info){
		$now = strtotime('now');
		if ($now <= strtotime($info->endTime) && ! $this->user->is_admin() && $info->contestMode == 'OI Traditional') return FALSE;
		
		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();
		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode == TRUE){
			$data = $this->db->query("SELECT * FROM Team
									WHERE cid=? ORDER BY score DESC",
									array($cid))
									->result();
			foreach ($data as $row){
				$result[$row->idTeam]->name = $row->name;
				$result[$row->idTeam]->score = $row->score;
				$result[$row->idTeam]->penalty = $row->penalty;
				$result[$row->idTeam]->isFormal = $row->isFormal;
			}
			
		}else {
			$result_pids = $this->db->query("SELECT pid FROM Contest_has_ProblemSet WHERE cid=?",
				array($cid))->result_array();
			$pids = array();
			foreach ($result_pids as $pid) $pids[] = $pid['pid'];
			$pids = implode(',', $pids);
			if ($this->session->userdata('priviledge') == 'admin')
				$data = $this->db->query("SELECT sid, uid, name, pid, score, submitTime FROM Submission WHERE pid in ($pids) ORDER BY sid DESC")->result();
			else
				$data = $this->db->query("SELECT sid, uid, name, pid, score, submitTime FROM Submission WHERE isShowed=1 AND pid in ($pids) ORDER BY sid DESC")->result();
			foreach ($data as $row){
				if ( ! isset($result[$row->uid])){
					$result[$row->uid] = new Participant;
					$result[$row->uid]->name = $row->name;
					$result[$row->uid]->score = 0;
					$result[$row->uid]->isFormal = TRUE;
					$result[$row->uid]->submitTime = strtotime($row->submitTime); // it should be ORDER BY sid DESC up.
				}
				if (isset($result[$row->uid]->attempt[$row->pid])) {
					$result[$row->uid]->score -= $result[$row->uid]->acList[$row->pid];
					$result[$row->uid]->acList[$row->pid] = max($result[$row->uid]->acList[$row->pid], round($row->score, 1));
					$result[$row->uid]->score += $result[$row->uid]->acList[$row->pid];
				} else {
					$result[$row->uid]->attempt[$row->pid] = $row->sid;
					$result[$row->uid]->acList[$row->pid] = round($row->score, 1);
					$result[$row->uid]->score += round($row->score, 1);
				}
			}
			if (isset($result)) usort($result, "team_cmp_OI");
		}
		
		if (isset($result)){
			$rank = $cnt = $score = 0;
			foreach ($result as $row){
				if ( ! $row->isFormal) continue;
				if ($score == $row->score) $cnt++;
				else{
					$rank += $cnt + 1;
					$cnt = 0;
				}
				$row->rank = $rank;
				$score = $row->score;
			}
			return $result;
		}
		return NULL;
	}

	function load_contest_start_time($cid)
	{
		return strtotime($this->db->query("SELECT startTime FROM Contest WHERE cid=?", array($cid))->row()->startTime);
	}
	
	function load_statistic_OI($pids, $uids){
		if ($uids == FALSE) {
			$data = $this->db->query("SELECT sid, uid, name, pid, score FROM Submission
								WHERE pid in ($pids) ORDER BY sid DESC")->result();
		} else {
			$data = $this->db->query("SELECT sid, uid, name, pid, score FROM Submission
								WHERE pid in ($pids) AND uid in ($uids) ORDER BY sid DESC")->result();
		}
		
		foreach ($data as $row) {
			if ( ! isset($result[$row->uid])){
				$result[$row->uid] = new Participant;
				$result[$row->uid]->name = $row->name;
				$result[$row->uid]->score = 0;
				$result[$row->uid]->isFormal = TRUE;
			}
			if (isset($result[$row->uid]->attempt[$row->pid])) {
				$result[$row->uid]->score -= $result[$row->uid]->acList[$row->pid];
				$result[$row->uid]->acList[$row->pid] = max($result[$row->uid]->acList[$row->pid], round($row->score, 1));
				$result[$row->uid]->score += $result[$row->uid]->acList[$row->pid];
			} else {
				$result[$row->uid]->attempt[$row->pid] = $row->sid;
				$result[$row->uid]->acList[$row->pid] = $row->score;
				$result[$row->uid]->score += $row->score;
			}
		}
		if (isset($result)) usort($result, "team_cmp_OI");
		
		if (isset($result)){
			$rank = $cnt = $score = 0;
			foreach ($result as $row){
				if ( ! $row->isFormal) continue;
				if ($score == $row->score) $cnt++;
				else{
					$rank += $cnt + 1;
					$cnt = 0;
				}
				$row->rank = $rank;
				$score = $row->score;
			}
			return $result;
		}
		return NULL;
	}


	function declaration_count($cid){
		return $result = $this->db->query('SELECT COUNT(*) AS count FROM Declaration
										WHERE cid=?',
										array($cid))
										->row()->count;
	}
	
	function load_declaration_list($cid){
		return $this->db->query('SELECT idDeclaration, title, pid FROM Declaration
								WHERE cid=?',
								array($cid))
								->result();
	}
	
	function load_declaration($id){
		return $this->db->query('SELECT * FROM Declaration
								WHERE idDeclaration=?',
								array($id))
								->result();
	}
	
	function load_contest_configuration($cid){
		$data = (array)$this->db->query('SELECT * FROM Contest
										WHERE cid=?',
										array($cid))
										->row();
		$problems = $this->db->query('SELECT * FROM Contest_has_ProblemSet
										WHERE cid=? ORDER BY id',
										array($cid));
		for ($i = 0; $i < $problems->num_rows(); $i++)
			$data['problems'][] = $problems->row($i);
		return $data;
	}
	
	function add($raw, $cid = FALSE){
		foreach ($raw['languages'] as $lang)
			if (isset($languages)) $languages .= ',' . $lang;
			else $languages = $lang;
			
		$data = array(
			'title' => $raw['contest_title'],
			'description' => $raw['description'],
			'startTime' => $raw['start_date'] . ' ' . $raw['start_time'],
			'submitTime' => $raw['submit_date'] . ' ' . $raw['submit_time'],
			'endTime' => $raw['end_date'] . ' ' . $raw['end_time'],
			'contestMode' => $raw['contestMode'],
			'isShowed' => $raw['isShowed'],
			'private' => (int)$raw['contestType'],
			'teamMode' => (int)$raw['teamMode'],
			'language' => $languages
		);
		if ($cid != FALSE) $sql = $sql = $this->db->update_string('Contest', $data, "cid=$cid");
		else $sql = $this->db->insert_string('Contest', $data);
		$this->db->query($sql);
		if ($cid == FALSE) $cid = $this->db->insert_id();
		
		if (isset($raw['pid'])){
			$this->db->query("DELETE FROM Contest_has_ProblemSet
							WHERE cid=?",
							array($cid));
			$cnt = count($raw['pid']);
			for ($now = 0; $now < $cnt; $now++){
				if (isset($problem)) unset($problem);
				$problem['cid'] = $cid;
				$pid = $problem['pid'] = $raw['pid'][$now];
				if ($raw['title'][$now] != '') $problem['title'] = $raw['title'][$now];
				else $problem['title'] = $this->db->query("SELECT title FROM ProblemSet
														WHERE pid=?",
														array($pid))
														->row()->title;
				$problem['score'] = (int)$raw['score'][$now];
				$problem['scoreDecreaseSpeed'] = (int)$raw['speed'][$now];
				$problem['id'] = $now;

				$sql = $this->db->insert_string('Contest_has_ProblemSet', $problem);
				$this->db->query($sql);	
				$this->db->query("UPDATE ProblemSet SET isShowed=0 WHERE pid=?", array($pid));
			}
		}
	}
	
	function delete($cid){
		$this->db->query("DELETE FROM Contest
						WHERE cid=?",
						array($cid));
		$cnt = $this->db->query('SELECT MAX(cid) AS cnt FROM Contest')->row()->cnt + 1;
		if ($cnt == 1) $cnt = 1000;
		$this->db->query('ALTER TABLE Contest AUTO_INCREMENT=?',
						array($cnt));
	}
	
	function contest_to_task($cid) {
		$result = $this->db->query("SELECT title, description, language FROM Contest
									WHERE cid=?", array($cid))->row_array();
		$sql = $this->db->insert_string('Task', $result);
		$this->db->query($sql);
		$tid = $this->db->insert_id();
		
		$result = $this->db->query("SELECT pid, title FROM Contest_has_ProblemSet
									WHERE cid=?", array($cid))->result_array();
		foreach ($result as $row) {
			$row['tid'] = $tid;
			$sql = $this->db->insert_string('Task_has_ProblemSet', $row);
			$this->db->query($sql);
		}
		
	}

}
