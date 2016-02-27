<?php
	function strtrim($str){
		if (mb_strlen($str, 'UTF8') > 18) return (mb_substr($str, 0, 18, 'UTF8') . '..'); else return $str;
	}
?>

<script>
	var option_reverse_order = "<i class='icon-refresh'></i> <?=lang('reverse_order');?>";
	var option_show_in_control = "<i class='icon-user'></i> <?=lang('show_in_control');?>";
	var including_hidden = "<?=lang('including_hidden')?>";
	var option_select_starred = "<i class='icon-star'></i> <?=lang('select_starred')?>";
	var option_select_noted = "<i class='icon-tags'></i> <?=lang('select_noted')?>";
	var option_match_in_note = "<i class='icon-tags'></i> <?=lang('match_in_note')?>";
</script>
<script type="text/javascript" src="js/problemset.js"></script>
<!--
<style>
	th.spliter, td.spliter, td.uploader, td.chg_status, td.chg_nosubmit, td.btn_edit, td.btn_configure, td.btn_del
	{
		box-shadow: -7px 7px 5px #aaaaaa;
		position: relative;
	}
	th.spliter, td.spliter { z-index: 11 }
	td.uploader { z-index: 10; }
	td.chg_status { z-index: 9; }
	td.chg_nosubmit { z-index: 8; }
	td.btn_edit { z-index: 7; }
	td.btn_configure { z-index: 6; }
	td.btn_del { z-index: 5; }
</style>
-->
<div style="margin-left:10px; margin-right:10px">
	<div id="div_goto_pid" class="control-group input-prepend input-append">
		<span class="add-on"><strong><font color="#006652"><?=lang('problem_id')?></font></strong></span>
		<input type="number" min="1000" id="goto_pid" class="input-mini" />
		<div class="btn-group">
			<span id="goto_button" class="btn"><?=lang('go')?></span>
			<span class="btn dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
			</span>
			<ul class="dropdown-menu">
				<li><a id="btn_edit_pid"><?=lang('edit_problem')?></a></li>
				<li><a id="btn_configure_pid"><?=lang('configure_data')?></a></li>
			</ul>
		</div>
	</div>

	<button class="btn btn-primary pull-right" onclick="window.location.hash='admin/addproblem'"><?=lang('add_problem')?></button>
	<button class="btn btn-link pull-right" onclick="window.location.hash='admin/problemset?old_version'"><?=lang('old_version_admin')?></button>
</div>

<form class="form-inline" id="action_form" style="margin-left:10px; margin-right:10px">

	<div id="div_search" class="control-group input-prepend input-append">
		<span class="add-on"><strong><font color="#006652"><?=lang('search_title_and_source')?></font></strong></span>
		<input type="text" id="search_content" class="input-xlarge" placeholder="Use '|' to split multiple keywords" value="<?=$keyword?>"/>
		<select id="filter_content" style="width:140px">
			<option value="0">All</option>
		<?php
			foreach ($category as $id => $name) {
				if ($id == $filter) $selected = 'selected';
				else $selected = '';
				
				echo "<option value='$id' $selected>$name</option>";
			}
		?>
		</select>
		<span id="search_button" class="btn"><?=lang('search')?></span>
	</div>
	<span id="adv_button" class="btn btn-link"><?=lang('p_advanced')?></span>
	
	<div id="div_goto_page" class="control-group input-prepend input-append pull-right">
		<span class="add-on"><strong><font color="#006652"><?=lang('page')?></font></strong></span>
		<input type="number" id="goto_page" min=1 class="input-mini" />
		<span id="btn_goto_page" class="btn"><?=lang('go')?></span>
	</div>
</form>

<?php
	$row_cnt = 0;
	$spanflag = false;
	foreach ($data as $row)
	{
		if (!$row->isShowed && !$this->user->is_admin() && $this->user->uid() != $row->uid) continue;
		$row_cnt++;
	}
?>

