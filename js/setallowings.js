$(document).ready(function(){
	if (typeof(loaded)!='undefined') return;
	loaded = true;

	function reload(data)
	{
		if (data === undefined) data = {};
		var users = [], probs = [];
		$(".user-li").each(function(){
			var s = $(this).attr('data-user');
			if (s) users.push(s);
		});
		$(".prob-li").each(function(){
			var s = $(this).attr('data-prob');
			if (s) probs.push(Number(s));
		});
		data["users"] = users;
		data["probs"] = probs;
		$.post("index.php/admin/setallowings",data,function(data, status){
			if (status!="success") return;
			focus_tmp = $(":focus");
			if (focus_tmp) focus_tmp = focus_tmp.attr('id')
			users_tmp = $("#user-input").val();
			probs_tmp = $("#prob-input").val();
			$("#page_content").html(data);
			$("#user-input").val(users_tmp);
			$("#prob_input").val(probs_tmp);
			if (focus_tmp) $("#"+focus_tmp).focus();
		});
	}

	$('.close').live('click',function(){
		$(this).parents('li').remove();
		reload();
	});

	$('#user-input').live('keydown', function(event){
		if (event.keyCode == 8 && !$(this).val()) $(".user-close:last").click();
	});

	$('#prob-input').live('keydown', function(event){
		if (event.keyCode == 8 && !$(this).val()) $(".prob-close:last").click();
	});

	function user_change(){
		$('#user-input').die('input');
		var users = $(this).val().split(/[\s,]/);
		var last = users.pop();
		var need = false;
		for (i in users) if (users[i] && !$('#user-span-'+users[i]).length)
		{
			$(".user-li:last").after(
				"<li class='user-li' data-user='"+users[i]+"'><span id='user-span-"+users[i]+"' class='label label-info'>"+users[i]+"<span class='close user-close'>&times;</span></span></li>"
			);
			need = true;
		}
		$(this).val(last);
		$('#user-input').live('input', user_change);
		if (need) reload();
	}
	$('#user-input').live('input', user_change);

	function prob_change(){
		$('#prob-input').die('input');
		var probs = $(this).val().split(/[\s,]/);
		var last = probs.pop();
		var need = false;
		for (i in probs) if (probs[i] && !$('#prob-span-'+probs[i]).length)
		{
			$(".prob-li:last").after(
				"<li class='prob-li' data-prob='"+probs[i]+"'><span id='prob-span-"+probs[i]+"' class='label label-info'>"+probs[i]+"<span class='close prob-close'>&times;</span></span></li>"
			);
			need = true;
		}
		$(this).val(last);
		$('#prob-input').live('input',prob_change);
		if (need) reload();
	}
	$('#prob-input').live('input',prob_change);

	$('#user-input,#prob-input').live('blur',function(){
		$(this).val($(this).val()+' ');
		$(this).trigger('input');
	});
	
	$('#add_all').live('click',function(){
		reload({"all":"add"});
	});
	
	$('#del_all').live('click',function(){
		reload({"all":"del"});
	});
	
	$('.check,.cross').live('click',function(){
		reload({
			"alter_user":$(this).attr('data-user'),
			"alter_prob":Number($(this).attr('data-prob'))
		});
	});
});
