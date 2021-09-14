<?php
/**
 * General functions for overall usefulness
 * @since 1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the restriction method
 * @since 1.0.0
 */
function wcmo_get_restriction_method() {
	$restriction_method = get_option( 'wcmo_restriction_method', false );
	return apply_filters( 'wcmo_restriction_method', $restriction_method );
}

/**
 * Get permitted roles
 * @since 1.0.0
 */
function wcmo_get_permitted_roles() {
	$user_roles = get_option( 'wcmo_user_roles', array() );
	return apply_filters( 'wcmo_permitted_user_roles', $user_roles );
}

/**
 * Get the URL of the page to redirect to after successfully logging in
 * @param $password	The password entered by the user
 * @since 1.0.0
 */
function wcmo_get_redirect_url( $password='' ) {

	$redirect_to = get_option( 'wcmo_redirect_to', 'stay' );

	if( $redirect_to == 'stay' ) {

		$url = get_permalink();

	} else {

		$redirect_page = get_option( 'wcmo_redirect_page' );

		// Get a redirect URL
		$url = get_permalink( $redirect_page );

		// If we're using the referrer
		if( $redirect_page == 'referrer' && isset( $_GET['wcmo_referrer'] ) ) {
			$url = get_site_url() . $_GET['wcmo_referrer'];
		}

	}

	return apply_filters( 'wcmo_redirect_url', $url, $password );

}

/**
 * Get the URL of the page to redirect to after trying to access restricted content
 * @since 1.0.0
 */
function wcmo_get_redirect_restricted_url() {

	$redirect_page = get_option( 'wcmo_redirect_restricted', false );

	if( ! $redirect_page ) {
		$url = get_site_url();
	} else {
		$url = get_permalink( $redirect_page );
	}

	return apply_filters( 'wcmo_redirect_restricted_url', $url );

}

/**
 * Get the URL of the page containing the password or log-in form
 * @since 1.0.0
 */
function wcmo_get_form_page() {
	$form_page = get_option( 'wcmo_display_form', 'page' );
	$url = false;
	if( $form_page == 'page' ) {
		$form_page = get_option( 'wcmo_form_page' );
		if( $form_page ) {
			$url = get_permalink( $form_page );
		} else {
			$url = false;
		}
	}
	return apply_filters( 'wcmo_get_form_page', $url );
}

/**
 * Check if we're on the form page
 * @since 1.0.0
 */
function wcmo_is_form_page() {
	$is_form_page = false;
	$form_page = get_option( 'wcmo_form_page' );
	$current_page = get_the_ID();
	if( $form_page == $current_page ) {
		$is_form_page = true;
	}
	return apply_filters( 'wcmo_is_form_page', $is_form_page );
}

/**
 * Get the restricted content type
 * @since 1.1.0
 */
function wcmo_get_restricted_content() {
	$restricted_content = get_option( 'wcmo_restricted_content', 'products' );
	return apply_filters( 'wcmo_restricted_content', $restricted_content );
}

/**
 * Get the array of restricted categories
 * @since 1.0.0
 */
function wcmo_get_restricted_categories() {
	$restricted_categories = get_option( 'wcmo_restricted_categories', array() );
	return $restricted_categories;
}

/**
 * Check if products are hidden from archive pages
 * @since 1.0.2
 */
function wcmo_hide_products_in_archives() {
	$hide_products = get_option( 'wcmo_hide_products', 'no' );
	return apply_filters( 'wcmo_hide_products_in_archives', $hide_products );
}

/**
 * Get global alternative excerpt / content
 * @since 1.6.0
 */
function wcmo_get_global_excerpt_text() {
	$excerpt = get_option( 'wcmo_excerpt', false );
	return apply_filters( 'wcmo_global_excerpt_text', $excerpt );
}

/**
 * Check if products are hidden from archive pages
 * @since 1.0.2
 */
function wcmo_get_add_to_cart_text() {
	$text = get_option( 'wcmo_add_to_cart', false );
	return apply_filters( 'wcmo_add_to_cart', $text );
}

/**
 * Check if we are allowed to view product pages
 * @since 1.9.6
 */
function wcmo_allow_view_products() {
	$view = get_option( 'wcmo_allow_view_products', 'no' );
	return apply_filters( 'wcmo_allow_view_products', $view );
}

/**
 * Check if we are allowed to view product pages
 * @since 1.9.6
 */
function wcmo_link_redirect() {
	$redirect = get_option( 'wcmo_link_redirect', 'no' );
	return apply_filters( 'wcmo_link_redirect', $redirect );
}

/**
 * Check if products are hidden from archive pages
 * @since 1.0.2
 */
function wcmo_get_hide_price() {
	$hide = get_option( 'wcmo_hide_price', 'no' );
	return apply_filters( 'wcmo_hide_price', $hide );
}

/**
 * Get the array of whitelisted widget names
 * @since 1.3.0
 */
function wcmo_get_enable_widget_whitelist() {
	$enable_widget_whitelist = get_option( 'wcmo_enable_widget_whitelist', 'no' );
	return apply_filters( 'wcmo_enable_widget_whitelist', $enable_widget_whitelist );
}

