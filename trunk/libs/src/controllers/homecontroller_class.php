<?php


class HomeController extends HTTPController {
	
	/**
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
// 		$request->get
		if( isPOST('data') ) {
			try {
				$test = DemoTest::create($_POST['data']);
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
		return HTMLHTTPResponse::renderWithPHP('app/home');
	}

	
}
