<?php
//! Initerlization
/*!
 * Translation plugin using ini files
 */

//! Loads a language ini file
/*
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

//! Translation function, do nothing very special for the moment.
function t($k, $domain='global') {
	global $LANG;
	if( !isset($LANG[$domain]) ) {
		loadLangFile($domain);
	}
	return ( isset($LANG[$domain]) && isset($LANG[$domain][$k]) ) ? $LANG[$domain][$k] : $k;
}