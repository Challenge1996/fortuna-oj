var status_open_all=false;

function upd_bookmark(pid)
{
	$.post("index.php/main/upd_bookmark/"+pid.toString(), {
		star: (! $("#star_icon_"+pid.toString()).hasClass("icon-white")).toString(),
	note: $("#note_textarea_"+pid.toString()).val()
	});
}

function upd_star(pid)
{
	$("#star_icon_"+pid.toString()).toggleClass("icon-white");
	if ($("#star_icon_"+pid.toString()).attr("title")=='star it')
		$("#star_icon_"+pid.toString()).attr("title",'unstar it');
	else
		$("#star_icon_"+pid.toString()).attr("title",'star it');
	upd_bookmark(pid);
}

function open_note(pid)
{
	if ($(".note_text_tr_"+pid.toString()).css('display')=='none')
	{
		$(".note_text_tr_"+pid.toString()).slideDown('fast');
		$("#note_textarea_"+pid.toString()).focus();
	}
}

function close_note(pid)
{
	upd_bookmark(pid);
	pid=pid.toString();
	if ($("#note_textarea_"+pid).val()!='')
	{
		$("#note_icon_"+pid).removeClass("icon-white");
		$(".note_text_tr_"+pid).addClass("note_text_tr_nonempty");
	}
	else
	{
		$("#note_icon_"+pid).addClass("icon-white");
		$(".note_text_tr_"+pid).removeClass("note_text_tr_nonempty");
	}
	if (status_open_all) return;
	$(".note_text_tr_"+pid).slideUp('fast');
}

function toggle_open_all()
{
	if (status_open_all)
	{
		$("#open_all_icon").removeClass("icon-resize-small");
		$("#open_all_icon").addClass("icon-resize-full");
		$(".note_text_tr").slideUp('fast');
		status_open_all=false;
	} else
	{
		$("#open_all_icon").removeClass("icon-resize-full");
		$("#open_all_icon").addClass("icon-resize-small");
		$(".note_text_tr_nonempty").slideDown('fast');
		status_open_all=true;
	}
}

$(document).ready(function(){
	$('#goto_button').live('click', function(){
		var pid = $('#goto_pid').val();
		if (pid != '')
		load_page('main/show/' + pid);
	return false;
	}),

	$('#search_button').live('click', function(){
		var content = $('#search_content').val();
		content=encodeURIComponent(content);
		if (content != '') load_page("main/problemset?search=" + content);
		return false;
	}),

	$('#filter_button').live('click', function(){
		var content = $('#filter_content').val();
		if (content == 0) load_page('main/problemset/1');
		else if (content != '') load_page("main/problemset?filter=" + content);
		return false;
	}),

	$('#btn_goto_page').live('click', function(){
		var page = $('#goto_page').val();
		load_page("main/problemset/" + page);
		return false;
	}),

	$('#goto_pid').live('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13 && $('#goto_pid').val() != ''){
				$('#goto_button').click();
				return false;
			}
		})
	}),

	$('#search_content').live('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13 && $('#search_content').val() != ''){
				$('#search_button').click();
				return false;
			}
		})
	}),

	$('#goto_page').live('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13 && $('#goto_page').val() != ''){
				$('#btn_goto_page').click();
				return false;
			}
		})
	})
});
