<?php
	$data = array(
		'username' => array(
			'name' => 'username',
			'value' => set_value('username'),
			'placeholder' => 'Username*',
			'style' => 'margin-left:160px'
		),
		'password' => array(
			'name' => 'password',
			'placeholder' => 'Password*',
			'style' => 'margin-left:160px'
		),
		'confirm_password' => array(
			'name' => 'confirm_password',
			'placeholder' => 'Confirm Password*',
			'style' => 'margin-left:160px'
		),
		'email' => array(
			'name' => 'email',
			'value' => set_value('email'),
			'placeholder' => 'Email Address*',
			'style' => 'margin-left:160px'
		),
		'school' => array(
			'name' => 'school',
			'value' => set_value('school'),
			'placeholder' => 'School',
			'class' => 'span6',
			'style' => 'margin-left:160px'
		),
		'description' => array(
			'name' => 'description',
			'value' => set_value('description'),
			'placeholder' => 'Descrption',
			'class' => 'span6',
			'style' => 'margin-left:160px'
		)
	);
?>
	
<div id="register_field" class="modal" style="width:740px; margin-left:-370px">
	<div class="modal-header"><h3><em>Register</em></h3></div>
	
	<form action="index.php/main/register" id="register_form" class="form" method="post">
		<div class="modal-body">
			<div>
				<span style="position:absolute; left:10"><i class="icon-user"></i>Username*</span>
				<?=form_input($data['username']) . form_error('username')?>
			</div>
			<div>
				<span style="position:absolute; left:10"><i class="icon-envelope"></i>Email Address*</span>
				<?=form_input($data['email']) . form_error('email') . '<br />'?>
			</div>
			<div>
				<span style="position:absolute; left:10"><i class="icon-briefcase"></i>Password*</span>
				<?=form_password($data['password']) . form_error('password')?>
			</div>
			<div>
				<span style="position:absolute; left:10"><i class="icon-repeat"></i>Confirm Password*</span>
				<?=form_password($data['confirm_password']) . form_error('confirm_password')?>
			</div>
			<div>
				<span style="position:absolute; left:10"><i class="icon-home"></i>School</span>
				<?=form_input($data['school']) . '<br />'?>
			</div>
			<div>
				<span style="position:absolute; left:10"><i class="icon-star"></i>Descrption</span>
				<?=form_textarea($data['description'])?>
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