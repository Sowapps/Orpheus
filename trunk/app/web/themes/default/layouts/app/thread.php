<?php
global $USER;
HTMLRendering::useLayout('page_skeleton');

displayReportsHTML();

?>
<div class="row">
	<div class="col-xs-8 col-xs-offset-2">

<h1>Community Thread</h1>

<div class="row">
<?php
if( SiteUser::isLogged() ) {
	displayReportsHTML();
	?>
	<form method="POST" role="form"><?php echo $FORM_TOKEN; ?>
	<fieldset>
		<legend>Post a new message (as <?php echo $USER; ?>)</legend>
		<div class="form-group">
			<textarea class="form-control" rows="3" name="tm[content]" placeholder="Enter your message..."></textarea>
		</div>
		<button name="submitAdd" type="submit" class="btn btn-primary pull-right">Post</button>
	</fieldset>
	</form>
	<?php
} else {
	?><p>Maybe you could <a href="<?php _u('user_login'); ?>">sign in</a> to participate to this thread ?!</p><?php	
}
?>
</div>
<div class="row">
	<h4>Thread</h4>
	<form method="POST" role="form"><?php echo $FORM_TOKEN; ?>
	<ul class="list-group">
<?php
foreach( ThreadMessage::getLastOnes() as $tm ) {
// 			<span class="badge">14</span>
	echo '
		<li class="list-group-item">
			'.($USER_CAN_THREADMESSAGE_MANAGE ? '<div class="btn-group tm-actions"><button name="submitDelete['.$tm->id().']" type="submit" class="btn btn-default"><i class="fa fa-trash-o"></i></button></div>' : '').'
			['.$tm->getAdaptiveDate().'] <b>'.escapeText($tm->user_name).'</b> : '.$tm.'</li>';
}
?>
	</ul>
	</form>
</div>

	</div>
</div>
<style>
.tm-actions {
	position: absolute;
	top:	0;
	right:	0;
	display:	none;
}
.list-group-item:hover .tm-actions {
	display:	block;
}
.tm-actions > button {
	padding:	4px 8px;
}
</style>
