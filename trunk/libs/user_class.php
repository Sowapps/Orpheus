<?php
/* class/user_class.php
 * PHP File for class: User
 * Classe d'utilisation et de gestion d'un utilisateur pour CE site web.
 *
 * Auteur: Florent Hazard (Cartman34).
 * Version: 24
 * 
 * Requiert:
 * is_id()
 * is_email()
 * pdo_query()
 * 
 */

class User extends SiteUser {
	
	//Attributes
	protected static $fields = array(
		'fullname', 'contact_time'
	);
	protected static $userEditableFields = array(
		'fullname'
	);

	// *** METHODES SURCHARGEES ***
	
	public function __toString() {
		return $this->fullname;
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}
	
	public function update($uInputData, array $data=array()) {
		
		try {
			$inputData['fullname'] = self::checkFullName($uInputData);
			if( $inputData['fullname'] != $this->fullname ) {
				$data['fullname'] = $inputData['fullname'];
			}
		} catch(UserException $e) { addUserError($e); }
		
//		if( !empty($data['name']) ) {
//			$name = $data['name'];
//		}
		
		$r = parent::update($uInputData, $data);
//		if( $r && isset($name) && $name == ) {
//			
//		}
		return $r;
	}
	
	public function runForUpdate() {
		if( !empty($this->modFields) && in_array('fullname', $this->modFields) ) {
			$table=Anecdote::getTable();
			pdo_query("UPDATE {$table} SET user_name='{$this->fullname}' WHERE user_id={$this->id}", PDOEXEC);
		}
	}
	
	// 		** METHODES DE VERIFICATION **

	public static function checkFullName($inputData) {
		if( empty($inputData['fullname']) ) {
			throw new UserException('invalidFullName');
		}
		return strip_tags($inputData['fullname']);
	}
	
	public static function checkUserInput($uInputData) {
		$data = parent::checkUserInput($uInputData);
		$data['fullname'] = static::checkFullName($uInputData);
		return $data;
	}
	
	public static function init() {
		self::$fields = array_unique(array_merge(self::$fields,parent::$fields));
		self::$userEditableFields = array_unique(array_merge(self::$userEditableFields,parent::$userEditableFields));
	}
}
User::init();
?>
