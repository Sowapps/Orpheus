<?php
/** The HTML rendering class

	A basic class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL		= 'page_skeleton';
	
	/**
	 * @var string
	 */
	public static $theme			= 'default';
	
	public static $cssPath			= 'css/';
	public static $jsPath			= 'js/';
	public static $modelsPath		= 'layouts/';
	
	public static $cssURLs			= array();// CSS files
	public static $jsURLs			= array();// Javascript files
	public static $metaprop			= array();// Meta-properties

	const LINK_TYPE_PLUGIN	= 1;
	const LINK_TYPE_CUSTOM	= 2;
	
	/** 
	 * Render the model.
	 * @copydoc Rendering::render()
	 */
	public function render($model=null, $env=array()) {
		ob_start();
		$this->display($model, $env);
		return ob_get_clean();
	}

	/** 
	 * Display the model, allow an absolute path to the template file.
	 * @copydoc Rendering::display()
	 */
	public function display($model=null, $env=array()) {
		if( $model === NULL ) {
			throw new Exception("Invalid Rendering Model");
		}
		$rendering = $this->getCurrentRendering();
		if( $rendering ) {
// 			$env = array_merge($env, $rendering[1]);
			$env += $rendering[1];
		}
		
		// TODO Merge layoutStack and rendering stack
		$prevLayouts	= count(static::$layoutStack);
		$this->pushToStack($model, $env);
		
		extract($env, EXTR_SKIP);
		include static::getModelPath($model);
		
		$this->pullFromStack();
		$currentLayouts	= count(static::$layoutStack);
		while( $currentLayouts > $prevLayouts && static::endCurrentLayout($env) ) {
			$currentLayouts--;
		}
	}
	
	/**
	 * Set the default theme used to render layouts
	 * @param string $theme
	 */
	public static function setDefaultTheme($theme) {
		static::$theme	= $theme;
	}
	
	public static function getModelPath($model) {
		return is_readable($model) ? $model : static::getModelsPath().$model.'.php';
	}
	
	public static function renderReport($report, $domain, $type, $stream) {
		$report	= nl2br($report);
		if( file_exists(static::getModelPath('report-'.$type)) ) {
			return static::doRender('report-'.$type, array('Report'=>$report, 'Domain'=>$domain, 'Type'=>$type, 'Stream'=>$stream));
		}
		if( file_exists(static::getModelPath('report')) ) {
			return static::doRender('report', array('Report'=>$report, 'Domain'=>$domain, 'Type'=>$type, 'Stream'=>$stream));
		}
		return '
		<div class="report report_'.$stream.' '.$type.' '.$domain.'">'.nl2br($report).'</div>';
	}
	
	public static function addThemeCSSFile($filename, $type=null) {
		static::addCSSURL(static::getCSSURL().$filename, $type);
	}
	public static function addCSSFile($filename, $type=null) {
		static::addThemeCSSFile($filename, $type);
	}
	public static function addCSSURL($url, $type=null) {
// 		static::$cssURLs[]	= $url;
		static::addTypedURL(static::$cssURLs, $url, $type);
	}
	
	public static function addThemeJSFile($filename, $type=null) {
		static::addJSURL(static::getJSURL().$filename, $type);
	}
	public static function addJSFile($filename, $type=null) {
		static::addJSURL(JSURL.$filename, $type);
	}
	public static function addJSURL($url, $type=null) {
		static::addTypedURL(static::$jsURLs, $url, $type);
	}
	
	public static function addMetaProperty($property, $content) {
		static::$metaprop[$property] = $content;
	}
	
	public static function listCSSURLs($type=null) {
		return static::listTypedURL(static::$cssURLs, $type);
	}
	
	public static function listJSURLs($type=null) {
		return static::listTypedURL(static::$jsURLs, $type);
	}
	
	protected static function addTypedURL(&$array, $url, $type=null) {
		if( !$type ) {
			$type	= self::LINK_TYPE_CUSTOM;
		}
		if( !isset($array[$type]) ) {
			$array[$type]	= array();
		}
		$array[$type][]	= $url;
	}
	
	protected static function listTypedURL(&$array, $type=null) {
		if( $type ) {
			if( !isset($array[$type]) ) {
				return array();
			}
			$r	= $array[$type];
			unset($array[$type]);
			return $r;
		}
		$r	= array();
		foreach( $array as $type => $typeURLs ) {
			if( !is_array($typeURLs) ) {
				debug('$array', $array);
				die();
			}
			$r	= array_merge($r, $typeURLs);
		}
		$array	= array();
		return $r;
	}
	
	/**
	 * Gets the theme path.
	 * 
	 * @return The theme path
	 * 
	 * Gets the path to the current theme.
	*/
	public static function getThemePath() {
		return ACCESSPATH.THEMESDIR.static::$theme.'/';
	}

	/** Get the absolute theme path.
	 *
	 * @return The theme path.
	 *
	 * Gets the absolute path to the current theme.
	 */
	public static function getAbsThemePath() {
		return pathOf(static::getThemePath());
	}
	
	/** Gets the models theme path.

		\return The models theme path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return static::getThemePath().static::$modelsPath;
	}

	/** Gets the css theme path.

		\return The css theme path.
		
		Gets the path to the css files.
	*/
	public static function getCSSPath() {
		return static::getThemePath().static::$cssPath;
	}


	/** Get the theme path.
	 *
	 * @return The theme path.
	 *
	 * Get the URL to the current theme.
	 */
	public static function getThemeURL() {
		return THEMESURL.static::$theme.'/';
	}

	/** 
	 * Gets the CSS files path.
	 * @return string The CSS path.
	 * 
	 * Gets the URL to the CSS files.
	*/
	public static function getCSSURL() {
		return static::getThemeURL().static::$cssPath;
	}

	/** 
	 * Gets the JS files path.
	 * @return string The JS path.
	 * 
	 * Gets the URL to the JS files.
	*/
	public static function getJSURL() {
		return static::getThemeURL().static::$jsPath;
	}
}