<?php

use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

/**
 * @var string $CONTROLLER_OUTPUT
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var UserException $exception
 */

// Remove classic reports
global $REPORTS;
$REPORTS = [];

// Remove backoffice title
$env['contentTitle'] = false;
$rendering->useLayout('page_skeleton');

$message = "$exception";
if( $message === 'notFound' ) {
	// NotFoundException's default
	$message = 'user_error';
}
?>

<div class="row justify-content-center mt-md-4">
	<div class="col-lg-6">
		<div class="text-center mt-4">
			<img class="mb-4 img-error" src="/static/vendor/sb-admin/sb-admin-6.0.2/img/error-404-monochrome.svg" alt="Non trouvé"/>
			<p class="lead"><?php echo t($message); ?></p>
			<a href="<?php echo u(getHomeRoute()); ?>">
				<i class="fas fa-arrow-left mr-1"></i> Retourner à l'accueil
			</a>
		</div>
	</div>
</div>
