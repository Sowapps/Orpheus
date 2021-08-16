<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Publisher\Fixture\FixtureRepository;
use Orpheus\Publisher\PermanentObject\PermanentObject;

class InstallFixturesSetupController extends SetupController {
	
	protected static $routeName = 'setup_installfixtures';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$formToken = new FormToken();
		$env = [
			'formToken'     => $formToken,
			'allowContinue' => false,
		];
		
		if( $request->hasData('submitInstallFixtures') ) {
			
			try {
				$c = $t = 0;
				foreach( FixtureRepository::listAll() as $class ) {
					$t++;
					try {
						/** @var PermanentObject $class */
						$class::loadFixtures();
						$c++;
					} catch( Exception $e ) {
						throw $e;
					}
				}
				$env['allowContinue'] = true;
				$this->validateStep();
				if( $c ) {
					reportSuccess(t('successInstallFixtures', DOMAIN_SETUP, array('PROCESSED'=>$c, 'TOTAL'=>$t)));
				}
	
			} catch(UserException $e) {
				reportError($e);
			}
		}
		
		// At end
		$env['wasAlreadyDone'] = $this->isStepValidated();
		if( $env['wasAlreadyDone'] ) {
			reportWarning('fixturesAlreadyLoaded', DOMAIN_SETUP);
			$env['allowContinue'] = true;
		}
		
		return $this->renderHtml('setup/setup_installfixtures', $env);
	}

}
