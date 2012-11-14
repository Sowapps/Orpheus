<?php
//! The HTML rendering class
/*!
	A class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL = 'page_skeleton';
	
	public static $theme = 'default';
	
	//public static $themesPath = 'themes/';
	public static $cssPath = 'css/';
	public static $modelsPath = '';
	
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
	
	//! Gets the models path.
	/*!
		\return The models path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return THEMESPATH.static::$theme.'/'.static::$modelsPath;
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the path to the CSS files.
	*/
	public static function getCSSPath() {
		return THEMESPATH.static::$theme.'/'.static::$cssPath;
	}
}