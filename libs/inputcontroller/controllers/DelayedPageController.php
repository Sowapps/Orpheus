<?php

use Orpheus\Exception\ForbiddenException;
use Orpheus\Cache\APCache;
use Orpheus\Exception\NotFoundException;

class DelayedPageController extends HTTPController {
	
// 	const SESSION_STOREDPAGES = '__STOREDPAGES';
// 	public static $max = 10;

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		if( !DEV_VERSION ) {
			throw new ForbiddenException("You're not allowed to access to this content.");
		}
		$pathValues	=	$request->getPathValues();
		$cache		= new APCache('delayedpage', $pathValues->page);
		if( !$cache->get($content) ) {
			$cache->reset();
			throw new NotFoundException('The delayed page "'.$pathValues->page.'" was not found');
		}
// 		if( empty($_SESSION[self::SESSION_STOREDPAGES][$pathValues->page]) ) {
// 			throw new NotFoundException('No saved content found for page "'.$pathValues->page.'"');
// 		}
// 		$content	= $_SESSION[self::SESSION_STOREDPAGES][$pathValues->page];
// 		unset($_SESSION[self::SESSION_STOREDPAGES][$pathValues->page]);
		return new HTMLHTTPResponse($content);
	}

	public static function store($page, $content) {
		// Do it and in some case, routes will not be loaded
		// Case this is not loaded will lead to infinite loop
// 		HTTPRoute::initialize();
		if( !ControllerRoute::isInitialized() ) {
			throw new Exception('Routes not initialized, application is not able to show content, it will fail again & again...');
		}
		
// 		debug('DelayedPageController::store()');
		$cache	= new APCache('delayedpage', $page, 60);
		$cache->set($content);
// 		debug('DelayedPageController::store() - Content saved');
// 		debug('DelayedPageController::store() - URL => '.u('delayedpage', array('page'=>$page)));
		return u('delayedpage', array('page'=>$page));
// 		if( !isset($_SESSION[self::SESSION_STOREDPAGES]) ) {
// 			$_SESSION[self::SESSION_STOREDPAGES]	= array();
// 		}
// 		$_SESSION[self::SESSION_STOREDPAGES][$page]	= $content;
// 		if( count($_SESSION[self::SESSION_STOREDPAGES]) > static::$max ) {
// 			array_shift($_SESSION[self::SESSION_STOREDPAGES]);
// 		}
	}
}

