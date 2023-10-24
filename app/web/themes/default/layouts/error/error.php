<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var Exception $exception
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;

$code = $exception->getCode();
$rendering->display('error/layout.error', [
	'title'   => t('user_error_title'),
	'message' => tn('user_error_' . $code) ?? tn('user_error_legend'),
]);
