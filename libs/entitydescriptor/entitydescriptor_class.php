<?php

class EntityDescriptor {

	protected $name;
	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name) {
		$this->name		= $name;
		$descriptorPath	= ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		text('$descriptorPath: '.$descriptorPath);
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
// 		if( !$cache->get($conf) ) {
			$conf = YAML::build($descriptorPath, true);
			// Build descriptor
			//    Parse Config file
			//      Fields
			$this->fields = array('id'=>(object) array('type'=>'ref', 'args'=>array('decimals'=>0, 'min'=>0, 'max'=>4294967295), 'writable'=>false, 'nullable'=>false));
			foreach( $conf->fields as $field => $fieldInfos ) {
				$type					= is_array($fieldInfos) ? $fieldInfos['type'] : $fieldInfos;
				$fData					= (object) static::parseType($type);
				$TYPE					= static::getType($fData->type);
				$fData->args			= $TYPE->parseArgs($fData->args);
				$fData->writable		= isset($fieldInfos['writable']) ? !empty($fieldInfos['writable']) : true;
				$fData->nullable		= isset($fieldInfos['nullable']) ? !empty($fieldInfos['nullable']) : true;
				$this->fields[$field]	= $fData;
			}

			//      Indexes
			$this->indexes = array();
			if( !empty($conf->indexes) ) {
				foreach( $conf->indexes as $index ) {
					$this->indexes[] = (object) static::parseType($type);
				}
			}
			
			//    Generate cache output
			
			// Save descriptor
// 			text('Descriptor');
// 			text(array(
// 				'fields'	=> $this->fields,
// 				'indexes'	=> $this->indexes,
// 			));
// 			$cache->set(array(
// 				'fields' => $fields,
// 				'indexes' => $conf['indexes'],
// 			));
// 		}
		
// 		return $conf;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getFields() {
		return $this->fields;
	}
	
	public function getIndexes() {
		return $this->indexes;
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
		if( $field=='id' ) {
			throw new InvalidFieldException('readOnlyField', $field, $value);
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
	
	public static function registerType($name, $parent, $argsParser=null, $validator=null, $formatter=null) {
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
// 	text('Parse Args');
// 	text($fArgs);
	$args = (object) array('decimals'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( isset($fArgs[2]) ) {
		$args->decimals	= $fArgs[0];
		$args->min			= $fArgs[1];
		$args->max			= $fArgs[2];
	} else if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
// 	text('Args');
// 	text($args);
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
	$args = (object) array('min'=>0, 'max'=>65535);
	if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
	return $args;
}, function($args, $value) {
	$len = strlen($value);
	if( $len < $args->min ) {
		throw new FE('belowMinLength');
	}
	if( $len > $args->max ) {
		throw new FE('aboveMaxLength');
	}
});

EntityDescriptor::registerType('date', null, null
/*function($fArgs) {
	$args = (object) array('country'=>'FR');
	if( isset($fArgs[0]) ) {
		$args->country		= strtoupper($fArgs[0]);
	}
	if( $args->country != 'FR' ) {
		throw new Exception('invalidCountry_'.$args->country);
	}
	return $args;
	
}*/
, function($args, $value) {
	// FR Only for now
// 	$time = null;
// 	text("is_date($value, false, $time, {$args->country})");
//, $args->country
	if( !is_date($value, false, $time) ) {
		throw new FE('notDate');
	}
	// Format to timestamp
	$value = $time;
	
}, function($args, $value) {
// 	text("Formatter");
// 	text(var_dump($value));
// 	$value = strtr($value, '/', '-');
	$value = strftime('%Y-%m-%d', $value);
});

EntityDescriptor::registerType('datetime', null, null
/*function($fArgs) {
	return (object) array();
// 	$args = (object) array('country'=>'FR');
// 	if( isset($fArgs[0]) ) {
// 		$args->country		= strtoupper($fArgs[0]);
// 	}
// 	if( $args->country != 'FR' ) {
// 		throw new Exception('invalidCountry_'.$args->country);
// 	}
// 	return $args;
	
}*/
, function($args, $value) {
	//, $args->country
	// FR Only for now
	if( !is_date($value, true, $time) ) {
		throw new FE('notDatetime');
	}
	// Format to timestamp
	$value = $time;
	
}, function($args, $value) {
	$value = strftime('%Y-%m-%d %H:%M:%S', $value);
});


// Derived types
EntityDescriptor::registerType('integer', 'number', function($fArgs) {
	$args = (object) array('decimals'=>0, 'min'=>-2147483648, 'max'=>2147483647);
	if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->max			= $fArgs[0];
	}
	return $args;
}, null, function($args, $value) {
	$value = (int) $value;
});

EntityDescriptor::registerType('float', 'integer', function($fArgs) {
	$args = (object) array('decimals'=>2, 'min'=>-2147483648, 'max'=>2147483647);
	if( isset($fArgs[2]) ) {
		$args->decimals		= $fArgs[0];
		$args->min			= $fArgs[1];
		$args->max			= $fArgs[2];
	} else if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->decimals		= $fArgs[0];
	}
	return $args;
});

