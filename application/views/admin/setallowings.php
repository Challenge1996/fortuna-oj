<link href="css/setallowings.css" rel="stylesheet">
<script src="js/setallowings.js" type="text/javascript"></script>

<div class='container-fluid' style="overflow:hidden">
	<h3>Set Allowed Problems</h3>
	<ul class="inline row-fluid list-ul">
		<li class="user-li" style="display:none"></li>
		<?php foreach ($users as $user):?>
			<li class="user-li" data-user="<?=$user?>"><span id="user-span-<?=$user?>" class="label label-info"><?=$user?><span class="close user-close">&times;</span></span></li>
		<?php endforeach;?>
		<li id='user-input-li'><input id='user-input' placeholder='Users. Saperated by spaces, tabs or commas. The users should be set to "restricted" mode.'></input>
	</ul>
	<ul class="inline row-fluid list-ul">
		<li class="prob-li" style="display:none"></li>
		<?php foreach ($probs as $prob):?>
			<li class="prob-li" data-prob="<?=$prob?>"><span id="prob-span-<?=$prob?>" class="label label-info"><?=$prob?><span class="close prob-close">&times;</span></span></li>
		<?php endforeach;?>
		<li id='prob-input-li'><input id='prob-input' placeholder='Problem IDs. Saperated by spaces, tabs or commas.'></input>
	</ul>

	<div class='row-fluid'><span class='pull-right'>
		<button id='del_all' class='btn'>Set All to Forbidden</button>
		<button id='add_all' class='btn'>Set All to Allowed</button>
	</span></div>

	<div class='row-fluid'><span class='pull-right'>
		<i>Click on a check or a cross to flip one status.</i>
	</span></div>

	<table class="row-fluid table-striped table-bordered">
		<tr>
			<th></th>
			<?php foreach ($probs as $prob): ?>
				<th><span class="label label-info"><?=$prob?></span></th>
			<?php endforeach; ?>
		</tr>
		<?php foreach ($users as $user): ?>
			<?php $user = strtolower($user); ?>
			<tr>
				<th><span class="label label-info"><?=$user?></span></th>
				<?php
					foreach ($probs as $prob)
						echo isset($data[$user][$prob])?
						"<td><div class='check pull-right' data-user='$user' data-prob='$prob'></div></td>":
						"<td><div class='cross pull-right' data-user='$user' data-prob='$prob'></div></td>";
				?>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
