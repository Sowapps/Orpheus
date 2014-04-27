<?php

class Forum extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function getPosts($publishedOnly=true) {
		return ForumPost::get('forum_id='.$this->id().($publishedOnly ? ' AND published' : ''));
	}
	
	public static function getAll($parent=0, $publishedOnly=true) {
		return static::get('1'.($parent ? ' AND parent_id='.$parent : '').($publishedOnly ? ' AND published' : ''), 'position ASC');
	}

	public static function getMaxPosition($forum) {
		return (int) ForumPost::get(array(
			'what'		=> 'MAX(position) max',
			'where'		=> 'parent_id='.id($forum),
			'output'	=> SQLAdapter::ARR_FIRST
		));
	}
}
Forum::init();
