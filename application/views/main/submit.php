<?php $this->load->model('contests'); ?>

<script src="application/third_party/codemirror/lib/codemirror.js" charset="utf-8"></script>
<link rel="stylesheet" href="application/third_party/codemirror/lib/codemirror.css" />
<link rel="stylesheet" href="application/third_party/codemirror/theme/neat.css" />
<script src="application/third_party/codemirror/mode/clike/clike.js" charset="utf-8"></script>
<script src="application/third_party/codemirror/mode/pascal/pascal.js" charset="utf-8"></script>
<script src="application/third_party/codemirror/mode/python/python.js" charset="utf-8"></script>
<script type='text/javascript'>
	var editor = new Object(), fileIO = new Object();

	function language_on_change(who)
	{
		switch ($(".language[data-file='"+who+"']").val())
		{
			case "c" :
				editor[who].setOption("mode", "text/x-csrc");
				break;
			case "c++" :
			case "c++11" :
				editor[who].setOption("mode", "text/x-c++src");
				break;
			case "pascal" :
				editor[who].setOption("mode", "text/x-pascal");
				break;
			case "txt" :
				editor[who].setOption("mode", "text/text-plain");
		}
	}
</script>

<?=validation_errors();?>

<form method='post' id='submit-form' class='form-inline'>

	<div>
		<label for="pid">Problem ID</label>
		<input name="pid" id="pid" type="text" class="input-mini" value="<?=$pid?>" readonly />
		<label for="cid">Contest ID</label>
		<input name="cid" id="cid" type="text" class="input-mini" value="<?=isset($cid) ? $cid : ''?>" readonly />
		<label for="tid">Task ID</label>
		<input name="tid" id="tid" type="text" class="input-mini" value="<?=isset($tid) ? $tid : ''?>" readonly />
		<input name="gid" id="gid" type="hidden" class="input-mini" value="<?=isset($gid) ? $gid : ''?>" />
		<input name='cookie-language' id='cookie-lang' type='hidden' value='<?=$language?>' />
	</div>

	<?php foreach ($toSubmit as $name => $property): ?>
		<br />
		<div class='one-file'>
			<?php if ($property->fileIO) echo "<script> fileIO['$name']=true; </script>"; ?>
			<div><strong> File <?=$name?>:  
				<span data-file='<?=$name?>' class='use-textarea btn btn-link active'>paste it in a text area</span> or
				<span data-file='<?=$name?>' class='use-upload btn btn-link'>upload it in a file</span> Format: 
				<select data-file='<?=$name?>' name='language[<?=$name?>]' class='language input-small' onchange='language_on_change("<?=$name?>")'>
					<?php foreach ($property->language as $allowed): ?>
						<option value='<?=$allowed?>' <?php if ($language==$allowed) echo 'selected';?> ><?=$allowed?></option>
					<?php endforeach; ?>
				</select>
			</strong></div>
			<div class='textarea-part'>
				<div class='well textarea' style='padding:0'>
					<textarea data-file='<?=$name?>' class="source-editor span12" rows="22"></textarea>
					<textarea data-file='<?=$name?>' class="submit-editor span12" rows="22" name="texteditor[<?=$name?>]" style='display:none'></textarea>
					<script type='text/javascript'>
						editor["<?=$name?>"] = CodeMirror.fromTextArea($(".source-editor[data-file='<?=$name?>'").get(0), {
							lineNumbers: true,
							theme: "neat",
							indentUnit: 4,
							smartIndent: true,
							tabSize: 4,
							indentWithTabs: true,
							autofocus: true
						});
						language_on_change("<?=$name?>");
						setTimeout(function(){editor["<?=$name?>"].refresh();},100);
					</script>
				</div>
				<div class='form-action'>
					<label>
						<input data-file='<?=$name?>' type="checkbox" value="codemirror" class="toggle_editor" checked />
						Toggle CodeMirror
					</label>
				</div>
			</div>
			<div class='upload-part' style='display:none'>
				<input type='file' name="file[<?=$name?>]"></input>
			</div>
		</div>
	<?php endforeach; ?>

	<span type="submit" class="btn btn-primary pull-right" id="submit_button" onclick='return check_on_submit()'>Submit</span>
</form>

<div id='est-modal' class='modal hide fade'>
	<div class='modal-header'>
		<h3>Expected Score?</h3>
	</div>
	<div class='modal-body'>
		<input id='est-input' type='number'></input>
	</div>
	<div class='modal-footer'>
		<span id='est-submit' class='btn btn-primary'>OK</span>
	</div>
