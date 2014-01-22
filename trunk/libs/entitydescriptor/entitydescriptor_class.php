<?php

class EntityDescriptor {

	protected $class;
	protected $name;
	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';
	
	public function __construct($name, $class=null) {
		$this->name		= $name;
		$this->class	= $class;
		$descriptorPath	= ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
		if( !$cache->get($descriptor) ) {
			$conf = YAML::build($descriptorPath, true);
			if( empty($conf->fields) ) {
				throw new Exception('Descriptor file for '.$name.' is corrupted, empty or not found');
			}
			// Build descriptor
			//    Parse Config file
			//      Fields
			$this->fields = array('id'=>(object) array('type'=>'ref', 'args'=>(object)array('decimals'=>0, 'min'=>0, 'max'=>4294967295), 'writable'=>false, 'nullable'=>false));
			foreach( $conf->fields as $field => $fieldInfos ) {
// 				text('$field : '.$field);
				$type					= is_array($fieldInfos) ? $fieldInfos['type'] : $fieldInfos;
				$parse					= (object) static::parseType($type);
				$FIELD					= (object) array(
					'name' => $field,
					'type' => $parse->type,
				);
				$TYPE					= static::getType($FIELD->type);
				$FIELD->args			= $TYPE->parseArgs($parse->args);
				$FIELD->default			= $parse->default;
// 				text($parse->flags);
				// Type's default
				$FIELD->writable		= $TYPE->isWritable();
				$FIELD->nullable		= $TYPE->isNullable();
				// Default if no type's default
				if( !isset($FIELD->writable) ) { $FIELD->writable = true; }
				if( !isset($FIELD->nullable) ) { $FIELD->nullable = false; }
// 				text(__LINE__.' => '.($FIELD->writable ? 'WRITABLE' : 'READONLY').' '.($FIELD->nullable ? 'NULLABLE' : 'NOTNULL'));
				// Field flags
				if( !isset($fieldInfos['writable']) ) {
					$FIELD->writable = !empty($fieldInfos['writable']);
				} else if( $FIELD->writable ) {
					$FIELD->writable = !in_array('readonly', $parse->flags); 
				} else {
					$FIELD->writable = in_array('writable', $parse->flags); 
				}
// 				text(__LINE__.' => '.($FIELD->writable ? 'WRITABLE' : 'READONLY').' '.($FIELD->nullable ? 'NULLABLE' : 'NOTNULL'));
				if( !isset($fieldInfos['nullable']) ) {
					$FIELD->nullable = !empty($fieldInfos['nullable']);
				} else if( $FIELD->nullable ) {
					$FIELD->nullable = !in_array('notnull', $parse->flags); 
				} else {
					$FIELD->nullable = in_array('nullable', $parse->flags); 
				}
// 				text(__LINE__.' => '.($FIELD->writable ? 'WRITABLE' : 'READONLY').' '.($FIELD->nullable ? 'NULLABLE' : 'NOTNULL'));
				$this->fields[$field]	= $FIELD;
			}

			//      Indexes
			$this->indexes = array();
			if( !empty($conf->indexes) ) {
				foreach( $conf->indexes as $index ) {
					$this->indexes[] = (object) static::parseType($type);
				}
			}
			//    Save cache output
			$cache->set(get_object_vars($this));
			return;
		}
		$descriptor		= (object) $descriptor;
		$this->fields	= $descriptor->fields;
		$this->indexes	= $descriptor->indexes;
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
	
	public function validateFieldValue($field, &$value, $inputData=array(), $ref=null) {
		if( !isset($this->fields[$field]) ) {
			throw new InvalidFieldException('unknownField', $field, $value);
		}
		$FIELD	= $this->fields[$field];
		if( !$FIELD->writable ) {
			throw new InvalidFieldException('readOnlyField', $field, $value);
		}
		
		if( is_null($value) ) {
			if( !$FIELD->nullable ) {
				throw new InvalidFieldException('requiredField', $field, $value);
			}
			// We will format valid null value later (in formatter)
			return;
		}
		$TYPE	= static::getType($FIELD->type);
		// TYPE Validator - Use inheritance, mandatory in super class
		try {
			$TYPE->validate($FIELD, $value, $inputData);
			// Field Validator - Could be undefined
			if( !empty($FIELD->validator) ) {
				call_user_func_array($FIELD->validator, array($FIELD, &$value, $inputData));
			}
		} catch( FE $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $FIELD->type, null, $FIELD->args);
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$TYPE->format($FIELD, $value);
		// Field Formatter - Could be undefined
	}
	
	public function validate(&$uInputData, $fields=null, $ref=null, &$errCount=0) {
// 		$class = $this->class;
		foreach( $this->fields as $field => $fData ) {
			try {
				if( !is_null($fields) && !in_array($field, $fields) ) { continue; }
				$this->validateFieldValue($field, $uInputData[$field], $uInputData, $ref);

			} catch( UserException $e ) {
				$errCount++;
				if( isset($this->class) ) {
					$c = $this->class;
					$c::reportException($e);
					return;
				}
				throw $e;
			}
		}
		return $uInputData;
	}
	
	protected static $types = array();

	public static function getType($name, &$type=null) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		$type = &static::$types[$name];
		return $type;
	}
	
