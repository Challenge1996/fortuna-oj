<div class="mb_10 pull-right">
	<i class="icon-info-sign" style="vertical-align:middle" title="Only users that not enabled and never login will be deleted."></i>
	<button class="btn btn-small btn-danger" onclick="delete_unused_users()"><i class="icon-eye-close icon-white"></i> Delete <span id="unused_count"><?=$unused?></span> Unused Users</button>
</div>
<table class="table table-bordered table-condensed table-stripped">
	<thead style="background-color:#89cff0">
		<?php foreach (array(
			'uid' => 'UID',
			'name' => 'Name',
			'school' => 'School',
			'isEnabled' => 'Status',
			'priviledge' => 'Privilege',
			'groups' => 'Groups',
			'lastIP' => 'Last IP Address',
			'lastLogin' => 'Last Login Time',
			'expiration' => 'Expiration') as $key => $title): ?>
			<th style='white-space: nowrap'>
				<?php
					$iconType = $keyword!=$key?'icon-resize-vertical':($order!='reverse'?'icon-arrow-up':'icon-arrow-down');
					$iconUrl = ($keyword!=$key||$order=='reverse')?"#admin/users?sort=$key":"#admin/users?sort=$key&order=reverse";
				?>
				<a href='<?=$iconUrl?>'>
					<?=$title?>
					<i class='<?=$iconType?>'></i>
				</a>
				<?php if ($key == 'school'): ?>
					<span class='btn btn-mini pull-right school-display' onclick='edit_school();'>Edit</span>
					<span class='btn btn-mini btn-primary pull-right school-edit' style='display:none' onclick='submit_school();'>Save</span>
				<?php endif; ?>
			</th>
		<?php endforeach; ?>
		<th></th>
	</thead>
	<tbody><?php
		foreach ($data as $row){
			echo "<tr><td>$row->uid</td>";
			echo "<td><span class='label label-info name'><a href='#users/$row->name'>$row->name</a></span></td>";
			echo "<td>
				<span class='school-display'>$row->school</span>
				<input type='text' class='school-edit' style='display:none' onchange='newSchool[$row->uid]=$(this).val()' value='$row->school' />
			</td>";
			echo "<td><span style='width:55px; text-align:center' onclick=\"user_change_status($row->uid, $(this))\"";
			if ($row->isEnabled) echo 'class="label label-success">Enabled';
			else echo 'class="label label-important">Disabled';
			echo '</span></td><td>';
			echo '<div class="dropdown">';
			$display = "style='display:none'";
			if ($row->priviledge == 'admin')
				echo "<span id='bt$row->uid' class='label label-warning dropdown-toggle' data-toggle='dropdown' role='button'>Administrator</span>";
			else if ($row->priviledge == 'user')
				echo "<span id='bt$row->uid' class='label dropdown-toggle' data-toggle='dropdown' role='button'>User</span>";
			else {
				echo "<span id='bt$row->uid' class='label label-inverse dropdown-toggle' data-toggle='dropdown' role='button'>Restricted</span>";
				$display = '';
			}
			echo "<ul class='dropdown-menu' role='menu' aria-labelledby='bt$row->uid'>";
			echo "<li role='presentation'><a onclick='change_user_priviledge($row->uid,\"admin\",$(\"#bt$row->uid\"),$(\".op$row->uid\"));'>Administrator</a></li>";
			echo "<li role='presentation'><a onclick='change_user_priviledge($row->uid,\"user\",$(\"#bt$row->uid\"),$(\".op$row->uid\"));'>User</a></li>";
			echo "<li role='presentation'><a onclick='change_user_priviledge($row->uid,\"restricted\",$(\"#bt$row->uid\"),$(\".op$row->uid\"));'>Restricted</a></li>";
			echo "<li role='presentation' class='divider op$row->uid' $display></li>";
			echo "<li role='presentation' class='op$row->uid' $display><a href='#admin/setallowing/$row->uid'>Set Allowing</a></li>";
			echo '</ul>';
			echo '</div>';
			echo '</td><td>';
			foreach ($row->groups as $group) echo "<span class=\"label\">$group->name</span> ";
			echo "</td><td>$row->lastIP</td>";
			echo "<td class='lastlogin'>$row->lastLogin</td>";
			echo "<td>$row->expiration</td>";
			echo "<td><button class='close' onclick=\"delete_user($row->uid, $(this))\">&times;</button>";
			if ($row->isUnused) echo "<span><i class='icon-eye-close'></i></span>";
			echo "</td></tr>";
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

<div class="modal hide fade" id="modal_confirm_unused">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<h4>Are you sure to delete <span id="modal_unused_count"><?=$unused?></span> unused users?</h4>
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

	function delete_unused_users(){
		$('#modal_confirm_unused #delete').live('click', function(){
			$('#modal_confirm_unused').modal('hide');
			access_page('admin/delete_unused_users');
		});
		$('#modal_confirm_unused').modal({backdrop: 'static'});
	}
	
	function user_change_status(uid, selector){
		access_page('admin/change_user_status/' + uid, function(){
			unused_count = $("#unused_count").html();
			if (selector.hasClass('label-success')){
				selector.removeClass('label-success');
				selector.addClass('label-important');
				selector.html('Disabled');
				if (selector.parent().parent().find('.lastlogin').html() == "")
					unused_count++;
			} else {
				selector.removeClass('label-important');
				selector.addClass('label-success');
				selector.html('Enabled');
				if (selector.parent().parent().find('.lastlogin').html() == "")
					unused_count--;
			}
			$("#unused_count").html(unused_count);
			$("#modal_unused_count").html(unused_count);
		}, false);
	}

	function change_user_priviledge(uid, priviledge, button, option)
	{
		access_page('admin/change_user_priviledge/' + uid + '/' + priviledge, function(){
			button.removeClass('label-inverse');
			button.removeClass('label-warning');
			option.hide();
			if (priviledge == 'admin')
			{
				button.addClass('label-warning');
				button.html('Administrator');
			} else if (priviledge == 'user')
				button.html('User');
			else
			{
				button.addClass('label-inverse');
				button.html('Restricted');
				option.show();
			}
		}, false);
	}

	function edit_school()
	{
		$('.school-display').hide();
		$('.school-edit').show();
		newSchool = {};
	}

	function submit_school()
	{
		$.post("index.php/admin/users", {'newschool':newSchool}, function(data) {
			newSchool = undefined;
			$("#page_content").html(data);
		});
	}
</script>
