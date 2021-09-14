<?php
namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;
use com\cminds\payperposts\helper\TimeHelper;

class WooCommerceProduct extends ProductAbstract {
	
	const POST_TYPE = 'product';
	const META_PRICE = '_price';
	
	static function registerPostType() {
		// don't
	}
	
	/**
	 *
	 * @param mixed $post
	 * @return WooCommerceProduct
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	static function isAvailable() {
		return apply_filters('cm_pppedd_available', false) && class_exists('\\WooCommerce');
	}
	

	static function isConfigured() {
		return static::isAvailable();
	}
	
	
	static function getCurrency() {
		if (function_exists('get_woocommerce_currency')) {
			return get_woocommerce_currency();
		}
	}
	
	
	function addToCart($details = array()) {
		$cart_item_data = array(
			static::CART_KEY => $details,
		);
		return WC()->cart->add_to_cart($productId = $this->getId(), $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data);
	}
	
	static function getCheckoutUri() {
		if (function_exists('wc_get_cart_url')) {
			return wc_get_cart_url();
		}

		return "/";
	}
	
	
	static function create($subscriptionTimeSec, $price, $pricingGroupIndex, $title='') {
		
		if (!static::isAvailable()) return;
		
		$post_title = static::createDownloadName($subscriptionTimeSec, $pricingGroupIndex);
		if($pricingGroupIndex == '0')
		{
			$post_title = $title.' ('.TimeHelper::seconds2period($subscriptionTimeSec).')';
		}

		$post_id = wp_insert_post( array(
			'post_title'	 => $post_title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => static::POST_TYPE,
		) );
		
		if ( $post_id ) {
			
			wp_set_object_terms( $post_id, 'simple', 'product_type' );
			update_post_meta( $post_id, '_visibility', 'visible' );
			update_post_meta( $post_id, '_stock_status', 'instock');
			update_post_meta( $post_id, 'total_sales', '0' );
			update_post_meta( $post_id, '_downloadable', 'no' );
			update_post_meta( $post_id, '_virtual', 'yes' );
			update_post_meta( $post_id, '_regular_price', '' );
			update_post_meta( $post_id, '_sale_price', '' );
			update_post_meta( $post_id, '_purchase_note', '' );
			update_post_meta( $post_id, '_featured', 'no' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
			update_post_meta( $post_id, '_sku', '' );
			update_post_meta( $post_id, '_product_attributes', array() );
			update_post_meta( $post_id, '_sale_price_dates_from', '' );
			update_post_meta( $post_id, '_sale_price_dates_to', '' );
			update_post_meta( $post_id, '_price', '' );
			update_post_meta( $post_id, '_sold_individually', '' );
			update_post_meta( $post_id, '_manage_stock', 'no' );
			update_post_meta( $post_id, '_backorders', 'no' );
			update_post_meta( $post_id, '_stock', '' );
			
			$product = static::getInstance($post_id);
			
			$product->setPrice($price);
			$product->setPricingGroupIndex($pricingGroupIndex);
			$product->setSubscriptionTime($subscriptionTimeSec);
			if ($categoryTermId = static::getPluginsCategoryId()) {
				wp_set_post_terms($product->getId(), array($categoryTermId), static::CATEGORY_TAXONOMY, $append = false);
			}
			
			return $product;
			
		}
		
	}
	
	
	static function synchronizeWithSettings() {
		
		$productsIdsAfterUpdate = array();
		$productsBeforeUpdate = array();
		$createdProductsIds = array();
		$productsMap = array();
		$products = static::getAll();
		//echo "<pre>"; print_r($products); echo "</pre>"; die;

		foreach ($products as $product) {
			$productsBeforeUpdate[$product->getId()] = $product;
			$productsMap[$product->getPricingGroupIndex()][$product->getSubscriptionTimeSec()] = $product;
		}
		
		$groups = Settings::getOption(Settings::OPTION_WOO_PRICING_GROUPS);
		//echo "<pre>"; print_r($groups); echo "</pre>"; die;

		if (is_array($groups)) foreach ($groups as $pricingGroupIndex => $group) {
			if (isset($group['prices']) AND is_array($group['prices'])) foreach ($group['prices'] as $price) {
				$timeSec = TimeHelper::period2seconds($price['number'] . $price['unit']);
				if (isset($productsMap[$pricingGroupIndex][$timeSec])) { // find the product with the same group and time
					$product = $productsMap[$pricingGroupIndex][$timeSec];
					$productsIdsAfterUpdate[] = $product->getId();
					if ($product->getPrice() != $price['price']) { // update price if different
						$product->setPrice($price['price']);
					}
				} else {
					// product not found, create new one
					$product = static::create($timeSec, $price['price'], $pricingGroupIndex);
					$productsIdsAfterUpdate[] = $createdProductsIds[] = $product->getId();
				}
			}
		}
		
		// Delete unused products
		$toDelete = array_diff(array_keys($productsBeforeUpdate), $productsIdsAfterUpdate);

		foreach ($toDelete as $id) {
			$cmppp_pricing_group = get_post_meta($id, 'cmppp_pricing_group', true);
			if (isset($productsBeforeUpdate[$id]) && $cmppp_pricing_group != '0') {
				//$productsBeforeUpdate[$id]->archive();
				wp_delete_post($id, true);
			}
		}

	}
	
	
	static function getByPricingGroupAndTime($pricingGroupIndex, $timeSec) {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT p.ID FROM $wpdb->posts p
			JOIN $wpdb->postmeta stime ON stime.post_id = p.ID AND stime.meta_key = %s
			JOIN $wpdb->postmeta pgroup ON pgroup.post_id = p.ID AND pgroup.meta_key = %s
			WHERE stime.meta_value = %s
					AND pgroup.meta_value = %s
					AND p.post_type = %s",
				static::META_SUBSCRIPTION_TIME_SEC,
				static::META_PRICING_GROUP,
				$timeSec,
				$pricingGroupIndex,
				static::POST_TYPE);
		if ($postId = $wpdb->get_var($sql)) {
			return static::getInstance($postId);
		}
	}
	
	
}
