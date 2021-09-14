<?php
/**
 * Functions for a My Account tab
 * @since 1.9.14
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query customer's orders to find products with expiration date
 * @since 1.9.14
 */
function wcmo_get_users_expiration_dates( $user_id=false ) {

	if( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$user_roles = wcmo_get_current_user_roles();
	$expiring_roles = get_option( 'wcmo_expiring_roles', array() );
	$check_roles = array_intersect( $user_roles, $expiring_roles );

	if( empty( $check_roles ) ) {
		// There's no roles for this user that are set to expire
		return apply_filters(
			'wcfad_no_expiring_user_roles',
			sprintf(
				'<p>%s</p>',
				'You have no expiring memberships',
				'wcmo'
			)
		);
	}

	$expirations = array();

	// Find any expiration dates
	foreach( $check_roles as $role ) {
		$expirations[$role] = get_user_meta( $user_id, $role . '_expires', true );
	}

	if( $expirations ) {

		global $wp_roles;

		$return = sprintf(
			'<table class="wcmo-expirations"><thead><th>%s</th><th>%s</th></thead>',
			apply_filters( 'wcmo_expirations_table_role_heading', __( 'Role', 'wcmo' ) ),
			apply_filters( 'wcmo_expirations_table_expires_heading', __( 'Expires', 'wcmo' ) )
		);

		foreach( $expirations as $role=>$expires ) {

			$return .= sprintf(
				'<tr><td>%s</td><td>%s</td></tr>',
				translate_user_role( $wp_roles->roles[ $role ]['name'] ),
				wcmo_get_nice_expiry_date( $expires )
			);
		}

		$return .= '</table>';

	}

	return $return;

}

function wcmo_get_users_expiration_dates_shortcode( $atts ) {
	$return = wcmo_get_users_expiration_dates();
	return $return;
}
add_shortcode( 'wcmo_get_users_expiration_dates', 'wcmo_get_users_expiration_dates_shortcode' );

/**
 * Shortcode to display products restricted to this user
 */
// function wcmo_products_shortcode( $atts ) {
//
// 	$user_id = ! empty( $atts['user'] ) ? $atts['user'] : get_current_user_id();
// 	// Find all products restricted to this user
// 	return do_shortcode( '[products]' );
// }
// add_shortcode( 'wcmo_products', 'wcmo_products_shortcode' );
