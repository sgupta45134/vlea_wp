<?php
/**
 * Functions for Members Only options on Product pages
 * @since 1.1.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Members Only tab / panel
 * @param $tabs	List of tabs
 */
function wcmo_product_tabs( $tabs ) {
	$tabs['wcmo'] = array(
		'label'		=> __( 'Members Only', 'wcmo' ),
		'target'	=> 'wcmo_options',
		'class'		=> array(),
		'priority'	=> 110
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'wcmo_product_tabs' );

/**
 * Change tab icon
 */
function wcmo_icon_style() { ?>
	<style>
		#woocommerce-product-data ul.wc-tabs li.pewc_options a:before { font-family: 'membersonly'; content: '\e900'; }
	</style><?php
}
add_action( 'admin_head', 'wcmo_icon_style' );

/**
 * Members Only tab options.
 */
function wcmo_tab_options() {

	global $post;
	$product = wc_get_product( $post->ID );

	if( is_wp_error( $product ) || ! is_object( $product ) ) {
		return;
	} ?>

	<div id='wcmo_options' class='panel woocommerce_options_panel wcmo_panel'>

		<div class="options_group">

			<?php
			woocommerce_wp_select(
				array(
					'id'            => 'wcmo_product_restriction_method',
					'label'         => __( 'Restriction method', 'wcmo' ),
					'type'					=> 'select',
					'desc_tip'      => true,
					'options'				=> array(
						'no-restriction'		=> __( 'No Restriction', 'wcmo' ),
						'user-id'						=> __( 'By User', 'wcmo' ),
						'user-role'					=> __( 'By User Role', 'wcmo' ),
						'password'					=> __( 'Password', 'wcmo' )
					),
					'description'   => __( 'The restriction method for this product.', 'wcmo' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'            => 'wcmo_product_user_ids',
					'label'         => __( 'User IDs', 'wcmo' ),
					'desc_tip'      => true,
					'class'					=> 'wcmo_product_show_if_user_id',
					'description'   => __( 'Enter a comma separated list of User IDs that can access this product.', 'wcmo' ),
				)
			);

			$roles = $product->get_meta( 'wcmo_product_user_roles' ); ?>

			<p class="form-field wcmo_product_user_roles_field wcmo_product_show_if_user_role">
				<label for="wcmo_product_user_roles"><?php _e( 'Permitted User Roles', 'wcmo' ); ?></label>
				<select id="wcmo_product_user_roles" name="wcmo_product_user_roles[]" multiple class="postform wcmo_multiselect">
					<?php $options = wcmo_get_user_roles();
					if( $options ) {
						foreach( $options as $key=>$value ) {
							$selected = ( is_array( $roles ) && in_array( $key, $roles ) ) ? 'selected' : '';
							printf(
								'<option value="%s" %s>%s</option>',
								$key,
								$selected,
								$value
							);
						}
					} ?>
				</select>
				<?php echo wc_help_tip( 'Select any user roles that are permitted to view this page', true ); ?>
			</p>

			<?php $hide_from_roles = $product->get_meta( 'wcmo_hide_from_user_roles' ); ?>

			<p class="form-field wcmo_hide_from_user_roles_field wcmo_product_show_if_user_role">
				<label for="wcmo_hide_from_user_roles"><?php _e( 'Hide From User Roles', 'wcmo' ); ?></label>
				<select id="wcmo_hide_from_user_roles" name="wcmo_hide_from_user_roles[]" multiple class="postform wcmo_multiselect">
					<?php $options = wcmo_get_user_roles();
					if( $options ) {
						foreach( $options as $key=>$value ) {
							$selected = ( is_array( $hide_from_roles ) && in_array( $key, $hide_from_roles ) ) ? 'selected' : '';
							printf(
								'<option value="%s" %s>%s</option>',
								$key,
								$selected,
								$value
							);
						}
					} ?>
				</select>
				<?php echo wc_help_tip( 'Select any user roles that are not permitted to view this page', true ); ?>
			</p>

			<?php
			woocommerce_wp_textarea_input(
				array(
					'id'            => 'wcmo_product_passwords',
					'label'         => __( 'Passwords', 'wcmo' ),
					'desc_tip'      => true,
					'class'					=> 'wcmo_product_passwords',
					'description'   => __( 'Enter a list of passwords for this product.', 'wcmo' ),
				)
			);
			woocommerce_wp_checkbox(
				array(
					'id'            => 'wcmo_product_exclude_from_archives',
					'label'         => __( 'Hide in Archives', 'wcmo' ),
					'desc_tip'      => false,
					'class'					=> 'wcmo_product_show_if_user_id',
					'description'   => __( 'Select this to remove the product from the shop, category, tag archives.', 'wcmo' ),
				)
			);
			?>
		</div>

		<div class="options_group show_if_simple">

			<p class="form-field wcmo_product_assign_roles_field">

				<label for="wcmo_product_assign_roles">

					<?php _e( 'Assign user roles', 'wcmo' ); ?>

				</label>

				<select class="wcmo_multiselect" multiple="multiple" style="width: 50%;" name="wcmo_product_assign_roles[]" id="wcmo_product_assign_roles">
					<?php
					$assigned_roles = get_post_meta( $post->ID, 'wcmo_product_assign_roles', true );
					$roles = wcmo_get_assignable_user_roles();
					if( ! empty( $roles ) ) {
						foreach( $roles as $id=>$role ) {
							$selected = ( is_array( $assigned_roles ) && in_array( $id, $assigned_roles ) ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $role ) . '</option>';
						}
					} ?>
				</select>

				<?php printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Choose which roles to assign to the customer after purchasing this product', 'wcmo' )
				); ?>

			</p>

			<p class="form-field wcmo_product_expires_after_field">

				<label for="wcmo_product_expires_after">

					<?php _e( 'Expires after', 'wcmo' ); ?>

				</label>

				<?php $expires_after_value = get_post_meta( $post->ID, 'wcmo_product_expires_after_value', true ); ?>

				<input type="number" style="width: 60px; margin-right: 10px" name="wcmo_product_expires_after_value" id="wcmo_product_expires_after_value" min=0 step=1 value="<?php echo $expires_after_value; ?>">

				<?php $expires_after_period = get_post_meta( $post->ID, 'wcmo_product_expires_after_period', true ); ?>
				<select name="wcmo_product_expires_after_period" id="wcmo_product_expires_after_period">
					<?php
					$periods = wcmo_get_expiration_periods();
					if( ! empty( $periods ) ) {
						foreach( $periods as $id=>$period ) {
							$selected = $id == $expires_after_period ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $period ) . '</option>';
						}
					} ?>
				</select>

				<?php printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Set how long these roles will be assigned to the user', 'wcmo' )
				); ?>

			</p>

			<p class="form-field wcmo_product_reminder_before_field">

				<label for="wcmo_product_reminder_before">

					<?php _e( 'Send reminder', 'wcmo' ); ?>

				</label>

				<?php $reminder_before_value = get_post_meta( $post->ID, 'wcmo_product_reminder_before_value', true ); ?>

				<input type="number" style="width: 60px; margin-right: 10px" name="wcmo_product_reminder_before_value" id="wcmo_product_reminder_before_value" min=0 step=1 value="<?php echo $reminder_before_value; ?>">

				<?php $reminder_before_period = get_post_meta( $post->ID, 'wcmo_product_reminder_before_period', true ); ?>
				<select name="wcmo_product_reminder_before_period" id="wcmo_product_reminder_before_period">
					<?php
					$periods = wcmo_get_expiration_periods();
					if( ! empty( $periods ) ) {
						foreach( $periods as $id=>$period ) {
							$selected = $id == $reminder_before_period ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $period ) . '</option>';
						}
					} ?>
				</select>

				<?php
				printf(
					'<span>&nbsp;&nbsp;&nbsp;%s</span>',
					__( 'before the expiry', 'wcmo' )
				);
				printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Set when to send a reminder email - define a period before the end of the expiry period', 'wcmo' )
				); ?>

			</p>

			<?php if( class_exists( 'WC_Subscriptions' ) ) { ?>

				<p class="form-field wcmo_product_sub_cancelled_roles_field">

					<label for="wcmo_product_sub_cancelled_roles">

						<?php _e( 'Assign user roles after cancelling subscription' ); ?>

					</label>

					<select class="wcmo_multiselect" multiple="multiple" style="width: 50%;" name="wcmo_product_sub_cancelled_roles[]" id="wcmo_product_sub_cancelled_roles">
						<?php
						$sub_cancelled_roles = get_post_meta( $post->ID, 'wcmo_product_sub_cancelled_roles', true );
						$roles = wcmo_get_user_roles();
						if( ! empty( $roles ) ) {
							foreach( $roles as $id=>$role ) {
								$selected = ( is_array( $sub_cancelled_roles ) && in_array( $id, $sub_cancelled_roles ) ) ? 'selected' : '';
								echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $role ) . '</option>';
							}
						} ?>
					</select>

				</p>

			<?php } ?>

		</div>

	</div>