</div>
	
<script type="text/javascript">

	function final_submit()
	{
		$('#est-modal').modal('hide');
		$('#submit-form').ajaxSubmit({
			url: 'index.php/main/submit/' + $('#pid').val(),
			success: function(responseText, statusText){
				if (responseText == 'success') {
					if ($('#cid').val() != '') load_page('contest/status/' + $('#cid').val());
					else load_page('main/status');
				} else {
					alert('Failed to submit!');
					location.reload();
				}
			},
			error: function() {
				alert('Failed to submit!');
				location.reload();
			}
		});
	}


	$(document).ready(function(){
		<?php if (isset($cid) && $cid): ?>
			$('#est-submit').click(function(){
				$.ajax({ url:"index.php/contest/estimate/<?=$cid?>/<?=$pid?>/"+$("#est-input").val(), success: final_submit});
			});
		<?php endif; ?>

		$(".CodeMirror-linenumbers").width(28);
		
		$('.toggle_editor').change(function (){
			var root = $(this).parents('.one-file');
			var cm = root.find('.CodeMirror');
			var ta = root.find('.submit-editor');
			var e = editor[$(this).attr('data-file')];
			if ($(this).attr("checked")){
				cm.show(); ta.hide();
				e.setValue($(".submit-editor[data-file='"+$(this).attr('data-file')+"']").val());
			}else{
				cm.hide(); ta.show();
				ta.val(e.getValue());
			}
		});

		$('.use-textarea').click(function(){
			var root = $(this).parents('.one-file');
			$(this).addClass('active');
			root.find('.use-upload').removeClass('active');
			root.find('.textarea-part').show();
			root.find('.upload-part').hide();
		});

		$('.use-upload').click(function(){
			var root = $(this).parents('.one-file');
			$(this).addClass('active');
			root.find('.use-textarea').removeClass('active');
			root.find('.textarea-part').hide();
			root.find('.upload-part').show();
		});
	});
	
	function check_on_submit(){
		for (file in editor)
			if ($(".toggle_editor[data-file='"+file+"']").attr('checked'))
				$(".submit-editor[data-file='"+file+"']").val(editor[file].getValue());
		if ($('#pid').val() == ''){
			alert("You should specify the Problem ID!");
			return false;
		}
		try
		{
			$(".submit-editor").each(function(){
				if ($(this).parents('.textarea-part').css('display')=='none') return true;
				var name = $(this).attr('data-file');
				var lang = $(this).parents('.one-file').find('.language');
				var str = $(this).val();
				if (!str.length && !confirm("My friend, are you surely going to submit an empty '"+name+"'?"))
					throw 'err';
				if ((lang=='c' || lang=='c++' || lang=='c++11') && str.indexOf('%I64d')!=-1)
					if (!confirm("On Linux, you are not supposed to use '%I64d' specifier. Ignore this warning?"))
						throw 'err';
				if (!fileIO[name])
				{
					if ((lang=='c' || lang=='c++' || lang=='c++11') && (str.indexOf('freopen')!=-1 || str.indexOf('FILE')!=-1 || str.indexOf('fstream')!=-1)
						|| lang=='pascal' && str.toLowerCase.indexOf('assign')!=-1)
						if (!confirm("Please confirm you are NOT using FILE IO. Sure to submit?"))  throw 'err';
				} else
						{
							if ((lang=='c' || lang=='c++' || lang=='c++11') && str.indexOf('freopen')==-1 && str.indexOf('FILE')==-1 && str.indexOf('fstream')==-1
								|| lang=='pascal' && str.toLowerCase.indexOf('assign')==-1)
								if (!confirm("Please confirm you are NOT using FILE IO. Sure to submit?")) throw 'err';
						}
			});
		} catch (err) { return false; }
		$('#submit_button').attr('disabled','true');
		$('.textarea-part:hidden,.upload-part:hidden').remove();

		var new_lang = undefined;
		$(".language").each(function(){
			if (new_lang === undefined && $(this).val()!='txt') new_lang = $(this).val();
			if (new_lang != $(this).val()) new_lang = null;
		});
		if (new_lang) $("#cookie-lang").val(new_lang);

		<?php if (isset($cid) && $cid && $this->contests->load_contest_mode($cid)=='OI Traditional'): ?>
			$('#est-modal').modal('show');
		<?php else: ?>
			final_submit();
		<?php endif; ?>
		
		return false;
	}

</script>
<!--  End of file submit.php -->
