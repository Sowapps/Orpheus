<?php /** @noinspection HtmlFormInputWithoutLabel */

/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var User $user
 * @var boolean $allowUserUpdate
 * @var boolean $allowUserPasswordChange
 * @var boolean $allowUserDelete
 * @var boolean $allowUserGrant
 * @var boolean $allowImpersonate
 */

use App\Entity\User;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');
?>

<div class="row">
	<div class="col-lg-6">
		
		<form method="POST">
			
			<div style="display: none;">
				<input type="text" autocomplete="new-password"/>
				<input type="password" autocomplete="new-password"/>
			</div>
			<?php $rendering->useLayout('component/panel'); ?>
			
			<div class="mb-3">
				<label for="InputUserName"><?php echo User::text('name'); ?></label>
				<input id="InputUserName" name="user[fullname]" value="<?php echo $user->fullname ?? ''; ?>" type="text" class="form-control">
			</div>
			<div class="mb-3">
				<label for="InputUserEmail"><?php echo User::text('email'); ?></label>
				<input id="InputUserEmail" name="user[email]" value="<?php echo $user->email ?? ''; ?>" type="email" class="form-control" readonly>
				<?php /*
			<input id="InputUserEmail" name="user[email]" value="<?php echo $user->email ?? ''; ?>" type="email" class="form-control" autocomplete="new-password">
 */ ?>
			</div>
			<div class="mb-3">
				<label for="InputUserPassword"><?php echo User::text('password'); ?></label>
				<?php
				if( $allowUserPasswordChange ) {
					?>
					<input id="InputUserPassword" name="user[password]" type="password" class="form-control" autocomplete="new-password" placeholder="<?php echo User::text('fillToUpdate'); ?>">
					<?php
				} else {
					?><p class="form-control-static"><?php echo User::text('updateNotAllowed'); ?></p><?php
				}
				?>
			</div>
			<?php
			if( $allowUserGrant ) {
				?>
				<div class="mb-3">
					<label for="InputUserAccessLevel"><?php echo User::text('role'); ?></label>
					<select id="InputUserAccessLevel" name="user[accesslevel]" class="form-control widget-select">
						<?php echo htmlOptions('user/accesslevel', array_filter(User::getUserRoles(), function ($value) {
							return $value >= 0;
						}), null, OPT_LABEL2VALUE, 'role_', User::getDomain()); ?>
					</select>
				</div>
				<?php
			}
			$rendering->startNewBlock('footer');
			/*
			if( $allowUserDelete ) {
				?>
				<button class="btn btn-warning me-4" type="button" data-toggle="confirm"
						data-confirm-title="<?php echo t('remove.title', DOMAIN_USER, [$user]); ?>"
						data-confirm-message="<?php echo t('remove.legend', DOMAIN_USER, [$user]); ?>"
						data-confirm-submit-name="submitDelete"><?php echo t('delete'); ?></button>
				<?php
			}
			if( $allowImpersonate ) {
				?>
				<button class="btn btn-secondary" type="submit" name="submitImpersonate">
					<i class="fa-solid fa-user-secret me-1"></i> <?php echo User::text('impersonate'); ?>
				</button>
				<?php
			}
			*/
			?>
			<button class="btn btn-primary" type="submit" name="submitUpdate"><?php echo t('save'); ?></button>
			<?php
			
			$rendering->endCurrentLayout(['title' => User::text('editUser')]); ?>
		
		</form>
	</div>
</div>
