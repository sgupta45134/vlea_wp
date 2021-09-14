<?php
/**
 * Functions to help with WooCommerce Subscriptions
 * @since 1.7.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if we need to assign a different user role from the default subscription role
 * @since 1.7.0
 */
function wcmo_subscription_payment_complete( $subscription ) {

	// Assign any new roles
	// This will override the default subscription role
	$order_id = method_exists( $subscription, 'get_parent_id' ) ? $subscription->get_parent_id() : $subscription->order->id;
	wcmo_assign_roles_after_purchase( $order_id );

}
add_action( 'woocommerce_subscription_payment_complete', 'wcmo_subscription_payment_complete' );
