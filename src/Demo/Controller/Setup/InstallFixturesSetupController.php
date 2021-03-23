<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use Orpheus\Publisher\Fixture\FixtureRepository;

class InstallFixturesSetupController extends SetupController {
	
	protected static $routeName = 'setup_installfixtures';
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
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
	
		return $this->renderHTML('setup/setup_installfixtures', $env);
	}

}
