<?php

class EntityDescriptor {

	protected $class;
	protected $name;
	protected $fields = array();
	protected $indexes = array();
	
	const DESCRIPTORCLASS='EntityDescriptor';

	public static function load($name, $class=null) {
		$descriptorPath	= ENTITY_DESCRIPTOR_CONFIG_PATH.$name;
		$cache = new FSCache(self::DESCRIPTORCLASS, $name, filemtime(YAML::getFilePath($descriptorPath)));
		if( $cache->get($descriptor) ) {
// 			return $descriptor;
		}
		$conf = YAML::build($descriptorPath, true);
		if( empty($conf->fields) ) {
			throw new Exception('Descriptor file for '.$name.' is corrupted, empty or not found');
		}
		// Build descriptor
		//    Parse Config file
		//      Fields
		$fields = array();
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
		$fields['id'] = (object) array('name'=>'id', 'type'=>'ref', 'args'=>(object)array('decimals'=>0, 'min'=>0, 'max'=>4294967295), 'writable'=>false, 'nullable'=>false);
		foreach( $conf->fields as $field => $fieldInfos ) {
			$type					= is_array($fieldInfos) ? $fieldInfos['type'] : $fieldInfos;
			$parse					= static::parseType($type);
			$Field					= (object) array(
				'name' => $field,
				'type' => $parse->type,
			);
// 			text('Field: '.$field);
			$TYPE					= static::getType($Field->type);
			$Field->args			= $TYPE->parseArgs($parse->args);
			$Field->default			= $parse->default;
			// Type's default
			$Field->writable		= $TYPE->isWritable();
			$Field->nullable		= $TYPE->isNullable();
			// Default if no type's default
			if( !isset($Field->writable) ) { $Field->writable = true; }
			if( !isset($Field->nullable) ) { $Field->nullable = false; }
// 			text('Type nullable: '.b($Field->nullable));
			// Field flags
			if( isset($fieldInfos['writable']) ) {
				$Field->writable = !empty($fieldInfos['writable']);
			} else if( $Field->writable ) {
				$Field->writable = !in_array('readonly', $parse->flags); 
			} else {
				$Field->writable = in_array('writable', $parse->flags); 
			}
			if( isset($fieldInfos['nullable']) ) {
				$Field->nullable = !empty($fieldInfos['nullable']);
			} else if( $Field->nullable ) {
				$Field->nullable = !in_array('notnull', $parse->flags); 
			} else {
// 				text($parse->flags);
				$Field->nullable = in_array('nullable', $parse->flags); 
			}
			$fields[$field]	= $Field;
		}

		//      Indexes
		$indexes = array();
		if( !empty($conf->indexes) ) {
// 			text($conf->indexes);
			foreach( $conf->indexes as $index ) {
				$iType		= static::parseType($index);
// 				text($iType);
				$indexes[]	= (object) array('name'=>$iType->default, 'type'=>strtoupper($iType->type), 'fields'=>$iType->args);
			}
		}
		//    Save cache output
		$descriptor = new EntityDescriptor($name, $fields, $indexes, $class);
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
		$Field	= $this->fields[$field];
		if( !$Field->writable ) {
			throw new InvalidFieldException('readOnlyField', $field, $value, null, $this->name);
		}
		
		$TYPE	= static::getType($Field->type);
		
		if( is_null($value) || ($value==='' && $TYPE->emptyIsNull($Field)) ) {
			$value	= null;
			if( isset($Field->default) ) {
				$value = $Field->default;
				if( is_object($value) ) {
					$value = call_user_func_array($value->type, (array) $value->args);
				}
			} else
			if( !$Field->nullable ) {
				throw new InvalidFieldException('requiredField', $field, $value, null, $this->name);
			}
			// We will format valid null value later (in formatter)
			return;
		}
		// TYPE Validator - Use inheritance, mandatory in super class
		try {
			$TYPE->validate($Field, $value, $inputData);
			// Field Validator - Could be undefined
			if( !empty($Field->validator) ) {
				call_user_func_array($Field->validator, array($Field, &$value, $inputData));
			}
		} catch( FE $e ) {
			throw new InvalidFieldException($e->getMessage(), $field, $value, $Field->type, $this->name, $Field->args);
		}

		// TYPE Formatter - Use inheritance, mandatory in super class
		$TYPE->format($Field, $value);
		// Field Formatter - Could be undefined
	}
	
