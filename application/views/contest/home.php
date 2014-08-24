<?php
	echo '<div class="hero-unit" style="text-align: center">';
	echo "<h2>$data->title</h2>";
	echo "<p class=\"explanation\">Start Time: <span class=\"badge badge-info\">$data->startTime</span> " . 
		 "Submit Time: <span class=\"badge badge-info\">$data->submitTime</span> " .
		 "End Time: <span class=\"badge badge-info\">$data->endTime</span> Status: $data->status</p>";
	echo "<p>$data->description</p>";
	echo '</div>';
