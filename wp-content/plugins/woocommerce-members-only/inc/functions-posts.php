<?php
/**
 * Functions for post specific stuff
 * @since 1.6.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wcmo_the_content( $content ) {

	global $post;

	// If the post is restricted, display alternative content
	if( isset( $post->ID ) && wcmo_is_post_restricted( $post->ID ) ) {
		$content = wcmo_get_restricted_post_excerpt( $post->ID, $content );
		// return $content;
	}

	return $content;

}
add_filter( 'the_content', 'wcmo_the_content' );

/**
 * Get replacement post content/excerpt for a restricted post
 * @since 1.6.0
 */
function wcmo_get_restricted_post_excerpt( $post_id, $content ) {

	// Check for local category settings that will override the global setting
	// Get this post's categories
	$category_ids = get_the_category( $post_id );
	// We only want the category IDs
	$category_ids = wp_list_pluck( $category_ids, 'cat_ID' );
	if( $category_ids ) {
		foreach( $category_ids as $category_id ) {
			// This is going to find the first local category rule and return that value
			$local_rules = wcmo_get_category_local_rules_by_id( $category_id );
			if( ! empty( $local_rules['excerpt'] ) ) {
				return $local_rules['excerpt'];
			}
		}
	}

	// If there's no locally set value, use the global one
	if( wcmo_get_global_excerpt_text() ) {
		return wcmo_get_global_excerpt_text();
	}

	return $content;

}

/**
 * Check if specific post is restricted
 * Used by wcmo_the_content to display alternative excerpt/content in post archives
 * @since 1.6.0
 */
function wcmo_is_post_restricted( $post_id ) {

	$is_restricted = false;

	$restriction_method = wcmo_get_restriction_method();
	if( ! $restriction_method || $restriction_method == 'no-restriction' ) {
		return false;
	}

	$restricted_content = wcmo_get_restricted_content();

	// Get this post's categories
	$category_ids = get_the_category( $post_id );
	// We only want the category IDs
	$category_ids = wp_list_pluck( $category_ids, 'cat_ID' );

	// Check if the post belongs to a restricted category
	if( $restricted_content == 'category' && wcmo_is_restricted_category_by_id( $category_ids ) ) {

		// Can only access the form page
		$is_restricted = true;

	}

	return apply_filters( 'wcmo_is_post_restricted', $is_restricted, $restricted_content );

}
