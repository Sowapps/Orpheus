<?php
//! The HTML rendering class
/*!
	A class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL = 'page_skeleton';
	
	public static $theme = 'default';
	
	public static $themesPath = 'themes/';//Static rejects DS
	public static $cssPath = 'css/';//Static rejects DS
	public static $modelsPath = '';//Static rejects DS
	
	//! Render the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($env, $model=null) {
		if( !isset($model) ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env);
		include static::getModelsPath().$model.'.php';
	}
	
	//! Get the models path.
	/*!
		\return The models path.
		
		Get the path to the models.
	*/
	public static function getModelsPath() {
		return static::$themesPath.static::$theme.DS.static::$modelsPath;
	}
	
	
	//! Get the CSS files path.
	/*!
		\return The CSS path.
		
		Get the path to the CSS files.
	*/
	public static function getCSSPath() {
		return static::$themesPath.static::$theme.DS.static::$cssPath;
	}
}