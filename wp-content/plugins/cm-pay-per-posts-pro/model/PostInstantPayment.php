<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;
use com\cminds\payperposts\controller\EDDController;

// for EDD plugin
class PostInstantPayment extends PaymentMethod implements IPaymentMethod {

	const PAYMENT_PLUGIN_NAME = 'CM Instant EDD Payments';
	const META_EDD_PRICING_GROUP_INDEX = 'cmpppedd_pricing_group';
	const META_PRICING_GROUP_INDEX = 'cmpppedd_pricing_group';
	const META_EDD_PRICING_SINGLE_INDEX = 'cmpppedd_pricing_single';
	const POST_META_PRICING_SINGLE_INDEX = 'cmpppedd_pricing_single';
	const META_TRANSACTIONS_PREFIX = 'cmpppedd_transaction_';
	const PLUGIN_PREFIX = 'EDD';

	public function __construct( $post = null ) {
		if ( ! is_null( $post ) && $post instanceof Post ) {
			$this->post = $post;
		}
	}

	public static function isPluginActive() {
		return is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );
	}

	public static function getMetaPricingGroupIndexName() {
		return "cmpppedd_pricing_group";
	}

	public static function getInstance( Post $post ) {
		return new static( $post );
	}

	public function isPaid() {

		if ( ! self::isPluginActive() ) {
			return 0;
		}

		$all_prices = $this->getPostsPrices();

		$category_downloads = $all_prices['category_downloads'];
		$groups             = $all_prices['groups'];
		$single             = $all_prices['single'];

		if ( ! empty( $category_downloads ) or ! empty( $groups ) or ( isset( $single['allow'] ) and $single['allow'] == '1' and isset( $single['number'] ) and $single['number'] > 0 and isset( $single['price'] ) and $single['price'] > 0 ) ) {
			return 1;
		} else {
			return 0;
		}
	}

	public function getPostsPrices() {
		global $wpdb;

		$groups             = [];
		$category_downloads = [];
		$single             = [];

		if ( ! is_null( $this->post ) ) {
			$post_id = $this->post->getId();

			// GROUPS
			$groups = self::getPostPricingGroupsIndexes( $post_id );

			// CATEGORIES if enabled
			$category_downloads = [];
			if ( Settings::getOption( Settings::OPTION_ENABLE_CATEGORIES_PRICES ) ) {
				// downloads for categories
				// get all downloads for categories
				// check if current post in found categories
				$q = "SELECT p.ID as download_id, p.post_title,  pm.meta_value as category_id
			  FROM {$wpdb->prefix}posts as p
			  LEFT JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
			  LEFT JOIN {$wpdb->prefix}term_relationships as tr ON tr.term_taxonomy_id=pm.meta_value
			  WHERE p.post_type='download'
			  AND pm.meta_key='cmppp_category_id'
			  AND tr.object_id={$this->post->getId()}";

				$category_downloads = $wpdb->get_results( $q, ARRAY_A );
			}

			// SINGLE
			$single = $this->getPricingSingleIndex();
		}

		return [
			'groups'             => $groups,
			'single'             => $single,
			'category_downloads' => $category_downloads
		];
	}

	public static function getProductIdByGroupIndex( $group_index ) {
		global $wpdb;
		$query = "SELECT post_id FROM {$wpdb->prefix}postmeta 
				WHERE meta_key='cmppp_pricing_group' 
				AND meta_value='{$group_index}'";

		$res = $wpdb->get_results( $query, ARRAY_A );

		$post_ids = [];
		if ( ! empty( $res ) ) {
			foreach ( $res as $r ) {
				$post_ids[] = $r['post_id'];
			}

			return $post_ids;
		}

		return false;
	}

	public function setPricingSingleIndex( $singleIndex ) {
		update_post_meta( $this->post->getId(), self::META_EDD_PRICING_SINGLE_INDEX, $singleIndex );
	}

	public function getPricingSingleIndex() {
		return get_post_meta( $this->post->getId(), self::META_EDD_PRICING_SINGLE_INDEX, $single = true );
	}

	public function initPayment( array $subscriptionPlan, $callbackUrl ) {
		global $wpdb;

		$request = [];

		if ( isset( $subscriptionPlan['download_id'] ) && ! empty( $subscriptionPlan['download_id'] ) ) {

			$postId = $subscriptionPlan['download_id'];

		} else {

			$pricingGroupIndex = $subscriptionPlan['pricing_group_index'];

			$subscription = array(
				'userId'            => get_current_user_id(),
				'subscriptionPlan'  => $subscriptionPlan,
				'initTime'          => time(),
				'postId'            => ( ! is_null( $this->post ) ? $this->post->getId() : 0 ),
				'pricingGroupIndex' => $pricingGroupIndex,
			);
			$request      = array(
				'userId'         => get_current_user_id(),
				'price'          => $subscriptionPlan['price'],
				'subscription'   => $subscription,
				'label'          => App::getPluginName() . ': ' . $this->getTransactionLabelByCost( $subscriptionPlan ),
				'callbackAction' => EDDController::EDDPAY_CALLBACK_ACTION,
				'callbackUrl'    => $callbackUrl,
			);

			$sql    = $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p
			JOIN $wpdb->postmeta stime ON stime.post_id = p.ID AND stime.meta_key = %s
			JOIN $wpdb->postmeta pgroup ON pgroup.post_id = p.ID AND pgroup.meta_key = %s
			WHERE stime.meta_value = %s
					AND pgroup.meta_value = %s
					AND p.post_type = %s",
				'cmppp_subscription_time_sec',
				'cmppp_pricing_group',
				$subscriptionPlan['seconds'],
				$pricingGroupIndex,
				'download' );
			$postId = $wpdb->get_var( $sql );
		}


		if ( in_array( $postId, array_column( edd_get_cart_contents(), 'id' ) ) ) {
			return [
				'msg' => 'The subscription is already in your cart'
			];
		}

		$response = apply_filters( 'cm_edd_payment_init_transaction', false, $request );

		if ( $response and is_array( $response ) and ! empty( $response['success'] ) and ! empty( $response['redirectionUrl'] ) ) {
			$this->registerTransaction( $subscription, $request, $response );
			if ( $postId ) {
				$res_url = $response['redirectionUrl'] . '&edd_action=add_to_cart&download_id=' . $postId;
			} else {
				$res_url = $response['redirectionUrl'];
			}

			return $res_url;

		} else {

			if ( function_exists( 'edd_get_checkout_uri' ) ) {
				$res_url = edd_get_checkout_uri() . '?edd_action=add_to_cart&download_id=' . $postId;
			} else {
				$res_url = '/checkout/?edd_action=add_to_cart&download_id=' . $postId;
			}

			return $res_url;
		}
	}

	public function initSinglePayment( array $singlePlan, $callbackUrl ) {
		global $wpdb;
		$subscriptionPlan  = array(
			'number'  => $singlePlan['number'],
			'unit'    => $singlePlan['unit'],
			'price'   => $singlePlan['price'],
			'period'  => $singlePlan['period'],
			'seconds' => $singlePlan['seconds']
		);
		$pricingGroupIndex = isset( $singlePlan['pricing_group_index'] ) ? $singlePlan['pricing_group_index'] : 0;
		$subscription      = array(
			'userId'            => get_current_user_id(),
			'subscriptionPlan'  => $subscriptionPlan,
			'initTime'          => time(),
			'postId'            => $this->post->getId(),
			'pricingGroupIndex' => $pricingGroupIndex,
		);
		$request           = array(
			'userId'         => get_current_user_id(),
			'price'          => $subscriptionPlan['price'],
			'subscription'   => $subscription,
			'label'          => App::getPluginName() . ': ' . $this->getTransactionLabelByCost( $subscriptionPlan ),
			'callbackAction' => EDDController::EDDPAY_CALLBACK_ACTION,
			'callbackUrl'    => $callbackUrl,
		);

		$postId = $singlePlan['product_id'];

		if ( in_array( $postId, array_column( edd_get_cart_contents(), 'id' ) ) ) {
			return [
				'msg' => 'The subscription is already in your cart.'
			];
		}

		$response = apply_filters( 'cm_edd_payment_init_transaction', false, $request );

		if ( $response and
		     is_array( $response ) and
		     ! empty( $response['success'] ) and
		     ! empty( $response['redirectionUrl'] ) ) {

			$this->registerTransaction( $subscription, $request, $response );

			if ( $postId ) {
				$res_url = $response['redirectionUrl'] . '&edd_action=add_to_cart&download_id=' . $postId;
			} else {
				$res_url = $response['redirectionUrl'];
			}

			return $res_url;

		} else {

			if ( function_exists( 'edd_get_checkout_uri' ) ) {
				$res_url = edd_get_checkout_uri() . '?edd_action=add_to_cart&download_id=' . $postId;
			} else {
				$res_url = '/checkout/?edd_action=add_to_cart&download_id=' . $postId;
			}

			return $res_url;
		}
	}

	protected function registerTransaction( $subscription, $request, $response ) {
		if ( ! is_null( $this->post ) ) {
			add_post_meta(
				$this->post->getId(),
				self::META_TRANSACTIONS_PREFIX . $response['transactionId'],
				compact( 'request', 'response', 'subscription' ),
				$unique = false
			);
		}
	}

	public static function getTransaction( $transactionId ) {
		global $wpdb;
		$meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", self::META_TRANSACTIONS_PREFIX . $transactionId ), ARRAY_A );
		if ( $meta and $post = Post::getInstance( $meta['post_id'] ) ) {
			$transaction = unserialize( $meta['meta_value'] );

			return compact( 'meta', 'post', 'transaction' );
		}

		return [];
	}

	public function getTransactionLabelByCost( array $cost ) {
		$title = ( ! is_null( $this->post ) ) ? $this->post->getTitle() : '';

		return sprintf( Labels::getLocalized( 'eddpay_transaction_label' ), $title, $cost['period'] );
	}

	public static function isAvailable() {
		return apply_filters( 'cm_pppedd_available', false ) && function_exists( '\\EDD' );
	}

	public static function isConfigured() {
		return static::isAvailable();
	}

	public static function getPricingGroups() {
		if ( Settings::getOption( Settings::OPTION_EDD_PRICING_GROUPS ) ) {
			return Settings::getOption( Settings::OPTION_EDD_PRICING_GROUPS );
		} else {
			return array();
		}
	}
}
