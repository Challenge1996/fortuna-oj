<link href="css/jquery.fileupload-ui.css" rel="stylesheet">

<div id="user-header" class="row-fluid">
	<div class="span6"><fieldset id="user-information">
		<legend>
			Basic Information
			<a href="#users/<?=$data->name?>/statistic" class="pull-right">
				<small><strong>Statistic</strong></small>
			</a>
		</legend>
		
		<div class="row-fluid">
			<div id="user-picture" class="span6" style="text-align:center">
				<div class="well">
					<img src="images/avatar/<?=$data->userPicture?>" alt="User Avatar" width="225" height="300" >
				</div>
				
				<?php if ($this->user->uid() == $data->uid) { ?>
				<button class="btn btn-small btn-success" id="btn_change">Change Avatar</button>
				<?php } ?>
				<?php if ($data->blogURL != '') { ?>
				<a href=<?=$data->blogURL?> class="btn btn-small btn-info" type="button">Visit <?=$data->name?>'s Blog</a>
				<?php } ?>
			</div>
			
			<div class="span6" style="height:339px"><dl class="dl-horizontal">
			
				<dt class="user_specificator">uid</dt>
				<dd><span class="badge badge-info"><?=$data->uid?></span></dd>
				
				<dt class="user_specificator">Username</dt>
				<dd><span class="label label-info"><?=$data->name?></span></dd>
					
				<dt class="user_specificator">Rank</dt>
				<dd><span class="badge badge-info"><?=$data->rank?></span></dd>
						
				<dt class="user_specificator">AC Problems</dt>
				<dd><a href="#users/<?=$data->name?>/statistic">
					<span class="badge badge-info"><?=$data->acCount?></span>
				</a></dd>
				
				<dt class="user_specificator">Solved</dt>
				<dd><a href="#users/<?=$data->name?>/statistic">
					<span class="badge badge-info"><?=$data->solvedCount?></span>
				</a></dd>
				
				<dt class="user_specificator">Submit</dt>
				<dd><a href="#users/<?=$data->name?>/statistic">
					<span class="badge badge-info"><?=$data->submitCount?></span>
				</a></dd>
				
				<dt class="user_specificator">Rate</dt>
				<dd><span class="badge badge-info"><?=$data->rate . '%'?></span></dd>
				
			</dl></div>
		</div>
	</fieldset></div>
	
	<div class="span5" id="chart-container" style="height:400px">
	</div>
</div>

<div class="modal hide fade" id="modal_avatar">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Change your profile avatar</h3>
	</div>
	
	<div class="modal-body">
		<div>
			<img id="preview" width="225px" height="300px"></img>
		</div>
		<div class="pull-right">
			<button class="fileinput-button btn btn-small btn-primary">
				Select Picture
				<input type="file" id="avatar" name="avatar" data-url="index.php/users/<?=$data->name?>/avatar_upload" />
			</button>
			<button id="btn_upload" class="btn btn-small btn-success">Upload</button>
		</div>
	</div>
</div>


<script src="js/jquery-ui.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<!--[if gte IE 8]><script src="js/jquery.xdr-transport.js"></script><![endif]-->
<script type="text/javascript">
	$(document).ready(function(){
		$("#avatar").fileupload({
			dataType: 'json',
			add: function(e, data) {
				$.each(data.files, function(index, file) {
					var reader = new FileReader();
					reader.onload = function(e) {
						$("#preview").attr('src', e.target.result);
					}
					reader.readAsDataURL(file);
						
					$("#btn_upload").click(function() {
						data.submit();
					});
				})
			},
			done: function(e, data) {
				$("#modal_avatar").modal('hide');
				location.reload();
			}
		}),
		
		$("#btn_change").click(function() {
			$("#modal_avatar").modal()
		})
	})

	verdicts = [{
		type: 'pie',
		data: [
			[ 'Other',    <?=$data->count[-2] + $data->count[3] + $data->count[9]?> ],
			[ 'Pending',    <?=$data->count[-1]?> ],
			{
				name: 'Accepted',
				y: <?=$data->count[0]?>,
				sliced: true,
				selected: true
			},
			['PE',    <?=$data->count[1]?>],
			['WA',    <?=$data->count[2]?>],
			['OLE',    <?=$data->count[4]?>],
			['MLE',    <?=$data->count[5]?>],
			['TLE',    <?=$data->count[6]?>],
			['RE',    <?=$data->count[7]?>],
			['CE',    <?=$data->count[8]?>],
		]
	}]
			
	if ( typeof (Highcharts) == 'undefined') {
		$.getScript("js/highcharts.js", function(script, textStatus, jqXHR) {
			$.getScript("js/exporting.js", function(script, textStatus, jqXHR) {
				initialize_chart()
				render_pie('#chart-container', 'Verdicts Chart', verdicts)
			})
		})

	} else render_pie('#chart-container', 'Verdicts Chart', verdicts)
	
</script>
