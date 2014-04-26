<?php
//! The HTML rendering class
/*!
	A basic class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL		= 'page_skeleton';
	
	public static $theme			= 'default';
	
	public static $cssPath			= 'css/';
	public static $modelsPath		= 'layouts/';
	
	public static $cssFiles			= array();// CSS files
	public static $jsFiles			= array();// Javascript files
	public static $metaprop			= array();// Meta-properties
	
	//! Renders the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($model=null, $env=array()) {
		ob_start();
		$this->display($model, $env);
		return ob_get_clean();
	}

	//! Displays the model.
	/*!
	 \copydoc Rendering::display()
	*/
	public function display($model=null, $env=array()) {
		if( $model === NULL ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env, EXTR_SKIP);
		
		include static::getModelsPath().$model.'.php';
	}
	
	public static function addCSSFile($basename) {
		static::$cssFiles[] = $basename;
	}
	
	public static function addJSFile($basename) {
		static::$jsFiles[] = $basename;
	}
	
	public static function addMetaProperty($property, $content) {
		static::$metaprop[$property] = $content;
	}
	
	//! Gets the theme path.
	/*!
		\return The theme path.
		
		Gets the path to the current theme.
	*/
	public static function getThemePath() {
		return THEMESDIR.static::$theme.'/';
	}
	
	//! Gets the models theme path.
	/*!
		\return The models theme path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return pathOf(static::getThemePath().static::$modelsPath);
	}

	//! Gets the css theme path.
	/*!
		\return The css theme path.
		
		Gets the path to the css files.
	*/
	public static function getCSSPath() {
		return pathOf(static::getThemePath().static::$cssPath);
	}

	//! Gets the theme path.
	/*!
		\return The theme path.
		
		Gets the URL to the current theme.
	*/
	public static function getThemeURL() {
		return THEMESURL.static::$theme.'/';
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the URL to the CSS files.
	*/
	public static function getCSSURL() {
		return static::getThemeURL().static::$cssPath;
	}
}