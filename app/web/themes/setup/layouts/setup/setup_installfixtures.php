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

$rendering->useLayout('page_skeleton');

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-8 col-lg-offset-2">
			
			<h1><?php _t('installfixtures_title', DOMAIN_SETUP, t('app_name')); ?></h1>
		<p class="lead"><?php echo text2HTML(t('installfixtures_description', DOMAIN_SETUP, array('APP_NAME'=>t('app_name')))); ?></p>
		
		<?php
		$this->display('reports-bootstrap3');
		?>
		<p>
			<button type="submit" class="btn btn-lg <?php echo $wasAlreadyDone ? 'btn-default' : 'btn-primary' ?>" name="submitInstallFixtures">
				<?php _t('install_fixtures', DOMAIN_SETUP); ?>
			</button>
			<?php
			if( $allowContinue ) {
				?>
			<a class="btn btn-lg btn-primary" href="<?php _u('setup_end'); ?>" role="button"><?php _t('continue', DOMAIN_SETUP); ?></a>
			<?php
			}
			?>
		</p>
		
	</div>
	
</div>
</form>
