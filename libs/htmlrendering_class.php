<?php
//! The HTML rendering class
/*!
	A class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL = 'page_skeleton';
	
	public static $theme = 'default';
	
	public static $themesPath = 'themes/';
	public static $cssPath = 'css/';
	public static $modelsPath = '';
	
	//! Renders the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($env, $model=null) {
		if( !isset($model) ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env);
		$MENUSCONF = Config::build('menus', 1);
		$MENUS = array();
		foreach( $MENUSCONF as $mName => $mModules ) {
			$menu = '';
			foreach( $mModules as $module ) {
				$menu .= "
<li class=\"item {$module}\">{$menu}
</li>";
			}
			if( !empty($menu) ) {
				$menu = "
<ul class=\"menu {$mName}\">{$menu}
</ul>";
			}
			$MENUS[$mName] = $menu;
		}
		include static::getModelsPath().$model.'.php';
	}
	
	//! Gets the models path.
	/*!
		\return The models path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return static::$themesPath.static::$theme.'/'.static::$modelsPath;
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the path to the CSS files.
	*/
	public static function getCSSPath() {
		return static::$themesPath.static::$theme.'/'.static::$cssPath;
	}
}