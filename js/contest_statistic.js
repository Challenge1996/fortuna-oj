angular.module('appStanding', [])
	.service('sortUser', function() {
		var sorting = "rank";
		var order = 1; // -1 means reverse

		this.toggleFactory = function(data) {
			return function(keyword) { // keyword : string means member of row of data, number means pid
				if (keyword != sorting) {
					sorting = keyword;
					order = 1;
				} else
					order = -order;

				var cmp = function(a, b) {
					var _a = (a ? a : 0);
					var _b = (b ? b : 0);
					return (_a<_b ? -1 : _a>_b ? 1 : 0);
				}

				if (! isNaN(Number(keyword)))
					data.sort(function(lhs, rhs) { 
						if (! lhs.acList) lhs.acList = {};
						if (! rhs.acList) rhs.acList = {};
						return order * cmp(lhs.acList[keyword], rhs.acList[keyword]);
					});
				else
					data.sort(function(lhs, rhs) {
						return order * cmp(lhs[keyword], rhs[keyword]);
					});
			};
		};

		this.getClass = function(keyword) {
			if (keyword != sorting)
				return 'icon-resize-vertical';
			if (order == 1)
				return 'icon-arrow-up';
			else
				return 'icon-arrow-down';
		};
	})
	.controller('StandingCtrl', ['$scope', 'data', 'info', 'startTime', 'est', 'sortUser', function($scope, data, info, startTime, est, sortUser) {
		$scope.indexChar = function(i) {
			return String.fromCharCode(Number(i) + 65);
		};

		$scope.isset = function(x) {
			return (typeof(x) != 'undefined' && x !== null);
		};

		$scope.show_previous = false;
		$scope.oi = (info.contestMode == 'OI' || info.contestMode == 'OI Traditional');
		$scope.acm = (info.contestMode == 'ACM');

		$scope.download_result = function(cid) {
			$("#downloader").attr('src', 'index.php/contest/result/' + cid);
		};
		$scope.download_statistic = function(cid) {
			$("#downloader").attr('src', 'index.php/contest/fullresult/' + cid);
		};

		sortUser.toggle = sortUser.toggleFactory(data);

		$scope.data = data;
		$scope.info = info;
		$scope.startTime = startTime;
		$scope.est = est;
		$scope.sortUser = sortUser;
	}]);

