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
	
	public function getAuthor() {
		return SiteUser::load($this->user_id);
	}
	
	public function getAuthorName() {
		return escapeText($this->user_name);
	}

	public function getLastAnswer() {
		return $this->last_answer_id ? static::load($this->last_answer_id) : $this;
	}

	public function getLink() {
		return u('forum_post', $this->id());
	}
}
ForumPost::init();
