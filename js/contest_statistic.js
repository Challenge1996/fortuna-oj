angular.module('appStanding', [])
	.controller('StandingCtrl', ['$scope', 'data', 'info', 'startTime', 'est', function($scope, data, info, startTime, est) {
		$scope.range = function(lo, hi) {
			var ret = [];
			for (var i = Number(lo); i < Number(hi); i++) ret.push(i);
			return ret;
		};
		$scope.indexChar = function(i) {
			return String.fromCharCode(i + 65);
		};

		$scope.isset = function(x) {
			return (typeof(x) != 'undefined' && x !== null);
		}

		$scope.show_previous = false;
		$scope.oi = (info.contestMode == 'OI' || info.contestMode == 'OI Traditional');
		$scope.acm = (info.contestMode == 'ACM');

		$scope.download_result = function(cid) {
			$("#downloader").attr('src', 'index.php/contest/result/' + cid);
		};
		$scope.download_statistic = function(cid) {
			$("#downloader").attr('src', 'index.php/contest/fullresult/' + cid);
		};

		$scope.data = data;
		$scope.info = info;
		$scope.startTime = startTime;
		$scope.est = est;
	}]);

