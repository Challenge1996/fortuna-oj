<h3 style="text-align:center">
	<button class="pull-left btn btn-small" onclick="javascript:history.back()">Back</button>
	Mails
	<button class="pull-right btn btn-small btn-primary" onclick="load_page('misc/newmail/<?=$user?>')">Reply</button>
</h3>
<hr />

<div class="row-fluid" style="margin-bottom: 7px">
	<span class="pull-left label label-info"><a href="#users/<?=$user?>"><?=$user?></a></span>
	<span class="pull-right label label-info"><a href="#users/<?=$this->user->username()?>"><?=$this->user->username()?></a></span>
</div>

<div id="content"><?php
	$uid = $this->user->uid();
	foreach ($mails as $mail) {
		echo '<div class="row-fluid">';
		if ($mail->to_uid == $uid) echo "<div class='pull-left span6 well mail_content'>";
		else echo "<div class='pull-right span6 well mail_content'>";
		
		echo "<p class='lead' style='margin-bottom: 5px'>$mail->title</p>";
		echo '<hr style="margin: 3px;" />';
		echo "<p>$mail->content</p>";
		echo "<span class='pull-right'>$mail->sendTime</span>";

		echo '</div></div>';
	}
?></div>
