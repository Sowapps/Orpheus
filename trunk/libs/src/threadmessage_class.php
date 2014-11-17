<?php
/** The Thread Message class

	This class represent a Thread message object
*/
class ThreadMessage extends PermanentEntity {
	
	//Attributes
	protected static $table		= 'threadmessage';
	
	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	public function __toString() {
		return nl2br(escapeText($this->content));
	}
	
	public function getUser() {
		return SiteUser::load($this->user_id);
	}
	
// 	public function getISOCreateDate() {
// 		return strftime('%Y-%m-%d %R', strtotime($this->create_date));
// 	}
	
	public function getAdaptiveDate() {
		$time	= strtotime($this->create_date.' GMT');
		return dayTime($time) == dayTime() ? strftime('%R', $time) : strftime('%Y-%m-%d', $time);
	}
}

ThreadMessage::init();