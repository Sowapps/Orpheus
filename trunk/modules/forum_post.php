<?php
/* @var $USER SiteUser */

HTMLRendering::addJSFile('external/jquery.hotkeys.js');

HTMLRendering::addJSURL('https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.js');
HTMLRendering::addCSSURL('https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.css');

HTMLRendering::addJSFile('debug.js');
HTMLRendering::addJSFile('bootstrap-wysiwyg.min.js');
HTMLRendering::addCSSFile('forum');
HTMLRendering::addJSFile('forum-forums.js');

$TOPBAR_CONTENTS	= '
<form class="navbar-form navbar-right">
	'.( !SiteUser::is_login() ? '
	<button type="button" class="login-btn btn btn-default" data-toggle="modal" data-target="#connectForm">Log in<span class="fa fa-power-off"></span></button>' : '').'
	<input type="text" placeholder="What are you lookin\' for ?" autofocus="autofocus" class="form-control search-query">
	<button type="submit" class="btn btn-default" name="submitSearch">Search</button>
</form>';

try {
	$Post	= ForumPost::load($Action);
} catch( UserException $e ) {
	reportError($e); displayReportsHTML(); return;
}

debug('POST', POST());
try {
	if( isPOST('submitCreatePost') ) {
		if( !SiteUser::is_login() ) { SiteUser::throwException('userRequired'); }
		$post	= POST('newpost');
// 		$post['user_id']	= $USER->id();
// 		$post['user_name']	= $USER->fullname;
// 		$post['published']	= 1;
// 		$post['post_date']	= sqlDatetime();
		$post['parent_id']	= 0;// Use addAnswer() to add an answer
// 		text('ForumPost domain is : '.ForumPost);
		ForumPost::make($post);
	}
} catch( UserException $e ) {
	reportError($e);
}

displayReportsHTML();

function displayPost(ForumPost $post) {
	$author	= $post->getAuthor();
	echo '
	<article id="'.$post->id().'">
		<div class="post_head">
<!--			<img src="../../themes/default/images/empty_140.png" class="user_avatar img-rounded">-->
			<div class="post_infos">
				<a href="'.$author->getLink().'">'.$author.'</a>At '.$post->getCreationDate().'
			</div>
		</div>
		<div class="post_body">'.$post->getMessage().'</div>
<!--		<div style="clear: both; height: 10px;"></div>-->
	</article>';
}

echo '
<div class="thread_head">
	<ul class="breadcrumb">';
$breadcrumb	= '';
$prev	= $Post->forum_id;
while( $prev ) {
	$forum	= Forum::load($prev);
	$prev	= $forum->parent_id;
	$breadcrumb	= '
		<li><a href="'.$forum->getLink().'">'.$forum.'</a></li>'.$breadcrumb;
// 	$breadcrumb[]	= $forum;
}

echo $breadcrumb;

echo '
	</ul>
	<h3><a href="'.$Post->getLink().'">'.$Post.'</a></h3>
</div>
<div class="postlist">';
displayPost($Post);
foreach( $Post->getAnswers() as $post ) {
	displayPost($post);
}
echo '
</div>';
?>

<div id="newThreadForm" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" class="">
<!-- form-horizontal -->
	<input type="hidden" name="newpost[forum_id]" id="ntf_fid" />
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Create new Thread</h3>
	</div>
	<div class="modal-body" style="text-align: center;">
		<h3 id="ntf_title"></h3>
		<div class="control-group">
			<label class="control-label" for="inputName">Title</label>
			<input class="form-control" type="text" name="newpost[name]" id="inputName" placeholder="Enter the title of your new thread">
		</div>
		
		<div class="btn-toolbar mt5 mb5" data-role="editor-toolbar" data-target="#editor">
			<div class="btn-group">
				<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Font Size"><i class="fa fa-text-height"></i>&nbsp;<b
					class="caret"></b>
				</a>
				<ul class="dropdown-menu">
					<li><a data-edit="fontSize 5"><font size="5">Huge</font></a></li>
					<li><a data-edit="fontSize 3"><font size="3">Normal</font></a></li>
					<li><a data-edit="fontSize 1"><font size="1">Small</font></a></li>
				</ul>
			</div>
			<div class="btn-group">
				<a class="btn btn-default" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold"></i></a>
				<a class="btn btn-default" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic"></i></a>
				<a class="btn btn-default" data-edit="strikethrough" title="Strikethrough"><i class="fa fa-strikethrough"></i></a>
				<a class="btn btn-default" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn btn-default" data-edit="insertunorderedlist" title="Bullet list"><i class="fa fa-list-ul"></i></a>
				<a class="btn btn-default" data-edit="insertorderedlist" title="Number list"><i class="fa fa-list-ol"></i></a>
				<a class="btn btn-default" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="fa fa-indent"></i></a>
				<a class="btn btn-default" data-edit="indent" title="Indent (Tab)"><i class="fa fa-outdent"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn btn-default" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left"></i></a>
				<a class="btn btn-default" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center"></i></a>
				<a class="btn btn-default" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right"></i></a>
				<a class="btn btn-default" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify"></i></a>
			</div>
			<div class="btn-group">
				<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Hyperlink"><i class="fa fa-link"></i></a>
				<div class="dropdown-menu input-append">
					<input class="span2" placeholder="URL" type="text" data-edit="createLink" />
					<button class="btn" type="button">Add</button>
				</div>
				<a class="btn btn-default" data-edit="unlink" title="Remove Hyperlink"><i class="fa fa-cut"></i></a>
				<a class="btn btn-default" title="Insert picture (or just drag & drop)" id="pictureUploadBtn"><i class="fa fa-picture-o"></i></a>
				<input id="pictureUploadInput" style="display: none;" type="file" data-role="magic-overlay" data-target="#pictureBtn" data-edit="insertImage" />
			</div>
			<div class="btn-group">
				<a class="btn btn-default" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="fa fa-undo"></i></a>
				<a class="btn btn-default" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="fa fa-repeat"></i></a>
			</div>
		</div>
		<textarea name="newpost[message]" class="lead" data-editor="editor" placeholder="Enter your message here..." style="display: none;"></textarea>
		
	</div>
	<div class="modal-footer">
		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button type="submit" name="submitCreatePost" class="btn btn-primary">Save</button>
	</div>
</form>
</div>
</div>
</div>

<style>
#editor {
	max-height: 250px;
	height: 250px;
	background-color: white;
	border-collapse: separate; 
	border: 1px solid rgb(204, 204, 204); 
	padding: 4px; 
	box-sizing: content-box; 
	-webkit-box-shadow: rgba(0, 0, 0, 0.0745098) 0px 1px 1px 0px inset; 
	box-shadow: rgba(0, 0, 0, 0.0745098) 0px 1px 1px 0px inset;
	border-top-right-radius: 3px; border-bottom-right-radius: 3px;
	border-bottom-left-radius: 3px; border-top-left-radius: 3px;
	overflow: scroll;
	outline: none;
}
#voiceBtn {
  width: 20px;
  color: transparent;
  background-color: transparent;
  transform: scale(2.0, 2.0);
  -webkit-transform: scale(2.0, 2.0);
  -moz-transform: scale(2.0, 2.0);
  border: transparent;
  cursor: pointer;
  box-shadow: none;
  -webkit-box-shadow: none;
}
div[data-role="editor-toolbar"] {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.dropdown-menu a {
  cursor: pointer;
}

.cover {
	width: 100%;
	height: 100%;
	position: fixed;
	top: 0;
	left: 0;
/* 	opacity: 0.5; */
	background: #FFFFFF;
	z-index: 1500;
}
</style>
