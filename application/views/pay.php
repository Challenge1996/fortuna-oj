<?php $this->load->model('user'); ?>

<div id="pay_field" class="modal">
	<div class="modal-header">
		<h3>
			<div style="text-align:center"><?=$this->config->item('oj_title')?></div> <!-- span can't be used here -->
			<em style="font-size:80%"><?=lang('enable_account')?></em>
		</h3>
	</div>

	<form action="index.php/main/pay" id="pay_form" method="post">
		<div class="modal-body">
			<?php if (isset($illegal)): ?>
			<div class="alert mb_10"><?=$illegal?></div>
			<?php endif; ?>
			
			<div class="form_block">
				<span style="width:90px"><i class="icon-user"></i><?=lang('user')?></span>

				<input type="text" id="username" name="username" placeholder="Username" value="<?=set_value('username')?>"/>

				<?=form_error('username')?>
			</div>
			
			<div class="form_block">
				<span style="width:90px"><i class="icon-shopping-cart"></i><?=lang('pay_item')?></span>

				<select id="pay_item" name="itemid">
					<?php
						foreach ($pay_item as $item){
							echo "<option ";
							if ($item->price == 0)
								echo "class='renew' ";
							echo "value='$item->itemid'>$item->itemDescription (￥$item->price)</option>";
						}
					?>
				</select>

				<?=form_error('itemid')?>
			</div>

			<div class="alert alert-info mb_0">
				<?=lang('expiration')?>: 
				<strong><span id="expire_info"></span></strong>
			</div>

			<input type="hidden" id="istype" name="istype" value="0" />
		</div>

		<div class="modal-footer">
			<span class="btn pull-left" onclick="return login()"><?=lang('login')?></span>

			<?php
				if (in_array(2, $pay_method)) echo "<button class='btn btn-primary pull-right pay_button' style='margin-left:5px; display:none' onclick='return pay_submit(2)'>".lang('wepay')."</button>";
				if (in_array(1, $pay_method)) echo "<button class='btn btn-primary pull-right pay_button' style='display:none' onclick='return pay_submit(1)'>".lang('alipay')."</button>";
			?>
			<button class='btn btn-primary pull-right renew_button' style='display:none' onclick='return pay_submit(1)'><?=lang('apply_renew')?></button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#pay_field').modal({backdrop: 'static'});
	var expiration = {
		<?php 
			foreach ($pay_item as $item){
				$expiration = $item->timeInt;
				if ($item->type == 0)
					$expiration += time();
				echo "'$item->itemid':'".date('Y-m-d H:i', $expiration)."',";
			}
		?>
	};
	change_pay_item();

	function login(){
		$('#pay_field').modal('hide');
		load_page('main/home');
		return false;
	}

	function pay_submit(istype){
		$('#pay_field').modal('hide');
		$('#istype').val(istype);
		$('#pay_form').ajaxSubmit({
			success: function (response){
				if (response === 'success'){
					load_page('main/home');
					return;
				}
				try {
					$.parseJSON(response);
					var form = $("<form method='post' action='https://pay.sxhhjc.cn/'></form>");
					var input;
					$.each(response, function(key, value){
						input = $("<input type='hidden'>");
						input.attr({"name": key});
						input.val(value);
						form.append(input);
					});
					$('body').append(form);
					form.submit();
				}
				catch (e) {
					$('#page_content').html(response);
				}
			}
		});
		return false;
	}

	function change_pay_item(){
		$('#expire_info').html(expiration[$('#pay_item').val()]);
		if ($('#pay_item option:selected').hasClass('renew')){
			$('.pay_button').hide();
			$('.renew_button').show();
		}
		else {
			$('.renew_button').hide();
			$('.pay_button').show();
		}
	}

	$('#pay_item').change(function(){
		change_pay_item();
	});

</script>
