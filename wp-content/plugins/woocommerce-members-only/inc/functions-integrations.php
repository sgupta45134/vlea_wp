<?php
/**
 * Functions to help with integrating other plugins
 * @since 1.9.17
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wcmo_filter_ptuwc_tax_query( $tax_query, $args, $table_query ) {

	// Filter out any restricted categories
	$all_restricted_categories = wcmo_get_all_restricted_categories();

	// Check whether we are hiding protected products
	// if( wcmo_hide_products_in_archives() == 'yes' ) {

		// Iterate through each restricted category
		if( is_array( $all_restricted_categories ) ) {

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

	// }

	return $tax_query;

}
add_filter( 'ptuwc_query_tax-query', 'wcmo_filter_ptuwc_tax_query', 10, 3 );
