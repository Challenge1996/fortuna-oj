<div class="row-fluid">
	<div class="well span4">
		<fieldset>
			<legend><h5>Basic Settings</h5></legend>
			
			<form id="settings" class="form-horizontal">
				<div class="control-group">
					<label for="name" class="control-label">Name</label>
					<div class="controls">
						<input type="text" class="input-block-level" name="name" value="<?=isset($grouping->name) ? $grouping->name : ''?>" disabled/>
					</div>
				</div>
				
				<div class="control-group">
					<label for="description" class="control-label">Description</label>
					<div class="controls">
						<textarea name="description" rows="5" class="input-block-level" disabled><?=isset($grouping->description) ? $grouping->description : ''?></textarea>
					</div>
				</div>		
				
				<div class="control-group">
					<label class="control-label">Type</label>
					<div class="controls controls-row">
						<label class="radio inline">
							<input type="radio" name="private" id="public" value="0" disabled <?=isset($grouping->private) && $grouping->private == 0 ? 'checked' : ''?> />
							Public
						</label>
						<label class="radio inline">
							<input type="radio" name="private" id="private" value="1" disabled <?=isset($grouping->private) && $grouping->private == 1 ? 'checked' : ''?> />
							Private
						</label>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Invitation</label>
					<div class="controls">
						<span class="uneditable-input input-block-level" id="code"><?=isset($grouping->invitationCode) ? $grouping->invitationCode : ''?></span>
					</div>
				</div>
				
			</form>
			
		</fieldset>
	</div>

	<div class="well span8">
		<fieldset>
			<legend><h5>Tasks</h5></legend>
		</fieldset>
		<?php
			$left = true;
			foreach ($grouping->tasks as $task){
				if ($left) echo '<div class="row-fluid">';
				echo '<div class="well span6" ' . ($left ? 'style="margin-left:0" ' : '') . '>';
				echo '<table class="table table-condensed table-hover table-bordered clear">';
				echo '<caption>';
				if ($task->new_title != 'NULL' && $task->new_title != '') echo $task->new_title; else echo $task->title;
				echo "<br /><a href='#task/statistic/$grouping->gid/$task->tid'>Statistic</a>";
				echo '</caption>';
				foreach ($task->problems as $problem) 
					echo "<tr><td><a href='#task/show/$problem->pid/$grouping->gid/$task->tid'>$problem->title</a></td></tr>";
				echo '</table></div>';
				$left = ! $left;
				if ($left) echo '</div>';
			}
			if ( ! $left) echo '</div>';
		?>
	</div>
</div>

<div class="row-fluid"><div class="well">
	<fieldset>
		<legend><h5>Members</h5></legend>

		<ul><?php
		if (isset($grouping)){
			foreach ($grouping->members as $row)
				if ($row->isAccepted){
					echo '<li style="display:inline; float:left"><div class="well" style="padding:5px">';
					echo "<span class='label label-info'><a href='#users/$row->name'>$row->name</a></span>";
					echo '</div></li>';
				}
		}
		?></ul>
	</fieldset>
</div></div>

<script>

</script>