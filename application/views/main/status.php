<?php
	function my_set_checkbox($array, $value){
		if ($array != NULL && in_array($value, $array)) return 'checked';
	}

	$allowed_download = '';
?>

<div class="status_table">
	<form class="accordion form form-inline" id="form_filter" method="get">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a id="filter_toggle" class="accordion-toggle" data-toggle="collapse" data-parent="#form_filter" data-target="#filters">
					<span>
						<b><?=lang('status_filters')?></b>
						<i id="filter_tips" class="icon-info-sign" title="Select none means select all!"></i>
					</span>
					<i class="icon-chevron-down pull-right"></i>
				</a>
			</div>
			<div class="accordion-body collapse" id="filters">
				<div class="accordion-inner">
					<div id="problem_filter" class="filter">
						<span>
							<b><?=lang('problem')?></b>
							<i class="icon-plus-sign" id="add_problem" rel="popover"></i>
							<span id="problems"></span>
							<span id="popover_add_problem"></span>
						</span>
					</div>
					
					<div id="user_filter" class="filter">
						<span>
							<b><?=lang('user')?></b>
							<i class="icon-plus-sign" id="add_user"></i>
							<span id="popover_add_user"></span>
						</span>
						<span id="users"></span>
					</div>
					
					<div>
						<span><b><?=lang('result')?></b> </span>
						<label for="status">
							<input type="checkbox" name="status[]" value="-1" <?=my_set_checkbox($filter['status'], -1)?> />
							<span class="label"><?=lang('pending')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="-2" <?=my_set_checkbox($filter['status'], -2)?> />
							<span class="label label-important"><?=lang('running')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="0" <?=my_set_checkbox($filter['status'], 0)?> />
							<span class="label label-success"><?=lang('accepted')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="1" <?=my_set_checkbox($filter['status'], 1)?> />
							<span class="label label-important"><?=lang('presentation_error')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="2" <?=my_set_checkbox($filter['status'], 2)?> />
							<span class="label label-important"><?=lang('wrong_answer')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="3" <?=my_set_checkbox($filter['status'], 3)?> />
							<span class="label label-info"><?=lang('checker_error')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="4" <?=my_set_checkbox($filter['status'], 4)?> />
							<span class="label label-warning"><?=lang('output_limit_exceeded')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="5" <?=my_set_checkbox($filter['status'], 5)?> />
							<span class="label label-warning"><?=lang('memory_limit_exceeded')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="6" <?=my_set_checkbox($filter['status'], 6)?> />
							<span class="label label-warning"><?=lang('time_limit_exceeded')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="7" <?=my_set_checkbox($filter['status'], 7)?> />
							<span class="label label-important"><?=lang('runtime_error')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="8" <?=my_set_checkbox($filter['status'], 8)?> />
							<span class="label"><?=lang('compile_error')?></span>
						</label>
						<label for="status">
							<input type="checkbox" name="status[]" value="9" <?=my_set_checkbox($filter['status'], 9)?> />
							<span class="label"><?=lang('internal_error')?></span>
						</label>
					</div>
					
					<div>
						<span><b><?=lang('language')?></b> </span>
						<label for="languages">
							<input type="checkbox" name="languages[]" value="C" <?=my_set_checkbox($filter['languages'], 'C')?> />
							C
						</label> 
						<label for="languages">
							<input type="checkbox" name="languages[]" value="C++" <?=my_set_checkbox($filter['languages'], 'C++')?> />
							C++
						</label> 
						<label for="languages">
							<input type="checkbox" name="languages[]" value="C++11" <?=my_set_checkbox($filter['languages'], 'C++11')?> />
							C++11(0x)
						</label> 
						<label for="languages">
							<input type="checkbox" name="languages[]" value="Pascal" <?=my_set_checkbox($filter['languages'], 'Pascal')?> />
							Pascal
						</label> 
					</div>
				</div>
				<button class="btn btn-primary btn-mini pull-right" id="btn_filter" style="margin:5px 10px"><?=lang('filter')?></button>
			</div>
		</div>
	</form>
	
	<table class="table table-condensed table-striped" style="text-align:center">
		<thead><tr>
			<th><?=lang('sid')?></th>
			<th><?=lang('problem')?></th>
			<th><?=lang('user')?></th>
			<th><?=lang('result')?></th>
			<th><?=lang('time')?></th>
			<th><?=lang('memory')?></th>
			<th><?=lang('language')?></th>
			<th><?=lang('length')?></th>
			<th><?=lang('submit_time')?></th>
			<th><?=lang('access')?></th>
			<th></th>
			<?php if ($this->user->is_admin()) echo '<th></th>'; ?>
		</tr></thead>
		
		<tbody><?php
		foreach ($data as $row){
			if (($row->isShowed == 0 || !$this->problems->is_showed($row->pid)) && !$this->user->is_admin()) continue;
				
			echo "<tr><td>$row->sid</td>";
			
			if ( ! isset($row->tid) || $row->tid == NULL) {
				echo "<td><a href=\"#main/show/$row->pid\">$row->pid</a></td>";
			} else {
				echo "<td><a href=\"#task/show/$row->pid/$row->gid/$row->tid\">$row->pid</a>";
				echo "<i class='icon-info-sign' title='Task: $row->tid'></i></td>";
			}
			echo "<td><span class=\"label label-info\"><a href=\"#users/$row->name\">$row->name</a></span></td>";

			echo '<td>';
			if ($row->status < 0 && $row->status > -3) echo $row->result;
			elseif ($row->status == 8 || $row->status == 9) echo "<a href=\"#main/result/$row->sid\">$row->result</a>";
			else{
				switch ($row->status) {
					case -3: ;
					case 0: $tag = 'label-success'; break;
					case 1: ;
					case 2: ;
					case 7: $tag = 'label-important'; break;
					case 3: $tag = 'label-info'; break;
					case 4:
					case 5:
					case 6: $tag = 'label-warning'; break;
					default: $tag = '';
				}
				
				$sname = "$row->result <span class=\"label $tag\">" . round($row->score, 1) . '</span>';
				echo "<a href=\"#main/result/$row->sid\"> $sname </a>";
				if ($row->sim != NULL && $row->sim != '') {
					$sim = json_decode($row->sim);
					if ($sim->similarity>50)
						echo "<a class='sim' href=\"#main/code/$sim->sid\" data-original-title='$sim->name: $sim->sid'>($sim->similarity%)</a>";
				}
			}
			echo '</td>';
			
			//if ($row->codeLength > 0) {
			
				if ($row->status == -3 || ($row->status >= 0 && $row->status <= 7)) {
					echo "<td><span class=\"label label-info\">$row->time</span></td>";
					echo "<td><span class=\"label label-info\">$row->memory</span></td>";
				} else echo '<td>---</td><td>---</td>';
				
				if ($row->uid == $this->user->uid() || $this->user->is_admin() || ! $row->private)
					echo "<td><a href=\"#main/code/$row->sid\">$row->language</a></td>";
				else echo "<td>$row->language</td>";
				echo "<td>$row->codeLength</td>";
				
			/*} else {
				echo '<td>---</td><td>---</td>';
				if ($row->uid == $this->user->uid() || $this->user->is_admin() || ! $row->private) {
					echo "<td>File <a href='index.php/main/download/$row->pid/$row->sid.compressed/1/submission' target='_blank'>
						<i class='icon-download-alt'></i></a></td>";
					$allowed_download .= "$row->sid.compressed";
				} else echo '<td>---</td>';
				echo '<td>---</td>';
			}*/
			
			echo "<td>$row->submitTime</td>";
			
			echo '<td>';
			if ($this->user->uid() == $row->uid || $this->user->is_admin()){
				echo "<a onclick=\"access_page('main/submission_change_access/$row->sid')\">";
				if ($row->private == 1) echo '<i class="icon-lock"></i>'; else echo '<i class="icon-globe"></i>';
				echo '</a>';
			} else if ($row->private == 0) echo '<i class="icon-globe"></i>';
			echo '</td>';
			
			echo '<td>';
			if ($this->user->is_admin()){
				echo "<a onclick=\"access_page('admin/change_submission_status/$row->sid')\">";
				if ($row->isShowed == 1) echo '<i class="icon-eye-open"></i>';
				else echo '<i class="icon-eye-close"></i>';
				echo '</a>';
			}
			echo '</td>';
			
			echo '</tr>';
		}
		?></tbody>
		
	</table>
