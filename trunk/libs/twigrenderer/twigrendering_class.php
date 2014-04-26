<?php
//! The Twig rendering class
/*!
	A class to render templates with the Twig engine.
*/
class TwigRendering extends Rendering {
	
	protected static $SHOWMODEL	= 'layout';
	public static $theme		= 'default';
	
	public static $cssPath		= 'css/';
	public static $modelsPath	= '';
	public static $cachePath	= 'cache/';
	
	protected static $twigenv;
	
	//! Renders the model.
	/*!
	 * \copydoc Rendering::render()
	 */
	public function render($model=null, $env=array()) {
		if( $model === NULL ) {
			throw new Exception("Invalid Rendering Model");
		}
		$env['RENDERER']	= $this;
		return static::$twigenv->render($model.'.twig', $env);
	}
	
	//! Initializes the Twig rendering
	/*!
	 * Initializes the Twig rendering, cache and models' path are defined.
	 */
	public static function init() {
		if( isset(static::$twigenv) ) {
			return;// Already done ?
		}
		static::$twigenv	= new Twig_Environment(new Twig_Loader_Filesystem(static::getModelsPath()), array(
			'cache' => static::getCachePath(),
		));
		static::setTwigEnvironment(static::$twigenv);
	}
	
	//! Sets the Twig Environment
	/*!
	 * \param $twigEnv The new Twig_Environment object.
	 */
	public static function setTwigEnvironment(Twig_Environment $twigEnv) {
		static::$twigenv	= $twigEnv;
	}
	
	//! Gets the Twig Environment
	/*!
	 * \return The Twig_Environment object.
	 */
	public static function getTwigEnvironment() {
		return static::$twigenv;
	}
	
	//! Gets the models path.
	/*!
	 * \return The models path.
	 * 
	 * Gets the path to the models.
	*/
	public static function getModelsPath() {
		return pathOf(THEMESDIR.static::$theme.'/'.static::$modelsPath);
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the path to the CSS files.
	*/
	public static function getCSSURL() {
		return THEMESURL.static::$theme.'/'.static::$cssPath;
	}

	//! Gets the Cache path.
	/*!
		\return The Cache path.
		
		Gets the path to the Cache files.
	*/
	public static function getCachePath() {
		return dirname(__FILE__).'/'.static::$cachePath;
	}
}