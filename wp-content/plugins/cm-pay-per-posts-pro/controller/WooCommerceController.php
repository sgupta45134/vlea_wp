<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\UserModel;

class WooCommerceController extends Controller {

	const CALLBACK_ACTION = 'cmppp_woo_payment_completed_callback';

	static $actions = array(
		'cmppp_after_save_settings',
		self::CALLBACK_ACTION                                => array( 'args' => 1 ),
		'woocommerce_order_status_processing'                => array(
			'method' => 'cm_woocommerce_order_status_processing',
			'args'   => 1
		),
		'woocommerce_order_status_on-hold'                   => array(
			'method' => 'cm_woocommerce_order_status_processing',
			'args'   => 1
		),
		'woocommerce_order_status_completed'                 => array(
			'method' => 'cm_woocommerce_order_status_completed',
			'args'   => 1
		),
		'woocommerce_order_details_before_order_table_items' => array( 'args' => 1 ),
		'woocommerce_new_order'                              => array( 'args' => 2 ),
		'woocommerce_new_order_item'                         => array( 'args' => 3 ),
		'before_delete_post'                => array(
			'method' => 'cmppp_remove_wait_until_admin_approve_meta',
			'args'   => 1
		),
	);

	static $filters = array(
		'cmppp_options_config',
		'woocommerce_get_cart_item_from_session' => array( 'args' => 3 ),
	);

	public static function cmppp_remove_wait_until_admin_approve_meta($order_id) {

		$post_type = get_post_type($order_id);
		if($post_type !== 'shop_order') {
			return;
		}

		global $wpdb;

		$meta_key = $wpdb->get_var("SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = '$order_id'");
		$user_id = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta WHERE meta_value = '$order_id'");
		
		delete_user_meta($user_id, $meta_key);

	}

	static function cmppp_options_config( $config ) {

		if ( model\WooCommerceProduct::isAvailable() ) {
			$config[ Settings::OPTION_WOO_PRICING_GROUPS ] = array(
				'type'         => Settings::TYPE_MP_PRICE_GROUPS,
				'category'     => 'pricing',
				'subcategory'  => 'woo',
				'title'        => 'WooCommerce pricing groups',
				'currency'     => model\WooCommerceProduct::getCurrency(),
				'currencyStep' => 0.01,
				'desc'         => 'Sets period one (1) if you choose lifetime subscription.',
			);
		}

		return $config;
	}

	static function cmppp_after_save_settings() {
		if ( model\WooCommerceProduct::isAvailable() ) {
			model\WooCommerceProduct::synchronizeWithSettings();
		}
	}

	static function getViewNameControllerPart() {
		return 'woo';
	}

	public static function getPaybox( $post ) {
		if ( model\PostWooPayment::getInstance( $post )->isPaid() ) {
			ob_start();
			static::displayPaybox( $post );

			return ob_get_clean();
		}

		return "";
	}

	static function displayPaybox( model\Post $post ) {
		if ( $post and model\PostWooPayment::isAvailable() and $instantPayment = model\PostWooPayment::getInstance( $post ) and $instantPayment->isPaid() ) {
			return PayboxController::displayPaybox( $post, $instantPayment );
		}

		return "";
	}

	static function woocommerce_get_cart_item_from_session( $cartItemData, $cartItemSessionData, $cartItemKey ) {
		if ( isset( $cartItemSessionData[ model\WooCommerceProduct::CART_KEY ] ) ) {
			$cartItemData[ model\WooCommerceProduct::CART_KEY ] = $cartItemSessionData[ model\WooCommerceProduct::CART_KEY ];
		}

		return $cartItemData;
	}

	static function woocommerce_new_order_item( $itemId, $values, $key ) {
		if ( isset( $values[ model\WooCommerceProduct::CART_KEY ] ) ) {
			try {
				wc_add_order_item_meta( $itemId, model\WooCommerceProduct::CART_KEY, $values[ model\WooCommerceProduct::CART_KEY ] );
			} catch ( \Exception $e ) {
				error_log( "\n\n[" . date( "Y-m-d H:i:s" ) . "]\n[File: " . basename( __FILE__ ) . ' -> Function: ' . __FUNCTION__ . ']: ' . "\n" .
				           '[Line]: ' . __LINE__ . "\n" .
				           '[$e->getMessage()]: ' . print_r( $e->getMessage(), true ), 3, 'cm_error.log' );
			}
		}
	}

	static function woocommerce_order_details_before_order_table_items( $order ) {
		$cmppp_back_post_url = self::getBackLinkHtmlToPaidPost( $order->get_id() );

		if ( $cmppp_back_post_url ) {
			echo "<div id='cmppp_backlink'>{$cmppp_back_post_url}</div>";
		} else {
			echo "<div id='cmppp_backlink' class='empty'></div>";
		}
	}

