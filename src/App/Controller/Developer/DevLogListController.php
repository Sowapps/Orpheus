<?php

namespace App\Controller\Developer;

use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class DevLogListController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$logs = [
			'sys'   => (object) ['label' => t('file_system_title', DOMAIN_LOGS), 'file' => LOGFILE_SYSTEM],
			'sql'   => (object) ['label' => t('file_sql_title', DOMAIN_LOGS), 'file' => LOGFILE_SQL],
			'hack'  => (object) ['label' => t('file_hack_title', DOMAIN_LOGS), 'file' => LOGFILE_HACK],
			// 			'server' => (object) array('label' => t('ServerLogs', DOMAIN_LOGS), 'file' => SERVLOGFILENAME),
			'debug' => (object) ['label' => t('file_debug_title', DOMAIN_LOGS), 'file' => LOGFILE_DEBUG],
		];
		
		//		if( $request->hasDataKey('submitEraseLogs', $logId) ) {
		//
		//			if( file_put_contents($logs[$logId]['file'], '') !== false ) {
		//				reportSuccess('successFileErased');
		//
		//			} else {
		//				reportError('unableToEraseFile');
		//			}
		//		}
		
		$this->addThisToBreadcrumb();
		
		return $this->renderHtml('developer/dev_log_list', [
			'logs' => $logs,
		]);
	}
	
	public function listLogsOfFile(string $logFile): array {
		$files = [];
		foreach( scanFolder(LOGS_PATH) as $file ) {
			if( str_starts_with($file, $logFile) ) {
				$files[] = LOGS_PATH . '/' . $file;
			}
		}
		return $files;
	}
	
}
