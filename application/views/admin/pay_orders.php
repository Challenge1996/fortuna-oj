<link href="css/iconfont.css" rel="stylesheet">

<table class="table table-bordered table-condensed table-stripped">
	<thead style="background-color:#89cff0">
		<?php foreach (array(
			'orderid' => '#',
			'payid' => lang('payapi_id'),
			'uid' => 'UID',
			'name' => lang('username'),
			'itemDescription' => lang('item_description'),
			'expiration' => lang('expiration'),
			'price' => lang('price'),
			'realPrice' => lang('realprice'),
			'method' => lang('pay_method'),
			'status' => lang('status'),
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
	</thead>
	<tbody><?php
		foreach ($data as $row){
			if ($row->status == 1)
				echo "<tr style='background-color:#6DEF9D'>";
			else if ($row->status == -1)
				echo "<tr style='background-color:#FF8888'>";
			else
				echo "<tr>";
			echo "<td>$row->orderid</td>";
			echo "<td>$row->payid</td>";
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
			if ($row->status == 0) $iconType = 'iconfont iconfont-dengdai';
			else if ($row->status == 1) $iconType = 'iconfont iconfont-yiwancheng1';
			else $iconType = 'icon-remove';
			echo "<td><i class='$iconType'></i></td>";
			echo "<td>$row->createTime</td>";
			echo "<td>$row->finishTime</td></tr>";
		}
	?></tbody>
</table>