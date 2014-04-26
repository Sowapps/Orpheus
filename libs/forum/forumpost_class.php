<?php
//! The post class for threads
/*!
 *
 * Require core and publisher plugin.
 */

class ForumPost extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum_post';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	// *** OVERRIDDEN METHODS ***
	
	// *** DEV METHODS ***
	
	// *** STATIC METHODS ***
	
}
ForumPost::init();