<?php }
add_action( 'woocommerce_product_data_panels', 'wcmo_tab_options' );

/**
 * Save the custom fields
 * @since 1.2.0
 */
function wcmo_process_product_meta( $post_id ) {

	$product = wc_get_product( $post_id );
	$method = isset( $_POST['wcmo_product_restriction_method'] ) ? $_POST['wcmo_product_restriction_method'] : '';
	$product->update_meta_data( 'wcmo_product_restriction_method', sanitize_text_field( $method ) );
	$ids = isset( $_POST['wcmo_product_user_ids'] ) ? $_POST['wcmo_product_user_ids'] : '';
	$product->update_meta_data( 'wcmo_product_user_ids', sanitize_text_field( $ids ) );
	$roles = isset( $_POST['wcmo_product_user_roles'] ) ? $_POST['wcmo_product_user_roles'] : '';
	$product->update_meta_data( 'wcmo_product_user_roles', $roles );
	$hide_from_roles = isset( $_POST['wcmo_hide_from_user_roles'] ) ? $_POST['wcmo_hide_from_user_roles'] : '';
	$product->update_meta_data( 'wcmo_hide_from_user_roles', $hide_from_roles );
	$exclude = isset( $_POST['wcmo_product_exclude_from_archives'] ) ? $_POST['wcmo_product_exclude_from_archives'] : '';
	$product->update_meta_data( 'wcmo_product_exclude_from_archives', sanitize_text_field( $exclude ) );
	$passwords = isset( $_POST['wcmo_product_passwords'] ) ? $_POST['wcmo_product_passwords'] : '';
	$product->update_meta_data( 'wcmo_product_passwords', sanitize_textarea_field( $passwords ) );

	$roles = isset( $_POST['wcmo_product_assign_roles'] ) ? $_POST['wcmo_product_assign_roles'] : '';
	$product->update_meta_data( 'wcmo_product_assign_roles', $roles );
	$value = isset( $_POST['wcmo_product_expires_after_value'] ) ? $_POST['wcmo_product_expires_after_value'] : '';
	$product->update_meta_data( 'wcmo_product_expires_after_value', $value );
	$period = isset( $_POST['wcmo_product_expires_after_period'] ) ? $_POST['wcmo_product_expires_after_period'] : '';
	$product->update_meta_data( 'wcmo_product_expires_after_period', $period );
	$value = isset( $_POST['wcmo_product_reminder_before_value'] ) ? $_POST['wcmo_product_reminder_before_value'] : '';
	$product->update_meta_data( 'wcmo_product_reminder_before_value', $value );
	$period = isset( $_POST['wcmo_product_reminder_before_period'] ) ? $_POST['wcmo_product_reminder_before_period'] : '';
	$product->update_meta_data( 'wcmo_product_reminder_before_period', $period );

	$sub_cancelled_roles = isset( $_POST['wcmo_product_sub_cancelled_roles'] ) ? $_POST['wcmo_product_sub_cancelled_roles'] : '';
	$product->update_meta_data( 'wcmo_product_sub_cancelled_roles', $sub_cancelled_roles );

	// If we've got a product restricted by password, set a flag so we know to set the WC session cookie
	if( $method == 'password' ) {
		update_option( 'wcmo_uses_local_password', true );
	}

	$product->save();

	// Update the list of excluded products
	wcmo_update_excluded_products_transient();

	// Update the list of product passwords
	wcmo_update_product_passwords_transient();

}
add_action( 'woocommerce_process_product_meta', 'wcmo_process_product_meta' );

