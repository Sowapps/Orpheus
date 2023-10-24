<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Setup;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Publisher\Fixture\FixtureInterface;
use Orpheus\Publisher\Fixture\FixtureRepository;

class InstallFixturesSetupController extends AbstractSetupController {
	
	protected static string $routeName = 'setup_install_fixtures';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$formToken = new FormToken();
		$allowContinue = false;
		
		if( $request->hasData('submitInstallFixtures') ) {
			
			try {
				$countProcessed = $countTotal = 0;
				foreach( FixtureRepository::listAll() as $class ) {
					$countTotal++;
					try {
						/** @var FixtureInterface $class */
						$class::loadFixtures();
						$countProcessed++;
					} catch( Exception ) {
						//						throw $e;
					}
				}
				$allowContinue = true;
				$this->validateStep();
				if( $countProcessed ) {
					reportSuccess(t('successInstallFixtures', DOMAIN_SETUP, ['PROCESSED' => $countProcessed, 'TOTAL' => $countTotal]));
				}
	
			} catch(UserException $e) {
				reportError($e);
			}
		}
		
		// Already validated
		$wasAlreadyDone = $this->isStepValidated();
		if( $wasAlreadyDone ) {
			reportWarning('fixturesAlreadyLoaded', DOMAIN_SETUP);
			$allowContinue = true;
		}
		
		return $this->renderHtml('setup/setup_install_fixtures', [
			'formToken'      => $formToken,
			'allowContinue'  => $allowContinue,
			'wasAlreadyDone' => $wasAlreadyDone,
		]);
	}
	
}
