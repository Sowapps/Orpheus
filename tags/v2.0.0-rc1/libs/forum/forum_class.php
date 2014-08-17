<?php

class Forum extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return escapeText($this->name);
	}

	public function getLink() {
		return u('forums').'#forum-'.$this->id();
	}
	
	/**
	 * @param boolean $publishedOnly
	 * @return multitype:static
	 */
	public function getPosts($publishedOnly=true) {
		return ForumPost::get('!parent_id AND forum_id='.$this->id().($publishedOnly ? ' AND published' : ''));
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
	
	protected static $config;
	protected static function config($key, $default=null) {
		if( static::$config === NULL ) {
			static::$config	= Config::build('forum', true);
		}
		return static::$config->get($key, $default);
	}
}
Forum::init();