/**
 * Update the transient where we store locally restricted products that should be excluded from archives
 * @since 1.0.0
 */
function wcmo_update_excluded_products_transient() {

	$args = array(
		'post_type'				=> 'product',
		'posts_per_page'	=> -1,
		'meta_query'			=> array(
			'relation'			=> 'AND',
			// array(
			// 	'key'					=> 'wcmo_product_exclude_from_archives',
			// 	'value'				=> 'yes',
			// 	'compare'			=> '='
			// ),
			array(
				'key'					=> 'wcmo_product_restriction_method',
				'value'				=> 'no-restriction',
				'compare'			=> '!='
			)
		),
		'fields'					=> 'ids'
	);

	$excluded_products = new WP_Query( $args );

	$restricted_products = array();

	// Use both these arrays to create transients that will let us query roles and product IDs that are restricted on single pages
	$restricted_products_by_role_single = array();
	$restricted_roles_by_product_single = array();

	// Use both these arrays to create transients that will let us query roles and product IDs that are restricted on archive pages
	$restricted_products_by_role_archive = array();
	$restricted_roles_by_product_archive = array();

	if( $excluded_products->posts ) {

		foreach( $excluded_products->posts as $product_id ) {

			$product = wc_get_product( $product_id );

			if( ! is_object( $product ) ) {
				continue;
			}
			$restriction_method = $product->get_meta( 'wcmo_product_restriction_method' );

			if( $restriction_method == 'user-id' ) {

				// Save which user IDs can view this product
				$user_ids = get_post_meta( $product_id, 'wcmo_product_user_ids', true );
				$restricted_products[$product_id] = explode( ',', $user_ids );

			} else if( $restriction_method == 'user-role' ) {

				// Create an array of user roles and restricted products
				$user_roles = get_post_meta( $product_id, 'wcmo_product_user_roles', true );
				$exclude_from_archives = get_post_meta( $product_id, 'wcmo_product_exclude_from_archives', true );

				if( ! empty( $user_roles ) ) {

					foreach( $user_roles as $user_role ) {

						if( ! isset( $restricted_roles_by_product_single[$user_role] ) ) {
							// Create a new element for this role
							$restricted_roles_by_product_single[$user_role] = array( $product_id );
						} else {
							$restricted_roles_by_product_single[$user_role][] = $product_id;
						}

						// Set separate values for pages/products that are hidden in archives
						if( $exclude_from_archives ) {

							if( ! isset( $restricted_roles_by_product_archive[$user_role] ) ) {
								// Create a new element for this role
								$restricted_roles_by_product_archive[$user_role] = array( $product_id );
							} else {
								$restricted_roles_by_product_archive[$user_role][] = $product_id;
							}

						}

					}

				}

				$restricted_products_by_role_single[$product_id] = $user_roles;

				if( $exclude_from_archives ) {
					$restricted_products_by_role_archive[$product_id] = $user_roles;
				}

			}

		}

	}

	$args = array(
		'post_type'				=> 'product',
		'posts_per_page'	=> -1,
		'meta_query'			=> array(
			'relation'			=> 'AND',
			// array(
			// 	'key'					=> 'wcmo_product_exclude_from_archives',
			// 	'value'				=> 'yes',
			// 	'compare'			=> '='
			// ),
			array(
				'key'					=> 'wcmo_hide_from_user_roles',
				'value'				=> '',
				'compare'			=> '!='
			),
			array(
				'key'					=> 'wcmo_product_restriction_method',
				'value'				=> 'user-role',
				'compare'			=> '='
			)
		),
		'fields'					=> 'ids'
	);

	// Get any products that are hidden from certain roles
	$hidden_products = new WP_Query( $args );

	// Use both these arrays to create transients that will let us query roles and product IDs that are hidden on single pages
	$hidden_products_by_role_single = array();
	$hidden_roles_by_product_single = array();

	// Use these arrays to create transients that will let us query roles and product IDs for products hidden on archive pages
	$hidden_products_by_role_archive = array();
	$hidden_roles_by_product_archive = array();

	if( $hidden_products->posts ) {

		foreach( $hidden_products->posts as $product_id ) {

			$product = wc_get_product( $product_id );

			if( ! is_object( $product ) ) {
				continue;
			}

			// Create an array of user roles and restricted products
			$user_roles = get_post_meta( $product_id, 'wcmo_hide_from_user_roles', true );
			$exclude_from_archives = get_post_meta( $product_id, 'wcmo_product_exclude_from_archives', true );

			if( ! empty( $user_roles ) ) {

				foreach( $user_roles as $user_role ) {

					if( ! isset( $hidden_roles_by_product_single[$user_role] ) ) {
						// Create a new element for this role
						$hidden_roles_by_product_single[$user_role] = array( $product_id );
					} else {
						$hidden_roles_by_product_single[$user_role][] = $product_id;
					}

					$hidden_products_by_role_single[$product_id] = $user_roles;

					// Set separate values for pages/products that are hidden in archives
					if( $exclude_from_archives ) {

						if( ! isset( $hidden_roles_by_product_archive[$user_role] ) ) {
							// Create a new element for this role
							$hidden_roles_by_product_archive[$user_role] = array( $product_id );
						} else {
							$hidden_roles_by_product_archive[$user_role][] = $product_id;
						}

						$hidden_products_by_role_archive[$product_id] = $user_roles;

					}

				}

			}

		}

	}

	set_transient( 'wcmo_products_restricted_by_user', $restricted_products, wcmo_get_transient_expiration() );

	/**
	 * We set this to specify whether there are any products restricted by user ID
	 * Used on the front-end to confirm whether to run the query again
	 */
	$has_restricted_products = ! empty( $restricted_products ) ? 'yes' : 'no';
	update_option( 'wcmo_has_products_restricted_by_user', $has_restricted_products );

	// Array of product IDs with roles
	set_transient( 'wcmo_products_restricted_by_user_role_archive', $restricted_products_by_role_archive, wcmo_get_transient_expiration() );
	// Array of roles with product IDs
	set_transient( 'wcmo_restricted_roles_by_product_archive', $restricted_roles_by_product_archive, wcmo_get_transient_expiration() );

	// Array of product IDs with roles
	set_transient( 'wcmo_products_restricted_by_user_role_single', $restricted_products_by_role_single, wcmo_get_transient_expiration() );
	// Array of roles with product IDs
	set_transient( 'wcmo_restricted_roles_by_product_single', $restricted_roles_by_product_single, wcmo_get_transient_expiration() );

	// Array of product IDs with roles (hidden on single pages)
	set_transient( 'wcmo_products_hidden_by_user_role_single', $hidden_products_by_role_single, wcmo_get_transient_expiration() );
	// Array of roles with product IDs (hidden on single pages)
	set_transient( 'wcmo_hidden_roles_by_product_single', $hidden_roles_by_product_single, wcmo_get_transient_expiration() );

	// Array of product IDs with roles (hidden on archive pages)
	set_transient( 'wcmo_products_hidden_by_user_role_archive', $hidden_products_by_role_archive, wcmo_get_transient_expiration() );
	// Array of roles with product IDs (hidden on archive pages)
	set_transient( 'wcmo_hidden_roles_by_product_archive', $hidden_roles_by_product_archive, wcmo_get_transient_expiration() );

	wcmo_delete_current_user_archive_transients();

}