</div>
	
<?=$this->pagination->create_links()?>
<?php
	$this->session->set_userdata('download', $allowed_download);
?>

<script type="text/javascript">
	var problems = [<?php
		if (isset($filter['problems']))
			foreach ($filter['problems'] as $pid) echo $pid . ',';
	?>];
	var users = [<?php
		if (isset($filter['users']))
			foreach ($filter['users'] as $pid) echo "'$pid',";
	?>];
	var url = "<?=$filter['url']?>";
	if (url != window.location.hash.substr(1)){
		window.preventHashchange = true;
		window.location.hash = '#' + url;
	}

	for (pid in problems) add_problem(problems[pid]);
	for (name in users) add_user(users[name]);
	
	$(document).ready(function(){
		$('.sim').tooltip(),
		$('#filter_tips').tooltip({placement: 'right'}),
		
		$('#add_problem').tooltip({
			html: true,
			placement: 'right',
			trigger: 'click',
			selector: $('#popover_add_problem'),
			title: '<input id="problem" type="text" class="input-mini" /> \
					<button id="btn_add_problem" class="btn btn-mini">Add</button>'
		}),
		
		$('#add_user').tooltip({
			html: true,
			placement: 'right',
			trigger: 'click',
			selector: $('#popover_add_user'),
			title: '<input id="user" type="text" class="input-mini" /> \
					<button id="btn_add_user" class="btn btn-mini">Add</button>'
		}),
		
		$('#btn_add_problem').die(),
		$('#btn_add_problem').live('click', function(){
			$('#add_problem').tooltip('hide');
			var pid = $('#problem').val();
			$('#problem').val('');
			add_problem(pid);
			return false;
		}),
		
		$('#btn_add_user').die(),
		$('#btn_add_user').live('click', function(){
			$('#add_user').tooltip('hide');
			var name = $('#user').val();
			$('#user').val('');
			add_user(name);
			return false;			
		}),
		
		$('.close').die(),
		$('.close').live('click', function(){
			var selector = '#' + $(this).parent().attr('data');
			$(selector).remove();
			$(this).parent().remove();
		}),
		
		$('.pagination a').click(function(){
			filter(hash_to_url($(this).attr('href')), $(this).attr('href').substr(1));
			return false;
		}),
		
		$('#btn_filter').click(function(){
			filter("index.php/main/status", "main/status");
			return false;
		}),
		
		$('#filter_toggle').click(function(){
			if ($(this).children('i').hasClass('icon-chevron-down')){
				$(this).children('i').removeClass('icon-chevron-down');
				$(this).children('i').addClass('icon-chevron-up');
			}else{
				$(this).children('i').removeClass('icon-chevron-up');
				$(this).children('i').addClass('icon-chevron-down');
			}
		})
	});
	
	function filter(url, hash){
		$('.overlay').css({'z-index': '1000', 'display': 'block'});
		$('.overlay').animate({opacity: '0.5'}, 250);
		
		$('#form_filter').ajaxSubmit({
			url: url,
			success: function(responseText){
				$('.overlay').css({'z-index': '-1000', 'display': 'none'});
				$('.overlay').animate({opacity: '0'}, 250);
				$('#page_content').html(responseText);
			}
		});
	}
	
	function add_problem(pid){
		$('#problems').append("<span class='label label-info' data='problem_" + pid + "'>" + pid + "<span class='close' style='line-height:14px'>&times;</span></span> ");
		$('#problems').append("<input type='hidden' name='problems[]' id='problem_" + pid + "' value='" + pid + "' />");
	}
	
	function add_user(name){
		$('#users').append("<span class='label label-info' data='user_" + name + "'>" + name + "<span class='close' style='line-height:14px'>&times;</span></span> ");
		$('#users').append("<input type='hidden' name='users[]' id='user_" + name + "' value='" + name + "' />");
	}
	
	//refresh_flag = setTimeout("refresh_page()", 10000);
</script>

<!-- End of file status.php -->
