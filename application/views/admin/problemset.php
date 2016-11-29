<?php
	function strtrim($str){
		if (mb_strlen($str, 'UTF8') > 18) return (mb_substr($str, 0, 18, 'UTF8') . '..'); else return $str;
	}
?>

<h4>
	Problemset
	<div class="btn-group pull-right">
		<a class="btn btn-primary btn-small dropdown-toggle" data-toggle="dropdown" href="#">
			Add
			<span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<?php foreach ($this->config->item("problemset_name") as $id => $tab): ?>
				<li><a href="#admin/addproblem/0/<?=$id?>"><?=$tab?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</h4>
<hr />

<div class="admin_problemset_table">
	<form class="form-inline form-search" id="action_form" style="margin-left:10px; margin-right:10px">
		<div id="div_goto_pid" class="control-group input-prepend">
			<span class="add-on">Problem ID</span>
			<input type="number" min="1000" id="goto_pid" class="input-mini" />
		</div>
		<div class="input-prepend input-append">
		 	<button id="btn_edit_pid" class="btn">Edit</button>
			<button id="btn_configure_pid" class="btn">Configure</button>
		</div>
		
		<div id="div_goto_page" class="control-group input-prepend input-append pull-right">
			<span class="add-on">Page</span>
			<input type="number" id="goto_page" min=1 class="input-mini" />
			<button id="btn_goto_page" class="btn">Go</button>
		</div>
	</form>
	
	<table class="table table-condensed table-striped table-bordered">
		<thead><tr>
			<th>Problem ID</th>
			<th>Author</th>
			<th>Title</th>
			<th>Source</th>
			<th>Show/Hide</th>
			<th>Allow Submit</th>
			<th>Edit</th>
			<th>Data</th>
			<th></th>
		</tr></thead>
		
		<tbody><?php
		foreach ($data as $row){
			$pid = $row->pid;
			echo "<tr><td><a href='#main/show/$pid'>$row->pid</a></td>";
			echo "<td><span class='label label-info'>$row->author</span></td>";
			echo "<td><a class='title' href='#main/show/$pid'>$row->title</a></td>";
			echo '<td>' . strtrim($row->source) . '</td>';
			echo "<td><a onclick=\"access_page('#admin/change_problem_status/$pid')\">$row->isShowed</a></td>";
			echo "<td><a onclick=\"access_page('#admin/change_problem_nosubmit/$pid')\">$row->noSubmit</a></td>";
			echo "<td><button class='btn btn-mini' onclick=\"window.location.href='#admin/addproblem/$pid'\">Edit</button></td>";
			echo "<td><button class='btn btn-mini' onclick=\"window.location.href='#admin/dataconf/$pid'\">Configure</button></td>";
			echo "<td><button class='close' onclick=\"delete_problem($pid, $(this))\">&times;</button></tr>";
		}
		?></tbody>
	</table>
</div>
	
<?=$this->pagination->create_links()?>

<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm</h3>
	</div>
	<div class="modal-body">
		<p>Are you sure to delete problem: </p>
		<h3><div id="info"></div></h3>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$("#btn_edit_pid").live('click', function(){
			var pid = $('#goto_pid').val();
			if (pid != '') load_page("admin/addproblem/" + pid);
			return false;
		}),
		
		$("#btn_configure_pid").live('click', function(){
			var pid = $("#goto_pid").val();
			if (pid != '') load_page("admin/dataconf/" + pid);
			return false;
		}),
		
		$("#btn_goto_page").live('click', function(){
			var page = $('#goto_page').val();
			load_page("admin/problemset/" + page);
			return false;
		}),
		
		$("#goto_pid").live('focus', function(){
			$("#action_form").die();
			$("#action_form").live('keypress', function(event){
				if (event.keyCode == 13 && $("#goto_pid").val() != ''){
					$("#btn_edit_pid").click();
					return false;
				}
			})
		}),
		
		$("#goto_page").live('focus', function(){
			$("#action_form").die();
			$("#action_form").live('keypress', function(event){
				if (event.keyCode == 13 && $("#goto_page").val() != ''){
					$("#btn_goto_page").click();
					return false;
				}
			})
		})
	})
	
	function delete_problem(pid, selector){
		$("#modal_confirm #delete").click(function(){
			$("#modal_confirm").modal('hide');
			access_page("admin/delete_problem/" + pid);
		});
		$("#modal_confirm #info").html(pid + '. ' + selector.parent().parent().find('.title').html());
		$("#modal_confirm").modal({backdrop: 'static'});
	}
</script>
	
<!-- End of file problemset.php -->
