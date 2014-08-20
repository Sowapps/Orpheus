<?php

if( isPOST('submitUpdate') ) {
	$input	= POST('user');
	$fields	= array('fullname', 'email');
	if( !empty($input['password']) ) {
		$fields[]	= 'password';
	}
	if( $USER->update($input, $fields) ) {
		reportSuccess('Settings saved.');
	}
	
	displayReportsHTML($noReportErrors);
}
$formData = $USER->all;

?>
<form method="POST">

<div class="row">
	<div class="col-lg-6">
		<h2>Settings</h2>
		<div class="form-group">
			<label>Displayed name</label>
			<?php _adm_htmlTextInput('user/fullname', 'form-control'); ?>
		</div>
		<div class="form-group">
			<label>Email</label>
			<?php _adm_htmlTextInput('user/email', 'form-control', 'autocomplete="off"'); ?>
		</div>
		<div class="form-group">
			<label>New password</label>
			<?php _adm_htmlPassword('user/password', 'class="form-control" autocomplete="off"'); ?>
		</div>
		<div class="form-group">
			<label>Confirm password</label>
			<?php _adm_htmlPassword('user/password_conf', 'class="form-control" autocomplete="off"'); ?>
		</div>
		<button class="btn btn-default" type="submit" name="submitUpdate">Save</button>
	</div>
</div>

</form>