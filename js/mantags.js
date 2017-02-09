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
				var groupSet = new Set($scope.tags.map(function(x) { return x.properties.group; }));
				$scope.groups = Array.from(groupSet);
				if ($scope.groups === [])
					$scope.groups = [undefined];
				if (! groupSet.has($scope.curGroup))
					$scope.curGroup = $scope.groups[0];
			});
		};
		$scope.curGroup = undefined;
		updTags();
		$scope.lists = [{prototype: null, selection: ['null']}];
		$scope.chosen = null;

		$scope.showModal = false; // this means modal for input new tag
		$scope.showNewGroupModal = false;

		$scope.updList = function(param) {
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
				if ($scope.lists[i + 1].prototype != next || $scope.lists[i] == param) {
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
					if ($scope.tags[j].idCategory != id && $scope.tags[j].prototype == ret.proto) {
						if ($scope.tags[j].properties && ret.properties && $scope.tags[j].properties.group !== ret.properties.group)
							continue;
						ret.peers.push({id: $scope.tags[j].idCategory, name: $scope.tags[j].name});
					}
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
					$scope.lists = [{prototype: null, selection: ['null']}];
					$scope.chosen = null;
				});
		};

		$scope.add = function() {
			$http.post(
				'index.php/admin/add_tag/' + $scope.inputName + '/' + ($scope.chosen ? $scope.chosen.id : ''),
				$.param({properties: JSON.stringify({group: $scope.curGroup})}),
				{headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
			).then(function(res) {
				res = res.data;
				if (res.status == "ok") {
					updTags();
					$scope.lists = [{prototype: null, selection: ['null']}];
					$scope.chosen = null;
				} else
					alert(res.message);
			});
		};

		$scope.addGroup = function() {
			$scope.groups.push($scope.curGroup = $scope.inputGroupName);
		}

		$scope.changeProto = function(newProto) {
			$http.get('index.php/admin/tag_change_proto/' + $scope.chosen.id + '/' + newProto).then(function(res) {
				updTags();
				$scope.lists = [{prototype: null, selection: ['null']}];
				$scope.chosen = null;
			});
		};

		$scope.updProperties = function() {
			$http.post(
				'index.php/admin/tag_set_properties/' + $scope.chosen.id,
				$.param({properties: JSON.stringify($scope.chosen.properties)}),
				{headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
			).then(function(res) {
				updTags();
			});
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
