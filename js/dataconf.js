// DATA PART BEGIN

(function() {

var use_script, cases_cnt, tests_cnt, editor_init, editor_run;

function upd_gs(data, group)
{
	//console.log(data.cases[0].tests[0].userInput);
	var res = form2script(data);
	editor_init.setValue(res["init"]);
	editor_run.setValue(res["run"]);
	init_group(group);
	$("#submit-group").val(JSON.stringify(group));
}

function clean_data(data)
{
	if (data.cases === undefined) return data;
	data.cases = data.cases.filter(function(x){
		if (x === undefined || x === null) return false;
		if (x.tests === undefined || x.tests === null) return false;
		return true;
	});
	for (var i in data.cases)
		data.cases[i].tests = data.cases[i].tests.filter(function(x){ return x !== undefined && x !== null; })
	return data;
}

function initialize(data)
{
	$("#traditional").val(data);
	data = eval('('+data+')');
	data = clean_data(data);
	var group = Array();
	$("#data").html("");
	cases_cnt = tests_cnt = 0;
	if (data && data.cases)
	{
		for (var i in data.cases)
		{
			//if (data.cases[i] === null || data.cases[i] === undefined)
			//	continue;
			case_id = add_case();
			group[case_id] = Array();
			current_case = $("#" + case_id);
			if (typeof data.cases[i].score != 'undefined')
				current_case.find('.score').val(data.cases[i].score);
			for (var j in data.cases[i].tests)
			{
				var test_id = add_test(current_case);
				group[case_id].push(test_id);
				var current_test = $("#test" + test_id);
				if (typeof data.cases[i].tests[j].input != 'undefined')
					current_test.find('.in').val(data.cases[i].tests[j].input);
				if (typeof data.cases[i].tests[j].output != 'undefined')
					current_test.find('.out').val(data.cases[i].tests[j].output);
				if (typeof data.cases[i].tests[j].userInput != 'undefined')
					current_test.find('.user_input').val(data.cases[i].tests[j].userInput);
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
	if (data && data.IOMode) $("#IOMode").val(data.IOMode);
	upd_IOmode();
	if (data)
	{
		if (data.spjMode)
		{
			$("#spj").attr("checked", true);
			$("#spjMode").val(data.spjMode);
			$("#spjFile").val(data.spjFile);
		} else
			$("#spj").removeAttr("checked");
		if ((data.IOMode == 0 || data.IOMode == 1) && data.cases)
		{
			if (data.cases[0] && data.cases[0].tests[0] && data.cases[0].tests[0].userInput)
				$("#user_input").val(data.cases[0].tests[0].userInput);
			if (data.cases[0] && data.cases[0].tests[0] && data.cases[0].tests[0].userOutput)
				$("#user_output").val(data.cases[0].tests[0].userOutput);
		}
	}
	upd_spj();
	upd_gs(data, group);
}

function getDataFromElement()
{
	var data = {}, group = Array();
	data.IOMode = Number($("#IOMode").val());
	data.cases = Array();
	for (var i=0; i<cases_cnt; i++) if ($("#"+i).length > 0)
	{
		data.cases[i]={};
		data.cases[i].score = Number($("#"+i).find(".score").val());
		data.cases[i].tests = Array();
		group[i] = Array();
	}
	for (var i=0; i<tests_cnt; i++) if ($("#test"+i).length > 0)
	{
		var x = $("#test"+i), f = Number(x.parent().parent().attr('id')), cur = {};
		if (x.find("input.in").val())
			cur.input = x.find("input.in").val();
		if (x.find("input.out").val())
			cur.output = x.find("input.out").val();
		if (x.find("input.user_input").val())
			cur.userInput = x.find("input.user_input").val();
		if (x.find("input.user_output").val())
			cur.userOutput = x.find("input.user_output").val();
		if (x.find("input.time").val())
			cur.timeLimit = Number(x.find("input.time").val());
		if (x.find("input.memory").val())
			cur.memoryLimit = Number(x.find("input.memory").val());
		data.cases[f].tests.push(cur);
		group[f].push(i);
	}
	if ($("#spj").attr("checked"))
	{
		data.spjMode = Number($("#spjMode").val());
		data.spjFile = $("#spjFile").val();
	}
	upd_gs(data, group);
	$("#traditional").val(JSON.stringify(data));
}

// DATA PART END

// UI PART BEGIN

function init_group(group)
{
	$("#group").html("");
	for (var i=0; i<group.length; i++) if (group[i] !== undefined)
	{
		$("#group").append("<div id='gc"+i+"' data-case='"+i+"' class='gc alert alert-info' style='width:full'><strong>GROUP "+i+" : </strong></div>");
		for (var j in group[i]) $("#gc"+i).append(" <span id='gt"+group[i][j]+"' data-test='"+group[i][j]+"' class='gt label label-info'>"+group[i][j]+"</span> ");
		$("#gc"+i).append("<span class='pull-right panel' style='display:none'> \
				<form class='form-inline'> \
				<input type='number' class='panel-from' style='width:35px;height:17px'></input> \
				<strong>~</strong>\
				<input type='number' class='panel-to' style='width:35px;height:17px'></input> \
				<span class='btn btn-small panel-add'>Add</span> \
				<button class='close panel-close'>&times;</button> \
				</form> \
				</span>");
	}
}

function add_test(current_case)
{
	var id = tests_cnt++;
	current_case.children(".holder").append("<div class='datatest well' style='padding:3px' id='test" + id + "'> \
			<div class='span12'><span class='label label-info pull-left'>" + id + "</span></div>\
			<label>Input File <input readonly type='text' class='in input-small pull-right'></label> \
			<div class='clearfix'></div> \
			<label>Answer File <input readonly type='text' class='out input-small pull-right'></label> \
			<div class='clearfix'></div> \
			<input type='hidden' class='user_input'></label> \
			<div class='clearfix'></div> \
			<label class='user_output'>Output File <input type='text' class='user_output input-small pull-right'></label> \
			<div class='clearfix'></div> \
			<label class='time'>Time Limit (ms) <input class='time input-small pull-right' type='number' min='0'></label> \
			<div class='clearfix'></div> \
			<label class='memory'>Mem Limit (KB) <input class='memory input-small pull-right' type='number' min='0'></label> \
			<input class='case_no' type='hidden'/><div class='clearfix'></div></div>");

	return id;
}

function add_case()
{
	var id = cases_cnt++;
	$('#data').append("<div class='datacase well' id='" + id + "' style='padding-bottom:0'> \
			<label class='pull-left'>Score <input class='score input-mini' type='text'/></label> \
			<button class='close case_close'>&times;</button><div class='clearfix'></div> \
			<div class='holder'></div></div>");

	$('.holder').sortable({
		connectWith : '.holder',
		update : function() { getDataFromElement(); }
	});

	return id;
}

function upd_IOmode()
{
	if ($("#IOMode").val() == 1) {
		$(".user_input").show();
	} else {
		$(".user_input").hide();
	}

	$(".user_output").attr('disabled', '');
	$(".user_output").hide();
	if ($("#IOMode").val() == 1 || $("#IOMode").val() == 2) {
		$(".label_user_output").show();
		$("#user_output").show();
		if ($("#IOMode").val() == 2) {
			$(".user_output").removeAttr('disabled');
			$(".user_output").show();
		} else {
			$("#user_output").removeAttr('disabled');
		}
	} else {
		$("#user_output").hide();
		$("#user_output").attr('disabled');
	}

	if ($("#IOMode").val() == 2) {
		$(".time, .memory").attr("disabled", '');
		$(".time, .memory").hide();
	} else {
		$(".time, .memory").removeAttr("disabled");
		$(".time, .memory").show();
	}

	if ($("#IOMode").val() == 3) $(".framework").show();
	else $(".framework").hide();
}

function upd_spj()
{
	if ($("#spj").attr("checked"))
		$(".spjMode, .spjFile").show();
	else
		$(".spjMode, .spjFile").hide();
}

// UI PART END

function init_codemirror()
{
	CodeMirror.defineSimpleMode("yauj",{
		start : [
			{ regex : /[Ii][Ff]\b/,						token : "keyword" },
			{ regex : /[Dd][Oo]\b/,						token : "keyword" },
			{ regex : /[Ww][Hh][Ii][Ll][Ee]\b/,			token : "keyword" },
			{ regex : /[Ff][Oo][Rr]\b/,					token : "keyword" },
			{ regex : /[Ee][Ll][Ss][Ee]\b/,				token : "keyword" },
			{ regex : /[Tt][Rr][Uu][Ee]\b/,				token : "atom" },
			{ regex : /[Ff][Aa][Ll][Ss][Ee]\b/,			token : "atom" },
			{ regex : /[Ff][Oo][Rr][Ee][Aa][Cc][Hh]\b/,		token : "keyword" },
			{ regex : /[Aa][Ss]\b/,						token : "keyword" },
			{ regex : /[Bb][Rr][Ee][Aa][Kk]\b/,			token : "keyword" },
			{ regex : /[Cc][Oo][Nn][Tt][Ii][Nn][Uu][Ee]\b/,	token : "keyword" },
			{ regex : /[Tt][Rr][Yy]/,					token : "keyword" },
			{ regex : /[Cc][Aa][Tt][Cc][Hh]/,				token : "keyword" },
			{ regex : /[Tt][Hh][Rr][Oo][Ww]/,				token : "keyword" },
			{ regex : /\"[^\"]*?\"/,						token : "string" },
			{ regex : /[a-zA-Z_]([a-zA-Z0-9_])*/,			token : "variable" },
			{ regex : /[0-9]+/,							token : "number" },
			{ regex : /[0-9]*\.[0-9]+/,					token : "number" },
			{ regex : /\/\/.*?$/,						token : "comment" }
		]
	});
	editor_init = CodeMirror.fromTextArea($("#editor-init").get(0), {
		mode : 'yauj',
		lineNumbers : true,
		indentUnit : 2,
		smartIndent : true,
		tabSize : 2,
		indentWithTabs : false,
		autofocus : true,
		theme : 'neat',
		readOnly : true
	});
	editor_run = CodeMirror.fromTextArea($("#editor-run").get(0), {
		mode : 'yauj',
		lineNumbers : true,
		indentUnit : 2,
		smartIndent : true,
		tabSize : 2,
		indentWithTabs : false,
		autofocus : true,
		theme : 'neat',
		readOnly : true
	});
	$(".CodeMirror").css("height","300px");
}

var signal = 0

function loaded() {
	if (++signal < 6) return;
	init_codemirror();
	var fileId = 0;
	cases_cnt = tests_cnt = 0;
	use_script = (!$("#traditional").val() && $("#editor-init").val());
	
	// UPLOPAD PART BEGIN
	
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
	});

	$("#btn_start").click(function() {
		$(".btn_upload").click()
		$(".progress").css('display', 'block');
		return false;
	});

	$("#btn_clear").click(function() {
		$("#files").html('');
		$("#div_upload_controls").html('');
		$(".progress").css('display', 'block');
	});
	
	// UPLOAD PART END

	$("#addcase").click(function() { add_case(); getDataFromElement(); return false; });
	$("#addgroup").click(function() {
		if (use_script)
		{
			var group = eval('('+$("#submit-group").val()+')');
			group.push([]);
			init_group(group);
			$("#submit-group").val(JSON.stringify(group));
		}
		else
			$("#addcase").click();
	});
	
	$(".case_close").live('click', function() {
		$(this).parent().fadeOut("normal", function() { $(this).remove(); getDataFromElement(); });
		return false;
	});

	$("#btn_scan").click(function() {
		$('#data_identification').ajaxSubmit({
			type: 'POST',
			success: function(data) { initialize(data); }
		});
		return false;
	});

	$("#overall_time").change(function() { $(".time").val($(this).val()); getDataFromElement(); });
	$("#overall_memory").change(function(){ $(".memory").val($(this).val()); getDataFromElement(); });
	$("#overall_score").change(function() { $(".score").val($(this).val()); getDataFromElement(); });
	$("#user_input").change(function() {
		var s = $(this).val();
		$("input.user_input").each(function(){ $(this).val(s); });
		getDataFromElement();
	});
	$("#user_output").change(function(){
		if ($("#IOMode").val() == 2) {
			var outfile = $(this).val();
			$(".datatest").each(function() {
				var id = Number($(this).attr("id").match(/\d+/)[0]) + 1;
				var user_output = outfile.replace(/\d+/, id.toString());
				$(this).find("input.user_output").val(user_output);
			});
		} else
		{
			var s = $(this).val();
			$("input.user_output").each(function(){ $(this).val(s); });
		}
		getDataFromElement();
	});
	$("#IOMode").change(function() { upd_IOmode(); getDataFromElement(); });
	$("#spj").change(function() { upd_spj(); getDataFromElement(); });
	$("input.in, input.out, input.user_input, input.user_output, input.time, input.memory, #spjMode, #spjFile").live('change',function() { getDataFromElement(); });

	$(".gc").live('mouseenter',function() { $(this).find('.panel').show(); });
	$(".gc").live('mouseleave',function() { $(this).find('.panel').hide(); });
	$(".panel-from, .panel-to").each().live('keypress',function(event) {
		if (event.keyCode==13) { $(this).siblings('.panel-add').click(); return false; }
	});
	$(".panel-add").live('click',function() {
		var from = Number($(this).siblings('.panel-from').val());
		var to = Number($(this).siblings('.panel-to').val());
		if (from > to) return;
		var cur = $(this).parents('.gc'), caseid = Number(cur.attr('data-case')), arr = Array();
		var group = eval('('+$("#submit-group").val()+')');
		for (var i = from; i <= to; i++)
		{
			$("#gt"+i).remove();
			if (!use_script)
				$("#"+caseid).children('.holder').append($("#test"+i).detach());
			arr.push(i);
		}
		for (var i = 0; i < cases_cnt; i++) if (group[i])
		{
			var nw = Array();
			for (var j in group[i]) if (group[i][j]<from || group[i][j]>to)
				nw.push(group[i][j]);
			group[i]=nw;
		}
		cur.find('.gt').each(function(){
			arr.push(Number($(this).attr('data-test')));
			$(this).remove();
		});
		arr.sort();
		group[caseid] = Array();
		for (var i in arr)
		{
			cur.append(" <span id='gt"+arr[i]+"' data-test='"+arr[i]+"' class='gt label label-info'>"+arr[i]+"</span> ");
			group[caseid].push(arr[i]);
		}
		$("#submit-group").val(JSON.stringify(group));
		return false;
	});
	$(".panel-close").live('click',function() {
		var group = eval('('+$("#submit-group").val()+')');
		var caseid = Number($(this).parents('.gc').attr('data-case'));
		if (!use_script) $("#"+caseid).find('.case_close').click();
		group.splice(caseid,1);
		$("#submit-group").val(JSON.stringify(group));
		$(this).parents('.gc').remove();
		return false;
	});
	
	$("#submit").click(function(){
		$('#backdrop').addClass('modal-backdrop');
		if (use_script)
			$('#traditional').val('');
		else
			getDataFromElement();
		$('#submit-init').val(editor_init.getValue());
		$('#submit-run').val(editor_run.getValue());
		$('#submit-script').ajaxSubmit({
			type: 'post',
			success: function(responseText, statusText) {
				if (responseText == 'success') window.location.hash = 'admin/problemset';
				else $('#page_content').html(responseText);
			}
		});
		return false;
	});

	$("#wipe").click(function(){
		access_page("admin/wipedata/"+pid, void 0, false);
		$("#data").html("");
		getDataFromElement();
		return false;
	});


	
		
	$("#nav-form-a").click(function(){
		if (use_script) return false;
		$("li.fgsnav.active").removeClass("active");
		$("div.fgsnav").hide();
		$("#nav-form-li").addClass("active");
		$("#div-form").show();
		return false;
	});
	
	$("#nav-group-a").click(function(){
		$("li.fgsnav.active").removeClass("active");
		$("div.fgsnav").hide();
		$("#nav-group-li").addClass("active");
		$("#div-group").show();
		return false;
	});
	
	$("#nav-script-a").click(function(){
		$("li.fgsnav.active").removeClass("active");
		$("div.fgsnav").hide();
		$("#nav-script-li").addClass("active");
		$("#div-script").show();
		setTimeout(function(){editor_init.refresh();},100);
		setTimeout(function(){editor_run.refresh();},100);
		return false;
	});
	
	$("#btn-unlock").click(function(){
		$("#btn-unlock").hide();
		use_script = true;
		$("#data_config").slideUp();
		$("#data_identify").slideUp();
		$("#nav-form-li").addClass("disabled");
		editor_init.options.readOnly = false;
		editor_run.options.readOnly = false;
		$("#btn-discard").show();
	});
	
	$("#btn-discard").click(function(){
		$("#btn-discard").hide();
		use_script = false;
		$("#data_config").slideDown();
		$("#data_identify").slideDown();
		$("#nav-form-li").removeClass("disabled");
		editor_init.options.readOnly = true;
		editor_run.options.readOnly = true;
		getDataFromElement();
		$("#btn-unlock").show();
	});
	
	if (use_script)
	{
		upd_spj();
		upd_IOmode();
		if ($("#submit-group").val())
			init_group(eval('('+$("#submit-group").val()+')'));
		else
			$("#submit-group").val("[]");
		$("#nav-script-a").click();
		$("#btn-unlock").click();
	} else
	{
		if ($("#traditional").val())
			initialize($("#traditional").val());
		else
			$("#btn_scan").click();
		if (!$("#submit-group").val())
			$("#submit-group").val("[]");
	}
	$('#backdrop').removeClass('modal-backdrop');
}

$(document).ready(function(){
	loadJsFile("jquery-ui", "js/jquery-ui.js", loaded);
	loadJsFile("jquery.ui.widget", "js/jquery.ui.widget.js", loaded);
	loadJsFile("jquery.iframe-transport", "js/jquery.iframe-transport.js", loaded);
	loadJsFile("jquery.fileupload", "js/jquery.fileupload.js", loaded);
	loadJsFile("yaujscript", "js/yaujscript.js", loaded);
	loadJsFile("codemirror", "application/third_party/codemirror/lib/codemirror.js", function(){
		loadJsFile("codemirror-simple", "application/third_party/codemirror/addon/mode/simple.js", loaded)
	});
});

})();
