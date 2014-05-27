<?php

class EntityDescriptor {

	protected $class;
	protected $name;
    /**
     * @var $fields Field[]
     */
	protected $fields	= array();
	protected $indexes	= array();
	
	const DESCRIPTORCLASS	= 'EntityDescriptor';

	public static function load($name, $class=null) {
		$descriptorPath	= ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache	= new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
		if( $cache->get($descriptor) ) {
			return $descriptor;
		}
		$conf	= YAML::build($descriptorPath, true);
		if( empty($conf->fields) ) {
			throw new Exception('Descriptor file for '.$name.' is corrupted, empty or not found');
		}
		// Build descriptor
		//    Parse Config file
		//      Fields
		$fields	= array();
		if( !empty($conf->parent) ) {
			if( !is_array($conf->parent) ) {
				$conf->parent = array($conf->parent);
			}
			foreach( $conf->parent as $p ) {
				$p = static::load($p);
				if( !empty($p) ) {
					$fields = array_merge($fields, $p->getFields());
				}
			}
		}
		$fields['id']	= (object) array('name'=>'id', 'type'=>'ref', 'args'=>(object)array('decimals'=>0, 'min'=>0, 'max'=>4294967295), 'writable'=>false, 'nullable'=>false);
		foreach( $conf->fields as $field => $fieldInfos ) {
			$fields[$field]	= FieldDescriptor::parseType($field, $fieldInfos);
		}

		//      Indexes
		$indexes	= array();
		if( !empty($conf->indexes) ) {
			foreach( $conf->indexes as $index ) {
				$iType		= static::parseType($index);
				$indexes[]	= (object) array('name'=>$iType->default, 'type'=>strtoupper($iType->type), 'fields'=>$iType->args);
			}
		}
		//    Save cache output
		$descriptor	= new EntityDescriptor($name, $fields, $indexes, $class);
		$cache->set($descriptor);
		return $descriptor;
	}
	
	protected function __construct($name, $fields, $indexes, $class=null) {
		$this->name		= $name;
		$this->class	= $class;
		$this->fields	= $fields;
		$this->indexes	= $indexes;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getField($field) {
		return isset($this->fields[$field]) ? $this->fields[$field] : null;
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
	
	public function validateFieldValue($field, &$value, $inputData=array(), $ref=null) {
// 		text("validateFieldValue($field, $value)");
		if( !isset($this->fields[$field]) ) {
			throw new InvalidFieldException('unknownField', $field, $value, null, $this->name);
		}
		/* @var $Field Field */
		$Field	= $this->fields[$field];
		if( !$Field->writable ) {
			throw new InvalidFieldException('readOnlyField', $field, $value, null, $this->name);
		}
		$TYPE	= $Field->getType();
// 		$TYPE	= static::getType($Field->type);
		
		if( $value === NULL || ($value==='' && $TYPE->emptyIsNull($Field)) ) {
			$value	= null;
			// Look for default value
			if( isset($Field->default) ) {
				$value	= $Field->getDefault();
// 				$value	= $Field->default;
// 				if( is_object($value) ) {
// 					$value = call_user_func_array($value->type, (array) $value->args);
// 				}
			} else
			// Reject null value 
			if( !$Field->nullable ) {
				throw new InvalidFieldException('requiredField', $field, $value, null, $this->name);
			}
			// We will format valid null value later (in formatter)
			return;
		}
		// TYPE Validator - Use inheritance, mandatory in super class
		try {
			$TYPE->validate($Field, $value, $inputData, $ref);
			// Field Validator - Could be undefined
			if( !empty($Field->validator) ) {
				call_user_func_array($Field->validator, array($Field, &$value, $inputData, &$ref));
			}
		} catch( FE $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $Field->type, $this->name, $Field->args);
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$TYPE->format($Field, $value);
		// Field Formatter - Could be undefined
	}
	
	public function validate(array &$uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data	= array();
// 		$class = $this->class;
		foreach( $this->fields as $field => &$fData ) {
			try {
				if( $fields!==NULL && !in_array($field, $fields) ) {
					unset($uInputData[$field]);
					continue;
				}
				if( !$fData->writable ) { continue; }
				if( !isset($uInputData[$field]) ) {
					$uInputData[$field] = null;
				}
				$this->validateFieldValue($field, $uInputData[$field], $uInputData, $ref);
				// PHP does not make difference between 0 and NULL, so every non-null value is different from null.
// 				if( isset($ref) ) {
// 					debug("Ref -> $field => ", $ref->getValue($field));
// 					debug("New value", $uInputData[$field]);
// 				}
				if( !isset($ref) || ($ref->getValue($field)===NULL XOR $uInputData[$field]===NULL) || $uInputData[$field]!=$ref->getValue($field) ) {
					$data[$field]	= $uInputData[$field];
				}

			} catch( UserException $e ) {
				$errCount++;
				if( isset($this->class) ) {
					$c	= $this->class;
					$c::reportException($e);
				} else {
					reportError($e);
// 					throw $e;
				}
			}
		}
		return $data;
	}

	protected static $types = array();
	
	public static function registerType(TypeDescriptor $type) {
		static::$types[$type->getName()] = $type;
	}
	
	/**
	 * @param string $name
	 * @param string $type
	 * @throws Exception
	 * @return TypeDescriptor
	 */
	public static function getType($name, &$type=null) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		$type	= &static::$types[$name];
		return $type;
	}

	public static function parseType($string) {
		$result = array('type'=>null, 'args'=>array(), 'default'=>null, 'flags'=>array());
		if( !preg_match('#([^\(\[=]+)(?:\(([^\)]*)\))?(?:\[([^\]]*)\])?(?:=([^\[]*))?#', $string, $matches) ) {
			throw new Exception('failToParseType');
		}
		$result['type']			= trim($matches[1]);
		$result['args']			= !empty($matches[2]) ? preg_split('#\s*,\s*#', $matches[2]) : array();
		$result['flags']		= !empty($matches[3]) ? preg_split('#\s#', $matches[3], -1, PREG_SPLIT_NO_EMPTY) : array();
		if( isset($matches[4]) ) {
			$result['default']	= $matches[4];
			if( $result['default']==='true' ) {
				$result['default'] = true;
			} else
			if( $result['default']==='false' ) {
				$result['default'] = false;
			} else {
				$len = strlen($result['default']);
				if( $len && $result['default'][$len-1]==')' ) {
					$result['default'] = static::parseType($result['default']);
				}
			}
		}
		return (object) $result;
	}
}

// Short Field Exception
class FE extends Exception { }

defifn('ENTITY_DESCRIPTOR_CONFIG_PATH', 'entities/');

// Primary Types
class TypeNumber extends TypeDescriptor {
	protected $name = 'number';
	
