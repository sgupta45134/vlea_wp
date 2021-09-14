<?php

namespace com\cminds\payperposts\metabox;

use com\cminds\payperposts\App;

abstract class MetaBox {
	
	const SLUG = '';
	const NAME = '';
	const CONTEXT = 'normal';
	const PRIORITY = 'high';
	const META_BOX_PRIORITY = 10;
	const SAVE_POST_PRIORITY = 10;
	
	static protected $supportedPostTypes = array();
	static protected $suspendActions = 0;
	
	static function bootstrap() {
		add_action('add_meta_boxes', array(get_called_class(), 'add_meta_boxes'), static::META_BOX_PRIORITY);
		add_action('save_post', array(get_called_class(), 'save_post'), static::SAVE_POST_PRIORITY, 1);
	}
	
	
	static function render($post) {}
	
	static function savePost($post_id) {}
	
	
	
	protected static function renderNonceField($post) {
		$field = static::getNonceFieldName($post->ID);
		printf('<input type="hidden" name="%s" value="%s" />', $field, wp_create_nonce($field));
	}
	
	
	static function add_meta_boxes() {
		// Register meta box
		foreach (static::getSupportedPostTypes() as $postType) {
			\add_meta_box(
				$id = static::getId(),
				$name = static::getName(),
				$func = array(get_called_class(), 'render'),
				$postType,
				static::CONTEXT,
				static::PRIORITY
			);
		}
	}
	
	
	static function save_post($post_id) {
		if (!static::$suspendActions) {
			static::$suspendActions++;
			if (static::validateNonce($post_id)) {
				static::savePost($post_id);
			}
			static::$suspendActions--;
		}
	}
	
	
	
	static function getNonceFieldName($post_id) {
		return static::getId() . '_nonce_' . $post_id;
	}
	
	
	static function validateNonce($post_id) {
		$field = static::getNonceFieldName($post_id);
		return (!empty($_POST[$field]) AND wp_verify_nonce($_POST[$field], $field));
	}
	
	static function getName() {
		return static::NAME;
	}
	
	
	static function getId() {
		return App::prefix('-' . static::SLUG);
	}
	
	
	static function getSupportedPostTypes() {
		return static::$supportedPostTypes;
	}
	
}
