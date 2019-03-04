<div id="pay_check_field" class="modal">
	<div class="modal-header">
		<h3>
			<div style="text-align:center"><?=$this->config->item('oj_title')?></div> <!-- span can't be used here -->
		</h3>
	</div>

	<div class="modal-body">
		<div id="pay_check">
			<h4><?=lang('pay_check_message')?></h4>
			<div class="alert alert-info mb_10">
				<strong><?=lang('pay_check_query')?></strong>
				<span id="query_num" class="badge badge-success">0</span>
			</div>
		</div>
		<div id="pay_success" style="display:none">
			<h4><?=lang('pay_success0')?></h4>
			<strong><?=lang('pay_success1')?></strong>
		</div>
		<div id="pay_review" style="display:none">
			<h4><?=lang('pay_review0')?></h4>
			<strong><?=lang('pay_review1')?></strong>
		</div>
		<div id="pay_fail" style="display:none;color:red">
			<h4><?=lang('pay_fail0')?></h4>
			<strong><?=lang('pay_fail1')?></strong>
		</div>
	</div>

	<div class="modal-footer">
		<button class="btn btn-primary pull-right" onclick="return login()"><?=lang('login')?></button>
	</div>
</div>

<script type="text/javascript">
	$('#pay_check_field').modal({backdrop: 'static'});
	var orderid = '<?=$orderid?>', query_num = 0;
	pay_query();

	function login(){
		$('#pay_check_field').modal('hide');
		load_page('main/home');
		return false;
	}

	function pay_query(){
		$.ajax({
			type:"POST",
			url:"main/pay_status",
			data:{orderid:orderid},
			success: function(data) {
				if (data == 1){
					$('#pay_check').hide();
					$('#pay_success').show();
				}
				else if (data == 2){
					$('#pay_check').hide();
					$('#pay_review').show();
				}
				else if (data == -1 || query_num >= 60){
					$('#pay_check').hide();
					$('#pay_fail').show();
				}
				else {
					$('#query_num').html(parseInt($('#query_num').html())+1);
					++query_num;
					setTimeout('pay_query()', 3000);
				}
			},
			error: function() {
				++query_num;
				setTimeout('pay_query()', 3000);
			}
		});
	}

</script>