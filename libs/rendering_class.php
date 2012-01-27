<?php
abstract class Rendering {
	
	protected static $SHOWMODEL = 'show';
	
	public abstract static function render($env, $model=null);
	
	public static function display($env, $model=null) {
		echo static::render($env);
	}
	
	public static function show($env=null) {
		if( !isset($env) ) {
			$env = $GLOBALS;
		}
		static::display($env, static::$SHOWMODEL);
		exit();
	}
	
	final private static function doShow() {
		${Config::get('default_rendering')}::show();
	}
}