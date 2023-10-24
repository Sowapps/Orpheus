<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Setup;

use App\Controller\Developer\EntitiesControllerTrait;
use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\SqlAdapter\AbstractSqlAdapter;

class InstallDatabaseSetupController extends AbstractSetupController {
	
	use EntitiesControllerTrait;
	
	protected static string $routeName = 'setup_install_database';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		
		$formToken = new FormToken();
		$requireEntityValidation = false;
		$queriesHtml = null;
		$selectedEntities = [];
		$unknownTables = [];
		$allowContinue = false;
		
		try {
			$processGeneration = (int)$request->getData('submitGenerateSql');
			if( $processGeneration ) {
				$this->adapter = AbstractSqlAdapter::getInstance();
				if( $processGeneration === self::OUTPUT_APPLY ) {
					$formToken->validateForm($request);
				} else {
					$processGeneration = self::OUTPUT_DISPLAY;
				}
				$selectedEntities = $request->getArrayData('entities');
				[$queries, $unknownTables] = $this->calculateChanges($selectedEntities);
				
				if( !$queries ) {
					$allowContinue = true;
				}
				
				$queriesHtml = implode('', $queries);
				$removableTables = $this->calculateRemovableTables($unknownTables, $request->getData('removeTable'));
				$requireEntityValidation = $this->processGeneration($processGeneration, $queries, $removableTables);
			}
			
		} catch( UserException $e ) {
			reportError($e);
		}
		
		if( $allowContinue ) {
			$this->validateStep();
		}
		
		return $this->renderHtml('setup/setup_install_database', [
			'allowContinue'           => $allowContinue,
			'formToken'               => $formToken,
			'requireEntityValidation' => $requireEntityValidation,
			'queriesHtml'             => $queriesHtml,
			'selectedEntities'        => $selectedEntities,
			'unknownTables'           => $unknownTables,
		]);
	}
	
}
