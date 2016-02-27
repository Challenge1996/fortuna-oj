<?php

require_once 'application/vendor/autoload.php';
require_once 'application/myjob.php';

function compare_submission($lhs, $rhs) {
	if ($lhs[0]->score != $rhs[0]->score) return $lhs[0]->score < $rhs[0]->score ? 1 : -1;
	if ($lhs[0]->time != $rhs[0]->time) return $lhs[0]->time > $rhs[0]->time ? 1 : -1;
	if ($lhs[0]->memory != $rhs[0]->memory) return $lhs[0]->memory > $rhs[0]->memory ? 1 : -1;
	return $lhs[0]->sid > $rhs[0]->sid ? 1 : -1;
}

class Submission extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function rejudge($sid){
		$data = $this->db->query("SELECT cid, pid, uid, status, score, ACCounted, langDetail FROM Submission WHERE sid=?",array($sid))->row();

		if ($data->ACCounted)
		{
			$this->db->query("UPDATE Submission SET ACCounted=0 WHERE sid=?", array($sid));
			$this->db->query("UPDATE ProblemSet SET scoreSum=scoreSum-?,submitCount=submitCount-1 WHERE pid=?", array($data->score, $data->pid));
			$this->db->query("UPDATE User SET submitCount=submitCount-1 WHERE uid=?", array($data->uid));
			if ($data->status==0)
			{
				$this->db->query("UPDATE User SET solvedCount=solvedCount-1 WHERE uid=?", array($data->uid));
				$this->db->query("UPDATE ProblemSet SET solvedCount=solvedCount-1 WHERE pid=?", array($data->pid));
				if ($this->db->query("SELECT COUNT(*) AS cnt FROM Submission WHERE uid=? AND status=0", array($data->uid))->row()->cnt==1)
					$this->db->query("UPDATE User SET acCount=acCount-1 WHERE uid=?", array($data->uid));
			}
		}
		
		$pushTime = date("Y-m-d H:i:s");
		$this->db->query("UPDATE Submission SET score=0,status=-1,time=NULL,memory=NULL,codeLength=NULL,judgeResult=NULL,pushTime=? WHERE sid=?",array($pushTime,$sid));

