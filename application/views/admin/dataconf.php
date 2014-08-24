<link href="css/jquery.fileupload-ui.css" rel="stylesheet">

<?="<center><h3>$pid . $title <sub>(Data Configuration)</sub></h3></center>";?>

<?php $dataconf = json_decode($data); ?>

<?=validation_errors()?>

<form id="form_file_upload" class="form-horizontal" enctype="multipart/form-data">
	<div class="control-group">
		<label for="file_upload" class="control-label">
			Upload Data
			<i id="file_upload_tips" class="icon-info-sign" title="File will be auto compiled if has c/cpp/pas/dpr extension."></i>
		</label>
		<div class="controls">
			<div class="fileupload-buttonbar">
				<span class="fileinput-button btn btn-small btn-success">
					<i class="icon-plus icon-white"></i>Add
					<input type="file" id="file_upload" name="files[]" data-url="index.php/admin/upload/<?=$pid?>" multiple />
				</span>
				<input type="submit" id="btn_start" class="btn btn-small btn-primary" value="Start" />
				<input type="reset" id="btn_clear" class="btn btn-small btn-danger" value="Clear" />
				
				<button id="wipe" class="btn btn-small btn-danger pull-right">Wipe All Data From Server</button>
				
				<div style="display:none" class="progress progress-info progress-striped">
					<div id="div_progress" class="bar" style="width:10%"></div>
				</div>
			</div>
			
			<div id="files" style="margin-top:5px"></div>
			<div id="div_upload_controls"></div>
		</div>
	</div>
</form>

<p class="alert-error">For Output Only problem, if there are additional files, please compress them as data.zip and upload with testdata.</p>

<fieldset class="span5" style="display:none" id="data_config">
<legend>Data Configuration</legend>
<form id="data_configuration" class="form-horizontal" action="index.php/admin/dataconf/<?=$pid?>">
	<input type="hidden" name="pid" value="<?=$pid?>" />
	
	<div class="control-group">
		<label for="IOMode" class="control-label">IO Mode</label>
		<div class="controls">
			<select id="IOMode" name="IOMode">	
				<option value="0" <?=set_select('IOMode', '0', TRUE)?> >Standard IO</option>
				<option value="1" <?=set_select('IOMode', '1')?> >File IO</option>
				<option value="2" <?=set_select('IOMode', '2')?> >Output Only</option>
				<option value="3" <?=set_select('IOMode', '3')?> >Interactive</option>
			</select>
		</div>
		
		<label for="overall_score" class="control-label">Overall Score</label>
		<div class="controls">
			<input type="text" id="overall_score" min="0" name="overall_score">
		</div>
		
		<label for="overall_time" class="time control-label">Overall Time Limit (ms)</label>
		<div class="controls">
			<input type="number" id="overall_time" class="time" min="0" name="overall_time">
		</div>
		
		<label for="overall_memory" class="memory control-label">Overall Mem Limit (KB)</label>
		<div class="controls">
			<input type="number" id="overall_memory" class="memory" min="0" name="overall_memory">
		</div>
		<?
			$input_filename = 'data.in';
			$output_filename = 'data.out';
			if (isset($dataconf->cases)) {
				if (isset($dataconf->cases[0]->tests)) {
					$input_filename = $dataconf->cases[0]->tests[0]->userInput;
					$output_filename = $dataconf->cases[0]->tests[0]->userOutput;
				}
			}
		?>
		
		<label for="user_input" class="user_input control-label">User Input</label>
		<div class="controls">
			<input type="text" id="user_input" class="user_input" name="user_input" value="<?=$input_filename?>">
		</div>
		
		<label for="user_output" class="user_output label_user_output control-label">User Output</label>
		<div class="controls">
			<input type="text" id="user_output" name="user_output" value="<?=$output_filename?>">
		</div>
		
		<label for="spj" class="control-label">Special Judge</label>
		<div class="controls">
			<input type="checkbox" id="spj" name="spj" <?=isset($dataconf->spjMode) ? 'checked' : '';?> >	
		</div>
		<div class="clearfix"></div>
		
		<label for="spjMode" class="spjMode control-label">Special Judge Mode</label>
		<div class="controls">
			<select id="spjMode" class="spjMode" name="spjMode">
				<option value="0" <?=(isset($dataconf->spjMode) && $dataconf->spjMode == 0) ? 'selected' : '';?>>Default</option>
				<option value="1" <?=(isset($dataconf->spjMode) && $dataconf->spjMode == 1) ? 'selected' : '';?>>Cena</option>
				<option value="2" <?=(isset($dataconf->spjMode) && $dataconf->spjMode == 2) ? 'selected' : '';?>>Tsinsen</option>
				<option value="3" <?=(isset($dataconf->spjMode) && $dataconf->spjMode == 3) ? 'selected' : '';?>>HustOJ</option>
				<option value="4" <?=(isset($dataconf->spjMode) && $dataconf->spjMode == 4) ? 'selected' : '';?>>Arbiter</option>
			</select>
		</div>
		
		<label for="spjFile" class="spjFile control-label">Special Judge Filename</label>
		<div class="controls">
			<input type="text" class="spjFile" id="spjFile" name="spjFile" />
		</div>
		
		<label for="framework" class="framework control-label">Framework Code</label>
		<div class="controls">
			<textarea rows="7" class="framework span8" name="framework" id="framework"></textarea>
		</div>
	</div>
	
	

	<input type="hidden" name="tcnt" id="tcnt" />
	<button class="btn btn-primary pull-right" type="submit" id="submit">Submit</button>
