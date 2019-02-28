<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'application/myjob.php';

class Main extends MY_Controller {

	private function _redirect_page($method, $params = array()){
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		
		$allowed_methods = array('index', 'register', 'userinfo', 'logout', 'reset_password');
		$payment_methods = array('pay', 'pay_notify', 'pay_check', 'pay_status');

		if ($this->user->is_logged_in() || in_array($method, $allowed_methods))
			$this->_redirect_page($method, $params);
		else if (in_array($method, $payment_methods)){
			if ($this->config->item('enable_payment'))
				$this->_redirect_page($method, $params);
			else
				$this->load->view('error', array('message' => lang('function_turned_off')));
		}
		else
			$this->login();
	}
	
	public function logout(){
		$this->user->logout();
		$this->login();
	}
	
	public function userinfo(){
		$user = $this->user->username();
		$avatar = $this->user->load_avatar($this->user->uid());
		$this->load->view('userinfo', array('user' => $user, 'avatar' => $avatar));
	}
	
	public function register(){
		$this->load->library('form_validation');
		$this->load->model('user');

		$this->form_validation->set_error_delimiters('<span class="alert add-on alert-error">', '</span>');
		
		$this->form_validation->set_rules('username', 'Username', 'required|is_unique[User.name]');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[User.email]');

		$this->form_validation->set_message('is_unique', '%s not available!');
		$this->form_validation->set_message('required', '%s is required!');
		
		if ($this->form_validation->run() == FALSE)
			$this->load->view('register');
		else {
			$this->user->registion_success($this->input->post(NULL, TRUE));
			
			$this->load->view('success');
		}
	}

