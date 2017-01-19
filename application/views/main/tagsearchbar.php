<link href="css/tagsearchbar.css" rel="stylesheet">
<script src='js/tagsearchbar.js' type='text/javascript'></script>
<script src='js/bootstrap-better-typeahead.min.js' type='text/javascript'></script>

<span id='tagbar-app' style='overflow:hidden' ng-controller='TagbarCtrl'>
	<ul id='tag-list-ul' class='inline'>
		<li ng-repeat='tag in chosen'>
			<span class='label'>
				{{tag.name}}
				<span class='close' ng-click='del(tag)'>&times;</span>
			</span>
		</li>
		<li id='tag-input-li'>
			<input type="text" id='tag-input' placeholder='Input tags...' autocomplete='off' ng-model='typing' ng-keydown='keydown($event)'></input>
		</li>
	</ul>
	<input id='filter_content' type='text' style='display:none' value='{{output()}}' />
</span>

<script>
	angular.module('appTagbar') // already created
		.constant('tags', <?=json_encode($tags)?>);

	$(document).ready(function() {
		angular.bootstrap($('#tagbar-app'), ['appTagbar']); // this must be after 'tags' ready

		$scope = $("#tagbar-app").scope();

		$("#tag-input").typeahead({ // use js because unable to pass functions via 'data-' attributes
		   items: 18,
		   minLength: 0,
		   source: $scope.tags.map(function(x) { return x.fullname; }),
		   updater: function(item) { $scope.$apply(function() { $scope.add(item); }); }
		});

		$('#tag-input').bind('focus', function(){
			$('#action_form').die();
			$('#action_form').live('keypress', function(event){
				if (event.keyCode == 13){
					$('#search_button').click();
					return false;
				}
			})
		});
	});
</script>
