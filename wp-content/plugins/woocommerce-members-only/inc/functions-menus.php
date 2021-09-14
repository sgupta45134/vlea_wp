<?php
/**
 * General functions for menus
 * @since 1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters navigation menus
 *
 * @since 3.1.0
 *
 * @param array    $sorted_menu_items The menu items, sorted by each menu item's menu order.
 * @param stdClass $args              An object containing wp_nav_menu() arguments.
 */
function wcmo_filter_nav_menu_objects( $sorted_menu_items, $args ) {
	$menu_exclusions = wcmo_get_menu_exclusions();
	// Iterate through each menu item and remove any specified pages
	if( $sorted_menu_items ) {
		foreach( $sorted_menu_items as $id=>$item ) {
			// We decide whether to remove this page from the menu depending on the restricted content rule
			if( wcmo_is_menu_item_excluded( $item, $menu_exclusions ) ) {
				unset( $sorted_menu_items[$id] );
			}
		}
	}
	return $sorted_menu_items;
}
add_filter( 'wp_nav_menu_objects', 'wcmo_filter_nav_menu_objects', 10, 2 );

/**
 * Check if a page is restricted
 */
function wcmo_is_menu_item_excluded( $menu_item, $menu_exclusions ) {

	$is_restricted = false;

	// What pages could be hidden conditionally, e.g. some pages are hidden for some roles but visible for others

	// If it's a product or product category, then it could be hidden conditionally
	if( $menu_item->type == 'taxonomy' && $menu_item->object == 'product_cat' ) {

		// Check if we have the correct user role for this category
		$is_restricted = wcmo_is_restricted_category_by_id( $menu_item->object_id );

	} else if( is_array( $menu_exclusions ) && in_array( $menu_item->title, $menu_exclusions ) && ! wcmo_get_access_status() ) {

		$is_restricted = true;

	}

	// @todo - Check parent items
	return apply_filters( 'wcmo_is_menu_item_restricted', $is_restricted, $menu_item );

}
