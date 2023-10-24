<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Setup;

use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class StartSetupController extends AbstractSetupController {
	
	protected static string $routeName = 'setup_start';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		try {
			if( static::isThisStepValidated('EndSetupController') ) {
				throw new UserException('alreadyInstalled');
			} else {
				$this->validateStep();
			}
		} catch( UserException $e ) {
			reportError($e, DOMAIN_SETUP);
		}
		
		return $this->renderHtml('setup/setup_start');
	}
	
}