		Resque::setBackend('127.0.0.1:6379');
		Resque::enqueue('default', 'myjob', array(
			'passwd' => $this->config->item('local_passwd'),
			'oj_name' => $this->config->item('oj_name'),
			'pid' => (int)$data->pid,
			'sid' => (int)$sid,
			'lang' => $data->langDetail,
			'servers' => $this->config->item('servers'),
			'pushTime' => $pushTime
		));
		
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
		$temp = null;
		if ($this->user->is_admin())
			$temp = $this->db->query("SELECT * FROM Submission WHERE pid=? AND (status >= 0 OR status <= -3) ORDER BY -score, time, memory, sid", array($pid))->result();
		else
			$temp = $this->db->query("SELECT * FROM Submission WHERE pid=? AND (status >= 0 OR status <= -3) AND isShowed=1 ORDER BY -score, time, memory, sid", array($pid))->result();
		$result = array();
		foreach ($temp as $row) 
			$result[$row->uid][] = $row;
		uasort($result, 'compare_submission');
		return array_slice($result,$row_begin,$count);
	}
	
	private function filter_to_string($filter){
		$conditions = '';
		if (isset($filter['problems'])){
			$conditions .= ' AND pid IN (';
			foreach ($filter['problems'] as $pid) $conditions .= $this->db->escape($pid) . ',';
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['users'])){
			$conditions .= ' AND name IN (';
			foreach ($filter['users'] as $name) $conditions .= $this->db->escape($name) . ',';
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['status'])){
			$conditions .= ' AND status IN (';
			foreach ($filter['status'] as $status) $conditions .= $this->db->escape($status) . ',';
			$conditions[strlen($conditions) - 1] = ')';
		}
		if (isset($filter['languages'])){
			$conditions .= ' AND language IN (';
			foreach ($filter['languages'] as $language) $conditions .= $this->db->escape($language) . ',';
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
							codeLength, submitTime, language, isShowed, private, sim FROM Submission
							WHERE (cid IS NULL OR cid IN
							(SELECT cid FROM Contest WHERE UNIX_TIMESTAMP(endTime)<UNIX_TIMESTAMP()))
							$conditions ORDER BY sid DESC LIMIT ?, ?",
					array($row_begin, $count));
		return $result->result();
	}

	function allow_view_code($sid)
	{
		$this->load->model('problems');
		$this->load->model('contests');
		$result = $this->db->query("SELECT uid, pid, cid, private FROM Submission WHERE sid=?", array($sid));
		if ($result->num_rows() == 0) return FALSE; else $result = $result->row();
		if ($this->user->is_admin()) return TRUE;
		$uid = $this->session->userdata('uid');

		if (isset($result->cid)) {
			$cid = $result->cid;
			$contest_status = 
				($this->contests->is_template_contest($cid) ?
				 $this->contests->load_template_contest_status($cid, $uid) :
				 $this->contests->load_contest_status($cid));
			if (!$contest_status || $contest_status->running) return FALSE;
		}

		if (!$this->problems->allow($result->pid)) return FALSE;
		$accepted = $this->db->query("SELECT * FROM Submission WHERE pid=? AND uid=? AND status=0", array($result->pid, $uid))->num_rows() > 0;
		if ($this->db->query("SELECT pid FROM ProblemSet WHERE pid=? AND isShowed=1", array($result->pid))->num_rows() == 0) $accepted = FALSE;
		if ($result->uid != $uid && $result->private != 0 && !$accepted) return FALSE;
		return TRUE;
	}
	
	function load_code($sid){
		if (!$this->allow_view_code($sid)) return FALSE;
		$show = array();
		$front = intval($sid/10000);
		$back = $sid%10000;
		$path = $this->config->item('code_path') . "$front/$back";
		$files = scandir($path);
		foreach ($files as $file)
		{
			if ($file == '.ast') continue;
			if (! is_file("$path/$file")) continue;
			if (filesize("$path/$file") > 10*1024)
				$show[$file] = null;
			else
				$show[$file] = file_get_contents("$path/$file");
		}
		uksort($show, 'strnatcmp');
		return $show;
	}
	
	function load_result($sid){
		$result = $this->db->query("SELECT uid, pid, cid, judgeResult AS result
									FROM Submission
									WHERE sid=?",
									array($sid));
									
		if ($result->num_rows() == 0) return FALSE;
		
		$result = $result->row();
		$this->load->model('problems');
		if ($result->uid == $this->session->userdata('uid') && $this->problems->allow($result->pid) || $this->session->userdata('priviledge') == 'admin') {
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

	function load_pushTime($sid)
	{
		return $this->db->query("SELECT pushTime FROM Submission WHERE sid=?", array($sid))->row()->pushTime;
	}

	function upd_status($sid, $stat)
	{
		$this->db->query("UPDATE Submission SET status=? WHERE sid=?", array($stat,$sid));
	}
	
	function judge_done($sid, $pid, $data)
	{
		$got = $this->db->query("SELECT uid, pushTime FROM Submission WHERE sid=?", array($sid))->row();
		if ($got->pushTime != $data['pushTime']) return;
		unset($data['pushTime']);
		$uid = $got->uid;
		$data['sim'] = $this->gen_sim($sid, $uid, $pid, strtolower($data['language']));
		$this->db->query($this->db->update_string('Submission',$data,"sid=$sid"));
		$notEnd = false;
		$this->load->model('contests');
		$ret = $this->contests->load_problems_in_contests(array((object)array('pid'=>$pid)));
		$now = strtotime('now');
		foreach ($ret as $row)
		{
			$res = $this->db->query('SELECT startTime,endTime FROM Contest WHERE cid=?', array($row->cid))->row();
			if (strtotime($res->startTime)<=$now && strtotime($res->endTime)>=$now)
			{
				$notEnd = true;
				break;
			}
		}
		if (!$notEnd)
		{
			$this->db->query("UPDATE Submission SET ACCounted=1 WHERE sid=?", array($sid));
			$this->db->query("UPDATE ProblemSet SET scoreSum=scoreSum+?,submitCount=submitCount+1 WHERE pid=?", array($data['score'],$pid));
			$this->db->query("UPDATE User SET submitCount=submitCount+1 WHERE uid=?",array($uid));
			if ($data['status']===0 && !$notEnd)
			{
				$this->db->query("UPDATE User SET solvedCount=solvedCount+1 WHERE uid=?", array($uid));
				$this->db->query("UPDATE ProblemSet SET solvedCount=solvedCount+1 WHERE pid=?", array($pid));
				if ($this->db->query("SELECT COUNT(*) AS cnt FROM Submission WHERE uid=? AND status=0 AND pid=?", array($uid, $pid))->row()->cnt==1)
					$this->db->query("UPDATE User SET acCount=acCount+1 WHERE uid=?", array($uid));
			}
		}
	}
	
	function status_id($status)
	{
		switch (str_replace('_',' ',strtolower($status)))
		{
			case 'output not found':
				return -4;
			case 'partially accepted':
			case 'partially accept':
			case 'partial accepted':
			case 'partial accept':
				return -3;
			case 'running':
				return -2;
			case 'pending':
				return -1;
			case 'accept':
			case 'accepted':
				return 0;
			case 'presentation error':
				return 1;
			case 'wrong answer':
				return 2;
			case 'checker error':
			case 'spj error':
			case 'special judge error':
				return 3;
			case 'output limit exceeded':
			case 'output limit exceed' :
				return 4;
			case 'memory limit exceeded':
			case 'memory limit exceed':
				return 5;
			case 'time limit exceeded':
			case 'time limit exceed':
				return 6;
			case 'runtime error':
			case 'run time error':
			case 'dangerous syscall':
				return 7;
			case 'compile error':
			case 'compiling error':
				return 8;
			case 'internal error':
			default :
				return 9;
		}
	}

	function gen_sim($sid, $uid, $pid, $lang)
	{
		if ($lang != 'pascal') return '';
		$data = $this->db->query("SELECT sid,name FROM Submission WHERE uid!=? AND pid=? AND language=?", array($uid, $pid, ucfirst($lang)))->result();
		$current = null;
		$fileA = $this->config->item('code_path') . intval($sid/10000) . '/' . ($sid%10000) . '/.ast';
		foreach ($data as $row)
		{
			$fileB = $this->config->item('code_path') . intval($row->sid/10000) . '/' . ($row->sid%10000) . '/.ast';
			if (!file_exists($fileB)) continue;
			$ret_code = 0;
			$sim = system("yast_test $fileA $fileB", $ret_code);
			if ($sim!='' && !$ret_code) $sim = (int)((1-floatval($sim))*100);
			if (!$current || $sim > $current["similarity"])
				$current = array("sid" => $row->sid, "similarity" => $sim, "name" => $row->name);
		}
		return json_encode($current);
	}
}
