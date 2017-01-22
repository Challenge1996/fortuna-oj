var loadedJsFile = new Array()

function loadJsFile ( name, src, success ) {
	if ($.inArray(name, loadedJsFile) == -1) {
		loadedJsFile.push(name)
		$.getScript(src, success).fail(function(jqxhr, settings, exception){console.log([name, settings])});
	} else success();
}

if ( ! ('onhashchange' in window)) {
	alert('Not Supported!');
	/*var old_href = location.href;
	setInterval( function() {
		var new_href = location.href;
		if (old_href != new_href){
			old_href = new_href;
			on_hash_change.call(window, {type: 'hashchange', 'newURL' : new_href, 'oldURL': old_href});
		}
	}, 100 );*/
	
} else if ( window.addEventListener ) {
	window.addEventListener('hashchange', on_hash_change, false);
	
} else if ( window.attachEvent ) {
	window.attachEvent('onhashchange', on_hash_change);
}

var notificationHandle = window.Notification || window.mozNotification || window.webkitNotification;
if (notificationHandle) notificationHandle.requestPermission();

function myNotification(_title, _body, _tag)
{
	this.title = _title;
	this.body = _body;
	this.tag = _tag;
	this.show = function()
	{
		if (!notificationHandle) return;
		var instance = new notificationHandle(this.title, {
			"body": this.body,
			"tag": this.tag,
			"icon": '/favicon.ico',
		});
		instance.onclick = function() { console.log('a'); window.focus(); }
	}
}

var MailNotification = new myNotification(
	'Fortuna Online Judge',
	'You have some new mails!',
	'mail'
);

var addRequest = {}, firstNotification = true;
function getServerPushData() {
	var url = "index.php/background/push";
	if (firstNotification) url += '/1';
	$.ajax({
		type:"POST",
		url:url,
		data: addRequest,
		success: function(data) {
			if (data != '' && data != null) {
				firstNotification = false;
				data = eval('(' + data + ')');

				if (data.m > 0) {
					$('#unread_mail_count').html(data.m);
					MailNotification.show();
				} else
					$('#unread_mail_count').html('');

				if (data.c > 0)
					$('#running_contest_count').html(data.c);
				else
					$('#running_contest_count').html('');

				if (data.submitNotification != undefined)
				{
					var SubmitNotification = new myNotification(
						'Fortuna Online Judge',
						'Don\'t forget to submit problem '
							+ data.submitNotification
							+ ' in contest '
							+ addRequest['submitNotification']
							+ ' !',
						'submit'
					);
					SubmitNotification.show();
				}
			}
			setTimeout('getServerPushData()', 3000);
		},
		error: function() {
			setTimeout('getServerPushData()', 3000);
		}
	})
}

function get_cookie(name){
	if (document.cookie.length > 0){
		start = document.cookie.indexOf(name + '=');
		if (start != -1){
			start += name.length + 1;
			end = document.cookie.indexOf(';', start);
			if (end == -1) end = document.cookie.length;
			return unescape(document.cookie.substring(start, end));
		}
	}
	return '';
}

function randomize(url) {
	if (url.indexOf('?') == -1) url += '?';
	if (url.indexOf('seed') == -1) url += '&seed=' + Math.random();
	return url;
}

function hash_to_url(hash) {
	if (hash[0] == '#') hash = hash.substr(1);
	url = "index.php/" + hash;
	return url;
}

function set_page_content(selector, url, success) {
	addRequest = {};
	url = randomize(url);
	$('.overlay').css({'z-index': '1000', 'display': 'block'});
	$('.overlay').animate({opacity: '0.5'}, 250);
	$.ajax({
		type: "GET",
		url: url,
		success: function(data){
			$(selector).hide();
			$('.overlay').css({'z-index': '-1000', 'display': 'none'});
			$('.overlay').animate({opacity: '0'}, 250);
			$(selector).html(data);
			$(selector).fadeIn(250);
			if (success != void 0) success();
		},
		error: function(xhr, statusText, error){
			$('.overlay').css({'z-index': '-1000', 'display': 'none'});
			$('.overlay').animate({opacity: '0'}, 250);
			$(selector).html('<div class="alert"><strong>Error: ' + ' ' + error + '</strong></div>');
		}
	});
}

function access_page(hash, success){
	url = randomize(hash_to_url(hash));
	refresh = arguments.length == 3 && arguments[2] == false ? false : true;
	if (refresh){
		$.get(url, function(){
			set_page_content('#page_content', hash_to_url(window.location.hash), success);
		});
	}else{
		$.get(url, success);
	}
}

function load_page(url) {
	window.location.hash = url;
	return false;
}

