<?php
/**
 * Functions for products specific stuff
 * @since 1.2.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if individual product is restricted
 * @since 1.0.0
 */
function wcmo_is_product_restricted( $product_id ) {

	if( is_admin() ) return;

	$is_restricted = false;
	$restriction_method = wcmo_get_restriction_method();
	$restricted_content = wcmo_get_restricted_content();

	if( $restriction_method == 'no-restriction' ) {

		// No global restriction, so check for product-level restrictions
		$is_restricted = wcmo_is_single_product_restricted( $product_id );

	} else if( $restriction_method == 'password' ) {

		// Check for any category restrictions
		if( $restricted_content == 'category' ) {

			$category_id = wcmo_is_product_in_restricted_cat( $product_id );

			if( $category_id ) {

				// Checking local category rules
				$is_restricted = ! wcmo_is_category_permitted_by_password( $category_id, 'specific' );

			} else {

				// Check if the category is globally restricted
				$global_categories = wcmo_get_restricted_categories();
				if( is_array( $global_categories ) && in_array( $category_id, $global_categories ) ) {
					$is_restricted = ! WC()->session->get( 'wcmo_can_access_global' );
				}

			}

		} else {

			// Content type is not category
			$is_restricted = ! WC()->session->get( 'wcmo_can_access_global' );

		}

	} else if( $restriction_method == 'log-in-status' ) {

		if( $restricted_content == 'category' && wcmo_is_product_in_restricted_cat( $product_id ) ) {

			// Categories
			$is_restricted = ! is_user_logged_in();

		} else if( $restricted_content == 'products' ) {

			// Products
			$is_restricted = ! is_user_logged_in();

		} else {

			$is_restricted = false;

		}

	} else if( $restriction_method == 'user-role' ) {

		if( $restricted_content == 'category' && wcmo_is_product_in_restricted_cat( $product_id ) || $restricted_content != 'category' ) {

			// If it's in a restricted category, it must be restricted
			// $is_restricted = true;

			$is_permitted_user_role = wcmo_is_permitted_user_role();

			if( ! wcmo_is_permitted_user_role() ) {
				$is_restricted = true;
			}

		} else {

			// Check whether the user has a permitted user role
			// Check if the user is restricted
			// $is_permitted_user_role = wcmo_is_permitted_user_role();
			//
			// if( ! wcmo_is_permitted_user_role() ) {
			// 	$is_restricted = true;
			// }

			// $is_restricted = false;

			// Products
			// $is_restricted = ! is_user_logged_in();

		}

	}

	// Local product settings can override the global setting
	$is_restricted = wcmo_is_single_product_restricted( $product_id, $is_restricted );

	return $is_restricted;

}

/**
 * Check if specific product is restricted at local level
 * @param $product_id
 * @param $is_restricted If a value is passed here, the function acts like a filter
 * @since 1.3.0
 * @return Boolean
 */
function wcmo_is_single_product_restricted( $product_id, $is_restricted=false ) {

	if( 'product' !== get_post_type( $product_id ) ) {
		return;
	}

	$product = wc_get_product( $product_id );
	$product_restriction_type = $product->get_meta( 'wcmo_product_restriction_method' );

	if( ! $product_restriction_type || $product_restriction_type == 'no-restriction' ) {

		return $is_restricted;

	} else if( $product_restriction_type == 'user-id' ) {

		// Check to see if the user is permitted to view this product
		$user_ids = $product->get_meta( 'wcmo_product_user_ids' );

		if( $user_ids ) {
			$user_ids = explode( ',', $user_ids );
			$user_id = get_current_user_id();
			if( ! in_array( $user_id, $user_ids ) ) {
				// This user can't view, so redirect
				$is_restricted = true;
			}
		}

	} else if( $product_restriction_type == 'password' ) {

		// Check if we've entered a valid password
		$products = WC()->session->get( 'wcmo_access_products' );

		if( ! is_array( $products ) || ! in_array( $product_id, $products ) ) {
			$is_restricted = true;
		}

	} else if( $product_restriction_type == 'user-role' ) {

		// Check to see if the user is permitted to view this product
		$user_roles = $product->get_meta( 'wcmo_product_user_roles' );
		$hidden_roles = $product->get_meta( 'wcmo_hide_from_user_roles' );
		$current_user_roles = wcmo_get_current_user_roles();

		if( $user_roles ) {

			if( ! array_intersect( $user_roles, $current_user_roles ) ) {
				// This user doesn't have a permitted user role, so redirect
				$is_restricted = true;
			}

		}

		if( $hidden_roles ) {

			if( array_intersect( $hidden_roles, $current_user_roles ) ) {
				// This user doesn't have a permitted user role, so redirect
				$is_restricted = true;
			}

		}

	}

	return $is_restricted;

}

