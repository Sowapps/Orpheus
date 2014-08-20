<?php

try {
	if( isPOST('submitAdd') ) {
		if( !SiteUser::isLogged() ) {
			SiteUser::throwException('forbiddenOperation');
		}
		$input	= POST('tm');
		$input['user_id']	= $USER->id();
		$input['user_name']	= $USER->fullname;
		ThreadMessage::create($input, array('content', 'user_id', 'user_name'));
		reportSuccess('successCreate', ThreadMessage::getDomain());
	}
} catch(UserException $e) {
	reportError($e);
}

?>
<div class="row">
	<div class="col-xs-6">

<div class="row">
<?php
if( SiteUser::isLogged() ) {
	displayReportsHTML();
	?>
	<form method="POST" role="form">
	<fieldset>
		<legend>Post a new message (<?php echo $USER; ?>)</legend>
		<textarea rows="3" name="tm[content]"></textarea>
		<button name="submitAdd" type="submit" class="btn btn-primary">Post</button>
	</fieldset>
	</form>
	<?php
} else {
	?><p>Maybe you could <a href="<?php _u('user_login'); ?>">sign in</a> to participate to this thread ?!</p><?php	
}
?>
</div>
<div class="row">
	<ul class="list-group">
<?php
foreach( ThreadMessage::get(array('orderby' => 'create_date')) as $tm ) {
// 			<span class="badge">14</span>
	echo '
		<li class="list-group-item">'.$tm.'</li>';
}
?>
	</ul>
</div>

	</div>
</div>