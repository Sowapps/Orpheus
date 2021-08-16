<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Demo\DemoEntity;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class HomeController extends HttpController {
	
	/**
	 * Controller declaration
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
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
		
		return HtmlHttpResponse::render('app/home');
	}

}