	public function validate(&$uInputData, $fields=null, $ref=null, &$errCount=0) {
		$inputData	= array();
// 		$class = $this->class;
		foreach( $this->fields as $field => &$fData ) {
			try {
				if( !is_null($fields) && !in_array($field, $fields) ) {
					unset($uInputData[$field]);
// 					if( !is_null($ref) ) { unset($uInputData[$field]); }
					continue;
				}
				if( !$fData->writable ) { continue; }
// 				if( !isset($this->fields[$field]) || !$fData->writable ) { continue; }
				if( !isset($uInputData[$field]) ) {
// 					if( !is_null($ref) ) { continue; }
					$uInputData[$field] = null;
				}
				$this->validateFieldValue($field, $uInputData[$field], $uInputData, $ref);
				if( !isset($ref) || $uInputData[$field]!=$ref->getValue($field) ) {
					$inputData[$field]	= $uInputData[$field];
				}

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
		return $inputData;
	}

	protected static $types = array();
	
	public static function registerType(TypeDescriptor $type) {
		static::$types[$type->getName()] = $type;
	}
	
	public static function getType($name, &$type=null) {
		if( !isset(static::$types[$name]) ) {
			throw new Exception('unknownType_'.$name);
		}
		$type = &static::$types[$name];
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

	public function validate($Field, &$value, $inputData) {
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

	public function validate($Field, &$value, $inputData) {
		$len = strlen($value);
		if( $len < $Field->args->min ) {
			throw new FE('belowMinLength');
		}
		if( $len > $Field->args->max ) {
			throw new FE('aboveMaxLength');
		}
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
	
	public function validate($Field, &$value, $inputData) {
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
	
	public function validate($Field, &$value, $inputData) {
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

	public function validate($Field, &$value, $inputData) {
		$value = (int) !empty($value);
		parent::validate($Field, $value, $inputData);
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
		return (object) array('decimals'=>0, 'min'=>0, 'max'=>4294967295);	
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

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
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

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
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

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
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
	protected $name = 'url';

	public function parseArgs($fArgs) {
		return (object) array('min'=>10, 'max'=>200);
	}

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
		if( !is_url($value) ) {
			throw new FE('notURL');
		}
	}
}
EntityDescriptor::registerType(new TypeURL());

class TypeIP extends TypeString {
	protected $name = 'ip';

	public function parseArgs($fArgs) {
		$args = (object) array('min'=>7, 'max'=>40, 'version'=>null);
		if( isset($fArgs[0]) ) {
			$args->version		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
		if( !is_ip($value) ) {
			throw new FE('notIPAddress');
		}	
	}
}
EntityDescriptor::registerType(new TypeIP());

class TypeEnum extends TypeString {
	protected $name = 'enum';

	public function parseArgs($fArgs) {
		$args = (object) array('min'=>1, 'max'=>20, 'source'=>null);
		if( isset($fArgs[0]) ) {
			$args->source		= $fArgs[0];
		}
		return $args;
	}

	public function validate($Field, &$value, $inputData) {
		parent::validate($Field, $value, $inputData);
		$values = call_user_func($Field->args->source);
		if( isset($values[$value]) ) {
			$value = $values[$value];
		} else
		if( !in_array($value, $values) ) {
			throw new FE('notEnumValue');
		}
	}
}
EntityDescriptor::registerType(new TypeEnum());

