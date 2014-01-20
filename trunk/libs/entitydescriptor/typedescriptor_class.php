<?php

class TypeDescriptor {

	protected $name;
	protected $parent;
	protected $argsParser;
	protected $validator;
	protected $formatter;
	
	public function __construct($name, $parent, $argsParser, $validator, $formatter=null) {
		$this->name			= $name;
		$this->parent		= $parent;
		$this->argsParser	= $argsParser;
		$this->validator	= $validator;
		$this->formatter	= $formatter;
	}
	
	public function validate($args, &$value) {
		if( isset($this->parent) ) {
			$this->parent->validate($args, $value);
		}
		call_user_func($this->validator, $args, $value);
	}
	
	public function format($args, &$value) {
		if( isset($this->parent) ) {
			$this->parent->format($args, $value);
		}
		if( $this->formatter ) {
			call_user_func($this->formatter, $args, $value);
		}
	}
}

