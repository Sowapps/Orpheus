<?php

try {
	if( isPOST('submitUpdate') ) {
		$input	= POST('user');
		$fields	= array('fullname', 'email');
		if( !empty($input['password']) ) {
			$fields[]	= 'password';
		}
		if( $USER->update($input, $fields) ) {
			reportSuccess('Settings saved.');
		}
	}
} catch(UserException $e) {
	reportError($e);
}
$formData = array('user'=>$USER->all);

require_once ORPHEUSPATH.LIBSDIR.'src/admin-form.php';

?>
<form method="POST">

<div class="row">
	<div class="col-lg-6 col-lg-offset-3">
		<h2>Settings</h2>
		<?php displayReportsHTML(); ?>
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