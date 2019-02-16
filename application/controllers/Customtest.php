<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function find_abstract($path)
{
	if (!file_exists($path)) return 'Nothing has been uploaded';
	$s=file_get_contents($path,false,null,-1,50);
	//$s=nl2br(htmlentities(file_get_contents($path,false,null,-1,50)));
	//$s=str_replace("\n",'',$s);
	//$s=str_replace("\r",'',$s);
	if (strlen($s)==50) $s=$s.' ...';
	return $s;
}

class Customtest extends MY_Controller {

	private function _redirect_page($method, $params = array()){
		if (! $this->config->item('allow_custom_test')) {
			$this->load->view('error', array('message' => lang('function_turned_off')));
			return;
		}
		if (method_exists($this, $method))
			return call_user_func_array(array($this, $method), $params);
		else
			show_404();
	}

	public function _remap($method, $params = array()){
		$this->load->model('user');
		
		if ($method == 'home') $method = 'run';
		
		if ($this->user->is_logged_in())
			$this->_redirect_page($method, $params);
		else
			$this->login();
	}
	
	public function run() {
		$this->load->model('user');
		$this->load->library('form_validation');
		$this->load->helper('cookie');
		
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('language', 'Language', 'required');

		$uid = $this->user->uid();
		$uploaded_file='/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid.'/input';
	
		if ($this->form_validation->run() == FALSE){
			$data = array(
				'language'	=>	$this->input->cookie('language'),
				'code'		=>	'',
				'input'		=>	'',
				'file_abstract' => find_abstract($uploaded_file),
				'output'	=>	'',
				'text_checked' => 'checked',
				'file_checked' => ''
			);

			$this->load->view('customtest/run', $data);
			
		} else {
			$language = $this->input->post('language');
			$use_file_selected=($this->input->post('input_method')=='use_file');
			$this->input->set_cookie(array('name' => 'language', 'value' => $language, 'expire' => '10000000'));
			$this->user->save_language($uid, $language);

			$code = base64_decode($this->input->post('texteditor', FALSE));
			$input_text = $this->input->post('input_text');
			
			$path = '/tmp/foj/customtest/run' . rand();
			mkdir($path, 0777, true);

			$output='';
			$status=$memory=$time=$retcode=false;
			switch ($language) {
				case 'C':	
					$cmd = 'gcc Main.c -o Main -lm';
					$source = 'Main.c';
					$flags = ' -DONLINE_JUDGE';
					break;
				case 'C++':
					$cmd = 'g++ Main.cpp -o Main';
					$source = 'Main.cpp';
					$flags = ' -DONLINE_JUDGE';
					break;
				case 'C++11':
					$cmd = 'g++ Main.cpp --std=c++11 -o Main';
					$source = 'Main.cpp';
					$flags = ' -DONLINE_JUDGE';
					break;
				case 'Pascal':
					$cmd = 'fpc Main.pas -oMain -Co -Ci';
					$source = 'Main.pas';
					$flags = ' -dONLINE_JUDGE';
					break;
			}
			$file = fopen("$path/$source", 'w');
			fwrite($file, $code);
			fclose($file);
			if ($this->input->post('with_o2')) $flags .= ' -O2';
			$cmd .= $flags;
			$cmd .=' >data.out 2>&1';
			system("cd $path; $cmd", $CE);
			if ($CE)
			{
				$status = "compile error";
				$output = file_get_contents("$path/data.out");
			}
			unlink("$path/data.out");
			
			if ($status != "compile error")
			{
				if ($use_file_selected)
				{
					if (file_exists($uploaded_file))
					{
						$cmd="cp $uploaded_file $path/data.in";
						system($cmd);
					} else
						$output = 'The input file dose not exist.';
				}
				else
				{
					$file = fopen("$path/data.in", 'w');
					fwrite($file, $input_text);
					fclose($file);
				}
				$judge = popen("cd $path; uoj_run -T 10000 -M 1048576 -S 1048576 -i data.in -o data.out Main","r");
				fscanf($judge,"%d%d%d%d",$status,$time,$memory,$retcode);
				pclose($judge);
	
				if (file_exists("$path/data.out"))
					$output = file_get_contents("$path/data.out");
				else
					$output = 'Output not found';
				switch ($status)
				{
					case 0: $status = "normal"; break;
					case 2: $status = "run time error"; break;
					case 3: $status = "memory limit exceeded (1G)"; break;
					case 4: $status = "time limit exceeded (10s)"; break;
					case 5: $status = "output limit exceeded"; break;
					case 6: $status = "dangerous syscall"; break;
					case 7: $status = "Oh, you should find VFK!!";
				}
			}

			
			$data = array(
				'language' => $language,
				'code'	=>	$code,
				'input'	=>	$input_text,
				'file_abstract' => find_abstract($uploaded_file),
				'output'	=>	$output,
				'time'	=>	$time,
				'memory'	=>	$memory,
				'status'	=>	$status,
				'retcode'	=>	$retcode,
				'text_checked' => ($use_file_selected?'':'checked'),
				'file_checked' => ($use_file_selected?'checked':'')
			);
			
			system('rm -R ' . $path);
			
			$this->load->view('customtest/run', $data);
		}
	}

	public function upload_input()
	{
		$this->load->model('user');
		if ($_FILES['input_file']['error']>0) exit('uploading error '.$_FILES['input_file']['error']);
		if ($_FILES['input_file']['size']>16777216) exit('too large');
		$uid = $this->user->uid();
		if (!file_exists('/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid))
			mkdir('/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid,0777,true);
		if (!move_uploaded_file($_FILES['input_file']['tmp_name'],'/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid.'/input'))
			exit('moving error');
		exit('success'); 
	}
}
