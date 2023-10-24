<?php

use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;

/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
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

//$message = "$exception";
//if( $message === 'notFound' ) {
//	// NotFoundException's default
//	$message = 'user_error_404';
//}


$rendering->display('error/layout.error', [
	'title'   => null,
	'image'   => '/static/vendor/sb-admin/sb-admin-7.0.4/assets/img/error-404-monochrome.svg',
	'message' => t('user_error_404'),
]);
