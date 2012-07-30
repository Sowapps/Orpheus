<?php
//! The abstract publication class
/*!
 * This class implements a publication system to the abstract status class.
 * Its purpose is to be used for articles, posts, comments and other publications.
 * It manages a cache for user editable contents and register automatically some events.
 * The status of the publication can also be managed easily by automations or administrators.
 * Finally, it implements an anti-flood system to avoid spam of publications.
 *
 * Require core plugin.
 * 
 * Events:
 * - 'create'	: When the new publication is created.
 * - 'edit'		: When the publication is edited.
 * 
 * AbstractStatus fields:
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
	
	//! Magic string conversion
	/*!
		\return The string valu of this object.
		
		The string value is the contents of the publication.
	*/
	public function __toString() {
		return $this->getHTML();
	}
	
	// *** USER METHODS ***
	
	//! Updates this publication object
	/*!
	 * \sa PermanentObject::update()
	 * 
	 * This update method manages 'name' and 'user_name' fields.
	 */
	public function update($uInputData, array $data=array()) {
		if( !User::canDo(static::$table.'_edit') ) {
			throw new UserException('forbiddenUpdate');
		}
		
		try {
			$inputData['name'] = self::checkName($uInputData);
			if( $inputData['name'] != $this->name ) {
				$data['name'] = $inputData['name'];
			}
		} catch(UserException $e) { reportError($e); }
		
		try {
			$inputData['user_name'] = self::checkUserName($uInputData);
			if( $inputData['user_name'] != $this->user_name ) {
				$data['user_name'] = $inputData['user_name'];
			}
		} catch(UserException $e) { reportError($e); }
		
		return parent::update($uInputData, $data);
	}
	
	//! Gets HTML contents
	/*!
	 * \param $cacheUpdate True to force the cache to update.
	 * \return The cache content, the generated HTML.
	 * \sa generateHTML()
	 */
	public function getHTML($cacheUpdate=0) {
		if( !strlen($this->cache) || $cacheUpdate ) {
			$this->cache = $this->generateHTML();
		}
		return $this->cache;
	}
	
	//! Generate HTML contents
	/*!
	 * \return The generated HTML contents.
	 * \overrideit
	 */
	abstract public function generateHTML();
	
	//! Gets permalink
	/*!
	 * \return The permalink.
	 * \overrideit
	 * 
	 * Gets the unique and permanent link.
	 */
	abstract public function getPermalink();
	
	// *** STATIC METHODS ***
	
	//! Erase all cache for this publication type
	public static function eraseAllCache() {
		return SQLMapper::doUpdate(array(
			'table' => static::$table,
			'what' => "cache=''",
		));
	}
	
	// 		** CHECK METHODS **
	
	//! Checks a name
	/*!
	 * \param $inputData The input data from the user.
	 * \return The stripped name.
	 * 
	 * Validates the name in array $inputData.
	 */
	public static function checkName($inputData) {
		if( empty($inputData['name']) ) {
			throw new UserException('invalidName');
		}
		return strip_tags($inputData['name']);
	}
	
	//! Checks a user id
	/*!
	 * \param $inputData The input data from the user.
	 * \return The user id as integer.
	 * 
	 * Validates the user_id in array $inputData.
	 */
	public static function checkUserID($inputData) {
		if( !isset($inputData['user_id']) || !is_ID($inputData['user_id']) ) {
			throw new UserException('invalidUserID');
		}
		return (int) $inputData['user_id'];
	}
	
	//! Checks a user name
	/*!
	 * \param $inputData The input data from the user.
	 * \return The stripped user name.
	 * 
	 * Validates the user_name in array $inputData.
	 */
	public static function checkUserName($inputData) {
		if( empty($inputData['user_name']) ) {
			throw new UserException('invalidUserName');
		}
		return strip_tags($inputData['user_name']);
	}
	
	//! Checks user input
	/*!
	 * \sa PermanentObject::checkUserInput()
	*/
	public static function checkUserInput($uInputData) {
		$data = array();
		$data['name'] = self::checkName($uInputData);
		$data['user_id'] = self::checkUserID($uInputData);
		$data['user_name'] = self::checkUserName($uInputData);
		
		$data += parent::checkUserInput($uInputData);
		
		return $data;
	}
	
	//! Checks for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data) {
		if( empty($data['name']) && empty($data['user_id']) && empty($data['create_ip']) ) {
			return;//Nothing to check.
		}
		$ucheck = ($data['user_id']) ? "user_id={$data['user_id']}" : "create_ip LIKE '{$data['create_ip']}'";
		
		$publication = SQLMapper::doSelect(array(
			'table' => static::$table,
			'what' => 'name',
			'where' => 'name = '.SQLMapper::quote($data['name']).' OR
				( '.$ucheck.' AND create_time >= '.(time()-static::$floodDelay).')',
			'number' => 1
		));
		if( empty($publication) ) {
			return;
		}
		if( $publication['name'] == $data['name'] ) {
			throw new UserException("entryExisting");
		} else if( !user_can('anecdotes_safeUse') ) {
			throw new UserException("floodDelay");
		}
	}
	
	//! Internal initialization
	public static function init() {
		self::$fields = array_unique(array_merge(self::$fields,parent::$fields));
	}
}
AbstractPublication::init();
?>