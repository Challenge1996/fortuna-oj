<?=validation_errors();?>

<form method="post" class="form-inline" id="submit_file" onsubmit="return check_on_submit()" enctype="multipart/form-data">
	<div class="submit_control" style="margin:5%">
		<label for="pid">Problem ID</label>
		<input name="pid" id="pid" type="text" class="input-mini" value="<?=$pid?>" <?=isset($cid) || isset($tid) ? 'readonly' : ''?> />
		<label for="cid">Contest ID</label>
		<input name="cid" id="cid" type="text" class="input-mini" value="<?=isset($cid) ? $cid : ''?>" readonly />
		<label for="tid">Task ID</label>
		<input name="tid" id="tid" type="text" class="input-mini" value="<?=isset($tid) ? $tid : ''?>" readonly />
		<input name="gid" id="gid" type="hidden" class="input-mini" value="<?=isset($gid) ? $gid : ''?>" />
		<input name="file" id="file" type="file" />
		<button type="submit" class="btn btn-primary pull-right">Submit</button>
	</div>
</form>
	
	
<script type="text/javascript">

	function check_on_submit(){
		if ($('#pid').val() == ''){
			alert("You should specify the Problem ID!");
			return false;
		}
		$('#submit_file').ajaxSubmit({
			url: 'index.php/main/upload/' + $('#pid').val(),
			success: function(responseText, statusText){
				if (responseText == 'success') {
					if ($('#cid').val() != '') load_page('contest/status/' + $('#cid').val());
					else load_page('main/status');
				} else alert('Failed to submit!');
			}
		});
		return false;
	}

</script>

<!--  End of file upload.php --> 
