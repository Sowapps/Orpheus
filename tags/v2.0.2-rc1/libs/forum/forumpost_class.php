<?php

class ForumPost extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum_post';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;

	public function __toString() {
		return escapeText($this->name);
	}
	
	public function getCreationDate() {
		return d($this->create_date);
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @return SiteUser
	 */
	public function getAuthor() {
		return SiteUser::load($this->user_id);
	}
	
	public function getAuthorName() {
		return escapeText($this->user_name);
	}
	
	public function getParent() {
		return static::load($this->parent_id);
	}

	public function getLastAnswer() {
		return $this->last_answer_id ? static::load($this->last_answer_id) : $this;
	}
	
	public function getAllAnswers() {
		return static::get('parent_id='.$this->id());
	}
	
	public function getAnswers($page=1, $number=20) {
		return static::get(array('where' => 'published AND parent_id='.$this->id(), 'orderby'=>'create_date ASC', 'offset'=>$number*($page-1), 'number'=>$number));
	}

	public function addAnswer($input) {
		if( empty($input['name']) ) {
			$input['name']	= 'Re: '.$this->name;
		}
		if( !empty($input['message']) ) {
			$input['message']	= strip_tags($input['message'], '<a><p><br><b><i><u><strike><font><ol><ul><li><blockquote><div>');
		}
		$input['forum_id']	= $this->forum_id;
		$input['parent_id']	= $this->id();
		$this->last_answer_id = $r = static::make($input);
		$this->post_date	= sqlDatetime();
		return $r;
	}
	
	public function remove() {
		foreach( $this->getAllAnswers() as $post ) {
			$post->remove();
		}
		return parent::remove();
	}

	public static function make($input) {
		global $USER;
		$input['user_id']	= $USER->id();
		$input['user_name']	= $USER->fullname;
		$input['published']	= 1;
		$input['post_date']	= sqlDatetime();
		// TODO: SECURE MESSAGE
		return static::create($input, array('parent_id', 'forum_id', 'name', 'message', 'published', 'user_id', 'user_name', 'post_date'));
	}

	/**
	 * @return string The link to the post ifself, with its answers.
	 */
	public function getLink() {
		return static::genLink($this->id());
	}
	/**
	 * @return string The link to the post ifself, with its answers.
	 */
	public static function genLink($id) {
		return u('forum_post', $id);
	}
	
	/**
	 * @return string The link to the post in the parent context.
	 * 
	 * If post has no parent post, we target the post itself
	 */
	public function getThreadLink() {
		return static::genLink($this->parent_id ? $this->parent_id : $this->id()).'#Post-'.$this->id();
	}
}
ForumPost::init();
