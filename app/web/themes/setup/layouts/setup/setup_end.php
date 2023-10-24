<?php
/**
 * @var HtmlRendering $rendering
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
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.setup');

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-10 offset-lg-1">
			
			<div class="p-5 mb-4 bg-light border rounded-3">
				<h1>
					<i class="fa-solid fa-check text-success"></i>
					<?php echo t('end_title', DOMAIN_SETUP, [t('app_name')]); ?>
				</h1>
				<p class="lead"><?php echo html(t('end_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
				<div class="text-center mt-5">
					<a class="btn btn-lg btn-primary" href="<?php echo u(ROUTE_HOME); ?>" role="button">
						<i class="fa fa-home me-1"></i>
						<?php echo t('goToHome', DOMAIN_SETUP); ?>
					</a>
				</div>
			</div>
		
		</div>
	
	</div>
</form>
