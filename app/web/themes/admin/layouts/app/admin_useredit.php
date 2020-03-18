<?php

use Demo\User;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;

/**
 * @var HTMLRendering $rendering
 * @var HTTPRequest $Request
 * @var HTTPRoute $Route
 * @var HTTPController $Controller
 *
 * @var User $user
 */

$rendering->useLayout('page_skeleton');
?>

<div class="row">
	<div class="col-lg-6">
		
		<form method="POST" id="UserEditForm">
			
			<div style="display: none;">
				<input type="text" autocomplete="new-password"/>
				<input type="password" autocomplete="new-password"/>
			</div>
			<?php $rendering->useLayout('panel-default'); ?>
			
			<div class="form-group">
				<label><?php User::_text('name'); ?></label>
				<?php _adm_htmlTextInput('user/fullname'); ?>
			</div>
			<div class="form-group">
				<label><?php User::_text('email'); ?></label>
				<?php _adm_htmlTextInput('user/email', '', 'autocomplete="new-password"'); ?>
			</div>
			<div class="form-group">
			<label><?php User::_text('password'); ?></label>
			<?php _adm_htmlPassword('user/password', '', 'autocomplete="new-password" placeholder="'.User::text('fillToUpdate').'"'); ?>
		</div>
		<?php
		if( $USER_CAN_USER_GRANT ) {
			?>
		<div class="form-group">
			<label><?php User::_text('role'); ?></label>
			<select name="user[accesslevel]" class="form-control">
				<?php echo htmlOptions('user/accesslevel', array_filter(User::getUserRoles(), function($value) { return $value >= 0; }), null, OPT_LABEL2VALUE, 'role_', User::getDomain()); ?>
			</select>
		</div>
			<?php
		}
		if( $USER_CAN_USER_DELETE ) {
			?>
			<button class="btn btn-warning ml20" type="button"
					data-confirm_title="Supprimer <?php echo $user; ?>"
					data-confirm_message="Souhaitez-vous réellement supprimer l'utilisateur « <?php echo $user; ?> » ?"
					data-confirm_submit_name="submitDelete"><?php _t('delete'); ?></button>
			<?php
		}
		
		$rendering->endCurrentLayout([
			'title'  => User::text('editUser'),
			'footer' => '
<div class="panel-footer text-right">
	<button class="btn btn-primary" type="submit" name="submitUpdate">' . t('save') . '</button>
</div>']); ?>
		
		</form>
	</div>
</div>
