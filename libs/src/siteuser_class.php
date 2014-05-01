<?php
//! The site user class
/*!
 * A site user is a registered user.
 * 
 * Require:
 * is_id()
 * is_email()
 * pdo_query()
 * 
 */

class SiteUser extends User {
	
	//Attributes
	protected static $fields = array(
		'fullname'
	);
	protected static $editableFields = array(
		'fullname'
	);
	
	// *** OVERLOADED METHODS ***
	
	public function __toString() {
		return escapeText($this->fullname);
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}

	// 		** CHECK METHODS **

	public static function checkFullName($inputData) {
		if( empty($inputData['fullname']) ) {
			static::throwException('invalidFullName');
		}
		return strip_tags($inputData['fullname']);
	}
	
	public static function checkUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data = parent::checkUserInput($uInputData, $fields, $ref, $errCount);
		if( !empty($uInputData['password']) ) {
			$data['real_password'] = $uInputData['password'];
		}
		return $data;
	}
	
	// *** FORUM LIB ***
	protected $postViews	= NULL;
	
	public function getAllPostViews() {
		if( $this->postViews === NULL ) {
			$postViews	= ForumPostView::get('user_id='.$this->id());
			$this->postViews	= array();
			foreach( $postViews as $pv ) {
				$this->postViews[$pv->id()]	= $pv;
			}
		}
		return $this->postViews;
	}
	
	public function setPostView($post) {
		$postView	= ForumPostView::get(array('where' => 'user_id='.$this->id().' AND post_id='.id($post), 'output'=>SQLAdapter::OBJECT));
		if( $postView ) {
			$postView->last_date	= sqlDatetime();
		} else {
			ForumPostView::create(array('user_id' => $this->id(), 'post_id' => id($post), 'last_date'=>sqlDatetime()));
		}
		$this->postViews	= null;// Should be rarely effective
	}
	
	public function getLink() {
		return static::genLink($this->id());
	}
	public static function genLink($id) {
		return u('profile', $id);
	}
}
SiteUser::init();
