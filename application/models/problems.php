<?php

class Problems extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function change_status($pid){
		$this->db->query("UPDATE ProblemSet SET isShowed=1-isShowed WHERE pid=?", array($pid));
	}
	
	function uid($pid){
		return $this->db->query("SELECT uid FROM ProblemSet WHERE pid=?", array($pid))->row()->uid;
	}

	function gen_keyword_lim($keyword)
	{
		if (!$keyword) return 'TRUE';
		$pattern='';
		$keyword=mb_split('\|',$keyword);
		foreach ($keyword as $word)
		{
			$_word = $this->db->escape_like_str($word);
			if ($pattern) $pattern .= ' OR ';
			$pattern .= " title LIKE '%$_word%' OR source LIKE '%$_word%' ";
		}
		return $pattern;
	}

	function gen_filter_lim($filter)
	{
		if (!$filter) return 'TRUE';
		$filter = $this->db->escape($filter);
		return "pid in (SELECT pid FROM Categorization WHERE idCategory=$filter)";
	}

	function gen_bookmark_lim($show_starred, $show_note, $search_note)
	{
		$s = ($show_starred ? 'starred=1' : '');
		$s .= ($show_note ? ($s?' AND ':'') ."note!=''" : '');
		$word = $this->db->escape_like_str($search_note);
		$s .= ($word ? ($s?' AND ':'') ."note LIKE '%$word%'" : '');
		$uid = $this->session->userdata('uid');
		return ( $s ? "pid in (SELECT pid FROM Bookmark WHERE uid=$uid AND $s)" : 'TRUE');
	}

	function gen_uid_lim($uid)
	{
		if (!$uid) return 'TRUE';
		$uid = $this->db->escape($uid);
		return "uid=$uid";
	}

	function gen_admin_lim($admin)
	{
		if ($admin)
			return 'TRUE';
		else
			return 'isShowed=1';
	}
	
	function count($uid=FALSE, $admin=FALSE, $keyword=FALSE, $filter=FALSE, $show_starred=FALSE, $show_note=FALSE, $search_note=FALSE)
	{
		$keyword_lim = $this->gen_keyword_lim($keyword);
		$filter_lim = $this->gen_filter_lim($filter);
		$bookmark_lim = $this->gen_bookmark_lim($show_starred, $show_note, $search_note);
		$uid_lim = $this->gen_uid_lim($uid);
		$admin_lim = $this->gen_admin_lim($admin);

		return $this->db->query("
			SELECT COUNT(*) AS count FROM ProblemSet
			WHERE ($keyword_lim) AND ($filter_lim) AND ($bookmark_lim) AND ($uid_lim) AND ($admin_lim)
			")->row()->count;
	}

	function load_problemset($row_begin, $count, $rev=FALSE, $uid=FALSE, $admin=FALSE, $keyword=FALSE, $filter=FALSE, $show_starred=FALSE, $show_note=FALSE, $search_note=FALSE)
	{
		$keyword_lim = $this->gen_keyword_lim($keyword);
		$filter_lim = $this->gen_filter_lim($filter);
		$bookmark_lim = $this->gen_bookmark_lim($show_starred, $show_note, $search_note);
		$uid_lim = $this->gen_uid_lim($uid);
		$admin_lim = $this->gen_admin_lim($admin);
		$rev_str = ($rev?"DESC":"");

		return $this->db->query("
			SELECT pid, title, source, solvedCount, submitCount, scoreSum AS average, isShowed, uname AS author
			FROM ProblemSet LEFT JOIN (SELECT uid AS uuid, name AS uname FROM User)T ON ProblemSet.uid=T.uuid
			WHERE ($keyword_lim) AND ($filter_lim) AND ($bookmark_lim) AND ($uid_lim) AND ($admin_lim)
			ORDER BY isShowed ASC, pid $rev_str LIMIT ?, ?
			", array($row_begin, $count))->result();
	}
	
	function load_dataconf($pid){
		return $this->db->query("SELECT title, dataConfiguration FROM ProblemSet WHERE pid=?", array($pid))->row();
	}
	
	function save_dataconf($pid, $data){
		$sql = $this->db->update_string('ProblemSet', array('dataConfiguration' => $data), "pid=$pid");
		$this->db->query($sql);
	}
	
	function load_code_size_limit($pid){
		$result = $this->db->query("SELECT codeSizeLimit FROM ProblemSet WHERE pid=?", array($pid));
		if ($result->num_rows() == 0) return FALSE;
		return $result->row()->codeSizeLimit;
	}
	
	function load_status($uid, $pids)
	{
		if ($pids=='()') return NULL;
		return $this->db->query("SELECT min(status) AS status, pid FROM Submission WHERE uid=? AND pid in $pids AND status>-4 GROUP BY pid", array($uid))->result();
	}

	function load_bookmark($uid, $pids)
	{
		if ($pids=='()') return NULL;
		return $this->db->query("SELECT pid, starred, note FROM Bookmark WHERE uid=? AND pid in $pids", array($uid))->result();
	}
	
	function load_problem($pid){
		$result = $this->db->query("SELECT * from ProblemSet WHERE pid=?", array($pid));
		if ($result->num_rows() == 0) return FALSE;
		return $result->row();
	}
	
	function load_limits($pid){
		$result = $this->db->query("SELECT pid, title, dataConfiguration, isShowed from ProblemSet WHERE pid=?", array($pid));
		if ($result->num_rows() == 0) return FALSE;
		if ($this->user->is_admin() || $result->row()->isShowed == 1) return $result->row();
		else return FALSE;
	}
	
	function add($data, $pid = 0){
		$cnt = $this->db->query('SELECT MAX(pid) AS cnt FROM ProblemSet')->row()->cnt + 1;
		if ($cnt == 1) $cnt = 1000;
		$this->db->query('ALTER TABLE ProblemSet AUTO_INCREMENT=?', array($cnt));

		if ($pid == 0){
			$data['uid'] = $this->user->uid();
			$sql = $this->db->insert_string('ProblemSet', $data);
		}else $sql = $this->db->update_string('ProblemSet', $data, "pid=$pid");
		$this->db->query($sql);
		
		return $pid == 0 ? $this->db->insert_id() : $pid;
	}
	
	function delete($pid){
		$this->db->query("DELETE FROM ProblemSet WHERE pid=?", array($pid));
	}
	
	function is_showed($pid){
		return $this->db->query("SELECT isShowed FROM ProblemSet WHERE pid=?", array($pid))->row()->isShowed;
	}
	
	function load_problem_submission($pid){
		return $this->db->query("SELECT sid, cid FROM Submission WHERE pid=?", array($pid))->result();
	}
	
	function add_solution($pid, $filename) {
		$sql = $this->db->insert_string('Solution', array('uid' => $this->user->uid(), 'pid' => $pid, 'filename' => $filename));
		$this->db->query($sql);
	}
	
	function load_solutions($pid) {
		//$is_accepted = $this->misc->is_accepted($this->session->userdata('uid'), $pid);
		
		//if ($is_accepted)
			return $this->db->query("SELECT idSolution, uid, filename FROM Solution WHERE pid=?",
									array($pid))->result();
	}
	
	function delete_solution($idSolution) {
		$data = $this->db->query("SELECT pid, filename FROM Solution WHERE idSolution=?",
									array($idSolution))->row();
		$target_file = $this->config->item('data_path') . $data->pid . '/solution/' . $data->filename;
		
		if (file_exists($target_file)) unlink($target_file);
		
		$this->db->query("DELETE FROM Solution WHERE idSolution=?",
						array($idSolution));
	}
	
	function load_solution_uid($idSolution) {
		return $this->db->query("SELECT uid FROM Solution WHERE idSolution=?",
								array($idSolution))->row()->uid;
	}

	function load_IO_mode($pid)
	{
		$dataConfigStr=$this->db->query("SELECT dataConfiguration FROM ProblemSet where pid=?",array($pid))->row()->dataConfiguration;
		$dataConfig=json_decode($dataConfigStr);
		return $dataConfig->IOMode;
	}

	function update_bookmark($pid)
	{
		$star=($this->input->post('star')=='true'?1:0);
		$note=$this->input->post('note');
		$uid=$this->session->userdata('uid');
		$this->db->query("DELETE FROM Bookmark WHERE pid=? AND uid=?", array($pid,$uid));
		if ($star || $note)
			$this->db->query("INSERT INTO Bookmark (pid,uid,starred,note) VALUES (?,?,?,?)", array($pid,$uid,$star,$note));
	}

	function load_title($pid)
	{
		return $this->db->query("SELECT title FROM ProblemSet WHERE pid=?", array($pid))->row()->title;
	}
}