/**
 * Check for product-level restrictions when loading the product page
 */
function wcmo_check_product_restrictions() {

	if( is_admin() ) return;

	if( 'product' !== get_post_type() || ! is_single() ) {
		return;
	}

	global $post;

	$is_restricted = wcmo_is_single_product_restricted( $post->ID );

	if( $is_restricted && wcmo_allow_view_products() != 'yes' ) {
		wcmo_redirect_to_restricted_url();
	}

}
add_action( 'template_redirect', 'wcmo_check_product_restrictions' );

/**
 * Get a list of product IDs that have a user ID restriction
 * @since 1.4.2
 * @deprecated 1.5.0
 */
function wcmo_get_products_restricted_by_user() {
	_deprecated_function( __FUNCTION__, '1.5.0', 'wcmo_get_products_restricted_by_user_id' );
	wcmo_get_products_restricted_by_user_id();
}

/**
 * Get a list of product IDs that have a user ID restriction
 * @since 1.4.2
 */
function wcmo_get_products_restricted_by_user_id() {

	// First, check for a transient which stores restricted products and their permitted user IDs
	$products_restricted_by_user = get_transient( 'wcmo_products_restricted_by_user' );

	// If the transient doesn't exist, create it
	if( ! $products_restricted_by_user ) {

		// If the transient is empty because there are no products restricted by user, then don't keep running the query
		$has_restricted_products = wcmo_has_products_restricted_by_user();
		if( $has_restricted_products != 'no' ) {
			// There are restricted products, so regenerate the transient
			wcmo_update_excluded_products_transient();
		}

		$products_restricted_by_user = get_transient( 'wcmo_products_restricted_by_user' );

	}

	return $products_restricted_by_user;

}

/**
 * Get a list of product IDs that have a user role restriction, archive pages
 * @since 1.5.0
 */
function wcmo_get_products_restricted_by_user_role_archive() {

	// Get all products restricted by roles
	$roles_restricted_by_product = get_transient( 'wcmo_restricted_roles_by_product_archive', false );

	if( ! $roles_restricted_by_product ) {

		// If the transient is empty because there are no products restricted by user, then don't keep running the query
		$has_restricted_products = wcmo_has_products_restricted_by_user();
		if( $has_restricted_products != 'no' ) {
			// There are restricted products, so regenerate the transient
			wcmo_update_excluded_products_transient();
		}

		$roles_restricted_by_product = get_transient( 'wcmo_restricted_roles_by_product_archive', false );

	}
	// Get roles and their restricted products
	$products_restricted_by_user_role = get_transient( 'wcmo_products_restricted_by_user_role_archive', false );

	return array(
		$roles_restricted_by_product,
		$products_restricted_by_user_role
	);

}

/**
 * Get a list of product IDs that have a user role restriction, single pages
 * @since 1.6.0
 */
function wcmo_get_products_restricted_by_user_role_single() {

	// Get all products restricted by roles
	$roles_restricted_by_product = get_transient( 'wcmo_restricted_roles_by_product_single', false );
	// Get roles and their
	$products_restricted_by_user_role = get_transient( 'wcmo_products_restricted_by_user_role_single', false );

	return array(
		$roles_restricted_by_product,
		$products_restricted_by_user_role
	);

}

/**
 * Get a list of product IDs that are hidden to certain user roles on archive pages
 * @since 1.6.0
 */
