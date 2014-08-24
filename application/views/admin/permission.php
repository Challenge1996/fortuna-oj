<h4>Permission</h4>
<hr />

<?=validation_errors()?>

<form method="post" action="index.php/admin/permission" class="form form-horizontal" id="form_permission">
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

	<button class="btn btn-primary pull-right" onclick="return set_permission()">Set</button>
</form>

<script type="text/javascript">
	function set_permission() {
		$("#form_permission").ajaxSubmit({
			success: function(responseText) {
				if (responseText == 'success') load_page('main/home');
				else $("#page_content").html(responseText);
			}
		});
		return false;
	}
</script>