	public function parseArgs($fArgs) {
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
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		$value	= str_replace(',', '.', $value);
		if( !is_numeric($value) ) {
			throw new FE('notNumeric');
		}
		if( $value < $Field->args->min ) {
			throw new FE('belowMinValue');
		}
		if( $value > $Field->args->max ) {
			throw new FE('aboveMaxValue');
		}
	}
	
	public function getHTMLInputAttr($Field) {
		$min	= $Field->arg('min');
		$max	= $Field->arg('max');
		return array('maxlength'=>max(strlen($min), strlen($max)), 'min'=>$min, 'max'=>$max, 'type'=>'number');
	}
	
	public function htmlInputAttr($args) {
		return ' maxlength="'.strlen($args->max).'"';
	}
}
EntityDescriptor::registerType(new TypeNumber());

class TypeString extends TypeDescriptor {
	protected $name = 'string';
	
	public function parseArgs($fArgs) {
		$args = (object) array('min'=>0, 'max'=>65535);
		if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		$len = strlen($value);
		if( $len < $Field->args->min ) {
			throw new FE('belowMinLength');
		}
		if( $len > $Field->args->max ) {
			throw new FE('aboveMaxLength');
		}
	}
	
	public function getHTMLAttr($Field) {
		$min	= $Field->arg('min');
		$max	= $Field->arg('max');
		return array('maxlength'=>$Field->arg('max'), 'type'=>'text');
	}
	
	public function htmlInputAttr($args) {
		return ' maxlength="'.$args->max.'"';
	}
	
	public function emptyIsNull($field) {
		return $field->args->min > 0;
	}
}
EntityDescriptor::registerType(new TypeString());

class TypeDate extends TypeDescriptor {
	protected $name = 'date';
	
	public function validate($Field, &$value, $inputData, &$ref) {
		// FR Only for now - Should use user language
		if( !is_date($value, false, $time) && !is_date($value, false, $time, 'SQL') ) {
			throw new FE('notDate');
		}
		// Format to timestamp
		$value = $time;
	}
	
	public function format($Field, &$value) {
		$value = sqlDate($value);
	}
}
EntityDescriptor::registerType(new TypeDate());

class TypeDatetime extends TypeDescriptor {
	protected $name = 'datetime';
	
	public function validate($Field, &$value, $inputData, &$ref) {
		// FR Only for now - Should use user language
		if( !is_date($value, true, $time) && !is_date($value, true, $time, 'SQL') ) {
			throw new FE('notDatetime');
		}
		// Format to timestamp
		$value = $time;
	}
	
	public function format($Field, &$value) {
		$value = sqlDatetime($value);
	}
}
EntityDescriptor::registerType(new TypeDatetime());

// Derived types
class TypeInteger extends TypeNumber {
	protected $name = 'integer';

	public function parseArgs($fArgs) {
		$args = (object) array('decimals'=>0, 'min'=>-2147483648, 'max'=>2147483647);
		if( isset($fArgs[1]) ) {
			$args->min			= $fArgs[0];
			$args->max			= $fArgs[1];
		} else if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}
	
	public function format($Field, &$value) {
		$value = (int) $value;
	}
}
EntityDescriptor::registerType(new TypeInteger());

class TypeBool extends TypeInteger {
	protected $name = 'bool';

