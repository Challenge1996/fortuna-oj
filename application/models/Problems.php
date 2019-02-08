<?php

class Problems extends CI_Model{

	function __construct(){
		parent::__construct();
	}

	function approve_review($pid){
		$this->db->query("UPDATE ProblemSet SET isShowed=1 WHERE pid=?", array($pid));

		$this->load->model('user');
		$accepter = $this->user->username();
		$submitter_id = $this->uid($pid);
		$this->user->save_mail(array(
			'title' => 'Your problem has been accepted',
			'content' => "<span class='label label-info'>$accepter</span> accepted your problem <a href='#/main/show/$pid'>$pid</a>",
			'to_user' => $this->user->load_username($submitter_id),
			'to_uid' => $submitter_id,
			'from_user' => 'noreply',
			'from_uid' => $this->user->load_uid('noreply'),
			'sendTime' => date("Y-m-d H:i:s")
		));
	}

	function decline_review($pid, $msg){
		$this->db->query("UPDATE ProblemSet SET isShowed=0, reviewing=0 WHERE pid=?", array($pid));

		$this->load->model('user');
		$rejecter = $this->user->username();
		$submitter_id = $this->uid($pid);
		$this->user->save_mail(array(
			'title' => 'Your problem has been rejected',
			'content' => "<span class='label label-info'>$rejecter</span> rejecteded your problem <a href='#/main/show/$pid'>$pid</a> for the reason that:<br />$msg",
			'to_user' => $this->user->load_username($submitter_id),
			'to_uid' => $submitter_id,
			'from_user' => 'noreply',
			'from_uid' => $this->user->load_uid('noreply'),
			'sendTime' => date("Y-m-d H:i:s")
		));
	}
	
	function change_status($pid){
		if ($this->user->is_admin())
			$this->db->query("UPDATE ProblemSet SET isShowed=1-isShowed WHERE pid=?", array($pid));
		else {
			if ($this->is_showed($pid))
				$this->db->query("UPDATE ProblemSet SET isShowed=0, reviewing=0 WHERE pid=?", array($pid));
			else
				$this->db->query("UPDATE ProblemSet SET reviewing=1-reviewing WHERE pid=?", array($pid));
		}
	}

	function change_nosubmit($pid){
		$this->db->query("UPDATE ProblemSet SET noSubmit=1-noSubmit WHERE pid=?", array($pid));
	}
	
	function uid($pid){
		return $this->db->query("SELECT uid FROM ProblemSet WHERE pid=?", array($pid))->row()->uid;
	}

	private function gen_keyword_lim($keyword)
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

	private function gen_filter_lim($filter)
	{
		if (!$filter) return 'TRUE';
		if (! is_numeric($filter))
		{
			$filter = array_map(function($x){return $this->db->escape($x);}, json_decode($filter));
			$count = count($filter);
			$filter = implode(',', $filter);
		} else
			$count = 1;
		return "pid in 
			(SELECT pid FROM 
				(SELECT pid, COUNT(*) AS count FROM Categorization WHERE idCategory in ($filter) GROUP BY pid)T
			WHERE count = '$count')";
	}

	private function gen_bookmark_lim($show_starred, $show_note, $search_note)
	{
		$this->load->model('user');
		$s = ($show_starred ? 'starred=1' : '');
		$s .= ($show_note ? ($s?' AND ':'') ."note!=''" : '');
		$word = $this->db->escape_like_str($search_note);
		$s .= ($word ? ($s?' AND ':'') ."note LIKE '%$word%'" : '');
		$uid = $this->user->uid();
		return ( $s ? "pid in (SELECT pid FROM Bookmark WHERE uid=$uid AND $s)" : 'TRUE');
	}

	private function gen_admin_only_lim($yes)
	{
		if (!$yes) return 'TRUE';
		return "uid IN (SELECT uid FROM User WHERE priviledge='admin')";
	}

	private function gen_user_only_lim($yes)
	{
		if (!$yes) return 'TRUE';
		return "uid IN (SELECT uid FROM User WHERE priviledge!='admin')";
	}

