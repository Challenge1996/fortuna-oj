var show_previous=false;

$(document).ready(
	function download_result(){
		$("#downloader").attr('src', 'index.php/contest/result/<?=$info->cid?>');
	}
);

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
