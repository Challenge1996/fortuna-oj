<?php
	$data = array(
		'itemDescription' => array(
			'name' => 'itemDescription',
			'id' => 'itemDescription',
			'value' => set_value('itemDescription', isset($item) ? $item->itemDescription : ''),
			'placeholder' => lang('item_description') . '*',
			'class' => 'input-medium'
		),
		'price' => array(
			'name' => 'price',
			'id' => 'price',
			'value' => set_value('price', isset($item) ? $item->price : '0.00'),
			'placeholder' => lang('price') . '*',
			'class' => 'input-medium'
		),
		'type' => array(
			'0' => lang('item_type0'),
			'1' => lang('item_type1')
		)
	);

	$itemid = isset($item) ? $item->itemid : 0;
	$item_type = set_value('type', isset($item) ? $item->type : 0);
	$timeInt = set_value('timeInt', isset($item) ? $item->timeInt : 24 * 60 * 60);
?>
	
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h3><?=isset($item) ? lang('change_item') : lang('add_item')?></h3>
</div>

<form action="admin/change_item/<?=$itemid?>" id="form_edit" class="form" method="post">
	<div class="modal-body form-horizontal">
		<input type="hidden" name="itemid" value="<?=$itemid?>" />

		<div class="control-group">
			<label class="control-label" for="itemDescription"><i class="icon-comment"></i><?=lang('item_description') . '*'?></label>
			<div class="controls"><?=form_input($data['itemDescription'])?></div>
		</div>
		<?=form_error('itemDescription')?>

		<div class="control-group">
			<label class="control-label" for="price"><i class="icon-shopping-cart"></i><?=lang('price') . '*'?></label>
			<div class="controls"><?=form_input($data['price'])?></div>
		</div>
		<?=form_error('price')?>

		<div class="control-group">
			<label class="control-label" for="type"><i class="icon-th-list"></i><?=lang('type')?></label>
			<div class="controls"><?=form_dropdown('type', $data['type'], $item_type, 'id="type" class="span4"')?></div>
		</div>
		<?=form_error('type')?>

		<div class="control-group">
			<label class="control-label"><i class="icon-time"></i>
				<span id="item_type0" style="display:none"><?=lang('item_type0')?>*</span>
				<span id="item_type1" style="display:none"><?=lang('item_type1')?>*</span>
			</label>
			<div class="controls">
				<input type="number" id="period" name="period" class="input-small" style="display:none" value="<?=$timeInt?>" />
				<select id="period_type" name="period_type" class="span3" style="display:none">
					<option value="0"><?=lang('day')?></option>
					<option value="1"><?=lang('hour')?></option>
					<option value="2"><?=lang('minute')?></option>
				</select>
				<input type="date" id="date" name="date" class="input-medium" style="display:none"/>
				<input type="time" id="time" name="time" class="input-small" style="display:none"/>

				<input type="hidden" name="timeInt" id="timeInt" value="<?=$timeInt?>"/>
			</div>
		</div>
		<?=form_error('timeInt')?>
	</div>
	
	<div class="modal-footer">
		<a class="btn pull-left" data-dismiss="modal">Cancel</a>
		<button class="btn btn-primary" onclick="return save_item()">Submit</button>
	</div>
</form>

<script type="text/javascript">
	var period_arr = [24 * 60 * 60, 60 * 60, 60];
	var period_type = 0;
	<?php if (set_value('period') == '' || set_value('period_type') == ''): ?>
		var item_type = <?=$item_type?>, period = <?=$timeInt?>, datetime = <?=$timeInt?>;
		for (var i = 0; i < 3; ++i)
			if (period % period_arr[i] == 0 || i == 2){
				period_type = i;
				period /= period_arr[i];
				break;
			}
	<?php else: ?>
		var item_type = <?=$item_type?>, period = <?=set_value('period')?>, period_type = <?=set_value('period_type')?>, datetime = <?=$timeInt?>;
	<?php endif; ?>
	if (item_type == 1) period = 0; else datetime = 0;
	change_timeint();

	$('#type').change(function(){
		item_type = $('#type').val();
		change_timeint();
		update_timeint();
	});

	$('#period').change(function(){
		period = $('#period').val();
		update_timeint();
	});

	$('#period_type').change(function(){
		period_type = $('#period_type').val();
		update_timeint();
	});

	$('#date, #time').change(function(){
		var str = $('#date').val() + ' ' + $('#time').val()
		datetime = (new Date(Date.parse(str.replace(/-/g,"/"))).getTime()) / 1000;
		update_timeint();
	});

	function twodigit(num){
		return num < 10 ? '0' + num : num;
	}

	function change_timeint(){
		if (item_type == 0){
			if (period == 0){
				period = 1;
				period_type = 0;
			}
			$('#period').val(period);
			$('#period_type').val(period_type);
			$('#period, #period_type, #item_type0').show();
			$('#date, #time, #item_type1').hide();
			//datetime = 0;
		}
		else {
			if (datetime == 0) datetime = (new Date().getTime() / 1000) + 24 * 60 * 60;
			var obj = new Date(datetime * 1000);
			var year = obj.getFullYear(),
				month = twodigit(obj.getMonth() + 1),
				day = twodigit(obj.getDate()),
				hour = twodigit(obj.getHours()),
				minute = twodigit(obj.getMinutes());
			$('#date').val(year+'-'+month+'-'+day);
			$('#time').val(hour+':'+minute);
			$('#date, #time, #item_type1').show();
			$('#period, #period_type, #item_type0').hide();
			//period = 0;
		}
	}

	function update_timeint(){
		if (item_type == 0)
			$('#timeInt').val(parseInt(period * period_arr[period_type]));
		else
			$('#timeInt').val(parseInt(datetime));
	}

</script>

<!-- End of file form_item.php -->