function wcmo_get_products_hidden_by_user_role_archive() {

	// Get all products restricted by roles
	$roles_hidden_by_product = get_transient( 'wcmo_hidden_roles_by_product_archive', false );
	// Get roles and their
	$products_hidden_by_user_role = get_transient( 'wcmo_products_hidden_by_user_role_archive', false );

	return array(
		$roles_hidden_by_product,
		$products_hidden_by_user_role
	);

}

/**
 * Get a list of product IDs that are hidden to certain user roles on single pages
 * @since 1.6.0
 */
function wcmo_get_products_hidden_by_user_role_single() {

	// Get all products restricted by roles
	$roles_hidden_by_product = get_transient( 'wcmo_hidden_roles_by_product_single', false );
	// Get roles and their
	$products_hidden_by_user_role = get_transient( 'wcmo_products_hidden_by_user_role_single', false );

	return array(
		$roles_hidden_by_product,
		$products_hidden_by_user_role
	);

}

/**
 * Get a list of product IDs that have a user ID restriction, that the current user cannot access
 * @since 1.4.2
 */
function wcmo_get_products_restricted_by_current_user() {
	_deprecated_function( __FUNCTION, '1.6.0', 'wcmo_get_products_restricted_by_current_user_archive' );
	wcmo_get_products_restricted_by_current_user_archive();
}

/**
 * Get a list of product IDs that have a user ID restriction, that the current user cannot access
 * @since 1.4.2
 */
