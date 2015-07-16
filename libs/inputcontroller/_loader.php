<?php
/** InputController Library

 * InputController library to bring MVC features
 * 
 */
 
addAutoload('Controller',					'inputcontroller/controller');
addAutoload('ControllerRoute',				'inputcontroller/controllerroute');
addAutoload('InputRequest',					'inputcontroller/inputrequest');
addAutoload('OutputResponse',				'inputcontroller/outputresponse');

addAutoload('HTTPRoute',					'inputcontroller/http/httproute');
addAutoload('HTTPRequest',					'inputcontroller/http/httprequest');
addAutoload('HTTPController',				'inputcontroller/http/httpcontroller');
addAutoload('HTTPResponse',					'inputcontroller/http/httpresponse');
addAutoload('HTMLHTTPResponse',				'inputcontroller/http/htmlhttpresponse');

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
