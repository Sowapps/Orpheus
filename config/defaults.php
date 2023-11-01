<?php
/**
 * All web site defaults.
 */

defifn('ENTITY_CLASS_CHECK', false);

// Routes
const ROUTE_HOME = 'home';
const ROUTE_USER_LOGIN = 'user_login';
const ROUTE_USER_LOGOUT = 'user_logout';
const ROUTE_FILE_DOWNLOAD = 'file_download';
const ROUTE_DOWNLOAD_LATEST = 'download_latest';
const ROUTE_DOWNLOAD_RELEASES = 'download_releases';

const ROUTE_ADM_HOME = 'admin_home';
const ROUTE_ADM_USER_LIST = 'adm_user_list';
const ROUTE_ADM_USER = 'adm_user';
const ROUTE_ADM_MY_SETTINGS = 'adm_my_settings';

const ROUTE_DEV_HOME = 'dev_home';
const ROUTE_DEV_CONFIG = 'dev_config';
const ROUTE_DEV_SYSTEM = 'dev_system';
const ROUTE_DEV_COMPOSER = 'dev_composer';
const ROUTE_DEV_ENTITIES = 'dev_entities';
const ROUTE_DEV_LOGS = 'dev_logs';
const ROUTE_DEV_LOG_VIEW = 'dev_log_view';
const ROUTE_DEV_APP_TRANSLATE = 'dev_translate';

// Route's defaults
const DEFAULT_ROUTE = ROUTE_HOME;
const DEFAULT_MEMBER_ROUTE = ROUTE_HOME;

// Application's Identity
// TODO Move application's identity to file ".env"
// The official domain of the application. Used when there is no HTTP_HOST environment variable, which is in console.
const DEFAULT_HOST = 'APP_DOMAIN_IS_MISSING';
// Company/Developer/Both who developed this application.
const AUTHOR_NAME = 'AUTHOR_IS_MISSING';
// The public contact administrator's email. Used as sender of automatic email and recipient of contact form.
const ADMIN_EMAIL = 'ADMIN_EMAIL_IS_MISSING';
// The email to contact the developer. Used in rare cases.
const DEV_EMAIL = 'DEV_EMAIL_IS_MISSING';

