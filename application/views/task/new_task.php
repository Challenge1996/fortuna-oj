<h3>New Task</h3>
<hr />

<?=validation_errors();?>

<div class="newtask_form">

	<form class="form-horizontal" action="index.php/admin/new_task<?=isset($tid) ? "/$tid" : ''?>" method="post" id="newtask">
		<div class="control-group">
			<label for="title" class="control-label">Title</label>
			<div class="controls controls-row">
				<input type="text" name="task_title" class="span6" value="<?=set_value('task_title', isset($title) ? $title : '')?>" />
			</div>

			<label for="description" class="control-label">Task Description</label>
			<div class="controls controls-row">
				<textarea name="description" rows="5" id="description" class="span6"><?=set_value('description', isset($description) ? $description : '')?></textarea>
			</div>

			<?php
				if (isset($language)) $languages = explode(',', $language);
			?>
			<label class="control-label">Allowed Languages</label>
			<div class="controls controls-row">
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="C" <?=set_checkbox('languages[]', 'C', isset($languages) ? in_array('C', $languages) : TRUE)?> />C
				</label>
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="C++" <?=set_checkbox('languages[]', 'C++', isset($languages) ? in_array('C++', $languages) : TRUE)?> />C++
				</label>
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="C++11" <?=set_checkbox('languages[]', 'C++11', isset($languages) ? in_array('C++11', $languages) : FALSE)?> />C++11(0x)
				</label>
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="Pascal" <?=set_checkbox('languages[]', 'Pascal', isset($languages) ? in_array('Pascal', $languages) : TRUE)?> />Pascal
				</label>
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="Java" <?=set_checkbox('languages[]', 'Java', isset($languages) ? in_array('Java', $languages) : FALSE)?> />Java
				</label>
				<label class="checkbox inline">
					<input type="checkbox" name="languages[]" value="Python" <?=set_checkbox('languages[]', 'Python', isset($languages) ? in_array('Python', $languages) : FALSE)?> />Python
				</label>
			</div>
			
			<label class="control-label">Problems</label>
			<div class="controls controls-row">
				<table id="problems_table" class="table table-bordered table-condensed table-stripped table-hover">
					<thead>
						<th>Problem ID</th>
						<th>Title</th>
						<th></th>
					</thead>
					
					<tbody id="problems"><?php
					if (isset($problems)){
						$cnt = count($problems);
						for ($i = 0; $i < $cnt; $i++){
							$problem = $problems[$i];
							echo "<td><input type='number' name='pid[]' min='1000' class='input-small' value='$problem->pid' /></td>
								  <td><input type='text' name='title[]' class='input-xxlarge' value='$problem->title' /></td>
								  <td><button class='close delete_problem_from_task'>&times;</button></td></tr>";
						}
					}
					?></tbody>
				</table>
				
				<button class="btn pull-right" onclick="return add_problem_to_task()">Add</button>
			</div>
		<div>
		
		<button type="submit" class="btn btn-primary pull-right" onclick="return new_task()">Save</button>
	</form>
</div> 

<script type="text/javascript">
	$('.delete_problem_from_task').live('click', function(){
		$(this).parent().parent().remove();
		return false;
	});
	
	function new_task(){
		$('#newtask').ajaxSubmit({
			success: function login_success(responseText, statusText){
				if (responseText == 'success') load_page('admin/task_list');
				else $('#page_content').html(responseText);
			}
		});
		return false;
	}

	function add_problem_to_task(){
		$('#problems').append('<tr> \
			<td><input type="number" name="pid[]" min="1000" class="input-small"/></td> \
			<td><input type="text" name="title[]" class="input-xxlarge" /></td> \
			<td><button class="close delete_problem_from_task">&times;</button></tr>');
		return false;
	}

</script> 
