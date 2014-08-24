<h4>Statistic</h4>
<hr />
<?=validation_errors()?>
<form action="index.php/admin/statistic" class="form-inline" id="form_admin_statistic" method='post'>
	<span class='alert aler-error span8'>各数据间请用逗号隔开; 若要全选用户请将“用户”框留空</span>
	<button class="btn btn-primary" style="margin-left:8px" id="stat">Stat</button>
	<fieldset class="span5">
		<legend>Users</legend>
		<label for='user'><span class='span4'>User name: </span>
			<textarea name='user'></textarea>
		</label>
	</fieldset>
	
	<fieldset class="span5">
		<legend>Problems / Contests / Tasks</legend>
		<label for='user'><span class='span4'>Problem ID: </span>
			<textarea name='problem'></textarea>
		</label>
		
		<label for='group'><span class='span4'>Contest ID: </span>
			<textarea name='contest'></textarea>
		</label>
		
		<label for='user'><span class='span4'>Task ID: </span>
			<textarea name='task'></textarea>
		</label>
	</fieldset>
</form>

<script type="text/javascript">
	$("#stat").click(function() {
		$("#form_admin_statistic").ajaxSubmit({
			success: function(responseText, stautsText){
				$('#page_content').html(responseText);
			}
		});
		return false;
	})
</script>