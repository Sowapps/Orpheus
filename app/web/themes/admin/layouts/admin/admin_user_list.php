<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var boolean $allowCreate
 * @var boolean $allowUpdate
 * @var SqlSelectRequest $users
 * @var array $userInput
 */

use App\Entity\User;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;
use Orpheus\SqlRequest\SqlSelectRequest;

$rendering->useLayout('layout.admin');
?>
	<form method="POST">
		
		<div class="row">
			<div class="col-lg-12">
				<?php
				$rendering->useLayout('component/panel');
				
				if( $allowCreate ) {
					?>
					<div class="btn-group mb-3" role="group" aria-label="<?php echo t('actionsColumn'); ?>">
						<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#DialogUserCreate">
							<i class="fa fa-plus"></i> <?php echo t('new'); ?>
						</button>
					</div>
					<?php
				}
				?>
				<table class="table table-bordered table-hover">
					<thead>
					<tr>
						<th><?php echo t('idColumn'); ?></th>
						<th><?php echo User::text('name'); ?></th>
						<th><?php echo User::text('email'); ?></th>
						<th><?php echo User::text('role'); ?></th>
						<th><?php echo t('actionsColumn'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					/* @var User $user */
					foreach( $users as $user ) {
						?>
						<tr>
							<th scope="row"><?php echo $user->id(); ?></th>
							<td>
								<a href="<?php echo $user->getAdminLink(); ?>">
									<?php echo $user; ?>
								</a>
							</td>
							<td><?php echo $user->email; ?></td>
							<td><?php echo $user->getRoleText(); ?></td>
							<td><?php
								if( $allowUpdate ) {
									?>
									<div class="btn-group btn-group-sm" role="group" aria-label="<?php echo t('actionsColumn'); ?>">
										<a href="<?php echo $user->getAdminLink(); ?>" class="btn btn-success btn-sm">
											<i class="fa fa-edit"></i>
										</a>
									</div>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				
				<?php $rendering->endCurrentLayout(); ?>
			</div>
		</div>
	</form>

<?php
if( $allowCreate ) {
	$rendering->useLayout('component/dialog');
	?>
	
	<p class="help-block"><?php echo User::text('addUser_lead'); ?></p>
	<div class="mb-3">
		<label for="UserCreateName"><?php echo User::text('name'); ?></label>
		<input id="UserCreateName" class="form-control" type="text" name="user[fullname]" value="<?php echo $userInput['fullname'] ?? ''; ?>"/>
	</div>
	<div class="mb-3">
		<label for="UserCreateEmail"><?php echo User::text('email'); ?></label>
		<input id="UserCreateEmail" class="form-control" type="text" name="user[email]" value="<?php echo $userInput['email'] ?? ''; ?>" autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="UserCreatePassword"><?php echo User::text('password'); ?></label>
		<input id="UserCreatePassword" class="form-control" type="password" name="user[password]" autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="UserCreatePasswordConfirm"><?php echo User::text('confirmPassword'); ?></label>
		<input id="UserCreatePasswordConfirm" class="form-control" type="password" name="user[password_conf]">
	</div>
	<?php
	$rendering->startNewBlock('footer');
	?>
	<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
	<button type="submit" class="btn btn-primary" name="submitCreate" data-submittext="<?php echo t('saving'); ?>"><?php echo t('add'); ?></button>
	<?php
	
	$rendering->endCurrentLayout([
		'id'    => 'DialogUserCreate',
		'title' => User::text('addUser'),
	]);
}
