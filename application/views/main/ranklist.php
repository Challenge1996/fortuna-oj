<table class="ranklist table table-striped table-bordered table-condensed">
	<thead>
		<tr><th>Rank</th><th>Name</th><th>Description</th><th>Solved</th><th>Submit(Solved)</th><th>Submit(Total)</th><th>Rate</th></tr>
	</thead>
	<tbody>
	<?php
		foreach ($data as $row)
			echo "<tr><td>$row->rank</td><td><a href=\"#users/$row->name\"><span class=\"label label-info\">$row->name</span></a>" . 
				"</td><td>$row->description</td><td>$row->acCount</td><td>$row->solvedCount</td><td>$row->submitCount</td><td>$row->rate%</td></tr>";
	?>
	</tbody>
</table>

<?=$this->pagination->create_links();?>

<!-- End of file ranklist.php -->