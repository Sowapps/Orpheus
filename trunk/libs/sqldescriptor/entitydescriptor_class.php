<?php

class EntityDescriptor {

	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name) {
		$descriptorPath = ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
		if( !$cache->get($conf) ) {
			$conf = YAML::build($descriptorPath, true);
			// Build descriptor
			
			//    Parse Config file
			
			
			//    Generate cache output
			
			// Save descriptor
			$cache->set($conf = empty($conf) ? false : $conf->all);
		}
		
// 		return $conf;
	}
	
	protected static $types = array();
	public static function registerType($name, $argsParser) {
		
		
		static::$types[$name] = array(
			'argsParser'	=> $argsParser
		);
	}
	
	public static function parseType($string) {
		
	}
}

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

EntityDescriptor::registerType('number', function($a1=null, $a2=null, $a3=null) {
	$args = array('precision'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( !is_null($a3) ) {
		$args['precision']	= $a1;
		$args['min']		= $a2;
		$args['max']		= $a3;
	} else if( !is_null($a2) ) {
		$args['min']		= $a1;
		$args['max']		= $a2;
	} else if( !is_null($a1) ) {
		$args['max']		= $a1;
	}
	return $args;
});