EntityDescriptor::registerType('double', 'integer', function($fArgs) {
	$args = (object) array('decimals'=>8, 'min'=>-2147483648, 'max'=>2147483647);	
	if( isset($fArgs[2]) ) {
		$args->decimals		= $fArgs[0];
		$args->min			= $fArgs[1];
		$args->max			= $fArgs[2];
	} else if( isset($fArgs[1]) ) {
		$args->min			= $fArgs[0];
		$args->max			= $fArgs[1];
	} else if( isset($fArgs[0]) ) {
		$args->decimals		= $fArgs[0];
	}
	return $args;
});

EntityDescriptor::registerType('ref', 'integer', function($fArgs) {
	return (object) array('decimals'=>0, 'min'=>0, 'max'=>4294967295);	
});

EntityDescriptor::registerType('email', 'string', function($fArgs) {
	return (object) array('min'=>5, 'max'=>100);
	
}, function($args, $value) {
	if( !is_email($value) ) {
		throw new FE('notEmail');
	}
});

EntityDescriptor::registerType('password', 'string', function($fArgs) {
	return (object) array('min'=>5, 'max'=>128);

}, null, function($args, $value) {
	$value = hashString($value);
});

EntityDescriptor::registerType('phone', 'string', function($fArgs) {
	return (object) array('min'=>10, 'max'=>20);
// 	$args = (object) array('min'=>10, 'max'=>20, 'country'=>'FR');
	//, 'country'=>'FR'
// 	if( isset($fArgs[0]) ) {
// 		$args->country		= strtoupper($fArgs[0]);
// 	}
// 	if( $args->country != 'FR' ) {
// 		throw new Exception('invalidCountry_'.$args->country);
// 	}
// 	return $args;
	
}, function($args, $value) {
	// FR Only for now
// 	if( !is_phone_number($value, $args->country) ) {
	if( !is_phone_number($value) ) {
		throw new FE('notPhoneNumber');
	}
	
}, function($args, $value) {
	// FR Only for now
	$value = standardizePhoneNumber_FR($value, '.', 2);
	
});

EntityDescriptor::registerType('url', 'string', function($fArgs) {
	return (object) array('min'=>10, 'max'=>200);
	
}, function($args, $value) {
	if( !is_url($value) ) {
		throw new FE('notURL');
	}	
});

EntityDescriptor::registerType('ip', 'string', function($fArgs) {
	$args = (object) array('min'=>7, 'max'=>40, 'version'=>null);
	if( isset($fArgs[0]) ) {
		$args->version		= $fArgs[0];
	}
	return $args;
	
}, function($args, $value) {
	if( !is_ip($value) ) {
		throw new FE('notIPAddress');
	}	
});

EntityDescriptor::registerType('enum', 'string', function($fArgs) {
	$args = (object) array('min'=>1, 'max'=>20, 'source'=>null);
	if( isset($fArgs[0]) ) {
		$args->source		= $fArgs[0];
	}
	return $args;
	
}, function($args, $value) {
	if( !in_array($value, call_user_func($args->source)) ) {
		throw new FE('notEnumValue');
	}	
});

