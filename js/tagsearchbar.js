angular.module('appTagbar', [])
	.controller('TagbarCtrl', ["$scope", "tags", function($scope, tags) {
		$scope.tags = tags;
		$scope.typing = '';

		$scope.chosen = [];
		if (typeof(currentFilter) == "object") // is array
			for (var i in currentFilter)
				for (var j in tags) // optimizable
					if (tags[j].idCategory == currentFilter[i]) {
						$scope.chosen.push(tags[j]);
						break;
					}

		$scope.add = function(item) {
			var tag = null;
			for (var i in $scope.tags)
				if ($scope.tags[i].fullname == item) {
					tag = $scope.tags[i];
					break;
				}
			if (tag == null) return; // this will occur when pressing ENTER before typing any characters
			if ($.inArray(tag, $scope.chosen) == -1) // because ng-repeat cannot resolve duplicated keys
				$scope.chosen.push(tag);

			// re-prompt the menu immediately
			// trigger `focus` after all other event processed
			setTimeout(function(){ $("#tag-input").focus(); }, 0);
		};

		$scope.del = function(tag) {
			$scope.chosen = $scope.chosen.filter(function(x) { return x !== tag; });
		};

		$scope.keydown = function(e) { // do not use 'keyup' because this should be executed before charaters in the input to be deleted
			if (e.keyCode == 8 && $scope.chosen.length > 0 && $scope.typing == '') // backspace
				$scope.chosen = $scope.chosen.slice(0, $scope.chosen.length - 1);
		}

		$scope.output = function() {
			return JSON.stringify($scope.chosen.map(function(x){ return x.idCategory; }));
		}
	}]);

