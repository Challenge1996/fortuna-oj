
<div>
<table id="contest_problems" class="table table-condensed table-bordered table-striped">
	<thead><tr>
		<th class="span2"><?=lang('problem_id')?></th>
		<th><?=lang('title')?></th>
<?php
	if ($info->contestMode != 'OI' && $info->contestMode != 'OI Traditional') echo '<th class="span2">' . lang('statistic') . '</th>';
	echo '</tr></thead><tbody>';

	foreach ($data as $row){
		echo '<tr><td>' .
			($info->contestMode == 'ACM' ? chr(65 + $row->id) : $row->id) .
			"</td><td><a href=\"#contest/show/$cid/$row->id\">$row->title</a></td>" .
			($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional' 
				? '' : '<td>' . $row->statistic->solvedCount . '/' . $row->statistic->submitCount . '</td>') .
			'</tr>';
	}
?>
	
	</tbody>
</table></div>