<?php $this->load->model('user'); ?>

<script> saltl="<?=$saltl?>"; saltr="<?=$saltr?>"; </script>

<script src="js/login.js"></script>

<div id="login_field" class="modal">
	<div class="modal-header">
		<h3>
			<div style="text-align:center"><?=$this->config->item('oj_title')?></div> <!-- span can't be used here -->
		</h3>
	</div>

	<form action="index.php/main/login" id="login_form" method="post">
		<div class="modal-body">
			<div id="body0">
				<div>
					<span style="position:absolute; left:10"><i class="icon-user"></i><?=lang('user')?></span>
					<input type="text" id="username" name="username" placeholder="Username" style="margin-left:90px" value="<?=set_value('username')?>"/>
					<span id="username_error" class="add-on alert alert-error" style="display:none">Username is required</span>
					<?=form_error('username')?>
				</div>
				
				<div>
				<span style="position:absolute; left:10"><i class="icon-briefcase"></i><?=lang('password')?></span>
					<input id="ori_passwd" type="password" placeholder="Password" style="margin-left:90px" />
					<input id="new_passwd" type="hidden" name="password" />
					<?=form_error('password')?>
				</div>
				
				<div>
					<input type="checkbox" name="remember" style="margin-left:90px" value="1" />
					<?=lang('remember_me')?>
				</div>
			</div>
			<?php if ($this->config->item('mail_method')): ?>
				<div id="body1" style="display:none">
					<h3><?=lang('resetpwd_description')?></h3>
				</div>
			<?php endif; ?>
		</div>

		<div class="modal-footer">
			<div id="footer0">
				<?php if ($this->config->item('mail_method')): ?>
					<span class="btn btn-link pull-left" onclick="return load_forget()"><?=lang('resetpwd_link')?></span>
				<?php endif; ?>
				<button class="btn btn-primary pull-right" style="margin-left:5px" onclick="return login_submit()"><?=lang('login')?></button>
				<span class="btn pull-right" onclick="return register()"><?=lang('register')?></span>
				<?php if ($this->config->item('enable_payment')): ?>
					<span class="btn btn-info pull-right" onclick="return pay()"><?=lang('enable_account')?></span>
				<?php endif; ?>
			</div>
			<?php if ($this->config->item('mail_method')): ?>
				<div id="footer1" style="display:none">
					<span class="btn btn-link pull-left" onclick="return hide_forget()">Back</span>
					<span class="btn btn-danger pull-right" onclick="return send_reset()">Send</span>
				</div>
			<?php endif; ?>
		</div>
	</form>
</div>

