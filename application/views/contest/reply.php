<?php
function onelevel($root, $data, $uid, $is_admin)
{
	foreach ($data as $row)
	{
		echo "<div class='row'><hr /></div>";
		echo "<div class='row reply-body' data-id='$row->id'>";
		echo "<span class='reply-content' data-id='$row->id'>$row->content</span>";
		echo "<span class='pull-right'>";
		echo "   <span class='btn btn-link reply-reply' data-id='$row->id' data-root='$root'>Reply</span>";
		if ($uid==$row->uid)
			echo "<span class='btn btn-link mdfy-reply' data-id='$row->id' data-root='$root'>Modify</span>";
		if ($uid==$row->uid || $is_admin)
			echo "<span class='btn btn-link del-reply' data-id='$row->id' data-root='$root'>Delete</span>";
		echo "   <span class='label label-info'>$row->user</span>";
		echo "   $row->date";
		echo "</span>";
		echo "</div>";
		if (count($row->reply))
		{
			echo "<div class='row'>";
			echo "   <div class='span2'></div>";
			echo "   <div class='span10'>";
			onelevel($root, $row->reply, $uid, $is_admin);
			echo "   </div>";
			echo "</div>";
		}
	}
}

$this->load->model('user');
$uid = $this->user->uid();
$is_admin = $this->user->is_admin();
onelevel($id, $data, $uid, $is_admin);

echo "<div class='row'><hr /></div>";
echo "   <span class='form-inline'>";
echo "   <input class='span10 reply-input' type='text' data-id='$id'></input> ";
echo "   <span class='btn reply-button' data-id='$id' data-to='$id'>Reply</span>";
echo "   <span class='btn reply-cancelr' data-id='$id' style='display:none'>Cancel</span>";
echo "   <span class='btn reply-modify' data-id='$id' style='display:none'>Modify</span>";
echo "   <span class='btn reply-cancelm' data-id='$id' style='display:none'>Cancel</span>";
echo "</span>";
