<?php
/** Initernationalization

 * Translation plugin using ini files
 * 
 * Require declaration of constants: LANGDIR, LANG.
 */

define('HOOK_GETLANG', 'getDomainLang');
Hook::create(HOOK_GETLANG);

/** Loads a language ini file

 * @param $domain The domain of the file to load.
 * 
 * Loads a language ini file from the file system.
 * You don't have to use this function explicitly.
 */
function loadLangFile($domain='global') {
	global $LANG, $APP_LANG;
	if( isset($LANG[$domain]) ) { return; }
	if( !isset($APP_LANG) ) {
		$APP_LANG	= Hook::trigger(HOOK_GETLANG, true, LANG, $domain);
	}
	if( !empty($domain) && existsPathOf(LANGDIR.'/'.$APP_LANG.'/'.$domain.'.ini') ) {
		$GLOBALS['LANG'][$domain] = parse_ini_file(pathOf(LANGDIR.'/'.$APP_LANG.'/'.$domain.'.ini'));
		
	} else if( existsPathOf(LANGDIR.'/'.$APP_LANG.'.ini') ) {
		$GLOBALS['LANG'] = parse_ini_file(pathOf(LANGDIR.'/'.$APP_LANG.'.ini'));
	}
}

/** Text function for translations.

 * @param $k The Key to translate, prefer to use an internal language (English CamelCase).
 * @param $domain The domain to apply the Key. Default value is 'global'.
 * @param $values The values array to replace in text. Could be used as second parameter.
 * @return The translated human text.
 * 
 * This function try to translate the given key, in case of failure, it just returns the Key.
 * It tries to replace $values in text by key using \#key\# format using str_replace() but if $values is a list of values, it uses sprintf().
 * $values allows 3 formats:
 *  - array('key1'=>'value1', 'key2'=>'value2'...)
 *  - array(array('key1', 'key2'...), array('value1', 'value2'...))
 *  - array('value1', 'value2'...)
 *  This function is variadic, you can specify values with more scalar arguments.
 *  
 *  Examples: t('untranslatedString', 'aDomain'), t('My already translated string'), t('untranslatedString', 'global', array('key1'=>'val1')), t('untranslatedString', 'global', 'val1', 60)
 */
function t($k, $domain='global', $values=array()) {
	global $LANG;
	if( is_array($domain) ) {
		$values = $domain;
		$domain = 'global';
	}
	$k	= "$k";
	$r	= hasTranslation($k, $domain) ? $LANG[$domain][$k] : $k;
	while( isset($r[0]) && $r[0]=='%' ) {
		$k = substr($r, 1);
		if( hasTranslation($k, $domain) ) {
			$r = $LANG[$domain][$k];
		} else {
			break;
		}
	}
	if( !empty($values) ) {
		if( !is_array($values) ) {
			$values		= array_slice(func_get_args(), 2);
		}
		if( !empty($values[0]) ) {
			if( !is_array($values[0]) ) {
				return vsprintf($r, $values);
			}
			$rkeys		= $values[0];
			$rvalues	= !empty($values[1]) ? $values[1] : '';
		} else {
// 			text('Mapping keys');
			$rkeys		= array_map(function ($v) { return "#{$v}#"; }, array_keys($values));
			$rvalues	= array_values($values);
// 			text($rkeys);
// 			text($rvalues);
		}
		$r = str_replace($rkeys, $rvalues, $r);
	}
	return $r;
}
function _t($k, $domain='global', $values=array()) {
	echo t($k, $domain, $values);
}

/** Checks if this key exists.

 * @param $k The Key to translate, prefer to use an internal language (English CamelCase).
 * @param $domain The domain to apply the Key. Default value is 'global'.
 * @return True if the translation exists in this domain.
 * 
 * This function checks if the key is known in the translation list.
 */
function hasTranslation($k, $domain='global') {
	global $LANG;
	loadLangFile($domain);
	return isset($LANG[$domain]) && isset($LANG[$domain][$k]);
}

/** Checks if this key exists.

 * @param $k The Key to translate, prefer to use an internal language (English CamelCase).
 * @param $default The default translation value to use.
 * @param $domain The domain to apply the Key. Default value is 'global'.
 * @return The translation
 * 
 * This function translate the key without any fail.
 * If no translation is available, it uses the $default.
 */
function translate($k, $default, $domain='global') {
	return hasTranslation($k, $domain) ? t($k, $domain) : $default;
}

if( hasTranslation('locale') ) {
	setlocale(LC_ALL, t('locale'));
} else
if( defined('LOCALE') ) {
	setlocale(LC_ALL, LOCALE);
}

function tc($k) {
	if( hasTranslation($k) ) { return t($k); }
	global $LOCALECONV;
	if( !isset($LOCALECONV) ) {
		$LOCALECONV	= localeconv();
	}
	return isset($LOCALECONV[$k]) ? $LOCALECONV[$k] : null;
}

function sanitizeNumber($value) {
	return str_replace(array(tc('decimal_point'), tc('thousands_sep')), array('.', ''), $value);
}

// define('LOCALE_', 'decimal_point');
