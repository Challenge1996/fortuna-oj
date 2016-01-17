<script type="text/javascript" src="js/contest_statistic.js"></script>

<div class="standing_table">
	<?php
		if (!isset($data) || !$data) {
			echo '<div class="alert"><strong>THERE IS NO SUBMISSION</strong></div>';
			return;
		}
	?>

	<?php if (isset($startTime)): ?>
		<button onclick="download_statistic(<?=$info->cid?>)" class="btn btn-small pull-right"><strong>export</strong></button>
		<button onclick="toggle_previous()" class="btn btn-small pull-right" id="sps_button"><strong>show previous submissions</strong></button>
	<?php else: ?>
		<button onclick="download_result(<?=$info->cid?>)" class="btn btn-small pull-right"><strong>export</strong></button>
	<?php endif; ?>

	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th><?=lang('rank')?></th><th><?=lang('user')?></th>
				<?php
					if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
						echo '<th>' . lang('score') . '</th>';
						foreach ($info->problemset as $row){
							$pid[] = $row->pid;
							echo "<th style='text-align:center'><a href='#contest/show/$info->cid/$row->id'>$row->title</a></th>";
						}
					}else if ($info->contestMode == 'ACM'){
						echo '<th>Solved</th><th>Penalty</th>';
						for ($i = 0; $i < $info->count; $i++) echo '<th style="text-align: center">' . chr(65 + $i) . '</th>';
					}
				?>
			</tr>
		</thead>
		<tbody><?php
		if ($data != FALSE){
			foreach ($data as $row){
				$uid = $row->uid;
				$s=(isset($startTime) && $row->submitTime < $startTime)?' class="submitted_before" style="display:none" ':'';
				echo "<tr".$s.">";
				echo "<td><div ".$s."><span class=\"label\">$row->rank</span></div></td>";
				echo "<td><div ".$s."><a href='#users/$row->name'><span class=\"label label-info\">$row->name</span></a></div></td>";
				echo "<td><div ".$s.">".
					"<span class=\"badge badge-info\">$row->score</span>".
					(isset($est[$uid])? "<sup><span class=\"badge\">".$est[$uid]['sum']."</span></sup>" :'').
					"</div></td>";
				
				if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional'){
					foreach ($pid as $prob){
						echo "<td style='text-align:center'><div ".$s.">";
						if (isset($row->acList[$prob])){
							//echo $prob;
							echo "<a href='#main/code/" . $row->attempt[$prob] . "'>";
							if ($row->acList[$prob] == 0)
								echo '<span class="badge badge-important">' . $row->acList[$prob] . '</span>';
							else
								echo '<span class="badge badge-success">' . $row->acList[$prob] . '</span>';
							echo '</a>';
						}
						if (isset($est[$uid]) && isset($est[$uid][$prob]))
							echo '<sup><span class="badge">'.$est[$uid][$prob].'</span></sup>';
						echo '</div></td>';
					}
				}else if ($info->contestMode == 'ACM'){
					echo "<td><div ".$s."><span class=\"badge badge-info\">$row->penalty</span></div></td>";
					foreach ($info->problemset as $prob){
						echo '<td style="text-align: center"><div'.$s.'>';
						if (isset($row->attempt[$prob->pid])){
							if (isset($row->acList[$prob->pid])){
								echo '<span class="badge badge-success">' . $row->attempt[$prob->pid] . '/' . $row->acList[$prob->pid] . '</span>';
							}else{
								echo '<span class="badge badge-important">-' . $row->attempt[$prob->pid] . '</span>';
							}
						}
						echo '</div></td>';
					}
				}
				
				echo '</tr>';
			}
		}	
		?></tbody>
	</table>
</div>

<iframe id="downloader" style="display:none"></iframe>

