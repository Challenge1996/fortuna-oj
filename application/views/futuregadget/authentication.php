<form action="index.php/futuregadget/authentication" method="post" class="form" id="auth">
	<label>PASSCODE: <input name='passcode' id="passcode" type="password" class="input input-xlarge" /></label>
	<button type="submit" class="btn btn-danger">Super Power!</buton>
</form>

<script type="text/javascript">
	$("#auth").submit(function() {

		$("#auth").ajaxSubmit({
			success: function(responseText, stautsText){
				if (responseText == 'success'){
					alert("Welcom agent!");
				} else $('#page_content').html(responseText);
			}
		});
		
		return false;
	})
</script>