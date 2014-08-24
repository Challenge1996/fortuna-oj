<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Fortuna Online Judge System</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="" />
		<meta name="author" content="" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<link href="/css/bootstrap.css" rel="stylesheet">
		<link href="/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="/css/style.css" rel="stylesheet">

		<script src="/js/jquery.js"></script>
		<script src="/js/bootstrap.js"></script>
		<?php if (isset($head)) echo $head;?>
		
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>

<body>


	<div class="container-fluid nav nav-list">
		<div class="row-fluid">
		
			<div class="span2">
				<div class="well sidebar-nav">
				
					<img src="/images/school_logo.png" />
					<div class="nav-collapse">
						<?php
							$name = $this->session->userdata('username'); 
							if ($name){
								echo anchor(base_url('index.php/main/logout'), 'Logout', 'id="logout"');
								$name = $this->session->userdata('username');
								echo anchor(base_url("index.php/main/user/$name"), $name, 'id="username"');
							} else {
							}
						?>
						<div class="clearfix"></div>
						
						<ul id="navigation" class="nav nav-pills nav-stacked">
							<li id="home" class="active"><?=anchor('main/home', 'Home'); ?></li>
							<li id="problemset"><?=anchor('main/problemset', 'Problemset');?></li>
							<li id="status"><?=anchor('main/status', 'Status');?></li>
							<li id="task"><?=anchor('main/task', 'Task');?></li>
							<li id="contest"><?=anchor('contest', 'Contest');?></li>
							<li id="ranklist"><?=anchor('main/ranklist', 'Ranklist');?></li>
							<?php
								if ($this->session->userdata('priviledge') == 'admin')
									echo '<li id="admin">' . anchor('admin', 'Administer') . '</li>';
							?>
						</ul>
					</div>
					
				</div>
			</div>
			
			<div class="span10">
				<div id="page_content" class="well sidebar-nav">
