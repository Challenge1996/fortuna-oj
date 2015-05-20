<?php
	if (!$simple)
	{
		echo '<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>';
		echo '<table id="limits" class="table table-bordered table-striped table-condensed"' .
			'<thead><tr><th>Case/Test No.</th><th>Time</th><th>Memory</th></tr></thead><tbody>';
	}
	foreach ($group as $case_id => $case)
	{
		if (!$simple)
			echo "<tr><td>Case $case_id</td></tr>";
		else
			echo "Case $case_id<br />";
		foreach ($case as $test)
		{
			if (!$simple)
				echo "<tr><td>Test $test</td>";
			else
				echo "<i class='icon-arrow-right'></i>Test $test ";
			if (!$simple)
			{
				echo "<td>";
				if (isset($time[$test]))
					foreach ($time[$test] as $f => $t)
					{
						if ($showName) echo "<span class='label'>$f</span> ";
						echo "<span class='label label-info'>$t ms</span>";
					}
				echo "</td><td>";
				if (isset($memory[$test]))
					foreach ($memory[$test] as $f => $m)
					{
						if ($showName) echo "<span class='label'>$f</span> ";
						echo "<span class='label label-info'>$m KB</span>";
					}
				echo "</td></tr>";
			} else
			{
				$arr = array();
				if (isset($time[$test]))
					foreach ($time[$test] as $f => $t)
					{
						if (!isset($arr[$f])) $arr[$f] = (object) null;
						$arr[$f]->t=$t;
					}
				if (isset($memory[$test]))
					foreach ($memory[$test] as $f => $m)
					{
						if (!isset($arr[$f])) $arr[$f] = (object) null;
						$arr[$f]->m=$m;
					}
				foreach ($arr as $f => $tm)
				{
					if ($showName) echo "<span class='label'>$f</span>";
					echo "<span class='label label-info'>$tm->t ms</span>" .
						"<span class='label label-info'>$tm->m KB</span> ";
				}
				echo "<br />";
			}
		}
	}
	if (!$simple)
		echo '</tbody></table>';

// End of file limits.php
