<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\EntityDescriptor\SQLGenerator\SQLGeneratorMySql;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\SqlAdapter\SqlAdapter;

class InstallDatabaseSetupController extends SetupController {
	
	protected static $routeName = 'setup_installdb';
	
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
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$defaultAdapter = SqlAdapter::getInstance();
					$output = $output == OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$formToken->validateForm($request);
					}
					$generator = new SQLGeneratorMySql();
					$result = [];
					/** @var PermanentEntity $entityClass */
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$query = $generator->matchEntity($entityClass::getValidator(), $entityClass::getSqlAdapter());
						if( $query ) {
							$result[$entityClass] = $query;
						}
					}
					
					if( !$result ) {
						$env['allowContinue'] = true;
						throw new UserException('errorNoChanges', DOMAIN_SETUP);
					}
					$env['resultingSQL'] = implode('', $result);
					if( $output == OUTPUT_DISPLAY ) {
						$env['requireEntityValidation'] = 1;
					} elseif( $output == OUTPUT_APPLY ) {
						foreach( $result as $query ) {
							$defaultAdapter->query(strip_tags($query), PDOEXEC);
						}
						$env['allowContinue'] = true;
						reportSuccess('successSQLApply', DOMAIN_SETUP);
					}
				}
			}
			
		} catch( UserException $e ) {
			if( $e->getMessage() === 'errorNoChanges' ) {
				reportSuccess($e, $e->getDomain());
			} else {
				reportError($e);
			}
		}
		
		if( $env['allowContinue'] ) {
			$this->validateStep();
		}
		
		return $this->renderHtml('setup/setup_installdb', $env);
	}
	
}

define('OUTPUT_APPLY', 1);
define('OUTPUT_DISPLAY', 2);
define('OUTPUT_DLRAW', 3);
define('OUTPUT_DLZIP', 4);
