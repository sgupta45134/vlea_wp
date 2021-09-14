<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;
use com\cminds\payperposts\controller\WooCommerceController;

class PostWooPayment extends PaymentMethod implements IPaymentMethod {

	const PAYMENT_PLUGIN_NAME = 'CM Pay Per Post WooCommerce Payment';
	const POST_META_PRICING_GROUP_INDEX = 'cmppp_woo_pricing_group_index';
	const META_PRICING_GROUP_INDEX = 'cmppp_woo_pricing_group_index';
	const POST_META_PRICING_SINGLE_INDEX = 'cmppp_woo_pricing_single';
	const PLUGIN_PREFIX = 'Woo';

	public function __construct( $post = null ) {
		if ( ! is_null( $post ) && $post instanceof Post ) {
			$this->post = $post;
		}
	}

	public static function isPluginActive() {
		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	public static function getMetaPricingGroupIndexName() {
		return "cmppp_woo_pricing_group_index";
	}


	public static function getInstance( Post $post ) {
		return new static( $post );
	}


	public static function isAvailable() {
		return WooCommerceProduct::isAvailable();
	}

	public static function isConfigured() {
		return static::isAvailable();
	}

	public function getPricingSingleIndex() {
		return $this->post->getPostMeta( static::POST_META_PRICING_SINGLE_INDEX );
	}

	public function setPricingSingleIndex( $singleIndex ) {
		return $this->post->setPostMeta( static::POST_META_PRICING_SINGLE_INDEX, $singleIndex );
	}

	public static function getPricingGroups() {
		if ( Settings::getOption( Settings::OPTION_WOO_PRICING_GROUPS ) ) {
			return Settings::getOption( Settings::OPTION_WOO_PRICING_GROUPS );
		} else {
			return array();
		}
	}

	public function isPaid() {

		if ( ! self::isPluginActive() ) {
			return 0;
		}

		$groups = self::getPostPricingGroupsIndexes( $this->post->getId() );

		$single = $this->getPricingSingleIndex();
		if ( ! empty( $groups ) || ( isset( $single['allow'] ) && $single['allow'] == '1' && isset( $single['number'] ) && $single['number'] > 0 && isset( $single['price'] ) && $single['price'] > 0 ) ) {
			return 1;
		} else {
			return 0;
		}
	}


	public function getProductBySubscriptionTime( $pricintGroupIndex, $timeSec ) {
		return WooCommerceProduct::getByPricingGroupAndTime( $pricintGroupIndex, $timeSec );
	}


	public function initPayment( array $subscriptionPlan, $callbackUrl ) {
		$pricingGroupIndex = $subscriptionPlan['pricing_group_index'];
		$product           = $this->getProductBySubscriptionTime( $pricingGroupIndex, $subscriptionPlan['seconds'] );


		if ( ! is_null( $product ) && in_array( $product->getId(), array_column( WC()->cart->get_cart(), 'product_id' ) ) ) {
			return [
				'msg' => 'The subscription is already in your cart'
			];
		}

		if ( $product ) {
			$post_id = ( ! is_null( $this->post ) ) ? $this->post->getId() : 0;
			$request = array(
				'subscription'      => $subscriptionPlan,
				'pricingGroupIndex' => $pricingGroupIndex,
				'label'             => App::getPluginName() . ': ' . $this->getTransactionLabelByCost( $subscriptionPlan ),
				'price'             => $product->getPrice(),
				'postId'            => $post_id,
				'userId'            => get_current_user_id(),
				'initTime'          => time(),
				'callbackUrl'       => $callbackUrl,
				'callbackAction'    => WooCommerceController::CALLBACK_ACTION,
			);

			$cartkey = $product->addToCart( $request );
			if ( $post_id ) {
				add_post_meta( $post_id, 'cmppp_woo_payment_request_' . $cartkey, $request );
			}

			return WooCommerceProduct::getCheckoutUri();
		}

		return "/";
	}

	public function initSinglePayment( array $singlePlan, $callbackUrl ) {
		$product = WooCommerceProduct::getInstance( $singlePlan['product_id'] );

		if ( ! is_null( $product ) && in_array( $singlePlan['product_id'], array_column( WC()->cart->get_cart(), 'product_id' ) ) ) {
			return [
				'msg' => 'The subscription is already in your cart'
			];
		}

		if ( $product ) {
			$subscriptionPlan = array(
				'number'  => $singlePlan['number'],
				'unit'    => $singlePlan['unit'],
				'price'   => $singlePlan['price'],
				'period'  => $singlePlan['period'],
				'seconds' => $singlePlan['seconds']
			);

			$product->addToCart( array(
				'subscription'      => $subscriptionPlan,
				'pricingGroupIndex' => 0,
				'label'             => ( ! is_null( $this->post ) ) ? $this->post->getTitle() : '',
				'price'             => $product->getPrice(),
				'postId'            => ( ! is_null( $this->post ) ) ? $this->post->getId() : 0,
				'userId'            => get_current_user_id(),
				'initTime'          => time(),
				'callbackUrl'       => $callbackUrl,
				'callbackAction'    => WooCommerceController::CALLBACK_ACTION,
			) );

			return WooCommerceProduct::getCheckoutUri();
		}

		return "/";
	}


	public function getTransactionLabelByCost( array $cost ) {
		$title = ( ! is_null( $this->post ) ) ? $this->post->getTitle() : '';

		return sprintf( Labels::getLocalized( 'eddpay_transaction_label' ), $title, $cost['period'] );
	}

}
