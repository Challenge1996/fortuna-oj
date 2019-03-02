<link href="css/tablewithpin.css" rel="stylesheet">

<div class="contest_list">
	<table id="contest_table" class="table table-condensed table-bordered">
		<thead><tr>
			<th align="center"><?=lang('contest_id')?></th>
			<th><?=lang('title')?></th>
			<th><?=lang('mode')?></th>
			<th><?=lang('start_time')?></th>
			<th><?=lang('submit_time')?></th>
			<th><?=lang('end_time')?></th>
			<th><?=lang('status')?></th>
			<th><?=lang('register')?></th>
		</tr></thead>
		<tbody><?php
			foreach ($data as $row):
				$cid = $row->cid; ?>
				<tr <?=$row->isPinned ? 'class="pinned"' : ''?>><td><?=$cid?></td>
				<td class="title"><?="<a href=\"#contest/home/$cid\">$row->title</a>"?></td>
				<td><span class="label label-info"><?=$row->contestMode?></span></td>
				<td><?=$row->startTime?></td>
				<td><?=$row->isTemplate ? 'N/A' : $row->submitTime?></td>
				<td><?=$row->endTime?></td>
				<td><?=$row->status?></td>
				<td><?php if ($row->private)
					echo anchor("#contest/register/$cid", "<span class=\"btn btn-success btn-mini\" style=\"font-weight:bold\">" . 
													lang('register') . "</span><span class=\"badge badge-info\">x$row->count</span>");
				?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?=$this->pagination->create_links()?>
</div>

<!-- End of file index.php  -->