	public function reset_password($name, $key) {
		if ($this->user->is_logged_in()) $this->user->logout();
		$this->load->library('form_validation');
		$this->load->model('user');

		$user = $this->user->load_user($name);

		if (!isset($user->uid)) {
			$this->load->view('error', array('message' => 'User not found!'));
			return;
		}

		$uid = $user->uid;
		$email = $user->email;
		$school = $user->school;
		$description = $user->description;
		$verification_key = $user->verificationKey;

		if (!isset($verification_key)) {
			$this->load->view('error', array('message' => 'You didn\'t request a verification key!'));
			return;
		}

		if ($key != $verification_key) {
			$this->load->view('error', array('message' => 'Your verification key is invalid or incorrect!'));
			return;
		}

		$this->form_validation->set_error_delimiters('<span class="alert add-on alert-error">', '</span>');

		$this->form_validation->set_rules('password', 'New Password', 'required');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

		$this->form_validation->set_message('required', '%s is required!');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('reset_password', array(
				'name' => $name,
				'email' => $email,
				'key' => $verification_key,
			));
		} else {
			$data = $this->input->post(NULL, TRUE);
			$this->user->set_verification_key($uid, NULL);
			$this->user->save_password($uid, md5(md5($data['password']) . $this->config->item('password_suffix')));
			$this->load->view('success');
		}
	}

	function username_exist($username){
		return $this->user->username_exist($username);
	}

	public function pay(){
		$this->load->library('form_validation');
		$this->load->model('user');
		$this->load->model('payment');

		$this->form_validation->set_error_delimiters('<span class="add-on alert alert-error">', '</span>');

		$this->form_validation->set_rules('username', 'lang:username', 'required|callback_username_exist');
		$this->form_validation->set_rules('itemid', 'lang:pay_item', 'required');

		$this->form_validation->set_message('required', '%s is required!');
		$this->form_validation->set_message('username_exist', lang('form_user_not_exist'));

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('pay', array(
				'pay_item' => $this->payment->get_items_list(),
				'pay_method' => $this->config->item('pay_method')
			));
		}
		else {
			$name = $this->input->post('username');
			$uid = $this->user->load_uid($name);
			$pay_uid = $this->config->item('pay_uid');
			$istype = $this->input->post('istype');

			$itemid = $this->input->post('itemid');
			$item = $this->payment->get_item($itemid);
			$price = $item->price;

			$notify_url = base_url('main/pay_notify');
			$return_url = base_url('#main/pay_check');

			// $orderid = 1;
			$orderid = $this->payment->new_order($uid, $name, $item, $istype);

			$key = md5($istype.$notify_url.$orderid.$uid.$price.$return_url.$this->config->item('pay_token').$pay_uid);
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'uid' => $pay_uid,
					'price' => $price,
					'istype' => intval($istype),
					'notify_url' => $notify_url,
					'return_url' => $return_url,
					'orderid' => $orderid,
					'orderuid' => $uid,
					'key' => $key
				)));
		}
	}

	public function pay_notify(){
		$paysapi_id = $this->input->post('paysapi_id');
		$orderid = $this->input->post('orderid');
		$price = $this->input->post('price');
		$realprice = $this->input->post('realprice');
		$orderuid = $this->input->post('orderuid');
		$key = $this->input->post('key');
		
		if ($key != md5($orderid.$orderuid.$paysapi_id.$price.$realprice.$this->config->item('pay_token'))){
			set_status_header(401);
			return;
		}

		$this->load->model('user');
		$this->load->model('payment');
		$this->payment->finish_order($paysapi_id, $orderid, $realprice);
		$this->user->set_expiration(intval($orderuid), $this->payment->get_order($orderid)->expiration);
	}

	public function pay_check(){
		$this->load->view('pay_check', array('orderid' => $this->input->get('orderid')));
	}

	public function pay_status(){
		$this->load->model('payment');
		$this->output
			->set_content_type('text/html')
			->set_output($this->payment->get_order($this->input->post('orderid'))->status);
	}

	public function index(){
		$theme = $this->input->cookie('theme');
		if ( ! $theme) $theme = 'default';
		$this->load->view("$theme/framework", array('logged_in' => $this->user->is_logged_in()));
	}
	
	public function home(){
		$this->load->model('user');
		$online = $this->user->load_online_users();
		$this->load->view('main/home', array("online" => $online));
	}
	
	static function _convert_status($status){
		switch ($status){
			case -3: return '<span class="label label-success">PAC</span>';
			case -2: return '<i class="icon-time"></i>';
			case -1: return '<i class="icon-time"></i>';
			case 0: return '<span class="label label-success">AC</span>';
			case 1: return '<span class="label label-important">PE</span>';
			case 2: return '<span class="label label-important">WA</span>';
			case 3: return '<span class="label">Err</span>';
			case 4: return '<span class="label label-warning">OLE</span>';
			case 5: return '<span class="label label-warning">MLE</span>';
			case 6: return '<span class="label label-warning">TLE</span>';
			case 7: return '<span class="label label-important">RE</span>';
			case 8: return '<span class="label">CE</span>';
			case 9: return '<span class="label">Err</span>';
			default: return $status;
		}
	}

	public function problemset($page = 0){
		$problems_per_page = (int)$this->session->userdata('problems_per_page');
		session_write_close();
		if ( ! $problems_per_page) $problems_per_page = 20;
		
		$uid = $this->user->uid();
		
		$this->load->model('user');
		$this->load->model('problems');
		$this->load->model('misc');

		$admin_only = $this->input->get('admin_only', TRUE);
		$user_only = $this->input->get('user_only', TRUE); // these two are used with copyright
		$keyword = $this->input->get('search',TRUE);
		$filter = $this->input->get('filter',TRUE);
		$show_starred = $this->input->get('show_starred',TRUE);
		$show_note = $this->input->get('show_note',TRUE);
		$search_note = $this->input->get('search_note',TRUE);
		$spliter = $this->input->get('spliter',TRUE);
		$reverse = $this->input->get('reverse_order',TRUE);
		$show_in_control = $this->input->get('show_in_control',TRUE);

		if (count($this->input->get(NULL,TRUE))==1)
			if ($page == 0)
				$page = $this->user->load_last_page($uid);
			else
				$this->user->save_last_page($uid, $page);
		else if ($page == 0)
			$page = 1;

		$query = (object)array(
			'rev' => $reverse,
			'admin_only' => $admin_only,
			'user_only' => $user_only,
			'admin' => $show_in_control,
			'keyword' => $keyword,
			'filter' => $filter,
			'show_starred' => $show_starred,
			'show_note' => $show_note,
			'search_note' => $search_note
		);

		$count = $this->problems->count($query);
		if ($count > 0 && ceil($count / $problems_per_page) < $page)
			$page = ceil($count / $problems_per_page);
		$row_begin = ($page - 1) * $problems_per_page;

		$data = $this->problems->load_problemset($row_begin, $problems_per_page, $query);

		foreach ($data as $row)
			$row->hasControl = $this->problems->has_control($row->pid);
		
		$pids='';
		foreach ($data as $row)
			$pids.="$row->pid,";
		$pids=rtrim($pids,',');
		$pids="($pids)";
		
		$status_result=$this->problems->load_status($uid,$pids);
		$bookmark_result=$this->problems->load_bookmark($uid,$pids);
		
		if (isset($status_result)) {
			foreach ($status_result as $row)
				$status["$row->pid"] = $row->status;
		}

		if (isset($bookmark_result)) {
			foreach ($bookmark_result as $row)
				$bookmark["$row->pid"] = (object) array('starred'=>$row->starred, 'note'=>$row->note);
		}

		foreach ($data as $row){
			if ($row->submitCount > 0) $row->average = $row->average / $row->submitCount;
			
			$row->category = $this->problems->load_tags($row->pid);
			$row->average = number_format($row->average, 2);
			$row->status = '';
			$row->ac = FALSE;
			
			if (isset($status["$row->pid"])){
				if ($this->misc->is_accepted($uid, $row->pid)) $status["$row->pid"] = 0;
				$row->status = self::_convert_status($status["$row->pid"]);
				if ($status["$row->pid"] == 0) $row->ac = TRUE;
			}

			if (isset($bookmark["$row->pid"]))
				$row->bookmark=$bookmark["$row->pid"];
		}

		$this->load->library('pagination');
		$config['base_url'] = '#main/problemset/';
		$config['total_rows'] = $count;
		$config['per_page'] = $problems_per_page;
		$config['cur_page'] = $page;
		$config['suffix'] = '?' . http_build_query($this->input->get());
		$config['first_url'] = $config['base_url'] . '1' . $config['suffix'];
		$this->pagination->initialize($config);

		$this->load->view('main/problemset', array(
			'data' => $data,
			'keyword' => $keyword,
			'filter' => $filter,
			'spliter' => $spliter,
			'count' => $count
		));
	}

	public function show($pid){
		$this->load->model('problems');
		$this->load->model('user');
		$this->load->model('misc');

		$data = null;
		try {	
			$data = $this->problems->load_problem($pid);
		} catch (MyException $e) {
			$this->load->view('error', array('message'=>$e->getMessage()));
			return;
		}	
		if ($data != FALSE && $this->problems->allow($pid)) {
			
			$data->filemode = json_decode($data->confCache);
			unset($data->confCache);

			$noTime = $noMemory = false;
			if (isset($data->filemode[4]))
				foreach ($data->filemode[4] as $executable => $conf)
				{
					if (!$noTime && isset($conf->time))
						foreach ($conf->time as $time)
							if (!isset($data->timeLimit) || $data->timeLimit == $time)
								$data->timeLimit = $time;
							else
							{
								unset($data->timeLimit);
								$noTime = true;
								break;
							}
					if (!$noMemory && isset($conf->memory))
						foreach ($conf->memory as $memory)
							if (!isset($data->memoryLimit) || $data->memoryLimit == $memory)
								$data->memoryLimit = $memory;
							else
							{
								unset($data->memoryLimit);
								$noMemory = true;
								break;
							}
				}
			
			$data->solutions = $this->problems->load_solutions($pid);

			$copyright = null;
			if ($this->config->item('show_copyright'))
				$copyright = ($this->user->load_priviledge($data->uid) == 'admin' ? 'admin' : 'user');
		} else $data = FALSE;
	
		if ($data == FALSE)
			$this->load->view('error', array('message' => 'Problem not available!'));
		else
			$this->load->view('main/show', array(
				'data' => $data,
				'noSubmit' => $this->problems->no_submit($pid),
				'copyright' => $copyright
			));
	}

	public function showdownload($pid)
	{
		$this->load->model('problems');
		
		if (! $this->problems->is_allowed($pid))
		{
			$this->load->view('error', array('message' => 'Problem not available!'));
			return;
		}

		$data = null;	
		try {
			$data = $this->problems->load_dataconf($pid,true);
		} catch (MyException $e) {
			$this->load->view('error', array('message'=>$e->getMessage()));
			return;
		}	
		$data->filemode = json_decode($data->confCache);
		unset($data->confCache);
		$files = array();
		foreach ($data->filemode[3] as $name => $property)
			if (isset($property->download) && $property->download)
				$files[] = $name;
		natsort($files);
		$this->session->set_userdata('download',implode('|',$files));
		session_write_close();
		$this->load->view('main/showdownload', array('pid' => $pid, 'files' => $files));
	}
	
	public function download($pid, $filename = 'data.zip', $filetypeflag = 0, $path = 'data_path') {
		//filetypeflag: 0 -> auto detect, 1 -> application/octet-stream
		$filename = rawurldecode(urldecode($filename));

		if ($filename != $this->security->sanitize_filename($filename, true))
		{
			$this->load->view('error', array('message' => 'The file name has disallowed charaters'));
			return;
		}

		$file = $this->config->item($path) . $pid . "/$filename";
  		$filetype = mime_content_type($file);
  		if ($filetype == 'image/x-portable-bitmap') $filetype = 'text/plain';
		if ($filetypeflag == 1) $filetype = 'application/octet-stream';
		
		if (! strstr($this->session->userdata('download'), $filename))
			$this->load->view('error', array('message' => 'You are not allowed to download this file!'));
		else if ( ! is_file($file))
			$this->load->view('error', array('message' => 'File Not Found!'));
		else
			$this->load->view('main/download', array('file' => $file, 'filename' => $filename, 'filetype' => $filetype));
	}

	public function codedownload($sid, $file)
	{
		if ($file != $this->security->sanitize_filename($file, true))
		{
			$this->load->view('error', array('message' => 'The file name has disallowed charaters'));
			return;
		}

		$this->load->model('submission');
		$front = intval($sid/10000);
		$back = $sid%10000;
		$path = $this->config->item('code_path') . "$front/$back";
		if (!$this->submission->allow_view_code($sid))
			$this->load->view('error', array('message' => 'You are not allowed to download this file!'));
		else if (!is_file("$path/$file"))
			$this->load->view('error', array('message' => 'File Not Found!'));
		else
			$this->load->view('main/download', array(
				'file' => "$path/$file",
				'filename' => $file.($this->input->get('ext')?'.'.$this->input->get('ext'):''),
				'filetype' => 'application/octet-strem'
			));	
	}
	
	public function datadownload($pid) {
		$this->load->library('form_validation');

		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('pid', 'Problem ID', 'required');
		$this->form_validation->set_rules('authcode', 'Authentication Code', 'required');

		
	}
	
	public function limits($pid){
		$this->load->model('problems');

		if (! $this->problems->is_allowed($pid))
		{
			$this->load->view('error', array('message' => 'Problem not available!'));
			return;
		}

		$simple = false;
		if (isset($_GET['simple'])) $simple = true;
		$data = null;
		try {
			$data = $this->problems->load_dataconf($pid,true);
		} catch (MyException $e) {
			$this->load->view('error', array('message'=>$e->getMessage()));
			return;
		}	
		$data->filemode = json_decode($data->confCache);
		$data->dataGroup = json_decode($data->dataGroup);
		unset($data->confCache);

		$TIME = array(); $MEMORY = array();
		$src = (array)$data->filemode[2];
		if (isset($data->filemode[4]))
			foreach ($data->filemode[4] as $property) if (isset($property->source))
				foreach ((array)($property->source) as $source)
					if (isset($source) && isset($src[$source]))
					{
						foreach ((array)$property->time as $id => $time) if ($time!==NULL)
							$TIME[$id][$source]=$time;
						foreach ((array)$property->memory as $id => $memory) if ($memory!==NULL)
							$MEMORY[$id][$source]=$memory;
					}
		$this->load->view('main/limits',array(
			'time' => (array)$TIME,
			'memory' => (array)$MEMORY,
			'group' => $data->dataGroup,
			'simple' => $simple,
			'showName' => count($src)>1
		));
	}

	public function tags($pid)
	{
		$this->load->model("problems");
		$this->load->model("user");
		if (! $this->problems->allow($pid))
		{
			$this->load->view('error', array('message' => 'Problem not available!'));
			return;
		}

		$readonly = ($this->input->get("readonly") == "true");

		$flatTags = $this->problems->load_tags($readonly ? $pid : null);
		$chosen = $this->problems->load_tags($pid);
		$groupedTags = array();
		foreach ($flatTags as $tag)
		{
			$group = isset($tag->properties->group) ? $tag->properties->group : '';
			if (! isset($groupedTags[$group])) $groupedTags[$group] = array();
			$groupedTags[$group][] = $tag;
		}

		$ret = array();
		foreach ($groupedTags as $group => $tags)
		{
			$byId = array();
			foreach ($tags as $tag)
			{
				$byId[$tag->idCategory] = $tag;
				$byId[$tag->idCategory]->subtags = array();
				$byId[$tag->idCategory]->chosen = false;
			}
			foreach ($tags as $tag)
				if ($tag->prototype !== null)
					$byId[$tag->prototype]->subtags[] = $tag->idCategory;
			foreach ($chosen as $tag)
				if (isset($byId[$tag->idCategory]))
					$byId[$tag->idCategory]->chosen = true;

			$ret[$group] = array();
			$sortTags = function($id, $depth, &$ret) use ($byId, &$sortTags) { // function must be passed in as reference to recurse
				$byId[$id]->depth = $depth;
				$ret[] = $byId[$id];
				foreach ($byId[$id]->subtags as $subtag)
					$sortTags($subtag, $depth + 1, $ret);
			};
			foreach ($tags as $tag)
				if ($tag->prototype === null)
					$sortTags($tag->idCategory, 0, $ret[$group]);
		}
		$isAdmin = $this->user->is_admin();
		$this->load->view("main/tags", array("list" => $ret, "readonly" => $readonly, "pid" => $pid, "isAdmin" => $isAdmin));
	}

	function tagsearchbar()
	{
		$this->load->model("problems");

		$tags = $this->problems->load_tags();
		$byId = array();
		foreach ($tags as $tag)
		{
			$byId[$tag->idCategory] = $tag;
			$byId[$tag->idCategory]->subtags = array();
		}
		foreach ($tags as $tag)
			if ($tag->prototype !== null)
				$byId[$tag->prototype]->subtags[] = $tag->idCategory;

		$sorted = array();
		$sortTags = function($id, $prefix) use ($byId, &$sorted, &$sortTags) {
			$byId[$id]->fullname = $prefix . ($prefix == '' ? '' : '/') . $byId[$id]->name;
			$sorted[] = $byId[$id];
			foreach ($byId[$id]->subtags as $subtag)
				$sortTags($subtag, $byId[$id]->fullname);
		};
		foreach ($tags as $tag)
			if ($tag->prototype === null)
				$sortTags($tag->idCategory, '');

		$this->load->view("main/tagsearchbar", array("tags" => $sorted));
	}

	public function addtag($pid){
		$id = $this->input->get('tag', TRUE);
		if (!$id) return;
		
		$this->load->model('misc');
		$this->load->model('problems');
		if ($this->problems->allow($pid))
			$this->misc->add_categorization($pid, $id);
	}
	
	public function deltag($pid, $id){
		$this->load->model('misc');
		$this->load->model('problems');
		if ($this->problems->allow($pid))
			$this->misc->delete_categorization($pid, $id);		
	}

	public function submit($pid = 0, $cid = 0, $gid = 0, $tid = 0){
		$this->load->library('form_validation');
		$this->load->helper('cookie');
		$this->load->model('problems');

		if ($this->problems->no_submit($pid))
		{
			$this->load->view('error', array('message'=>'This problem is set to NO SUBMISSION'));
			return;
		}
		
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
		$this->form_validation->set_rules('pid', 'Problem ID', 'required');
		
		$filemode = null;
		try {
			$filemode = json_decode($this->problems->load_dataconf($pid,true)->confCache);
		} catch (MyException $e) {
			$this->load->view('error', array('message'=>$e->getMessage()));
			return;
		}	

		$toSubmit = (array)$filemode[2];
		uksort($toSubmit, 'strnatcmp');
		foreach ($toSubmit as $name => &$property)
		{
			$langArr = array();
			foreach ($property->language as $lang => $choose)
				if ($choose) $langArr[] = $lang;
			$property->language = array_change_key_case($langArr);
			$property->fileIO = false;
			foreach (array_merge((array)$filemode[0],(array)$filemode[1]) as $io)
				foreach ((array)($io->by) as $s) if ($s == $name)
				{
					$property->fileIO = true;
					break;
				}
		}
		if ($cid > 0)
		{
			$this->load->model('contests');
			$cstat = $this->contests->load_contest_status($cid);
			$lang = explode(',',strtolower($cstat->language));
			$lang[] = 'txt';
			foreach ($toSubmit as $file => &$property)
				$property->language = array_intersect($property->language,$lang);
		}
		if ($tid > 0)
		{
			$this->load->model('misc');
			$tstat = $this->misc->load_task_info($gid, $tid);
			$lang = explode(',',strtolower($tstat->language));
			$lang[] = 'txt';
			foreach ($toSubmit as $file => &$property)
				$property->language = array_intersect($property->language,$lang);
		}

		if ($this->form_validation->run() == FALSE){
			$data = array(
				'pid' => $pid,
				'language' => $this->input->cookie('language'),
				'toSubmit' => $toSubmit
			);
			if ($cid > 0) $data['cid'] = $cid;
			if ($tid > 0) $data['tid'] = $tid;
			if ($gid > 0) $data['gid'] = $gid;
			$this->load->view('main/submit', $data);
			
		} else {
			$language = $this->input->post('cookie-language');
			$uid = $this->user->uid();
			if ($language && $language!='txt')
			{
				$this->input->set_cookie(array('name' => 'language', 'value' => $language, 'expire' => '10000000'));
				$this->user->save_language($uid, $language);
			}

			$data = array(
				'uid'	=>	$uid,
				'name'	=>	$this->user->username(),
				'pid'	=>	$this->input->post('pid', TRUE),
				'submitTime'	=>	date("Y-m-d H:i:s"),
				'pushTime' => date("Y-m-d H:i:s")
			);
			
			if ($this->input->post('cid') != '') $data['cid'] = $this->input->post('cid');
			if ($this->input->post('gid') != '') $data['gid'] = $this->input->post('gid');
			if ($this->input->post('tid') != '') $data['tid'] = $this->input->post('tid');			
			
			if (isset($data['tid'])) {
				$this->load->model('misc');
				if (!$this->misc->is_in_group($data['uid'],$data['gid'])) exit('you are not team member');
				$info = $this->misc->load_task_info($data['gid'], $data['tid']);
				if (strtotime($info->startTime) > time() || strtotime($info->endTime) < time()) exit('not yet');
				//unset($data['gid']);
			} elseif (isset($data['cid'])) {
				$this->load->model('contests');
				if (! $this->contests->is_valid($data['cid'])) exit('not valid');
				$info = $this->contests->load_contest_status($data['cid']);
				if (max(strtotime($info->startTime), strtotime($info->submitTime)) > time() || strtotime($info->endTime) < time()) exit('not yet');
				if ($info->isTemplate) {
					$info = $this->contests->load_template_contest_status($data['cid'], $data['uid']);
					if (!$info || max(strtotime($info->startTime), strtotime($info->submitTime)) > time() || strtotime($info->endTime) < time()) exit('not yet');
				}
			} else {
				$this->load->model('problems');
				if (! $this->problems->allow($data['pid'])) exit('hidden');
				$showed = $this->problems->is_showed($data['pid']);
				if ($showed == 0) $data['isShowed'] = 0;
			}

			$language = $this->input->post('language');
			$editor = (array)$this->input->post('texteditor',FALSE);
			foreach ($editor as &$code) $code = base64_decode($code);
			$upload = array('name'=>array());
			if (isset($_FILES['file'])) $upload = $_FILES['file'];
			$arg_lang = array();
			$toSubmitArr = (array)$toSubmit;
			foreach ($language as $file => $lang)
			{
				if (! in_array($lang, $toSubmitArr[$file]->language))
					exit('lang');
				$arg_lang[] = array('source' => $file, 'language' => $lang);
			}
			$data['langDetail'] = json_encode($arg_lang);
			
			$this->load->model('submission');
			$sid = $this->submission->save_submission($data);
			$front = intval($sid/10000);
			$back = $sid%10000;
			if (!mkdir($this->config->item('code_path')."$front/$back",0777,true))
				exit('error when mkdir');
			foreach ($language as $file => $lang)
			{
				$saveto = $this->config->item('code_path') . "$front/$back";
				if (isset($upload['name'][$file]))
				{
					if ($upload['error'][$file]>0) exit('upload error');
					if ($upload['size'][$file]>67108864) exit('too large');
					move_uploaded_file($upload['tmp_name'][$file], "$saveto/$file");
				}
				else
				{
					$handle = fopen("$saveto/$file", 'w');
					if (isset($editor[$file])) fwrite($handle, $editor[$file]);
					fclose($handle);
				}
				if ($lang == 'pascal')
				{
					$ret_code = 0;
					passthru("yast_gen_pas < $saveto/$file > $saveto/.ast", $ret_code);
					if ($ret_code) exec("rm $saveto/.ast");
				}
			}

			Resque::setBackend('127.0.0.1:6379');
			Resque::enqueue('default', 'myjob', array(
				'passwd' => $this->config->item('local_passwd'),
				'oj_name' => $this->config->item('oj_name'),
				'pid' => $pid,
				'sid' => $sid,
				'lang' => $data['langDetail'],
				'servers' => $this->config->item('servers'),
				'pushTime' => $data['pushTime']
			));
			
			$this->load->view('success');
		}
	}
	
	public function statistic($pid, $page = 1){
		$this->load->model('problems');
		if ( ! $this->problems->is_showed($pid) && ! $this->user->is_admin() || ! $this->problems->allow($pid)) {
			$this->load->view('error', array('message' => 'You have NO priviledge to see the statistic.'));
			return;
		}
	
		$users_per_page = 20;
		
		$this->load->model('submission');
		$row_begin = ($page - 1) * $users_per_page;
		$count = $this->submission->statistic_count($pid);
		$data = $this->submission->load_statistic($pid, $row_begin, $users_per_page);
		foreach ($data as &$row) $this->submission->format_data($row);
		
		$this->load->library('pagination');
		$config['base_url'] = "#main/statistic/$pid/";
		$config['total_rows'] = $count;
		$config['per_page'] = $users_per_page;
		$config['cur_page'] = $page;
		$config['uri_segment'] = 4;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);
		
		$this->load->view('main/statistic', array('data' => $data, 'pid' => $pid));
	}

	public function status($page = 1){
		$submission_per_page = (int)$this->session->userdata('submission_per_page');
		session_write_close();
		if ( ! $submission_per_page) $submission_per_page = 20;
		
		$filter = (array)$this->input->get(NULL, TRUE);
		
		$this->load->model('problems');
		$this->load->model('submission');
		$row_begin = ($page - 1) * $submission_per_page;
		$count = $this->submission->count($filter);
		$data = $this->submission->load_status($row_begin, $submission_per_page, $filter);
		$this->submission->format_data($data);
		
		$this->load->library('pagination');
		$config['base_url'] = '#main/status/';
		$config['total_rows'] = $count;
		$config['per_page'] = $submission_per_page;
		$config['first_link'] = 'Top';
		$config['last_link'] = FALSE;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);

		$url = uri_string() . ($_SERVER['QUERY_STRING'] == '' ? '' : ('?' . $_SERVER['QUERY_STRING']));
		$filter = array_merge(array('status' => array(), 'languages' => array(), 'url' => $url), $filter);
		$this->load->view('main/status', array('data'	=>	$data, 'filter' => $filter));
	}
	
	public function submission_change_access($sid){
		$this->load->model('submission');
		$this->load->model('user');
		
		if ($this->user->uid() == $this->submission->load_uid($sid) && $this->config->item('allow_normal_user_public') === true ||
			$this->user->is_admin() && $this->config->item('allow_normal_user_public') !== 'default_public')
			$this->submission->change_access($sid);
	}

	public function code($sid){
		$this->load->model('submission');
		$data = $this->submission->load_code($sid);
		
		if ($data === FALSE)
			$this->load->view('error', array('message' => 'You have NO priviledge to see this code.'));
		else
			$this->load->view('main/code', array("sid" => $sid, "data" => $data));
	}
	
	public function result($sid){
		$this->load->model('submission');
		$this->load->model('problems');
		
		$data = $this->submission->load_result($sid);

		if ($data === FALSE)
			$this->load->view('error', array('message' => 'You have NO priviledge to see this result.'));
		else
		{
			$result = json_decode($data->result);

			if (!isset($result))
				$this->load->view('error', array('message' => 'No result avalible'));
			else
			if (isset($result->error))
				$this->load->view('error', array('message' => $result->error));
			else
			{
				$got = null;
				try {
					$got = $this->problems->load_dataconf($data->pid,true);
				} catch (MyException $e) {
					$this->load->view('error', array('message'=>$e->getMessage()));
					return;
				}	
				$group = json_decode($got->dataGroup);
				$filemode = json_decode($got->confCache);

				$this->load->view('main/result', array(
					'result' => $result,
					'pid' => $data->pid,
					'group' => $group,
					'filemode' => $filemode
				));
			}
		}
	}
	
	public function ranklist($page = 1){
		$users_per_page = 20;
		
		$this->load->model('user');
		$this->load->model('misc');
		
		$count = $this->user->count();
		if ($count > 0 && ($count + $users_per_page - 1) / $users_per_page < $page)
			$page = ($count + $users_per_page - 1) / $users_per_page;
			
		$row_begin = ($page - 1) * $users_per_page;
		$data = $this->misc->load_ranklist($row_begin, $users_per_page);
		$rank = $row_begin;
		
		foreach ($data as $row){
			$row->rank = ++$rank;
			$row->rate = 0.00;
			if ($row->submitCount > 0) $row->rate = $row->solvedCount / $row->submitCount * 100;
			$row->rate = number_format($row->rate, 2);
		}
		
		$this->load->library('pagination');
		$config['base_url'] = '#main/ranklist/';
		$config['total_rows'] = $count;
		$config['per_page'] = $users_per_page;
		$config['first_url'] = $config['base_url'] . '1';
		$this->pagination->initialize($config);

		$this->load->view('main/ranklist', array('data' => $data));
	}
	
	function addsolution($pid) {
		$this->load->model('misc');
		$this->load->model('problems');
		
		$is_accepted = $this->misc->is_accepted($this->user->uid(), $pid);
		if (! $this->user->is_admin()){
			$this->load->view('error', array('message' => lang('error_admin_only')));
			return;
		}
		
		if ( !isset($_FILES['solution'])) return;
		
		$temp_file = $_FILES['solution']['tmp_name'];
		$target_path = $this->config->item('solution_path') . $pid . '/';
		if (! is_dir($target_path)) mkdir($target_path, 0777, true);
		$sanitized_filename = $this->security->sanitize_filename($_FILES['solution']['name']);
		$target_file = $target_path . $sanitized_filename;
		
		if (file_exists($target_file)) return;
		
		if (! is_executable($temp_file)) {
			move_uploaded_file($temp_file, $target_file);
			$this->problems->add_solution($pid, $sanitized_filename);
			
			$this->load->view('success');
		}
	}
	
	function deletesolution($idSolution) {
		$this->load->model('problems');
		
		if ($this->user->is_admin() || $this->problems->load_solution_uid($idSolution) == $this->user->uid()) {
			$this->problems->delete_solution($idSolution);
		}
	}

	function upd_bookmark($pid)
	{
		$this->load->model('problems');
		$this->problems->update_bookmark($pid);
	}

	function all_tags()
	{
		$this->load->model('problems');
		echo json_encode($this->problems->load_tags());
	}

	function recentcontest() // don't move this to misc controller, because it calls misc model.
	{
		if ($this->config->item('fetch_contest_list') !== true)
			exit('');
		$this->load->model('misc');
		$data = $this->misc->load_recent_contest();
		if (!$data) return;
		$data = json_decode($data, true);
		foreach ($data as &$row)
			if (strpos($row['countDown'], ':') !== false)
				$row['countDown'] = gmdate('z \D\a\y\(\s\) H:i:s', strtotime($row['startTime'])-time());

		$this->load->view('main/recentcontest', array('data'=>$data));
	}
}

// End of file main.php
