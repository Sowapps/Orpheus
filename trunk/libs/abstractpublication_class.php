<?php
/* class/abstractpublication_class.php
 * PHP File for class: AbstractPublication
 * Classe abstraite implémentant un système de publication à la classe AbstractStatus.
 * Cette classe est essentiellement destinée aux articles et aux publications en tout genre.
 *
 * Author: Florent Hazard (Cartman34).
 * Revision: 2
 * 
 * Requiert:
 * is_id()
 * 
 * Events:
 * - 'create'	: When the new publication is created.
 * - 'edit'		: When the publication is edited.
 */
 
/* Statuable require:
 * - $status attribute to determine known status with first status in first.
 * - field 'status'
 * 
 * Common example:
 * private static $status = array('draft'=>array('waiting'), 'waiting'=>array('approved', 'rejected'), 'approved'=>array('rejected'), 'rejected'=>array('approved'));
 */

/* AbstractStatus fields:
 * status.
 * 
 * Publication fields (from AbstractStatus):
 * name, user_id, published, cache, create_time, create_ip, edit_time, edit_ip.
 * 
 */

abstract class AbstractPublication extends AbstractStatus {
	
	//Attributes
	protected static $status = array('draft'=>array('waiting'), 'waiting'=>array('approved', 'rejected'), 'approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
		'name', 'user_id', 'user_name', 'published', 'cache',
		'create_time', 'create_ip', 'edit_time', 'edit_ip'
	);
	public static $floodDelay = 300;//in seconds.

	// *** METHODES SURCHARGEES ***
	
	public function __toString() {
		return $this->getHTML();
	}
	
	// *** METHODES UTILISATEUR ***
	
	public function update($uInputData, array $data=array()) {
		if( !user_can(static::$table.'_edit') ) {
			throw new UserException('forbiddenUpdate');
		}
		
		try {
			$inputData['name'] = self::checkName($uInputData);
			if( $inputData['name'] != $this->name ) {
				$data['name'] = $inputData['name'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		try {
			$inputData['user_name'] = self::checkUserName($uInputData);
			if( $inputData['user_name'] != $this->user_name ) {
				$data['user_name'] = $inputData['user_name'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		return parent::update($uInputData, $data);
	}
	
	public function getHTML($cacheUpdate=0) {
		if( !strlen($this->cache) ) {
			$this->cache = $this->generateHTML();
		}
		return $this->cache;
	}
	abstract public function generateHTML();
	abstract public function getPermalink();
	
	// *** METHODES STATIQUES ***
	
	public static function getClass() {
		return __CLASS__;
	}
	
	public static function eraseAllCache() {
		$table=static::$table;
		return pdo_query("UPDATE {$table} SET cache=''", PDOEXEC);
	}
	
	// 		** METHODES DE VERIFICATION **
	
	public static function checkName($inputData) {
		if( empty($inputData['name']) ) {
			throw new UserException('invalidName');
		}
		return strip_tags($inputData['name']);
	}
	
	public static function checkUserID($inputData) {
		if( !isset($inputData['user_id']) || !is_ID($inputData['user_id']) ) {
			throw new UserException('invalidUserID');
		}
		return (int) $inputData['user_id'];
	}
	
	//Correspond à User::checkFullName($inputData)
	public static function checkUserName($inputData) {
		if( empty($inputData['user_name']) ) {
			throw new UserException('invalidUserName');
		}
		return strip_tags($inputData['user_name']);
	}
	
	public static function checkUserInput($uInputData) {
		$data = array();
		$data['name'] = self::checkName($uInputData);
		$data['user_id'] = self::checkUserID($uInputData);
		$data['user_name'] = self::checkUserName($uInputData);
		
		$data += parent::checkUserInput($uInputData);
		
		return $data;
	}
	
	public static function checkForEntry($data) {
		if( empty($data['name']) && empty($data['user_id']) && empty($data['create_ip']) ) {
			return;//Nothing to check.
		}
		$ucheck = ($data['user_id']) ? "user_id={$data['user_id']}" : "create_ip LIKE '{$data['create_ip']}'";
		$table = static::$table;
		$publication = pdo_query("SELECT name FROM {$table} WHERE
			name LIKE ".pdo_quote($data['name'])." OR
			( {$ucheck} AND create_time >= ".(time()-static::$floodDelay).")
			LIMIT 1", PDOFETCH);
		if( empty($publication) ) {
			return;
		}
		if( $publication['name'] == $data['name'] ) {
			throw new UserException("entryExisting");
		} else if( !user_can('anecdotes_safeUse') ) {
			throw new UserException("floodDelay");
		}
	}
	
	public static function init() {
		self::$fields = array_unique(array_merge(self::$fields,parent::$fields));
	}
}
AbstractPublication::init();
?>