/**
 * Get the array of whitelisted widget names
 * @since 1.0.0
 */
function wcmo_get_widget_whitelist() {
	$widget_whitelist = get_option( 'wcmo_widget_whitelist', array() );
	$widget_whitelist = explode( "\n", str_replace( "\r", "", $widget_whitelist ) );
	return apply_filters( 'wcmo_widget_whitelist', $widget_whitelist );
}

/**
 * Get the array of excluded menu items
 * @since 1.0.0
 */
function wcmo_get_menu_exclusions() {
	$menu_exclusions = get_option( 'wcmo_menu_exclusions', '' );
	$menu_exclusions = explode( "\n", str_replace( "\r", "", $menu_exclusions ) );
	return apply_filters( 'wcmo_menu_exclusions', $menu_exclusions );
}

/**
 * Is user approval enabled?
 * @since 1.7.0
 */
function wcmo_get_user_approval() {
	$user_approval = get_option( 'wcmo_user_approval', 'no' );
	return apply_filters( 'wcmo_user_approval', $user_approval );
}

/**
 * Are multiple user roles enabled?
 * @since 1.7.0
 */
function wcmo_get_multiple_roles_approval() {
	$multiple_roles = get_option( 'wcmo_multiple_user_roles', 'no' );
	return apply_filters( 'wcmo_multiple_user_roles', $multiple_roles );
}

/**
 * Prevent pending users from logging in?
 * @since 1.7.0
 */
function wcmo_get_prevent_pending() {
	$prevent_pending = get_option( 'wcmo_prevent_pending', 'no' );
	return apply_filters( 'wcmo_prevent_pending', $prevent_pending );
}

/**
 * Prevent rejected users from logging in?
 * @since 1.7.0
 */
function wcmo_get_prevent_rejected() {
	$prevent_rejected = get_option( 'wcmo_prevent_rejected', 'no' );
	return apply_filters( 'wcmo_prevent_rejected', $prevent_rejected );
}

/**
 * Prevent rejected users from logging in?
 * @since 1.7.0
 */
function wcmo_get_default_user_roles() {
	$default_user_roles = get_option( 'wcmo_default_user_roles', array( 'customer' ) );
	return apply_filters( 'wcmo_default_user_roles', $default_user_roles );
}

/**
 * Have we disabled automatic log-in?
 * @since 1.7.0
 */
function wcmo_get_prevent_auto_login() {
	$prevent_auto_login = get_option( 'wcmo_prevent_auto_login', 'no' );
	return apply_filters( 'wcmo_prevent_auto_login', $prevent_auto_login );
}

/**
 * Enabled extra registration fields
 * @since 1.9.0
 */
function wcmo_get_enabled_registration_fields() {

	$enabled_fields = get_option( 'wcmo_registration_fields', array() );
	return apply_filters( 'wcmo_enabled_registration_fields', $enabled_fields );

}

/**
 * Get a list of upload fields used in the registration form
 * @since 1.10.0
 */
function wcmo_get_upload_registration_fields( $registration_fields, $enabled_fields ) {

	$fields = isset( $enabled_fields['fields'] ) ? $enabled_fields['fields'] : false;

	$upload_fields = array();
	if( $fields ) {
		foreach( $fields as $id=>$value ) {
			if( isset( $registration_fields[$id]['type'] ) && $registration_fields[$id]['type'] == 'upload' ) {
				$upload_fields[] = $id;
			}
		}
	}

	return $upload_fields;

}

/**
 * Which roless the user can select from the registration forms
 * @since 1.9.0
 */
function wcmo_get_enabled_registration_roles() {
	$registration_roles = get_option( 'wcmo_registration_roles', array() );
	return apply_filters( 'wcmo_enabled_registration_roles', $registration_roles );
}

/**
 * Check if the page we're loading is restricted
 * @since 1.0.0
 */
function wcmo_is_content_restricted() {

	$is_restricted = false;

	$restriction_method = wcmo_get_restriction_method();
	$restricted_content = wcmo_get_restricted_content();

	if( $restriction_method && $restriction_method != 'no-restriction' ) {

		// Check for any restrictions outside single pages

		if( $restricted_content == 'products' && is_product() ) {

			// Product pages are restricted
			$is_restricted = wcmo_get_restriction_status( $is_restricted, $restriction_method );

		} else if( $restricted_content == 'store' && ( is_woocommerce() || is_cart() ) ) {

			// All shop pages are restricted
			$is_restricted = wcmo_get_restriction_status( $is_restricted, $restriction_method );

		} else if( $restricted_content == 'site' && ( ! wcmo_is_form_page() ) ) {

			// Can only access the form page
			$is_restricted = wcmo_get_restriction_status( $is_restricted, $restriction_method );

		} else if( $restricted_content == 'category' && wcmo_is_restricted_category() ) {

			// Can only access the form page
			$is_restricted = true;

		}

	}

	if( is_single() ) {

		// Check for local page rules
		$post_id = get_the_ID();
		$post_type = get_post_type( $post_id );
		$restricted_products = wcmo_get_products_restricted_by_current_user_single();
		if( is_array( $restricted_products ) && in_array( $post_id, $restricted_products ) ) {
			$is_restricted = true;
		} else if( $post_type == 'post' && wcmo_is_post_restricted( $post_id ) ) {
			$is_restricted = true;
		}

	}

	return apply_filters( 'wcmo_is_content_restricted', $is_restricted, $restricted_content );

}

