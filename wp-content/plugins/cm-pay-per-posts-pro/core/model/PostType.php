<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;

abstract class PostType extends Model {
	
	const POST_TYPE = '';
	
	static protected $postTypeOptions = array();
	static protected $instances;
	
	protected $post;
	
	
	static function init() {
		parent::init();
		static::registerPostType();
	}
	
	
	static function registerPostType() {
		static::$postTypeOptions['labels'] = static::getPostTypeLabels();
		register_post_type(static::POST_TYPE, static::$postTypeOptions);
	}
	

	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\payperposts\model\PostType
	 */
	static function getInstance($post) {
		if (is_scalar($post)) {
			if (!empty(static::$instances[$post])) return static::$instances[$post];
			else if (is_numeric($post)) $post = get_post($post);
			else $post = get_post(array('post_name' => $post));
		}
		if (!empty($post) AND is_object($post) AND $post instanceof \WP_Post AND $post->post_type == static::POST_TYPE) {
			if (empty(static::$instances[$post->ID])) {
				static::$instances[$post->ID] = new static($post);
			}
			return static::$instances[$post->ID];
		}
	}
	
	
	static protected function getPostTypeLabels() {
		return array();
	}
	
	
	function __construct($post) {
		$this->post = $post;
	}
	
	
	function getId() {
		return $this->post->ID;
	}
	
	
	function getPostMeta($name, $single = true) {
		return get_post_meta($this->getId(), $this->getPostMetaKey($name), $single);
	}
	
	function setPostMeta($name, $value) {
		update_post_meta($this->getId(), $this->getPostMetaKey($name), $value);
		return $this;
	}
	
	
	function getPostMetaKey($name) {
		return App::prefix('_' . $name);
	}
	

	function getTitle() {
		return $this->post->post_title;
	}
	
	
	function setTitle($title) {
		$this->post->post_title = $title;
		return $this;
	}
	
	
	function getSlug() {
		return $this->post->post_name;
	}
	
	function setSlug($slug) {
		$this->post->post_name = $slug;
		return $this;
	}
	

	function getContent() {
		return $this->post->post_content;
	}
	
	
	function setContent($desc) {
		$this->post->post_content = $desc;
		return $this;
	}
	
	function save() {
		return wp_update_post((array)$this->post);
	}
	
	
	function getPermalink() {
		return get_permalink($this->getId());
	}
	
	
	function getPost() {
		return $this->post;
	}
	
	
}
