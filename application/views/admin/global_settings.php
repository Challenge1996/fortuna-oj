<h3><?=lang('global_settings')?></h3>

<hr />

<?php foreach ($data as $key => $config): ?>
	<?php if ($config->format->datatype == 'group_begin'): ?>
		<div class="thumbnail text-left mb_10">
			<legend><h4 style="margin-left:10px"><?=lang("global_settings_item_key_$key")?></h4></legend>
	<?php elseif ($config->format->datatype == 'group_end'): ?>
		</div>
	<?php else: ?>
		<dl class="dl-horizontal" style="margin-left:10px">
			<dt><?=lang("global_settings_item_key_$key")?></dt>
			<dd>
				<p><?=lang("global_settings_item_description_$key")?></p>
				<p>
				<?php if ($config->format->datatype == 'enum'): ?>
					<div class="btn-group" data-toggle="buttons-radio">
						<?php foreach ($config->format->enum_value as $enum_key => $enum_value): ?>
							<?php $active = ($config->value===$enum_value?'active':''); ?>
								<span class="btn btn-primary <?=$active?>" onclick='change("<?=$key?>",<?=json_encode($enum_value)?>)'>
								<?=lang("global_settings_enum_$enum_key")?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php elseif ($config->format->datatype == 'input'): ?>
					<input class="settings_input" id="<?=$key?>" value="<?=htmlspecialchars($config->value)?>" />
					<span class="btn btn-primary" onclick='change_input("<?=$key?>")'><?=lang('save')?></span>
				<?php endif ?>
				</p>
			</dd>
		</dl>
	<?php endif; ?>
<?php endforeach; ?>

<script>
	function change(key, value)
	{
		$.post(
			"index.php/admin/global_settings",
			{ set: JSON.stringify({key: key, value: value}) },
			function() { location.reload(); }
		);
	}

	function change_input(key)
	{
		change(key, $('#'+key).val());
	}

	$('.settings_input').bind('keypress', function(event){
		if (event.keyCode == '13')
			change_input($(this).attr('id'));
	});
</script>
