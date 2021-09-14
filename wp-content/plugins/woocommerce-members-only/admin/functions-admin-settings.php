<?php
/**
 * Settings functions for the admin
 * @package WCMO
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a list of different ways to restrict content
 */
function wcmo_get_restriction_methods() {
	$methods = array(
		'no-restriction'	=> __( 'No Restriction', 'wcmo' ),
		'log-in-status'		=> __( 'Log In Status', 'wcmo' ),
		'password'				=> __( 'Password', 'wcmo' ),
		'user-role'				=> __( 'User Role', 'wcmo' )
	);
	return apply_filters( 'wcmo_restriction_methods', $methods );
}

/**
 * Get a list of user roles
 */
function wcmo_get_user_roles() {
	global $wp_roles;
	$roles = $wp_roles->role_names;
	$options = array();
	if( $roles ) {
		foreach( $roles as $id=>$role ) {
			$options[$id] = $role;
		}
	}
	return apply_filters( 'wcmo_user_roles', $options );
}

/**
 * Get a list of user roles that can be assigned to new users
 * @since 1.9.3
 */
function wcmo_get_assignable_user_roles() {
	$roles = wcmo_get_user_roles();
	unset( $roles['administrator'] );
	return $roles;
}

/**
 * Get a list of different content types to restrict
 */
function wcmo_get_restricted_content_types() {
	$content = array(
		'products'		=> __( 'All Product Pages', 'wcmo' ),
		'store'				=> __( 'All WooCommerce Pages', 'wcmo' ),
		'site'				=> __( 'Entire Site', 'wcmo' ),
		'category'		=> __( 'Specified Categories', 'wcmo' ),
		// 'pages'				=> __( 'Specified Posts and Pages', 'wcmo' )
	);
	return apply_filters( 'wcmo_restricted_content_types', $content );
}

/**
 * Get a list of different content types to restrict
 */
function wcmo_get_all_pages() {
	$content = array(
		'products'		=> __( 'All Product Pages', 'wcmo' ),
		'store'				=> __( 'All WooCommerce Pages', 'wcmo' ),
		'site'				=> __( 'Entire Site', 'wcmo' ),
		'category'		=> __( 'Specified Categories', 'wcmo' ),
		// 'pages'				=> __( 'Specified Posts and Pages', 'wcmo' )
	);
	$pages = get_pages(
		array(
			'sort_column'  => 'menu_order',
			'sort_order'   => 'ASC',
			'hierarchical' => 0,
		)
	);
	$options = array();
	foreach( $pages as $page ) {
		$options[$page->ID] = ! empty( $page->post_title ) ? $page->post_title : '#' . $page->ID;
	}

	// Add the option to redirect back to referring page
	$options['referrer'] = __( 'Referring page', 'wcmo' );
	return apply_filters( 'wcmo_all_pages', $options );
}

/**
 * Get a redirect options
 */
function wcmo_get_redirect_options() {
	$options = array(
		'new'		=> __( 'Redirect', 'wcmo' ),
		'stay'	=> __( 'Stay on Page', 'wcmo' )
	);
	return apply_filters( 'wcmo_redirect_options', $options );
}

/**
 * Get a list of product categories
 * @deprecated 1.6.0
 */
function wcmo_get_product_categories() {
	_deprecated_function( __FUNCTION__, '1.6.0', 'wcmo_get_all_taxonomy_terms' );
	$categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
	$options = array();
	if( $categories ) {
		foreach( $categories as $category ) {
			$options[$category->term_id] = $category->name;
		}
	}
	return apply_filters( 'wcmo_product_categories', $options );
}

/**
 * Get a list of all taxonomies
 * @since 1.6.0
 */
function wcmo_get_all_taxonomy_terms() {
	// Decide what categories/taxnomies we're going to allow
	$taxonomies = apply_filters( 'wcmo_taxonomies', array( 'category', 'product_cat' ) );
	$taxonomy = get_taxonomy( 'category' );
	$all_terms = array();
	$args = array( 'hide_empty' => 0 );
	$terms = get_terms( $taxonomies, $args );
	if( $terms ) {
		foreach( $terms as $term ) {
			$tax_object = get_taxonomy( $term->taxonomy );
			$object_types = $tax_object->object_type;
			$post_type = array();
			foreach( $object_types as $object_type ) {
				// Add the post type name to the term label to avoid any confusion
				$post_type_object = get_post_type_object( $object_type );
				$post_type[] = $post_type_object->labels->name;
			}
			$all_terms[$term->term_id] = $term->name . ' (' . join( ', ', $post_type ). ')';
		}
	}
	return apply_filters( 'wcmo_all_taxonomy_terms', $all_terms );
}

/**
 * Add a column to the product list
 */
function wcmo_add_product_columns( $columns ) {
   //add column
   $columns['restriction_method'] = __( 'Restriction', 'wcmo' );
   return $columns;
}
add_filter( 'manage_edit-product_columns', 'wcmo_add_product_columns' );

function wcmo_product_column_offercode( $column, $post_id ) {
    if ( $column == 'restriction_method' ) {
      echo get_post_meta( $post_id, 'wcmo_product_restriction_method', true );
    }
}
add_action( 'manage_product_posts_custom_column', 'wcmo_product_column_offercode', 10, 2 );

/**
 * The list of expiration periods
 * @since 1.8.0
 */
function wcmo_get_expiration_periods() {

	return apply_filters(
		'wcmo_expiration_periods',
		array(
			''			=> '',
			'day'		=> __( 'Day(s)', 'wcmo' ),
			'week'	=> __( 'Week(s)', 'wcmo' ),
			'month'	=> __( 'Month(s)', 'wcmo' ),
			'year'	=> __( 'Year(s)', 'wcmo' )
		 )
	 );

}
