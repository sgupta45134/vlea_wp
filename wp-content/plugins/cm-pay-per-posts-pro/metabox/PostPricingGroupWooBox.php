<?php

namespace com\cminds\payperposts\metabox;

use com\cminds\payperposts\controller;
use com\cminds\payperposts\model;
use com\cminds\payperposts\helper\TimeHelper;

class PostPricingGroupWooBox extends MetaBox {

	const SLUG = 'cmppp-pricing-group-woo';
	const NAME = 'CM Pay Per Posts: WooCommerce Pricing';

	const FIELD_PRICING_GROUP = 'cmppp_woo_post_pricing_group';

	static function bootstrap() {
		add_action( 'plugins_loaded', function () {
			if ( model\WooCommerceProduct::isConfigured() ) {
				parent::bootstrap();
			}
		} );
	}

	static function getSupportedPostTypes() {
		return model\Subscription::getSupportedPostTypes();
	}

	static function render( $post ) {

		wp_enqueue_style( 'cmvlpay-backend' );
		wp_enqueue_script( 'cmvlpay-backend' );

		static::renderNonceField( $post );

		$fieldName = static::FIELD_PRICING_GROUP;

		$pricingGroupsList = array( '' => '' ) + model\PostWooPayment::getPricingGroupsList();

		$pricingGroupIndex = '';
		if ( $postObj = model\Post::getInstance( $post ) ) {
			$pricingGroupIndex = model\PostWooPayment::getInstance( $postObj )->getPricingGroupIndex();
		}

		$pricingSingleIndex = '';
		if ( $postObj = model\Post::getInstance( $post ) ) {
			$pricingSingleIndex = model\PostWooPayment::getInstance( $postObj )->getPricingSingleIndex();
		}

		echo controller\WooCommerceController::loadBackendView( 'post-metabox', compact( 'pricingGroupsList', 'fieldName', 'pricingGroupIndex', 'pricingSingleIndex' ) );

	}

	static function savePost( $post_id ) {
		if ( $post_id and $post = get_post( $post_id ) and $postObj = model\Post::getInstance( $post ) ) {

			// GROUPS
			$groupsIndexs   = $_POST[ static::FIELD_PRICING_GROUP ] ?? [];
			$groups_indexes = [];
			foreach ( $groupsIndexs as $group_index ) {
				if ( ! empty( $group_index ) ) {
					$groups_indexes[] = $group_index;
				}
			}
			model\PostWooPayment::getInstance( $postObj )->setPricingGroupIndex( $groups_indexes );


			// SINGLE
			$pricingSingleIndex = model\PostWooPayment::getInstance( $postObj )->getPricingSingleIndex();

			$cmppp_woo_pricing_single_enabled = filter_input( INPUT_POST, 'cmppp_woo_pricing_single_enabled' );
			$cmppp_woo_pricing_single_number  = filter_input( INPUT_POST, 'cmppp_woo_pricing_single_number' );
			if ( $cmppp_woo_pricing_single_number == '' ) {
				$cmppp_woo_pricing_single_number = 0;
			}
			$cmppp_woo_pricing_single_unit  = filter_input( INPUT_POST, 'cmppp_woo_pricing_single_unit' );
			$cmppp_woo_pricing_single_price = filter_input( INPUT_POST, 'cmppp_woo_pricing_single_price' );
			if ( $cmppp_woo_pricing_single_price == '' ) {
				$cmppp_woo_pricing_single_price = 0;
			}

			$singleIndexArr          = array();
			$singleIndexArr['allow'] = $cmppp_woo_pricing_single_enabled;

			$singleTimeSec = TimeHelper::period2seconds( $cmppp_woo_pricing_single_number . $cmppp_woo_pricing_single_unit );

			if ( $cmppp_woo_pricing_single_enabled == '1' ) {
				if ( isset( $pricingSingleIndex['product_id'] ) && $pricingSingleIndex['product_id'] != '' && $pricingSingleIndex['product_id'] > 0 && $product = get_post( $pricingSingleIndex['product_id'] ) ) {
					$product_id = $pricingSingleIndex['product_id'];

					$post_title = $post->post_title . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')';
					wp_update_post( array(
						'ID'         => $product_id,
						'post_title' => $post_title
					) );

					update_post_meta( $product_id, '_price', $cmppp_woo_pricing_single_price );
					update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );

				} else {
					$product    = model\WooCommerceProduct::create( $singleTimeSec, $cmppp_woo_pricing_single_price, '0', $post->post_title );
					$product_id = $product->getID();
				}
			} else {
				$product_id = 0;
			}

			$singleIndexArr['number']     = $cmppp_woo_pricing_single_number;
			$singleIndexArr['unit']       = $cmppp_woo_pricing_single_unit;
			$singleIndexArr['price']      = $cmppp_woo_pricing_single_price;
			$singleIndexArr['product_id'] = $product_id;
			model\PostWooPayment::getInstance( $postObj )->setPricingSingleIndex( $singleIndexArr );
		}
	}

}
