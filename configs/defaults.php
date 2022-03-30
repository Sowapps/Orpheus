<?php
/**
 * All web site defaults.
 */

defifn('ENTITY_CLASS_CHECK', false);

// Routes
defifn('ROUTE_HOME', 'home');
define('ROUTE_LOGIN', 'login');
define('ROUTE_LOGOUT', 'logout');
define('ROUTE_FILE_DOWNLOAD', 'file_download');
define('ROUTE_DOWNLOAD_LATEST', 'download_latest');
define('ROUTE_DOWNLOAD_RELEASES', 'download_releases');

define('ROUTE_ADM_DEMO', 'admin_demo');
define('ROUTE_ADM_HOME', ROUTE_ADM_DEMO);
define('ROUTE_ADM_USERS', 'adm_users');
define('ROUTE_ADM_USER', 'adm_user');
define('ROUTE_ADM_MYSETTINGS', 'adm_mysettings');

define('ROUTE_DEV_HOME', 'dev_home');
define('ROUTE_DEV_CONFIG', 'dev_config');
define('ROUTE_DEV_SYSTEM', 'dev_system');
define('ROUTE_DEV_COMPOSER', 'dev_composer');
define('ROUTE_DEV_ENTITIES', 'dev_entities');
define('ROUTE_DEV_LOGS', 'dev_loglist');
define('ROUTE_DEV_LOG_VIEW', 'dev_log_view');
define('ROUTE_DEV_APPTRANSLATE', 'dev_app_translate');

// Route's defaults
defifn('DEFAULT_ROUTE', ROUTE_HOME);
defifn('DEFAULT_MEMBER_ROUTE', ROUTE_ADM_DEMO);
defifn('DEFAULT_HOST', 'yourdomain.com');

