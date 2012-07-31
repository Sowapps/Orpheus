<?php
//! The rendering class
/*!
	This class is the core for custom rendering use.
*/
abstract class Rendering {
	
	protected static $SHOWMODEL = 'show';
	
	//! Renders the model.
	/*!
		\param $env An environment variable, commonly an array but depends on the rendering class used.
		\param $model The model to use, default use is defined by child.
		
		Renders the model using $env.
		This function does not display the result, see display().
	*/
	public abstract function render($env, $model=null);
	
	//! Displays rendering.
	/*!
		\param $env An environment variable.
		\param $model The model to use.
		
		Displays the model rendering using $env.
	*/
	public function display($env, $model=null) {
		echo $this->render($env, $model);
	}
	
	//! Shows the rendering using a child rendering class.
	/*!
		\param $env An environment variable.
		\attention Require the use of a child class, you can not instantiate this one.
		
		Shows the $SHOWMODEL rendering using the child class.
		A call to this function terminate the running script.
		Default is the global environment.
	*/
	private static function show($env=null) {
		if( !isset($env) ) {
			$env = $GLOBALS;
		}
		$r = new static();
		$r->display($env, static::$SHOWMODEL);
		exit();
	}
	
	//! Calls the show function.
	/*!
		Calls the show function using the 'default_rendering' configuration.
	*/
	final public static function doShow() {
		$c = Config::get('default_rendering');
		$c::show();
	}
}