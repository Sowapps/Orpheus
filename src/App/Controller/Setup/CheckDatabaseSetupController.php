<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Setup;

use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Pdo\PdoErrorAnalyzer;
use Orpheus\Pdo\PdoPermissionAnalyzer;
use Orpheus\SqlAdapter\AbstractSqlAdapter;
use PDOException;
use RuntimeException;

class CheckDatabaseSetupController extends AbstractSetupController {
	
	protected static string $routeName = 'setup_check_database';
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$allowContinue = false;
		$sqlAdapter = AbstractSqlAdapter::getInstance();
		try {
			$sqlAdapter->ensureConnection();
			$allowContinue = true;
			reportSuccess('successDBAccess', DOMAIN_SETUP);
		} catch( PDOException $exception ) {
			reportError($exception->getMessage(), DOMAIN_SETUP);
			$this->resolveError($exception, $sqlAdapter);
		}
		
		if( $allowContinue ) {
			$this->validateStep();
		}
		
		return $this->renderHtml('setup/setup_check_database', [
			'sqlAdapter'    => $sqlAdapter,
			'allowContinue' => $allowContinue,
		]);
	}
	
	protected function resolveError(PDOException $exception, AbstractSqlAdapter $sqlAdapter): void {
		$analyzer = PdoErrorAnalyzer::fromDriver($exception, $sqlAdapter::getDriver());
		switch( $analyzer->getCodeReference() ) {
			case PdoErrorAnalyzer::CODE_UNKNOWN_DATABASE:
				$this->checkCreateDatabase($sqlAdapter);
				break;
			default:
				throw new RuntimeException(sprintf('Unknown reference "%s"', $analyzer->getCodeReference()));
		}
	}
	
	protected function checkCreateDatabase(AbstractSqlAdapter $sqlAdapter): void {
		$permissionAnalyzer = PdoPermissionAnalyzer::fromSqlAdapter($sqlAdapter);
		if( !$permissionAnalyzer->canDatabaseCreate() ) {
			$settings = $sqlAdapter->getConfig();
			reportError(sprintf('Current database user "%s" has no permission to create database "%s" on "%s"',
				$settings['user'] ?? 'NO USER', $settings['dbname'] ?? 'NO DB NAME', $settings['host'] ?? 'NO HOST'));
			
			return;
		}
		$this->setOption('allowCreateDatabase', true);
	}
	
}
