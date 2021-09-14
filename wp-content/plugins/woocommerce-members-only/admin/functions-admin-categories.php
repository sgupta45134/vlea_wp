<?php
/**
 * Functions for adding new product category meta boxes
 * @since 1.1.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add meta fields to the product category
 * @since 1.1.0
 */
function wcmo_product_cat_add_form_fields() {
	if( wcmo_get_restricted_content() == 'category' ) { ?>
		<h3><?php _e( 'WooCommerce Members Only', 'raa' ); ?></h3>
		<?php printf(
			'<p class="description">%s</p>',
			__( 'These settings will override the global Members Only settings for this category only', 'raa' )
		); ?>
		<div class="form-field term-wcmo-override-global-restrictions-wrap">
			<label for="wcmo_override_global_restrictions"><?php _e( 'Override Global Restrictions?', 'wcmo' ); ?></label>
			<input type="checkbox" id="wcmo_override_global_restrictions" name="wcmo_override_global_restrictions" class="postform" value="yes">
		</div>
		<div class="form-field term-wcmo-passwords-wrap wcmo_show_if_password">
			<label for="wcmo_passwords"><?php _e( 'Passwords', 'wcmo' ); ?></label>
			<textarea id="wcmo_passwords" name="wcmo_passwords" class="postform"></textarea>
		</div>
		<div class="form-field term-wcmo-user-roles-wrap wcmo_show_if_user-role">
			<label for="wcmo_user_roles"><?php _e( 'User Roles', 'wcmo' ); ?></label>
			<select id="wcmo_user_roles" name="wcmo_user_roles[]" multiple class="postform wcmo_multiselect">
				<?php $options = wcmo_get_user_roles();
				if( $options ) {
					foreach( $options as $key=>$value ) {
						printf(
							'<option value="%s">%s</option>',
							$key,
							$value
						);
					}
				} ?>
			</select>
		</div>
		<div class="form-field term-wcmo-excerpt-wrap wcmo_show_if_category">
			<label for="wcmo_excerpt"><?php _e( 'Excerpt', 'wcmo' ); ?></label>
			<textarea id="wcmo_excerpt" name="wcmo_excerpt" class="postform"></textarea>
		</div>
	<?php
	}
}
add_action( 'product_cat_add_form_fields', 'wcmo_product_cat_add_form_fields', 999 );
add_action( 'category_add_form_fields', 'wcmo_product_cat_add_form_fields', 999 );

/**
 * Edit meta fields in the product category
 * @since 1.1.0
 */
