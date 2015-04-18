<?php
/** The inlay class for contents blocks

 *
 * Require core and publisher plugin.
 */

class Inlay extends PermanentEntity {
	
	//Attributes
	protected static $table		= 'inlay';
	
	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	public function analyze() {
		
	}
	
	public function render() {
		
	}
	
	public function getModel() {
		return $this->model;
	}
	
// 	public static function getOneByIdentifier($identifier) {
// 		return static::get(array(
// 			'where'		=> 'identifier LIKE '.static::fv($identifier),
// 			'output'	=> SQLAdapter::OBJECT,
// 		));
// 	}
	
	public static function getByIdentifier($identifier, $limit) {
		return static::get(array(
			'where'		=> 'identifier LIKE '.static::fv($identifier),
			'number'	=> $limit,
		));
	}
}