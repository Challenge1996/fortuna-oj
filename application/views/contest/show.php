<div class="row-fluid">
	<div id="header"><?php
		$average = 0;
		$IOMode = $data->data->IOMode;
		if ($data->submitCount > 0) $average = number_format($data->scoreSum / $data->submitCount, 2);
		
		if (!isset($data->data) || $data->data->IOMode == 0) $IO = '(Standard IO)';
		else if ($IOMode == 1) $IO = '(File IO)';
		else if ($IOMode == 2) $IO = '(Output Only)';
		else if ($IOMode == 3) $IO = '(Interactive)';
		echo '<div style="text-align:center">';
		echo "<h2>$data->title <sub>$IO</sub></h2>";
		
		echo '<div>';
		if (isset($data->timeLimit)){
			echo lang('time_limit') . ": <span class=\"badge badge-info\">$data->timeLimit ms</span> &nbsp;";
			echo lang('memory_limit') . ": <span class=\"badge badge-info\">$data->memoryLimit KB</span>";
		} else if ($IOMode != 2) {
			echo lang('time_memory_limit');
		} else {
			$this->session->set_userdata('download', 'data.zip');
			echo "<a href='index.php/main/download/$data->pid' target='_blank'>Download Input</a>";
		}
		
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#main/limits/$data->pid\" style=\"text-align:left\">";
		echo '<span id="trigger"><i class="icon-chevron-down"></i></span></a>';
		
		if (isset($data->data->spjMode)) echo '&nbsp;&nbsp;&nbsp;<span class="label label-important">Special Judge</span>';
		echo '</div>';
		
		if ($info->contestMode != 'OI' && $info->contestMode != 'OI Traditional'){
			echo "<div>Solved: <span class=\"badge badge-info\">$data->solvedCount</span> &nbsp;";
			echo "Submit: <span class=\"badge badge-info\">$data->submitCount</span></div>";
		}
		
		if (strtotime($info->submitTime) <= time() && strtotime($info->endTime) > time()){
			if ($IOMode != 2) {
				echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/submit/$data->pid/$cid')\">" . lang('submit') . "</button>";
			} else {
				echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/upload/$data->pid/$cid')\">" . lang('submit') . "</button>";
			}
			
		} else {
			if (strtotime($info->submitTime) <= time())
				echo "<a href='#main/show/$data->pid'>Goto ProblemSet</a>";
			else
				echo "<p>Please wait until <strong>$info->submitTime</strong> to make a submission</p>";
		}
		
		echo '</div>';
	?></div>
</div>


<div class="row-fluid" style="margin-top:7px">
	<div id="mainbar">
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
					<div class="content"><pre style='border:none'><?=nl2br($data->inputSample)?></pre></div>
				</fieldset></div>
			
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('sample_output')?></h4></legend>
					<div class="content"><pre style='border:none'><?=nl2br($data->outputSample)?></pre></div>
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
</div>

<div style="text-align:center"><?php
	if (strtotime($info->submitTime) <=time() && strtotime($info->endTime) > time()){
		if ($IOMode != 2) {
			echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/submit/$data->pid/$cid')\">" . lang('submit') . "</button>";
		} else {
			echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/upload/$data->pid/$cid')\">" . lang('submit') . "</button>";
		}	
	}
?></div>

<script type="text/javascript">
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
		$('#trigger').popover({html: true, content: dataconf, trigger: 'hover', placement: 'bottom'}),
		$('#trigger').click(function(){
			$('#trigger').popover('hide')
		}),
		$('#page_content').one('DOMNodeInserted', function(){
			document.title = "<?=OJ_TITLE?>";
		})
	});
	
	document.title = "<?=rtrim($data->title) . ' ' . $IO?>";
</script>
