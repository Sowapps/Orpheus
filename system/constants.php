<?php
/*
 * @author Florent HAZARD <f.hazard@sowapps.com>
 *
 * File to declare constants for IDE
 * Because we are using defifn to declare them in app
 * I'm planning to get it by another way of configuration
 */

const ACCESS_PATH = __DIR__ . '/../public';
const ORPHEUS_PATH = '..';
const APPLICATION_PATH = ORPHEUS_PATH;
const INSTANCE_PATH = APPLICATION_PATH;
const CONFIG_FOLDER = '/config';
const LANG_FOLDER = '/languages';
const THEMES_FOLDER = '/themes';
const COMPOSER_HOME = INSTANCE_PATH . '/.composer';
const SRC_PATH = APPLICATION_PATH . '/src';
const STORE_PATH = INSTANCE_PATH . '/store';
const CACHE_PATH = STORE_PATH . '/cache';
const LOGS_PATH = STORE_PATH . '/logs';
const TEMP_PATH = STORE_PATH . '/temp';
const TRANSLATIONS_PATH = STORE_PATH . '/translations';
const FILE_STORE_PATH = STORE_PATH . '/files';
const VENDOR_PATH = INSTANCE_PATH . '/vendor';
const THEMES_PATH = ACCESS_PATH . THEMES_FOLDER;
const TIME = 1000000;
const DEFAULT_LOCALE = 'en_US';
const HTTPS = true;
const DEFAULT_HOST = 'domain.com';
const SCHEME = 'https';
const HOST = SCHEME;
const PATH = '/';
const WEB_ROOT = SCHEME . '://' . HOST . (PATH !== '/' ? PATH : '');
const STATIC_ASSETS_URL = WEB_ROOT . '/static';
const STYLE_URL = STATIC_ASSETS_URL . '/style';
const VENDOR_URL = STATIC_ASSETS_URL . '/vendor';
const IMAGES_URL = STATIC_ASSETS_URL . '/images';
const JS_URL = STATIC_ASSETS_URL . '/js';
const INSTANCE_ID = 'orpheus.local';
const DOMAIN_COMPOSER = 'composer';
const DOMAIN_SETUP = 'setup';
const DOMAIN_TRANSLATIONS = 'translations';
const DEFAULT_ROUTE = 'home';
const DEFAULT_MEMBER_ROUTE = DEFAULT_ROUTE;

const CHECK_MODULE_ACCESS = true;
const SESSION_COOKIE_LIFETIME = 86400 * 7;
const SESSION_COOKIE_PATH = '/';
const SESSION_SHARE_ACROSS_SUBDOMAIN = false;

const ADMIN_EMAIL = 'admin@domain.com';
const DEV_EMAIL = 'developer@domain.com';
const REPLY_EMAIL = 'no-reply@domain.com';
const AUTHOR_NAME = 'Developer';
const AUTHOR_WEBSITE = 'https://developer.com/';
const EMAIL_SENDER_NAME = 'Application name';