function wcmo_get_products_restricted_by_current_user_archive() {

	if( is_user_logged_in() ) {
		$current_user_id = get_current_user_id();
		$products_restricted_to_current_user_archive = get_transient( 'wcmo_products_restricted_to_current_user_archive_' . $current_user_id );
		if( $products_restricted_to_current_user_archive ) {
			return $products_restricted_to_current_user_archive;
		}
	} else {
		$products_restricted_to_current_user_archive = get_transient( 'wcmo_products_restricted_to_current_user_archive_0' );
		if( $products_restricted_to_current_user_archive ) {
			return $products_restricted_to_current_user_archive;
		}
	}

	// First, get all products restricted by ID
	$products_restricted_by_user_id = wcmo_get_products_restricted_by_user_id();

	// Get all products restricted by role
	$restricted_by_user_role_archive = wcmo_get_products_restricted_by_user_role_archive();

	// Get all products restricted by role
	$hidden_by_user_role_archive = wcmo_get_products_hidden_by_user_role_archive();

	// Save array of restricted product IDs here
	$products_restricted_to_current_user_archive = array();

	// The global setting for hiding in archive pages
	$hide_in_archive = wcmo_hide_products_in_archives();

	// Identify any products that are restricted by user ID
	if( $products_restricted_by_user_id ) {

		if( is_user_logged_in() ) {

			// If the user is logged in, create an array of products that are restricted just to them
			foreach( $products_restricted_by_user_id as $product_id=>$user_ids ) {

				// Check that the product is hidden
				$hide_product = get_post_meta( $product_id, 'wcmo_product_exclude_from_archives', true );
				if( ! in_array( $current_user_id, $user_ids ) && ( $hide_product == 'yes' || $hide_in_archive == 'yes' ) ) {

					$products_restricted_to_current_user_archive[] = $product_id;

				}

			}

		} else if( $hide_in_archive == 'yes' ) {

			// If the user is not logged in and products set to hidden globally, all products restricted by user ID are restricted
			$products_restricted_to_current_user_archive = array_keys( $products_restricted_by_user_id );

		} else {

			// If the user is not logged in but products not globally hidden, check for local product rules
			$products_restricted_to_current_user_archive = array();
			$check_products_restricted_by_user_id = array_keys( $products_restricted_by_user_id );

			// Check each product in the list to see if is locally hidden
			if( $check_products_restricted_by_user_id ) {
				foreach( $check_products_restricted_by_user_id as $restricted_product_id ) {

					// Check that the product is hidden
					$hide_product = get_post_meta( $restricted_product_id, 'wcmo_product_exclude_from_archives', true );

					if( $hide_product == 'yes' ) {

						$products_restricted_to_current_user_archive[] = $restricted_product_id;

					}

				}
			}

		}

	}

	// Identify any products that are restricted by user role
	if( $restricted_by_user_role_archive ) {

		// This returns an array
		$roles_restricted_by_product = isset( $restricted_by_user_role_archive[0] ) ? $restricted_by_user_role_archive[0] : array();
		$products_restricted_by_user_role = isset( $restricted_by_user_role_archive[1] ) ? $restricted_by_user_role_archive[1] : array();

		if( is_user_logged_in() ) {

			// Get the current user's roles
			$user_roles = wcmo_get_current_user_roles();

			// Check each restricted product ID
			// If it doesn't include the user role(s), then add it to the list of restricted products
			if( $products_restricted_by_user_role ) {
				foreach( $products_restricted_by_user_role as $product_id=>$restricted_roles ) {
					if( is_array( $restricted_roles ) && ! array_intersect( $user_roles, $restricted_roles ) ) {
						$products_restricted_to_current_user_archive[] = $product_id;
					}
				}
			}

		} else {

			// If the user is not logged in, all products restricted by user role are restricted

			// Remove any product IDs with empty values - these don't have any restrictions that would affect a non-logged-in user
			if( $products_restricted_by_user_role ) {
				$products_restricted_by_user_role = array_filter( $products_restricted_by_user_role );
			}

			// Combine with any pre-existing product IDs
			if( $products_restricted_by_user_role ) {
				$products_restricted_to_current_user_archive = array_merge( $products_restricted_to_current_user_archive, array_keys( $products_restricted_by_user_role ) );
			}

		}

	}

	// Identify any products that are restricted by user role
	if( $hidden_by_user_role_archive ) {

		// This returns an array
		$roles_hidden_by_product_archive = isset( $hidden_by_user_role_archive[0] ) ? $hidden_by_user_role_archive[0] : array();
		$products_hidden_by_user_role_archive = isset( $hidden_by_user_role_archive[1] ) ? $hidden_by_user_role_archive[1] : array();

		if( is_user_logged_in() ) {

			// Get the current user's roles
			$user_roles = wcmo_get_current_user_roles();

			// Check each restricted product ID
			// If it does include the user role(s), then add it to the list of restricted products
			if( $products_hidden_by_user_role_archive ) {
				foreach( $products_hidden_by_user_role_archive as $product_id=>$hidden_roles ) {
					if( array_intersect( $user_roles, $hidden_roles ) ) {
						$products_restricted_to_current_user_archive[] = $product_id;
					}
				}
			}

		} else {

			// Hide to non logged in users because they don't have a role at all
			if( $products_hidden_by_user_role_archive ) {
				$products_restricted_to_current_user_archive = array_merge( $products_restricted_to_current_user_archive, array_keys( $products_hidden_by_user_role_archive ) );
			}

		}

	}

	if( is_user_logged_in() ) {
		set_transient( 'wcmo_products_restricted_to_current_user_archive_' . $current_user_id, $products_restricted_to_current_user_archive, wcmo_get_transient_expiration() );
	} else {
		set_transient( 'wcmo_products_restricted_to_current_user_archive_0', $products_restricted_to_current_user_archive, wcmo_get_transient_expiration() );
	}

	return $products_restricted_to_current_user_archive;

}

/**
 * Get a list of product IDs that have a user ID restriction, that the current user cannot access
 * @since 1.6.0
 */
