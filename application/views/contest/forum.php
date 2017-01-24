<?php $this->load->model('user'); ?>

<?php foreach ($data as $row): ?>
	<div class='span11'>
		<div class='span2'>
			<div class='text-center'><img src='<?=$row->avatar?>' /></div>
			<div class='text-center'><span class='label label-info'><?=$row->user?></span></div>
			<div class='text-center'><?=$row->date?></div>
		</div>
		<div class='span10 well'>
			<span class='span9'><h4 class='title' data-id='<?=$row->id?>'><?=$row->title?></h4></span>
			<?php if ($this->user->uid()==$row->uid || $this->user->is_admin()): ?>
				<span class='pull-right btn btn-link del' data-id='<?=$row->id?>'>Delete</span>
			<?php endif; ?>
			<?php if ($this->user->uid()==$row->uid): ?>
				<span class='pull-right btn btn-link mdfy' data-id='<?=$row->id?>'>Modify</span>
			<?php endif; ?>
			<br /><hr />
			<div class='content' data-id='<?=$row->id?>'><?=$row->content?></div>
			<div class='span12'>
				<span class='pull-right btn btn-link hide-reply' style='display:none' data-id='<?=$row->id?>'>Hide</span>
				<span class='pull-right btn btn-link show-reply' data-id='<?=$row->id?>'>Reply(<?=$row->replyCnt?>)</span>
			</div>
			<div class='span12'>
				<div class='span10 offset1 reply-area' style='display:none' data-id='<?=$row->id?>'></div>
			</div>
		</div>
	</div>
<?php endforeach; ?>

<div class='span11'><hr /></div>

<div class='span6 pull-left'>
<form id='form' method='post' action='index.php/contest/forum/<?=$cid?>?post=1'>

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
	<span id='cancel' class='btn pull-right' style='display:none'>Cancel</span>
	<span id='modify' class='btn btn-primary pull-right' style='display:none'>Modify</span>
</form>
</div>

