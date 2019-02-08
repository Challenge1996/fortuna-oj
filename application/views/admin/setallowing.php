<button class="btn btn-small" onclick="javascript:history.back()">Return</button>

<div class="container-fluid">

	<div class="row-fluid">
		<div class="span8"><h3>Set Allowed Problems</h3></div>
		<div class='span4'>
			<a class='btn btn-link' href='#admin/setallowings'>Set Allowed Problems in Volume</a>
			<form id="form-add" class="form-inline" action="index.php/admin/setallowing/<?=$uid?>">
				<input type="number" placeholder="pid" name="add"></input>
				<span class="btn btn-primary" id="add">Add</span>
			</form>
		</div>
	</div>

	<div class="row-fluid">
		<table class="table table-bordered table-condensed table-striped">
			<tr>
				<th>pid</th>
				<th>Title</th>
				<th>Source</th>
				<th></th>
			</tr>
			<?php foreach ($data as $row): ?>
				<tr>
					<td><a href="#main/show/<?=$row->pid?>"><?=$row->pid?></a></td>
					<td><a href="#main/show/<?=$row->pid?>"><?=$row->title?></a></td>
					<td><?=$row->source?></td>
					<td><span class="close" onclick="del(<?=$row->id?>);">&times;</span></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

</div>

<script>
	$("#add").click(function(){
		$("#form-add").ajaxSubmit({
			type: "GET",
			success: function(data) { $("#page_content").html(data); }
		});
	});

	function del(id)
	{
		$.get("index.php/admin/setallowing/<?=$uid?>?del="+id, function(data) { $("#page_content").html(data); });
	}
</script>