	public static function registerType($name, $parent, $argsParser=null, $validator=null, $formatter=null, $writable=null, $nullable=null) {
		// If previously registered, we just replace it
		static::$types[$name] = new TypeDescriptor($name, isset($parent) ? static::getType($parent) : null, $argsParser, $validator, $formatter, $writable, $nullable);
// 		static::$types[$name] = (object) array(
// 			'argsParser'	=> $argsParser,
// 			'validator'		=> $validator,
// 			'formatter'		=> $formatter,
// 		);
	}

	public static function parseType($string) {
		$result = array('type'=>null, 'args'=>array());
		if( !preg_match('#([^\(]+)(?:\(([^\)]+)\))?(?:\[([^\]]+)\])?#', $string, $matches) ) {
			throw new Exception('failToParseType');
		}
		$result['type']		= trim($matches[1]);
		$result['args']		= !empty($matches[2]) ? preg_split('#\s*,\s*#', $matches[2]) : array();
		$result['flags']	= !empty($matches[3]) ? preg_split('#\s#', $matches[3], -1, PREG_SPLIT_NO_EMPTY) : array();
		return $result;
	}
}

// Short Field Exception
class FE extends Exception { }

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

// Primary Types
EntityDescriptor::registerType('number', null, function($fArgs) {
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
	return $args;
	
}, function($FIELD, &$value) {
	if( !is_numeric($value) ) {
		throw new FE('notNumeric');
	}
	if( $value < $FIELD->args->min ) {
		throw new FE('belowMinValue');
	}
	if( $value > $FIELD->args->max ) {
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
	
}, function($FIELD, $value) {
	$len = strlen($value);
	if( $len < $FIELD->args->min ) {
		throw new FE('belowMinLength');
	}
	if( $len > $FIELD->args->max ) {
		throw new FE('aboveMaxLength');
	}
});

EntityDescriptor::registerType('date', null, null, function($args, $value) {
	// FR Only for now
	if( !is_date($value, false, $time) ) {
		throw new FE('notDate');
	}
	// Format to timestamp
	$value = $time;
	
}, function($FIELD, $value) {
	// SQL Format
	$value = strftime('%Y-%m-%d', $value);
});

EntityDescriptor::registerType('datetime', null, null, function($args, $value) {
	// FR Only for now
	if( !is_date($value, true, $time) ) {
		throw new FE('notDatetime');
	}
	// Format to timestamp
	$value = $time;
	
}, function($FIELD, $value) {
	// SQL Format
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
	
}, null, function($FIELD, $value) {
	$value = (int) $value;
});

EntityDescriptor::registerType('bool', 'integer', function($fArgs) {
	return (object) array('decimals'=>0, 'min'=>0, 'max'=>1);
	
}, null, function($FIELD, $value) {
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
}, null, null, false);

EntityDescriptor::registerType('email', 'string', function($fArgs) {
	return (object) array('min'=>5, 'max'=>100);
	
}, function($FIELD, $value) {
	if( !is_email($value) ) {
		throw new FE('notEmail');
	}
});

EntityDescriptor::registerType('password', 'string', function($fArgs) {
	return (object) array('min'=>5, 'max'=>128);

}, function($FIELD, $value, $inputData) {
	if( empty($inputData[$FIELD->name.'_conf']) || $value!=$inputData[$FIELD->name.'_conf'] ) {
		throw new FE('invalidConfirmation');
	}
	
}, function($FIELD, $value) {
	$value = hashString($value);
});

EntityDescriptor::registerType('phone', 'string', function($fArgs) {
	return (object) array('min'=>10, 'max'=>20);
	
}, function($FIELD, $value) {
	// FR Only for now
	if( !is_phone_number($value) ) {
		throw new FE('notPhoneNumber');
	}
	
}, function($FIELD, $value) {
	// FR Only for now
	$value = standardizePhoneNumber_FR($value, '.', 2);
	
});

EntityDescriptor::registerType('url', 'string', function($fArgs) {
	return (object) array('min'=>10, 'max'=>200);
	
}, function($FIELD, $value) {
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
	
}, function($FIELD, $value) {
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
	
}, function($FIELD, $value) {
	if( !in_array($value, call_user_func($FIELD->args->source)) ) {
		throw new FE('notEnumValue');
	}	
});

