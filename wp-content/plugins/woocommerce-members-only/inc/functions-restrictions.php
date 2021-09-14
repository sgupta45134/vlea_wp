<?php
/**
 * Functions to help with restricted content
 * @since 1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the user's access status
 * @since 1.0.0
 */
function wcmo_get_access_status() {

	if( is_admin() ) {
		return true;
	}

	$restriction_method = wcmo_get_restriction_method();

	if( $restriction_method == 'no-restriction' || ! $restriction_method ) {
		$can_access = true;
	} else if( $restriction_method == 'password' ) {
		$can_access = WC()->session->get( 'wcmo_can_access_global' );
	} else if( $restriction_method == 'log-in-status' ) {
		$can_access = is_user_logged_in();
	} else if( $restriction_method == 'user-role' ) {
		$can_access = wcmo_is_permitted_user_role();
	}
	/**
	 * @filtered Filtered by wcmo_get_category_access_status
	 */
	return $can_access;
}

/**
 * Get the user's roles
 * @since 1.0.0
 */
function wcmo_get_current_user_roles() {
  if( is_user_logged_in() ) {
    $user = wp_get_current_user();
    $roles = ( array ) $user->roles;
    return $roles;
  } else {
    return array();
  }
}

/**
 * Is the user role permitted?
 * @since 1.0.0
 */
function wcmo_is_permitted_user_role( $permitted=array() ) {

	if( ! $permitted ) {
		$permitted = wcmo_get_permitted_roles();
	}

	$roles = wcmo_get_current_user_roles();

	if( ! empty( $permitted ) && empty( $roles ) ) {
		return false;
	}

	return ! empty( array_intersect( $permitted, $roles ) );

}

/**
 * Has the user entered the correct password?
 * @param $category_id
 * @param $context Either 'specific' or 'global'
 * @since 1.1.0
 */
function wcmo_is_category_permitted_by_password( $category_id, $context ) {

	// Trying to get WC->session in admin will throw an error
	if( is_admin() ) return false;

	if( $context == 'specific' ) {
		// Check for locally restricted categories
		$permitted_categories = WC()->session->get( 'wcmo_access_cats' );
		// Check if a password has been entered for this category
		if( is_array( $permitted_categories ) && in_array( $category_id, $permitted_categories ) ) {
			return true;
		}
	} else {
		// Check for global rules
		$restricted_categories = wcmo_get_restricted_categories();
		// Is the category globally restricted?
		if( is_array( $restricted_categories ) && in_array( $category_id, $restricted_categories ) ) {
			// Has the user got global access?
			$has_global = WC()->session->get( 'wcmo_can_access_global' );
			return $has_global;
		}
	}
	// Fallback
	return false;
}

/**
 * Hide restricted products in archives
 * @since 1.0.2
 */
function wcmo_product_query( $q ) {

	if( wcmo_get_restricted_content() == 'site' ) {
		return;
	}

	// Check if the user has access to these categories
	$all_restricted_categories = wcmo_get_all_restricted_categories();

	// Check whether we are hiding protected products
	if( wcmo_hide_products_in_archives() == 'yes' ) {

		// Hide products in restricted categories
		$tax_query = (array) $q->get( 'tax_query' );
	  $tax_query[] = array(
	     'taxonomy' => 'product_cat',
	     'field' 		=> 'term_taxonomy_id',
	     'terms' 		=> $all_restricted_categories, // Don't display products in restricted categories on the shop page.
	     'operator' => 'NOT IN'
	  );
	  $q->set( 'tax_query', $tax_query );

	}

	// Find any products that are restricted by user ID, that are hidden from archive pages
	$restricted_products = wcmo_get_products_restricted_by_current_user_archive();

	if( is_array( $restricted_products ) ) {
		$q->set( 'post__not_in', array_values( $restricted_products ) );
		// Also remove restricted product IDs from post__in parameter, otherwise this will override post__not_in
		$post__in = array_diff( array_values( $q->get( 'post__in' ) ), array_values( $restricted_products ) );
		$q->set( 'post__in', $post__in );
	}

}
add_action( 'woocommerce_product_query', 'wcmo_product_query' );

/**
 * Parse the main WP_Query
 * This is to ensure that restricted products are filtered from WP_Query
 * @since 1.50
 */
