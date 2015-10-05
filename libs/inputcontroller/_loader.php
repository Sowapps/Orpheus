<?php
/** InputController Library

 * InputController library to bring MVC features
 * 
 */
 
addAutoload('Controller',					'inputcontroller/Controller');
addAutoload('ControllerRoute',				'inputcontroller/ControllerRoute');
addAutoload('InputRequest',					'inputcontroller/InputRequest');
addAutoload('OutputResponse',				'inputcontroller/OutputResponse');

addAutoload('HTTPRoute',					'inputcontroller/http/HTTPRoute');
addAutoload('HTTPRequest',					'inputcontroller/http/HTTPRequest');
addAutoload('HTTPController',				'inputcontroller/http/HTTPController');
addAutoload('HTTPResponse',					'inputcontroller/http/HTTPResponse');
addAutoload('HTMLHTTPResponse',				'inputcontroller/http/HTMLHTTPResponse');
addAutoload('RedirectHTTPResponse',			'inputcontroller/http/RedirectHTTPResponse');

addAutoload('DelayedPageController',		'inputcontroller/controllers/DelayedPageController');
addAutoload('StaticPageController',			'inputcontroller/controllers/StaticPageController');

// define('HOOK_ROUTEMODULE', 'routeModule');
// Hook::create(HOOK_ROUTEMODULE);


function u($routeName, $values=array()) {
// 	$routes	= HTTPRoute::getRoutes();
// 	$routes	= HTTPRoute::getRoutes();
	$route	= HTTPRoute::getRoute($routeName);
	if( !$route ) {
		throw new Exception('Unable to find route '.$routeName);
	}
// 	if( !isset($routes[$routeName]) ) {
// 		throw new Exception('Unable to find route '.$routeName);
// 	}
// 	if( !isset($routes[$routeName][HTTPRoute::METHOD_GET]) ) {
// 		throw new Exception('Unable to find route '.$routeName.' for GET method');
// 	}
	/* @var $route HTTPRoute */
// 	$route	= $routes[$routeName][HTTPRoute::METHOD_GET];
	return $route->formatURL($values);
}

function exists_route($routeName) {
	return !!HTTPRoute::getRoute($routeName);
}

function is_current_route($route) {
	return get_current_route() === $route;
}

function get_current_route() {
	$request	= HTTPRequest::getMainRequest();
	return $request->getRoute()->getName();
}

function _u($route, $values=array()) {
	echo u($route, $values);
}
