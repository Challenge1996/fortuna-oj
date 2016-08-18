<?php
	if (!isset($data) || !$data) {
		echo '<div class="alert"><strong>THERE IS NO SUBMISSION</strong></div>';
		return;
	}
?>

<script type="text/javascript" src="js/contest_statistic.js"></script>
<script>
	angular.module('appStanding') // already created
		.constant('data', <?=json_encode(isset($data)?$data:null)?>)
		.constant('info', <?=json_encode(isset($info)?$info:null)?>)
		.constant('startTime', <?=json_encode(isset($startTime)?$startTime:null)?>)
		.constant('est', <?=json_encode(isset($est)?$est:null)?>);
</script>

<div id='standing-app' ng-controller='StandingCtrl'>
	<button ng-if-start='startTime' ng-click="download_statistic(info.cid)" class="btn btn-small pull-right">
		<strong>export</strong>
	</button>
	<button ng-if-end ng-click="$parent.show_previous=!show_previous" class="btn btn-small pull-right" id="sps_button">
		<strong>{{show_previous ? 'hide' : 'show'}} previous submissions</strong>
	</button>
	<button ng-if='!startTime' ng-click="download_result(info.cid)" class="btn btn-small pull-right">
		<strong>export</strong>
	</button>

	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th><?=lang('rank')?></th>
				<th><?=lang('user')?></th>
				<th ng-if-start='oi'><?=lang('score')?></th>
				<th ng-if-end ng-repeat='row in info.problemset' style='text-align:center'>
					<a href='#contest/show/{{info.cid}}/{{row.id}}'>{{row.title}}</a>
				</th>
				<th ng-if-start='acm'>Solved</th>
				<th>Penalty</th>
				<th ng-if-end ng-repeat='i in range(0, info.count)' style='text-align:center'>
					{{ indexChar(i) }}
				</th>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat='row in data' ng-if='!(startTime && row.submitTime < startTime) || show_previous'>
				<td><span class='label'>{{row.rank}}</span></td>
				<td><a href='#users/{{row.name}}'><span class='label label-info'>{{row.name}}</span></a></td>
				<td>
					<span class='badge badge-info'>{{row.score}}</span><!-- to remove spaces
				 --><sup ng-if='est[row.uid]'><span class='badge'>{{est[row.uid]['sum']}}</span></sup>
				</td>
				<td ng-if='oi' ng-repeat='prob in info.problemset' style='text-align:center'>
					<a ng-if='isset(row.acList[prob.pid])' href='#main/code/{{row.attempt[prob.pid]}}'>
						<span ng-class='{badge:true, "badge-important":!row.acList[prob.pid], "badge-success":row.acList[prob.pid]}'>
							{{row.acList[prob.pid]}}
						</span><!-- to remove spaces
				 --></a><!-- to remove spaces
				 --><sup ng-if='est[row.uid] && isset(est[row.uid][prob.pid])'><!-- to remove spaces
					 --><span class='badge'>{{est[row.uid][prob.pid]}}</span>
					<sup>
				</td>
				<td ng-if-start='acm'><span class='badge badge-info'>{{row.penalty}}</span></td>
				<td ng-if-end ng-repeat='prob in info.problemset' style='text-align:center'>
					<span ng-if='row.attempt[prob.pid]'>
						<span ng-if='isset(row.acList[prob.pid])' class='badge badge-success'>
							{{row.attempt[prob.pid]}}/{{row.acList[prob.pid]}}
						</span>
						<span ng-if='!isset(row.acList[prob.pid])' class='badge badge-important'>
							-{{row.attempt[prob.pid]}}
						</span>
					</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<iframe id="downloader" style="display:none"></iframe>

<script>
	// place this after all js code
	// have to do this because the page is loaded via AJAX
	$(document).ready(function() {
		angular.bootstrap($('#standing-app'), ['appStanding']);
	});
</script>

