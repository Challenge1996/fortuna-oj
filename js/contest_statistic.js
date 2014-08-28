var show_previous=false;

function download_result(cid){
	$("#downloader").attr('src', 'index.php/contest/result/' + cid);
}

function toggle_previous(){
	if (show_previous)
	{
		show_previous=false;
		$("#sps_button").html("<strong>show previous submissions</strong>");
		$(".submitted_before").slideUp('fast');
	} else
	{
		show_previous=true;
		$("#sps_button").html("<strong>hide previous submissions</strong>");
		$(".submitted_before").slideDown('fast');
	}
}
