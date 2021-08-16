<?php

namespace Demo;

use Orpheus\Exception\UserException;
use Orpheus\Publisher\PermanentObject\PermanentObject;

/** A sample demo test class
 * Example of how to use the permanent object.
 */
class DemoTest extends PermanentObject {
	
	//Attributes
	protected static string $table = 'test';
	
	protected static array $fields = ['id', 'name'];
	
	protected static ?array $editableFields = ['name'];
	
	protected static $validator = ['name' => 'checkName'];
	
	// *** OVERLOADED METHODS ***
	
	// *** STATIC METHODS ***
	
	
	// 		** CHECK METHODS **
	
	/** Checks Field 'name'
	 *
	 * @param array $input The user input.
	 * @param PermanentObject|null $ref The reference to check the field from.
	 * @return string a valid field 'name'.
	 */
	public static function checkName(array $input, $ref = null) {
		if( empty($input['name']) ) {
			throw new UserException('emptyName');
		}
		if( strlen($input['name']) < 10 ) {
			throw new UserException('tooShortName');
		}
		
		return strip_tags($input['name']);
	}
	
	/**
	 * @param array $data
	 * @param PermanentObject|null $ref
	 */
	public static function checkForObject($data, $ref = null) {
		if( empty($data['name']) ) {
			return;//Nothing to check.
		}
		$options = [
			'number' => 1,
			'where'  => 'name=' . static::formatValue($data['name']),
		];
		$data = static::get($options);
		if( empty($data) ) {
			return;
		}
		throw new UserException("existingObject");
	}
	
}
