if (typeof Object.assign == "undefined") alert("Please update your browser");

var origin_query = deparam(window.location.hash.substring(1));

var status_open_all=false;
var show_in_control = origin_query['show_in_control']?1:0;
var show_note = origin_query['show_note']?1:0;
var show_starred = origin_query['show_starred']?1:0;
var reverse_order = origin_query['reverse_order']?1:0;
var search_note = origin_query['search_note'];

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

function delete_problem(pid, selector){
	$("#modal_confirm #delete").click(function(){
		$("#modal_confirm").modal('hide');
		$("#modal_confirm #delete").unbind('click');
		access_page("admin/delete_problem/" + pid);
	});
	$("#modal_confirm #info").html(pid + '. ' + selector.parent().parent().find('.title').html());
	$("#modal_confirm").modal({backdrop: 'static'});
}

$(document).ready(function(){
	$('#goto_button').bind('click', function(){
		var pid = $('#goto_pid').val();
		if (pid != '')
		load_page('main/show/' + pid);
		return false;
	});
	
	$("#btn_edit_pid").bind('click', function(){
		var pid = $('#goto_pid').val();
		if (pid != '') load_page("admin/addproblem/" + pid);
		return false;
	});
	
	$("#btn_configure_pid").bind('click', function(){
		var pid = $("#goto_pid").val();
		if (pid != '') load_page("admin/dataconf/" + pid);
		return false;
	});

	$('#search_button').bind('click', function(){
		var query = {};
		Object.assign(query, origin_query);
		if ($('#search_content').val())
			query['search'] = encodeURIComponent($('#search_content').val());
		else
			delete query['search'];
		if ($('#filter_content').val()!='0')
			query['filter'] = $('#filter_content').val();
		else
			delete query['filter'];
		if (reverse_order)
			query['reverse_order'] = reverse_order;
		else
			delete query['reverse_order'];
		if (show_in_control)
			query['show_in_control'] = show_in_control;
		else
			delete query['show_in_control'];
		if (show_starred)
			query['show_starred'] = show_starred;
		else
			delete query['show_starred'];
		if (show_note)
			query['show_note'] = show_note;
		else
			delete query['show_note'];
		if (search_note)
			query['search_note'] = search_note;
		else
			delete query['search_note'];
		console.log(query);
		console.log($.param(query));
		if (!query) return false;
		load_page("main/problemset?" + $.param(query));
		return false;
	});

	$('#btn_goto_page').bind('click', function(){
		var page = $('#goto_page').val();
		var hash = window.location.hash.split('?');
		var query = (hash.length<2 ? '' : hash[1]);
		load_page("main/problemset/" + page + '?' + query);
		return false;
	});

	$('#goto_pid').bind('keypress', function(event){
		if (event.keyCode == 13 && $('#goto_pid').val() != ''){
			$('#goto_button').click();
			return false;
		}
	});

	$('#search_content').bind('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13){
				$('#search_button').click();
				return false;
			}
		})
	});
	
	$('#filter_content').bind('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13){
				$('#search_button').click();
				return false;
			}
		})
	});
	
	$('#goto_page').bind('focus', function(){
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13 && $('#goto_page').val() != ''){
				$('#btn_goto_page').click();
				return false;
			}
		})
	});
	
	$("#search_button").popover({
		html: true,
		trigger: 'manual',
		placement: 'bottom',
		title:'\
			<strong>Advanced Searching</strong> \
			<span id="close_popover" class="close pull-right">&times;</span>',
		content:'\
			<div> \
				<label for="use_reverse_order">'+option_reverse_order+'</label> \
				<input id="use_reverse_order" class="pull-right" type="checkbox" '+(reverse_order?'checked':'')+' onclick="reverse_order=1-reverse_order"></input> \
			</div> \
			<div> \
				<label for="use_show_in_control">'+option_show_in_control+'</label> \
				<input id="use_show_in_control" class="pull-right" type="checkbox" '+(show_in_control?'checked':'')+' onclick="show_in_control=1-show_in_control"></input> \
				<span class="pull-right"><i>'+including_hidden+'</i></span>\
			</div> \
			<div> \
				<label for="show_starred_content">'+option_select_starred+'</label> \
				<input id="show_starred_content" class="pull-right" type="checkbox" '+(show_starred?'checked':'')+' onclick="show_starred=1-show_starred"></input> \
			</div> \
			<div> \
				<label for="show_note_content">'+option_select_noted+'</label> \
				<input id="show_note_content" class="pull-right" type="checkbox" '+(show_note?'checked':'')+' onclick="show_note=1-show_note"></input> \
			</div> \
			<div> \
				<label for="search_note_content">'+option_match_in_note+'</label> \
				<input id="search_note_content" style="width:100%" onkeydown="search_note=$(this).val()">'+(search_note?search_note:'')+'</input> \
			</div>'
	});
	
	$("#adv_button").bind('click',function(){
		$("#search_button").popover('toggle');
		$('#action_form').die();
		$('#action_form').live('keypress', function(event){
			if (event.keyCode == 13){
				$('#search_button').click();
				return false;
			}
		})
		return false;
	});
	
	$("#close_popover").die();
	$("#close_popover").live('click',function(){
		$("#search_button").popover('hide');
		return false;
	});
});
