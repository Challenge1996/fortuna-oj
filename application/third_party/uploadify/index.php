<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Uploadify Test</title>
<script src="jquery.js" type="text/javascript"></script>
<script src="jquery.uploadify.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="uploadify.css">
<script type="text/javascript">
	$(function() {
		$('#file_upload').uploadify({
			'swf'      : 'uploadify.swf',
			'uploader' : 'uploadify.php',
			'formData' : '
				""'
		});
	});
	</script>
</head>

<body>
<input id="file_upload" name="file_upload" type="file" multiple="true">
</body>
</html>