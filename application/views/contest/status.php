<div class="status_table"><table class="table table-condensed table-striped">
	<thead><tr>
			<th><?=lang('sid')?></th>
			<th><?=lang('problem')?></th>
			<th><?=lang('user')?></th>
			<th><?=lang('result')?></th>
			<th><?=lang('time')?></th>
			<th><?=lang('memory')?></th>
			<th><?=lang('language')?></th>
			<th><?=lang('length')?></th>
			<th><?=lang('submit_time')?></th>
	</tr></thead>
	
	<tbody><?php
		$this->load->model('problems');
		foreach ($data as $row){
			if (($row->isShowed = 0 || (!$this->problems->is_showed($row->pid) && !$info->running)) && !$is_admin) continue;
			
			echo "<tr><td>$row->sid</td><td><a href='#contest/show/$info->cid/$row->id'>" . 
				($info->contestMode == 'ACM' ? chr(65 + $row->id) : $row->id) . '</a></td><td>' . 
				"<span class=\"label label-info\"><a href=\"#users/$row->name\">$row->name</a></span></td><td>";
				
			if ($row->status < 0 && $row->status > -3) echo $row->result;
			elseif ($row->status == 8 || $row->status == 9 || $row->status == -4) echo "<a href=\"#main/result/$row->sid\">$row->result</a>";
			else{
				if ($info->running && $info->contestMode == 'OI Traditional' && ! $is_admin){
					echo '<span class="label label-success">Submitted</span>';
				}else{
					if ($info->contestMode == 'OI' || $info->contestMode == 'OI Traditional') {
						switch ($row->status) {
							case -3: $tag = 'label-success'; break;
							case 0: $tag = 'label-success'; break;
							case 1:
							case 2:
							case 7: $tag = 'label-important'; break;
							case 3: $tag = 'label-info'; break;
							case 4:
							case 5:
							case 6: $tag = 'label-warning'; break;
							default: $tag = '';
						}
						$sname = "$row->result <span class=\"label $tag\">" . round($row->score, 1) . '</span>';
						
						echo "<a href=\"#main/result/$row->sid\">$sname</a>";
					} else {
						$sname = $row->result;
						
						echo "<a href=\"#main/result/$row->sid\">$sname</a>";
					}
				}
			}
			//echo "<td><a href=\"#main/code/$row->sid\">$row->language</a>";
			if ($row->codeLength > 0) {
				if ($info->running && $info->contestMode == 'OI Traditional' && ! $is_admin){
					echo "<td>---</td><td>---</td>";
					
				}else if ($row->status == -3 || ($row->status >= 0 && $row->status <= 7)) {
					echo "</td><td><span class=\"label label-info\">$row->time</span></td>";
					echo "<td><span class=\"label label-info\">$row->memory</span></td>";
					
				} else echo '<td>---</td><td>---</td>';
				
				echo "<td><a href=\"#main/code/$row->sid\">$row->language</a></td>";
				echo "</td><td>$row->codeLength</td>";
				
			} else echo '<td>---</td><td>---</td><td>---</td><td>---</td>';
			
			echo "<td>$row->submitTime</td>";
				
			echo '</tr>';
		}
	?></tbody>
</table></div>

<?=$this->pagination->create_links()?>


<script type="text/javascript">
	//refresh_flag = setTimeout("refresh_page()", 30000);
</script>

<!-- End of file status.php -->
