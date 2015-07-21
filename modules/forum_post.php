<?php
/* @var $USER User */

HTMLRendering::addJSFile('external/jquery.hotkeys.js');

HTMLRendering::addJSURL('https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.js');
HTMLRendering::addCSSURL('https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.css');

HTMLRendering::addJSFile('debug.js');
HTMLRendering::addJSFile('bootstrap-wysiwyg.min.js');
HTMLRendering::addCSSFile('forum');
HTMLRendering::addJSFile('forum-forums.js');

$TOPBAR_CONTENTS	= '
<form class="navbar-form navbar-right">
	'.( !User::is_login() ? '
	<button type="button" class="login-btn btn btn-default" data-toggle="modal" data-target="#connectForm">Log in<span class="fa fa-power-off"></span></button>' : '').'
	<input type="text" placeholder="What are you lookin\' for ?" autofocus="autofocus" class="form-control search-query">
	<button type="submit" class="btn btn-default" name="submitSearch">Search</button>
</form>';

try {
	/* @var $sPost ForumPost */
	$Post	= ForumPost::load($Action);
	$MODTITLE	= "$Post";
} catch( UserException $e ) {
	reportError($e); displayReportsHTML(); return;
}

// debug('POST', POST());
try {
	if( isPOST('submitAnswer') ) {
		if( !User::is_login() ) { User::throwException('userRequired'); }
		$sPost	= POST('postid') ? ForumPost::load(POST('postid')) : $Post;
		$sPost->addAnswer(POST('answer'));
		reportSuccess('successAddAnswer');
	} else
	if( isPOST('submitDelete') ) {
		$sPost	= ForumPost::load(POST('submitDelete'));
		if( empty($USER) || !$USER->canForumPostDelete(CRAC_CONTEXT_RESOURCE, $post) ) { User::throwException('forbiddenOperation'); }
		$sPost->remove();
	}
} catch( UserException $e ) {
	reportError($e);
}

displayReportsHTML();

function displayPost(ForumPost $post) {
	$author	= $post->getAuthor();
	/*
			<img src="../../themes/default/images/empty_140.png" class="user_avatar img-rounded">
		<div style="clear: both; height: 10px;"></div>
	*/
	echo '
	<article id="Post-'.$post->id().'" data-id="'.$post->id().'">
		<a class="post_meta" href="'.$post->getThreadLink().'">#'.$post->id().' | '.$post.'</a>
		<div class="post_head">
			<div class="post_infos"><a href="'.$author->getLink().'">'.$author.'</a>At '.$post->getCreationDate().'</div>
			<div class="btn-group btn-group-xs">
				<a href="#ReplyEditor" class="btn btn-default replybtn">Reply <i class="fa fa-reply"></i></a>'.
				((!empty($USER) && $USER->canForumPostUpdate(CRAC_CONTEXT_RESOURCE, $post)) ? '<a href="#" class="btn btn-default" title="Edit the post"><i class="fa fa-edit"></i></a>' : '').
				((!empty($USER) && $USER->canForumPostDelete(CRAC_CONTEXT_RESOURCE, $post)) ? '<button type="submit" name="removePost['.$post->id().']" class="btn btn-default" title="Delete the post"><i class="fa fa-trash-o"></i></button>' : '').
			'</div>
		</div><div class="post_body">'.$post->getMessage().'</div>
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
		<li class="active">Reading post</li>
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

<form method="post">
<h3 id="answerEditorTitle">Your answer</h3>
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
	<button class="btn btn-primary pull-right" name="submitAnswer" type="submit">Save</button>
</div>
<textarea name="answer[message]" class="lead" data-editor="editor" placeholder="Enter your message here..." style="display: none;"></textarea>
<input type="hidden" name="postid" id="postid" />
</form>