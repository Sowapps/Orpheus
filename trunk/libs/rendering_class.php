<?php
abstract class Rendering {
	
	protected static $SHOWMODEL = 'show';
	
	public abstract function render($env, $model=null);
	
	public function display($env, $model=null) {
		echo $this->render($env);
	}
	
	public static function show($env=null) {
		if( !isset($env) ) {
			$env = $GLOBALS;
		}
		$r = new static();
		$r->display($env, static::$SHOWMODEL);
		exit();
	}
	
	final private static function doShow() {
		${Config::get('default_rendering')}::show();
	}
}