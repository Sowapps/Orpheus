<?php

class EntityDescriptor {

	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name) {
		$descriptorPath = ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
// 		if( !$cache->get($conf) ) {
			$conf = YAML::build($descriptorPath, true);
			// Build descriptor
			$fields = array();
			//    Parse Config file
			foreach( $conf['fields'] as $field => $fieldInfos ) {
				$type	= is_array($fieldInfos) ? $fieldInfos['type'] : $fieldInfos;
				$fData	= static::parseType($type);
				$TYPE	= static::getType($fData['type']);
				$fData['args']	= call_user_func_array($TYPE['argsParser'], $fData['args']);
				
				$fields[$field] = $fData;
			}
			
			//    Generate cache output
			
			// Save descriptor
			debug('Descriptor', array(
				'fields' => $fields,
				'indexes' => $conf['indexes'],
			));
// 			$cache->set(array(
// 				'fields' => $fields,
// 				'indexes' => $conf['indexes'],
// 			));
// 		}
		
// 		return $conf;
	}
	
	public function validateFieldValue($field, &$inputValue, $conf=null, $ref=null) {
		
	}
	
	public function validate(&$uInputData, $fields=null, $ref=null, &$errCount=0) {
		
	}
	
	protected static $types = array();
	
	public static function getType($name) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		return static::$types[$name];
	}
	public static function registerType($name, $argsParser) {
		
		static::$types[$name] = array(
			'argsParser'	=> $argsParser
		);
	}

	public static function parseType($string) {
		$result = array('type'=>null, 'args'=>array());
		if( !preg_match('#([^\(]+)(?:\(([^\)]+)\))?#', $string, $matches) ) {
			throw new Exception('failToParseType');
		}
		$result['type'] = trim($matches[1]);
		$result['args'] = !empty($matches[2]) ? preg_split('#\s*,\s*#', $matches[2]) : array();
		return $result;
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
