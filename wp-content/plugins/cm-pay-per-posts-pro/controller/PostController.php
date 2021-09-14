<?php
namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\Subscription;

class PostController extends Controller {
	
	const COLUMN_POST_PRICING_GROUP = 'cmppp_edd_post_pricing_group';
	const COLUMN_POST_PRICE = 'cmppp_edd_post_price';
	
	
	protected static $actions = array(
		'admin_init',
	);
	
	
	static function admin_init() {
		foreach (Subscription::getSupportedPostTypes() as $postType) {
			add_filter('manage_'. $postType .'_posts_columns', array(__CLASS__, 'registerPostPriceColumn'));
			add_action('manage_'. $postType .'_posts_custom_column', array(__CLASS__, 'displayPostPriceColumn'), 10, 2);
		}
	}
	
	
	static function registerPostPriceColumn($columns) {
		if (Settings::getOption(Settings::OPTION_POST_PRICING_GROUP_COLUMN_SHOW)) {
			$columns[self::COLUMN_POST_PRICING_GROUP] = 'Pricing group';
		}
		if (Settings::getOption(Settings::OPTION_POST_PRICES_COLUMN_SHOW)) {
			$columns[self::COLUMN_POST_PRICE] = 'Prices';
		}
		return $columns;
	}
	
	
	
	static function displayPostPriceColumn($column, $postId) {
		if (self::COLUMN_POST_PRICE == $column) {
			self::showPostPrices($postId);
		}
		else if (self::COLUMN_POST_PRICING_GROUP == $column) {
			self::showPostPricingGroup($postId);
		}
	}
	
	
	
	static function showPostPrices($postId) {
		ob_start();
		do_action('cmppp_admin_show_post_prices', $postId);
		$out = ob_get_clean();
		if (empty($out)) echo '&#8213;';
		else echo $out;
	}
	
	
	static function showPostPricingGroup($postId) {
		ob_start();
		do_action('cmppp_admin_show_post_pricing_group', $postId);
		$out = ob_get_clean();
		if (empty($out)) echo '&#8213;';
		else echo $out;
	}
	
	/**
	 * Check if this is WP preview mode or this is search engine crawler's request.
	 * 
	 * @param Post $post
	 * @return bool
	 */
	static function isPreviewAllowed(Post $post) {
		
		if (is_preview()) {
			// Check if preview mode is allowed
			return Settings::getOption(Settings::OPTION_SHOW_FULL_POST_IN_PREVIEW);
		}
		
		if (Settings::getOption(Settings::OPTION_SHOW_FULL_POST_FOR_SEARCH_ENGINES)) {
			// Allow search engines crawlers
			$userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
			$list = explode("\n", Settings::getOption(Settings::OPTION_ALLOWED_SEARCH_ENGINES));
			foreach ($list as $regexp) {
				if (preg_match('~'. $regexp .'~i', $userAgent)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
}
