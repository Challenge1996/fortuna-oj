<?php
	echo '<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>';

	echo '<div class="status"><table class="table table-striped table-condensed table-bordered">';
	echo '<thead><tr>';
	echo '<th class="sid">Submission ID</th>';
	echo '<th class="user">User</th>';
	echo '<th class="result">Result</th>';
	echo '<th class="time">Time</th>';
	echo '<th class="memory">Memory</th>';
	echo '<th class="language">Language</th>';
	echo '<th class="codeLength">Code Length</th>';
	echo '<th class="submitTime">Submit Time</th>';
	echo '<th>Access</th>';
	echo '<th></th>';
	echo '</tr></thead>';
	
	echo '<tbody>';

	foreach ($data as $user)
	{
		$cnt = 0;
		$row_count = count($user);
		foreach ($user as $row)
		{
			$class = 'uid-' . $row->uid;
			echo $cnt ? "<tr class='$class muted' style='display:none'>" : "<tr>";
			echo "<td>$row->sid ($row_count)";
			if (!$cnt && $row_count>1)
				echo "<i class='pull-right icon-resize-vertical' onclick='$(\".$class\").toggle();'></i>";
			echo "</td>";
			echo "<td><a href=\"#users/$row->name\"><span class=\"label label-info\">$row->name</span></a></td>";

			echo '<td>';
			if ($row->status < 0 && $row->status > -3) echo $row->result;
			elseif ($row->status == 8 || $row->status == 9) echo "<a href=\"#main/result/$row->sid\">$row->result</a>";
			else{
				switch ($row->status) {
					case -3: ;
					case 0: $tag = 'label-success'; break;
					case 1: ;
					case 2: ;
					case 7: $tag = 'label-important'; break;
					case 3: $tag = 'label-info'; break;
					case 4:
					case 5:
					case 6: $tag = 'label-warning'; break;
					default: $tag = '';
				}

				$sname = "$row->result <span class=\"label $tag\">" . round($row->score, 1) . '</span>';
				echo "<a href=\"#main/result/$row->sid\"> $sname </a>";
			}
			echo '</td>';

			if ($row->codeLength > 0) {
				echo "<td><span class=\"label label-info\">$row->time</span></td>";
				echo "<td><span class=\"label label-info\">$row->memory</span></td>";
				echo "<td><a href=\"#main/code/$row->sid\">$row->language</a></td>";
				echo "<td>$row->codeLength</td>";
			} else echo '<td>---</td><td>---</td><td>---</td><td>---</td>';
			echo "<td>$row->submitTime</td>";

			echo '<td>';
			if ($this->user->uid() == $row->uid && $this->config->item('allow_normal_user_public') === true ||
				$this->user->is_admin() && $this->config->item('allow_normal_user_public') !== 'default_public') {
				echo "<a onclick=\"access_page('main/submission_change_access/$row->sid')\">";
				if ($row->private == 1)
					echo '<i class="icon-lock"></i>';
				else
					echo '<i class="icon-globe"></i>';
				echo '</a>';
			} else if ($row->private == 0 || $this->config->item('allow_normal_user_public') === 'default_public')
				echo '<i class="icon-globe"></i>';
			echo '</td>';

			echo '<td>';
			if ($this->user->is_admin()){
				echo "<a onclick=\"access_page('admin/change_submission_status/$row->sid')\">";
				if ($row->isShowed == 1) echo '<i class="icon-eye-open"></i>';
				else echo '<i class="icon-eye-close"></i>';
				echo '</a>';
			}
			echo '</td>';

			echo '</tr>';
			$cnt ++;
		}
	}
	
	echo '</tbody></table></div>';
	
	echo $this->pagination->create_links();

// End of file statistic
