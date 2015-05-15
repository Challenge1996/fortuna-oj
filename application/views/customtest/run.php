<link href="css/jquery.fileupload-ui.css" rel="stylesheet">
<h4>Run Your Code!</h4>
<hr />

<?=validation_errors();?>

<form method="post" class="form-horizontal" id="custom_run">
	<div class="span7" style="padding:0">
		<div class="well textarea" style="padding:0">
			<textarea id="texteditor" name="texteditor" class="span12" rows="22"><?=$code?></textarea>
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
			<input type="checkbox" value="codemirror" id="toggle_editor" name="toggle_editor" checked style="margin-left: 28px"/>
			<label for="editor" style="display:inline">Toggle CodeMirror</label>
		</div>
		<button type="submit" class="btn btn-primary pull-right" id="btn_run">Run</button>
	</div>
	
	<div class="span4" style="margin-left:48px">
		<div class="run_control">
			<label for="language"><strong>Language</strong>
				<select name="language" id="language" class="language" onchange="language_on_change();">
					<option value="C"<?php if ($language=="C") echo ' selected';?> >C</option>
					<option value="C++"<?php if ($language=="C++") echo ' selected';?> >C++</option>
					<option value="C++11"<?php if ($language=="C++11") echo ' selected';?> >C++11(0x)</option>
					<option value="Pascal"<?php if ($language=="Pascal") echo ' selected';?> >Pascal</option>
					<!--<option value="Java"<?php if ($language=="Java") echo ' selected';?> >Java</option>
					<option value="Python"<?php if ($language=="Python") echo ' selected';?> >Python</option>-->
				</select>
			</label>
		</div>
	</div>
	<div class="span4" style="margin-left:48px">
		<div>
			<strong>Input</strong>
			<div id="input">
				<label class='radio'>
					<input type="radio" id="use_text" name="input_method" value="use_text" onchange="change2text();" <?=$text_checked?>></input>
					<i>use textarea</i>
				</label>
				<textarea id="input_text" name="input_text" class="span12" rows="6"><?=$input?></textarea>
				<textarea id="input_file_display" class="span12" rows="6" readonly><?php echo $file_abstract ?></textarea>
				<label class='radio'>
					<input type="radio" id="use_file" name="input_method" value="use_file" onchange="change2file();" <?=$file_checked?>></input>
					<i>use file</i><br />
				</label>
				<ul>
					<li>The last file you have uploaded will be kept.</li>
					<li>Use standard IO.</li>
					<li>16M size limit.</li>
					<li><strong>Try using file when CustomTest differs from actual judgement!</strong></li>
				</ul>
			</div>
		</div>
	</div>
</form>
<div class="span4" style="margin-left:48px">
	<div>
		<div class='fileupload-buttonbar'>
			<span class="fileinput-button btn btn-primary">
				<i class="icon-plus icon-white"></i>Choose a file
				<input type="file" id="input_file" name="input_file"></input>
			</span>
			<button class='btn btn-success' id="v_button">upload</button>
		</div>
		<div style="display:none" class="progress progress-info progress-striped">
			<span id="div_progress" class="bar" style="width:0%"></span>
			<span id="file_name"></span>
		</div>

		<strong>Output</strong>
		<textarea id="output" class="span12" rows="6" readonly><?=$output?></textarea>

		<?php if (isset($status) && $status !== false): ?>
		<label>Status
			<span class="label label-info"><?=$status?></span>
		</label>
		<?php endif; ?>

		<?php if (isset($time) && $time !== false): ?>
		<label>Time
			<span class="label label-info"><?=$time?> ms</span>
		</label>
		<?php endif; ?>
		
		<?php if (isset($memory) && $memory !== false): ?>
			<label>Memory
			<span class="label label-info"><?=$memory?> KB</span>
			</label>
		<?php endif; ?>

		<?php if (isset($retcode) && $retcode !== false): ?>
			<label>Returned Code
			<span class="label label-info"><?=$retcode?></span>
			</label>
		<?php endif; ?>
	</div>
</div>
	
<script type="text/javascript">
	if ($('#use_text').attr('checked')=='checked')
		$('#input_file_display').css('display','none');
	else
		$('#input_text').css('display','none');

	var signal=0;
	function loaded(){
		if (++signal<4) return;
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
		});
			
		$("#btn_run").click(function() {
			if ($('#toggle_editor').attr("checked")) editor.save();
			if ($('#texteditor').val().length == 0 && $('#use_text').attr('checked')=='checked'){
				alert("You are attempting to run empty code!");
				return false;
			}
			if (($('#language').val() in {'C':1, 'C++':1}) && $('#texteditor').val().indexOf('%I64d') != -1){
				alert("Please be reminded to use '%lld' specificator in C/C++ instead of '%I64d'!");
				return false;
			}
			$('#custom_run').ajaxSubmit({
				url: 'index.php/customtest/run',
				success: function(responseText){
					$('#page_content').html(responseText);
				}
			});
			return false;
		});

		$("#input_file").fileupload({
			url: 'index.php/customtest/upload_input',
			add: function(e, data) {
				$.each(data.files, function(index, file) {
					if (file.size>16777216)
					{
						// 16M limit.
						alert('Your file is too large!');
						return false
					}
					$("#use_text").removeAttr("checked");
					$("#use_file").attr("checked","");
					$(".progress").css("display","block");
					$("#div_progress").css('width','0%');
					$("#file_name").html('<strong><i>'+file.name+'</i></strong>');
					$("#v_button").click(function()
					{
						$("#file_name").html('');
						data.submit();
					});
					return true;
				});
			},
			progressall: function(e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$("#div_progress").css('width', progress + '%');
			},
			done: function(e, data) {
				$("#input_file_display").val("(new file)");
				$(".progress").fadeOut(1500);
			},
			fail: function(e, data) {
				$(".progress").fadeOut(1500);
				alert("Fail to upload");
			}
		});
	}
	$(".CodeMirror-linenumbers").width(28);

	function language_on_change(){
		var language = $('#language').val();
		if (language == "C") editor.setOption("mode", "text/x-csrc");
		if (language == "C++" || language == "C++11") editor.setOption("mode", "text/x-c++src");
		if (language == "Pascal") editor.setOption("mode", "text/x-pascal");
		if (language == "Java") editor.setOption("mode", "text/x-java");
		if (language == "Python") editor.setOption("mode", "text/x-python");
	}

	function change2text()
	{
		$('#input_text').css('display','block');
		$('#input_file_display').css('display','none');
	}
	
	function change2file()
	{
		$('#input_text').css('display','none');
		$('#input_file_display').css('display','block');
	}

	loadJsFile("jquery-ui", "js/jquery-ui.js", loaded);
	loadJsFile("jquery.ui.widget", "js/jquery.ui.widget.js", loaded);
	loadJsFile("jquery.iframe-transport", "js/jquery.iframe-transport.js", loaded);
	loadJsFile("jquery.fileupload", "js/jquery.fileupload.js", loaded);
</script>
<!--  End of file run.php -->