</form>
</fieldset>

<fieldset class="span5">
<legend>Data Identification</legend>
<form id="data_identification" class="form-horizontal" action="index.php/admin/scan/<?=$pid?>">
	<p class="alert-error">Custom Match: Use * for variables, eg. data*.in</p>
	
	<div class="control-group">
		<label for="input_file" class="control-label">Input File Pattern</label>
		<div class="controls">
			<input type="text" id="input_file" min="0" name="input_file">
		</div>
		
		<label for="output_file" class="control-label">Output File Pattern</label>
		<div class="controls">
			<input type="text" id="output_file" min="0" name="output_file">
		</div>
	</div>
	
	<button id="btn_scan" class="btn btn-small btn-primary pull-right" onclick="return false;">Scan Server</button>
</form>
</fieldset>

<div class="clearfix"></div>
<hr style="height:1px;border:none;border-top:1px dashed #0066CC"/>
<div>
	<button class="btn btn-info pull-left" id="addcase">Add case</button>
</div>
<div class="clearfix"></div>
<div id="data" style="margin-top:10px"></div>

<script type="text/javascript">
	var signal = 0
	function loaded() {
		signal++
		if (signal == 4) {
			var fileId = 0;
			$("#file_upload").fileupload({
				dataType: 'json',
				add: function(e, data) {
					$.each(data.files, function(index, file) {
						file.context = $('<p class="alert alert-info file_' + (++fileId).toString() + '"><strong>' + file.name + '</strong></p> ')
							.appendTo("#files");

						$('<button class="close" id=close_file_'+fileId.toString()+' style="float:none">&times</button>')
							.appendTo(".file_" + fileId.toString());
						$("#close_file_"+fileId.toString()).click(function() {
							$(this).parent().remove();
						});

						$('<button style="display:none" class="btn_upload file_' + fileId.toString() + '"></button>')
							.appendTo("#div_upload_controls").click(function() {
								$(this).remove();
								data.submit();
							});
					})
				},
				progressall: function(e, data) {
					var progress = parseInt(data.loaded / data.total * 100, 10);
					$("#div_progress").css('width', progress + '%');
					if (data.loaded == data.total){
						$(".progress").css('display', 'none');
						$("#btn_scan").click();
					}
				},
				done: function(e, data) {
					$.each(data.files, function(index, file) {
						file.context.html('Uploaded');
						file.context.removeClass('alert-info');
						file.context.addClass('alert-success');
						file.context.fadeOut(1500);
					});
				}
			})
			
			$("#btn_start").click(function() {
				$(".btn_upload").click()
				$(".progress").css('display', 'block');
				return false;
			})
			
			$("#btn_clear").click(function() {
				$("#files").html('');
				$("#div_upload_controls").html('');
				$(".progress").css('display', 'block');
			})

			$("#addcase").click(function(){
				add_case();
				return false;
			})
			
			$(".case_close").live('click', remove_case)
			
			$("#btn_scan").click(function() {
				$('#data_identification').ajaxSubmit({
					type: 'POST',
					success: function(data){
						$('#data').html('');
						data = eval("(" + data + ")");
						if (data != null && data != '') initialize(data);
						$("#IOMode").trigger("change");
					}
				});
				return false;
			})
			
			$("#overall_time").change(function(){
				$(".time").val($(this).val());
			})
			
			$("#overall_memory").change(function(){
				$(".memory").val($(this).val());
			})
			
			$("#overall_score").change(function(){
				$(".score").val($(this).val());
			})
			
			$("#user_output").change(function(){
				if ($("#IOMode").val() == 2) {
					var outfile = $(this).val();
					$(".datatest").each(function() {
						var id = $(this).attr("id") - 1000000000 + 1;
						var user_output = outfile.replace(/[*]/, id.toString());
						$(this).children(".user_output").children(".user_output").val(user_output);
					});
				}
			})
			
			$("#IOMode").change(function(){
				if ($(this).val() == 1) {
					$(".user_input").show();
				} else {
					$(".user_input").hide();
				}
				
				$(".user_output").attr('disabled', '');
				$(".user_output").hide();
				if ($(this).val() == 1 || $(this).val() == 2) {
					$(".label_user_output").show();
					$("#user_output").show();
					if ($(this).val() == 2) {
						$(".user_output").removeAttr('disabled');
						$(".user_output").show();
					} else {
						$("#user_output").removeAttr('disabled');
					}
				} else {
					$("#user_output").hide();
					$("#user_output").attr('disabled');
				}
				
				if ($(this).val() == 2) {
					$(".time, .memory").attr("disabled", '');
					$(".time, .memory").hide();
				} else {
					$(".time, .memory").removeAttr("disabled");
					$(".time, .memory").show();
				}
				
				if ($(this).val() == 3) $(".framework").show();
				else $(".framework").hide();
			})
			
			$("#spj").change(function(){
				if ($(this).attr("checked")){
					$(".spjMode, .spjFile").show();
				}else{
					$(".spjMode, .spjFile").hide();
				}
			})
			
			$("#submit").click(function(){
				count = 0;
				valid = true;
				
				$('.datacase').each(function(){
					tests = $(this).find('.datatest');
					
					if (tests.is('div')){
						if ($(this).find('.score').val() == ''){
							alert("Configuration not valid!");
							return valid = false;
						}
						
						tests.each(function(){
							if ($("#IOMode").val() != 2) {
								if ($(this).find('.time').val() == '' || $(this).find('.memory').val() == ''){
									alert("Configuration not valid!");
									return valid_test = valid = false;
								}
							}
							$(this).children('.case_no').val(count);
						});
						
						if ( ! valid) return false;
						count++;
					} else $(this).remove();
				});
				
				if ( ! valid) return false;
				$('#tcnt').val(test_cnt - 1000000000);
				$('#data_configuration').append($('#data').detach());
				
				$('#data_configuration').ajaxSubmit({
					type: 'post',
					success: function(responseText, stautsText){
						if (responseText == 'success') window.location.hash = 'admin/problemset';
						else $('#page_content').html(responseText);
					}
				});
				
				return false;
			})

			function initialize(data){
				$('#framework').val(data.framework);
				test_cnt = 1000000000;
				for (var i in data.cases){
					case_id = add_case();
					current_case = $("#" + case_id);
					
					if (typeof data.cases[i].score != 'undefined') current_case.find('.score').val(data.cases[i].score);
					for (var j in data.cases[i].tests){
						test_id = add_test(current_case);
						current_test = $("#" + test_id);
						
						current_test.find('.in').val(data.cases[i].tests[j].input);
						current_test.find('.out').val(data.cases[i].tests[j].output);
						if (typeof data.cases[i].tests[j].userOutput != 'undefined')
							current_test.find('.user_output').val(data.cases[i].tests[j].userOutput);
						if (typeof data.cases[i].tests[j].timeLimit != 'undefined') 
							current_test.find('.time').val(data.cases[i].tests[j].timeLimit);
						if (typeof data.cases[i].tests[j].memoryLimit != 'undefined')
							current_test.find('.memory').val(data.cases[i].tests[j].memoryLimit);
					}
				}
				$(".testcase").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all");
			}

			function add_test(current_case){
				var id = test_cnt++;
				current_case.children(".holder").append("<div class='datatest well' style='padding:3px' id='" + id + "'> \
					<label>Input File <input readonly type='text' name='infile[" + id + "]' class='in input-small pull-right'></label> \
					<div class='clearfix'></div> \
					<label>Answer File <input readonly type='text' name='outfile[" + id + "]' class='out input-small pull-right'></label> \
					<div class='clearfix'></div> \
					<label class='user_output'>Output File <input type='text' name='user_output[" + id + "]' class='user_output input-small pull-right'></label> \
					<div class='clearfix'></div> \
					<label class='time'>Time Limit (ms) <input class='time input-small pull-right' type='number' min='0' name='time[" + id + "]'></label> \
					<div class='clearfix'></div> \
					<label class='memory'>Mem Limit (KB) <input class='memory input-small pull-right' type='number' min='0' name='memory[" + id + "]'></label> \
					<input name='case_no[" + id + "]' class='case_no' type='hidden'/><div class='clearfix'></div></div>");
					
				return id.toString();
			}

			function add_case(){
				var id = case_cnt++;
				$('#data').append("<div class='datacase well' id='" + id + "' style='padding-bottom:0'> \
					<label class='pull-left'>Score <input class='score input-mini' type='text' name='score[]' /></label> \
					<button class='close case_close'>&times;</button><div class='clearfix'></div> \
					<div class='holder'></div></div>");

				$('.holder').sortable({connectWith: '.holder'});

				return id.toString();
			}

			function remove_case(){
				$(this).parent().fadeOut("normal", function(){$(this).remove();});
				return false;
			} 
			
			$("#wipe").click(function(){
				access_page("admin/wipedata/<?=$pid?>", void 0, false);
				return false;
			})
			
			var pid=<?=$pid?>, case_cnt = 0, test_cnt = 1000000000;
			var data = <?=$data?>;
			initialize(data);
			
			$("#IOMode").val(data.IOMode);
			$("#IOMode").trigger("change");
			
			if (data.spjMode){
				$("#spj").attr("checked", true);
				$("#spj").val(data.spjMode);
				$("#spjFile").val(data.spjFile);
			}else $("#spj").removeAttr("checked");
			
			$("#spj").trigger("change");
		}
		
		$("#data_config").show();
	}
	
	loadJsFile("jquery-ui", "js/jquery-ui.js", loaded);
	loadJsFile("jquery.ui.widget", "js/jquery.ui.widget.js", loaded);
	loadJsFile("jquery.iframe-transport", "js/jquery.iframe-transport.js", loaded);
	loadJsFile("jquery.fileupload", "js/jquery.fileupload.js", loaded);
</script>
