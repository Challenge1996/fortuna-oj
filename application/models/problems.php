<?php

class Problems extends CI_Model{

	function __construct(){
		parent::__construct();
	}
	
	function change_status($pid){
		$this->db->query("UPDATE ProblemSet SET isShowed=1-isShowed WHERE pid=?", array($pid));
	}

	function change_nosubmit($pid){
		$this->db->query("UPDATE ProblemSet SET noSubmit=1-noSubmit WHERE pid=?", array($pid));
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
			SELECT pid, title, source, solvedCount, submitCount, scoreSum AS average, isShowed, noSubmit, uname AS author
			FROM ProblemSet LEFT JOIN (SELECT uid AS uuid, name AS uname FROM User)T ON ProblemSet.uid=T.uuid
			WHERE ($keyword_lim) AND ($filter_lim) AND ($bookmark_lim) AND ($uid_lim) AND ($admin_lim)
			ORDER BY isShowed ASC, pid $rev_str LIMIT ?, ?
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

	function no_submit($pid){
		return $this->db->query("SELECT noSubmit FROM ProblemSet WHERE pid=?", array($pid))->row()->noSubmit;
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
		$target_path = $this->config->item('solution_path') . $pid . '/' . $data->filename;
		
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
	
	function is_allowed($pid)
	{
		$this->load->model('user');
		$this->load->model('contests');
		if ($this->user->is_admin()) return true;
		if ($this->db->query('SELECT isShowed FROM ProblemSet WHERE pid=?',array($pid))->row()->isShowed==1) return true;
		$data = $this->contests->load_problems_in_contests(array((object)array('pid'=>$pid)));
		$now = strtotime('now');
		foreach ($data as $row)
		{
			$res = $this->db->query('SELECT startTime,endTime FROM Contest WHERE cid=?', array($row->cid))->row();
			if (strtotime($res->startTime)<=$now && strtotime($res->endTime)>=$now) return true;
		}
		return false;
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
		$datapath = $this->config->item('data_path').$pid;
		if (!is_dir($datapath)) mkdir($datapath,0777,true);
		$cwd = getcwd();
		$ojname = $this->config->item('oj_name');
		$rand = rand();
		if (!is_dir("/tmp/foj/dataconf/$ojname/$pid.$rand")) mkdir("/tmp/foj/dataconf/$ojname/$pid.$rand",0777,true);
		if (!chdir("/tmp/foj/dataconf/$ojname/$pid.$rand")) throw new MyException('Error when changing directory');
		exec('rm -r *');

		file_put_contents("init.src",$script_init);
		file_put_contents("run.src",$script_run);
		if (!copy("init.src",$datapath.'/init.src') || !copy("run.src",$datapath.'/run.src'))
		{
			chdir($cwd);
			throw new MyException('Error when copying');
		}

		$ret = 0; $out = array();
		file_put_contents("/tmp/foj/dataconf/$ojname/$pid.$rand/makefile","include /home/judge/resource/makefile");
		exec("make -B > compile.log 2>&1", $out, $ret);
		if ($ret)
		{
			$err = file_get_contents('compile.log');
			chdir($cwd);
			throw new MyException($err);
		}

		$ret = 0; $out = array();
		exec("./yauj_judge loadconf > conf.log 2> err.log", $out, $ret);
		if ($ret)
		{
			$err = file_get_contents('err.log');
			chdir($cwd);
			throw new MyException($err);
		}
		$confCache = str_replace(array(" ","\t","\n","\r"),array(),file_get_contents('conf.log'));
		
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
					$run .= '  result[i]["message"] = split(read("report.log"))[0];'."\n";
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
