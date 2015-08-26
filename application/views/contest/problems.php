<div>
<table id="contest_problems" class="table table-condensed table-bordered table-striped">
	<thead><tr>
		<th class="span2"><?=lang('problem_id')?></th>
		<th><?=lang('title')?></th>
<?php if ($info->contestMode != 'OI' && $info->contestMode != 'OI Traditional'): ?>
	<th class="span2"><?=lang('statistic')?></th>
<?php endif; ?>
	</tr></thead>

	<tbody>
	<?php foreach ($data as $row): ?>
		<tr>
		<td><?=($info->contestMode == 'ACM' ? chr(65 + $row->id) : $row->id)?></td>
		<td><a href="#contest/show/<?=$cid?>/<?=$row->id?>"><?=$row->title?></a></td>
		<?php if ($info->contestMode != 'OI' && $info->contestMode != 'OI Traditional'): ?>
				<td><?=$row->statistic->solvedCount?>/<?=$row->statistic->submitCount?></td>
		<?php endif; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table></div>
