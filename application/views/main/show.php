<div class="row-fluid">
	<div id="header"><?php
		$average = 0;
		$allowed_download = '';
		$IOMode = $data->data->IOMode;
		if ($data->submitCount > 0) $average = number_format($data->scoreSum / $data->submitCount, 2);
		
		if (!isset($data->data) || $data->data->IOMode == 0) $IO = '(Standard IO)';
		else if ($IOMode == 1) {
			$inputFile = $data->data->cases[0]->tests[0]->userInput;
			$outputFile = $data->data->cases[0]->tests[0]->userOutput;
			$IO = "<br />(File IO): <span style='color:red'><strong>$inputFile/$outputFile</strong></span>";
		} else if ($IOMode == 2) $IO = '(Output Only)';
		else if ($IOMode == 3) $IO = '(Interactive)';
		echo '<div style="text-align:center">';
		echo "<h2>$data->pid. $data->title <sub>$IO</sub></h2>";
		
		$is_accepted = $this->misc->is_accepted($this->session->userdata('uid'), $data->pid);

		echo '<div>';
		if (isset($data->timeLimit)){
			echo lang('time_limit') . ": <span class=\"badge badge-info\">$data->timeLimit ms</span> &nbsp;";
			echo lang('memory_limit') . ": <span class=\"badge badge-info\">$data->memoryLimit KB</span>";
		} else if ($IOMode != 2) {
			echo lang('time_memory_limit');
		} else {
			$allowed_download .= '|data.zip';
			echo "<a href='/index.php/main/download/$data->pid' target='_blank'>Download Input</a>";
		}
		
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#main/limits/$data->pid\" style=\"text-align:left\">";
		echo '<span id="trigger"><i class="icon-chevron-down"></i></span></a>';
		
		if (isset($data->data->spjMode)) echo "&nbsp;&nbsp;<span class=\"label label-important\">Special Judge</span>";
		
		echo '</div>';	
		echo '</div>';
	?></div>
</div>

<div class="row-fluid" style="margin-top:7px">
	<div id="mainbar" class="span10">
		<div class="problem">
			<div class="span12 well"><fieldset>
				<legend><h4><?=lang('description')?></h4></legend>
				<div class="content"><?=nl2br($data->problemDescription)?></div>
			</fieldset></div>
			<div class="clearfix"></div>
			
			<div>
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('input')?></h4></legend>
					<div class="content"><?=nl2br($data->inputDescription)?></div>
				</fieldset></div>
			
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('output')?></h4></legend>
					<div class="content"><?=nl2br($data->outputDescription)?></div>
				</fieldset></div>
			</div>
			<div class="clearfix"></div>
			
			<div>
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('sample_input')?></h4></legend>
					<div class="content"><?=nl2br($data->inputSample)?></div>
				</fieldset></div>
			
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('sample_output')?></h4></legend>
					<div class="content"><?=nl2br($data->outputSample)?></div>
				</fieldset></div>
			</div>
			<div class="clearfix"></div>
		
			<div class="well"><fieldset>
				<legend><h4><?=lang('data_constraint')?></h4></legend>
				<div class="content"><?=nl2br($data->dataConstraint)?></div>
			</fieldset></div>
			<div class="clearfix"></div>
			
			<?php if ($data->hint != ''){ ?>
				<div class="well"><fieldset>
					<legend><h4><?=lang('hint')?></h4></legend>
					<div class="content"><?=nl2br($data->hint)?></div>
				</fieldset></div>
			<?php } ?>
		</div>
	</div>
	
	<div id="sidebar" class="span2">
		<div class="well" id="div_statistics"><?php
			echo '<fieldset><legend>';
			echo '<h5><em>' . lang('statistic') . '</em>';
			echo '</h5></legend>';
			echo lang('solved') . ": <a class=\"pull-right\" href=\"#main/statistic/$data->pid\"><span class=\"badge badge-info\">$data->solvedCount</span></a><br />";
			echo lang('submit') . ": <a class=\"pull-right\" href=\"#main/statistic/$data->pid\"><span class=\"badge badge-info\">$data->submitCount</span></a><br />";
			echo lang('average') . ": <a class=\"pull-right\" href=\"#main/statistic/$data->pid\"><span class=\"badge badge-info\">$average</span></a><br />";
			echo '<div style="text-align:center">';
			if ($IOMode != 2) {
				echo "<button class=\"btn btn-primary\" onclick=\"window.location.href='#main/submit/$data->pid'\">" . lang('submit') . "</button>";
			} else {
				echo "<button class=\"btn btn-primary\" onclick=\"window.location.href='#main/upload/$data->pid'\">" . lang('submit') . "</button>";
			}
			echo '</div></section></fieldset>';
		?></div>
		
		<?php
		if ($this->session->userdata('show_category') == 1 || $is_accepted){
			echo '<div class="well" id="div_tags">';
			echo '<fieldset id="tags">';
			echo '<legend><h5><em>' . lang('tags') . '</em>';
			if ($is_accepted || $this->user->is_admin()) echo ' <button id="add_tag_btn" class="btn btn-mini pull-right">' . lang('add') . '</button>';
			echo '</h5></legend>';
			foreach ($data->category as $id => $name)
				echo "<span class=\"label tag\" id=\"$id\" style=\"margin-right:5px\">" .
					'<button class="close delete_tag" style="color: white;font-size:14px;opacity:0.8;height:14px">&times;</button>' .
					$name . '</span> ';
			
			echo '<form id="tag_form" >';
			echo '<select style="width:120px" name="tag">';
			foreach ($category as $id => $name) echo "<option value=\"$id\">$name</option>";
			echo '</select><br />';
			echo '<button class="btn btn-mini btn" id="cancel_add">cancel</button>';
			echo '<button class="btn btn-mini btn-primary pull-right" id="confirm_add">add</button>';
			echo '</form></fieldset></div>';	
		}
		?>
		<div class="well" id="div_solutions">
			<fieldset id="solutions">
				<legend><h5><em><?=lang('solutions')?></em>
					<button id="add_solution_btn" class="btn btn-mini pull-right" onclick="add_solution()"><?=lang('add')?></button>
				</h5></legend>
				
				<div><?php
					if ($data->solutions) {
					foreach ($data->solutions as $solution) {
						$allowed_download .= "|$solution->filename";
						echo "<a href='index.php/main/download/$data->pid/$solution->filename/solution'>$solution->filename</a>";
						if ($this->user->uid() == $solution->uid || $this->user->is_admin())
							echo "<a class='pull-right' onclick='delete_solution($solution->idSolution)'>&times;</a>";
						echo '<br />';
					}
					}
				?></div>
			</fieldset>
		</div>
		
		<?php if ($data->source != ''){ ?>
			<div class="well"><fieldset>
				<legend><h5><em><?=lang('Problemset_source')?></em></h5></legend>
				<div class="content"><?=nl2br($data->source)?></div>
			</fieldset></div>
		<?php } ?>

		<div class="well"><fieldset>
			<legend><h5><em><?=lang('advanced')?></em></h5></legend>
			<span><a href='index.php/misc/testdata/<?=$data->pid?>' target="_blank">Download Data</a></span>
		</fieldset></div>
	</div>
