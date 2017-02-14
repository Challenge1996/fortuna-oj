<h4>Problem Statements <a title="Click empty frames to edit!" id="editor_help"><i class="icon-question-sign"></i></a></h4>
<hr />

<?=validation_errors()?>

<div class="addproblem_form container-fluid">

	<form action="index.php/admin/addproblem/<?=isset($pid) ? $pid : ''?>" method="post" id="addproblem">
	
		<input type="hidden" name="title" id="title_data" />
		<fieldset>
			<legend><h5><?=lang('title')?></h5></legend>
			<h3><div class="well" id="title" style="padding: 5px; text-align:center" contenteditable="true">
				<?php if (isset($title)) echo $title; ?>
			</div></h3>
		</fieldset>

		<textarea id="problemDescription_data" name="problemDescription" style="display:none"></textarea>
		<fieldset>
			<legend><h5><?=lang('description')?></h5></legend>
			<div class="well" id="problemDescription" contenteditable="true">
				<?php if (isset($problemDescription)) echo nl2br($problemDescription); ?>
			</div>
		</fieldset>

		<div class="row-fluid">
			<textarea id="inputDescription_data" name="inputDescription" style="display:none"></textarea>
			<fieldset class="span6" style="margin-left:0; clear:both">
				<legend><h5><?=lang('input')?></h5></legend>
				<div class="well" id="inputDescription" contenteditable="true">
					<?php if (isset($inputDescription)) echo nl2br($inputDescription); ?>
				</div>
			</fieldset>

			<textarea id="outputDescription_data" name="outputDescription" style="display:none"></textarea>
			<fieldset class="span6">
				<legend><h5><?=lang('output')?></h5></legend>
				<div class="well" id="outputDescription" contenteditable="true">
					<?php if (isset($outputDescription)) echo nl2br($outputDescription); ?>
				</div>
			</fieldset>
		</div>

		<div class="row-fluid">
			<textarea id="inputSample_data" name="inputSample" style="display:none"></textarea>
			<fieldset class="span6" style="margin-left:0">
				<legend><h5><?=lang('sample_input')?></h5></legend>
				<div class="well" id="inputSample" contenteditable="true">
					<?php if (isset($inputSample)) echo nl2br($inputSample); ?>
				</div>
			</fieldset>

			<textarea id="outputSample_data" name="outputSample" style="display:none"></textarea>
			<fieldset class="span6">
				<legend><h5><?=lang('sample_output')?></h5></legend>
				<div class="well" id="outputSample" contenteditable="true">
					<?php if (isset($outputSample)) echo nl2br($outputSample); ?>
				</div>
			</fieldset>
		</div>

		<textarea id="dataConstraint_data" name="dataConstraint" style="display:none"></textarea>
		<fieldset>
			<legend><h5><?=lang('data_constraint')?></h5></legend>
			<div class="well" id="dataConstraint" contenteditable="true">
				<?php if (isset($dataConstraint)) echo nl2br($dataConstraint); ?>
			</div>
		</fieldset>

		<textarea id="hint_data" name="hint" style="display:none"></textarea>
		<fieldset>
			<legend><h5><?=lang('hint')?></h5></legend>
			<div class="well" id="hint" contenteditable="true">
				<?php if (isset($hint)) echo nl2br($hint); ?>
			</div>
		</fieldset>

		<div class="row-fluid">
			<fieldset class="span6">
				<legend><h5><?=lang('Problemset_source')?></h5></legend>
				<div class="controls controls-row">
					<input type="text" id="source" name="source" style="width:100%" value="<?=set_value('source', isset($source) ? $source : '', false)?>"/>
				</div>
			</fieldset>
			<?php if ($copyright): ?>
				<fieldset class="span6">
					<legend><h5><?=lang('copyright')?></h5></legend>
						<label class="checkbox alert">
							<input type="checkbox" id="acknowledged" style="height:25px; width:25px; margin:0px 10px"/>
							<div style="margin-left:45px"><strong>我同意以下内容：</strong><span id="confirmation"></span></div>
						</label>
						<script>
							$("#confirmation").load("static/copyright/<?=$copyright?>/confirmation.html");
							$("#acknowledged").change(function() {
								if ($("#acknowledged").is(":checked"))
									$("#submit-button").removeAttr('disabled');
								else
									$("#submit-button").attr('disabled', true);
							});
							$("#submit-button").attr('disabled', true);
						</script>
				</fieldset>
			<?php endif; ?>
		</div>

		<div>
			<button id="submit-button" type="submit" class="btn btn-primary pull-right" onclick="return add_problem()"><?=lang('save')?></button>
		</div>
		
		<script type="text/javascript">
			$(document).ready(function(){
				$('#editor_help').tooltip({placement: 'bottom'});
			});
		
			CKEDITOR.disableAutoInline = true;
			CKEDITOR.config.forcePasteAsPlainText = true;
			CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
			CKEDITOR.config.shiftEnterMode = CKEDITOR.ENTER_BR;
			CKEDITOR.on( 'instanceReady', function( ev ) {
				ev.editor.dataProcessor.writer.lineBreakChars = '';
			});
			var td = CKEDITOR.inline('title');
			CKEDITOR.instances.title.setMode('source');
			CKEDITOR.instances.title.blockless = true;
			var pd = CKEDITOR.inline('problemDescription');
			var id = CKEDITOR.inline('inputDescription');
			var od = CKEDITOR.inline('outputDescription');
			var sid = CKEDITOR.inline('inputSample');
			var sod = CKEDITOR.inline('outputSample');
			var dc = CKEDITOR.inline('dataConstraint');
			var hint = CKEDITOR.inline('hint');
 			//CKFinder.setupCKEditor(null, "application/third_party/ckfinder/");
			
			function add_problem(){
				$('#title_data').val(td.getData());
				$('#problemDescription_data').val(pd.getData());
				$('#inputDescription_data').val(id.getData());
				$('#outputDescription_data').val(od.getData());
				$('#inputSample_data').val(sid.getData());
				$('#outputSample_data').val(sod.getData());
				$('#dataConstraint_data').val(dc.getData());
				$('#hint_data').val(hint.getData());

				$('#addproblem').ajaxSubmit({
					success: function(responseText, stautsText){
						status = responseText.substr(0, 7);
						responseText = responseText.substr(7);
						if (status == 'success'){
							if (responseText == '') load_page('admin/problemset');
							else load_page('admin/edit_tags/' + responseText);
						} else $('#page_content').html(responseText);
					}
				});
				
				return false;
			}
		</script>
	</form>
</div>
