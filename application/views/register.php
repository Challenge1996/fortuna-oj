<?php
	$data = array(
		'username' => array(
			'name' => 'username',
			'id' => 'username',
			'value' => set_value('username'),
			'placeholder' => 'Username*',
		),
		'password' => array(
			'name' => 'password',
			'id' => 'password',
			'placeholder' => 'Password*',
		),
		'confirm_password' => array(
			'name' => 'confirm_password',
			'id' => 'confirm_password',
			'placeholder' => 'Confirm Password*',
		),
		'email' => array(
			'name' => 'email',
			'id' => 'email',
			'value' => set_value('email'),
			'placeholder' => 'Email Address*',
		),
		'school' => array(
			'name' => 'school',
			'id' => 'school',
			'value' => set_value('school'),
			'placeholder' => 'School',
		),
		'description' => array(
			'name' => 'description',
			'id' => 'description',
			'value' => set_value('description'),
			'placeholder' => 'Descrption',
		)
	);
?>
	
<div id="register_field" class="modal">
	<div class="modal-header"><h3><em>Register</em></h3></div>
	
	<form action="index.php/main/register" id="register_form" class="form" method="post">
		<div class="modal-body form-horizontal">
			<div class="control-group">
				<label class="control-label" for="username"><i class="icon-user"></i>Username*</label>
				<div class="controls"><?=form_input($data['username']) . form_error('username')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email"><i class="icon-envelope"></i>Email Address*</label>
				<div class="controls"><?=form_input($data['email']) . form_error('email')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password"><i class="icon-briefcase"></i>Password*</label>
				<div class="controls"><?=form_password($data['password']) . form_error('password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="confirm_password"><i class="icon-repeat"></i>Confirm Password*</label>
				<div class="controls"><?=form_password($data['confirm_password']) . form_error('confirm_password')?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="confirm_password"><i class="icon-home"></i>School</label>
				<div class="controls"><?=form_input($data['school']) . '<br />'?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="description"><i class="icon-star"></i>Descrption</label>
				<div class="controls"><?=form_textarea($data['description'])?></div>
			</div>
		</div>
		
		<div class="modal-footer">
			<button type="submit" class="btn pull-left" onclick="return login()">Login</button>
			<button type="submit" class="btn btn-primary" onclick="return register_submit()">Submit</button>
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