</div>

<form action="index.php/main/addsolution/<?=$data->pid?>" class="form-horizontal" enctype="multipart/form-data" id="form_solution_upload">
	<div class="modal hide fade" id="modal_upload">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Upload Solution</h3>
		</div>
		
		<div class="modal-body">
			<span></span>
			<input type="file" name="solution" />
		</div>
		
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal">Close</a>
			<a class="btn btn-success" id="btn_upload">Upload</a>
		</div>
	</div>
</form>

<?php
	//echo $allowed_download;
	$this->session->set_userdata('download', $allowed_download);
?>

<script type="text/javascript">
	function add_solution(){
		$('#modal_upload').modal({backdrop: 'static'});
	}
	
	function delete_solution(idSolution) {
		access_page('main/deletesolution/' + idSolution);
	}
	
	var dataconf = "<?php
		echo '<pre>';
		$caseCnt = 1;
		if (isset($data->data->cases)){
			foreach ($data->data->cases as $case){
				echo "Case $caseCnt: " . number_format($case->score, 2) . ' pts<br />';
				$testCnt = 1;
				foreach ($case->tests as $test){
					if ($IOMode != 2) {
						echo "<i class='icon-arrow-right'></i>Test $testCnt:<span class='badge badge-info'>$test->timeLimit ms</span>";
						echo "<span class='badge badge-info'>$test->memoryLimit KB</span><br />";
					}
					$testCnt++;
				}
				$caseCnt++;
			}
		}
		echo '</pre>';
	?>";
	$(document).ready(function(){
		$('#modal_upload #btn_upload').click(function(){
			$('#modal_upload').modal('hide');
			$('#form_solution_upload').ajaxSubmit({
				type: 'post',
				success: function(responseText, stautsText){
					if (responseText == 'success') refresh_page();
					else alert("Failed to upload!");
				}
			});
		}),
		$('.delete_tag').hide(),
		$('#tag_form').hide(),
		$('.tag').hover(
			function(){
				$(this).children('.close').show();
			},
			function(){
				$(this).children('.close').hide();
			}
		),
		$('.delete_tag').click(function(){
			access_page('main/deltag/<?=$data->pid?>/' + $(this).parent().attr('id'));
		}),
		$('#trigger').popover({html: true, content: dataconf, trigger: 'hover', placement: 'bottom'}),
		$('#trigger').click(function(){
			$('#trigger').popover('hide')
		}),
		$('#add_tag_btn').click(function(){
			$('#tag_form').show();
		}),
		$('#cancel_add').click(function(){
			$('#tag_form').hide();
			return false;
		}),
		$('#confirm_add').click(function(){
			$('#tag_form').hide();
			$('#tag_form').ajaxSubmit({
				type: "GET",
				url: "index.php/main/addtag/<?=$data->pid?>",
				success: function(){
					refresh_page();
					return false;
				}
			});
			return false;
		}),
		$('#page_content').one('DOMNodeInserted', function(){
			document.title = "<?=OJ_TITLE?>";
		})
	});
	
	document.title = "<?=$data->pid . '. ' . rtrim($data->title) . ' ' . $IO?>";
</script>

<!-- End of file show.php -->
