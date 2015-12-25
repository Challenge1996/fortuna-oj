var request = require('request');
var cheerio = require('cheerio');
var redis = require('redis');
var iconv = require('iconv-lite');

var client = redis.createClient();

function parse($)
{
	var table = $('table.table_text');
	var res = [];
	table.children('tr').each(function(){
		if ($(this).attr('class')=='table_header') return;
		var row = [];
		$(this).children('td').each(function(){
			row.push($(this).html());
		});
		res.push({
			'oj': row[1],
			'contestName': row[2],
			'startTime': row[4],
			'week': row[5],
			'status': row[6],
			'countDown': row[7]
		});
	});
	return res;
}

function save(data)
{
	console.log(data);
	client.set('recent_contest:form', data, function(){
		client.setex('recent_contest:valid', 3600, '', function(){
			console.log('done');
			process.exit();
		});
	});
}

client.on('ready', function(err){
	if (err)
	{
		console.log(err);
		return;
	}
	console.log('redis is ready');
	request.post
	(
		'http://acm.hdu.edu.cn/recentcontest/index.php',
		{ form: { type: 1 }, encoding: null },
		function (error, response, body)
		{
			if (!error && response.statusCode == 200)
			{
				body = iconv.decode(body, 'gb2312');
				form = parse(cheerio.load(body, {decodeEntities: false}));
				save(JSON.stringify(form));
			} else
			{
				console.log(error);
				console.log(response);
				process.exit();
			}
		}
	);
});