function wcmo_parse_query( $q ) {

	// We need to be careful where we run this filter
	if( is_admin() || is_single() || ! $q->is_main_query() ) return;

	if( wcmo_get_restricted_content() == 'site' ) {
		return;
	}

	// Filter out any restricted categories
	$all_restricted_categories = wcmo_get_all_restricted_categories();

	// Check whether we are hiding protected products
	if( wcmo_hide_products_in_archives() == 'yes' ) {

		// Iterate through each restricted category
		if( is_array( $all_restricted_categories ) ) {

			$tax_query = array();

			foreach( $all_restricted_categories as $cat_id ) {

				$term = get_term( $cat_id );
				if( isset( $term->taxonomy ) ) {

					// Get the taxonomy slug
					$taxonomy = $term->taxonomy;

					$tax_query[] = array(
						'taxonomy' 	=> $taxonomy,
						'field' 		=> 'id',
						'terms' 		=> $cat_id,
						'operator'	=> 'NOT IN'
					);

				}

			}

		}

		$q->set( 'tax_query', $tax_query );

	}

	// Find any products that are restricted by user ID, that are hidden in archives
	$restricted_products = wcmo_get_products_restricted_by_current_user_archive();

	if( is_array( $restricted_products ) ) {

		$q->query_vars['post__not_in'] = array_values( $restricted_products );
		// Also remove restricted product IDs from post__in parameter, otherwise this will override post__not_in
		$post__in = array_diff( array_values( $q->query_vars['post__in'] ), array_values( $restricted_products ) );
		$q->query_vars['post__in'] = $post__in;

	}

}
add_action( 'parse_query', 'wcmo_parse_query' );

/**
 * Exclude products from shortcodes
 * @since 1.5.0
 */
function wcmo_shortcode_products_query( $query_args, $attributes, $type ) {

	$original_query_args = $query_args;

	// Check if the user has access to these categories
	$all_restricted_categories = wcmo_get_all_restricted_categories();

	// Check whether we are hiding protected products
	if( wcmo_hide_products_in_archives() == 'yes' ) {

		// Hide products in restricted categories
		// $tax_query = (array) $q->get( 'tax_query' );
	  $query_args['tax_query'][] = array(
	     'taxonomy' => 'product_cat',
	     'field' 		=> 'term_taxonomy_id',
	     'terms' 		=> $all_restricted_categories, // Don't display products in restricted categories on the shop page.
	     'operator' => 'NOT IN'
	  );

	}

	// Find any products that are restricted by user ID, that are hidden in archives
	$restricted_products = wcmo_get_products_restricted_by_current_user_archive();

	if( is_array( $restricted_products ) ) {

		$query_args['post__not_in'] = array_values( $restricted_products );
		// Also remove restricted product IDs from post__in parameter, otherwise this will override post__not_in
		if( isset( $query_args['post__in'] ) ) {

			if( empty( array_diff( array_values( $query_args['post__in'] ), array_values( $restricted_products ) ) ) ) {
				// Return an empty list of products if there are no unrestricted products
				$query_args['post__in'] = array( 0 );
			} else {
				$post__in = array_diff( array_values( $query_args['post__in'] ), array_values( $restricted_products ) );
				$query_args['post__in'] = $post__in;
			}

		} else {
			// $post__in = array_values( $restricted_products );
		}

	}

	return $query_args;

}
add_filter( 'woocommerce_shortcode_products_query', 'wcmo_shortcode_products_query', 10, 3 );

/**
 * Exclude products from shortcodes
 * @since 1.5.0
 */
function wcmo_shortcode_product_categories( $terms ) {

	// Check if the user has access to these categories
	$all_restricted_categories = wcmo_get_all_restricted_categories();

	// Remove any restricted categories from the query
	if( $terms && $all_restricted_categories ) {
		foreach( $terms as $key=>$term ) {
			if( in_array( $term->term_id, $all_restricted_categories ) ) {
				unset( $terms[$key] );
			}
		}
	}

	return $terms;

}
add_filter( 'woocommerce_product_categories', 'wcmo_shortcode_product_categories', 10 );

/**
 * Hide protected categories on the shop and archive pages
 */
