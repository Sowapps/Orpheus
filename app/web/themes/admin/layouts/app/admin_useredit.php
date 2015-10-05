<?php
HTMLRendering::useLayout('page_skeleton');
?>
<form method="POST">

<div class="row">
	<div class="col-lg-6">
		<div class="adduserform">
		<h2>Éditer un utilisateur</h2>
		<div class="form-group">
			<label>Nom</label>
			<?php _adm_htmlTextInput('user/fullname'); ?>
		</div>
		<div class="form-group">
			<label>Email</label>
			<?php _adm_htmlTextInput('user/email', '', 'autocomplete="off"'); ?>
		</div>
		<div class="form-group">
			<label>Mot de passe</label>
			<?php _adm_htmlPassword('user/password'); ?>
		</div>
		<?php
		if( $USER_CAN_USER_GRANT ) {
			?>
		<div class="form-group">
			<label>Accréditations</label>
			<select name="user[accesslevel]" class="form-control">
				<?php echo htmlOptions('user/accesslevel', User::getUserRoles(), null, OPT_LABEL2VALUE, 'role_', User::getDomain()); ?>
			</select>
		</div>
			<?php
		}
		?>
		<button class="btn btn-default" type="submit" name="submitUpdate">Enregistrer</button>
		</div>
	</div>
</div>

</form>
