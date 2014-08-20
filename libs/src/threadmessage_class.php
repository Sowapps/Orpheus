<?php
//! The Thread Message class
/*!
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
		return escapeText($this->content);
	}
}

ThreadMessage::init();