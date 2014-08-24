<link rel="stylesheet" href="application/third_party/highlight/styles/xcode.css">
<script src="application/third_party/highlight/highlight.pack.js"></script>

<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>
<div class="code"><pre><code id="src" class="<?php
	switch($language){
		case 'C++': echo 'cpp'; break;
		case 'C++11': echo 'cpp'; break;
		case 'C': echo 'c'; break;
		case 'Pascal': echo 'delphi'; break;
		case 'Ruby': echo 'ruby'; break;
		case 'Python': echo 'python'; break;
		case 'Java': echo 'java'; break;
		case 'Bash': echo 'bash'; break;
		case 'PHP': echo 'php'; break;
		case 'javascript': echo 'javascript'; break;
		default: echo 'no-highlight';
	}
?>"><?=htmlentities($code);?></code></pre></div>

<script type="text/javascript">
	function code(){ hljs.initHighlightingOnLoad(); }
	
	$(document).ready(function() {
		$('#src').each(function(i, e) {hljs.highlightBlock(e)});
	});
</script>
