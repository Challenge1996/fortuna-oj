<div class="container-fluid">
	<h4> Recent Contest List </h4>
	<div class="row-fluid">
		<table class="table table-condensed table-striped">
			<tr>
				<th>OJ</th>
				<th>Contest Name</th>
				<th>Start Time</th>
				<th>Week</th>
				<th>Status</th>
				<th>Count Down</th>
			</tr>
			<?php foreach($data as $row): ?>
				<tr>
				<?php foreach(array('oj', 'contestName', 'startTime', 'week', 'status', 'countDown') as $key): ?>
					<td><?=$row[$key]?></td>
				<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>
