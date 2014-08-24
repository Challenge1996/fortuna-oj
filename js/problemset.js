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
	if ($("#note_textarea_"+pid.toString()).val()!='')
		$("#note_icon_"+pid.toString()).removeClass("icon-white");
	else
		$("#note_icon_"+pid.toString()).addClass("icon-white");
	$(".note_text_tr_"+pid.toString()).slideUp('fast');
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
