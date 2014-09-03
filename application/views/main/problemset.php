<?php
	function strtrim($str){
		if (mb_strlen($str, 'UTF8') > 18) return (mb_substr($str, 0, 18, 'UTF8') . '..'); else return $str;
	}
?>
	
<script type="text/javascript" src="js/problemset.js"></script>

<form class="form-inline form-search" id="action_form" style="margin-left:10px; margin-right:10px">
	<div id="div_goto_pid" class="control-group input-prepend input-append">
		<span class="add-on"><?=lang('problem_id')?></span>
		<input type="number" min="1000" id="goto_pid" class="input-mini" />
		<button id="goto_button" class="btn"><?=lang('go')?></button>
	</div>
	
	<div id="div_search" class="control-group input-append">
		<input type="text" id="search_content" class="input-long" placeholder="Use '|' to split multiple keywords" value="<?=$keyword?>"/>
		<button id="search_button" class="btn"><?=lang('search')?></button>
	</div>
	
	<div id="div_filter" class="control-group input-append">
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
		<button id="filter_button" class="btn"><?=lang('filter')?></button>
	</div>
	
	<div id="div_goto_page" class="control-group input-prepend input-append pull-right">
		<span class="add-on"><?=lang('page')?></span>
		<input type="number" id="goto_page" min=1 class="input-mini" />
		<button id="btn_goto_page" class="btn"><?=lang('go')?></button>
	</div>
</form>

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
	</tr></thead>
	
	<tbody>
	<?php
		$category = $this->session->userdata('show_category') == 1;
		foreach ($data as $row){
			if ($row->isShowed == 0) continue;
			$pid = $row->pid;
	?>
			<tr>
				<td style="background-color:#e8e8e8">
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
				<td><?=$row->status?></td>
				<td>
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
				<td class="source"><?=strtrim($row->source)?></td>
				<td>
					<a href="#main/statistic/<?=$pid?>">
						<span class="badge badge-info"><?=$row->solvedCount?></span>
					</a>
				</td>
				<td>
					<a href="#main/statistic/<?=$pid?>">
						<span class="badge badge-info"><?=$row->submitCount?></span>
					</a>
				</td>
				<td>
					<span class="badge badge-info"><?=$row->average?> pts</span>
				</td>
			</tr>
			<tr class="note_text_tr note_text_tr_<?=$row->pid?>" style="display:none"><td colspan="8">
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
			</td></tr>
			<script type="text/javascript">
				$("#note_textarea_<?=$row->pid?>").live('keypress', function(event){
					if (event.keyCode == 13) close_note(<?=$row->pid?>);
				});
			</script>
		<?php } ?>
	</tbody>
</table></div>

<?=$this->pagination->create_links()?>

<!-- End of file problemset.php -->
