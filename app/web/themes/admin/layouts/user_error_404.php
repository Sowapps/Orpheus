<?php

use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;

/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HTTPController $Controller
 * @var HTTPRequest $Request
 * @var HTTPRoute $Route
 *
 * @var UserException $exception
 */

// Remove classic reports
global $REPORTS;
$REPORTS = [];

// Remove backoffice title
$env['contentTitle'] = false;

$rendering->useLayout('page_skeleton');
?>

<div class="row justify-content-center mt-md-4">
	<div class="col-lg-6">
		<div class="text-center mt-4">
			<img class="mb-4 img-error" src="<?php echo $this->getThemeURL(); ?>libs/sb-admin/img/error-404-monochrome.svg" alt="Not found"/>
			<p class="lead"><?php echo $exception; ?></p>
			<a href="<?php echo u('home'); ?>">
				<i class="fas fa-arrow-left mr-1"></i> Back to home
			</a>
		</div>
	</div>
</div>
