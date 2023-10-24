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


$rendering->useLayout('layout.public');
?>

<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-6">
			<div class="text-center my-4">
				<?php
				if( !empty($title) ) {
					?>
					<h1 class="display-1"><?php echo $title; ?></h1>
					<?php
				}
				if( !empty($image) ) {
					?>
					<img class="mb-4 img-error" src="<?php echo $image; ?>" alt="Error"/>
					<?php
				}
				if( !empty($message) ) {
					?>
					<p class="lead"><?php echo $message; ?></p>
					<?php
				}
				?>
				<a href="<?php echo u(ROUTE_HOME); ?>">
					<i class="fas fa-arrow-left me-1"></i>
					Return to Home Page
				</a>
			</div>
		</div>
	</div>
</div>
