<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $content
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('document.web');

?>
<main role="main" class="flex-shrink-0 mb-5 pb-5">
	<?php echo $content; ?>
</main>
<footer class="py-4 bg-light mt-auto">
	<div class="container-fluid px-4">
		<div class="d-flex align-items-center justify-content-between small">
			<div class="text-muted">
				Copyright © Sowapps <?php echo date('Y'); ?>
			</div>
			<div>
				Made with ❤ by <a href="https://sowapps.com" target="_blank">Florent Hazard</a>
			</div>
		</div>
	</div>
</footer>
