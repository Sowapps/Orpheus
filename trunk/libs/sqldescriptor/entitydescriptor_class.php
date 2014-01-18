<?php

class EntityDescriptor {

	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name) {
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath(ENTITY_DESCRIPTOR_CONFIG_PATH.$name)));
		if( !$cache->get($conf) ) {
			$conf = YAML::build(ENTITY_DESCRIPTOR_CONFIG_PATH.$name, true);
			// Build descriptor
			
			//	Parse Config file
			
			//	Generate cache output
			
			// Save descriptor
			$cache->set($conf = empty($conf) ? false : $conf->all);
		}
		
// 		return $conf;
	}
}
defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');