<?php

use Orpheus\Publisher\Fixture\FixtureRepository;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Form\FormToken;
use Orpheus\Exception\UserException;

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class InstallFixturesSetupController extends SetupController {
	
	protected static $routeName = 'setup_installfixtures';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$FORM_TOKEN	= new FormToken();
		$env = array(
			'FORM_TOKEN'	=> $FORM_TOKEN,
			'allowContinue'	=> false,
		);
		
		if( $request->hasData('submitInstallFixtures') ) {
	
			try {
				$c = $t = 0;
				foreach( FixtureRepository::listAll() as $class ) {
					$t++;
					try {
						$class::loadFixtures();
						$c++;
// 					} catch( UserException $e ) {
// 						throw $e;
					} catch( \Exception $e ) {
						throw $e;
// 						throw new UserException(t('errorLoadingFixture', DOMAIN_SETUP, $class), DOMAIN_SETUP, 0, $e);
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
