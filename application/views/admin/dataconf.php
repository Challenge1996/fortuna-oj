<div id='backdrop' class='modal-backdrop'></div>

<link href="css/jquery.fileupload-ui.css" rel="stylesheet">
<link href="application/third_party/codemirror/lib/codemirror.css" rel="stylesheet">
<link rel="stylesheet" href="application/third_party/codemirror/theme/neat.css" />

<script src="js/dataconf.js" type="text/javascript"></script>

<?="<center><h3>$pid . $title <sub>(Data Configuration)</sub></h3></center>";?>

<?=validation_errors()?>
<?php if (isset($errmsg) && $errmsg) { $errmsg=nl2br(htmlentities($errmsg)); echo "<div class='alert'>$errmsg</div>"; } ?>

<script type="text/javascript"> pid=<?=$pid?>; </script>
	
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

<p class="alert-error">For Output Only problem, if there are additional files, please compress them as data.zip and upload with testdata if you don't use script.</p>

<fieldset class="span5" id="data_config">
<legend>Data Configuration</legend>
<form id="data_configuration" class="form-horizontal">
	<div class="control-group">
		<label for="IOMode" class="control-label">IO Mode</label>
		<div class="controls">
			<select id="IOMode" name="IOMode">	
				<option value="0" <?=set_select('IOMode', '0', TRUE)?> >Standard IO</option>
				<option value="1" <?=set_select('IOMode', '1')?> >File IO</option>
				<option value="2" <?=set_select('IOMode', '2')?> >Output Only</option>
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
		
		<label for="user_input" class="user_input control-label">User Input</label>
		<div class="controls">
			<input type="text" id="user_input" class="user_input" name="user_input">
		</div>
		
		<label for="user_output" class="user_output label_user_output control-label">User Output</label>
		<div class="controls">
			<input type="text" id="user_output" name="user_output">
		</div>
		
		<label for="spj" class="control-label">Special Judge</label>
		<div class="controls">
			<input type="checkbox" id="spj" name="spj" />
		</div>
		<div class="clearfix"></div>
		
		<label for="spjMode" class="spjMode control-label">Special Judge Mode</label>
		<div class="controls">
			<select id="spjMode" class="spjMode" name="spjMode">
				<option value="0">Default</option>
				<option value="1">Cena</option>
				<option value="2">Tsinsen</option>
				<option value="3">HustOJ</option>
				<option value="4">Arbiter</option>
			</select>
		</div>
		
		<label for="spjFile" class="spjFile control-label">Special Judge Filename</label>
		<div class="controls">
			<input type="text" class="spjFile" id="spjFile" name="spjFile" />
		</div>
	</div>
	
</form>
</fieldset>

<fieldset class="span5" id="data_identify">
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

<div><button class="btn btn-primary offset5" type="submit" id="submit">Submit</button></div>

<ul class="nav nav-tabs">
	<li id="nav-form-li" class="fgsnav active"><a id="nav-form-a" href="#">Case Form</a></li>
	<li id="nav-group-li" class="fgsnav"><a id="nav-group-a" href="#">Data Grouping</a></li>
	<li id="nav-script-li" class="fgsnav"><a id="nav-script-a" href="#">Script</a></li>
</ul>
<div id="div-form" class="fgsnav">
	<div><button class="btn btn-info pull-left" id="addcase">Add case</button></div>
	<div class="clearfix"></div>
	<div id="data" style="margin-top:10px"></div>
</div>
<div id="div-group" class="fgsnav" style="display:none">
	<div class="alert">Counted from 0</div>
	<div id="group"></div>
	<div><span class="btn btn-info pull-left" id="addgroup">Add</span></div>
</div>
<div id="div-script" class="fgsnav" style="display:none">
	<div class="alert alert-info">
		<strong>Heads up!</strong> You should also set <i>Data Grouping</i> when you write the script.
	</div>
	<form method="post" class="form-inline" id="submit-script" action="index.php/admin/dataconf/<?=$pid?>">
		<input type="hidden" id="traditional" name="traditional" value='<?=$traditional?>' />
		<input type="hidden" id="submit-group" name="group" value='<?=$group?>' />
		<div class="well textarea" style="padding:0">
			<label for="editor-init"> <strong>Initialization Part</strong> </label>
			<textarea id="editor-init" class="span12" rows="22"><?=$init?></textarea>
			<textarea id="submit-init" name="script-init" class="span12" rows="22" style='display:none'></textarea>
		</div>
		<div class="well textarea" style="padding:0">
			<label for="editor-run"> <strong>Running Part</strong> </label>
			<textarea id="editor-run" class="span12" rows="22"><?=$run?></textarea>
			<textarea id="submit-run" name="script-run" class="span12" rows="22" style='display:none'></textarea>
		</div>
		<span class="btn btn-primary pull-right" onclick='$("#submit").click()'>Submit</span>
		<span id="btn-unlock" class="btn btn-danger pull-right"><i class="icon-pencil"></i>Unlock and Edit</span>
		<span id="btn-discard" class="btn btn-danger pull-right" style="display:none"><i class="icon-remove"></i>Discard Change</span>
	</form>
</div>

