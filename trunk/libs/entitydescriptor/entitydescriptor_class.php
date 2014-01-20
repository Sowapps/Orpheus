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
				$fData->args	= $TYPE->parseArgs($fData->args);
				
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
	/*
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
	*/
	
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
			$TYPE->validate($FIELD->args, $value);
			// Field Validator - Could be undefined
			if( !empty($FIELD->validator) ) {
				call_user_func($FIELD->validator, $FIELD->args, $value);
			}
		} catch( FE $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $FIELD->type, null, $FIELD->args);
			//($message, $field, $value, $type=null, $domain=null, $typeArgs=array())
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$TYPE->format($FIELD->args, $value);
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
	
	public static function registerType($name, $parent, $argsParser, $validator=null, $formatter=null) {
		// If previously registered, we just replace it
		static::$types[$name] = new TypeDescriptor($name, isset($parent) ? static::getType($parent) : null, $argsParser, $validator, $formatter);
// 		static::$types[$name] = (object) array(
// 			'argsParser'	=> $argsParser,
// 			'validator'		=> $validator,
// 			'formatter'		=> $formatter,
// 		);
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

// Short Field Exception
class FE extends Exception { }

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

// Primary Types
EntityDescriptor::registerType('number', null, function($fArgs) {
	$args = (object) array('precision'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( isset($fArgs[2]) ) {
		$args->precision	= $fArgs[0];
		$args->min			= $fArgs[1];
		$args->max			= $fArgs[2];
	} else if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
	text('Args');
	text($args);
	return $args;
}, function($args, &$value) {
	if( !is_numeric($value) ) {
		throw new FE('notNumeric');
	}
	if( $value < $args->min ) {
		throw new FE('belowMinValue');
	}
	if( $value > $args->max ) {
		throw new FE('aboveMaxValue');
	}
});

EntityDescriptor::registerType('string', null, function($fArgs) {
	$args = array('min'=>0, 'max'=>65535);
	if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
	return $args;
}, function($args, &$value) {
	$len = strlen($value);
	if( $len < $args->min ) {
		throw new FE('belowMinLength');
	}
	if( $len > $args->max ) {
		throw new FE('aboveMaxLength');
	}
});


// Derived types
EntityDescriptor::registerType('integer', 'number', function($fArgs) {
	$args = (object) array('precision'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
	return $args;
}, null, function($args, &$value) {
	return (int) $value;
});

