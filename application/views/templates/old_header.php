<!DOCTYPE HTML>

<html>

	<head>
		<title>Fortuna Online Judge System</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />		
		<link href="<?=base_url('css/style.css');?>" rel="stylesheet" type="text/css" />
		<script src="<?=base_url('js/jquery.js');?>"></script>
		<?php
			if (isset($head)) echo $head;
		?>
	</head>

	<body>
	
		<div class="header">
		
			<div class="logo"></div>
			
			<div class="menu_navigation">
				<?=anchor('main/home', 'Home'); ?>
				<?=anchor('main/problemset', 'Problemset');?>
				<?=anchor('main/status', 'Status');?>
				<?=anchor('contest', 'Contest');?>
				<?=anchor('main/ranklist', 'Ranklist');?>
				<?php
					if ($this->session->userdata('priviledge') == 'admin')
						echo anchor('admin', 'Administer');
				?>
			</div>
			
			<div class="user_info">
				<?php
					if ( ! isset($logged_in) || $logged_in){
						echo anchor(base_url() . 'index.php/main/logout', 'Logout');
						$name = $this->session->userdata('username');
						echo anchor(base_url() . 'index.php/main/user/' . $name, $name);
					} else {
					}
				?>
			</div>

			<div class="clr"></div>

		</div>
