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
		foreach( $MENUSCONF->all as $mName => $mModules ) {
			$menu = '';
			foreach( $mModules as $modData ) {
				$CSSClasses = $Link = $Text = '';
				if( $modData[0] == '#' ) {
					text($modData);
					text(substr($modData, 1));
					text(explode('|', substr($modData, 1)));
					list($Link, $Text) = explode('|', substr($modData, 1));
					text($Link);
					text($Text);
					continue;
				} else {
					$modData = explode('-', $modData);
					$module = $modData[0];
					if( !User::canAccess($module) ) {
						continue;
					}
					$action = ( count($modData) > 1 ) ? $modData[1] : '';
					$queryStr = ( count($modData) > 2 ) ? $modData[2] : '';
					$Link = u($module, $action, $queryStr);
					$CSSClasses = $module.' '.(($module == $Module && (!isset($Action) || $Action == $action)) ? 'current' : '');
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