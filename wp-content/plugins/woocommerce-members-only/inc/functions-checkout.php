<?php
/**
 * Functions for the checkout
 * @since 1.4.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register user roles when the customer checks out
 * Also works if a new account is created at checkout
 * Also fired by wcmo_subscription_payment_complete
 */
function wcmo_assign_roles_after_purchase( $order_id ) {

	// Check the products in the order to see what roles need to be assigned
	$order = wc_get_order( $order_id );
	$order_items = $order->get_items( 'line_item' );

	foreach( $order_items as $order_item_id=>$order_item_values ) {

		$product_id = $order_item_values->get_product_id();

		if( ! empty( $order_item_values->get_variation_id() ) ) {
			$product_id = $order_item_values->get_variation_id();
		}

		$assign_roles = get_post_meta( $product_id, 'wcmo_product_assign_roles', true );

		if( $assign_roles ) {

			$customer_id = $order->get_customer_id();
			wcmo_update_users_role( $customer_id, $assign_roles );

			// Assign any expiration date
			$expires_after_value = get_post_meta( $product_id, 'wcmo_product_expires_after_value', true );
			$expires_after_period = get_post_meta( $product_id, 'wcmo_product_expires_after_period', true );
			if( $expires_after_value && $expires_after_period ) {
				wcmo_set_role_expiration_date( $customer_id, $expires_after_value, $expires_after_period, $assign_roles, $product_id );
			}

		}

	}

	do_action( 'wcmo_after_assign_roles_after_purchase', $customer_id, $order );

}
$hook = wcmo_get_assign_roles_order_status();
add_action( $hook, 'wcmo_assign_roles_after_purchase', 10 );

/**
 * Define when roles that are assigned on purchasing a product are actually assigned in the order process
 * @since 1.9.3
 */
function wcmo_get_assign_roles_order_status() {

	$status = get_option( 'wcmo_assign_roles_order_status', 'woocommerce_order_status_processing' );
	return $status;

}
