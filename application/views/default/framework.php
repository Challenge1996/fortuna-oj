<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?=OJ_TITLE?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="Fortuna Online Judge System Default Framework" />
		<meta name="author" content="moreD" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="css/style.css" rel="stylesheet">

		<script src="js/jquery.js"></script>
		<script src="js/jquery.form.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/framework.js"></script>
		<script src="js/jquery.hashchange.min.js"></script>
		<script src="application/third_party/ckeditor/ckeditor.js"></script>
		<script src="application/third_party/ckfinder/ckfinder.js"></script>

		<?php if (isset($head)) echo $head?>
		
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>

<body onload="init_framework()">
	<div id="nav_toggle" class="well" style="padding: 2px; position:fixed; top: 35px; left:0px">
		<i id="icon_nav_toggle" class="icon-arrow-left"></i>
	</div>
	<div id="scroll_tip" class="well" style="padding: 2px; position:fixed; right:0px; bottom:120px" onclick="javascript:scroll(0,0)">
		<i class="icon-arrow-up"></i>
	</div>
	
	<div class="container-fluid">
		<div class="row-fluid">
		
			<!-- Header -->
			<div class="span12">
				<div class="well" style="padding: 7px; min-height:560px">
					<div class="tabbable tabs-left">
						<ul id="navigation" class="nav nav-tabs pull-left">
							<li>
								<label id="countdown"></label>
							</li>
							<li>
								<div id="userinfo"></div>
								<div class="clearfix"></div>
							</li>
							<li class="nav_bar" id="nav_home"><a href="#main/home"><?=lang('home')?></a></li>
							<li class="nav_bar" id="nav_problemset"><a href="#main/problemset"><?=lang('problemset')?></a></li>
							<li class="nav_bar" id="nav_status"><a href="#main/status"><?=lang('status')?></a></li>
							<li class="nav_bar" id="nav_contest">
								<a href="#contest"><?=lang('contest')?>
									<span class="badge badge-important" id="running_contest_count" style="padding: 1px 4px"></span>
								</a>
							</li>
							<li class="nav_bar" id="nav_task"><a href="#task/task_list"><?=lang('task')?></a></li>
							<li class="nav_bar" id="nav_group"><a href="#group/group_list"><?=lang('groups')?></a></li>
							<li class="nav_bar" id="nav_ranklist"><a href="#main/ranklist"><?=lang('ranklist')?></a></li>
							<li class="nav_bar" id="nav_custom_test"><a href="#customtest/run"><?=lang('customtest')?></a></li>
							<li id="nav_admin" class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=lang('administer')?></a>
								<ul class="dropdown-menu">
									<li class="nav_bar"><a href="#admin/problemset">Problemset</a></li>
									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/contestlist">Contest List</a></li>
									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/task_list">Task List</a></li>
									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/users">Manage User</a></li>
 									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/statistic">Statistic</a></li>
									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/rejudge">Rejudge</a></li>
									<li class="nav_bar nav_admin" style="display:none"><a href="#admin/functions">Misc Functions</a></li>
								</ul>
							</li>
						</ul>
						
						<div id="page_content" class="tab-content" style="float:none"></div>
						
						<div class="clearfix"></div>
					</div>
					
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		
		<div class="clearfix"></div>
		
		<!-- footer -->
		<div class="row-fluid">
			<div class="span12" id="copyleft">
				<p>Project fortuna-oj hosting on <a href="http://github.com/roastduck/fortuna-oj">Github</a>.
				Powered by Codeigniter / Bootstrap<br />
				Author: <?=safe_mailto('moreDatPublic@gmail.com', 'moreD');?>, <?=safe_mailto('beantang.tang@gmail.com','RD')?><br />
				<label id="server_time"></label>
			</div>
		</div>
		
		<script type="text/javascript">
			timer = <?=mktime() * 1000?>;
			setInterval("$('#server_time').html('Server Time: ' + (new Date(timer).toString())); timer += 1000;", 1000);
			//var remain = (new Date("2013/5/18,8:00:00").getTime() - new Date(timer).getTime());
			//setInterval("$('#countdown').html('Time to GDOI:<br/><p style=\"text-align:center\">' + Math.floor(remain / (1000 * 86400)) + ' Days</p>'); remain += 1000;", 1000);
			
			var browser = navigator.userAgent;
			$('#scroll_tip').affix();
			<?php
				if ( ! $logged_in) echo 'var logged_in = false;';
				else echo 'load_userinfo(); var logged_in = true;';
			?>
			
			var hash = window.location.hash;
			if (hash.indexOf('main/problemset') != -1) $('#nav_problemset').addClass('active');
			else if (hash.indexOf('main/status') != -1) $('#nav_status').addClass('active');
			else if (hash.indexOf('task') != -1) $('#nav_task').addClass('active');
			else if (hash.indexOf('group') != -1) $('#nav_group').addClass('active');
			else if (hash.indexOf('contest') != -1) $('#nav_contest').addClass('active');
			else if (hash.indexOf('main/ranklist') != -1) $('#nav_ranklist').addClass('active');
			else if (hash.indexOf('main/customtest') != -1) $('#nav_custom_test').addClass('active');
			else if (hash.indexOf('main/home') != -1) $('#nav_home').addClass('active');
			else if (hash.indexOf('admin') != -1) $('#nav_admin').addClass('active');
		</script>
	</div>
	
	<div class="overlay"></div>
</body>

</html>
