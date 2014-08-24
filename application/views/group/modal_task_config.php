<form id="form_config_task" class="form" action="index.php/group/group_task_configuration/<?="$gid/$tid"?>">
	<span class="label" style="width: 45px; text-align:center">
		Title <a id="tips" title="Leave empty to use original title"><i class="icon-question-sign"></i></a>
	</span>
	<input type="text" name="title" value="<?=$title?>" />
	<br />
	<span class="label" style="width: 45px; text-align:center">Start</span>
	<input type="date" name="start_date" class="span4" value="<?=date('Y-m-d', strtotime($startTime))?>" />
	<input type="time" name="start_time" class="span4" value="<?=date('H:i', strtotime($startTime))?>" />
	<br />
	<span class="label" style="width: 45px; text-align:center">End</span>
	<input type="date" name="end_date" class="span4" value="<?=date('Y-m-d', strtotime($endTime))?>" />
	<input type="time" name="end_time" class="span4" value="<?=date('H:i', strtotime($endTime))?>" />
	<br />
</form>

<script type="text/javascript">
	$('#tips').tooltip({placement: 'right'});
</script>