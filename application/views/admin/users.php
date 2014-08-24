<table class="table table-bordered table-condensed table-stripped">
	<thead>
		<th>uid</th><th>Name</th><th>School</th><th>Status</th><th>Priviledge</th><th>Groups</th>
		<th>Last IP Addr</th>
		<th>Last Login</th>
		<th></th>
	</thead>
	<tbody><?php
		foreach ($data as $row){
			echo "<tr><td>$row->uid</td>";
			echo "<td><span class='label label-info name'><a href='#users/$row->name'>$row->name</a></span></td>";
			echo "<td>$row->school</td>";
			echo "<td><span style='width:55px; text-align:center' onclick=\"user_change_status($row->uid, $(this))\"";
			if ($row->isEnabled) echo 'class="label label-success">Enabled';
			else echo 'class="label label-important">Disabled';
			echo '</span></td><td>';
			if ($row->priviledge == 'admin') echo '<span class="label label-warning">Administrator</span>';
			else echo '<span class="label">User</span>';
			echo '</td><td>';
			foreach ($row->groups as $group) echo "<span class=\"label\">$group->name</span> ";
			echo "<td>$row->lastIP</td>";
			echo "<td>$row->lastLogin</td>";
			echo "</td><td><button class='close' onclick=\"delete_user($row->uid, $(this))\">&times;</button></td></tr>";
		}
	?></tbody>
</table>

<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure to delete user: </p>
		<h3><div id="info"></div></h3>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<script type="text/javascript">
	function delete_user(uid, selector){
		$('#modal_confirm #delete').live('click', function(){
			$('#modal_confirm').modal('hide');
			access_page('admin/delete_user/' + uid);
		});
		$('#modal_confirm #info').html(uid + '. ' + selector.parent().parent().find('.name').html());
		$('#modal_confirm').modal({backdrop: 'static'});
	}
	
	function user_change_status(uid, selector){
		access_page('admin/change_user_status/' + uid, function(){
			if (selector.hasClass('label-success')){
				selector.removeClass('label-success');
				selector.addClass('label-important');
				selector.html('Disabled');
			} else {
				selector.removeClass('label-important');
				selector.addClass('label-success');
				selector.html('Enabled');
			}
		}, false);
	}
</script>