function wcmo_get_products_restricted_by_current_user_single() {

	// First, get all products restricted by ID
	$products_restricted_by_user_id = wcmo_get_products_restricted_by_user_id();
	// Get all products restricted by role
	$restricted_by_user_role_single = wcmo_get_products_restricted_by_user_role_single();
	// Get all products restricted by role
	$hidden_by_user_role_single = wcmo_get_products_hidden_by_user_role_single();

	// Save array of restricted product IDs here
	$products_restricted_to_current_user_single = array();

	// Identify any products that are restricted by user ID
	if( $products_restricted_by_user_id ) {

		if( is_user_logged_in() ) {

			// If the user is logged in, create an array of products that are restricted just to them
			$current_user_id = get_current_user_id();
			foreach( $products_restricted_by_user_id as $product_id=>$user_ids ) {

				if( ! in_array( $current_user_id, $user_ids ) ) {

					$products_restricted_to_current_user_single[] = $product_id;

				}

			}

		} else {

			// If the user is not logged in, all products restricted by user ID are restricted
			$products_restricted_to_current_user_single = array_merge( $products_restricted_to_current_user_single, array_keys( $products_restricted_by_user_id ) );

		}

	}

	// Identify any products that are restricted by user role
	if( $restricted_by_user_role_single ) {

		// This returns an array
		$roles_restricted_by_product = isset( $restricted_by_user_role_single[0] ) ? $restricted_by_user_role_single[0] : array();
		$products_restricted_by_user_role = isset( $restricted_by_user_role_single[1] ) ? $restricted_by_user_role_single[1] : array();

		if( is_user_logged_in() ) {

			// Get the current user's roles
			$user_roles = wcmo_get_current_user_roles();

			// Check each restricted product ID
			// If it doesn't include the user role(s), then add it to the list of restricted products
			if( $products_restricted_by_user_role ) {
				foreach( $products_restricted_by_user_role as $product_id=>$restricted_roles ) {
					if( is_array( $restricted_roles ) && ! array_intersect( $user_roles, $restricted_roles ) ) {
						$products_restricted_to_current_user_single[] = $product_id;
					}
				}
			}

		} else {

			// If the user is not logged in, all products restricted by user role are restricted

			// Remove any product IDs with empty values - these don't have any restrictions that would affect a non-logged-in user
			if( $products_restricted_by_user_role ) {
				$products_restricted_by_user_role = array_filter( $products_restricted_by_user_role );
			}

			// Combine with any pre-existing product IDs
			if( $products_restricted_to_current_user_single ) {
				$products_restricted_to_current_user_single = array_merge( $products_restricted_to_current_user_single, array_keys( $products_restricted_by_user_role ) );
			}

		}

	}

	// Identify any products that are hidden by user role
	if( $hidden_by_user_role_single ) {

		// This returns an array
		$roles_hidden_by_product_single = isset( $hidden_by_user_role_single[0] ) ? $hidden_by_user_role_single[0] : array();
		$products_hidden_by_user_role_single = isset( $hidden_by_user_role_single[1] ) ? $hidden_by_user_role_single[1] : array();

		if( is_user_logged_in() ) {

			// Get the current user's roles
			$user_roles = wcmo_get_current_user_roles();

			// Check each restricted product ID
			// If it does include the user role(s), then add it to the list of restricted products
			if( $products_hidden_by_user_role_single ) {
				foreach( $products_hidden_by_user_role_single as $product_id=>$hidden_roles ) {
					if( array_intersect( $user_roles, $hidden_roles ) ) {
						$products_restricted_to_current_user_single[] = $product_id;
					}
				}
			}

		} else {

			// Set this filter to true to ignore this rule for non-logged-in users
			if( ! apply_filters( 'wcmo_ignore_hidden_roles_non_logged_in', false ) ) {

				// The user isn't logged in, so doesn't have a user role, therefore these products are hidden
				if( $products_restricted_to_current_user_single ) {
					$products_restricted_to_current_user_single = array_merge( $products_restricted_to_current_user_single, array_keys( $products_hidden_by_user_role_single ) );
				}

			}

		}

	}

	return $products_restricted_to_current_user_single;

}

/**
 * Create an Add to Cart button that will redirect restricted users
 * @since 1.9.6
 */
function wcmo_replacement_add_to_cart_button() {

	if( wcmo_link_redirect() == 'yes' ) {

		$url = wcmo_get_redirect_restricted_url();
		$referring_page = get_permalink();
		$url = add_query_arg(
			array(
				'wcmo_referrer'	=> esc_url( str_replace( get_site_url(), '', $referring_page ) )
			),
			$url
		);

		printf(
			'<p><a href="%s" class="single_add_to_cart_button button alt">%s</a></p>',
			$url,
			wcmo_get_add_to_cart_text()
		);

	} else {

		printf(
			'<p>%s</p>',
			wcmo_get_add_to_cart_text()
		);

	}



}
