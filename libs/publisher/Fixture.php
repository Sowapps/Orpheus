<?php
/**
 * The Fixture class
 * 
 * This interface is used to register fixtures.
 */
class FixtureRepository {

	protected static $fixtures	= array();
	public static function register($class) {
		if( array_key_exists($class, static::$fixtures) ) { continue; }
		static::$fixtures[$class] = null;
	}
	public static function listAll() {
		$r = array();
		foreach( static::$fixtures as $class => &$state ) {
			if( $state == null ) {
				$state	= class_exists($class, true) && is_subclass_of($class, 'FixtureInterface');
			}
			if( $state == true ) {
				$r[] = $class;
			}
		}
		return $r;
	}
}

/**
 * The FixtureInterface interface
 * 
 * This interface is used to register fixtures.
 */
interface FixtureInterface {
	
	public static function loadFixtures();
	
}