function refresh_page() {
	if (typeof refresh_flag != 'undefined'){
		clearTimeout(refresh_flag);
		delete refresh_flag;
	}
 	set_page_content('#page_content', hash_to_url(window.location.hash));
}

function on_hash_change() {
	if (window.preventHashchange) {
		window.preventHashchange = false;
		return;
	}
	
	if (typeof refresh_flag != 'undefined') {
		clearTimeout(refresh_flag);
		delete refresh_flag;
	}
	
	document.title = OJTitle;
	set_page_content('#page_content', hash_to_url(window.location.hash));
}

function init_framework() {
	window.preventHashchange = false;
	
	if (window.location.hash != '') set_page_content('#page_content', hash_to_url(window.location.hash));
	else load_page('main/home');
	var priviledge = get_cookie('priviledge');
	if (priviledge == 'admin') $('.nav_admin').attr({style:"display:block"});
	else $('.nav_admin').attr({style:"display:none"});

	getServerPushData();
}

function load_userinfo() {
	set_page_content('#userinfo', "index.php/main/userinfo");
}

function login_submit() {
	$('#login_field').modal('hide');
	$('#new_passwd').val(saltl+$('#ori_passwd').val()+saltr);
	$('#login_form').ajaxSubmit({
		success: function login_success(responseText, stautsText){
			if (responseText == 'success'){
				load_userinfo();
				set_page_content('#page_content', hash_to_url(window.location.hash));
				var priviledge = get_cookie('priviledge');
				if (priviledge == 'admin') $('.nav_admin').attr({style:"display:block"});
				else $('.nav_admin').attr({style:"display:none"});
			} else $('#page_content').html(responseText);
		}
	});
	return false;
}

function register_submit() {
	$('#register_field').modal('hide');
	$('#register_form').ajaxSubmit({
		success: function(responseText, statusText){
			if (responseText == 'success') load_page('main/home');
			else $('#page_content').html(responseText);
		}
	});
	return false;
}

$(document).ready( function() {
	$('.case').click(function() {
		var attr = "." + $(this).attr("id");
		$(this).siblings(attr).slideToggle(5);
	}),
	
	$('#navigation li a').click( function() {
		$('#navigation li').removeClass('active');
		$(this).parent().addClass('active');
	}),
	
	$('#logout').live('click', function() {
		access_page('main/logout', load_userinfo);
	}),
		
	$('#nav_toggle').toggle(function() {
			$('#navigation').animate({ left: '-200px', width: '0' }, 320);
			$('#icon_nav_toggle').removeClass();
			$('#icon_nav_toggle').addClass('icon-arrow-right');
		},
		function() {
			$('#navigation').animate({ left: '0', width: '100px' }, 320);
			$('#icon_nav_toggle').removeClass();
			$('#icon_nav_toggle').addClass('icon-arrow-left');
		}
	);
})

    $(document).ready(function() {

    });

function initialize_chart() {
    // Radialize the colors
	Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
	    return {
	        radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
	        stops: [
	            [0, color],
	            [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
	        ]
	    };
	});
}

function render_pie(selector, title, data) {
	// Build the chart
	$(selector).highcharts({
		chart: {
			plotBorderWidth: null,
			backgroundColor: 'transparent',
			width: 500
		},
		title: {
			text: title
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage}%</b>',
			percentageDecimals: 2
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					color: '#000000',
					connectorColor: '#000000',
					formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage) +' %';
					}
				}
			}
		},
		series: data
	});
}

function render_column(selector, title, data) {
	$(selector).highcharts({
		chart: {
			type: 'column',
			backgroundColor: 'transparent',
			width: 500
		},
		title: { text: title },
		xAxis: { categories: ['Categories'] },
		yAxis: {
			min: 0,
			title: { text: 'Count' }
		},
		tooltip: {
			headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
						'<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
			footerFormat: '</table>',
			shared: true,
			useHTML: true
		},
		plotOptions: {
			column: {
				pointPadding: 0.2,
				borderWidth: 0
			}
		},
		series: data
	});
}

function object_content(obj)
{
	var s;
	if (typeof(obj)=="object")
	{
		s='{';
		for (var p in obj)
			s+=p+':'+object_content(obj[p])+',\n';
		s+=',\n}'
	} else
		s=obj;
	return s
}

function deparam(querystring)
{
	// remove any preceding url and split
	if (querystring.indexOf('?') == -1) querystring += '?';
	querystring = querystring.substring(querystring.indexOf('?')+1).split('&');
	var params = {}, pair, d = decodeURIComponent;
	// march and parse
	for (var i = querystring.length - 1; i >= 0; i--) {
		pair = querystring[i].split('=');
		params[d(pair[0])] = d(pair[1]);
	}
	delete params[''];
	return params;
}

