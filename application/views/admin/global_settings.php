<h3><?=lang('global_settings')?></h3>

<hr />

<div class='alert alert-block alert-info'>
	<?=lang('global_settings_description')?>
</div>

<dl class="dl-horizontal">
	<?php foreach ($data as $key => $config): ?>
		<dt><?=lang("global_settings_item_key_$key")?></dt>
		<dd>
			<p><?=lang("global_settings_item_description_$key")?></p>
			<?php if ($config->format->datatype == 'enum'): ?>
				<p><div class="btn-group" data-toggle="buttons-radio">
					<?php foreach ($config->format->enum_value as $enum_key => $enum_value): ?>
						<?php $active = ($config->value==$enum_value?'active':''); ?>
							<span class="btn btn-primary <?=$active?>" onclick='change("<?=$key?>",<?=json_encode($enum_value)?>)'>
							<?=lang("global_settings_enum_$enum_key")?>
						</span>
					<?php endforeach; ?>
				</div></p>
			<?php endif ?>
		</dd>
	<?php endforeach; ?>
</dl>

<script>
	function change(key, value)
	{
		$.post(
			"index.php/admin/global_settings",
			{ set: JSON.stringify({key: key, value: value}) },
			function() { location.reload(); }
		);
	}
</script>
