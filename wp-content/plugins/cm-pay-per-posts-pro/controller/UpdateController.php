<?php
namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Post;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmppp_update_methods';
	
	static $actions = array('plugins_loaded' => array('priority' => 1));
	

	static function plugins_loaded() {
		global $wpdb;
		
		if (isset($_GET['cm_update_1_5_0_multisite'])) {
			static::update_1_5_0_multisite();
		}
		
		if (defined('DOING_AJAX') && DOING_AJAX) return;
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update_/', $method)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates, true);
		}
		
	}
	
	
	static function update_1_5_0_multisite() {
		global $wpdb;
		$subscriptions = $wpdb->get_results($wpdb->prepare("SELECT s.umeta_id AS subscription_id, s.user_id AS user_id, po.meta_value AS post_id
				FROM $wpdb->usermeta s
				JOIN $wpdb->usermeta po ON po.meta_key = CONCAT(%s, s.umeta_id)
				LEFT JOIN $wpdb->usermeta b ON b.meta_key = CONCAT(%s, s.umeta_id)
				WHERE s.meta_key = %s AND b.umeta_id IS NULL
				",
				Subscription::META_MP_SUBSCRIPTION_POST_ID .'_',
				Subscription::META_MP_SUBSCRIPTION_BLOG_ID .'_',
				Subscription::META_MP_SUBSCRIPTION
			), ARRAY_A);
// 		var_dump($subscriptions);
		
		$currentBlogId = (is_multisite() ? get_current_blog_id() : null);
		$sites = (is_multisite() ? get_sites() : array());
		
		foreach ($subscriptions as $subscription) {
			$blogId = null;
			if (is_multisite()) {
				foreach ($sites as $site) {
					switch_to_blog($site->blog_id);
					if ($post = Post::getInstance($subscription['post_id']) AND $post->isPaid()) {
						$blogId = $site->blog_id;
						break;
					}
				}
			} else {
				$blogId = get_current_blog_id();
			}
// 			var_dump($subscription);var_dump('blog id = ' . $blogId);
			if ($blogId) {
				add_user_meta($subscription['user_id'], Subscription::META_MP_SUBSCRIPTION_BLOG_ID . '_' . $subscription['subscription_id'], $blogId, $unique = true);
			}
		}
		
		// Back to previous blog id
		if ($currentBlogId) {
			switch_to_blog($currentBlogId);
		}
		
	}
	
	
}