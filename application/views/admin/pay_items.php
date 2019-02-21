<?php
	function display_timeInt($type, $timeInt){
		$str = '';
		if ($type == 0){
			$day = floor($timeInt / (24 * 60 * 60));
			$timeInt = $timeInt % (24 * 60 * 60);
			$hour = floor($timeInt / (60 * 60));
			$timeInt = $timeInt % (60 * 60);
			$minute = floor($timeInt / 60);

			if ($day > 0)
				$str .= $day . ' ' . lang('day') . ' ';
			if ($hour > 0)
				$str .= $hour . ' ' . lang('hour') . ' ';
			if ($minute > 0)
				$str .= $minute . ' ' . lang('minute');
		}
		else $str = date('Y-m-d H:i', $timeInt);
		return $str;
	}
?>

<button class="btn btn-primary pull-right mb_10" onclick="change_item()"><?=lang('add_item')?></button>

<table class="table table-bordered table-condensed table-stripped">
	<thead style="background-color:#89cff0">
		<?php foreach (array(
			'itemid' => '#',
			'itemDescription' => lang('item_description'),
			'price' => lang('price'),
			'type' => lang('type'),
			'timeInt' => lang('time_int')
			) as $key => $title): ?>
			<th style='white-space: nowrap'>
				<?php
					$iconType = $keyword!=$key?'icon-resize-vertical':($order!='reverse'?'icon-arrow-up':'icon-arrow-down');
					$iconUrl = ($keyword!=$key||$order=='reverse')?"#admin/items?sort=$key":"#admin/items?sort=$key&order=reverse";
				?>
				<a href='<?=$iconUrl?>'>
					<?=$title?>
					<i class='<?=$iconType?>'></i>
				</a>
			</th>
		<?php endforeach; ?>
		<th></th>
	</thead>
	<tbody><?php
		foreach ($data as $row){
			echo "<tr id='item$row->itemid'>";
			echo "<td>$row->itemid</td>";
			echo "<td class='itemdes'>$row->itemDescription</td>";
			echo "<td>￥$row->price</td>";
			echo "<td>".lang('item_type'.$row->type)."</td>";
			echo "<td>".display_timeInt($row->type, $row->timeInt)."</td>";
			echo "<td><button class='close' onclick=\"delete_item($row->itemid)\">&times;</button>";
			echo "<a href='javascript:void(0)' onclick=\"change_item($row->itemid)\" style='text-decoration: none'>
				<i class='icon-pencil'></i></a>";
			echo "</td></tr>";
		}
	?></tbody>
</table>

<div class="modal hide fade" id="modal_confirm">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<h3>Are you sure to delete item #<span id="info"></span>:</h3>
		<strong><h4 id="description"></span></h4>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Close</a>
		<a class="btn btn-danger" id="delete">Delete</a>
	</div>
</div>

<div class="modal hide fade" id="modal_edit" style="max-height:100%;max-width:100%;overflow-Y:auto"></div>

<script type="text/javascript">

	function delete_item(itemid){
		$('#modal_confirm #delete').live('click', function(){
			$('#modal_confirm').modal('hide');
			access_page('admin/delete_item/' + itemid);
		});
		$('#modal_confirm #info').html(itemid);
		$('#modal_confirm #description').html($('#item'+itemid).find('.itemdes').html());
		$('#modal_confirm').modal({backdrop: 'static'});
	}
	
	function change_item(itemid = 0){
		$.get("index.php/admin/change_item/" + itemid, {}, function(data) {
			$("#modal_edit").html(data);
			$("#modal_edit").modal({backdrop: 'static'});
		});
		return false;
	}

	function save_item(){
		$("#form_edit").ajaxSubmit({
			success: function(responseText, stautsText){
				status = responseText.substr(0, 7);
				if (status == 'success'){
					$.get(window.location.href.replace("#", ""), function(data){
						$('#modal_edit').modal('hide');
						$('#page_content').html(data);
					});
				} else $('#modal_edit').html(responseText);
			}
		});
		return false;
	}

</script>
