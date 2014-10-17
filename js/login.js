function register()
{
	$('#login_field').modal('hide');
	load_page('main/register');
}

function load_forget()
{
	if (!$('#username').val())
	{
		$('#username_error').show();
		return;
	}
	$('#username_error').hide();
	$('#body0').slideUp('normal',function(){$('#body1').slideDown();});
	$('#footer0').slideUp('normal',function(){$('#footer1').slideDown();});
}

function hide_forget()
{
	$('#body1').slideUp('normal',function(){$('#body0').slideDown();});
	$('#footer1').slideUp('normal',function(){$('#footer0').slideDown();});
}

function send_reset()
{
	$.get("index.php/misc/reset_password",
	{
		name: $('#username').val()
	},
	function(data, status)
	{
		if (status=="success")
			alert(data);
		else
			alert("An Error Occured. Try Again.");
	});
}

$(document).ready(function(){
	$('#login_field').modal({backdrop: 'static', keyboard: false});
});

