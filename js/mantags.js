angular.module('appMantags', [])
	.directive('akModal', function() {
		return {
			restrict: 'A',
			link: function(scope, element, attrs) {
				scope.$watch(attrs.akModal, function(value) {
					if (value)
						element.modal('show');
					else
						element.modal('hide');
				});
			}
		};
	})
	.controller('MantagsCtrl', ['$scope', '$http', function($scope, $http) {
		updTags = function() {
			$http.get('index.php/main/all_tags').then(function(res) {
				$scope.tags = res.data;
				$scope.lists = [{prototype: null, selection: ['null']}];
				$scope.chosen = null;
			});
		};
		updTags();

		$scope.showModal = false;

		$scope.updList = function() {
			for (var i = 0; i < $scope.lists.length; i++) {
				var next = Number($scope.lists[i].selection[0]);
				if (isNaN(next)) { // for '--' -> 'null'
					$scope.lists = $scope.lists.slice(0, i + 1);
					break;
				}
				if (i + 1 == $scope.lists.length) {
					$scope.lists.push({prototype: next, selection: ['null']});
					break;
				}
				if ($scope.lists[i + 1].prototype != next) {
					$scope.lists[i + 1] = {prototype: next, selection: ['null']};
					$scope.lists = $scope.lists.slice(0, i + 2);
					break;
				}
			}
			if ($scope.lists.length >= 2) {
				$scope.chosen = {id: $scope.lists[$scope.lists.length - 1].prototype};
				for (var j in $scope.tags)
					if ($scope.tags[j].idCategory == $scope.chosen.id) {
						$scope.chosen.name = $scope.tags[j].name;
					}
			} else
				$scope.chosen = null;
		};

		$scope.del = function(msg) {
			if (confirm(msg))
				$http.get('index.php/admin/del_tag/' + $scope.chosen.id).then(function(res) {
					updTags();
				});
		};

		$scope.add = function() {
			var url = 'index.php/admin/add_tag/' + $scope.inputName + '/' + ($scope.chosen ? $scope.chosen.id : '');
			$http.get(url).then(function(res) {
				res = res.data;
				if (res.status == "ok")
					updTags();
				else
					alert(res.message);
			});
		};

		$scope.addBtnTitle = function() {
			if ($scope.chosen) {
				return language == "english" ? "Add a tag as a sub-tag of " + $scope.chosen.name : "添加标签为" + $scope.chosen.name + "的子标签";
			} else {
				return language == "english" ? "Add a primary tag" : "添加一个顶级标签";
			}
		};
	}]);