function wcmo_get_restriction_status( $is_restricted, $restriction_method ) {

	if( is_admin() ) {

		return false;

	}

	// All shop pages are restricted
	if( $restriction_method == 'user-role' ) {

		// Check if the user is restricted
		$is_permitted_user_role = wcmo_is_permitted_user_role();
		if( ! wcmo_is_permitted_user_role() ) {
			$is_restricted = true;
		}

	} else if( $restriction_method == 'log-in-status' ) {

		$is_restricted = ! is_user_logged_in();

	} else if( $restriction_method == 'password' ) {

		$is_restricted = ! WC()->session->get( 'wcmo_can_access_global' );

	}

	return $is_restricted;

}

/**
 * Check if the page we're loading is restricted and, if so, do a redirect
 * @since 1.0.0
 */
function wcmo_do_redirect() {

	if( is_admin() ) return;

	$can_access = wcmo_get_access_status();
	if( $can_access ) {
		// We're allowed in
		// return;
	}

	$is_restricted = wcmo_is_content_restricted();

	// If we've enabled view products then remove this restriction for product pages
	if( wcmo_allow_view_products() == 'yes' ) {
		$is_restricted = false;
	}

	if( $is_restricted ) {

		// Check we're not trying to redirect away from the log-in page
		$redirect_page = get_option( 'wcmo_redirect_restricted', false );

		if( ! $redirect_page ) {
			return;
		}

		if( get_the_ID() != $redirect_page ) {
			wcmo_redirect_to_restricted_url();
		}

	}

}
// Hook on wp so that WooCommerce conditionals are available
add_action( 'wp', 'wcmo_do_redirect' );

/**
 * Send the user to the specified URL
 */
function wcmo_redirect_to_restricted_url() {

	if( is_admin() ) {
		return;
	}

	$url = wcmo_get_redirect_restricted_url();

	if( $url ) {

		$referring_page = get_permalink();
		if( is_shop() ) {
			$referring_page = get_permalink( wc_get_page_id( 'shop' ) );
		} else if( is_product_category() || is_product_tag() ) {
			global $wp_query;
			$cat_slug = isset( $wp_query->query['product_cat'] ) ? $wp_query->query['product_cat'] : '';
			if( is_array( $cat_slug ) ) {
				$cat_slug = $cat_slug[0];
			}
			$category = get_term_by( 'slug', $cat_slug, 'product_cat' );
			$referring_page = get_category_link( $category );
		}

		// Add param so that we can redirect back to referring page
		$url = add_query_arg(
			array(
				'wcmo_referrer'	=> esc_url( str_replace( get_site_url(), '', $referring_page ) )
			),
			$url
		);

		// nocache_headers();
		wp_redirect( $url );
		die;

	}

}

function wcmo_woocommerce_login_redirect( $redirect, $user ) {

	if( wcmo_get_redirect_url() ) {

		$redirect = wcmo_get_redirect_url();

		if( isset( $_GET['wcmo_referrer'] ) ) {
			$redirect = get_site_url() . $_GET['wcmo_referrer'];
		}

	}

	return $redirect;

}
add_action( 'woocommerce_login_redirect', 'wcmo_woocommerce_login_redirect', 10, 2 );

/**
 * This will hide widgets - return false for any widgets not on our white list of widgets
 * @param array     $instance The current widget instance's settings.
 * @param WP_Widget $this     The current widget instance.
 * @param array     $args     An array of default widget arguments.
 */
function wcmo_filter_widget_display_callback( $settings, $widget, $args ) {
	// Check if the whitelist is enabled
	$enable_widget_whitelist = wcmo_get_enable_widget_whitelist();
	if( $enable_widget_whitelist != 'yes' ) {
		return $settings;
	}
	$whitelist = wcmo_get_widget_whitelist();
	$can_access = wcmo_get_access_status();
	if( ! $can_access && in_array( $widget->name, $whitelist ) ) {
		return false;
	}
	return $settings;
}
add_filter( 'widget_display_callback', 'wcmo_filter_widget_display_callback', 10, 3 );

function wcmo_get_transient_expiration() {
	return apply_filters( 'wcmo_transient_expiration', DAY_IN_SECONDS );
}

/**
 * Error log function
 * @since 1.1.0
 */
function wcmo_error_log( $error ) {
	if( defined( 'WCMO_DEBUG' ) ) {
		if( is_array( $error ) ) {
			error_log( print_r( $error, true ) );
		} else {
			error_log( $error );
		}
	}
}
define( 'WCMO_DEBUG', true );
