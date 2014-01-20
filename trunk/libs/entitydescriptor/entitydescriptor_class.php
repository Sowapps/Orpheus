<?php

class EntityDescriptor {

	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name) {
		$descriptorPath = ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		text('$descriptorPath: '.$descriptorPath);
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
// 		if( !$cache->get($conf) ) {
			$conf = YAML::build($descriptorPath, true);
			// Build descriptor
			$this->fields = array();
			//    Parse Config file
			foreach( $conf->fields as $field => $fieldInfos ) {
				$type	= is_array($fieldInfos) ? $fieldInfos['type'] : $fieldInfos;
				$fData	= (object) static::parseType($type);
				$TYPE	= static::getType($fData->type);
				$fData->args	= call_user_func_array($TYPE->argsParser, $fData->args);
				
				$this->fields[$field] = $fData;
			}
			$this->indexes = !empty($conf->indexes) ? $conf->indexes : array();
			
			//    Generate cache output
			
			// Save descriptor
			text('Descriptor');
			text(array(
				'fields'	=> $this->fields,
				'indexes'	=> $this->indexes,
			));
// 			$cache->set(array(
// 				'fields' => $fields,
// 				'indexes' => $conf['indexes'],
// 			));
// 		}
		
// 		return $conf;
	}
	
	public function getFieldsName() {
		return array_keys($this->fields);
	}
	
	protected function callValidator($type, $args, &$value) {
		if( isset($type->parent) ) {
			$parent = static::getType($type->parent);
			$this->callValidator($parent, $args, $value);
		}
		call_user_func($type->validator, $args, $value);
	}
	
	protected function callFormatter($type, $args, &$value) {
		if( isset($type->parent) ) {
			$parent = static::getType($type->parent);
			$this->callFormatter($parent, $args, $value);
		}
		if( $type->formatter ) {
			call_user_func($type->formatter, $args, $value);
		}
	}
	
	public function validateFieldValue($field, &$value, $required=true, $ref=null) {
		if( !isset($this->fields[$field]) ) {
			throw new InvalidFieldException('unknownField', $field, $value);
		}
		
		if( is_null($value) ) {
			if( $required ) {
				throw new InvalidFieldException('requiredField', $field, $value);
			}
			// We will format valid null value later (in formatter)
			return;
		}
		$FIELD	= $this->fields[$field];
		$TYPE	= static::getType($FIELD->type);
		// TYPE Validator - Use inheritance, mandatory in super class
		try {
			$this->callValidator($TYPE, $FIELD->args, $value);
			// Field Validator - Could be undefined
			if( !empty($FIELD->validator) ) {
				call_user_func($FIELD->validator, $FIELD->args, $value);
			}
		} catch( Exception $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $FIELD->type, null, $FIELD->args);
			//($message, $field, $value, $type=null, $domain=null, $typeArgs=array())
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$this->callFormatter($TYPE, $FIELD->args, $value);
		// Field Formatter - Could be undefined
	}
	
	public function validate(&$uInputData, $fields=null, $ref=null, &$errCount=0) {
		
	}
	
	protected static $types = array();

	public static function getType($name, &$type=null) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		$type = &static::$types[$name];
		return $type;
	}
	
	public static function registerType($name, $argsParser, $validator, $formatter=null) {
		// If previously registered, we just replace it
		static::$types[$name] = (object) array(
			'argsParser'	=> $argsParser,
			'validator'		=> $validator,
			'formatter'		=> $formatter,
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

class FE extends Exception {
	
}

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

EntityDescriptor::registerType('number', function($a1=null, $a2=null, $a3=null) {
	$args = (object) array('precision'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( !is_null($a3) ) {
		$args->precision	= $a1;
		$args->min			= $a2;
		$args->max			= $a3;
	} else if( !is_null($a2) ) {
		$args->min			= $a1;
		$args->max			= $a2;
	} else if( !is_null($a1) ) {
		$args->max			= $a1;
	}
	return $args;
}, function(&$args, $value) {
	if( $value < $args->min ) {
		throw new FE('belowMin');
	}
	if( $value > $args->max ) {
		throw new FE('aboveMax');
	}
});

EntityDescriptor::registerType('string', function($a1=null, $a2=null, $a3=null) {
	$args = array('min'=>0, 'max'=>65535);
	if( !is_null($a2) ) {
		$args['min']		= $a1;
		$args['max']		= $a2;
	} else if( !is_null($a1) ) {
		$args['max']		= $a1;
	}
	return $args;
});

