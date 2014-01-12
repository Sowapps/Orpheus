<?php
//! The HTML rendering class
/*!
	A basic class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL = 'page_skeleton';
	
	public static $theme			= 'default';
	
	public static $cssPath			= 'css/';
	public static $modelsPath		= 'layouts/';
	
	//! Renders the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($model=null, $env=array()) {
		if( !isset($model) ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env);
		
		include static::getModelsPath().$model.'.php';
	}
	
// 	public function renderMenu($menu, $items, $layout) {
// 		global $USER_CLASS;
// 		if( !isset(static::$menusConf) ) {
// 			static::$menusConf = Config::build('menus', true);
// 		}
// 		if( empty(static::$menusConf) || empty(static::$menusConf[$menu]) ) { return false; }
		
// 	}
	
	//! Gets the models path.
	/*!
		\return The models path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return pathOf(THEMESDIR.static::$theme.'/'.static::$modelsPath);
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the path to the CSS files.
	*/
	public static function getCSSPath() {
		return static::getThemeURL().static::$cssPath;
	}

	//! Gets the theme path.
	/*!
		\return The theme path.
		
		Gets the path to the theme files.
	*/
	public static function getThemeURL() {
		return THEMESURL.static::$theme.'/';
	}
}