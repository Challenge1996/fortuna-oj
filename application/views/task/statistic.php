<button class="btn btn-small" onclick="javascript:history.back()">Return</button>

<table class="table table-condensed table-bordered table-stripped">
	<thead><tr>
		<td>Name</td><td>Total</td>
		<?php 
			foreach ($problems as $problem)
				echo "<td style='text-align:center'><a href='#task/show/$problem->pid/$info->gid/$info->tid'>
						<span class='badge badge-info'>$problem->pid</span></a></td>";
		?>
	</tr></thead>
	
	<tbody>
		<?php
			if (isset($data) && $data != FALSE){
				foreach ($data as $user) {
					echo "<tr><td><span class='label label-info'><a href='#users/$user[1]'>$user[1]</a></span></td>";
					echo "<td><span class='label label-info'>" . round($user[0], 1) . "</span></td>";
					foreach ($problems as $problem) {
						echo '<td style="text-align:center">';
						if (isset($user[$problem->pid])) {
							$score = round($user[$problem->pid]->score, 1);
							$time = $user[$problem->pid]->submitTime;
							echo "<span class='label label-info'>$score</span> / $time";
						}
						echo '</td>';
					}
					echo '</tr>';
				}
			}
		?>
	</tbody>
</table>
