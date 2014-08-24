<?=validation_errors();?>

<form method="post" class="form-inline" id="submit_code" onsubmit="return check_on_submit()">
	<div class="submit_control">
		<label for="language">Language</label>
		<select name="language" id="language" class="language" onchange="language_on_change();">
			<option value="C"<?php if ($language=="C") echo ' selected';?> >C</option>
			<option value="C++"<?php if ($language=="C++") echo ' selected';?> >C++</option>
			<option value="C++11"<?php if ($language=="C++11") echo ' selected';?> >C++11(0x)</option>
			<option value="Pascal"<?php if ($language=="Pascal") echo ' selected';?> >Pascal</option>
<!-- 			<option value="Java"<?php if ($language=="Java") echo ' selected';?> >Java</option> -->
<!-- 			<option value="Python"<?php if ($language=="Python") echo ' selected';?> >Python</option> -->
		</select>
		<label for="pid">Problem ID</label>
		<input name="pid" id="pid" type="text" class="input-mini" value="<?=$pid?>" readonly />
		<label for="cid">Contest ID</label>
		<input name="cid" id="cid" type="text" class="input-mini" value="<?=isset($cid) ? $cid : ''?>" readonly />
		<label for="tid">Task ID</label>
		<input name="tid" id="tid" type="text" class="input-mini" value="<?=isset($tid) ? $tid : ''?>" readonly />
		<input name="gid" id="gid" type="hidden" class="input-mini" value="<?=isset($gid) ? $gid : ''?>" />
	</div>

	<div class="well textarea" style="padding:0">
		<textarea id="texteditor" name="texteditor" class="span12" rows="22"></textarea>
 		<div>
 			<script src="application/third_party/codemirror/lib/codemirror.js" charset="utf-8"></script>
			<link rel="stylesheet" href="application/third_party/codemirror/lib/codemirror.css" />
 			<link rel="stylesheet" href="application/third_party/codemirror/theme/neat.css" />
 			<script src="application/third_party/codemirror/mode/clike/clike.js" charset="utf-8"></script>
 			<script src="application/third_party/codemirror/mode/pascal/pascal.js" charset="utf-8"></script>
			<script src="application/third_party/codemirror/mode/python/python.js" charset="utf-8"></script>
			<script type="text/javascript">
				var editor = CodeMirror.fromTextArea($("#texteditor").get(0), {
 						mode: "<?php if ($language=='Pascal') echo "text/x-pascal"; else echo "text/x-c++src";?>",
 						lineNumbers: true,
						theme: "neat",
 						indentUnit: 4,
 						smartIndent: true,
 						tabSize: 4,
 						indentWithTabs: true,
 						autofocus: true
					}
				);
			</script>
		</div> 
	</div>
	
	<div class="form-action">
		<input type="checkbox" value="codemirror" id="toggle_editor" name="toggle_editor" checked />
		<label for="editor">Toggle CodeMirror</label>
		<button type="submit" class="btn btn-primary pull-right" id="submit_button">Submit</button>
	</div>
</form>
	
	
<script type="text/javascript">
	$(document).ready(function(){
		$('#toggle_editor').change(function (){
			if ($('#toggle_editor').attr("checked")){
				$('.CodeMirror').css({"visibility" : "visible", "display" : "block"});
				$('#texteditor').css({"visibility" : "hidden", "display" : "none", "zIndex" : -10000});
				editor.setValue($('#texteditor').val());
			}else{
				$('.CodeMirror').css({"visibility" : "hidden", "display" : "none"});
				$('#texteditor').css({"visibility" : "visible", "display" : "block", "zIndex" : 10000});
				editor.save();
			}
		})
	})
	$(".CodeMirror-linenumbers").width(28);

	function language_on_change(){
		var language = $('#language').val();
		if (language == "C") editor.setOption("mode", "text/x-csrc");
		if (language == "C++" || language == "C++11") editor.setOption("mode", "text/x-c++src");
		if (language == "Pascal") editor.setOption("mode", "text/x-pascal");
		if (language == "Java") editor.setOption("mode", "text/x-java");
		if (language == "Python") editor.setOption("mode", "text/x-python");
	}

	function find_in_code(str)
	{
		return $('#texteditor').val().indexOf(str)!=-1;
	}
	
	function check_on_submit(){
		if ($('#pid').val() == ''){
			alert("You should specify the Problem ID!");
			return false;
		}
		if ($('#toggle_editor').attr("checked")) editor.save();
		if ($('#texteditor').val().length == 0){
			alert("You are attempting to submit empty code!");
			return false;
		}
		if (($('#language').val() in {'C':1, 'C++':1, 'C++11':1}) && find_in_code('%I64d')){
			alert("Please be reminded to use '%lld' specificator in C/C++ instead of '%I64d'!");
			return false;
		}
		<?php if ($IOMode==0): ?>
			if (
				($('#language').val() in {'C':1, 'C++':1, 'C++11':1}) && (find_in_code('freopen') || find_in_code('FILE') || find_in_code('fstream')) ||
				($('#language').val()=='Pascal') && $('#texteditor').val().toLowerCase().indexOf('assign')!=-1
			){
				if (!confirm("Please confirm you are NOT using FILE IO. Sure to submit?")) return false;
			}
		<?php endif ?>
		<?php if ($IOMode==1): ?>
			if (
				($('#language').val() in {'C':1, 'C++':1, 'C++11':1}) && !find_in_code('freopen') && !find_in_code('FILE') && !find_in_code('fstream') ||
				($('#language').val()=='Pascal') && $('#texteditor').val().toLowerCase().indexOf('assign')==-1
			){
				if (!confirm("Please confirm you ARE using FILE IO. Sure to submit?")) return false;
			}
		<?php endif ?>
		
		$('#submit_button').attr('disabled','true');
		$('#submit_code').ajaxSubmit({
			url: 'index.php/main/submit/' + $('#pid').val(),
			success: function(responseText, statusText){
				if (responseText == 'success') {
					if ($('#cid').val() != '') load_page('contest/status/' + $('#cid').val());
					else load_page('main/status');
				} else {
					$('#submit_button').removeAttr('disabled');
					alert('Failed to submit!');
				}
			},
			error: function() {
				$('#submit_button').removeAttr('disabled');
			}
		});
		return false;
	}

</script>
<!--  End of file submit.php -->