function wcmo_hide_restricted_categories( $terms, $taxonomies, $args, $term_query ) {

	if( is_admin() ) {
		return $terms;
	}

	// Check whether we are hiding protected products
	if( wcmo_hide_products_in_archives() != 'yes' ) {
		return $terms;
	}

	$new_terms = $terms;

	global $post;
	$has_shortcode = false;
	if( isset( $post->post_content ) && has_shortcode( $post->post_content, 'product_categories' ) ) {
		$has_shortcode = true;
	}

	if ( is_shop() || is_product_category() || is_product_tag() || $has_shortcode ) {

		// Iterate through each term and remove any that the user doesn't have access to
    foreach( $terms as $key=>$term ) {
      if( isset( $term->taxonomy ) && $term->taxonomy == 'product_cat' && wcmo_is_restricted_category_by_id( $term->term_id ) ) {
        unset( $new_terms[$key] );
      }
    }

  }

  return $new_terms;

}
add_filter( 'get_terms', 'wcmo_hide_restricted_categories', 10, 4 );

/**
 * Filter the product wrapper class for restricted products
 * @since 1.0.2
 */
function wcmo_post_class( $classes, $class, $post_id ) {

	if( 'product' == get_post_type( $post_id ) ) {

		$is_restricted = wcmo_is_product_restricted( $post_id );
		// Check if this product is restricted
		if( wcmo_is_product_restricted( $post_id ) ) {
			$classes[] = 'wcmo-protected-item wcmo-protected-product';
		}

	}

	return $classes;

}
add_filter( 'post_class', 'wcmo_post_class', 10, 3 );

/**
 * Filter the product wrapper class for restricted products
 * @since 1.0.2
 */
function wcmo_cat_class( $classes, $class, $category ) {
	$term_id = isset( $category->term_id ) ? $category->term_id : '';
	$tax = isset( $category->taxonomy ) ? $category->taxonomy : '';
	if( $term_id && 'product_cat' == $tax ) {

		if( wcmo_is_restricted_category_by_id( $term_id ) ) {
			// Check if the user has access to this category
			$classes[] = 'wcmo-protected-item wcmo-protected-category';
		}

	}

	return $classes;
}
add_filter( 'product_cat_class', 'wcmo_cat_class', 10, 3 );

/**
 * Replace add to cart button for restricted products
 * @return HTML
 * @since 1.1.0
 */
function wcmo_add_to_cart_link( $button, $product ) {
	$product_id = $product->get_id();
	if( ! wcmo_is_product_restricted( $product_id ) ) {
		return $button;
	} else if( wcmo_get_add_to_cart_text() ) {
		return wcmo_get_add_to_cart_text();
	}
  return '';
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wcmo_add_to_cart_link', 10, 2 );

/**
 * Hide prices for restricted products
 * @return HTML
 * @since 1.9.4
 */
function wcmo_hide_price_restricted_product( $is_purchasable, $product ) {

	// Don't hide the price if it's not set
	if( wcmo_get_hide_price() != 'yes' && wcmo_allow_view_products() != 'yes' ) {
		return $is_purchasable;
	}

	$product_id = $product->get_id();
	$is_restricted = wcmo_is_product_restricted( $product_id );

	if( $is_restricted && wcmo_get_hide_price() == 'yes' ) {

		// Remove the price for restricted products
		$product->set_price( '' );

	}

	// If the product is restricted but we're allowed to view it, add a special button to redirect the user
	if( $is_restricted && wcmo_allow_view_products() == 'yes' ) {

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'woocommerce_single_product_summary', 'wcmo_replacement_add_to_cart_button', 30 );

	}

	return ! $is_restricted;

}
add_filter( 'woocommerce_is_purchasable', 'wcmo_hide_price_restricted_product', 10, 2 );

/**
 * Hide prices for restricted products in WooCommerce widgets
 * @return HTML
 * @since 1.9.10
 */
function wcmo_hide_price_html( $price, $product ) {

	// Don't hide the price if it's not set
	if( wcmo_get_hide_price() != 'yes' && wcmo_allow_view_products() != 'yes' ) {
		return $price;
	}

	$product_id = $product->get_id();
	$is_restricted = wcmo_is_product_restricted( $product_id );

	if( $is_restricted && wcmo_get_hide_price() == 'yes' ) {

		// Remove the price for restricted products
		$price = '';

	}

	return $price;

}
add_filter( 'woocommerce_get_price_html', 'wcmo_hide_price_html', 10, 2 );
