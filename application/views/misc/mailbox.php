<h4>Mails
	<button class="btn btn-small btn-primary pull-right" onclick="load_page('misc/newmail')">New</button>
</h4>
<hr />

<table class="table table-bordered table-hover">
	<thead>
		<th>From</th>
		<th style="width: 50%">Title</th>
		<th>Sent Time</th>
		<th>Status</th>
	</thead>
	<tbody><?php
		$uid = $this->user->uid();

		foreach ($mails as $mail) {
			if ($mail->from_uid == $uid) $mail->user = $mail->to_user;
			else $mail->user = $mail->from_user;
			if (is_null($mail->isRead)) $mail->isRead = 1;

			if ($mail->isRead == 0) echo '<tr class="error">'; else echo '<tr>';
			echo "<td><a href='#users/$mail->user'><span class='label label-info'>$mail->user</span></a></td>";

			echo '<td><div style="width:100%">';
			if ($mail->isRead == 0) echo "<a href='#misc/mail/$mail->user'><strong>$mail->title</strong></a>";
			else echo "<a href='#misc/mail/$mail->user'>$mail->title</a>";
			echo '</div></td>';

			echo "<td>$mail->sendTime</td>";
			if ($mail->isRead == 1) echo "<td><span class='label label-success'>Read</span></td>";
			else echo "<td><span class='label label-important'>Unread</span></td>";
			echo '</tr>';
		}
	?></tbody>
</table>