	private function gen_admin_lim($admin)
	{
		$this->load->model("user");
		if ($admin)
			if ($this->user->is_admin()) // admin won't be bothered by problems that haven't been submitted to review
				return 'isShowed=1 OR reviewing=1 OR uid IN (SELECT uid FROM User WHERE priviledge="admin")';
			else
				return 'uid=' . $this->user->uid();
		else
			return 'isShowed=1';
	}

	private function gen_restricted_lim()
	{
		$this->load->model('user');
		$uid = $this->user->uid();
		$priv = $this->db->query("SELECT priviledge FROM User WHERE uid=?", array($uid))->row()->priviledge;
		if ($priv == 'restricted')
			return "pid IN (SELECT pid FROM Allowed_Problem WHERE uid=$uid)";
		else
			return 'TRUE';
	}
	
	private function gen_lim($config)
	{
		return (object)array(
			'rev' => (isset($config->rev) && $config->rev ? 'DESC' : ''),
			'admin_only' => $this->gen_admin_only_lim(isset($config->admin_only) ? $config->admin_only : false),
			'user_only' => $this->gen_user_only_lim(isset($config->user_only) ? $config->user_only : false),
			'admin' => $this->gen_admin_lim(isset($config->admin) ? $config->admin : false),
			'keyword' => $this->gen_keyword_lim(isset($config->keyword) ? $config->keyword : false),
			'filter' => $this->gen_filter_lim(isset($config->filter) ? $config->filter : false),
			'bookmark' => $this->gen_bookmark_lim(
							isset($config->show_starred) ? $config->show_starred : false,
							isset($config->show_note) ? $config->show_note : false,
							isset($config->search_note) ? $config->search_note : false
						  ),
			'restricted' => $this->gen_restricted_lim()
		);
	}

