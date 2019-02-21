<?php
	$data = array(
		'username' => array(
			'name' => 'username',
			'id' => 'username',
			'value' => set_value('username'),
			'placeholder' => lang('user') . '*',
		),
		'password' => array(
			'name' => 'password',
			'id' => 'password',
			'placeholder' => lang('password') . '*',
		),
		'confirm_password' => array(
			'name' => 'confirm_password',
			'id' => 'confirm_password',
			'placeholder' => lang('confirm_password') . '*',
		),
		'email' => array(
			'name' => 'email',
			'id' => 'email',
			'value' => set_value('email'),
			'placeholder' => lang('email_address') . '*',
		),
		'school' => array(
			'name' => 'school',
			'id' => 'school',
			'value' => set_value('school'),
			'placeholder' => lang('school'),
		),
		'description' => array(
			'name' => 'description',
			'id' => 'description',
			'value' => set_value('description'),
			'placeholder' => lang('user_description'),
		)
	);
?>
	
<div id="register_field" class="modal" style="width:700px;max-height:100%;max-width:100%;overflow-Y:auto">
	<div class="modal-header"><h3><em>Register</em></h3></div>
	
	<form action="index.php/main/register" id="register_form" class="form" method="post">
		<div class="modal-body form-horizontal">
			<div class="control-group">
				<label class="control-label" for="username"><i class="icon-user"></i><?=lang('user') . '*'?></label>
				<div class="controls"><?=form_input($data['username']) . form_error('username')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email"><i class="icon-envelope"></i><?=lang('email_address') . '*'?></label>
				<div class="controls"><?=form_input($data['email']) . form_error('email')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password"><i class="icon-briefcase"></i><?=lang('password') . '*'?></label>
				<div class="controls"><?=form_password($data['password']) . form_error('password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="confirm_password"><i class="icon-repeat"></i><?=lang('confirm_password') . '*'?></label>
				<div class="controls"><?=form_password($data['confirm_password']) . form_error('confirm_password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="school"><i class="icon-home"></i><?=lang('school')?></label>
				<div class="controls"><?=form_input($data['school']) . '<br />'?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="description"><i class="icon-star"></i><?=lang('user_description')?></label>
				<div class="controls"><?=form_textarea($data['description'])?></div>
			</div>
		</div>
		
		<div class="modal-footer">
			<span type="submit" class="btn pull-left" onclick="return login()"><?=lang('login')?></span> <!-- Pressing ENTER in the form will automatically trigger the first <button> -->
			<button type="submit" class="btn btn-primary" onclick="return register_submit()"><?=lang('register')?></button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$('#register_field').modal({backdrop: 'static'});
	function login(){
		$('#register_field').modal('hide');
		load_page('main/home');
		return false;
	}
</script>

<!-- End of file register.php -->
