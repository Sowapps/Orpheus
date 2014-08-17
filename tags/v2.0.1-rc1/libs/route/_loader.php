<?php
//! Route Library
/*!
 * Route library defining public route (links)
 * 
 */

//! Gets the full url of a module
/*!
 * \param $module The module.
 * \param $action The action to use for this url. Array allowed only with Route config usage.
 * \param $queryStr The query string to add to the url, can be an array.
 * \return The url of $module.

* Gets the full url of a module, using default link for default module.
*/
function u($module, $action='', $queryStr='') {
	if( $module == DEFAULTMOD && empty($action) ) {
		return DEFAULTLINK;
	}
	global $ROUTES;
	if( !isset($ROUTES) ) {
		$ROUTES = Config::build('routes', 1);
	}
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
	if( !empty($queryStr) ) {
		if( is_array($queryStr) ) {
			unset($queryStr['module'], $queryStr['action']);
			$queryStr = http_build_query($queryStr, '', '&amp;');
		} else {
			$queryStr = str_replace('&', '&amp;', $queryStr);
		}
	}
	return SITEROOT.$module.((!empty($action) && empty($actionProcessed)) ? '-'.$action : '').(!empty($queryStr) ? '-'.$queryStr : '').'.html';
}

//! Displays the full url of a module
/*!
 * \param $module The module.
 * \param $action The action to use for this url.
 * \param $queryStr The query string to add to the url, can be an array.
 * \sa u()

 * Displays the full url of a module, using default link for default module.
 */
function _u($module, $action='', $queryStr='') {
	echo u($module, $action, $queryStr);
}