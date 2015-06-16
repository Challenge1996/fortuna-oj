<h4>Task List</h4>
<hr />

<div class="row-fluid">

<?php 
	foreach ($data as $group){ 
?>
	<div class="well span4" style="padding:10px">
		<fieldset>
			<legend><h5 style="margin:0"><a href="#group/group_view/<?=$group->gid?>"><?=$group->name?></a></h5></legend>
			<?php
				foreach ($group->tasks as $task){
					if (strtotime($task->startTime) > time()) continue;
			?>
					<table class="table table-condensed table-bordered table-hover clear">
						<caption>
							<h5 style="margin-bottom: 3px"><a class="title" title="<?=$task->description?>" href="#task/statistic/<?=$group->gid?>/<?=$task->tid?>"><?php
								if ($task->new_title != 'NULL' && $task->new_title != '') echo $task->new_title; else echo $task->title;
							?></a>
							<?=strtotime($task->endTime) >= time() ?
								"<br /><span class='label'>Deadline</span> $task->endTime" : '<span class="label">End</span>'?>
							</h5>
						</caption>
						
						<tbody>
						<?php
							foreach ($task->problems as $problem){
								echo "<tr><td><a href='#task/show/$problem->pid/$task->gid/$task->tid'>$problem->title</a></td>";
								if (isset($problem->status)) {
									$score = round($problem->status, 1);
									echo "<td><span class='badge badge-info'>$score</span></td></tr>";
								} else
									echo '<td></td></tr>';
							}
						?>
						</tobdy>
					</table>
					
			<?php } ?>
		</fieldset>
	</div>

<?php } ?>
</div>

<script type="text/javascript">
	$('.title').tooltip();
</script>
