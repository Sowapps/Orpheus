<?php

class SessionPageController extends HTTPController {
	
	const SESSION_STOREDPAGES = '__STOREDPAGES';
	public static $max = 10;

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
		if( empty($_SESSION[self::SESSION_STOREDPAGES][$pathValues->page]) ) {
			throw new NotFoundException('No saved content found for page "'.$pathValues->page.'"');
		}
		$content	= $_SESSION[self::SESSION_STOREDPAGES][$pathValues->page];
		unset($_SESSION[self::SESSION_STOREDPAGES][$pathValues->page]);
		return new HTMLHTTPResponse($content);
	}

	public static function store($page, $content) {
		if( !isset($_SESSION[self::SESSION_STOREDPAGES]) ) {
			$_SESSION[self::SESSION_STOREDPAGES]	= array();
		}
		$_SESSION[self::SESSION_STOREDPAGES][$page]	= $content;
		if( count($_SESSION[self::SESSION_STOREDPAGES]) > static::$max ) {
			array_shift($_SESSION[self::SESSION_STOREDPAGES]);
		}
	}
}

