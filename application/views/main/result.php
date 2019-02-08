<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>

<?php
	$this->load->model('submission');
	$showName = (count(get_object_vars($filemode[2]))>1);
	$beenWrong = false;
?>

<?php foreach ($result as $id => &$detail): ?>
	<?php if (isset($detail->message) && strlen($detail->message)>35): ?>
		<div class="<?=$id?> modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Message #<?=$id?></h3>
			</div>
			<div class="modal-body">
				<p><?=nl2br(htmlentities($detail->message))?></p>
			</div>
		</div>
	<?php
		$detail->message = htmlentities(str_split($detail->message,32)[0]);
		$detail->message .= "<button class='btn btn-link' onclick='$(\".$id.modal\").modal(\"show\")'>... <i class='icon-comment'></i></button>";
	?>
	<?php endif; ?>
	<?php
		$statId = $this->submission->status_id($detail->status);
		switch ($statId)
		{
			case -4: $detail->status = '<span class="label">' . lang('output_not_found') . '</span>'; break;
			case -3: $detail->status = '<span class="label label-success">' . lang('partially_accepted') . '</span>'; break;
			case -2: $detail->status = '<span class="label label-important">' . lang('running') . '</span>'; break;
			case -1: $detail->status = '<span class="label">' . lang('pending') . '</span>'; break;
			case 0: $detail->status = '<span class="label label-success">' . lang('accepted') . '</span>'; break;
			case 1: $detail->status = '<span class="label label-important">' . lang('presentation_error') . '</span>'; break;
			case 2: $detail->status = '<span class="label label-important">' . lang('wrong_answer') . '</span>'; break;
			case 3: $detail->status = '<span class="label label-info">' . lang('checker_error') . '</span>'; break;
			case 4: $detail->status = '<span class="label label-warning">' . lang('output_limit_exceeded') . '</span>'; break;
			case 5: $detail->status = '<span class="label label-warning">' . lang('memory_limit_exceeded') . '</span>'; break;
			case 6: $detail->status = '<span class="label label-warning">' . lang('time_limit_exceeded') . '</span>'; break;
			case 7: $detail->status = '<span class="label label-important">' . lang('runtime_error') . '</span>'; break;
			case 8: $detail->status = '<span class="label">' . lang('compile_error') . '</span>'; break;
			case 9: $detail->status = '<span class="label">' . lang('internal_error') . '</span>'; break;
			default: $detail->status = 'Nothing Happened';
		}

		if ($statId && !$beenWrong && $this->config->item('allow_download_first_wrong'))
		{
			$s = '';
			if ($statId != 8)
				foreach ($filemode[3] as $file => $property)
					if (isset($property->case) && in_array($id, (array)($property->case)))
					{
						$detail->status .= " <a href='index.php/main/download/$pid/$file/1'>$file</a>";
						if ($s) $s .= '|';
						$s .= $file;
					}
			$this->session->set_userdata('download', $s);
			session_write_close();
			$beenWrong = true;
		}
		
		$s = '';
		if (isset($detail->time))
			foreach ($detail->time as $file => $time)
				$s .= ($showName?"<i>$file: </i>":"")."$time ms";
		else
			$s = '---';
		$detail->time = $s;

		$s = '';
		if (isset($detail->memory))
			foreach ($detail->memory as $file => $memory)
			{
				if ($showName) $s .= "<i>$file: </i>";
				if ($memory >= 1048576)
					$s .= number_format($memory / 1048576, 2) . ' GB';
				else if ($memory >= 1024)
					$s .= number_format($memory / 1024, 2) . ' MB';
				else
					$s .= $memory . ' KB';
			}
		else
			$s = '---';
		$detail->memory = $s;

		$s = '';
		if (isset($detail->codeLength))
			foreach ($detail->codeLength as $file => $codeLength)
				$s .= ($showName?"<i>$file: </i>":"")."$codeLength bytes";
		else
			$s = '---';
		$detail->codeLength = $s;
	?>
<?php endforeach; ?>

<table class="table table-bordered table-condensed table-striped">

	<tr>
		<th>Case</th>
		<th>Test</th>
		<th>Score</th>
		<th>Verdict</th>
		<th>Time</th>
		<th>Memory</th>
		<th>Code Length</th>
		<th>Message</th>
	</tr>

	<?php foreach ($group as $case => $tests): ?>
		<?php if (!isset($tests)) continue; ?>
		<tr><td rowspan="<?=count($tests)?>"><?=$case?></td>
		<?php foreach ($tests as $x => $test): ?>
			<?php if ($x) echo "<tr>"; ?>
			<td><?=$test?></td>
			<td><span class="badge badge-info"><?=$result[$test]->score?></span></td>
			<td><?=$result[$test]->status?></td>
			<td><?=$result[$test]->time?></td>
			<td><?=$result[$test]->memory?></td>
			<td><?=$result[$test]->codeLength?></td>
			<td><?=isset($result[$test]->message)? $result[$test]->message: ''?></td>
			</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>

</table>
