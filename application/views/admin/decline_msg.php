<?php if (isset($success) && $success): ?>
	<h3><?=lang('reject_success')?></h3>
<?php else: ?>
	<div class="container-fluid">
		<div class="row-fluid">
			<form class="span6" action="index.php/admin/decline_review/<?=$pid?>" method="post">
				<h3><?=lang('input_reject')?></h3>
				<textarea name="msg" rows="4" style="width:100%"></textarea>
				<input type="submit" class="btn btn-primary pull-right"></input>
				<span class="btn pull-right" onclick="history.back()">Cancel</span>
			</form>
		</div>
	</div>
<?php endif; ?>
