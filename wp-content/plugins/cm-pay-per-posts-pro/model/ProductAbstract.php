<?php
namespace com\cminds\payperposts\model;

use com\cminds\payperposts\helper\TimeHelper;

class ProductAbstract extends PostType {

	const META_PRICE = '';
	const META_SUBSCRIPTION_TIME_SEC = 'cmppp_subscription_time_sec';
	const META_PRICING_GROUP = 'cmppp_pricing_group';
	
	const CATEGORY_TAXONOMY = 'download_category';
	const CATEGORY_NAME = 'Video Lessons';
	
	const CART_KEY  = 'cm_pay_per_post';
	const CART_KEY_BACKLINK_URL = 'backlinkUrl';
	const CART_KEY_BACKLINK_TEXT = 'backlinkText';
	
	const ACTIVATE_SUBSCRIPTION_ACTION = 'cmppp_activate_subscription';
	
	
	static function registerPostType() {
		// don't
	}
	
	
	function getPostMetaKey($name) {
		return $name;
	}
	
	
	static function getSupportedPostTypes() {
		return array(
// 			EasyDigitalDownload::POST_TYPE => EasyDigitalDownload::class,
			WooCommerceProduct::POST_TYPE => WooCommerceProduct::class,
		);
	}
	
	
	static function getProductsList() {
		global $wpdb;
		
		static $results;
		if (!empty($results)) return $results;
		
		$currency = static::getCurrency();
		
		$sql = $wpdb->prepare("SELECT p.ID, p.post_title, pm_price.meta_value AS price
				FROM $wpdb->posts p
				JOIN $wpdb->postmeta pm_price ON pm_price.post_id = p.ID AND pm_price.meta_key = %s
				WHERE post_type = %s AND post_status = %s",
				static::META_PRICE, static::POST_TYPE, 'publish');
		$posts = $wpdb->get_results($sql, ARRAY_A);
		
		$results = array();
		foreach ($posts as $post) {
			$results[$post['ID']] = sprintf('%s --- %s %s', $post['post_title'], $post['price'], $currency);
		}
		return $results;
	}
	
	
	static function getCurrency() {
		throw self::createOverrideException();
	}
	
	
	static function getCostCurrency() {
		return static::getCurrency();
	}
	
	
	static function isAvailable() {
		throw self::createOverrideException();
	}
	
	
	static function isConfigured() {
		throw self::createOverrideException();
	}
	
