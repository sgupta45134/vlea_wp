<?php
/**
 * Functions for category specific stuff
 * @since 1.1.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if we're on a restricted category product page or archive
 * @since 1.0.0
 */
function wcmo_is_restricted_category() {

	$restricted_categories = wcmo_get_restricted_categories();
	$is_restricted = false;

	if( is_product_category( $restricted_categories ) ) {

		// Check if we have access to this category
		global $wp_query;
		$category_id = $wp_query->get_queried_object()->term_id;
		$is_restricted = wcmo_is_restricted_category_by_id( $category_id );

	} else if( is_product() ) {

		// If we're trying to access a product page, check if it's in a restricted category
		global $post;
		$is_restricted = wcmo_is_product_restricted( $post->ID );

	} else if( is_single() ) {

		global $post;
		$category = get_the_category();
		/**
		* Need to create array of all category IDs this post belongs to
		*/
		$category_id = $category[0]->cat_ID;
		$is_restricted = wcmo_is_restricted_category_by_id( $category_id, $restricted_categories );

	}

	return $is_restricted;

}

/**
 * Check if a specific category is restricted
 * @param $category_ids Mixed - could be integer or array of integers
 * @return Boolean
 * @since 1.1.0
 */
function wcmo_is_restricted_category_by_id( $category_ids, $categories=array() ) {

	$is_restricted = false;

	if( empty( $categories ) ) {

		// Get array of locally and globally restricted categories
		$global = wcmo_get_restricted_categories();
		$local = wcmo_get_locally_restricted_categories();
		$categories = array_unique( array_merge( $global, $local ) );

	}

	if( wcmo_get_restricted_content() != 'category' ) {

		// Bounce if restriction content type is not category
		return $is_restricted;

	}

	if( ! is_array( $category_ids ) ) {
		$category_ids = array( $category_ids );
	}

	// Iterate through each category ID
	foreach( $category_ids as $category_id ) {

		// Check for a local override
		$override = get_term_meta( $category_id, 'wcmo_override_global_restrictions', true );

		$restriction_method = wcmo_get_restriction_method();

		if( $restriction_method == 'log-in-status' ) {

			// Check globally restricted categories
			// $all_restricted_categories = wcmo_get_all_restricted_categories();
			// Check locally restricted categories

			// Log-in status
			if( in_array( $category_id, $categories ) ) {

				$is_restricted = ! is_user_logged_in();

			} else {

				$is_restricted = false;

			}

		} else if( $override == 'yes' ) {

			// Local rules apply
			$local_rules = wcmo_get_category_local_rules_by_id( $category_id );

			if( $restriction_method == 'user-role' ) {

				// User role
				$is_restricted = ! wcmo_is_permitted_user_role( $local_rules['user_roles'] );

			} else if( $restriction_method == 'password' ) {

				// Password
				$is_restricted = ! wcmo_is_category_permitted_by_password( $category_id, 'specific' );

			}

		} else if( in_array( $category_id, wcmo_get_restricted_categories() ) ) {

			// Check the global rules
			if( $restriction_method == 'user-role' ) {

				// Check the global rules
				$is_restricted = ! wcmo_is_permitted_user_role();

			} else if( $restriction_method == 'password' ) {

				// Password
				$is_restricted = ! wcmo_is_category_permitted_by_password( $category_id, 'global' );

			}

		}

		// At the end of each iteration, if $is_restricted equals true, then break -
		// Otherwise the restriction might get overwritten
		if( $is_restricted ) {
			break;
		}

	}

	return $is_restricted;

}

/**
 * Check if a product is in a restricted category
 * @return Mixed
 * @since 1.0.0
 */
function wcmo_is_product_in_restricted_cat( $product_id ) {
	$cats = get_the_terms( $product_id, 'product_cat' );
	if( $cats ) {
		foreach( $cats as $cat ) {
			if( wcmo_is_restricted_category_by_id( $cat->term_id ) ) {
				return $cat->term_id;
			}
		}
	}
	return false;
}

/**
 * Return any local category restriction rules
 * @since 1.1.0
 */
function wcmo_get_category_local_rules() {
	if( is_product_taxonomy() ) {
		global $wp_query;
		$current_category_id = $wp_query->get_queried_object()->term_id;
		$local_rules = wcmo_get_category_local_rules_by_id( $current_category_id );
		return $local_rules;
	} else if( is_product() ) {
		global $post;
		$terms = get_the_terms( $post->ID, 'product_cat' );
		if( $terms ) {
			foreach( $terms as $term ) {
				// This will return the first category with a local restriction
				$override = get_term_meta( $term->term_id, 'wcmo_override_global_restrictions', true );
				if( $override == 'yes' ) {
					// This category has a local restriction
					$local_rules = wcmo_get_category_local_rules_by_id( $term->term_id );
					return $local_rules;
				}
			}
		}
	}
	return false;
}

/**
 * Return any local restriction rules for a specific category
 * @since 1.1.0
 */
function wcmo_get_category_local_rules_by_id( $category_id ) {

	$override = get_term_meta( $category_id, 'wcmo_override_global_restrictions', true );

	if( $override != 'yes' ) {
		// This category doesn't have local rules
		return false;
	}

	$local_rules = array(
		'category_id' 				=> $category_id,
		'passwords' 					=> get_term_meta( $category_id, 'wcmo_passwords', true ),
		'user_roles' 					=> get_term_meta( $category_id, 'wcmo_user_roles', true ),
		'excerpt'							=> get_term_meta( $category_id, 'wcmo_excerpt', true )
	);

	return $local_rules;

}

/**
 * Return any locally restricted categories
 * @since 1.3.0
 */
function wcmo_get_locally_restricted_categories() {

	// Use a transient since 1.10.1
	$locally_restricted_categories = get_transient( 'wcmo_locally_restricted_categories' );

	if( ! is_array( $locally_restricted_categories ) ) {

		$locally_restricted_categories = wcmo_set_locally_restricted_categories();

	}

	return $locally_restricted_categories;

}

/**
 * Return all locally and globally restricted categories
 * @since 1.3.0
 */
function wcmo_get_all_restricted_categories() {

	$global = wcmo_get_restricted_categories();
	$local = wcmo_get_locally_restricted_categories();

	$categories = array_unique( array_merge( $global, $local ) );
	$restricted = array();

	// Remove any which are permitted
	if( $categories ) {
		foreach( $categories as $category_id ) {
			if( wcmo_is_restricted_category_by_id( $category_id, $categories ) ) {
				$restricted[] = $category_id;
			}
		}
	}

	return $restricted;

}
