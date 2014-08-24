<h4>New Contest</h4>
<hr />

<?=validation_errors();?>

<div class="newcontest_form">

	<form class="form-horizontal" action="index.php/admin/newcontest<?=isset($cid) ? "/$cid" : ''?>" method="post" id="newcontest">
		<div class="control-group">
			<label for="title" class="control-label">Title</label>
			<div class="controls controls-row">
				<input type="text" name="contest_title" class="span6" value="<?=set_value('contest_title', isset($title) ? $title : '')?>" />
			</div>

			<label for="description" class="control-label">Contest Description</label>
			<div class="controls controls-row">
				<textarea name="description" rows="5" id="description" class="span6"><?=set_value('description', isset($description) ? $description : '')?></textarea>
			</div>
			
			<label class="control-label">Contest Time</label>
			<div class="controls controls-row">
				<div style="display:inline">
				<span class="label" style="width: 75px; text-align:center">Start Time</span>
				<input type="date" name="start_date" class="input-medium" value="<?=set_value('start_date', isset($startTime) ? date('Y-m-d', strtotime($startTime)) : date('Y-m-d'))?>"/>
				<input type="time" name="start_time" class="input-medium" value="<?=set_value('start_time', isset($startTime) ? date('H:i', strtotime($startTime)) : date('H:i', time()))?>"/>
				</div>
				<br />
				<div style="display:inline">
				<span class="label" style="width: 75px; text-align:center">Submit Time</span>
				<input type="date" name="submit_date" class="input-medium" value="<?=set_value('submit_date', isset($submitTime) ? date('Y-m-d', strtotime($submitTime)) : date('Y-m-d'))?>"/>
				<input type="time" name="submit_time" class="input-medium" value="<?=set_value('submit_time', isset($submitTime) ? date('H:i', strtotime($submitTime)) : date('H:m', time() + 18000))?>"/>
				</div>
				<br />
				<div style="display:inline">
				<span class="label" style="width: 75px; text-align:center">End Time</span>
				<input type="date" name="end_date" class="input-medium" value="<?=set_value('end_date', isset($endTime) ? date('Y-m-d', strtotime($endTime)) : date('Y-m-d'))?>"/>
				<input type="time" name="end_time" class="input-medium" value="<?=set_value('end_time', isset($endTime) ? date('H:i', strtotime($endTime)) : date('H:m', time() + 18000))?>"/>
				</div>
			</div>
			
			<label class="control-label">Team Mode</label>
			<div class="controls controls-row">
				<label class="radio inline">
					<input type="radio" name="teamMode" id="individual" value="0" <?=set_radio('teamMode', '0', isset($teamMode) ? ($teamMode == '0' ? TRUE : FALSE) : TRUE)?> />
					Individual
				</label>
				<label class="radio inline">
					<input type="radio" name="teamMode" id="team" value="1" <?=set_radio('teamMode', '1', isset($teamMode) ? ($teamMode == '1' ? TRUE : FALSE) : FALSE)?> />
					Team
				</label>
			</div>
			
			<label class="control-label">Contest Mode</label>
			<div class="controls controls-row">
				<select name="contestMode">
					<option value="OI" <?=set_select('contestMode', 'OI', isset($contestMode) ? ($contestMode == 'OI' ? TRUE : FALSE) : TRUE)?> >OI</option>
					<option value="OI Traditional" <?=set_select('contestMode', 'OI Traditional', isset($contestMode) ? ($contestMode == 'OI Traditional' ? TRUE : FALSE) : TRUE)?> >OI Traditional</option>
					<option value="ACM" <?=set_select('contestMode', 'ACM', isset($contestMode) ? ($contestMode == 'ACM' ? TRUE : FALSE) : FALSE)?> >ACM</option>
					<option value="Codeforces" <?=set_select('contestMode', 'Codeforces', isset($contestMode) ? ($contestMode == 'Codeforces' ? TRUE : FALSE) : FALSE)?> >Codeforces</option>
					<option value="codejam" <?=set_select('contestMode', 'codejam', isset($contestMode) ? ($contestMode == 'codejam' ? TRUE : FALSE) : FALSE)?> >Code Jam</option>
				</select>
			</div>
			
			<label class="control-label">Contest Type</label>
			<div class="controls controls-row">
				<label class="radio inline">
					<input type="radio" name="contestType" id="public" value="0" <?=set_radio('contestType', '0', isset($private) ? ($private == '0' ? TRUE : FALSE) : TRUE)?> />
					Public
				</label>
				<label class="radio inline">
					<input type="radio" name="contestType" id="private" value="1" <?=set_radio('contestType', '1', isset($private) ? ($private == '1' ? TRUE : FALSE) : FALSE)?> />
					Private
				</label>
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
				<table id="problems_table" class="table table-bordered table-condensed table-stripped">
					<thead>
						<th>Problem ID</th>
						<th style="text-align:center">Title</th>
						<th class="score">Score</th>
						<th class="speed">Loss (per minute)</th>
						<th><button class="btn btn-mini btn-primary" onclick="return add_problem_to_contest()">Add</button></th>
					</thead>
					<tbody id="problems"><?php
					if (isset($problems)){
						$cnt = count($problems);
						for ($i = 0; $i < $cnt; $i++){
							$problem = $problems[$i];
							echo "<td><input type=\"number\" name=\"pid[]\" min=\"1000\" class=\"input-small\" value=\"$problem->pid\"/></td>
								<td><input type=\"text\" name=\"title[]\" class=\"input-xxlarge\" value=\"$problem->title\"/></td>
								<td class=\"score\"><input type=\"text\" name=\"score[]\" value=\"100\" class=\"input-mini\" value=\"$problem->score\"></td>
								<td class=\"speed\"><input type=\"text\" name=\"speed[]\" value=\"0\" class=\"input-mini\" value=\"$problem->scoreDecreaseSpeed\"></td>
								<td><button class=\"close delete_problem_from_contest\">&times;</button></td></tr>";
						}
					}
					?></tbody>
				</table>
				
				
			</div>
		<div>
		
		<button type="submit" class="btn btn-primary pull-right" onclick="return new_contest()">Save</button>
	</form>
</div> 

<script type="text/javascript">
	$('.delete_problem_from_contest').live('click', function(){
		$(this).parent().parent().remove();
		return false;
	});
	
	function new_contest(){
		$('#newcontest').ajaxSubmit({
			success: function login_success(responseText, statusText){
				if (responseText == 'success') load_page('admin/contestlist');
				else $('#page_content').html(responseText);
			}
		});
		return false;
	}

	function add_problem_to_contest(){
		$('#problems').append('<tr> \
			<td><input type="number" name="pid[]" min="1000" class="input-small"/></td> \
			<td><input type="text" name="title[]" class="input-xxlarge" /></td> \
			<td class="score"><input type="text" name="score[]" value="100" class="input-mini"></td> \
			<td class="speed"><input type="text" name="speed[]" value="0" class="input-mini"></td> \
			<td><button class="close delete_problem_from_contest">&times;</button></td></tr>');
		return false;
	}

</script>
