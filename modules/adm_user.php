<?php
	
try {
	$user	= User::load($Action);
} catch(UserException $e) {
	reportError($e);
// 	displayReportsHTML();
	return;
}
	
if( isPOST('submitUpdate') ) {
	try {
		$result = $user->update(POST('user'), array('fullname', 'email', 'password', 'accesslevel'));
		if( $result ) {
			reportSuccess('successEdit', User::getDomain());
		}
	} catch(UserException $e) {
		reportError($e, User::getDomain());
	}
}
$formData = array('user' => $user->getValue());

$ModuleTitle = 'Utilisateur '.$user;
// displayReportsHTML();

require_once ORPHEUSPATH.LIBSDIR.'src/admin-form.php';
?>

<form method="POST">

<div class="row">
	<div class="col-lg-6">
		<div class="adduserform">
		<h2>Éditer un utilisateur</h2>
		<div class="form-group">
			<label>Nom</label>
			<?php _adm_htmlTextInput('user/fullname', 'form-control'); ?>
		</div>
		<div class="form-group">
			<label>Email</label>
			<?php _adm_htmlTextInput('user/email', 'form-control', 'autocomplete="off"'); ?>
		</div>
		<div class="form-group">
			<label>Mot de passe</label>
			<?php _adm_htmlPassword('user/password', 'class="form-control" autocomplete="off"'); ?>
		</div>
		<div class="form-group">
			<label>Accréditations</label>
			<select name="user[accesslevel]" class="form-control">
				<?php echo htmlOptions('user/accesslevel', User::getAppRoles(), null, OPT_LABEL2VALUE, 'role_', User::getDomain()); ?>
			</select>
		</div>
		<button class="btn btn-default" type="submit" name="submitUpdate">Enregistrer</button>
		</div>
	</div>
</div>

</form>