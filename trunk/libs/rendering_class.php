<?php
abstract class Rendering {
	
	protected static $SHOWMODEL = 'show';
	
	public abstract function render($env, $model=null);
	
	public function display($env, $model=null) {
		echo $this->render($env, $model);
	}
	
	private static function show($env=null) {
		if( !isset($env) ) {
			$env = $GLOBALS;
		}
		$r = new static();
		$r->display($env, static::$SHOWMODEL);
		exit();
	}
	
	final public static function doShow() {
		$c = Config::get('default_rendering');
		$c::show();
	}
}