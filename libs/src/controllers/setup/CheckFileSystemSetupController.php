<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class CheckFileSystemSetupController extends SetupController {
	
	protected static $route = 'setup_checkfs';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$env	= array(
			'folders'	=> array(),
			'allowContinue'	=> true
		);

		if( is_writable(ACCESSPATH) ) {
			$env['folders']['accesspath']	= (object) array(
				'title'			=> t('folderWritable_error_title', DOMAIN_SETUP, t('folder_webaccess', DOMAIN_SETUP)),
				'description'	=> t('folderWritable_error_description', DOMAIN_SETUP, ACCESSPATH),
				'panel'			=> PANEL_WARNING,
				'open'			=> 1
			);
// 			collapsiblePanelHTML(, , , PANEL_WARNING, 1);
		} else {
			$env['folders']['accesspath']	= (object) array(
				'title'			=> t('folderNotWritable_success_title', DOMAIN_SETUP, t('folder_webaccess', DOMAIN_SETUP)),
				'description'	=> t('folderNotWritable_success_description', DOMAIN_SETUP, ACCESSPATH),
				'panel'			=> PANEL_SUCCESS,
				'open'			=> 0
			);
// 			collapsiblePanelHTML('accesspath', t('folderNotWritable_success_title', DOMAIN_SETUP, t('folder_webaccess', DOMAIN_SETUP)), t('folderNotWritable_success_description', DOMAIN_SETUP, ACCESSPATH), PANEL_SUCCESS, 0);
		}
		if( is_writable(STOREPATH) ) {
			$env['folders']['storepath']	= (object) array(
				'title'			=> t('folderWritable_success_title', DOMAIN_SETUP, t('folder_store', DOMAIN_SETUP)),
				'description'	=> t('folderWritable_success_description', DOMAIN_SETUP, STOREPATH),
				'panel'			=> PANEL_SUCCESS,
				'open'			=> 0
			);
// 			collapsiblePanelHTML('storepath', t('folderWritable_success_title', DOMAIN_SETUP, t('folder_store', DOMAIN_SETUP)), t('folderWritable_success_description', DOMAIN_SETUP, STOREPATH), PANEL_SUCCESS, 0);
		} else {
// 			$allowContinue	= false;
			$env['allowContinue']	= false;
			$env['folders']['storepath']	= (object) array(
				'title'			=> t('folderNotWritable_error_title', DOMAIN_SETUP, t('folder_store', DOMAIN_SETUP)),
				'description'	=> t('folderNotWritable_error_description', DOMAIN_SETUP, STOREPATH),
				'panel'			=> PANEL_DANGER,
				'open'			=> 1
			);
// 			collapsiblePanelHTML('storepath', t('folderNotWritable_error_title', DOMAIN_SETUP, t('folder_store', DOMAIN_SETUP)), t('folderNotWritable_error_description', DOMAIN_SETUP, STOREPATH), PANEL_DANGER, 1);
		}
		if( is_writable(LOGSPATH) ) {
			$env['folders']['logspath']	= (object) array(
				'title'			=> t('folderWritable_success_title', DOMAIN_SETUP, t('folder_logs', DOMAIN_SETUP)),
				'description'	=> t('folderWritable_success_description', DOMAIN_SETUP, LOGSPATH),
				'panel'			=> PANEL_SUCCESS,
				'open'			=> 0
			);
		} else {
			$env['allowContinue']	= false;
			$env['folders']['logspath']	= (object) array(
				'title'			=> t('folderNotWritable_error_title', DOMAIN_SETUP, t('folder_logs', DOMAIN_SETUP)),
				'description'	=> t('folderNotWritable_error_description', DOMAIN_SETUP, LOGSPATH),
				'panel'			=> PANEL_DANGER,
				'open'			=> 1
			);
		}
// 		if( is_writable(LOGSPATH) ) {
// 			collapsiblePanelHTML('logspath', t('folderWritable_success_title', DOMAIN_SETUP, t('folder_logs', DOMAIN_SETUP)), t('folderWritable_success_description', DOMAIN_SETUP, LOGSPATH), PANEL_SUCCESS, 0);
// 		} else {
// 			$allowContinue	= false;
// 			collapsiblePanelHTML('logspath', t('folderNotWritable_error_title', DOMAIN_SETUP, t('folder_logs', DOMAIN_SETUP)), t('folderNotWritable_error_description', DOMAIN_SETUP, LOGSPATH), PANEL_DANGER, 1);
// 		}
		
		if( $env['allowContinue'] ) {
			$this->validateStep();
		}
	
		return $this->renderHTML('setup/setup_checkfs', $env);
	}

}

//define('PANEL_DEFAULT',		'panel-default');
define('PANEL_SUCCESS',		'panel-success');
define('PANEL_WARNING',		'panel-warning');
define('PANEL_DANGER',		'panel-danger');
