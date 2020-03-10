<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use DemoEntity;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class HomeController extends HTTPController {
	
	/**
	 * Controller declaration
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		try {
			if( $data = $request->getData('data') ) {
				$testID = DemoEntity::create($data);
				reportSuccess(DemoEntity::text('successCreate'));
				
				$test = DemoEntity::load($testID);
				reportSuccess(DemoEntity::text('successLoad', $test, escapeText($test->name)));
				
				$test->remove();
				reportSuccess(DemoEntity::text('successDelete'));
			}
		
		} catch( UserException $e ) {
			reportError($e);
		}
		return HTMLHTTPResponse::render('app/home');
	}

}
