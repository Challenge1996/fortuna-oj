<h4>Misc Functions</h4>
<hr />

<?=validation_errors()?>

<form method="post" action="index.php/admin/functions" class="form form-horizontal" id="form_functions">
<div class="span5">
	<h5>Permissions</h5>
	<hr />
	<div class="control-group">
		<label for="name" class="control-label">Username</label>
		<div class="controls">
			<input type="text" class="input input-small" name="name" />
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">End Time</label>
		<div class="controls">
			<input type="date" name="date" class="input" value="<?=date('Y-m-d')?>"/>
			<input type="time" name="time" class="input" value="<?=date('H:m')?>"/>
		</div>
	</div>

	<div class="control-group">
		<label for="permission" class="control-label">Permission</label>
		<div class="controls">
			<input type="checkbox" name="permission[]" value="testdata" /> Download Testdata
		</div>
	</div>
</div>

<div class="span5">
	<h5>Reset Password</h5>
	<hr />
	<div class="control-group">
		<label for="reset_pwd_username" class="control-label">Username</label>
		<div class="controls">
			<input type="text" class="input input-small" name="reset_pwd_username" />
		</div>
	</div>

	<div class="control-group">
		<label for="password" class="control-label">Password</label>
		<div class="controls">
			<input type="text" name="reset_password" />
		</div>
	</div>
</div>

	<button class="btn btn-primary pull-right" onclick="return do_functions()">Set</button>
</form>

<script type="text/javascript">
	function do_functions() {
		$("#form_functions").ajaxSubmit({
			success: function(responseText) {
				if (responseText == 'success') load_page('main/home');
				else $("#page_content").html(responseText);
			}
		});
		return false;
	}
</script>
