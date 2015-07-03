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
		extract($env, EXTR_SKIP);
		$prevLayouts	= count(static::$layoutStack);
		include static::getModelPath($model);
		$currentLayouts	= count(static::$layoutStack);
		while( $currentLayouts > $prevLayouts && static::endCurrentLayout() ) {
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
	
	public static function addThemeCSSFile($filename) {
		static::addCSSURL(static::getCSSURL().$filename);
	}
	public static function addCSSFile($filename) {
		static::addThemeCSSFile($filename);
	}
	public static function addCSSURL($url) {
		static::$cssURLs[]	= $url;
	}
	
	public static function addThemeJSFile($filename) {
		static::addJSURL(static::getJSURL().$filename);
	}
	public static function addJSFile($filename) {
		static::addJSURL(JSURL.$filename);
	}
	public static function addJSURL($url) {
		static::$jsURLs[]	= $url;
	}
	
	public static function addMetaProperty($property, $content) {
		static::$metaprop[$property] = $content;
	}
	
	/** Gets the theme path.

		\return The theme path.
		
		Gets the path to the current theme.
	*/
	public static function getThemePath() {
		return WEBPATH.THEMESDIR.static::$theme.'/';
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