/**
 * Check whether there are any products restricted by user ID
 * Use this to know whether to re-run the queries in wcmo_update_excluded_products_transient
 * @since 1.10.1
 */
function wcmo_has_products_restricted_by_user() {
	$has_restricted_products = get_option( 'wcmo_has_products_restricted_by_user' );
	return $has_restricted_products;
}

/**
 * Clear all the transients used to store lists of restricted products per user
 * @since 1.9.14
 */
function wcmo_delete_current_user_archive_transients() {
	global $wpdb;
	$sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_wcmo_products_restricted_to_current_user_archive_%"';
	$wpdb->query( $sql );
}

/**
 * Update the transient where we store passwords for locally restricted products
 * @since 1.0.0
 */
function wcmo_update_product_passwords_transient() {
	$products = wc_get_products(
		array(
			'limit'						=> -1,
			'meta_key'				=> 'wcmo_product_passwords',
			'meta_value'			=> '',
			'meta_compare'		=> '!=',
			// 'fields' 					=> 'ids'
		)
	);
	$product_passwords = array();
	if( $products ) {
		foreach( $products as $product ) {
			$product_id = $product->get_id();
			// $product = wc_get_product( $product_id );
			$product_passwords[$product_id] = $product->get_meta( 'wcmo_product_passwords' );
		}
	}
	set_transient( 'wcmo_product_passwords', $product_passwords, wcmo_get_transient_expiration() );
}

