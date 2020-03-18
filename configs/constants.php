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

defifn('ERROR_LEVEL', DEV_VERSION && !defined('FORCE_ERRORS') ? DEV_LEVEL : PROD_LEVEL);

defifn('DEV_TOOLS', DEV_VERSION && (defined('TERMINAL') || !empty($_SERVER['PHP_AUTH_USER'])));

// Theme
defifn('LAYOUT_MENU', 'menu-bootstrap3');

// LIB Initernationalization
defifn('LANGDIR', 'languages/');
defifn('DEFAULT_LOCALE', 'en_US');

defifn('CACHEPATH', STOREPATH . 'cache/');
defifn('TEMPPATH', STOREPATH . 'temp/');
defifn('FILESTOREPATH', STOREPATH . 'files/');

defifn('STATIC_URL', SITEROOT . 'static/');

defifn('IMAGESURL', STATIC_URL . 'images/');


// Contact

defifn('AUTHORNAME', 'Your name');
defifn('SITENAME', 'Your App Name');// See also translation app_name
defifn('ADMINEMAIL', 'contact@yourdomain.com');
defifn('DEVEMAIL', 'contact@devdomain.com');

// Users
//define('USER_SALT',			'');
defifn('DEFAULT_TIMEZONE', 'Europe/Paris');
defifn('DATE_SQL_DATETIME', 'Y-m-d H:i:s');
defifn('DATE_SQL_DATE', 'Y-m-d');

define('CRAC_CONTEXT_APPLICATION', 1);
define('CRAC_CONTEXT_AGENCY', 2);
define('CRAC_CONTEXT_RESOURCE', 3);

define('FILE_USAGE_USER_PICTURE', 'user_picture');
define('FILE_USAGE_INVOICE', 'invoice');

function listFileUsages() {
	return [
		FILE_USAGE_USER_PICTURE => ['type' => 'image'],
	];
}

define('FILE_SOURCETYPE_UPLOAD', 'upload');
define('FILE_SOURCETYPE_UPLOAD_CONVERTED', 'upload_converted');
// define('FILE_SOURCETYPE_DATAURI',			'datauri');
define('FILE_SOURCETYPE_PHPQRCODE', 'qrcode');
define('FILE_SOURCETYPE_LOCALDEMO', 'demo');
define('FILE_SOURCETYPE_WKPDF', 'wkpdf');
define('FILE_SOURCETYPE_FACEBOOK', 'fb');

function listFileSourceTypes() {
	return [FILE_SOURCETYPE_UPLOAD, FILE_SOURCETYPE_UPLOAD_CONVERTED, FILE_SOURCETYPE_PHPQRCODE, FILE_SOURCETYPE_WKPDF, FILE_SOURCETYPE_LOCALDEMO, FILE_SOURCETYPE_FACEBOOK];
}

defifn('DOMAIN_TRANSLATIONS', 'translations');

defifn('TRANSLATIONS_PATH', STOREPATH . 'translations/');

