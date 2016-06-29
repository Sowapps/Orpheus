<?php
/** \file
 * All web site constants.
 *
 * @page constants Constants
 * 
 * This file contains all the main constants, you will often work with it and you need to define your own.
 * You will find here constants like AUTHORNAME and SITENAME, and also path constants.\n
 * Configure others carefully and only if it's really necessary, libraries may require some.\n
 * 
 * Set ERROR_LEVEL to put your website in production (with no error reports to the user).
 * This is compatible with multi-instance architecture, so you can set a dev version and
 * a production version using the same sources on you own server.
 * Official ERROR_LEVEL values are DEV_LEVEL (all errors) and PROD_LEVEL (no errors) and
 * ERROR_LEVEL is set depending on DEV_VERSION value (if set).
 */

defifn('ERROR_LEVEL',		DEV_VERSION && !defined('FORCE_ERRORS') ? DEV_LEVEL : PROD_LEVEL);

defifn('DEV_TOOLS',			DEV_VERSION && (defined('TERMINAL') || !empty($_SERVER['PHP_AUTH_USER'])));

// Theme
defifn('LAYOUT_MENU',		'menu-bootstrap3');

// LIB Initernationalization
defifn('LANGDIR',			'languages/');
defifn('LANG',				'en_US');
defifn('LANGBASE',			'en');
// defifn('LANGBASE',			array_shift(explode('_', LANG, 2)));
defifn('LOCALE',			LANG.'.utf8');

defifn('CACHEPATH',			STOREPATH.'cache/');
defifn('TEMPPATH',			STOREPATH.'temp/');
defifn('FILESTOREPATH',		STOREPATH.'files/');
// defifn('DYNCONFIGPATH',		STOREPATH.'config.json');

defifn('STATIC_URL',		SITEROOT.'static/');

// defifn('JSURL',				SITEROOT.'js/');
defifn('IMAGESURL',			STATIC_URL.'images/');

// Routes' contants
defifn('ROUTE_HOME',			'home');
define('ROUTE_LOGIN',			'login');
define('ROUTE_LOGOUT',			'logout');
define('ROUTE_FILE_DOWNLOAD',	'file_download');
define('ROUTE_DOWNLOAD_LATEST',		'download_latest');
define('ROUTE_DOWNLOAD_RELEASES',	'download_releases');
// define('ROUTE_DASHBOARD',		'user_dashboard');

define('ROUTE_ADM_DEMO',		'admin_demo');
define('ROUTE_ADM_USERS',		'adm_users');
define('ROUTE_ADM_USER',		'adm_user');
define('ROUTE_ADM_MYSETTINGS',	'adm_mysettings');

// Route
defifn('DEFAULTROUTE',			ROUTE_HOME);
defifn('DEFAULTMEMBERROUTE',	ROUTE_ADM_DEMO);
// defifn('DEFAULTMOD',		'home');
defifn('DEFAULTHOST',		'yourdomain.com');
defifn('DEFAULTPATH',		'');

defifn('AUTHORNAME',		'Your name');
defifn('SITENAME',			'Your App Name');// See also translation app_name
defifn('ADMINEMAIL',		'contact@orpheus-framework.com');

define('CRAC_CONTEXT_APPLICATION',	1);
define('CRAC_CONTEXT_AGENCY',		2);
define('CRAC_CONTEXT_RESOURCE',		3);
define('FILE_USAGE_USER_PICTURE',		'user_picture');
define('FILE_USAGE_INVOICE',			'invoice');

function listFileUsages() {
	return array(
		FILE_USAGE_USER_PICTURE		=> array('type' => 'image'),
// 		FILE_USAGE_INVOICE						=> array(),
	);
}

define('FILE_SOURCETYPE_UPLOAD',			'upload');
define('FILE_SOURCETYPE_UPLOAD_CONVERTED',	'upload_converted');
// define('FILE_SOURCETYPE_DATAURI',			'datauri');
define('FILE_SOURCETYPE_PHPQRCODE',			'qrcode');
define('FILE_SOURCETYPE_LOCALDEMO',			'demo');
define('FILE_SOURCETYPE_WKPDF',				'wkpdf');
define('FILE_SOURCETYPE_FACEBOOK',			'fb');

function listFileSourceTypes() {
	return array(FILE_SOURCETYPE_UPLOAD, FILE_SOURCETYPE_UPLOAD_CONVERTED, FILE_SOURCETYPE_PHPQRCODE, FILE_SOURCETYPE_WKPDF, FILE_SOURCETYPE_LOCALDEMO, FILE_SOURCETYPE_FACEBOOK);
}

