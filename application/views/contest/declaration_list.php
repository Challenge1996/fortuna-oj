<?php $this->load->model('user'); ?>
<div class="declaration_table">
	<table class="table table-condensed table-striped table bordered">
		<thead>
			<tr><th>Problem</th><th>Title</th><th>Post Time</th></tr>
		</thead>
		<tbody><?php
			foreach ($data as $row){
				echo "<tr><td>$row->prob</td><td><a href='#contest/declaration/$row->cid/$row->idDeclaration'>$row->title</a></td><td>$row->postTime</td></tr>";
			}
		?></tbody>
	</table>
</div>
<?php if ($this->user->is_admin()):?>
	<div class='well span4'>
		<fieldset>
			<legend> Add a declaration </legend>
			<form id="form" method='post'>
				<input type='text' name='title' placeholder='title' class='span8'></input>
				<input type='text' name='prob' placeholder='problem NO.' class='span4'></input>
				<textarea name='declaration' placeholder='declaration' class='span12' rows=6></textarea>
				<span id='submit' class='btn btn-primary pull-right'>Add</button>
			</form>
		</fieldset>
	</div>
	<script>
		$("#submit").click(function() {
			$("#submit").attr("disabled","disabled");
			$("#form").ajaxSubmit({
				url : 'index.php/contest/add_declaration/<?=$cid?>',
				success : function() { location.reload(); }
			});
		});
	</script>
<?php endif?>
