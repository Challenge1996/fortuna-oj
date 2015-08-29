<div class="hero-unit" style="text-align: center">
	<h2><?=$data->title?></h2>
	<p class="time">
		Start Time: <span class="badge badge-info"><?=$data->startTime?></span>
		Submit Time: <span class="badge badge-info"><?=$data->submitTime?></span>
		End Time: <span class="badge badge-info"><?=$data->endTime?></span> Status: <?=$data->status?><br />
	</p>
	<?php if ($data->isTemplate == 1 && !$this->user->is_admin()): ?>
	<p class="act_time">
		<?php if (isset($custom->startTime)): ?>
		Your Start Time: <span class="badge badge-info"><?=$custom->startTime?></span>
		Your Submit Time: <span class="badge badge-info"><?=$custom->submitTime?></span>
		Your End Time: <span class="badge badge-info"><?=$custom->endTime?></span>
		Your Status: <?=$custom->status?><br />
		<?php else: $temp = $this->contests->load_relative_time($data->cid); ?>
		Submit After: <span class="badge badge-info"><?=gmdate("H:i", $temp->submitTime)?></span>
		End After: <span class="badge badge-info"><?=gmdate("H:i", $temp->endTime)?></span>
		<?php if (strtotime('now') + $temp->endTime <= strtotime($data->endTime)): ?>
		<button class="btn btn-primary" onclick="start_contest_onclick()">Start</button>
		<?php endif; ?>
		<?php endif; ?>
	</p>
	<?php endif; ?>
	<p class="allowed_languages">Allowed Languages: <font color='#006652'><?=$data->language?></font></p>
	<p><?=$data->description?></p>
</div>

<script type="text/javascript">
	function start_contest_onclick() {
		if (confirm("Are you sure to start the contest? This cannot be reverted!")) {
			$.ajax({
				url: 'index.php/contest/start/<?=$data->cid?>',
				success: function (responseText, statusText) {
					if (responseText == 'success') load_page('contest/problems/<?=$data->cid?>');
					else alert('Contest start failed!');
				}
			});
		} else {
			location.reload();
		}
	}
</script>
