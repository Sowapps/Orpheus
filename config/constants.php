<?php
/** \file
 * All website constants.
 *
 * @page constants Constants
 *
 * This file contains all the main constants, you will often work with it, and you need to define your own.
 * You will find here constants like AUTHOR_NAME and also path constants.\n
 * Configure others carefully and only if it's really necessary, libraries may require some.\n
 *
 * Set ERROR_LEVEL to put your website in production (with no error reports to the user).
 * This is compatible with multi-instance architecture, so you can set a dev version and
 * a production version using the same sources on you own server.
 * Official ERROR_LEVEL values are ERROR_DEBUG_LEVEL (all errors) and ERROR_PROD_LEVEL (no errors) and
 * ERROR_LEVEL is set depending on DEV_VERSION value (if set).
 */

// Initernationalization
defifn('LANG_FOLDER', '/languages');

// Static medias
defifn('THEMES_URL', WEB_ROOT . THEMES_FOLDER);
defifn('STATIC_ASSETS_URL', WEB_ROOT . '/static');
defifn('STYLE_URL', STATIC_ASSETS_URL . '/style');
defifn('VENDOR_URL', STATIC_ASSETS_URL . '/vendor');
defifn('IMAGES_URL', STATIC_ASSETS_URL . '/images');
defifn('JS_URL', STATIC_ASSETS_URL . '/js');

// Time
defifn('DEFAULT_TIMEZONE', 'Europe/Paris');
defifn('DATE_SQL_DATETIME', 'Y-m-d H:i:s');
defifn('DATE_SQL_DATE', 'Y-m-d');
defifn('SYSTEM_TIME_FORMAT', '%H:%M');

const CRAC_CONTEXT_APPLICATION = 1;
const CRAC_CONTEXT_AGENCY = 2;
const CRAC_CONTEXT_RESOURCE = 3;

const FILE_USAGE_USER_PICTURE = 'user_picture';
const FILE_USAGE_INVOICE = 'invoice';

function listFileUsages(): array {
	return [
		FILE_USAGE_USER_PICTURE => ['type' => 'image'],
	];
}

const FILE_SOURCE_TYPE_UPLOAD = 'upload';
const FILE_SOURCE_TYPE_UPLOAD_CONVERTED = 'upload_converted';
const FILE_SOURCE_TYPE_DATA_URI = 'datauri';
const FILE_SOURCE_TYPE_WKPDF = 'wkpdf';

function listFileSourceTypes(): array {
	return [FILE_SOURCE_TYPE_UPLOAD, FILE_SOURCE_TYPE_UPLOAD_CONVERTED, FILE_SOURCE_TYPE_DATA_URI, FILE_SOURCE_TYPE_WKPDF];
}

const DOMAIN_APP = 'application';
const DOMAIN_SETUP = 'setup';
const DOMAIN_TRANSLATIONS = 'translations';
const DOMAIN_USER = 'user';

defifn('TRANSLATIONS_PATH', STORE_PATH . '/translations');
