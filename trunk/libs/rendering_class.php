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
		\return The generated rendering.
		
		Renders the model using $env.
		This function does not display the result, see display().
	*/
	public abstract function render($model=null, $env=array());
	
	//! Displays rendering.
	/*!
		\param $env An environment variable.
		\param $model The model to use.
		
		Displays the model rendering using $env.
	*/
	public function display($model=null, $env=array()) {
		echo $this->render($model, $env);
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
		text("Page is ".((isset($env['Page'])) ? 'defined' : 'not defined')." and its length is ".strlen($env['Page']));
		
		// Menus' things
		$MENUSCONF = Config::build('menus', true);
		$MENUS = array();
		foreach( $MENUSCONF->all as $mName => $mModules ) {
			$menu = '';
			foreach( $mModules as $modData ) {
				$CSSClasses = $Link = $Text = '';
				if( $modData[0] == '#' ) {
					list($Link, $Text) = explode('|', substr($modData, 1));
				} else {
					$modData = explode('-', $modData);
					$module = $modData[0];
					if( !User::canAccess($module) ) {
						continue;
					}
					$action = ( count($modData) > 1 ) ? $modData[1] : '';
					$queryStr = ( count($modData) > 2 ) ? $modData[2] : '';
					$Link = u($module, $action, $queryStr);
					$CSSClasses = $module.' '.(($module == $GLOBALS['Module'] && (!isset($Action) || $Action == $action)) ? 'current' : '');
					$Text = $module;
				}
				$menu .= "
		<li class=\"item {$CSSClasses}\"><a href=\"{$Link}\">".t($Text)."</a></li>";
			}
			if( !empty($menu) ) {
				$menu = "
	<ul class=\"menu {$mName}\">{$menu}
	</ul>";
			}
			$MENUS[$mName] = $menu;
		}
		$env['MENUS'] = &$MENUS;
		
		$r = new static();
		$r->display(static::$SHOWMODEL, $env);
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