	static function cm_woocommerce_order_status_processing( $order_id ) {
		$order = wc_get_order( $order_id );
		$payment_method = $order->data['payment_method'];

		// Needed for check if the user buying Subscription, not some other product
		$isSubscriptionInOrder = false;

		// Don't change Order status if it's offline method. Wait until admin change order status by himself
		if ($payment_method == 'bacs' || $payment_method == 'cheque' || $payment_method == 'cod') {

			foreach ($order->get_items() as $item) {
				$details = $item->get_meta( model\WooCommerceProduct::CART_KEY );
				if($details) {
					// Show message "Waiting for admin approve" instead of paybox form
					update_user_meta($order->get_user_id(), 'order_on_hold_' . $details['postId'], $order_id);
				}
			}

			return;
		}

		// Change order status only if one of the items (products) is Subscription
		foreach ($order->get_items() as $item) {
			$details = $item->get_meta( model\WooCommerceProduct::CART_KEY );
			if($details) {
				$isSubscriptionInOrder = true;
			}
		}
		if ( ! empty( $order ) && $isSubscriptionInOrder) {
			$order->update_status( 'completed' );
		}
	}

	static function cm_woocommerce_order_status_completed( $order_id ) {
		$order  = wc_get_order( $order_id );
		$userId = $order->get_user_id();
		$items  = $order->get_items();

		if ( $userId == 0 ) {
			$paid_products = [];
		}

		foreach ( $items as $item ) {
			$details = $item->get_meta( model\WooCommerceProduct::CART_KEY );

			if ( isset( $details['userId'] ) && isset( $details['subscription'] ) ) {

				$requestedSubscription = $details['subscription'];
				$post_id               = isset( $details['postId'] ) ? $details['postId'] : 0;

				if ( $post_id ) {
					$post              = model\Post::getInstance( $post_id );
					$subscriptionModel = new model\Subscription( $post );
				} else {
					$subscriptionModel = new model\Subscription();
				}

				// Turn a guest buyer into a registered user and log him in
				if(Settings::getOption( Settings::OPTION_AUTO_REGISTER_AND_LOGIN_USER )) {
					$details = $item->get_meta( model\WooCommerceProduct::CART_KEY );
					$new_user_email = $order->get_billing_email();
					$user = UserModel::registerAndLoginUserWoo($new_user_email);
					if($user){
						$userId = $user->ID;
					}
				}

				try {
					if ( $userId == 0 ) {
						// guest's order
						// set data to cookie
						$seconds = $requestedSubscription['seconds'] ?? 0;
						if ( ! headers_sent() && $seconds > 0 ) {
							$product_id = $item->get_product_id();
							setcookie( 'WooCommerce_paid_product_id_' . $product_id, $product_id, time() + $seconds );
						}

					} else if ( $userId > 0 ) {

						if ( isset( $subscriptionModel ) ) {
							// remove "Waiting for admin approve" box
							delete_user_meta($userId, 'order_on_hold_' . $details['postId']);

							$userId = ( $details['userId'] != '' && $details['userId'] > 0 ) ? $details['userId'] : $userId;

							$subscriptionModel->addSubscription(
								$userId,
								$requestedSubscription['seconds'],
								$details['price'],
								model\PostWooPayment::PAYMENT_PLUGIN_NAME,
								$details['pricingGroupIndex']
							);

							model\Subscription::notifyPurchaseConfirmation(
								$userId,
								$requestedSubscription['seconds'],
								$details['price'],
								model\PostWooPayment::PAYMENT_PLUGIN_NAME,
								$post_id
							);

						}

					}

				} catch ( \Exception $e ) {
					error_log( "\n " . '$e->getMessage() - ' . print_r( $e->getMessage(), true ), 3, 'cm_errors.log' );
				}
			}
		}
	}

	static function woocommerce_new_order( $order_id, $order ) {
		$cmppp_back_post_id = Storage::get( 'cmppp_back_post_id', 0 );

		if ( ! $cmppp_back_post_id ) {
			$cmppp_back_post_id = Storage::getGuestData( 'cmppp_back_post_id', 0 );
		}

		if ( $cmppp_back_post_id ) {
			update_post_meta( $order_id, 'cmppp_back_post_id', $cmppp_back_post_id );
			Storage::set( 'cmppp_back_post_id', 0 );

			if ( empty( $order->items ) ) {

				foreach ( $order->items as $item ) {
					$product_id              = $item->get_product_id();
					$storage_key             = 'Woo_paid_product_' . $product_id;
					$group_index             = get_post_meta( $product_id, 'cmppp_pricing_group', true );
					$storage_key_group_index = 'Woo_paid_group_' . $group_index;

					if ( ! empty( Storage::get( $storage_key, '' ) ) ) {
						continue;
					}

					$seconds = get_post_meta( $product_id, 'cmppp_subscription_time_sec', true );

					if ( ! empty( $seconds ) && $seconds > 0 ) {
						Storage::set( $storage_key, $product_id, $seconds );

						if ( $group_index ) {
							Storage::set( $storage_key_group_index, strtotime( "+$seconds sec" ), $seconds );
						}
					}
				}

			}
		}
	}

	static function cmppp_woo_payment_completed_callback( $details ) {
		/*
		if (isset($details['userId']) AND isset($details['subscription']) AND isset($details['postId'])) {
			$requestedSubscription = $details['subscription'];
			$post = model\Post::getInstance($details['postId']);
			$subscriptionModel = new model\Subscription($post);
			try {
				$subscriptionModel->addSubscription(
					$details['userId'],
					$requestedSubscription['seconds'],
					$details['price'],
					model\PostWooPayment::PAYMENT_PLUGIN_NAME,
					$details['pricingGroupIndex']
				);
			} catch (Exception $e) {

			}
		}
		*/
	}

}
