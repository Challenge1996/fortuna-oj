<link rel="stylesheet" href="application/third_party/highlight/styles/xcode.css">
<script src="application/third_party/highlight/highlight.pack.js"></script>

<button class="btn btn-small" onclick="javascript:history.back()">Return</button>

<?php foreach ($data as $name => $content): ?>
	<div class="well">
		<div class="alert alert-info span12">
			<strong><?=$name?></strong>
			<span class="pull-right">
				<a id="down<?=$name?>" href='index.php/main/codedownload/<?=$sid?>/<?=$name?>' target='_blank'>
					Download
					<i class='icon-download-alt'></i>
				</a>
			</span>
		</div>
		<?php if ($content !== null): ?>
			<div class="code"><pre><code id="code<?=$name?>"></code></pre></div>
			<script type="text/javascript">
				var code = <?=json_encode($content)?>;
				var res = hljs.highlightAuto(code, ['delphi', 'c', 'cpp']);
				//console.log(res);
				$("#code<?=$name?>").html(res.value);
				switch (res.language)
				{
					case "delphi":
						$("#down<?=$name?>").attr("href",$("#down<?=$name?>").attr("href")+"?ext=pas");
						break;
					case "c":
						$("#down<?=$name?>").attr("href",$("#down<?=$name?>").attr("href")+"?ext=c");
						break;
					case "cpp":
						$("#down<?=$name?>").attr("href",$("#down<?=$name?>").attr("href")+"?ext=cpp");
						break;
				}
			</script>
		<?php else: ?>
			<i>Oops, I can't load such a large file.</i>
		<?php endif; ?>
	</div>
<?php endforeach; ?>

<!--<script type="text/javascript">
	hljs.initHighlightingOnLoad();
	
	$(document).ready(function() {
		$('.src').each(function(i, e) {hljs.highlightBlock(e)});
	});
</script>-->
