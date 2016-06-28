<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPRequest;

class HomeController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
// 		$request->get
		if( $data = $request->getData('data') ) {
			try {
				$test = DemoTest::create($data);
				reportSuccess("Object created.");
				
				$test = DemoTest::load($test);
				reportSuccess("Object \"{$test}\" loaded, it's named \"{$test->name}\".");
				
		// 		DemoTest::delete($tid);
				$test->remove();
				reportSuccess("Object deleted.");
				
			} catch (UserException $e) {
				reportError($e);
			}
		}
		return HTMLHTTPResponse::render('app/home');
	}
	
}
