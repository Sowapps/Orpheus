<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

class DevLogListController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		$logs = array(
			'sys' => (object) array('label' => t('file_system_title', DOMAIN_LOGS), 'file' => LOGFILE_SYSTEM),
			'sql' => (object) array('label' => t('file_sql_title', DOMAIN_LOGS), 'file' => LOGFILE_SQL),
			'hack' => (object) array('label' => t('file_hack_title', DOMAIN_LOGS), 'file' => LOGFILE_HACK),
// 			'server' => (object) array('label' => t('ServerLogs', DOMAIN_LOGS), 'file' => SERVLOGFILENAME),
			'debug' => (object) array('label' => t('file_debug_title', DOMAIN_LOGS), 'file' => LOGFILE_DEBUG),
		);
		
		if( isPOST('submitEraseLogs') ) {
			$logID = key($_POST['submitEraseLogs']);
		
			if( file_put_contents($logs[$logID]['file'], '') !== false ) {
				reportSuccess('successFileErased');
		
			} else {
				reportError('unableToEraseFile');
			}
		}

		$this->addThisToBreadcrumb();
		return $this->renderHTML('devtools/dev_loglist', array(
// 			'logFolder' => LOGSPATH,
			'logs' => $logs
		));
	}
	
	public function listLogsOfFile($logFile) {
		$files = array();
		foreach( cleanscandir(LOGSPATH) as $file ) {
			if( strpos($file, $logFile) === 0 ) {
				$files[] = LOGSPATH.$file;
			}
		}
		return $files;
	}

}
