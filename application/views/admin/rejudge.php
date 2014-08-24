<h4>Rejudge</h4>
<hr />

<?=validation_errors()?>

<form id="form_rejudge" class="form form-inline" method="post" action="index.php/admin/rejudge">
	<label>
		<input type="radio" name="type" value="submission" />
		Submission
	</label>
	<label>
		<input type="radio" name="type" value="problem" />
		Problem
	</label>
	<br />
	<label>
		ID: 
		<input type="number" name="id" min="1000" class="input-small" />
	</label>
	
	<button id="btn_rejudge" class="btn btn-primary btn-small">Rejudge</button>
</form>

<script type="text/javascript">
	$(document).ready(function(){
		$("#btn_rejudge").click(function(){
			$("#btn_rejudge").addClass('disabled');
			$("#form_rejudge").ajaxSubmit({
				success: function(responseText){
					if (responseText == 'success'){
						load_page('main/status');
					} else $("#page_content").html(responseText);
				}
			});
			return false;
		})
	})
</script>