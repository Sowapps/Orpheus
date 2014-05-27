<?php
//! The rendering class
/*!
	This class is the core for custom rendering use.
*/
abstract class Rendering {
	
	protected static $SHOWMODEL = 'show';
	private static $rendering;
	private static $menusConf;
	
	//! Renders the model.
	/*!
	 * \param $env An environment variable, commonly an array but depends on the rendering class used.
	 * \param $model The model to use, default use is defined by child.
	 * \return The generated rendering.
	 * 
	 * Renders the model using $env.
	 * This function does not display the result, see display().
	 */
	public abstract function render($model=null, $env=array());
	
	//! Displays rendering.
	/*!
	 * \param $env An environment variable.
	 * \param $model The model to use.
	 * 
	 * Displays the model rendering using $env.
	 */
	public function display($model=null, $env=array()) {
		echo $this->render($model, $env);
	}
	
	public function getMenuItems($menu) {
		if( !isset(self::$menusConf) ) {
			self::$menusConf = Config::build('menus', true);
		}
		if( empty(self::$menusConf) || empty(self::$menusConf->$menu) ) {
			return array();
		}
		return self::$menusConf->$menu;
	}
	
	//! Displays rendering.
	/*!
	 * \param $env		An environment variable.
	 * \param $model	The model to use.
	 * \param $active	The active menu item.
	 * 
	 * Displays the model rendering using $env.
	 */
	public function showMenu($menu, $layout=null, $active=null) {
// 		self::checkRendering();
		global $USER_CLASS;
		if( !class_exists($USER_CLASS) ) { return false; }
		
		if( $active!==NULL ) {
			list($actModule, $actAction) = explodeList('-', $active, 2);
		} else {
			$actModule	= &$GLOBALS['Module'];
			$actAction	= &$GLOBALS['Action'];
		}
		
		if( $layout===NULL ) {
			$layout	= defined('LAYOUT_MENU') ? LAYOUT_MENU : 'menu-default';
		}
		
		$env	= array('menu'=>$menu, 'items'=>array());
		$items	= $this->getMenuItems($menu);
		if( empty($items) ) { return false; }
		foreach( $items as $itemConf ) {
			if( empty($itemConf) ) { continue; }
			$item = new stdClass;
			if( $itemConf[0] == '#' ) {
				list($item->link, $item->label) = explode('|', substr($itemConf, 1));
			} else {
				$itemConf	= explode('-', $itemConf);
				$module		= $itemConf[0];
				if( !existsPathOf(MODDIR.$module.'.php') || !$USER_CLASS::canAccess($module)
					|| !Hook::trigger('menuItemAccess', true, true, $module) ) { continue; }
				$action			= count($itemConf) > 1 ? $itemConf[1] : '';
				if( $action == 'ACTION' ) { $action = $GLOBALS['Action']; }
				$queryStr		= count($itemConf) > 2 ? $itemConf[2] : '';
				$item->link		= u($module, $action, $queryStr);
				$item->label	= ( !empty($action) && hasTranslation($module.'_'.$action) ) ? t($module.'_'.$action) : t($module);
				$item->module	= $module;
				if( $module==$actModule && ($actAction===NULL || $actAction==$action) ) {
					$item->current = 1;
				}
			}
			$env['items'][] = $item;
		}
		
		$this->display($layout, $env);
	}
	
	//! Shows the rendering using a child rendering class.
	/*!
	 * \param $env An environment variable.
	 * \attention Require the use of a child class, you can not instantiate this one.
	 * 
	 * Shows the $SHOWMODEL rendering using the child class.
	 * A call to this function terminate the running script.
	 * Default is the global environment.
	 */
	private static function show($env=null) {
		if( !isset($env) ) {
			$env = $GLOBALS;
		}

		self::checkRendering();
		self::$rendering->display(static::$SHOWMODEL, $env);
		
		exit();
	}
	
	//! Calls the show function.
	/*!
	 * \sa show()
	 * Calls the show function using the 'default_rendering' configuration.
	 */
	final public static function doShow() {
		$c = self::checkRendering();
		$c::show();
	}
	
	//! Calls the render function.
	/*!
	 * \param $env An environment variable.
	 * \param $model The model to use.
	 * \return The generated rendering.
	 * \sa render()
	 * 
	 * Calls the render function using the 'default_rendering' configuration.
	 */
	final public static function doRender($model=null, $env=array()) {
		self::checkRendering();
		return self::$rendering->render($model, $env);
	}
	
	//! Calls the display function.
	/*!
	 * \param $model The model to use. Default value is null (behavior depending on renderer).
	 * \param $env An array containing environment variables. Default value is null ($GLOBALS).
	 * \sa display()
	 * 
	 * Calls the display function using the 'default_rendering' configuration.
	 */
	final public static function doDisplay($model=null, $env=null) {
		self::checkRendering();
		if( !isset(self::$rendering) ) { return false; }
		if( $env === NULL ) { $env = $GLOBALS; }
		self::$rendering->display($model, $env);
		return true;
	}
	
	//! Checks the rendering
	/*!
	 * Checks the rendering and try to create a valid one.
	 */
	final private static function checkRendering() {
		if( is_null(self::$rendering) ) {
			if( class_exists('Config') ) {
				$c = Config::get('default_rendering');
			}
			if( !isset($c) ) {
				$c = defined("TERMINAL") ? 'RawRendering' : 'HTMLRendering';
			}
			if( !class_exists($c) ) {
				log_error('Rendering class "'.$c.'" should be loaded : '.print_r(debug_backtrace(), 1));
				die();
			}
			self::$rendering = new $c();
		}
		return get_class(self::$rendering);
	}

	protected static $layoutStack = null;
	
	//! Uses layout until the next endCurrentLayout()
	/*!
	 * \param $layout The layout to use.
	 * \sa endCurrentLayout()
	 * 
	 * Uses layout until the next endCurrentLayout() is encountered.
	 * 
	 * Warning: According to the ob_start() documentation, you can't call functions using output buffering in your layout.
	 * http://www.php.net/manual/en/function.ob-start.php#refsect1-function.ob-start-parameters
	 */
	public static function useLayout($layout) {
		if( is_null(static::$layoutStack) ) {
			static::$layoutStack = array();

// // 			Hook::register('checkModule', function($module, $content) {});
		}

		static::$layoutStack[] = $layout;
		ob_start();
		// Does not work, we can not start output buffer in this callback
// 		ob_start(function($content) use($layout) {
// 			$env = $GLOBALS;
// 			$env['Content'] = $content;
// 			return static::doRender($layout, $env);
// 		});
	}
	
	public static function endCurrentLayout() {
// 		text(__FILE__.':'.__LINE__);
		if( ob_get_level() < OBLEVEL_INIT+1 || empty(static::$layoutStack) ) { return false; }
// 		text(__FILE__.':'.__LINE__);
		$env = $GLOBALS;
		$env['Content'] = ob_get_clean();
// 		$env['Content'] = ob_get_flush();// Returns and displays
// 		text(__FILE__.':'.__LINE__);
		static::doDisplay(array_pop(static::$layoutStack), $env);
// 		text(__FILE__.':'.__LINE__);
		return true;
	}
	
// 	final public static function endCurrentLayout() {
// 		if( ob_get_level() <= OBLEVEL_INIT+1 ) { return false; }
// 		ob_end_flush();
// 		return true;
// 	}
}