/**
 * Filter the wc_get_products query to include wcmo_exclude_products meta field
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Product_Query.
 * @return array modified $query
 */
function wcmo_handle_custom_query_var( $query, $query_vars ) {
	if ( ! empty( $query_vars['wcmo_product_restriction_method'] ) ) {
		$query['meta_query'][] = array(
			'key' 	=> 'wcmo_product_restriction_method',
			'value' => esc_attr( $query_vars['wcmo_product_restriction_method'] ),
		);
	}
	if ( ! empty( $query_vars['wcmo_product_exclude_from_archives'] ) ) {
		$query['meta_query'][] = array(
			'key' 	=> 'wcmo_product_exclude_from_archives',
			'value' => esc_attr( $query_vars['wcmo_product_exclude_from_archives'] ),
		);
	}
	if ( ! empty( $query_vars['wcmo_product_passwords'] ) ) {
		$query['meta_query'][] = array(
			'key' 	=> 'wcmo_product_passwords',
			'value' => esc_attr( $query_vars['wcmo_product_passwords'] ),
		);
	}

	return $query;
}
add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'wcmo_handle_custom_query_var', 10, 2 );

/**
 * Role assignment fields for variations
 * @since 1.10.0
 */
function wcmo_variation_fields( $loop, $variation_data, $variation ) {

	global $post; ?>

	<div class="wc-metabox-content options wcmo-variation-fields">

		<?php
		printf(
			'<h3>%s</h3>',
			__( 'Members Only Options', 'wcmo' )
		); ?>

		<div class="">

			<p class="form-field form-row wcmo_product_assign_roles_field">

				<label for="wcmo_product_assign_roles">
					<?php _e( 'Assign user roles', 'wcmo' ); ?>
				</label>

				<select class="wcmo_multiselect" multiple="multiple" style="width: 50%;" name="wcmo_product_assign_roles[<?php echo $variation->ID; ?>][]" id="wcmo_product_assign_roles_<?php echo $variation->ID; ?>">
					<?php
					$assigned_roles = get_post_meta( $variation->ID, 'wcmo_product_assign_roles', true );
					$roles = wcmo_get_assignable_user_roles();
					if( ! empty( $roles ) ) {
						foreach( $roles as $id=>$role ) {
							$selected = ( is_array( $assigned_roles ) && in_array( $id, $assigned_roles ) ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $role ) . '</option>';
						}
					} ?>
				</select>

				<?php printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Choose which roles to assign to the customer after purchasing this variation', 'wcmo' )
				); ?>

			</p>

			<p class="form-field wcmo_product_expires_after_field">

				<label for="wcmo_product_expires_after">

					<?php _e( 'Expires after', 'wcmo' ); ?>

				</label>

				<?php $expires_after_value = get_post_meta( $variation->ID, 'wcmo_product_expires_after_value', true ); ?>

				<input type="number" style="width: 60px; margin-right: 10px" name="wcmo_product_expires_after_value[<?php echo $variation->ID; ?>]" id="wcmo_product_expires_after_value_[<?php echo $variation->ID; ?>]" min=0 step=1 value="<?php echo $expires_after_value; ?>">

				<?php $expires_after_period = get_post_meta( $variation->ID, 'wcmo_product_expires_after_period', true ); ?>
				<select name="wcmo_product_expires_after_period[<?php echo $variation->ID; ?>]" id="wcmo_product_expires_after_period_<?php echo $variation->ID; ?>">
					<?php
					$periods = wcmo_get_expiration_periods();
					if( ! empty( $periods ) ) {
						foreach( $periods as $id=>$period ) {
							$selected = $id == $expires_after_period ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $period ) . '</option>';
						}
					} ?>
				</select>

				<?php printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Set how long these roles will be assigned to the user', 'wcmo' )
				); ?>

			</p>

			<p class="form-field wcmo_product_reminder_before_field">

				<label for="wcmo_product_reminder_before">

					<?php _e( 'Send reminder', 'wcmo' ); ?>

				</label>

				<?php $reminder_before_value = get_post_meta( $variation->ID, 'wcmo_product_reminder_before_value', true ); ?>

				<input type="number" style="width: 60px; margin-right: 10px" name="wcmo_product_reminder_before_value[<?php echo $variation->ID; ?>]" id="wcmo_product_reminder_before_value_<?php echo $variation->ID; ?>" min=0 step=1 value="<?php echo $reminder_before_value; ?>">

				<?php $reminder_before_period = get_post_meta( $variation->ID, 'wcmo_product_reminder_before_period', true ); ?>
				<select name="wcmo_product_reminder_before_period[<?php echo $variation->ID; ?>]" id="wcmo_product_reminder_before_period_<?php echo $variation->ID; ?>">
					<?php
					$periods = wcmo_get_expiration_periods();
					if( ! empty( $periods ) ) {
						foreach( $periods as $id=>$period ) {
							$selected = $id == $reminder_before_period ? 'selected' : '';
							echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $period ) . '</option>';
						}
					} ?>
				</select>

				<?php
				printf(
					'<span>&nbsp;&nbsp;&nbsp;%s</span>',
					__( 'before the expiry', 'wcmo' )
				);
				printf(
					'<span class="woocommerce-help-tip" data-tip="%s"></span>',
					__( 'Set when to send a reminder email - define a period before the end of the expiry period', 'wcmo' )
				); ?>

			</p>

			<?php if( class_exists( 'WC_Subscriptions' ) ) { ?>

				<p class="form-field wcmo_product_sub_cancelled_roles_field">

					<label for="wcmo_product_sub_cancelled_roles">

						<?php _e( 'Assign user roles after cancelling subscription' ); ?>

					</label>

					<select class="wcmo_multiselect" multiple="multiple" style="width: 50%;" name="wcmo_product_sub_cancelled_roles[<?php echo $variation->ID; ?>][]" id="wcmo_product_sub_cancelled_roles_<?php echo $variation->ID; ?>]">
						<?php
						$sub_cancelled_roles = get_post_meta( $variation->ID, 'wcmo_product_sub_cancelled_roles', true );
						$roles = wcmo_get_user_roles();
						if( ! empty( $roles ) ) {
							foreach( $roles as $id=>$role ) {
								$selected = ( is_array( $sub_cancelled_roles ) && in_array( $id, $sub_cancelled_roles ) ) ? 'selected' : '';
								echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . wp_kses_post( $role ) . '</option>';
							}
						} ?>
					</select>

				</p>

			<?php } ?>

		</div>

	</div>

