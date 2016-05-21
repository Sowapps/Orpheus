<?php

class ForumPostView extends PermanentEntity {

	//Attributes
	protected static $table		= 'forum_postview';
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	// *** DEV METHODS ***
	
	public function isViewedAfter(ForumPost $post) {
		return strtotime($this->last_date) > strtotime($post->post_date);
	}
	
	// *** STATIC METHODS ***
	
}
ForumPostView::init();
