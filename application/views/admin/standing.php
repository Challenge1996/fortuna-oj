<div class="standing_table">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th><?=lang('rank')?></th><th><?=lang('user')?></th><th><?=lang('score')?></th>
				<?php
					$pid_array = explode(',', $pids);
					foreach ($pid_array as $pid)
						echo "<th style='text-align:center'><a href='#main/show/$pid'>$pid</a></th>";
				?>
			</tr>
		</thead>
		<tbody><?php
		if ($data != FALSE){
			foreach ($data as $row){
				echo "<tr><td><span class=\"label\">$row->rank</span></td>";
				echo "<td><a href='#users/$row->name'><span class=\"label label-info\">$row->name</span></a></td>";
				echo "<td><span class=\"badge badge-info\">$row->score</span></td>";
				
				foreach ($pid_array as $prob){
					echo "<td style='text-align:center'>";
					if (isset($row->acList[$prob])){
						echo "<a href='#main/code/" . $row->attempt[$prob] . "'>";
						if ($row->acList[$prob] == 0)
							echo '<span class="badge badge-important">' . $row->acList[$prob] . '</span>';
						else
							echo '<span class="badge badge-success">' . $row->acList[$prob] . '</span>';
						echo '</a>';
					}
					echo '</td>';
				}
				
				echo '</tr>';
			}
		}	
		?></tbody>
	</table>
</div>
