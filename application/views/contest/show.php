<div class="row-fluid">
	<div id="header"><?php

		$average = 0;
		if ($data->submitCount > 0) $average = number_format($data->scoreSum / $data->submitCount, 2);
		
		$IO = '';
		$src = (array)$data->filemode[2];
		$outputOnly = true; $spj = false;
		if (isset($data->filemode[4]))
			foreach ($data->filemode[4] as $executable => $property)
				if (isset($property->source))
					foreach ((array)($property->source) as $source)
						if (isset($source) && isset($src[$source]))
							$outputOnly = false;
						else
							$spj = true;
				else
					$spj = true;
		if ($outputOnly)
			$IO = '(Output Only)';
		else
		{
			$inputFile = $outputFile = '';
			if (isset($data->filemode[0]))
				foreach ($data->filemode[0] as $file => $property)
				{
					if ($inputFile) $inputFile .= '/';
					$inputFile .= $file;
				}
			if (isset($data->filemode[1]))
				foreach ($data->filemode[1] as $file => $property)
				{
					if ($outputFile) $outputFile .= '/';
					$outputFile .= $file;
				}
			if (!$inputFile && !$outputFile)
				$IO = '(Standard IO)';
			else
			{
				if (!$inputFile) $inputFile = 'None';
				if (!$outputFile) $outputFile = 'None';
				$IO = "<br />(File IO): <span style='color:red'>input:<strong>$inputFile</strong> output:<strong>$outputFile</strong></span>";
			}
		}

		echo '<div style="text-align:center">';
		echo "<h2>$data->pid. $data->title <sub>$IO</sub></h2>";
		echo '<div>';
		if (isset($data->timeLimit))
			echo lang('time_limit') . ": <span class=\"badge badge-info\">$data->timeLimit ms</span> &nbsp;";
		if (isset($data->memoryLimit))
			echo lang('memory_limit') . ": <span class=\"badge badge-info\">$data->memoryLimit KB</span> &nbsp;";
		echo lang('detailed_limit');
		$needDownload = false;
		if (isset($data->filemode[3]))
			foreach ($data->filemode[3] as $property)
				if (isset($property->download) && $property->download)
				{
					$needDownload = true;
					break;
				}
		echo "&nbsp; <a href=\"#main/limits/$data->pid\" style=\"text-align:left\">";
		echo '<span id="trigger"><i class="icon-chevron-down"></i></span></a>';
		if ($spj) echo "&nbsp;&nbsp;<span class=\"label label-important\">Special Judge</span>";
		if ($needDownload)
			echo "&nbsp;&nbsp;&nbsp; <a href='#main/showdownload/$data->pid'><strong style='color:red'>Downloads</strong></a>";
		echo '</div>';
		
		if ($info->contestMode != 'OI' && $info->contestMode != 'OI Traditional'){
			echo "<div>Solved: <span class=\"badge badge-info\">$data->solvedCount</span> &nbsp;";
			echo "Submit: <span class=\"badge badge-info\">$data->submitCount</span></div>";
		}
		
		if (strtotime($info->submitTime) <= time() && strtotime($info->endTime) > time())
			echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/submit/$data->pid/$cid')\">" . lang('submit') . "</button>";
		else
			if (strtotime($info->submitTime) <= time())
				echo "<a href='#main/show/$data->pid'>Goto ProblemSet</a>";
			else
				echo "<p>Please wait until <strong>$info->submitTime</strong> to make a submission</p>";
		
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
					<div class="content"><pre style='border:none;font-size:110%'><?=nl2br($data->inputSample)?></pre></div>
				</fieldset></div>
			
				<div class="span6 well"><fieldset>
					<legend><h4><?=lang('sample_output')?></h4></legend>
					<div class="content"><pre style='border:none;font-size:110%'><?=nl2br($data->outputSample)?></pre></div>
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
	if (strtotime($info->submitTime) <=time() && strtotime($info->endTime) > time())
			echo "<button style='margin-top:3px' class='btn btn-primary' onclick=\"load_page('main/submit/$data->pid/$cid')\">" . lang('submit') . "</button>";
?></div>

<script type="text/javascript">
	$.get('index.php/main/limits/<?=$data->pid?>?simple', function(data) {
		data = '<pre>'+data+'</pre>';
		$('#trigger').popover({html: true, content: data, trigger: 'hover', placement: 'bottom'});
	});
	
	$(document).ready(function(){
		$('#trigger').click(function(){
			$('#trigger').popover('hide')
		}),
		$('#page_content').one('DOMNodeInserted', function(){
			document.title = "<?=OJ_TITLE?>";
		})
	});
	
	document.title = "<?=rtrim($data->title) . ' ' . $IO?>";
</script>