<div class="problemset_table"><table class="table table-bordered table-striped table-condensed table-hover">
	<thead style="background-color:#89cff0"><tr>
		<th>
			<i class="icon-info-sign" title="private bookmarks"></i>
			<i class="icon-resize-full" id="open_all_icon" title="open all notes" onclick="toggle_open_all();"></i>
		</th>
		<th class="status"><?=lang('status')?></th>
		<th class="pid"><?=lang('problem_id')?></th>
		<th class="title"><?=lang('title')?></th>
		<th class="source"><?=lang('Problemset_source')?></th>
		<th class="solvedCount"><?=lang('solved')?></th>
		<th class="submitCount"><?=lang('submit')?></th>
		<th class="avg"><?=lang('average')?></th>
		<th class="spliter"></th>
		<th class="uploader"><?=lang('uploader')?></th>
		<th class="chg_status"><?=lang('show_hide')?></th>
		<th class="chg_nosubmit"><?=lang('allow_submit')?></th>
		<th class="btn_edit"><?=lang('edit_problem')?></th>
		<th class="btn_configure"><?=lang('configure_data')?></th>
		<th class="btn_del"></th>
	</tr></thead>
	
	<tbody>
	<?php
		$category = $this->session->userdata('show_category') == 1;
		foreach ($data as $row){
			if (!$row->isShowed && !$this->user->is_admin() && $this->user->uid() != $row->uid) continue;
			$pid = $row->pid;
	?>
			<tr style="height:0px">
				<td style="white-space:nowrap;background-color:#e8e8e8">
					<?php if (isset($row->bookmark) && $row->bookmark->note): ?>
						<i class="icon-tags" title="add notes" id="note_icon_<?=$row->pid?>" onclick="open_note(<?=$row->pid?>);"></i>
					<?php else: ?>
						<i class="icon-tags icon-white" title="add notes" id="note_icon_<?=$row->pid?>" onclick="open_note(<?=$row->pid?>);"></i>
					<?php endif ?>

					<?php if (isset($row->bookmark) && $row->bookmark->starred): ?>
						<i class="icon-star" title="unstar it" id="star_icon_<?=$row->pid?>" onclick="upd_star(<?=$row->pid?>);"></i>
					<?php else: ?>
						<i class="icon-star icon-white" title="star it" id="star_icon_<?=$row->pid?>" onclick="upd_star(<?=$row->pid?>);"></i>
					<?php endif ?>
				</td>
				<td class="status"><?=$row->status?></td>
				<td class="pid">
					<a href='#main/show/<?=$pid?>'><?=$pid?></a>
				</td>
				<td class="title">
					<a href="#main/show/<?=$pid?>"><?=$row->title?></a>
				<?php
					if ($category || $row->ac) {
						foreach ($row->category as $id => $tag) {
				?>
							<span class='label pull-right' style="cursor:pointer"
								onclick='window.location.href="#main/problemset?filter=<?=$id?>"'>
							<?=$tag?>
							</span>
				<?php 	
						}
					} 
				?>
				</td>
				<td style="white-space:nowrap" class="source"><?=strtrim($row->source)?></td>
				<td class="solvedCount">
					<a href="#main/statistic/<?=$pid?>">
						<span class="badge badge-info"><?=$row->solvedCount?></span>
					</a>
				</td>
				<td class="submitCount">
					<a href="#main/statistic/<?=$pid?>">
						<span class="badge badge-info"><?=$row->submitCount?></span>
					</a>
				</td>
				<td class="avg">
					<span class="badge badge-info"><?=$row->average?> pts</span>
				</td>
				<?php
					if (!$spanflag):
				?>
						<td rowspan="<?=$row_cnt*2?>" class="spliter" style="height:100%">
							<button class="spliter btn"><i class="spliter"></i></button>
							<style>
								td.spliter {
									padding-right:0;
									padding-left:0;
									padding-top:0;
									padding-bottom:0;
								}
								button.spliter {
									width:100%;
									height:100%;
									padding-right:0;
									padding-left:0;
									padding-top:0;
									padding-bottom:0;
									margin-right:0;
									margin-left:0;
									margin-top:0;
									margin-bottom:0;
								}
							</style>
						</td>
				<?php if ($spliter == 'right'): ?>
					<script>
						(function(){
							$('i.spliter').addClass('icon-indent-left');
							var query = {};
							Object.assign(query, origin_query);
							query['spliter'] = 'left';
							$('button.spliter').click(function(){load_page(window.location.hash.split('?')[0] + '?' + $.param(query));});
						})();
					</script>
					<style> .solvedCount, .submitCount, .avg { display: none; } </style>
				<?php else: ?>
					<script>
						(function(){
							$('i.spliter').addClass('icon-indent-right');
							var query = {};
							Object.assign(query, origin_query);
							query['spliter'] = 'right';
							$('button.spliter').click(function(){load_page(window.location.hash.split('?')[0] + '?' + $.param(query));});
						})();
					</script>
					<style> .uploader, .chg_status, .chg_nosubmit, .btn_edit, .btn_configure, .btn_del { display: none; } </style>
				<?php endif; ?>
				<?php
						$spanflag = true;
					endif;
				?>
				<?php
					if ($this->user->is_admin()) {
						$isShowed=($row->isShowed?'<span class="label label-success">Showed</span>':'<span class="label label-important">Hidden</span>');
					} else {
						$isShowed=($row->isShowed?'<span class="label">Showed</span>':'<span class="label">Hidden</span>');
					}
					if ($row->hasControl)
					{
						$noSubmit=($row->noSubmit?'<span class="label label-important">Disallowing</span>':'<span class="label label-success">Allowing</span>');
					} else
					{
						$noSubmit=($row->noSubmit?'<span class="label">Disallowing</span>':'<span class="label">Allowing</span>');
					}
				?>
				<td class="uploader"><span class='label label-info'><?=$row->author?></span></td>
				<td class="chg_status row<?=$row->pid?>"><a><?=$isShowed?></a></td>
				<td class="chg_nosubmit row<?=$row->pid?>"><a><?=$noSubmit?></a></td>
				<td class="btn_edit row<?=$row->pid?>"><button class='btn btn-mini'>Edit</button></td>
				<td class="btn_configure row<?=$row->pid?>"><button class='btn btn-mini'>Configure</button></td>
				<td class="btn_del row<?=$row->pid?>"><button class='close'>&times;</button></tr>
				<?php if ($row->hasControl): ?>
					<script>
						(function(){
							var pid = <?=$row->pid?>;
						<?php if ($this->user->is_admin()): ?>
							$("td.chg_status.row"+pid).children('a').click(
								function(){access_page('#admin/change_problem_status/'+pid);}
							);
						<?php endif; ?>
							$("td.chg_nosubmit.row"+pid).children('a').click(
								function(){access_page('#admin/change_problem_nosubmit/'+pid);}
							);
							$("td.btn_edit.row"+pid).children('button').click(
								function(){window.location.href='#admin/addproblem/'+pid;}
							);
							$("td.btn_configure.row"+pid).children('button').click(
								function(){window.location.href='#admin/dataconf/'+pid;}
							);
							$("td.btn_del.row"+pid).children('button').click(
								function(){delete_problem(pid, $(this));}
							);
						})();
					</script>
				<?php else: ?>
					<script>
						(function(){
							var pid = <?=$row->pid?>;
							$("td.btn_edit.row"+pid).children('button').addClass('disabled');
							$("td.btn_configure.row"+pid).children('button').addClass('disabled');
							$("td.btn_del.row"+pid).children('button').hide();
						})();
					</script>
				<?php endif; ?>
			</tr>
			<tr class="note_text_tr note_text_tr_<?=$row->pid?>" style="display:none">
				<td colspan="5">
					<div class="note_text_tr note_text_tr_<?=$row->pid?>" style="display:none">
						<input style="width:98%" \
	placeholder="Put your private note for this problem here." \
	maxlength="255" id="note_textarea_<?=$row->pid?>" \
	onblur="close_note(<?=$row->pid?>);" \
	value='<?php if (isset($row->bookmark) && ($row->bookmark->note)) echo $row->bookmark->note; ?>' \
	></input>
						<?php if (isset($row->bookmark) && ($row->bookmark->note)): ?>
							<script type="text/javascript">
								$(".note_text_tr_<?=$row->pid?>").addClass("note_text_tr_nonempty");
							</script>
						<?php endif ?>
					</div>
				</td>
				<td class="solvedCount" colspan="3"></td>
				<td class="chg_status" colspan="6"></td>
			</tr>
			<script type="text/javascript">
				$("#note_textarea_<?=$row->pid?>").live('keypress', function(event){
					if (event.keyCode == 13) close_note(<?=$row->pid?>);
				});
			</script>
		<?php } ?>
	</tbody>
</table></div>

<?=$this->pagination->create_links()?>

<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure to delete problem: </p>
		<h3><div id="info"></div></h3>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<!-- End of file problemset.php -->
