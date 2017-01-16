angular.module('appMantags', [])
	.directive('akModal', function() { // Angular doesn't work with native Bootstrap modal
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

			function getFromId(id) {
				var ret = null;
				for (var j in $scope.tags)
					if ($scope.tags[j].idCategory == id)
						ret = {id: id, name: $scope.tags[j].name, proto: $scope.tags[j].prototype, peers: [], properties: $scope.tags[j].properties};
				if (! ret) return null;
				for (var j in $scope.tags)
					if ($scope.tags[j].idCategory != id && $scope.tags[j].prototype == ret.proto)
						ret.peers.push({id: $scope.tags[j].idCategory, name: $scope.tags[j].name});
				for (var j in $scope.tags)
					if ($scope.tags[j].idCategory == ret.proto) // null != 0 in javascript
						ret.proto = {id: $scope.tags[j].idCategory, proto: $scope.tags[j].prototype};
				return ret;
			}

			$scope.chosen = ($scope.lists.length >= 2 ? getFromId($scope.lists[$scope.lists.length - 1].prototype) : null);
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

		$scope.changeProto = function(newProto) {
			$http.get('index.php/admin/tag_change_proto/' + $scope.chosen.id + '/' + newProto).then(function(res) {
				updTags();
			});
		};

		$scope.updProperties = function() {
			$http.post(
				'index.php/admin/tag_set_properties/' + $scope.chosen.id,
				$.param({properties: JSON.stringify($scope.chosen.properties)}),
				{headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
			);
		};

		$scope.addBtnTitle = function() {
			if ($scope.chosen) {
				return language == "english" ? "Add a tag as a sub-tag of " + $scope.chosen.name : "添加标签为" + $scope.chosen.name + "的子标签";
			} else {
				return language == "english" ? "Add a primary tag" : "添加一个顶级标签";
			}
		};

		$scope.moveInfTitle = function(name) {
			return language == "english" ? "Sub-tag of " + name : name + "的子标签";
		};
	}]);
