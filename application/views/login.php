<div id="login_field" class="modal">
	<div class="modal-header"><h3>Login</h3></div>

	<form action="index.php/main/login" id="login_form" method="post">
		<div class="modal-body">
			<div>
				<span style="position:absolute; left:10"><i class="icon-user"></i><?=lang('user')?></span>
				<input type="text" name="username" placeholder="Username" style="margin-left:90px" value="<?=set_value('username')?>"/>
				<?=form_error('username')?>
			</div>
			
			<div>
			<span style="position:absolute; left:10"><i class="icon-briefcase"></i><?=lang('password')?></span>
				<input type="password" name="password" placeholder="Password" style="margin-left:90px" />
				<?=form_error('password')?>
			</div>
			
			<div>
				<input type="checkbox" name="remember" style="margin-left:90px" value="1" />
				<?=lang('remember_me')?>
			</div>			
		</div>

		<div class="modal-footer">
			<button class="btn btn-primary pull-right" onclick="return login_submit()">Login</button>
			<button class="btn" onclick="return register()">Register</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#login_field').modal({backdrop: 'static', keyboard: false});
	function register(){
		$('#login_field').modal('hide');
		load_page('main/register');
		return false;
	}
</script>
