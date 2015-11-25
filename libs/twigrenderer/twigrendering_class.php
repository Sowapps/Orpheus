<?php
/** The Twig rendering class

	A class to render templates with the Twig engine.
*/
class TwigRendering extends Rendering {
	
	protected static $SHOWMODEL	= 'layout';
	
	/**
	 * @var string
	 */
	public static $theme		= 'default';

	public static $cssPath		= 'css/';
	public static $jsPath		= 'js/';
// 	public static $modelsPath	= 'layouts/';
	public static $modelsPath	= '';
	public static $cachePath	= 'cache/';
	
	public static $cssURLs		= array();// CSS files
	public static $jsURLs		= array();// Javascript files
	public static $metaprop		= array();// Meta-properties
	
	protected static $twigenv;
	
	/** Renders the model.

	 * @copydoc Rendering::render()
	 */
	public function render($model=null, $env=array()) {
		if( $model === NULL ) {
			throw new Exception("Invalid Rendering Model");
		}
		$env['RENDERER']	= $this;
		return static::$twigenv->render($model.'.twig', $env);
	}
	
	/** Initializes the Twig rendering

	 * Initializes the Twig rendering, cache and models' path are defined.
	 */
	public static function init() {
		if( isset(static::$twigenv) ) {
			return;// Already done ?
		}
		static::$twigenv	= new Twig_Environment(new Twig_Loader_Filesystem(static::getModelsPath()), array(
			'cache' => false,
// 			'cache' => static::getCachePath(),
		));
		static::setTwigEnvironment(static::$twigenv);
	}
	
	/**
	 * Set the default theme used to render layouts
	 * @param string $theme
	 */
	public static function setDefaultTheme($theme) {
		static::$theme	= $theme;
	}
	
	/** Sets the Twig Environment

	 * @param $twigEnv The new Twig_Environment object.
	 */
	public static function setTwigEnvironment(Twig_Environment $twigEnv) {
		static::$twigenv	= $twigEnv;
	}
	
	/** Gets the Twig Environment

	 * @return The Twig_Environment object.
	 */
	public static function getTwigEnvironment() {
		return static::$twigenv;
	}
	
	/** Gets the theme path.

		\return The theme path.
		
		Gets the path to the current theme.
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
	
	/** Gets the models path.

	 * @return The models path.
	 * 
	 * Gets the path to the models.
	*/
	public static function getModelsPath() {
		return static::getThemePath().static::$modelsPath;
// 		return pathOf(THEMESDIR.static::$theme.'/'.static::$modelsPath);
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

	/** Gets the Cache path.

		\return The Cache path.
		
		Gets the path to the Cache files.
	*/
	public static function getCachePath() {
		return dirname(__FILE__).'/'.static::$cachePath;
	}
}
