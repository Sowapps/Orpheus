<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Exception\UserException;

class HomeController extends HTTPController {

	/**
	 * Controller declaration
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		// $request->get
		try {
			if( $data = $request->getData('data') ) {
				$testID = DemoEntity::create($data);
// 				$test = DemoTest::create($data);
				reportSuccess(DemoEntity::text('successCreate'));
// 				reportSuccess("Object created.");
				
				$test = DemoEntity::load($testID);
				reportSuccess(DemoEntity::text('successLoad', $test, escapeText($test->name)));
// 				reportSuccess('Object "'.$test.'" loaded, it\'s named "'.escapeText($test->name).'".');
				
				$test->remove();
				reportSuccess(DemoEntity::text('successDelete'));
// 				reportSuccess("Object deleted.");
			}
		
		} catch( UserException $e ) {
			reportError($e);
		}
		return HTMLHTTPResponse::render('app/home');
	}

}