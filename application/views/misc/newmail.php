<h4>New Mail 
	<a title="Click empty frames to edit!" id="editor_help"><i class="icon-question-sign"></i></a>
</h4>
<hr />

<?=validation_errors()?>

<div class="newmail_form">

	<form class="form-horizontal" action="index.php/misc/newmail" method="post" id="newmail">
		
		<input type="hidden" name="title" id="title_data" />
		<fieldset>
			<legend>
				<h5>Send to:
					<input type="text" class="input-small" name='to_user' value="<?=set_value('to_user', $username, false)?>"/>
				</h5>
				<h5>Title</h5>
			</legend>
			<h3><div class="well" id="title" style="padding: 5px; text-align:center" contenteditable="true">
				<?=set_value('title', '', false)?>
			</div></h3>
		</fieldset>

		<textarea id="content_data" name="content" style="display:none"></textarea>
		<fieldset>
			<legend><h5>Content</h5></legend>
			<div class="well" id="content" contenteditable="true">
				<?=set_value('content', '', false)?>
			</div>
		</fieldset>

		<button type="submit" class="btn btn-primary pull-right" onclick="return new_mail()">Send</button>
		
		<script type="text/javascript">
			$(document).ready(function(){
				$('#editor_help').tooltip({placement: 'bottom'});
			});
		
			CKEDITOR.disableAutoInline = true;
			CKEDITOR.config.forcePasteAsPlainText = true;
			CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
			CKEDITOR.config.shiftEnterMode = CKEDITOR.ENTER_BR;
//			var td = CKEDITOR.inline('title');
//			CKEDITOR.instances.title.setMode('source');
//			CKEDITOR.instances.title.blockless = true;
			var cd = CKEDITOR.inline('content');
 			CKFinder.setupCKEditor(cd, "application/third_party/ckfinder/");
			
			function new_mail(){
				$('#title_data').val($('#title').text());
				$('#content_data').val(cd.getData());

				$('#newmail').ajaxSubmit({
					success: function(responseText, stautsText){
						if (responseText == 'success') load_page('misc/mailbox');
						else $('#page_content').html(responseText);
					}
				});
				
				return false;
			}
		</script>
	</form>
</div>
