<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HTTPController $controller
 * @var HTTPRequest $request
 * @var HTTPRoute $route
 *
 * @var Exception $exception
 */

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;


$rendering->useLayout('page_skeleton');
?>

<div class="row justify-content-center mt-md-4">
	<div class="col-lg-6">
		<div class="text-center mt-4">
			<h1 class="display-1 mb-4" title="<?php echo $exception->getCode() ?: 500; ?>"><?php echo t('user_error_title'); ?></h1>
			<p class="lead"><?php echo t('user_error_legend'); ?></p>
			<a href="<?php echo u(getHomeRoute()); ?>">
				<i class="fas fa-arrow-left mr-1"></i> Retourner à l'accueil
			</a>
		</div>
	</div>
</div>
