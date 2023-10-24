<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string|false $title
 * @var string|null $image
 * @var string|null $message
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;

$rendering->useLayout('layout.admin');

$backLinkRoute = DEFAULT_ROUTE;
?>

<div id="layoutError">
	<div id="layoutError_content">
		<main>
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-lg-6">
						<div class="text-center mt-4">
							<?php
							if( !empty($title) ) {
								?>
								<h1 class="display-1"><?php echo $title; ?></h1>
								<?php
							}
							if( !empty($image) ) {
								?>
								<img class="mb-4 img-error" src="<?php echo $image; ?>" alt="<?php echo $title; ?>"/>
								<?php
							}
							if( !empty($message) ) {
								?>
								<p class="lead"><?php echo $message; ?></p>
								<?php
							}
							?>
							<a href="<?php echo u($backLinkRoute); ?>">
								<i class="fas fa-arrow-left me-1"></i>
								Return to Dashboard
							</a>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
	<div id="layoutError_footer">
		<footer class="py-4 bg-light mt-auto">
			<div class="container-fluid px-4">
				<div class="d-flex align-items-center justify-content-between small">
					<div class="text-muted">Copyright &copy; <?php echo t('app_name'); ?> 2016<?php echo date('Y') !== '2021' ? ' - ' . date('Y') : ''; ?></div>
					<div>Made with ❤ by <a href="https://sowapps.com" target="_blank">Florent Hazard (Sowapps)</a></div>
				</div>
			</div>
		</footer>
	</div>
</div>
