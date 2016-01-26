<?php
/** Route Library

 * Route library defining public route (links)
 * 
 */

define('HOOK_ROUTEMODULE', 'routeModule');
Hook::create(HOOK_ROUTEMODULE);

/** Get the absolute url to access a module

 * @param string $module The module.
 * @param string $action The action to use for this url. Array allowed only with Route config usage.
 * @param mixed $options The options to route URL. Require more updates.
 * @return string The url of $module.

 * Get the absolute url to access a module, using default link for default module.
 * This function triggers hook HOOK_ROUTEMODULE with params ($url, $module, $action, $actionProcessed, $options, $isDefault), you can pass you own options using the $options parameter.
*/
function u($module=null, $action='', $options=null) {
	if( !$module ) {
		$module	= $GLOBALS['Module'];
	}
	$isDefault	= 0;
	if( $module === DEFAULTROUTE && !$action ) {
		$url		= DEFAULTLINK;
		$isDefault	= 1;
	}
	$extension	= 'html';
// 	debug($options);
	if( is_array($options) && isset($options['extension']) ) {
		$extension	= $options['extension'];
	}
	global $ROUTES;
	if( !isset($ROUTES) ) {
		$ROUTES = Config::build('routes', 1);
	}
	$actionProcessed	= 0;
	if( !empty($ROUTES) ) {
		if( !empty($ROUTES->{$module.'-'.$action}) ) {
			$module = $ROUTES->{$module.'-'.$action};
			$actionProcessed = 1;
		} else
		if( !empty($ROUTES->{$module.'-ACTION'}) ) {
			$module = is_array($action) ?
				vsprintf($ROUTES->{$module.'-ACTION'}, $action) :
				str_replace('%ACTION%', $action, $ROUTES->{$module.'-ACTION'});
			$actionProcessed = 1;
		} else
		if( !empty($ROUTES->$module) ) {
			$module = $ROUTES->$module;
		}
	}
// 	if( !empty($queryStr) ) {
// 		if( is_array($queryStr) ) {
// 			unset($queryStr['module'], $queryStr['action']);
// 			$queryStr = http_build_query($queryStr, '', '&amp;');
// 		} else {
// 			$queryStr = str_replace('&', '&amp;', $queryStr);
// 		}
// 	}
//(!empty($queryStr) ? '-'.$queryStr : '').
	if( !$isDefault ) {
		$url	= SITEROOT.$module.(($action && !$actionProcessed) ? '-'.$action : '').'.'.$extension;
	}
// 	debug("Trigger HOOK_ROUTEMODULE with parameters $url, $module, $action, $actionProcessed, $options, $isDefault, $extension");
	$url	= Hook::trigger(HOOK_ROUTEMODULE, false, $url, $module, $action, $actionProcessed, $options, $isDefault, $extension);
// 	die('URL: '.$url);
	return $url;
}

/** Display the full url of a module
 * @param string $module The module.
 * @param string $action The action to use for this url. Array allowed only with Route config usage.
 * @param mixed $options The options to route URL. Require more updates.
 * @see u()

 * Display the full url of a module, using default link for default module.
 */
function _u($module=null, $action='', $options=null) {
	echo u($module, $action, $options);
}