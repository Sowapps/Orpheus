<?php
//! Initernationalization
/*!
 * Translation plugin using ini files
 * 
 * Require declaration of constants: LANGPATH, LANG.
 */

//! Loads a language ini file
/*!
 * \param $domain The domain of the file to load.
 * 
 * Loads a language ini file from the file system.
 * You don't have to use this function explicitly.
 */
function loadLangFile($domain=null) {
	if( !empty($domain) && is_readable(LANGPATH.'/'.LANG.'/'.$domain.'.ini') ) {
		$GLOBALS['LANG'][$domain] = parse_ini_file(LANGPATH.'/'.LANG.'/'.$domain.'.ini');
		
	} else if( is_readable(LANGPATH.'/'.LANG.'.ini') ) {
		$GLOBALS['LANG'] = parse_ini_file(LANGPATH.'/'.LANG.'.ini');
	}
}

//! Text function, for translations.
/*!
 * \param $k The Key to translate, prefer to use an internal language (English CamelCase).
 * \param $domain The domain to apply the Key. Default value is 'global'.
 * \param $values The values array to replace in text. Could be used as second parameter.
 * \return The translated human text.
 * 
 * This function try to translate the given key, in case of failure, it just returns the Key.
 * It try to replace $values in text by key using #key# format.
 * $values accept 2 formats:
 *  - array('key1'=>'value1', 'key2'=>'value2')
 *  - array(array('key1', 'key2'), array('value1', 'value2'))
 */
function t($k, $domain='global', $values=array()) {
	global $LANG;
	if( is_array($domain) ) {
		$values = $domain;
		$domain = 'global';
	}
	if( !isset($LANG[$domain]) ) {
		loadLangFile($domain);
	}
	$r = ( isset($LANG[$domain]) && isset($LANG[$domain][$k]) ) ? $LANG[$domain][$k] : $k;
	if( !empty($values) ) {
		if( !empty($values[0]) ) {
			$rkeys = $values[0];
			$rvalues = !empty($values[1]) ? $values[1] : '';
		} else {
			$rkeys = array_map(function ($v) { return "#{$v}#"; }, array_keys($values));
			$rvalues = array_values($values);
		}
		$r = str_replace( $rkeys, $rvalues, $r);
	}
	return $r;
}