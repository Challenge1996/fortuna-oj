<h4><?=lang('add_tags')?></h4>
<hr />

<div id="ajaxarea" class="well"></div>

<script>
	$.get('index.php/main/tags/<?=$pid?>', function(data) {
		$('#ajaxarea').html(data);
	});
</script>

<hr />

<a href="#admin/dataconf/<?=$pid?>" class="btn btn-primary pull-right"><?=lang('next_step')?></a>

