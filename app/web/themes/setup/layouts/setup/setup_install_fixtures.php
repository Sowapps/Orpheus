<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var boolean $wasAlreadyDone
 * @var boolean $allowContinue
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.setup');

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-8 offset-lg-2">
			
			<h1><?php echo t('installfixtures_title', DOMAIN_SETUP, [t('app_name')]); ?></h1>
			<p class="lead"><?php echo html(t('installfixtures_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
			
			<?php $this->display('reports'); ?>
			<div class="text-end mt-3">
				<button type="submit" class="btn btn-lg <?php echo $wasAlreadyDone ? 'btn-outline-secondary' : 'btn-primary' ?>" name="submitInstallFixtures">
					<?php echo t('install_fixtures', DOMAIN_SETUP); ?>
				</button>
				<?php
				if( $allowContinue ) {
					?>
					<a class="btn btn-lg btn-primary" href="<?php echo u('setup_end'); ?>" role="button"><?php echo t('continue', DOMAIN_SETUP); ?></a>
					<?php
				}
				?>
			</div>
		
		</div>
	
	</div>
</form>
