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

class Customtest extends CI_Controller {

	private function _redirect_page($method, $params = array()){
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
		$this->load->library('form_validation');
		$this->load->helper('cookie');
		
		$this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');

		$this->form_validation->set_rules('language', 'Language', 'required');

		$uid = $this->session->userdata('uid');
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

			$code = html_entity_decode($this->input->post('texteditor'));	
			$input_text = $this->input->post('input_text');
			
			$cmd = 'mkdir /tmp/foj/customtest > /dev/null';
			system($cmd);
			
			$path = '/tmp/foj/customtest/run' . rand();
			$cmd = 'mkdir ' . $path . ' > /dev/null';
			system($cmd);
			
			$cmd = 'cp /usr/bin/judge_core ' . $path . ' > /dev/null';
			system($cmd);

			$output='';
			$memory=$time=false;
			switch ($language) {
				case 'C':	
					$cmd = 'gcc Main.c -o Main -O2 -DONLINE_JUDGE >data.out 2>&1';
					$source = 'Main.c';
					break;
				case 'C++':
					$cmd = 'g++ Main.cpp -o Main -O2 -DONLINE_JUDGE >data.out 2>&1';
					$source = 'Main.cpp';
					break;
				case 'C++11':
					$cmd = 'g++ Main.code --std=c++11 -o Main -O2 -DONLINE_JUDGE >data.out 2>&1';
					$source = 'Main.cpp';
					break;
				case 'Pascal':
					$cmd = 'fpc Main.pas -oMain -O2 -Co -Ci >data.out';
					$source = 'Main.pas';
					break;
			}
			
			$current_path = getcwd();
			chdir($path);
			
			$file = fopen($source, 'w');
			fwrite($file, $code);
			fclose($file);
			system($cmd, $status);
			if ($status)
				$output = file_get_contents('data.out');
			unlink('data.out');
			
			if ($output=='')
			{
				if ($use_file_selected)
				{
					if (file_exists($uploaded_file))
					{
						$uid=$this->session->userdata('uid');
						$cmd='cp '.$uploaded_file.' data.in';
						system($cmd);
					} else
						$output = 'The input file dose not exist.';
				}
				else
				{
					$file = fopen('data.in', 'w');
					fwrite($file, $input_text);
					fclose($file);
				}
			}
			
			if ($output=='')
			{
				$cmd = './judge_core 0 Main data.in /dev/null data.out 10000 524288 0 > /dev/null';
				system($cmd);
	
				if (file_exists('data.out'))
					$output = file_get_contents('data.out');
				else
					$output = 'Output not found';
				$file = fopen('test.log', 'r');
				fscanf($file, "%d %f %d %d", $time, $time, $time, $memory);
				fclose($file);
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
				'text_checked' => ($use_file_selected?'':'checked'),
				'file_checked' => ($use_file_selected?'checked':'')
			);
			
			system('rm -R ' . $path);
			
			chdir($current_path);
			$this->load->view('customtest/run', $data);
		}
	}

	public function upload_input()
	{
		if ($_FILES['input_file']['error']>0) exit('uploading error '.$_FILES['input_file']['error']);
		if ($_FILES['input_file']['size']>16777216) exit('too large');
		$uid=$this->session->userdata('uid');
		if (!file_exists('/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid))
			mkdir('/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid,0777,true);
		if (!move_uploaded_file($_FILES['input_file']['tmp_name'],'/tmp/foj/customtest/'.$this->config->item('oj_name').'/'.$uid.'/input'))
			exit('moving error');
		exit('success'); 
	}
}