function wcmo_product_cat_edit_form_fields( $term ) {
	if( wcmo_get_restricted_content() == 'category' ) {
		$override = get_term_meta( $term->term_id, 'wcmo_override_global_restrictions', true );
		$passwords = get_term_meta( $term->term_id, 'wcmo_passwords', true );
		$user_roles = get_term_meta( $term->term_id, 'wcmo_user_roles', true );
		$excerpt = get_term_meta( $term->term_id, 'wcmo_excerpt', true ); ?>
		<tr class="form-field term-wcmo-override-global-restrictions-wrap">
			<th scope="row" valign="top"><label><?php _e( 'Override Global Restrictions?', 'raa' ); ?></label></th>
			<td>
				<?php $checked = $override == 'yes' ? 'checked' : ''; ?>
				<input type="checkbox" id="wcmo_override_global_restrictions" name="wcmo_override_global_restrictions" class="postform" value="yes" <?php echo $checked; ?>>
			</td>
		</tr>
		<tr class="form-field term-wcmo-passwords-wrap wcmo_show_if_password">
			<th scope="row" valign="top"><label><?php _e( 'Passwords', 'raa' ); ?></label></th>
			<td>
				<textarea id="wcmo_passwords" name="wcmo_passwords" class="postform"><?php echo $passwords; ?></textarea>
			</td>
		</tr>
		<tr class="form-field term-wcmo-user-roles-wrap wcmo_show_if_user-role">
			<th scope="row" valign="top"><label><?php _e( 'User Roles', 'raa' ); ?></label></th>
			<td>
				<select id="wcmo_user_roles" name="wcmo_user_roles[]" multiple class="postform wcmo_multiselect">
					<?php $options = wcmo_get_user_roles();
					if( $options ) {
						foreach( $options as $key=>$value ) {
							$selected = ( is_array( $user_roles ) && in_array( $key, $user_roles ) ) ? 'selected' : '';
							printf(
								'<option value="%s" %s>%s</option>',
								$key,
								$selected,
								$value
							);
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr class="form-field term-wcmo-excerpt-wrap wcmo_show_if_category">
			<th scope="row" valign="top"><label><?php _e( 'Excerpt', 'raa' ); ?></label></th>
			<td>
				<textarea id="wcmo_excerpt" name="wcmo_excerpt" class="postform"><?php echo $excerpt; ?></textarea>
			</td>
		</tr>
		<?php
	}
}
add_action( 'product_cat_edit_form_fields', 'wcmo_product_cat_edit_form_fields', 999 );
add_action( 'category_edit_form_fields', 'wcmo_product_cat_edit_form_fields', 999 );


/**
 * Save the meta boxes
 *
 * @param mixed  $term_id Term ID being saved
 * @param mixed  $tt_id
 * @param string $taxonomy
 */
function wcmo_save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
	if( ! empty( $_POST['wcmo_override_global_restrictions'] ) && ( 'product_cat' === $taxonomy || 'category' === $taxonomy ) ) {
		update_term_meta( $term_id, 'wcmo_override_global_restrictions', esc_attr( $_POST['wcmo_override_global_restrictions'] ) );
	} else {
		delete_term_meta( $term_id, 'wcmo_override_global_restrictions' );
	}
	if ( isset( $_POST['wcmo_passwords'] ) && ( 'product_cat' === $taxonomy || 'category' === $taxonomy ) ) {
		update_term_meta( $term_id, 'wcmo_passwords', esc_attr( $_POST['wcmo_passwords'] ) );
	} else {
		delete_term_meta( $term_id, 'wcmo_passwords' );
	}
	if ( isset( $_POST['wcmo_user_roles'] ) && ( 'product_cat' === $taxonomy || 'category' === $taxonomy ) ) {
		update_term_meta( $term_id, 'wcmo_user_roles', $_POST['wcmo_user_roles'] );
	} else {
		delete_term_meta( $term_id, 'wcmo_user_roles' );
	}
	if ( isset( $_POST['wcmo_excerpt'] ) && ( 'category' === $taxonomy ) ) {
		update_term_meta( $term_id, 'wcmo_excerpt', $_POST['wcmo_excerpt'] );
	} else {
		delete_term_meta( $term_id, 'wcmo_excerpt' );
	}

	// Reset the wcmo_locally_restricted_categories transient
	wcmo_set_locally_restricted_categories();

}
add_action( 'created_term', 'wcmo_save_category_fields', 10, 3 );
add_action( 'edit_term', 'wcmo_save_category_fields', 10, 3 );

function wcmo_set_locally_restricted_categories() {

	remove_filter( 'get_terms', 'wcmo_hide_restricted_categories', 10, 4 );

	$args = array(
		// 'meta_key'		=> 'wcmo_override_global_restrictions',
		// 'meta_value'				=> 'yes',
		// 'meta_compare'			=> '='
	);

	$locally_restricted_categories = array();

	$all_cats = get_terms( 'product_cat', $args );
	foreach( $all_cats as $cat ) {
		if( isset( $cat->term_id ) ) {
			if( wcmo_get_category_local_rules_by_id( $cat->term_id ) ) {
				$locally_restricted_categories[] = $cat->term_id;
			}
		}
	}

	$all_cats = get_terms( 'category', $args );
	foreach( $all_cats as $cat ) {
		if( isset( $cat->term_id ) ) {
			if( wcmo_get_category_local_rules_by_id( $cat->term_id ) ) {
				$locally_restricted_categories[] = $cat->term_id;
			}
		}
	}

	add_filter( 'get_terms', 'wcmo_hide_restricted_categories', 10, 4 );

	// Set the transient to prevent running the query multiple times
	set_transient( 'wcmo_locally_restricted_categories', $locally_restricted_categories, wcmo_get_transient_expiration() );
	
	return $locally_restricted_categories;

}
