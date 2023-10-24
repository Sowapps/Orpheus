<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.setup');

?>
<form method="POST">
	<div class="container py-4">
		<div class="p-5 mb-4 bg-light border rounded-3">
			<div class="p-3">
				<h1 class="fw-bold mb-3">
					<?php echo t('start_title', DOMAIN_SETUP, [t('app_name')]); ?>
				</h1>
				<p class="lead">
					<?php echo html(t('start_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?>
				</p>
				
				<?php
				$this->display('reports');
				?>
				<div class="mt-5 text-end">
					<a class="btn btn-lg btn-primary" href="<?php echo u('setup_check_filesystem'); ?>" role="button">
						<?php echo t('start_install', DOMAIN_SETUP); ?>
						<i class="fa fa-chevron-right"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
</form>
