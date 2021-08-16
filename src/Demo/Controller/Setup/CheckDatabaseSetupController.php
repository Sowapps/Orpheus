<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\Pdo\PdoErrorAnalyzer;
use Orpheus\Pdo\PdoPermissionAnalyzer;

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class CheckDatabaseSetupController extends SetupController {
	
	protected static $routeName = 'setup_checkdb';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$env = [
			'folders'       => [],
			'allowContinue' => false,
		];
		
		pdo_loadConfig();
		$instance = pdo_getDefaultInstance();
		$settings = pdo_getSettings($instance);
		$env['DB_SETTINGS'] = (object) $settings;
		
		try {
			pdo_connect($settings);
			$env['allowContinue'] = true;
			reportSuccess('successDBAccess', DOMAIN_SETUP);
		} catch( PDOException $e ) {
			reportError($e->getMessage(), DOMAIN_SETUP);
			$this->resolveError($e, $settings);
		}
		
		if( $env['allowContinue'] ) {
			$this->validateStep();
		}
		
		return $this->renderHtml('setup/setup_checkdb', $env);
	}
	
	protected function resolveError($exception, array $settings) {
		$analyzer = PdoErrorAnalyzer::fromDriver($exception, $settings['driver']);
		switch( $analyzer->getCodeReference() ) {
			case PdoErrorAnalyzer::CODE_UNKNOWN_DATABASE:
				$this->checkCreateDatabase($settings);
				break;
		}
	}
	
	protected function checkCreateDatabase($settings) {
		$permissionAnalyzer = PdoPermissionAnalyzer::fromSettings($settings);
		if( !$permissionAnalyzer->canDatabaseCreate() ) {
			reportError(sprintf('Current database user "%s" has no permission to create database "%s" on "%s"', $settings['user'], $settings['dbname'], $settings['host']));
			
			return;
		}
		$this->setOption('allowCreateDatabase', true);
	}
	
}
