<form id="form_add_task">
	<table class="table table-condensed table-bordered table-hover">
	<?php
		foreach ($tasks as $task){
			echo "<tr><td><input type='checkbox' name='tid[]' value='$task->tid' class='select' /></td>";
			echo "<td>$task->tid</td><td class='title'><strong>$task->title</strong><div class='time'></div></td></tr>";
		}
	?>
	</table>
</form>

<script type="text/javascript">
	$('.select').change(function(){
		tid = $(this).val();
		time = '<span class="label" style="width: 40px; text-align:center">Start</span> \
			<input type="date" name="start_date[' + tid + ']" class="span5" value="<?=date('Y-m-d', strtotime('now'))?>" /> \
			<input type="time" name="start_time[' + tid + ']" class="span4" value="<?=date('H:i', strtotime('now'))?>" /> \
			<br /> \
			<span class="label" style="width: 40px; text-align:center">End</span> \
			<input type="date" name="end_date[' + tid + ']" class="span5" value="<?=date('Y-m-d', strtotime('+1 month'))?>" /> \
			<input type="time" name="end_time[' + tid + ']" class="span4" value="<?=date('H:i', strtotime('now'))?>" />';
		if ($(this).attr('checked')) $(this).parent().parent().find('.time').html(time);
		else $(this).parent().parent().find('.time').html('');
	})
</script>