<link href="css/iconfont.css" rel="stylesheet">

<table class="table table-bordered table-condensed table-stripped">
	<thead style="background-color:#89cff0">
		<?php foreach (array(
			'status' => '',
			'orderid' => lang('order_id'),
			'uid' => 'UID',
			'name' => lang('username'),
			'itemDescription' => lang('item_description'),
			'expiration' => lang('expiration'),
			'price' => lang('price'),
			'realPrice' => lang('realprice'),
			'method' => lang('pay_method'),
			'createTime' => lang('create_time'),
			'finishTime' => lang('finish_time')
			) as $key => $title): ?>
			<th style='white-space: nowrap'>
				<?php
					$iconType = $keyword!=$key?'icon-resize-vertical':($order!='reverse'?'icon-arrow-up':'icon-arrow-down');
					$iconUrl = ($keyword!=$key||$order=='reverse')?"#admin/orders?sort=$key":"#admin/orders?sort=$key&order=reverse";
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
			if ($row->status == 1){
				echo "<tr style='background-color:#6DEF9D'>";
				$iconType = 'iconfont iconfont-yiwancheng1';
			}
			else if ($row->status == -1){
				echo "<tr style='background-color:#FF8888'>";
				$iconType = 'icon-remove';
			}
			else if ($row->status == 2){
				echo "<tr style='background-color:#89cff0'>";
				$iconType = 'icon-question-sign';
			}
			else {
				echo "<tr>";
				$iconType = 'iconfont iconfont-dengdai';
			}
			echo "<td><i class='$iconType'></i></td>";
			echo "<td>$row->orderid</td>";
			echo "<td>$row->uid</td>";
			echo "<td><span class='label label-info'><a href='#users/$row->name'>$row->name</a></span></td>";
			echo "<td>$row->itemDescription</td>";
			echo "<td>$row->expiration</td>";
			echo "<td>￥$row->price</td>";
			if (isset($row->realPrice))
				echo "<td>￥$row->realPrice</td>";
			else echo "<td></td>";
			if ($row->method == 1)
				echo "<td><i class='iconfont iconfont-umidd17'></i></td>";
			else 
				echo "<td><i class='iconfont iconfont-pay-wechat'></i></td>";
			echo "<td>$row->createTime</td>";
			echo "<td>$row->finishTime</td><td>";
			if ($row->status == 2){
				echo "<a href='javascript:void(0)' onclick='review_order(\"$row->orderid\", \"$row->name\", \"$row->expiration\")'><i class='icon-ok'></i></a>";
				echo " <a href='javascript:void(0)' onclick='reject_order(\"$row->orderid\", \"$row->name\")'><i class='icon-remove'></i></a>";
			}
			echo "</td></tr>";
		}
	?></tbody>
</table>

<div class="modal hide fade" id="modal_review">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?=lang('review_order')?></h3>
	</div>
	<div class="modal-body">
		<h4 class="label label-info" style="font-size:16px" id="username"></h4>
		<form action="admin/review_order" id="form_review" class="form form-horizontal" method="post">
			<div class="control-group">
				<label class="control-label"><i class="icon-time"></i><?=lang('change_expiration')?></label>
				<div class="controls">
					<input type="date" id="date" class="input-medium"/>
					<input type="time" id="time" class="input-small"/>
				</div>
			</div>
			<input type="hidden" id="orderid" name="orderid" />
			<input type="hidden" id="datetime" name="datetime" />
		</form>
	</div>
	<div class="modal-footer">
		<a class="btn pull-left" data-dismiss="modal"><?=lang('close')?></a>
		<button class="btn btn-success" onclick="return submit_review()"><?=lang('ok')?></button>
	</div>
</div>

<div class="modal hide fade" id="modal_reject">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Confirm Action</h3>
	</div>
	<div class="modal-body">
		<h3>Are you sure to reject order for:</h3>
		<h4 class="label label-info" style="font-size:16px" id="username"></h4>
	</div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal"><?=lang('close')?></a>
		<a class="btn btn-danger" id="reject">Reject</a>
	</div>
</div>

<script type="text/javascript">

	function reject_order(orderid, username){
		$('#modal_reject #reject').live('click', function(){
			$('#modal_reject').modal('hide');
			access_page('admin/reject_order/' + orderid);
		});
		$('#modal_reject #username').html(username);
		$('#modal_reject').modal({backdrop: 'static'});
	}

	function review_order(orderid, username, datetime){
		$('#modal_review #username').html(username);
		$('#modal_review #orderid').val(orderid);
		$('#modal_review #date').val(datetime.split(' ')[0]);
		$('#modal_review #time').val(datetime.split(' ')[1]);
		$('#modal_review').modal({backdrop: 'static'});
	}

	function submit_review(){
		$('#modal_review #datetime').val($('#modal_review #date').val() + ' ' + $('#modal_review #time').val());
		
		$("#form_review").ajaxSubmit({
			success: function(responseText, stautsText){
				status = responseText.substr(0, 7);
				if (status == 'success'){
					$.get(window.location.href.replace("#", ""), function(data){
						$('#modal_review').modal('hide');
						$('#page_content').html(data);
					});
				} else $('#modal_review').html(responseText);
			}
		});
		return false;
	}

</script>