<script>
	CKEDITOR.disableAutoInline = true;
	CKEDITOR.config.forcePasteAsPlainText = true;
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

	$('#modify').click(function(){
		$('#modify').addClass('disabled');
		$('#title_data').val(title.getData());
		$('#content_data').val(content.getData());
		$('#form').ajaxSubmit({
			success: function(content)
				{
					$('#page_content').html(content);
				}
		});
	});

	$('#cancel').click(function(){
		$('#form').attr('action','index.php/contest/forum/<?=$cid?>?post=1');
		$('#cancel').hide();
		$('#modify').hide();
		$('#submit').show();
		$('.mdfy').each(function(){$(this).removeClass('disabled');});
	});

	$('.del').click(function(){
		if (!confirm('Sure to delete?')) return;
		$(this).addClass('disabled');
		$.get('index.php/contest/forum/<?=$cid?>?del='+$(this).attr('data-id'),function(data){
			$('#page_content').html(data);
		});
	});

	$('.mdfy').click(function(){
		$(this).addClass('disabled');
		$('#form').attr('action','index.php/contest/forum/<?=$cid?>?mdfy='+$(this).attr('data-id'));
		$('#submit').hide();
		$('#modify').show();
		$('#cancel').show();
		title.editable().setHtml($(".title[data-id='"+$(this).attr('data-id')+"']").html());
		content.editable().setHtml($(".content[data-id='"+$(this).attr('data-id')+"']").html());
		window.scrollTo(0,1e10);
	});

	$('.show-reply').click(function(){
		var id = Number($(this).attr('data-id'));
		$(".reply-area[data-id='"+id+"']").load('index.php/contest/reply/<?=$cid?>/'+id);
		$(".reply-area[data-id='"+id+"']").show();
		$(this).hide();
		$(this).parent().children('.hide-reply').show();
	});

	$('.hide-reply').click(function(){
		var id = Number($(this).attr('data-id'));
		$(".reply-area[data-id='"+id+"']").hide();
		$(this).hide();
		$(this).parent().children('.show-reply').show();
	});

	$('.reply-button').die('click');
	$('.reply-button').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var to = Number($(this).attr('data-to'));
		var content = $(".reply-input[data-id='"+id+"']").val();
		$(".reply-area[data-id='"+id+"']").load('index.php/contest/reply/<?=$cid?>/'+id+'/'+to+'?post=1',{'content':content});
	});

	$('.del-reply').die('click');
	$('.del-reply').live('click',function(){
		if (!confirm('Sure to delete?')) return;
		$(this).addClass('disabled');
		var id = Number($(this).attr('data-id'));
		var root = Number($(this).attr('data-root'));
		$(".reply-area[data-id='"+root+"']").load('index.php/contest/reply/<?=$cid?>/'+root+'?del='+id);
	});

	$('.mdfy-reply').die('click');
	$('.mdfy-reply').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var root = Number($(this).attr('data-root'));
		var content = $(".reply-content[data-id='"+id+"']").html();
		$(".reply-button[data-id='"+root+"']").hide();
		$(".reply-body[data-id='"+id+"']").css('background-color','#ffff66');
		$(".reply-input[data-id='"+root+"']").val(content);
		$(".reply-input[data-id='"+root+"']").focus();
		$(".reply-input[data-id='"+root+"']").removeClass('span10');
		$(".reply-input[data-id='"+root+"']").addClass('span9');
		$(".reply-modify[data-id='"+root+"']").attr('data-to',id);
		$(".reply-cancelm[data-id='"+root+"']").attr('data-to',id);
		$(".reply-modify[data-id='"+root+"']").show();
		$(".reply-cancelm[data-id='"+root+"']").show();
	});

	$('.reply-modify').die('click');
	$('.reply-modify').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var to = Number($(this).attr('data-to'));
		var content = $(".reply-input[data-id='"+id+"']").val();
		$(".reply-area[data-id='"+id+"']").load('index.php/contest/reply/<?=$cid?>/'+id+'?mdfy='+to,{'content':content});
	});

	$('.reply-cancelm').die('click');
	$('.reply-cancelm').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var to = Number($(this).attr('data-to'));
		$(".reply-modify[data-id='"+id+"']").hide();
		$(".reply-cancelm[data-id='"+id+"']").hide();
		$(".reply-body[data-id='"+to+"']").css('background-color','transparent');
		$(".reply-input[data-id='"+id+"']").val('');
		$(".reply-input[data-id='"+id+"']").removeClass('span9');
		$(".reply-input[data-id='"+id+"']").addClass('span10');
		$(".reply-button[data-id='"+id+"']").show();
	});

	$('.reply-reply').die('click');
	$('.reply-reply').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var root = Number($(this).attr('data-root'));
		$(".reply-body[data-id='"+id+"']").css('background-color','#ffff66');
		$(".reply-input[data-id='"+root+"']").focus();
		$(".reply-input[data-id='"+root+"']").removeClass('span10');
		$(".reply-input[data-id='"+root+"']").addClass('span9');
		$(".reply-button[data-id='"+root+"']").attr('data-to',id);
		$(".reply-cancelr[data-id='"+root+"']").attr('data-to',id);
		$(".reply-cancelr[data-id='"+root+"']").show();
	});

	$('.reply-cancelr').die('click');
	$('.reply-cancelr').live('click',function(){
		var id = Number($(this).attr('data-id'));
		var to = Number($(this).attr('data-to'));
		$(".reply-cancelr[data-id='"+id+"']").hide();
		$(".reply-body[data-id='"+to+"']").css('background-color','transparent');
		$(".reply-input[data-id='"+id+"']").val('');
		$(".reply-input[data-id='"+id+"']").removeClass('span9');
		$(".reply-input[data-id='"+id+"']").addClass('span10');
	});
</script>
