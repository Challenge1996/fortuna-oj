<?php
	echo '<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>';
	echo '<table id="downloads" class="table table-bordered table-striped table-condensed"' .
		 '<thead><tr><th>No.</th><th>File</th></tr></thead><tbody>';
	$fileCnt = 1;
	foreach ($files as $file)
	{
		echo "<tr><td>File $fileCnt</td><td><a href='index.php/main/download/$pid/$file'>$file</a></td></tr>";
		$fileCnt++;
	}
	echo '</tbody></table>';