<?php
}
add_action( 'woocommerce_product_after_variable_attributes', 'wcmo_variation_fields', 10, 3 );

/**
 * Save the role-based variation fields
 * @since 1.4.13
 */
function wcmo_save_product_variation( $variation_id, $index ) {

	if( isset( $_POST['wcmo_product_assign_roles'][$variation_id] ) ) {
		update_post_meta( $variation_id, 'wcmo_product_assign_roles', $_POST['wcmo_product_assign_roles'][$variation_id] );
	} else {
		delete_post_meta( $variation_id, 'wcmo_product_assign_roles' );
	}

	if( isset( $_POST['wcmo_product_expires_after_value'][$variation_id] ) ) {
		update_post_meta( $variation_id, 'wcmo_product_expires_after_value', $_POST['wcmo_product_expires_after_value'][$variation_id] );
	} else {
		delete_post_meta( $variation_id, 'wcmo_product_expires_after_value' );
	}

	if( isset( $_POST['wcmo_product_expires_after_period'][$variation_id] ) ) {
		update_post_meta( $variation_id, 'wcmo_product_expires_after_period', $_POST['wcmo_product_expires_after_period'][$variation_id] );
	} else {
		delete_post_meta( $variation_id, 'wcmo_product_expires_after_period' );
	}

	if( isset( $_POST['wcmo_product_reminder_before_value'][$variation_id] ) ) {
		update_post_meta( $variation_id, 'wcmo_product_reminder_before_value', $_POST['wcmo_product_reminder_before_value'][$variation_id] );
	} else {
		delete_post_meta( $variation_id, 'wcmo_product_reminder_before_value' );
	}

	if( isset( $_POST['wcmo_product_reminder_before_period'][$variation_id] ) ) {
		update_post_meta( $variation_id, 'wcmo_product_reminder_before_period', $_POST['wcmo_product_reminder_before_period'][$variation_id] );
	} else {
		delete_post_meta( $variation_id, 'wcmo_product_reminder_before_period' );
	}

}
add_action( 'woocommerce_save_product_variation', 'wcmo_save_product_variation', 10, 2 );
