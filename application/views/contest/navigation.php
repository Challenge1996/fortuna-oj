<div class="navbar">
	<div class="navbar-inner">
		<ul class="nav">
			<li><a href="#contest"><i class="icon-arrow-left"></i></a></li>
			<li><a href="#contest/home/<?=$cid?>"><?=lang('home')?></a></li>
			<li><a href="#contest/problems/<?=$cid?>"><?=lang('problem')?></a></li>
			<li><a href="#contest/declaration_list/<?=$cid?>"><?=lang('declaration')?>
				<?php if ($declaration_count > 0) echo "<span class=\"badge badge-info>$declaration_count</span>"; ?>
			</a></li>
			<li><a href="#contest/status/<?=$cid?>"><?=lang('status')?></a></li>
			<li><a href="#contest/standing/<?=$cid?>"><?=lang('standing')?></a></li>
			<li><a href="#contest/statistic/<?=$cid?>"><?=lang('statistic')?></a></li>
		</ul>
	</div>
</div>