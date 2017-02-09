<?php
	$this->load->model('user');
?>

<form>
	<?php foreach($list as $group => $sublist): ?>
		<div class='alert alert-info' style='margin:10px -15px; padding:10px 0px'>
			<?php if ($group): ?>
				<em><?=$group?></em>
			<?php endif; ?>
			<?php foreach($sublist as $item): ?>
				<label class="checkbox">
					<?php for ($i = 0; $i < $item->depth; $i++):?>
						<span style="color: grey">—</span>
					<?php endfor; ?>
					<input type="checkbox"
						   <?=$item->chosen ? "checked" : ""?>
						   <?=($readonly || (! $isAdmin && isset($item->properties->prohibit) && $item->properties->prohibit)) ?
								"disabled" : 
								("onclick=toggleTag($item->idCategory," . ($item->chosen ? "0" : "1") . ")")
						   ?>
					></input>
					<span class="label"><?=$item->name?></span>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
	<?php if (! $readonly): ?>
		<p><em style="color: grey"><?=lang('add_subtag_note')?></em></p>
		<?php if ($this->user->is_admin()): ?>
			<p><a href="#admin/manage_tags"><?=lang('add_new_tags')?></a></p>
		<?php endif; ?>
	<?php endif; ?>
</form>

<script>
	function toggleTag(id, value) {
		$.get(value ? ("index.php/main/addtag/<?=$pid?>?tag=" + id) : ("index.php/main/deltag/<?=$pid?>/" + id), function(res) {
			refresh_page();
		});
	}
</script>

