<?php $this->load->model('user'); ?>

<?php foreach ($data as $row): ?>
	<div class='span11'>
		<div class='span2'>
			<div class='text-center'><img src='<?=$row->avatar?>' /></div>
			<div class='text-center'><span class='label label-info'><?=$row->user?></span></div>
			<div class='text-center'><?=$row->date?></div>
		</div>
		<div class='span10 well'>
			<span class='span9'><h4><?=$row->title?></h4></span>
			<?php if ($this->user->uid()==$row->uid || $this->user->is_admin()): ?>
				<span class='pull-right btn btn-link del' data-id='<?=$row->id?>'>Delete</span>
			<?php endif; ?>
			<br /><hr />
			<?=$row->content?>
		</div>
	</div>
<?php endforeach; ?>

<div class='span11'><hr /></div>

<div class='span6 pull-left'>
<form id='form' method='post' action='index.php/contest/forum/<?=$cid?>'>

	<input type="hidden" name="title" id="title_data" />
	<div class='span12'>
		<div class='span2'><strong>Title</strong></div>
		<div class='span10'><h3><div class="well" id="title" style="padding: 5px; text-align:center" contenteditable="true">
		</div></h3></div>
	</div>

	<textarea id="content_data" name="content" style="display:none"></textarea>
	<div class='span12'>
		<div class='span2'><strong>Content</strong></div>
		<div class="span10 well" id="content" contenteditable="true"></div>
	</div>

	<span id='submit' class='btn btn-primary pull-right'>Post</span>
</form>
</div>

<script>
	CKEDITOR.disableAutoInline = true;
	CKEDITOR.config.forcePasteAsPlainText = true;
	CKEDITOR.config.htmlEncodeOutput = true;
	CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
	CKEDITOR.config.shiftEnterMode = CKEDITOR.ENTER_BR;
	CKEDITOR.on( 'instanceReady', function( ev ) {
		ev.editor.dataProcessor.writer.lineBreakChars = '';
	});
	var title = CKEDITOR.inline('title');
	CKEDITOR.instances.title.setMode('source');
	CKEDITOR.instances.title.blockless = true;
	var content = CKEDITOR.inline('content');
	CKFinder.setupCKEditor(null, "application/third_party/ckfinder/");
	
	$('#submit').click(function(){
		$('#submit').addClass('disabled');
		$('#title_data').val(title.getData());
		$('#content_data').val(content.getData());
		$('#form').ajaxSubmit({
			success: function(content)
				{
					$('#page_content').html(content);
				}
		});
	});

	$('.del').click(function(){
		if (!confirm('Sure to delete?')) return;
		$(this).addClass('disabled');
		//console.log($(this).attr('data-id'));
		//return;
		$.get('index.php/contest/forum/<?=$cid?>?del='+$(this).attr('data-id'),function(data){
			$('#page_content').html(data);
		});
	});
</script>
