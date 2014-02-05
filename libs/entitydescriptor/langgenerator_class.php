<?php

class LangGenerator {
	
	public static $testedValues = array(null, '', '0', 'string', '1.997758887755445', '-974455277432344345647573654743352', '974455277432344345647573654743352');
	
	public function generate($ed) {
		$rows = '';
		foreach( $ed->getFields() as $field ) {
			$rows .= $this->getErrorsForField($ed, $field);
		}
	}
	
	public function getRows($ed) {
		$r = array();
		foreach( $ed->getFieldsName() as $field ) {
// 			$r = array_merge($r, $this->getErrorsForField($ed, $field));
			$r += $this->getErrorsForField($ed, $field);
		}
		return array_unique($r);
	}
	
	public function getErrorsForField($ed, $field) {
		$r = array();
		foreach( static::$testedValues as $value ) {
			try {
				$ed->validateFieldValue($field, $value);
			} catch( InvalidFieldException $e ) {
				$r["$e"] = $e;
			}
		}
		return $r;
	}
	
}