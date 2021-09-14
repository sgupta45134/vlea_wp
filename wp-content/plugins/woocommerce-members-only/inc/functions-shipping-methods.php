<?php
/**
 * Functions to help with shipping methods
 * @since 1.8.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict and enable shipping methods by user role
 * @since 1.8.0
 */
function wcmo_available_shipping_methods( $rates ) {

	if( is_admin() ) {
		return $rates;
	}

	// Check for restricted shipping methods
	$restricted_methods = wcmo_get_restricted_shipping_methods();
	$permitted_methods = wcmo_get_permitted_shipping_methods();

	// Get current user's roles
	if( is_user_logged_in() ) {

		$user_id = get_current_user_id();
		$user = new WP_User( $user_id );
		$roles = $user->roles;

	} else {

		if( apply_filters( 'wcmo_ignore_shipping_for_non_logged_in', false ) ) {
			return $rates;
		}

		$roles = array();

	}

	// Check restrictions first
	if( $restricted_methods ) {

		foreach( $restricted_methods as $method_id=>$restricted_roles ) {

			foreach( $rates as $rate_key=>$rate ) {

				if( $rate->method_id === $method_id ) {

					// Check if the user has a restricted role or if the user is not logged in
					if( ( array_intersect( $roles, $restricted_roles ) || empty( $roles ) ) ) {
						unset( $rates[$rate_key] );
						break;
					}

				}

			}

		}

	}

	// Now check for exclusive roles
	if( $permitted_methods ) {

		foreach( $permitted_methods as $method_id=>$permitted_roles ) {

			foreach( $rates as $rate_key=>$rate ) {

				if( $rate->method_id === $method_id ) {

					// Check if the user has a restricted role or if the user is not logged in
					if( ( ! array_intersect( $roles, $permitted_roles ) || empty( $roles ) ) ) {
						unset( $rates[$rate_key] );
						break;
					}

				}

			}

		}

	}

	return $rates;

}
add_filter( 'woocommerce_package_rates', 'wcmo_available_shipping_methods', 100 );
