<?php

class Participant{
	var $score, $penalty, $rank, $uid, $name, $isFormal, $attempt, $acList, $submitTime;
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
		$uid = $this->session->userdata('uid');
		if (
			$this->db->query("SELECT priviledge FROM User WHERE uid=?", array($uid))->row()->priviledge == 'restricted' &&
			$this->db->query("SELECT COUNT(*) AS cnt FROM Contest_has_ProblemSet WHERE cid=? AND pid NOT IN (SELECT pid FROM Allowed_Problem WHERE uid=?)", array($cid, $uid))->row()->cnt
		) return FALSE;
		
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
			$data->now = time();
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

			if ($data->isTemplate) {
				$this->load->model('misc');
				$data->maxStartTime = $this->misc->format_datetime($endTime - $this->load_relative_time($cid)->endAfter);
			}

			return $data;
		}
	}
	
	function count(){
		return $this->db->query("SELECT COUNT(*) AS count FROM Contest
								WHERE isShowed = 1;")
								->row()->count;
	}
	
	function load_contests_list($row_begin, $count){
		return $this->db->query("SELECT cid, title, startTime, submitTime, endTime, contestMode, private, isTemplate FROM Contest
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
	
	function load_contest_title($cid)
	{
		return $this->db->query("SELECT title FROM Contest WHERE cid=?", array($cid))->row()->title;
	}
	
	function load_problems_in_contests($problems)
	{
		$arr = array();
		foreach ($problems as $row) $arr[]=$row->pid;
		$this->db->select('cid, pid');
		$this->db->order_by('cid desc, id asc');
		$this->db->where_in('pid',$arr);
		return $this->db->get('Contest_has_ProblemSet')->result();
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
		if ($teamMode == TRUE) {
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
			
		} else {
			$data = $this->db->query("SELECT uid, name, pid, submitTime, status FROM Submission
									  WHERE cid=? ORDER BY sid",
									  array($cid))
									  ->result();
			foreach ($data as $row){
				if (!isset($result[$row->uid])){
					$result[$row->uid] = new Participant;
					$result[$row->uid]->uid = $row->uid;
					$result[$row->uid]->name = $row->name;
					$result[$row->uid]->penalty = 0;
					$result[$row->uid]->score = 0;
					$result[$row->uid]->isFormal = TRUE;
				}
				if (isset($result[$row->uid]->acList[$row->pid])) continue;
				
				if (!isset($result[$row->uid]->attempt[$row->pid])) $result[$row->uid]->attempt[$row->pid] = 0;
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
		
		if (isset($result)) {
			$rank = $cnt = $score = $penalty = 0;
			foreach ($result as $row){
				if (!$row->isFormal) continue;
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
		$now = time();

		$allEndTime = $this->load_contest_status($cid)->endTime;

		if ($now <= strtotime($info->endTime) && !$this->user->is_admin() && $info->contestMode == 'OI Traditional') return FALSE;

		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();

		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode) {
			$data = $this->db->query("SELECT * FROM Team
									WHERE cid=? ORDER BY score DESC",
									array($cid))
									->result();
			foreach ($data as $row) {
				$result[$row->idTeam]->name = $row->name;
				$result[$row->idTeam]->score = $row->score;
				$result[$row->idTeam]->penalty = $row->penalty;
				$result[$row->idTeam]->isFormal = $row->isFormal;
			}
		} else {
			$data = $this->db->query("SELECT sid, uid, name, pid, score FROM Submission
									  WHERE cid=? ORDER BY sid DESC",
									  array($cid))
									  ->result();

			foreach ($data as $row) {
				if (!isset($result[$row->uid])) {
					$result[$row->uid] = new Participant;
					$result[$row->uid]->uid = $row->uid;
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

		if (isset($result)) {
			$rank = $cnt = 0;
			$score = -1;
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
		$now = time();
		if ($now <= strtotime($info->endTime) && !$this->user->is_admin() && $info->contestMode == 'OI Traditional') return FALSE;
		
		$info = $this->db->query("SELECT teamMode, startTime FROM Contest
								WHERE cid=?",
								array($cid))
								->row();
		$teamMode = $info->teamMode;
		$startTime = strtotime($info->startTime);
		if ($teamMode == TRUE) {
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

		} else {
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
				if (!isset($result[$row->uid])){
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
				if (!$row->isFormal) continue;
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
			if (!isset($result[$row->uid])){
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
		
		if (isset($result)) {
			$rank = $cnt = $score = 0;
			foreach ($result as $row) {
				if (!$row->isFormal) continue;
				if ($score == $row->score) $cnt++;
				else {
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
		return $this->db->query('SELECT cid, idDeclaration, title, pid, postTime FROM Declaration
								WHERE cid=?',
								array($cid))
								->result();
	}
	
	function load_declaration($cid, $id){
		return $this->db->query('SELECT * FROM Declaration
								WHERE cid=? AND idDeclaration=?',
								array($cid, $id))
								->row();
	}

	function add_declaration($cid, $pid, $title, $decl)
	{
		$cnt = $this->db->query("SELECT MAX(idDeclaration) AS cnt FROM Declaration")->row()->cnt+1;
		$this->db->query(
			"INSERT INTO Declaration VALUES (?,?,?,?,?,NOW())",
			array($cnt, $cid, $pid, $title, $decl)
		);
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
			'language' => $languages,
			'isTemplate' => $raw['isTemplate'],
			'submitAfter' => $raw['submitAfter'],
			'endAfter' => $raw['endAfter'],
		);

		if ($raw['isTemplate']) {
			$this->load->model('misc');
			$data['submitTime'] = $data['startTime'];
			$data['endTime'] = $this->misc->format_datetime(strtotime($data['endTime']) + strtotime('1970-01-01 ' . $data['endAfter'] . ' +0000'));
		}

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
				if (strtotime($data['endTime'])>time())
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

	function id_in_contest_to_pid($cid, $id)
	{
		return $this->db->query(
			"SELECT pid FROM Contest_has_ProblemSet WHERE cid=? AND id=?",
			array($cid,$id)
		)->row()->pid;
	}

	function load_forum($cid)
	{
		$data = $this->db->query("SELECT * FROM Contest_Forum WHERE cid=? AND replyTo IS NULL", array($cid))->result();
		$this->load->model('user');
		foreach ($data as &$row)
			$row->avatar = $this->user->load_avatar($row->uid);
		return $data;
	}

	function load_reply($id)
	{
		$data = $this->db->query("SELECT * FROM Contest_Forum WHERE replyTo=?", array($id))->result();
		foreach ($data as &$row)
			$row->reply = $this->load_reply($row->id);
		return $data;
	}
	
	function add_post($cid, $title, $content, $replyTo = NULL)
	{
		$this->load->model('user');
		$this->db->query("INSERT INTO Contest_Forum
			(cid, uid, user, date, title, content, replyTo) VALUE
			(?,   ?,   ?,    NOW(),    ?,     ?,     ?)",
			array($cid,$this->user->uid(),$this->user->username(),$title,$content,$replyTo)
		);
		if (isset($replyTo))
		{
			$username = $this->user->username();
			$tmp = $this->db->query("SELECT uid, user FROM Contest_Forum WHERE id=?", array($replyTo))->row();
			$this->db->query("UPDATE Contest_Forum SET replyCnt=replyCnt+1 WHERE id=?", array($replyTo));
			$this->user->save_mail(array(
				'title' => 'You have new reply',
				'content' => "<span class='label label-info'>$username</span> replied `$content` to you at <a href='#contest/forum/$cid'>this page</a>",
				'to_user' => $tmp->user,
				'to_uid' => $tmp->uid,
				'from_user' => 'root',
				'from_uid' => $this->user->load_uid('root'),
				'sendTime' => date("Y-m-d H:i:s")
			));
		}
	}

	function modify_post($id, $title, $content)
	{
		$uid = $this->db->query("SELECT uid FROM Contest_Forum WHERE id=?", array($id))->row()->uid;
		$this->load->model('user');
		if ($this->user->uid()==$uid)
		{
			$this->db->query("UPDATE Contest_Forum SET title=?, content=? WHERE id=?", array($title, $content,$id));
			return TRUE;
		}
		return FALSE;
	}

	function del_post($id)
	{
		$tmp = $this->db->query("SELECT uid, replyTo FROM Contest_Forum WHERE id=?", array($id))->row();
		$uid = $tmp->uid;
		$replyTo = $tmp->replyTo;
		$this->load->model('user');
		if ($this->user->uid()==$uid || $this->user->is_admin())
		{
			$this->db->query("DELETE FROM Contest_Forum WHERE id=?", array($id));
			if (isset($replyTo))
				$this->db->query("UPDATE Contest_Forum SET replyCnt=replyCnt-1 WHERE id=?", array($replyTo));
			return TRUE;
		}
		return FALSE;
	}
	
	function upd_estimate($cid, $pid, $score)
	{
		$endTime = $this->db->query("SELECT endTime FROM Contest WHERE cid=?", array($cid))->row()->endTime;
		$now = time();
		if ($now > strtotime($endTime)) return false;
		$uid = $this->user->uid();
		$cnt = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM Estimate WHERE cid=? AND pid=? AND uid=?",
			array($cid, $pid, $uid)
		)->row()->cnt;
		if ($cnt)
			$this->db->query(
				"DELETE FROM Estimate WHERE cid=? AND pid=? AND uid=?",
				array($cid, $pid, $uid)
			);
		$this->db->query(
			"INSERT INTO Estimate (cid, pid, uid, score) VALUES (?, ?, ?, ?)",
			array($cid, $pid, $uid, $score)
		);
		return true;
	}

	function load_estimate($cid)
	{
		$data = $this->db->query("SELECT * FROM Estimate WHERE cid=?", array($cid))->result();
		$ret = array();
		foreach ($data as $row)
		{
			if (!isset($ret[$row->uid])) $ret[$row->uid] = array();
			$ret[$row->uid][$row->pid] = (int)$row->score;
		}
		foreach ($ret as &$row)
		{
			$sum = 0;
			foreach ($row as $prob)
				$sum += $prob;
			$row['sum'] = $sum;
		}
		return $ret;
	}

	function load_contest_mode($cid)
	{
		return $this->db->query("SELECT contestMode FROM Contest WHERE cid=?", array($cid))->row()->contestMode;
	}

	function is_template_contest($cid)
	{
		$temp = $this->db->query("SELECT isTemplate FROM Contest WHERE cid=?", array($cid));
		if (!$temp->num_rows()) return FALSE;
		return $temp->row()->isTemplate == 1;
	}

	function load_relative_time($cid)
	{
		$res = $this->db->query("SELECT submitAfter, endAfter FROM Contest WHERE cid=?", array($cid))->row();
		$res->submitAfter = strtotime('1970-01-01 ' . $res->submitAfter . ' +0000');
		$res->endAfter = strtotime('1970-01-01 ' . $res->endAfter . ' +0000');
		return $res;
	}

	function load_template_contest_status($cid, $uid)
	{
 		if (!$this->is_template_contest($cid)) return FALSE;

		$res = $this->load_contest_status($cid);
		$temp = $this->db->query("SELECT startTime FROM Contest_has_User WHERE cid=? AND uid=?", array($cid, $uid));
		if (!$temp->num_rows()) return FALSE;

		$res->startTime = $temp->row()->startTime;
		$det = $this->load_relative_time($cid);

		$res->submitTime = strtotime($res->startTime) + $det->submitAfter;
		$res->endTime = strtotime($res->startTime) + $det->endAfter;

		$res->now = time();
		if ($res->now > $res->endTime) {
			$res->status = '<span class="label label-success">Ended</span>';
		} else {
			$res->status = '<span class="label label-important">Running</span>';
			$res->running = TRUE;
		}

		$this->load->model('misc');
		$res->submitTime = $this->misc->format_datetime($res->submitTime);
		$res->endTime = $this->misc->format_datetime($res->endTime);

		return $res;
	}

	function can_start_contest($cid, $uid) {
		if (!$this->is_template_contest($cid)) return FALSE;
		if ($this->load_template_contest_status($cid, $uid)) return FALSE;
		$info = $this->load_contest_status($cid);
		if ($info->now < strtotime($info->startTime)) return FALSE;
		$this->load->model('misc');
		if ($info->now + $this->load_relative_time($cid)->endAfter > strtotime($info->endTime)) return FALSE;
		return TRUE;
	}

	function start_contest($cid, $uid)
	{
		if (!$this->can_start_contest($cid, $uid)) return FALSE;
		$this->db->query("INSERT INTO Contest_has_User (cid, uid, startTime) VALUES (?, ?, NOW())", array($cid, $uid));
		$endTime = strtotime($this->load_contest_status($cid)->endTime);
		$actEndTime = strtotime($this->load_template_contest_status($cid, $uid)->endTime);
		if ($actEndTime > $endTime) {
			$this->db->query("DELETE FROM Contest_has_User WHERE cid=? AND uid=?", array($cid, $uid));
			return FALSE;
		}
		return TRUE;
	}
}
