<h4>
	Group List
	<input type="text" id="invitation_code" name="invitation_code" class="span2" style="margin-bottom:0; margin-left:3%" />
	<button class="btn btn-mini btn-primary" id="apply">Apply</button>
	<span id="status_indicator"></span>
	<?php 
	if ($this->user->is_admin())
		echo '<button class="btn btn-small btn-primary pull-right" onclick="window.location.href=\'#group/group_setting\'">New</button>';
	?>
</h4>
<hr />

<div><?php
	$count_control = 0;
	foreach ($groups as $row){
		if ($row->private && $this->session->userdata('priviledge') != 'admin' && $row->status == 'stranger') continue;
		if ($count_control == 0) echo '<div class="row-fluid">';
		
		echo '<div class="well span3"><fieldset>';
		$title = "$row->gid $row->name <span class='badge badge-info'> <i class='icon-user'></i>x$row->count</span>";
		echo "<legend><h5>";
		if ($row->status == 'admin') echo "<a href='#group/group_setting/$row->gid'>$title <i class='icon-cog'\"></i></a>";
		else {
			echo "<a href='#group/group_view/$row->gid'>$title </a>";
			if ($row->status == 'pending') echo "<span class='label'>Pending</span>";
			else if ($row->status == 'stranger') echo " <button class='btn btn-mini btn-primary' onclick=\"join('$row->gid', $(this))\">Join</button>";
		}
		if ($this->user->is_admin())
			echo "<button class='close pull-right' onclick=\"delete_group($row->gid, '$row->name')\">&times;</button>";
		echo '</h5></legend>';
		echo $row->description;
		echo '</fieldset></div>';
		
		$count_control++;
		if ($count_control == 4){
			echo '</div>';
			$count_control = 0;
		}
	}
?></div>

<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure to delete group: </p>
		<h3><div id="info"></div></h3>
		<p>All data related to this group will be lost!</p>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#apply').click(function(){
			code = $('#invitation_code').val();
			access_page('group/group_apply/' + code, function(responseText){
				if (responseText == 'success'){
					$('#status_indicator').html('Successfully Applied!');
					$('#status_indicator').removeClass();
					$('#status_indicator').addClass('alert alert-success');
				}
			}, false);
		})
	})

	function delete_group(gid, name){
		$('#modal_confirm #delete').live('click', function(){
			$('#modal_confirm').modal('hide');
			access_page('group/delete_group/' + gid);
		});
		$('#modal_confirm #info').html(gid + '. ' + name);
		$('#modal_confirm').modal({backdrop: 'static'});
	}
	
	function join(gid, object){
		access_page('group/group_join/' + gid, void 0, false);
		object.after("<span class='label'>Pending</span>");
		object.remove();
	}
</script>