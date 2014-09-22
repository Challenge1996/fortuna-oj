<h4>
	Contest List
	<button class="btn btn-primary btn-small pull-right" onclick="window.location.hash='admin/newcontest'">Add</button>
</h4>
<hr />

<div class="contest_list">
	<table id="contest_table" class="table table-condensed table-bordered table-striped">
		<thead>
			<th>Contest ID</th><th>Title</th><th>Start Time</th><th>Submit Time</th><th>End Time</th>
			<th>Status</th><th>Mode</th><th>Type</th><th>Edit</th><th></th>
		</thead>
		
		<tbody><?php
			foreach ($data as $row){
				$cid = $row->cid;
				echo "<tr><td>$cid</td><td>"; 
				echo (isset($row->running) ? "<a class='title' href=\"#contest/problems/$cid\">$row->title</a>"
											: "<a class='title' href=\"#contest/home/$cid\">$row->title</a>");
				echo "</td><td>$row->startTime</td><td>$row->submitTime</td><td>$row->endTime</td>";
				
				echo "<td>$row->status";
				if (strpos($row->status, 'Ended'))
					echo " <i class='icon-arrow-right' onclick='contest_to_task($cid, $(this))'></i>";
				echo '</td>';
				
				echo "<td><div class=\"badge badge-info\">$row->contestMode</div></td>";
				echo '<td><div class="badge badge-info">' . ($row->private ? 'Private' : 'Public') . '</div></td>';
//				echo "<td><div class=\"badge badge-info\"><i class=\"icon-user icon-white\"></i>x$row->count</div></td>";
				echo "<td><button class=\"btn btn-mini\" onclick='window.location.href=\"#admin/newcontest/$cid\"'>Edit</button></td>"; 
				echo "<td><button class=\"close\" onclick=\"delete_contest($cid, $(this))\">&times;</button></td></tr>";
			}
		?></tbody>
	</table>
	<?=$this->pagination->create_links()?>
</div>
 
<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure to delete contest: </p>
		<h3><div class="info"></div></h3>
		<p>All data related to this contest including <strong>submissions</strong> will be lost!</p>
		<p>Please set the problems to <span class="label label-success">Showed</span> manually</p>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<div class="modal hide fade" id="modal_convert">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Convert A Contest to Task</h3>
	</div>
	<div class="modal-body">
		Converting <h3><div class="info"></div></h3> to Task. Please confirm.
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-success" id="convert">Convert</a>
	</div>
</div>

<script type="text/javascript">
	function delete_contest(cid, selector){
		$('#modal_confirm #delete').click(function(){
			$('#modal_confirm').modal('hide');
			access_page('admin/delete_contest/' + cid);
		});
		$('#modal_confirm .info').html(cid + '. ' + selector.parent().parent().find('.title').html());
		$('#modal_confirm').modal({backdrop: 'static'});
	}
	
	function contest_to_task(cid, selector) {
		$('#modal_convert #convert').click(function(){
			$('#modal_convert').modal('hide');
			access_page('admin/contest_to_task/' + cid);
		});
		$('#modal_convert .info').html(cid + '. ' + selector.parent().parent().find('.title').html());
		$('#modal_convert').modal();
	}
</script>
