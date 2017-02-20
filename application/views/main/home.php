<div style="text-align:center; padding: 5px">
	<div class="hero-unit">
		<h2 class="text-error"><?=lang('welcome_to')?> <?=$this->config->item('oj_title')?>!</h2>
		<p>This OJ is based on Ubuntu Server 14.04 LTS amd64.</p>
		<p>Powered by Codeigniter / Bootstrap.</p>
	</div>
	<div class="container-fluid">
		<h3>FAQ: 为什么我的程序在本地跑没事，交到你的OJ上就挂了(ノ=Д=)ノ┻━┻</h3>
		<ul class="row-fluid text-left">
			<li>你的数组越界了，你的数组越界了，你的数组越界了！一些隐藏的数组越界可能在本地不会报错，而到了OJ上由于运行环境发生了变化，才发生错误。数组越界不仅可能导致运行错误，也可能导致答案错误或超时等，这是由于数组越界干扰了其他内存导致的。</li>
			<li>不要使用gets等读取一行，因为这些函数使用换行符来判断行结束（当然也不要手动判断换行符）。Windows下换行符是\r\n，而Linux下换行符是\r。假设此题的数据是在Windows下生成的，那么他的换行符是\r\n，而OJ上的程序是在Linux下编译的，读取的换行符是\n，这样就错了。</li>
			<li>请记得使用%lld而非%I64d。</li>
			<li>如果出现了编译错误，记得旧版g++可能不需要#include&lt;cstring&gt;，而OJ上的g++需要。</li>
			<li>如果你内存超限了，记住OJ是64位机器，指针占8字节内存。</li>
			<li>你的程序可能有精度等其他问题。</li>
			<li>如果很多人都挂了，也可能是SPJ由于上述原因挂了。</li>
		</ul>
	</div>
	<div class="container-fluid">
		<div class="row-fluid text-left">
			<?=count($online)?> User(s) Online:
			<?php foreach ($online as $name): ?>
				<a href="#users/<?=$name?>"><span class="label label-info"><?=$name?></span></a>
			<?php endforeach; ?>
		</div>
	</div>
	<hr />
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span3">
				<h4>Available languages</h4>
				<ul class="unstyled text-success">
					<li><strong>C</strong></li>
					<li><strong>C++ including C++11(0x)</strong></li>
					<li><strong>Pascal</strong></li>
				</ul>
				<br />
	<!--			Currently Java and Python are unavailable-->
			</div>
			<div id='extra-info' class="span9">
				<script> $('#extra-info').load('index.php/main/recentcontest'); </script>
				<!--
				OJ现已迁移至新版本YAUJ，主要改进了后端数据同步的稳定性和题目配置的灵活性。<br />
	鉴于某些SPJ使用了中文输出导致传输过程中乱码，迁移过程中没有处理，请找管理员重测<br />
				由于数据量较大，题目采用懒惰迁移，初次点开某题时可能卡顿不超过一分钟，请耐心等待。<br />
				原有的提交并未重测，鉴于机器配置不同，重测后得分可能发生变化。<br />
				某些题目SPJ编写不规范，评测机编译器版本较新，SPJ可能编译错误。如怀疑有此情况发生，请联系管理员。<br />
				鉴于代码相似度判断表现不佳，目前暂时取消。<br />
	如遇代码提交框行号显示混乱，请清除浏览器缓存后刷新<br />
				-->
				<!--
				<h4>Changelog</h4>
					<dl style="text-align: left" class="dl-horizontal">
						<dt>Oct.17 2014</dt>
						<dd>现在可以通过邮件重置密码，没有填写邮箱地址的可以在用户设置中填写，地址不被公开</dd>
						<dt>Aug.23 2014</dt>
						<dd>支持添加书签</dd>
						<dd>支持在statistic选择是否加载比赛前的提交（题目相当多时动画会相当卡）</dd>
						<dt>Aug.19 2014</dt>
						<dd>支持修改Description</dd>
						<dt>Aug.15 2014</dt>
						<dd>Custom Test新增输入文件上传</dd>
						<dt>Jul.12 2013</dt>
						<dd>Mail功能完成. 支持实时拉取数据.</dd>
						<dt>Apr.25  2013</dt>
						<dd>支持上传头像</dd>
						<dt>Apr.24  2013</dt>
						<dd>现在可以通过CustomTest自行在服务器上测试程序</dd>
						<dt>Apr.3  2013</dt>
						<dd>用户信息页面中新增各种统计.</dd>
						<dt>Apr.3  2013</dt>
						<dd>设置中可调ProblemSet中每页题数和Status中每页提交数</dd>
						<dt>Mar.17 2013</dt>
						<dd>现在未通过提交可以下载第一个未通过的数据。</dd>
					</dl>
				-->
				<!--
					Jan.13 2013:	现在管理员可以在Group页面看到Task的统计信息。 
				-->
			</div>
		</div>
	</div>
</div>
