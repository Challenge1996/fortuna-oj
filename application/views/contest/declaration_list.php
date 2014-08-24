<div class="declaration_table">
	<table class="table table-condensed table-striped table bordered">
		<thead>
			<tr><th>Problem ID</th><th>Title</th><th>Post Time</th></tr>
		</thead>
		<tbody><?php
			foreach ($data as $row){
				echo "<tr><td>$row->id</td><td>$row->title</td><td>$row->postTime</td></tr>";
			}
		?></tbody>
	</table>
</div>