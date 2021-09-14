<?php
/**
 * Functions to help with payment methods
 * @since 1.8.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict and enable payment gateways by user role
 * @since 1.8.0
 */
function wcmo_available_payment_gateways( $available_gateways ) {

	if( is_admin() ) {
		return $available_gateways;
	}

	// Check for restricted payment methods
	$restricted_methods = wcmo_get_restricted_payment_methods();
	$permitted_methods = wcmo_get_permitted_payment_methods();

	// Get current user's roles
	if( is_user_logged_in() ) {

		$user_id = get_current_user_id();
		$user = new WP_User( $user_id );
		$roles = $user->roles;

	} else {

		if( apply_filters( 'wcmo_ignore_payments_for_non_logged_in', false ) ) {
			return $available_gateways;
		}

		$roles = array();

	}

	// Check restrictions first
	if( $restricted_methods ) {

		foreach( $restricted_methods as $method_id=>$restricted_roles ) {

			if( ! is_user_logged_in() && apply_filters( 'wcmo_ignore_restricted_payments_for_non_logged_in', false ) ) {
				continue;
			}

			// Check if the user has a restricted role or if the user is not logged in
			if( isset( $available_gateways[$method_id] ) && ( array_intersect( $roles, $restricted_roles ) || empty( $roles ) ) ) {
			   unset( $available_gateways[$method_id] );
			}

		}

	}

	// Now check for exclusive roles
	if( $permitted_methods ) {

		foreach( $permitted_methods as $method_id=>$permitted_roles ) {

			if( ! is_user_logged_in() && apply_filters( 'wcmo_ignore_permitted_payments_for_non_logged_in', false ) ) {
				continue;
			}

			// Check if the user has a restricted role or if the user is not logged in
			if( isset( $available_gateways[$method_id] ) && ( ! array_intersect( $roles, $permitted_roles ) || empty( $roles ) ) ) {
			   unset( $available_gateways[$method_id] );
			}

		}

	}

 return $available_gateways;

}
add_filter( 'woocommerce_available_payment_gateways', 'wcmo_available_payment_gateways' );
