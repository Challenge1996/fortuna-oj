<?php
	if ($user) {
?>
	<div class="mb_10">
		<a href="#users/<?=$user?>" id="username">
			<div style="text-align:center; padding:3px; margin: 0 auto" class="well">
				<img src='<?=$avatar?>' />
			</div>
			<div style='text-align:center; margin: 3px auto 0'>
				<span class="label label-info"><?=$user?></span>
			</div>
		</a>
		<div style='text-align:center; margin-top:3px'>
			<a href='#misc/mailbox' style="text-decoration: none">
				<i class='icon-envelope'></i>
				<span class='badge badge-important' id="unread_mail_count" style="padding: 1px 4px"></span>
			</a>
			<a id="setting" href="#users/<?=$user?>/settings" style="text-decoration: none">
				<i class="icon-cog" style="margin-left: 3px"></i>
			</a>
			<a id="logout" href="javascript:void(0)" style="text-decoration: none">
				<i class="icon-off" style="margin-left: 3px"></i>
			</a>
		</div>
	</div>
<?php
	}
?>
