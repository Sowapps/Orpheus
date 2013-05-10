<?php
//! The post class for threads
/*!
 *
 * Require core and publisher plugin.
 */

class Post extends AbstractPublication {

	//Attributes
	protected static $table = 'posts';
	protected static $fields = array(
		'contents'
	);
	protected static $editableFields = array('contents');
	protected static $validator = array(
		'contents'		=> 'checkContents'
	);
	
	protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	
	public static function checkContents($inputData, $ref) {
		return $inputData['contents'];
	}
	
}
Post::init();