	function count($config)
	{
		$config = $this->gen_lim($config);
		return $this->db->query("
			SELECT COUNT(*) AS count FROM ProblemSet
			WHERE ($config->keyword) AND ($config->filter) AND ($config->bookmark) AND ($config->admin_only) AND ($config->user_only) AND
			      ($config->admin) AND ($config->restricted)
			")->row()->count;
	}

	function load_problemset($row_begin, $count, $config)
	{
		$config = $this->gen_lim($config);

		return $this->db->query("
			SELECT pid, title, source, solvedCount, submitCount, scoreSum AS average, isShowed, reviewing, noSubmit, uname AS author, uid
			FROM ProblemSet LEFT JOIN (SELECT uid AS uuid, name AS uname FROM User)T ON ProblemSet.uid=T.uuid
			WHERE ($config->keyword) AND ($config->filter) AND ($config->bookmark) AND ($config->admin_only) AND ($config->user_only) AND
			      ($config->admin) AND ($config->restricted)
			ORDER BY isShowed ASC, pid $config->rev LIMIT ?, ?
			", array($row_begin, $count))->result();
	}
	
	function load_dataconf($pid, $fix = false){ // this function may throw MyException
		$data = $this->db->query("SELECT title, dataConfiguration, dataGroup, confCache FROM ProblemSet WHERE pid=?", array($pid))->row();
		if ($fix && (!isset($data->dataGroup) || !$data->dataGroup || !isset($data->confCache) || !$data->confCache))
		{
			$got = $this->form2script(json_decode($data->dataConfiguration));
			$data->dataGroup = $got->group;
			$data->confCache = $this->save_script($pid, $got->init, $got->run);
			$this->problems->mark_update($pid);
			$this->problems->save_dataconf($pid, $data->dataConfiguration, $data->dataGroup, $data->confCache);
		}
		return $data;
	}
	
	function save_dataconf($pid, $traditional, $dataGroup, $confCache){
		$sql = $this->db->update_string('ProblemSet', array('dataConfiguration'=>$traditional, 'dataGroup'=>$dataGroup, 'confCache'=>$confCache), "pid=$pid");
		$this->db->query($sql);
	}

	function load_group_matching($pid)
	{
		$list = $this->db->query("SELECT dataGroup FROM ProblemSet WHERE pid=?", array($pid))->row();
		$list = json_decode($list->dataGroup);
		$ret = array();
		// suppose there is not a test not belonging to any case.
		foreach ($list as $case => $tests)
			foreach ($tests as $test)
				$ret[$test] = $case;
		return $ret;
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
	
	function load_problem($pid){ // may throw
		$result = $this->db->query("SELECT * from ProblemSet WHERE pid=?", array($pid));
		if ($result->num_rows() == 0) return FALSE;
		$data = $result->row();
		
		$path = $this->config->item('problem_path') . $pid;
		$data->problemDescription   = file_get_contents("$path/problemDescription.html");
		$data->inputDescription     = file_get_contents("$path/inputDescription.html");
		$data->outputDescription    = file_get_contents("$path/outputDescription.html");
		$data->inputSample          = file_get_contents("$path/inputSample.html");
		$data->outputSample         = file_get_contents("$path/outputSample.html");
		$data->dataConstraint       = file_get_contents("$path/dataConstraint.html");
		$data->hint                 = file_get_contents("$path/hint.html");
		
		if (!isset($data->dataGroup) || !$data->dataGroup || !isset($data->confCache) || !$data->confCache)
		{
			$got = $this->form2script(json_decode($data->dataConfiguration));
			$data->dataGroup = $got->group;
			$data->confCache = $this->save_script($pid, $got->init, $got->run);
			$this->problems->mark_update($pid);
			$this->problems->save_dataconf($pid, $data->dataConfiguration, $data->dataGroup, $data->confCache);
		}
		return $data;
	}
	
	function add($data, $pid = 0){
		$problemDescription = $data['problemDescription'];
		$inputDescription = $data['inputDescription'];
		$outputDescription = $data['outputDescription'];
		$inputSample = $data['inputSample'];
		$outputSample = $data['outputSample'];
		$dataConstraint = $data['dataConstraint'];
		$hint = $data['hint'];
		unset($data['problemDescription']);
		unset($data['inputDescription']);
		unset($data['outputDescription']);
		unset($data['outputDescription']);
		unset($data['inputSample']);
		unset($data['outputSample']);
		unset($data['dataConstraint']);
		unset($data['hint']);
		
		$cnt = $this->db->query('SELECT MAX(pid) AS cnt FROM ProblemSet')->row()->cnt + 1;
		if ($cnt == 1) $cnt = 1000;
		$this->db->query('ALTER TABLE ProblemSet AUTO_INCREMENT=?', array($cnt));

		if ($pid == 0){
			$this->load->model('user');
			$genPid = function($lo, $hi) { // it's better to locate pid in [lo, hi), to distinguish problems added by admins and users
				if ($hi === null)
					$ret = $this->db->query("SELECT MAX(pid) AS max FROM ProblemSet")->row()->max;
				else
					$ret = $this->db->query("SELECT MAX(pid) AS max FROM ProblemSet WHERE pid < ?", array($hi))->row()->max;
				$ret = max((int)$ret + 1, $lo);
				if ($hi !== null && $ret >= $hi)
					$ret = (int)($this->db->query("SELECT MAX(pid) AS max FROM ProblemSet")->row()->max) + 1;
				return $ret;
			};
			if ($this->user->is_admin())
				$data['pid'] = $pid = $genPid(1000, 100000);
			else
				$data['pid'] = $pid = $genPid(100000, null);
			$data['uid'] = $this->user->uid();
			$sql = $this->db->insert_string('ProblemSet', $data);
			$this->db->query($sql);
		} else {
			$sql = $this->db->update_string('ProblemSet', $data, "pid=$pid");
			$this->db->query($sql);
		}

		$path = $this->config->item('problem_path') . $pid;
		if (!is_dir($path)) mkdir($path, 0777, true);
		file_put_contents("$path/problemDescription.html",      $problemDescription);
		file_put_contents("$path/inputDescription.html",        $inputDescription);
		file_put_contents("$path/outputDescription.html",       $outputDescription);
		file_put_contents("$path/inputSample.html",             $inputSample);
		file_put_contents("$path/outputSample.html",            $outputSample);
		file_put_contents("$path/dataConstraint.html",          $dataConstraint);
		file_put_contents("$path/hint.html",                    $hint);
		
		return $pid;
	}
	
	function delete($pid){
		$this->db->query("DELETE FROM ProblemSet WHERE pid=?", array($pid));
	}
	
	function is_showed($pid){
		return $this->db->query("SELECT isShowed FROM ProblemSet WHERE pid=?", array($pid))->row()->isShowed;
	}

	function no_submit($pid){
		return $this->db->query("SELECT noSubmit FROM ProblemSet WHERE pid=?", array($pid))->row()->noSubmit;
	}

	/**
	 * Whether able to EDIT a problem
	 * For VIEWING and SUBMITTING permission, go to `allow`
	 */
	function has_control($pid) {
		$this->load->model('user');
		if ($this->user->is_admin()) return true;
		$row = $this->db->query("SELECT uid, isShowed, reviewing FROM ProblemSet WHERE pid=?", array($pid))->row();
		return $this->user->uid() == $row->uid && ! $row->isShowed && ! $row->reviewing;
	}

	/**
	 * Whether able to VIEW and SUBMIT TO a problem
	 * For EDITTING permission, go to 'has_control'
	 */
	function allow($pid){
		if (self::has_control($pid)) return true;

		$uid = $this->session->userdata('uid');
		if ($this->db->query("SELECT uid FROM ProblemSet WHERE pid=?", array($pid))->row()->uid == $uid) return true;

		if (! $this->is_showed($pid)) return false;

		$this->load->model('user');
		if ($this->user->load_priviledge($uid) != 'restricted') return true;
		if ($this->db->query("SELECT COUNT(*) AS cnt FROM Allowed_Problem WHERE uid=? AND pid=?", array($uid, $pid))->row()->cnt) return true;
		return false;
	}

	/**
	 * Same as `allow`, but hidden problems are avalible during contest
	 */
	function is_allowed($pid)
	{
		if (self::allow($pid)) return true;
		$this->load->model('contests');
		$data = $this->contests->load_problems_in_contests(array((object)array('pid'=>$pid)));
		$now = strtotime('now');
		foreach ($data as $row)
		{
			$res = $this->db->query('SELECT startTime,endTime FROM Contest WHERE cid=?', array($row->cid))->row();
			if (strtotime($res->startTime)<=$now && strtotime($res->endTime)>=$now) return true;
		}
		return false;
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
		$this->load->model('user');
		$solutions = $this->db->query("SELECT idSolution, uid, filename FROM Solution WHERE pid=?", array($pid))->result();
		if ($solutions)
			foreach ($solutions as $solution)
				$solution->username = $this->user->load_username($solution->uid);
		return $solutions;
	}
	
	function delete_solution($idSolution) {
		$data = $this->db->query("SELECT pid, filename FROM Solution WHERE idSolution=?",
									array($idSolution))->row();
		$target_file = $this->config->item('solution_path') . $data->pid . '/' . $data->filename;
		
		if (file_exists($target_file)) unlink($target_file);
		
		$this->db->query("DELETE FROM Solution WHERE idSolution=?",
						array($idSolution));
	}
	
	function load_solution_uid($idSolution) {
		return $this->db->query("SELECT uid FROM Solution WHERE idSolution=?",
								array($idSolution))->row()->uid;
	}

	function update_bookmark($pid)
	{
		$this->load->model('user');
		$star=($this->input->post('star')=='true'?1:0);
		$note=$this->input->post('note');
		$uid=$this->user->uid();
		$this->db->query("DELETE FROM Bookmark WHERE pid=? AND uid=?", array($pid,$uid));
		if ($star || $note)
			$this->db->query("INSERT INTO Bookmark (pid,uid,starred,note) VALUES (?,?,?,?)", array($pid,$uid,$star,$note));
	}

	function load_title($pid)
	{
		return $this->db->query("SELECT title FROM ProblemSet WHERE pid=?", array($pid))->row()->title;
	}

	function load_tags($pid = null)
	{
		if ($pid === null)
			$ret = $this->db->query("SELECT * FROM Category")->result();
		else
			$ret = $this->db->query("SELECT * FROM Category WHERE idCategory in (SELECT idCategory FROM Categorization WHERE pid = ?)", array($pid))->result();
		foreach ($ret as &$item)
		{
			$item->idCategory = (int)($item->idCategory);
			if ($item->prototype !== null) $item->prototype = (int)($item->prototype);
			$item->properties = ($item->properties === null ? (object)null : json_decode($item->properties));
		}
		$cmp = function($a, $b) {
			$seta = isset($a->properties->group);
			$setb = isset($b->properties->group);
			if (!$seta && !$setb)								// Both a and b not set group
				return $a->idCategory - $b->idCategory;
			if (!$seta)											// One of which not set group
				return -1;
			if (!$setb)
				return 1;
			if ($a->properties->group < $b->properties->group)	// If both set group then compare group name
				return -1;
			if ($a->properties->group > $b->properties->group)
				return 1;
			return $a->idCategory - $b->idCategory;				// Both in same group then compare idCategory
			
			/*
			if (! isset($a->properties->group) || $a->properties->group < $b->properties->group) return -1;
			if (! isset($b->properties->group) || $a->properties->group > $b->properties->group) return 1;
			return $a->idCategory - $b->idCategory;
			*/
		};
		usort($ret, $cmp);
		return $ret;
	}

	function del_tag($id)
	{
		$proto = $this->db->query("SELECT prototype FROM Category WHERE idCategory = ?", array($id))->row()->prototype;
		$this->db->query("UPDATE Category SET prototype = ? WHERE prototype = ?", array($proto, $id));
		$this->db->query("DELETE FROM Category WHERE idCategory = ?", array($id));
	}

	function add_tag($name, $proto = NULL, $properties = NULL)
	{
		if ($this->db->query("SELECT COUNT(*) AS count FROM Category WHERE name = ?", array($name))->row()->count)
			return false;
		$this->db->query("INSERT INTO Category (name, prototype, properties) VALUES (?, ?, ?)", array($name, $proto, $properties));
		return true;
	}

	function tag_change_proto($id, $proto)
	{
		$this->db->query("UPDATE Category SET prototype = ? WHERE idCategory = ?", array($proto, $id));
	}

	function tag_set_properties($id, $properties)
	{
		$decoded = ($properties === null ? (object)null : json_decode($properties));
		$queue = array($id);
		while (($id = array_shift($queue)) !== null)
		{
			$this->db->query("UPDATE Category SET properties = ? WHERE idCategory = ?", array($properties, $id));
			$nexts = array();
			if (isset($decoded->prohibit) && $decoded->prohibit === true)
			{
				$nexts = $this->db->query("SELECT idCategory FROM Category WHERE prototype = ?", array($id))->result();
				foreach ($nexts as $tag)
					$queue[] = $tag->idCategory;
			}
			if (isset($decoded->prohibit) && $decoded->prohibit === false)
			{
				$nexts = $this->db->query("SELECT prototype FROM Category WHERE idCategory = ?", array($id))->result();
				foreach ($nexts as $tag) // use for-loop in case of empty query
					$queue[] = $tag->prototype;
			}
		}
	}

	function load_pushed($pid)
	{
		$ret = json_decode($this->db->query('SELECT pushedServer FROM ProblemSet WHERE pid=?', array($pid))->row()->pushedServer,true);
		if (!isset($ret)) $ret = array();
		return $ret;
	}

	function save_pushed($pid, $s)
	{
		$this->db->query('UPDATE ProblemSet SET pushedServer=? WHERE pid=?', array(json_encode($s), $pid));
	}
	
	function file_exist($pid, $filename, $require = '')
	{
		$path = $this->config->item('data_path') . $pid . '/' . $filename;
		if (! file_exists($path)) return FALSE;
		if (stripos($require, 'r') !== FALSE && ! is_readable($path)) return FALSE;
		if (stripos($require, 'w') !== FALSE && ! is_writeable($path)) return FALSE;
		if (stripos($require, 'x') !== FALSE && ! is_executable($path)) return FALSE;
		return TRUE;
	}

	function mark_update($pid)
	{
		$pushed = $this->problems->load_pushed($pid);
		$pushed['version'] = date('Y-M-d H:i:s');
		$this->problems->save_pushed($pid,$pushed);
		$handle = curl_init('http://127.0.0.1/' . $this->config->item('oj_name') . "/index.php/misc/push_data/$pid");
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($handle, CURLOPT_TIMEOUT_MS, 1000);
		curl_exec($handle);
		curl_close($handle);
	}

	function save_script($pid, $script_init, $script_run) // this function may throw MyException
	{
		$ojname = $this->config->item('oj_name');
		$datapath = $this->config->item('data_path').$pid;

		$redis = new Redis();
		if (!$redis->connect($this->config->item('redis_host'), $this->config->item('redis_port'))) throw new MyException('Error when connecting to redis server');
		$redis->select(1);
		$redis->setOption(Redis::OPT_PREFIX, $ojname . ':');

		if ($redis->exists($pid))
		{
			while ($redis->exists($pid)) sleep(1);

			if (file_exists($datapath . '/init.src') && file_exists($datapath . '/run.src') && file_get_contents($datapath . '/init.src') == $script_init && file_get_contents($datapath . '/run.src') == $script_run)
			{
				$redis->close();
				syslog(LOG_INFO, "Duplicate compile request for pid=$pid in OJ $ojname");
				return;
			}
		}

		$redis->set($pid, '', array('ex'=>120));

		if (!is_dir($datapath)) mkdir($datapath,0777,true);
		$cwd = getcwd();
		$rand = rand();
		if (!is_dir("/tmp/foj/dataconf/$ojname/$pid.$rand")) mkdir("/tmp/foj/dataconf/$ojname/$pid.$rand",0777,true);
		if (!chdir("/tmp/foj/dataconf/$ojname/$pid.$rand"))
		{
			$redis->del($pid);
			$redis->close();
			throw new MyException('Error when changing directory');
		}
		exec('rm -r *');

		file_put_contents("init.src",$script_init);
		file_put_contents("run.src",$script_run);
		if (!copy("init.src",$datapath.'/init.src') || !copy("run.src",$datapath.'/run.src'))
		{
			chdir($cwd);
			$redis->del($pid);
			$redis->close();
			throw new MyException('Error when copying');
		}

		$ret = 0; $out = array();
		file_put_contents("/tmp/foj/dataconf/$ojname/$pid.$rand/makefile","include /home/judge/resource/makefile");
		syslog(LOG_INFO, "started compiling scripts for pid=$pid in OJ $ojname");
		exec("make -B > compile.log 2>&1", $out, $ret);
		syslog(LOG_INFO, "ended compiling scripts for pid=$pid in OJ $ojname");
		if ($ret)
		{
			$err = file_get_contents('compile.log');
			chdir($cwd);
			$redis->del($pid);
			$redis->close();
			throw new MyException($err);
		}

		$ret = 0; $out = array();
		exec("./yauj_judge loadconf > conf.log 2> err.log", $out, $ret);
		if ($ret)
		{
			$err = file_get_contents('err.log');
			chdir($cwd);
			$redis->del($pid);
			$redis->close();
			throw new MyException($err);
		}
		$confCache = str_replace(array(" ","\t","\n","\r"),array(),file_get_contents('conf.log'));

		$redis->del($pid);
		$redis->close();

		if (!copy("yauj_judge",$datapath.'/yauj_judge') || !copy("compile.log",$datapath.'/make.log'))
		{
			chdir($cwd);
			throw new MyException('Error when copying yauj_judge and make.log');
		}

		if (!chmod($datapath.'/yauj_judge', 0777))
		{
			chdir($cwd);
			throw new MyException('Error when changing file mode');
		}

		chdir($cwd);
		return $confCache;
	}
	
	function form2script($form)
	{
		$init = ''; $run = ''; $group = array(); $cnt = 0;
		if (!isset($form) || !isset($form->IOMode)) return (object)array('init'=>$init,'run'=>$run,'group'=>'{}');
		$init .= "// filemode, submission and result are global array used by the judge.\n";
		switch ($form->IOMode)
		{
			case 1: // file IO
				$init .= 'filemode[0]["' . $form->cases[0]->tests[0]->userInput . '"]={"by":"SRC"}; // "by" can be an array.'."\n";
				$init .= 'filemode[1]["' . $form->cases[0]->tests[0]->userOutput . '"]={"by":"SRC"};'."\n";
				// no break
			case 0:
				$init .= 'filemode[2]["SRC"]={"language":{"c","c++","c++11","pascal"}};'."\n";
				$init .= 'filemode[4]["EXE"]={"source":"SRC","time":[],"memory":[]};'."\n";
				break;
			case 2: // output only
				$init .= 'filemode[3]["data.zip"]={"download"};'."\n";
		}
		if (isset($form->cases)) foreach ($form->cases as $x)
		{
			$cur_group = array();
			$init .= "\n";
			if (isset($x->tests)) foreach ($x->tests as $y)
			{
				$cur_group[] = $cnt;
				$init .= "filemode[3][\"$y->input\"]={\"case\":$cnt}; // \"case\" can be an array.\n";
				$init .= "filemode[3][\"$y->output\"]={\"case\":$cnt};\n";
				if ($form->IOMode == 2)
				{
					$init .= "filemode[2][\"$y->userOutput\"]={\"language\":{\"txt\"}};\n";
					$init .= "filemode[3][\"$y->input\"][\"download\"] = true;\n";
				} else
				{
					if (isset($y->timeLimit))
						$init .= "filemode[4][\"EXE\"][\"time\"][$cnt]=$y->timeLimit;\n";
					if (isset($y->memoryLimit))
						$init .= "filemode[4][\"EXE\"][\"memory\"][$cnt]=$y->memoryLimit;\n";
				}
				$init .= "input[$cnt]=\"$y->input\";\n";
				$init .= "output[$cnt]=\"$y->output\";\n";
				$init .= "score[$cnt]=$x->score;\n";
				if (isset($y->userOutput))
					$init .= "userOut[$cnt]=\"$y->userOutput\";\n";
				else
					$init .= "userOut[$cnt]=\"data.out\";\n";
				if (isset($y->userInput))
					$init .= "userIn[$cnt]=\"$y->userInput\";\n";
				else
					$init .= "userIn[$cnt]=\"data.in\";\n";
				$cnt ++;
			}
			if ($cur_group) $group[] = $cur_group;
		}
		if (isset($form->spjFile) && gettype($form->spjFile)=='string')
			$init .= "filemode[4][\"$form->spjFile\"]={}; // not needed. you can set limits of spj here.\n";
		if ($form->IOMode == 0 || $form->IOMode == 1)
		{
			$run .= "compile(range(0,$cnt),submission[\"SRC\"],\"SRC\",\"EXE\"); // throw when CE.\n";
			$run .= "length = len(read(\"SRC\"));\n";
			$run .= "for (i=0; i<$cnt; i++) result[i][\"codeLength\"][\"SRC\"] = length;\n";
			$run .= "if (length>50*1024) { for (i=0; i<$cnt; i++) { result[i][\"status\"]=\"compile error\"; result[i][\"message\"]=\"the code is too long.\"; } throw; }\n";
		}
		$run .= "for (i=0; i<$cnt; i++) try {\n";
			if ($form->IOMode == 0)
			{
				$run .= "  remove(userOut[i]);\n";
				$run .= "  exec(i,\"EXE\",input[i],userOut[i]); // throw when error.\n";
			}
			if ($form->IOMode == 1)
			{
				$run .= "  copy(input[i],userIn[i]);\n";
				$run .= "  remove(userOut[i]);\n";
				$run .= "  exec(i,\"EXE\"); // throw when error.\n";
			}
			if (!isset($form->spjMode))
			{
				$run .= "  diff_ret = diff(userOut[i],output[i]);\n";
				$run .= "  if (diff_ret[\"verdict\"]) {\n";
					$run .= "    result[i][\"status\"]=\"wrong answer\";\n";
					$run .= "    result[i][\"score\"]=0;\n";
					$run .= "    result[i][\"message\"]=\"diff : \"+diff_ret[\"first_diff\"][\"f1\"]+\" : \"+diff_ret[\"first_diff\"][\"f2\"];\n";
				$run .= "  } else {\n";
					$run .= "    result[i][\"status\"]=\"accepted\";\n";
					$run .= "    result[i][\"score\"]=score[i];\n";
				$run .= "  }\n";
			} else switch ($form->spjMode)
			{
				case 0 :
					$run .= '  exec(i,"' . $form->spjFile . '","/dev/null","spj.out","/dev/null",input[i]+" "+output[i]+" "+userOut[i]+" "+score[i]);'."\n";
					$run .= '  res = split(read("spj.out"));'."\n";
					$run .= '  result[i]["score"] = res[1];'."\n";
					$run .= '  result[i]["message"] = res[2];'."\n";
					$run .= '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partially accepted"; else result[i]["status"]="wrong answer";'."\n";
					break;
				case 1 : // cena
					if ($form->IOMode != 1) $run .= "  copy(input[i],userIn[i]);\n";
					$run .= '  exec_ret = exec(i,"' . $form->spjFile . '","/dev/null","/dev/null","/dev/null",score[i]+" "+output[i]);'."\n";
					$run .= '  if (exec_ret["exitcode"]) { result[i]["status"]="spj error"; result[i]["score"]=0; throw; }'."\n";
					$run .= '  result[i]["score"] = split(read("score.log"))[0];'."\n";
					$run .= '  result[i]["message"] = read("report.log");'."\n";
					$run .= '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partially accepted"; else result[i]["status"]="wrong answer";'."\n";
					break;
				case 2 : // tsinsen
					$run .= '  exec_ret = exec(i,"' . $form->spjFile . '","/dev/null","/dev/null","/dev/null",input[i]+" "+userOut[i]+" "+output[i]+" spj.out");'."\n";
					$run .= '  if (exec_ret["exitcode"]) { result[i]["status"]="spj error"; result[i]["score"]=0; throw; }'."\n";
					$run .= '  res = split(read("spj.out"));'."\n";
					$run .= '  result[i]["score"] = res[0]*score[i];'."\n";
					$run .= '  result[i]["message"] = res[1];'."\n";
					$run .= '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partially accepted"; else result[i]["status"]="wrong answer";'."\n";
					break;
				case 3 : // hust oj
					$run .= '  exec_ret = exec(i,"' . $form->spjFile . '","/dev/null","/dev/null","/dev/null",input[i]+" "+output[i]+" "+userOut[i]);'."\n";
					$run .= '  if (exec_ret["exitcode"]) {'."\n";
						$run .= '    result[i]["status"]="wrong answer";'."\n";
						$run .= '    result[i]["score"]=0;'."\n";
					$run .= "  } else {\n";
						$run .= '    result[i]["status"]="accepted";'."\n";
						$run .= '    result[i]["score"]=score[i];'."\n";
					$run .= "  }\n";
					break;
				case 4 : // arbiter
					$run .= "  // WARNING : BECAUSE SPJ WILL WRITE THE FILE /tmp/_eval.score, YOU SHOULD JUDGE SUBMISSION ONE BY ONE MANUALLY.\n";
					$run .= '  exec(i,"' . $form->spjFile . '","/dev/null","/dev/null","/dev/null",input[i]+" "+userOut[i]+" "+output[i]);'."\n";
					$run .= '  tmp = split(read("/tmp/_eval.score"));'."\n";
					$run .= '  result[i]["score"] = tmp[-1]; // the last element.'."\n";
					$run .= '  result[i]["message"] = "";'."\n";
					$run .= '  for (k=0; k<len(tmp)-1; k++) result[i]["message"] += tmp[k]+" ";'."\n";
					$run .= '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partially accepted"; else result[i]["status"]="wrong answer";'."\n";
					break;
				case 5 : // lemon
					$run .= '  exec(i,"' . $form->spjFile + '","/dev/null","/dev/null","/dev/null",input[i]+" "+userOut[i]+" "+output[i]+" "+score[i]+" .score .message");'."\n";
					$run .= '  result[i]["score"] = split(read(".score"))[0];'."\n";
					$run .= '  result[i]["message"] = read(".message");'."\n";
					$run .= '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partially accepted"; else result[i]["status"]="wrong answer";'."\n";
			}
		$run .= "} catch {}\n";
		$group = json_encode($group);
		return (object)array('init'=>$init,'run'=>$run,'group'=>$group);
	}
}
