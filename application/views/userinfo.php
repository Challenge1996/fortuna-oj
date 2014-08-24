<?php
	if ($user) {
?>
	<div style="margin: 0">
		<a href="#users/<?=$user?>" id="username">
			<div style="text-align:center; padding:3px; margin: 0 auto" class="well">
				<img src='<?=$avatar?>' />
			</div>
			<div style='text-align:center; margin: 3px auto 0'>
				<span class="label label-info"><?=$user?></span>
				<a id="logout" href="#main/home" class='pull-right'>
					<i class="icon-off" style="margin-right: 5px"></i>
				</a>
			</div>
		</a>
		<div style='text-align:center; margin-top:3px'>
			<a href='#misc/mailbox'>
				<i class='icon-envelope'></i>
				<span class='badge badge-important' id="unread_mail_count" style="padding: 1px 4px"></span>
			</a>
			<a id="setting" href="#users/<?=$user?>/settings">
				<i class="icon-cog" style="margin-left: 3px"></i>
			</a>
		</div>
	</div>
<?php
	}
?>