	function addToCart() {
		throw self::createOverrideException();
	}
	
	
	static function getCheckoutUri() {
		throw self::createOverrideException();
	}
	
	
	static function create($subscriptionTimeSec, $price, $pricingGroupIndex) {
		throw self::createOverrideException();
	}
	
	
	static private function createOverrideException() {
		return new \Exception('This method should be overridden in the subclass.');
	}
	
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
				$this->getId()
			));
	}
	
	
	function updateTitle() {
		$title = static::createDownloadName($this->getSubscriptionTimeSec(), $this->getPricingGroupIndex());
		return $this->setTitle($title)->save();
	}
	
	
	function archive() {
		$this->setPostStatus('trash');
	}
	
	
	function setPostStatus($status) {
		$my_post = array();
		$my_post['ID'] = $this->getId();
		$my_post['post_status'] = $status;
		wp_update_post( $my_post );
		$this->post->post_status = $status;
	}
	
	
	function getPricingGroupIndex() {
		return $this->getPostMeta(static::META_PRICING_GROUP);
	}
	
	
	function setPricingGroupIndex($groupIndex) {
		return $this->setPostMeta(static::META_PRICING_GROUP, $groupIndex);
	}
	
	
	function isThePluginsPrice() {
		$time = $this->getSubscriptionTimeSec();
		return (!empty($time) AND $time > 0);
	}
	
	
	static function getAll() {
		return static::getForPricingGroup($pricingGroupIndex = null);
	}
	
	
	static function getForPricingGroup($pricingGroupIndex = null) {
		$ids = static::getIdsForPricingGroup($pricingGroupIndex);
		$results = array();
		foreach ($ids as $id) {
			$results[] = static::getInstance($id);
		}
		return $results;
	}
	
	
	static function getIdsForPricingGroup($pricingGroupIndex = null) {
		global $wpdb;
		
		$sql = "SELECT ID FROM $wpdb->posts p " . PHP_EOL;
		
		$join = $wpdb->prepare("JOIN $wpdb->postmeta subtime ON subtime.post_id = p.ID AND subtime.meta_key = %s", static::META_SUBSCRIPTION_TIME_SEC);
		if ($pricingGroupIndex) {
			$join = $wpdb->prepare("JOIN $wpdb->postmeta pgroup ON pgroup.post_id = p.ID AND pgroup.meta_key = %s AND pgroup.meta_value = %s",
					static::META_PRICING_GROUP, $pricingGroupIndex) . PHP_EOL;
		}
		$sql .= $join . PHP_EOL;
		
		$sql .= $wpdb->prepare("WHERE p.post_type = %s AND p.post_status = 'publish'
					ORDER BY p.menu_order ASC",
				static::POST_TYPE);
		
// 		var_dump($sql);exit;
		
		$ids = $wpdb->get_col($sql);
		return $ids;
	}
	
	
	
	static function createDownloadName($subscriptionTimeSec, $pricingGroup) {
		if(Settings::getOption(Settings::OPTION_SUBSCRIPTION_MODE) == 2) {
			return sprintf('CM Pay Per Groups: %s (%s)', $pricingGroup, TimeHelper::seconds2period($subscriptionTimeSec));
		} else {
			return sprintf('CM Pay Per Posts: %s (%s)', $pricingGroup, TimeHelper::seconds2period($subscriptionTimeSec));
		}
	}
	
	
	function setSubscriptionTime($timeSec) {
		return $this->setPostMeta(static::META_SUBSCRIPTION_TIME_SEC, $timeSec);
	}
	
	
	function getSubscriptionTimeSec() {
		return $this->getPostMeta(static::META_SUBSCRIPTION_TIME_SEC);
	}
	
	
	function getSubscriptionPeriodNumber() {
		$seconds = $this->getSubscriptionTimeSec();
		$period = explode(' ', TimeHelper::seconds2period($seconds));
		return reset($period);
	}
	
	
	function getSubscriptionPeriodUnit() {
		$seconds = $this->getSubscriptionTimeSec();
		$period = explode(' ', TimeHelper::seconds2period($seconds));
		$unit = end($period);
		if ($unit == 'minute' OR $unit == 'minutes') {
			return 'min';
		} else {
			return substr($unit, 0, 1);
		}
	}
	
	
	function setPrice($price) {
		return $this->setPostMeta(static::META_PRICE, $price);
	}
	
	
	function getPrice() {
		return $this->getPostMeta(static::META_PRICE);
	}
	
	static function getPluginsCategoryId() {
		global $wpdb;
		
		// We use manual queries since EDD registers taxonomy only after the init hook
		// and we need this in the plugins_loaded which caused an "invalid taxonomy" error.
		
		$termId = $wpdb->get_var($wpdb->prepare("SELECT tt.term_id FROM $wpdb->term_taxonomy tt
				JOIN $wpdb->terms t ON t.term_id = tt.term_id
				WHERE tt.taxonomy = %s AND t.name = %s", static::CATEGORY_TAXONOMY, static::CATEGORY_NAME));
		
		if ($termId) {
			return $termId;
		} else { // Insert term
			
			$termResult = $wpdb->insert($wpdb->terms, array('name' => static::CATEGORY_NAME, 'slug' => sanitize_title(static::CATEGORY_NAME)));
			if ($termResult) {
				$term_id = $wpdb->insert_id;
				$ttResult = $wpdb->insert($wpdb->term_taxonomy, array('taxonomy' => static::CATEGORY_TAXONOMY, 'term_id' => $term_id));
				if ($ttResult) {
					return $term_id;
				}
			}
			
		}
		
	}
	
	
	static function createProductInstance($productId) {
		$supportedPostTypes = static::getSupportedPostTypes();
		$product = null;
		if ($post = get_post($productId) AND isset($supportedPostTypes[$post->post_type])) {
			$productClassName = $supportedPostTypes[$post->post_type];
			$product = call_user_func(array($productClassName, 'getInstance'), $post);
		}
		return $product;
	}
	
	
}