<script type='text/javascript'>
	var language = '<?=$this->config->item('language')?>';
</script>

<script type='text/javascript' src='js/mantags.js'></script>
<link href="css/mantags.css" rel="stylesheet">

<div id='mantags-app' ng-controller='MantagsCtrl'>
	<h3><?=lang('manage_tags')?></h3>

	<hr />

	<div class='alert alert-block alert-info'>
		<?=lang('manage_tags_description')?>
	</div>

	<div class='container-fluid'>
		<div class='row-fluid'>
			<div class='span3 well'>
				<div ng-if='chosen'>
					<h4>{{chosen.name}}</h4>
					<hr />
					<form>
						<label class="checkbox">
							<input type="checkbox" ng-model="chosen.properties.prohibit" ng-change="updProperties()"/><?=lang('prohibited_tag')?>
						</label>
						<em><?=lang('will_affect_inferior_tags')?></em>
					</form>
					<hr />
					<form>
						<p ng-if="chosen.proto">
							<button class='btn btn-small' ng-click="changeProto(chosen.proto.proto)">
								<i class="icon-arrow-left"></i>
								<?=lang('move_up_tag')?>
							</button>
						</p>
						<p ng-if="chosen.peers.length">
							<div class="dropdown" ng-if="chosen.peers.length">
								<a href="#" class='btn btn-small dropdown-toggle' data-toggle="dropdown">
									<i class="icon-arrow-right"></i>
									<?=lang('move_down_tag')?>
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li ng-repeat="item in chosen.peers">
										<a role="menuitem" tabindex="-1" ng-click="changeProto(item.id)">{{moveInfTitle(item.name)}}</a>
									</li>
								</ul>
							</div>
						</p>
						<p>
							<button class='btn btn-small btn-danger' ng-click='del("<?=lang('del_tag_confirm')?>")'><?=lang('delete')?></button>
						</p>
					</form>
				</div>
				<div ng-if='! chosen'>
					<h4><?=lang('please_choose_a_tag')?></h4>
				</div>
				<hr />
				<button class="btn btn-primary" ng-click="showModal = true">{{addBtnTitle()}}</button>
			</div>
			<div class='span9'>
				<select multiple ng-repeat='list in lists' ng-model='list.selection' ng-click='updList(list)' size="10">
					<option value='null' selected>--</option>
					<option value='{{item.idCategory}}' ng-repeat='item in tags' ng-if='item.prototype==list.prototype'>{{item.name}}</option>
				</select>
			</div>
		</div>
	</div>

	<div class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" ak-modal="showModal" data-keyboard="false" data-backdrop="static">
		<div class="modal-header">
			<button type="button" class="close" aria-hidden="true" ng-click="showModal = false">×</button>
			<h3><?=lang('please_input_tag_name')?></h3>
		</div>
		<div class="modal-body">
			<p><input ng-model='inputName'></input></p>
		</div>
		<div class="modal-footer">
			<button class="btn" aria-hidden="true" ng-click="showModal = false"><?=lang('close')?></button>
			<button class="btn btn-primary" ng-click='add(); showModal = false'><?=lang('ok')?></button>
		</div>
	</div>
</div>

<script>
	// place this after all js code
	// have to do this because the page is loaded via AJAX
	$(document).ready(function() {
		angular.bootstrap($('#mantags-app'), ['appMantags']);
	});
</script>
