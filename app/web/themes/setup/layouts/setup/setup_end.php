<?php
/**
 * @var HTMLRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var array $folders
 * @var boolean $allowContinue
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;

$rendering->useLayout('page_skeleton');

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-10 col-lg-offset-1">
			
			<div class="jumbotron">
				<h1><i class="fa fa-2x fa-check-square-o pull-left"></i> <?php _t('end_title', DOMAIN_SETUP, t('app_name')); ?></h1>
				<p class="lead"><?php echo text2HTML(t('end_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
				<p>
					<a class="btn btn-lg btn-primary" href="<?php echo u(ROUTE_HOME); ?>" role="button">
						<i class="fa fa-home"></i>
						<?php _t('goToHome', DOMAIN_SETUP); ?>
					</a>
				</p>
			</div>
		
		</div>
	
	</div>
</form>
