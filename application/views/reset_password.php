<?php
	$data = array(
		'username' => array(
			'name' => 'username',
			'id' => 'username',
			'value' => $name,
			'readonly' => 'true',
		),
		'password' => array(
			'name' => 'password',
			'id' => 'password',
			'placeholder' => 'New Password*',
		),
		'confirm_password' => array(
			'name' => 'confirm_password',
			'id' => 'confirm_password',
			'placeholder' => 'Confirm Password*',
		),
		'email' => array(
			'name' => 'email',
			'id' => 'email',
			'value' => $email,
			'readonly' => 'true',
		),
		'verification_key' => array(
			'name' => 'verification_key',
			'id' => 'verification_key',
			'value' => $key,
			'readonly' => 'true',
		),
	);
?>

<div id="reset_password_field" class="modal" style="width:700px;max-height:100%;max-width:100%;overflow-Y:auto">
	<div class="modal-header"><h3><em>Reset Password</em></h3></div>

	<form id="reset_password_form" class="form" method="post">
		<div class="modal-body form-horizontal">
			<div class="control-group">
				<label class="control-label" for="username"><i class="icon-user"></i>Username*</label>
				<div class="controls"><?=form_input($data['username'])?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email"><i class="icon-envelope"></i>Email Address*</label>
				<div class="controls"><?=form_input($data['email'])?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password"><i class="icon-briefcase"></i>New Password*</label>
				<div class="controls"><?=form_password($data['password']) . form_error('password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="confirm_password"><i class="icon-repeat"></i>Confirm Password*</label>
				<div class="controls"><?=form_password($data['confirm_password']) . form_error('confirm_password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="description"><i class=" icon-lock"></i>Verification Key</label>
				<div class="controls"><?=form_input($data['verification_key'])?></div>
			</div>
		</div>

		<div class="modal-footer">
			<button type="submit" class="btn btn-primary" onclick="reset_password_submit(); return false;">Submit</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#reset_password_field').modal({backdrop: 'static'});
	function reset_password_submit() {
		$('#reset_password_field').modal('hide');
		$('#reset_password_form').ajaxSubmit({
			url: 'index.php/main/reset_password/' + $('#username').val() + '/' + $('#verification_key').val(),
			success: function(responseText, statusText) {
				if (responseText == 'success') load_page('main/home');
				else $('#page_content').html(responseText);
			}
		});
	}
</script>

<!-- End of file reset_password.php -->
