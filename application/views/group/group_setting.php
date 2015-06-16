<div class="row-fluid">
	<div class="well span4">
		<fieldset>
			<legend><h5>
				Basic Settings
				<span id="indicator"></span>
				<button id="save_settings" class="btn btn-mini btn-primary pull-right">Save</button>
			</h5></legend>
			
			<form id="settings" class="form-horizontal">
				<div class="control-group">
					<label for="name" class="control-label">Name</label>
					<div class="controls">
						<input type="text" class="input-block-level" name="name" value="<?=isset($grouping->name) ? $grouping->name : ''?>"/>
					</div>
				</div>
				
				<div class="control-group">
					<label for="description" class="control-label">Description</label>
					<div class="controls">
						<textarea name="description" rows="5" class="input-block-level"><?=isset($grouping->description) ? $grouping->description : ''?></textarea>
					</div>
				</div>		
				
				<div class="control-group">
					<label class="control-label">Type</label>
					<div class="controls controls-row">
						<label class="radio inline">
							<input type="radio" name="private" id="public" value="0" <?=isset($grouping->private) && $grouping->private == 0 ? 'checked' : ''?> />
							Public
						</label>
						<label class="radio inline">
							<input type="radio" name="private" id="private" value="1" <?=isset($grouping->private) && $grouping->private == 1 ? 'checked' : ''?> />
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
			<legend><h5>
				Tasks
				<button id="add_task" class="btn btn-mini btn-primary pull-right">Add</button>
			</h5></legend>
		</fieldset>
		<?php
		if (isset($grouping)){
			$left = true;
			foreach ($grouping->tasks as $task){
				if ($left) echo '<div class="row-fluid">';
				
				echo '<div class="well span6" ' . ($left ? 'style="margin-left:0" ' : '') . '>';
				echo '<table class="table table-condensed table-hover table-bordered clear">';
				echo '<caption>';
				if ($task->new_title != 'NULL' && $task->new_title != '') echo $task->new_title; else echo $task->title;
				echo "<button class='close' onclick=\"access_page('group/group_delete_task/$grouping->gid/$task->tid')\">&times;</button>
					<i class='icon-cog' onclick=\"task_config($grouping->gid, $task->tid)\"></i>
					<br /><a href='#task/statistic/$grouping->gid/$task->tid'>Statistic</a>
					</caption>";
					
				foreach ($task->problems as $problem)
					echo "<tr><td><a href='#task/show/$problem->pid/$grouping->gid/$task->tid'>$problem->title</a></td></tr>";
					
				echo '</table></div>';
				
				$left = ! $left;
				if ($left) echo '</div>';
			}
			if ( ! $left) echo '</div>';
		}
		?>
	</div>
</div>

<div class="row-fluid"><div class="well">
	<fieldset>
		<legend><h5>
			Members
 			<!--<button id="add_member" class="btn btn-mini btn-primary pull-right">Add</button>-->
		</h5></legend>
		
		<div class="span4 well" style="margin-left:0px">
			<fieldset>
				<legend><h5>Verifying</h5></legend>
				<ul>
				<?php
				if (isset($grouping)){
					foreach ($grouping->members as $row)
						if ( ! $row->isAccepted){
							echo '<li style="display:inline; float:left"><div class="well" style="padding:5px; text-align:center">';
							echo "<span class='label label-info' style='margin:10px; font-size:20px; padding: 5px'><a href='#users/$row->name'>$row->name</a></span><br />";
							echo "<button class='btn btn-mini btn-success accept' onclick='accept($grouping->gid, $row->uid, $(this))'>Accepted</button>";
							echo "<button class='btn btn-mini btn-danger decline' onclick='decline($grouping->gid, $row->uid, $(this))'>Decline</button>";
							echo '</div></li>';
						}
				}
				?>
				</ul>
			</fieldset>
		</div>
		
		<div class="span8 well">
			<fieldset>
				<legend><h5>Verified</h5></legend>
				<ul>
				<?php
				if (isset($grouping)){
					foreach ($grouping->members as $row)
						if ($row->isAccepted){
							echo '<li style="display:inline; float:left"><div class="well" style="padding:5px">';
							echo "<span class='label label-info'><a href='#users/$row->name'>$row->name</a></span>";
							echo "<button class='close' onclick='member_delete($grouping->gid, $row->uid, $(this))'>&times;</button>";
							echo '</div></li>';
						}
				}
				?>
				</ul>
			</fieldset>
		</div>
	</fieldset>
</div></div>

<div id="add_task_modal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Add a Task to This Group</h3>
	</div>
	<div class="modal-body" id="available_tasks"></div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-success" id="confirm_add">Add</a>
	</div>	
</div>

<div id="config_task_modal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Task Configuration</h3>
	</div>
	<div class="modal-body" id="task_config"></div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-success" id="confirm_modify">Save</a>
	</div>	
</div>

<script>

var gid=<?=isset($grouping->gid) ? $grouping->gid : 0?>;

$(document).ready(function(){
	$('#save_settings').click(function(){
		$('#settings').ajaxSubmit({
			type: 'post',
			url: 'index.php/group/save_group_settings/' + gid,
			success: function(responseText){
				$('#indicator').html('Successfully Saved!');
				$('#indicator').removeClass();
				$('#indicator').addClass("alert alert-success");
				new_gid = parseInt(responseText);
				if (gid != new_gid) window.location.href="#group/group_setting/" + new_gid;
			},
			error: function(){
				$('#indicator').html('Change Not Saved!');
				$('#indicator').removeClass();
				$('#indicator').addClass("alert alert-danger");
			}
		})
		return false;
	}),
	
	$('#add_task').click(function(){
		if (gid == 0){
			alert('Please create group first!');
			return;
		}
		set_page_content('#available_tasks', 'index.php/group/add_task/' + gid);
		$('#add_task_modal').modal('show');
	}),
	
	$('#confirm_add').click(function(){
		$('#add_task_modal').modal('hide');
		$('#form_add_task').ajaxSubmit({
			type: 'post',
			url: 'index.php/group/group_add_tasks/' + gid,
			success: refresh_page
		})
	}),
	
	$('#confirm_modify').click(function(){
		$('#config_task_modal').modal('hide');
		$('#form_config_task').ajaxSubmit({
			type: 'post',
			success: refresh_page
		})
	})
});

function task_config(gid, tid){
	set_page_content('#task_config', 'index.php/group/task_config/' + gid + '/' + tid);
	$('#config_task_modal').modal('show');
}

function accept(gid, uid, object){
	access_page('group/group_member_accept/' + gid + '/' + uid, void 0, false);
	object.addClass('disabled');
	object.parent().children('.decline').hide();
}

function decline(gid, uid, object){
	access_page('group/group_member_decline/' + gid + '/' + uid, void 0, false);
	object.addClass('disabled');
	object.parent().children('.accept').hide();
}

function member_delete(gid, uid, object){
	access_page('group/group_member_delete/' + gid + '/' + uid, void 0, false);
	object.parent().parent().fadeOut();
}

</script>
