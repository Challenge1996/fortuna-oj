<form>
	<?php foreach($list as $item): ?>
		<label class="checkbox">
			<?php for ($i = 0; $i < $item->depth; $i++):?>
				<span style="color: grey">—</span>
			<?php endfor; ?>
			<input type="checkbox"
			       <?=$item->chosen ? "checked" : ""?>
				   <?=($readonly || (! $isAdmin && $item->properties->prohibit)) ?
						"disabled" : 
						("onclick=toggleTag($item->idCategory," . ($item->chosen ? "0" : "1") . ")")
				   ?>
			></input>
			<span class="label"><?=$item->name?></span>
		</label>
	<?php endforeach; ?>
	<?php if (! $readonly): ?>
		<em style="color: grey"><?=lang('add_subtag_note')?></em>
	<?php endif; ?>
</form>

<script>
	function toggleTag(id, value) {
		$.get(value ? ("index.php/main/addtag/<?=$pid?>?tag=" + id) : ("index.php/main/deltag/<?=$pid?>/" + id), function(res) {
			refresh_page();
		});
	}
</script>

