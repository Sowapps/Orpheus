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

// define('HOOK_ROUTEMODULE', 'routeModule');
// Hook::create(HOOK_ROUTEMODULE);


function u($routeName, $values=array()) {
	$routes	= HTTPRoute::getRoutes();
	if( !isset($routes[$routeName]) ) {
		throw new Exception('Unable to find route '.$routeName);
	}
	if( !isset($routes[$routeName][HTTPRoute::METHOD_GET]) ) {
		throw new Exception('Unable to find route '.$routeName.' for GET method');
	}
	/* @var $route HTTPRoute */
	$route	= $routes[$routeName][HTTPRoute::METHOD_GET];
	return $route->formatURL($values);
}

function _u($route, $values=array()) {
	echo u($route, $values);
}
