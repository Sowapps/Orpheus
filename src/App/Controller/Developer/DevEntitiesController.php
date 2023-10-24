<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Developer;

use Exception;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\SqlAdapter\AbstractSqlAdapter;

class DevEntitiesController extends DevController {
	
	use EntitiesControllerTrait;
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		
		$this->addThisToBreadcrumb();
		
		$formToken = new FormToken();
		$requireEntityValidation = false;
		$queriesHtml = null;
		$selectedEntities = [];
		$unknownTables = [];
		
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
				
				$queriesHtml = implode('', $queries);
				$removableTables = $this->calculateRemovableTables($unknownTables, $request->getData('removeTable'));
				$requireEntityValidation = $this->processGeneration($processGeneration, $queries, $removableTables);
			}
			
		} catch( UserException $e ) {
			reportError($e);
		}
		
		return $this->renderHtml('developer/dev_entities', [
			'formToken'               => $formToken,
			'requireEntityValidation' => $requireEntityValidation,
			'queriesHtml'             => $queriesHtml,
			'selectedEntities'        => $selectedEntities,
			'unknownTables'           => $unknownTables,
		]);
	}
	
}
