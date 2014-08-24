<h4>
	Task List
	<button class="btn btn-primary btn-small pull-right" onclick="window.location.hash='admin/new_task'">Add</button>
</h4>
<hr />

<div class="task_list">
	<table id="contest_table" class="table table-condensed table-bordered table-striped">
		<thead>
			<th>Task ID</th>
			<th>Title</th>
			<th>Edit</th>
			<th></th>
		</thead>
		
		<tbody><?php
			foreach ($tasks as $row){
				$tid = $row->tid;
				echo "<tr><td>$tid</td>" . 
					 "<td class='title'>$row->title</td>" .
					 "<td><button class='btn btn-mini' onclick='window.location.href=\"#admin/new_task/$tid\"'>Edit</button></td>" . 
					 "<td><button class='close' onclick=\"delete_task($tid, '$row->title')\">&times;</button></td></tr>";
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
		<p>Are you sure to delete task: </p>
		<h3><div id="info"></div></h3>
		<p>All data related to this task including <strong>submissions</strong> will be lost!</p>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<script type="text/javascript">
	function delete_task(tid, title){
		$('#modal_confirm #delete').click(function(){
			$('#modal_confirm').modal('hide');
			access_page('admin/delete_task/' + tid);
		});
		$('#modal_confirm #info').html(tid + '. ' + title);
		$('#modal_confirm').modal({backdrop: 'static'});
	}
</script>