	public function parseArgs($fArgs) {
		return (object) array('decimals'=>0, 'min'=>0, 'max'=>1);
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		$value = (int) !empty($value);
		parent::validate($Field, $value, $inputData, $ref);
	}
}
EntityDescriptor::registerType(new TypeBool());

class TypeFloat extends TypeNumber {
	protected $name = 'float';

	public function parseArgs($fArgs) {
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
	}
}
EntityDescriptor::registerType(new TypeFloat());

class TypeDouble extends TypeNumber {
	protected $name = 'double';

	public function parseArgs($fArgs) {
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
	}
}
EntityDescriptor::registerType(new TypeDouble());

class TypeNatural extends TypeInteger {
	protected $name		= 'natural';

	public function parseArgs($fArgs) {
		$args = (object) array('decimals'=>0, 'min'=>0, 'max'=>4294967295);
		if( isset($fArgs[0]) ) {
			$args->max			= $fArgs[0];
		}
		return $args;
	}
}
EntityDescriptor::registerType(new TypeNatural());

class TypeRef extends TypeNatural {
	protected $name		= 'ref';
// 	protected $nullable	= false;
	// MySQL needs more logic to select a null field with an index
	// Prefer to set default to 0 instead of using nullable

}
EntityDescriptor::registerType(new TypeRef());

class TypeEmail extends TypeString {
	protected $name		= 'email';

	public function parseArgs($fArgs) {
		return (object) array('min'=>5, 'max'=>100);
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		if( !is_email($value) ) {
			throw new FE('notEmail');
		}
	}
}
EntityDescriptor::registerType(new TypeEmail());

class TypePassword extends TypeString {
	protected $name		= 'password';

	public function parseArgs($fArgs) {
		return (object) array('min'=>5, 'max'=>128);
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		if( empty($inputData[$Field->name.'_conf']) || $value!=$inputData[$Field->name.'_conf'] ) {
			throw new FE('invalidConfirmation');
		}
	}
	
	public function format($Field, &$value) {
		$value = hashString($value);
	}
}
EntityDescriptor::registerType(new TypePassword());

class TypePhone extends TypeString {
	protected $name		= 'phone';

	public function parseArgs($fArgs) {
		return (object) array('min'=>10, 'max'=>20);
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		// FR Only for now - Should use user language
		if( !is_phone_number($value) ) {
			throw new FE('notPhoneNumber');
		}
	}
	
	public function format($Field, &$value) {
		// FR Only for now - Should use user language
		$value = standardizePhoneNumber_FR($value, '.', 2);
	}
}
EntityDescriptor::registerType(new TypePhone());

class TypeURL extends TypeString {
	protected $name	= 'url';

	public function parseArgs($fArgs) {
		return (object) array('min'=>10, 'max'=>200);
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		if( !is_url($value) ) {
			throw new FE('notURL');
		}
	}
}
EntityDescriptor::registerType(new TypeURL());

class TypeIP extends TypeString {
	protected $name = 'ip';

	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>7, 'max'=>40, 'version'=>null);
		if( isset($fArgs[0]) ) {
			$args->version		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		if( !is_ip($value) ) {
			throw new FE('notIPAddress');
		}	
	}
}
EntityDescriptor::registerType(new TypeIP());

class TypeEnum extends TypeString {
	protected $name = 'enum';

	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>1, 'max'=>20, 'source'=>null);
		if( isset($fArgs[0]) ) {
			$args->source		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $inputData, &$ref) {
		parent::validate($Field, $value, $inputData, $ref);
		if( !isset($Field->args->source) ) { return; }
		$values		= call_user_func($Field->args->source, $inputData, $ref);
		if( isset($values[$value]) ) {
			$value	= $values[$value];
		} else
		if( !in_array($value, $values) ) {
			throw new FE('notEnumValue');
		}
	}
}
EntityDescriptor::registerType(new TypeEnum());

/*
 DEFAULT VALUE SHOULD BE THE FIRST OF SOURCE
 */
class TypeState extends TypeEnum {
	protected $name = 'state';

	/*
	public function parseArgs($fArgs) {
		$args	= (object) array('min'=>1, 'max'=>20, 'source'=>null);
		if( isset($fArgs[0]) ) {
			$args->source		= $fArgs[0];
		}
		return $args;
	}
	*/
	public function validate($Field, &$value, $inputData, &$ref) {
		TypeString::validate($Field, $value, $inputData, $ref);
		if( !isset($Field->args->source) ) { return; }
		$values		= call_user_func($Field->args->source, $inputData, $ref);
		if( !isset($values[$value]) ) {
			throw new FE('notEnumValue');
		}
		if( $ref===NULL ) {
			$value	= key($values);
		} else if( !isset($ref->{$Field->name}) || !isset($values[$ref->{$Field->name}]) || !in_array($value, $values[$ref->{$Field->name}]) ) {
			throw new FE('unreachableValue');
		}
	}
}
EntityDescriptor::registerType(new TypeState());

