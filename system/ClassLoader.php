<?php

namespace Orpheus\Core;

/**
 *
 * @author Florent HAZARD <florent@orpheus-framework.com>
 */
class ClassLoader {
	
	protected $classes;
	
	public function __construct() {
		$this->classes	= array();
	}
	
	public function __toString() {
		return 'orpheus-ClassLoader';
	}
	

	/**
	 * Unregister object from the SPL
	 */
	public function unregister() {
		spl_autoload_unregister(array($this, 'loadClass'));
	}
	
	/**
	 * Register object to the SPL
	 * 
	 * @param boolean
	 */
	public function register($prepend=false) {
		spl_autoload_register(array($this, 'loadClass'), true, $prepend);
	}
	
	/**
	 * Load class file
	 * 
	 * @param string $className
	 * @throws \Exception
	 */
	public function loadClass($className) {
		try {
// 			global $AUTOLOADS;
			// PHP's class' names are not case sensitive.
			$bFile = strtolower($className);

			// If the class file path is known in the our array
			if( !empty($this->classes[$bFile]) ) {
				$path	= null;
				$path	= $this->classes[$bFile];
				require_once $path;
				if( !class_exists($className, false) && !interface_exists($className, false) ) {
					throw new \Exception('Wrong use of Autoloads, the class "'.$className.'" should be declared in the given file "'.$path.'". Please use Class Loader correctly.');
				}
			}
		} catch( Exception $e ) {
			log_error($e, 'loading_class_'.$className);
		}
	}
	
	/**
	 * Set the file path to the class
	 * 
	 * @param string $className
	 * @param string $className
	 * @return boolean
	 * @throws \Exception
	 */
	public function setClass($className, $classPath) {
// 		global $AUTOLOADS;
		$className = strtolower($className);
// 		if( !empty($AUTOLOADS[$className]) ) {
// 			return false;
// 		}
		// Auto
// 		if( empty($classPath) ) {
// 			$bt	 	= debug_backtrace();
// 			$path	= dirname($bt[0]['file']);
// 		}
		if(
// 			isset($path) ||
			// Pure object naming with only lib name and exact class name
			existsPathOf(LIBSDIR.$classPath.'/'.$className.'.php', $path) ||
			// Pure object naming
			existsPathOf(LIBSDIR.$classPath.'.php', $path) ||
			// Old Orpheus naming
			existsPathOf(LIBSDIR.$classPath.'_class.php', $path) ||
			// Full path
			existsPathOf(LIBSDIR.$classPath, $path)
		) {
			$this->classes[$className] = $path;
			
		} else {
			throw new \Exception("ClassLoader : File \"{$classPath}\" of class \"{$className}\" not found.");
		}
		return true;
		
	}
	
	
	/* *** Singleton part *** */
	
	/**
	 * The active autoloader
	 * 
	 * @var ClassLoader
	 */
	protected static $loader;
	
	/**
	 * Get the active autoloader
	 * 
	 * @return ClassLoader
	 */
	public static function get() {
		if( !static::$loader ) {
			static::set(new static());
		}
		return static::$loader;
	}

	/**
	 * Set the active autoloader
	 * 
	 * @param ClassLoader
	 */
	public static function set(ClassLoader $loader) {
		// Unregister the previous one
		if( static::$loader ) {
			static::$loader->unregister();
		}
		// Set the new class loader
		static::$loader	= $loader;
		// Register the new one
		if( static::$loader ) {
			static::$loader->register(true);
		}
	}

	/**
	 * Check if active loader is valid
	 * 
	 * @return boolean
	 */
	public static function isValid() {
		return !!static::$loader;
	}
	
}

