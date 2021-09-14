<?php
/**
 * Functions for approval user registrations
 * @since 1.7.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add any new fields to the registration form
 * @since 1.9.0
 */
function wcmo_register_form() {

	$registration_fields = wcmo_get_registration_fields();
	$enabled_fields = wcmo_get_enabled_registration_fields();

	if( ! empty( $enabled_fields['fields'] ) ) {

		$fields = $enabled_fields['fields'];

		// If we have a priority, use that to order the fields
		if( ! empty( $enabled_fields['priority'] ) ) {
			$fields = $enabled_fields['priority'];
			asort( $fields );
		}

		foreach( $fields as $field_id=>$field ) {

			// If the field isn't in our list, skip on
			if( ! isset( $enabled_fields['fields'][$field_id] ) ) {
				continue;
			}

			$value = isset( $_POST[$field_id] ) ? $_POST[$field_id] : '';
			$required = ( isset( $enabled_fields['required'][$field_id] ) && $enabled_fields['required'][$field_id] == 'on' ) ? true : false;

			if( isset( $registration_fields[$field_id]['type'] ) ) {

				if( $registration_fields[$field_id]['type'] == 'country' ) {

					wp_enqueue_script( 'wc-country-select' );
					woocommerce_form_field(
						$field_id,
						array(
							'type'      	=> 'country',
							'class'     	=> array( 'chzn-drop' ),
							'label'     	=> $registration_fields[$field_id]['label'],
							'placeholder' => __( 'Choose your country', 'wcmo' ),
							'required'  	=> $required,
							'clear'     	=> true
						)
					);

				} else if( $registration_fields[$field_id]['type'] == 'upload' ) {

					wp_nonce_field( 'wcmo_file_upload', 'wcmo_file_upload', true ); ?>
					<p class="form-row" id="field-<?php echo esc_attr( $field_id ); ?>">
				    <label for="<?php echo esc_attr( $field_id ); ?>">
							<?php echo esc_html( $registration_fields[$field_id]['label'] );
							if( $required ) { ?>
								&nbsp;<span class="required">*</span>
							<?php } else { ?>
								&nbsp;<span class="optional">(<?php _e( 'optional', 'wcmo' ); ?>)</span></label>
							<?php } ?>
						</label>
						<input type="file" name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>">
						<?php if( ! empty( $enabled_fields['description'][$field_id] ) ) { ?>
							<span class="description" id="<?php echo esc_attr( $field_id ); ?>-description" aria-hidden="true">
								<?php echo esc_html( $enabled_fields['description'][$field_id] ); ?>x
							</span>
						<?php } ?>
				  </p>

				<?php } else {

					woocommerce_form_field(
						$field_id,
						array(
							'type'        => $registration_fields[$field_id]['type'],
							'required'    => $required, // just adds an "*"
							'label'       => $registration_fields[$field_id]['label'],
							'description'	=> isset( $enabled_fields['description'][$field_id] ) ? $enabled_fields['description'][$field_id] : false
						),
						$value
					);

				}

			}

		}

	}

	// Add a field that allows the user to select their role when registering
	$enable_registration_field = get_option( 'wcmo_enable_registration_roles', 'no' );
	if( $enable_registration_field == 'yes' ) {

		$enabled_roles = wcmo_get_enabled_registration_roles();
		if( isset( $enabled_roles['include'] ) ) {

			// Get an array of enabled roles
			$all_roles = wcmo_get_assignable_user_roles();
			$roles = array();
			foreach( $enabled_roles['include'] as $id=>$enabled ) {

				if( $enabled == 'on' ) {
					$roles[$id] = $all_roles[$id];
				}

			}

			$value = isset( $_POST['wcmo_registration_role'] ) ? $_POST['wcmo_registration_role'] : '';

			// Now print our field
			woocommerce_form_field(
				'wcmo_registration_role',
				array(
					'type'        => 'select',
					'required'    => true, // just adds an "*"
					'label'       => apply_filters( 'wcmo_registration_form_role_label', __( 'Role', 'wcmo' ) ),
					'options'			=> $roles
				),
				$value
			);

		}

	}

}
add_action( 'woocommerce_register_form', 'wcmo_register_form', 10 );

/**
 * Change the enctype for the registration form to allow uploads
 * @since 1.10.0
 */
function wcmo_enctype_custom_registration_forms() {
	echo 'enctype="multipart/form-data"';
}
add_action( 'woocommerce_register_form_tag', 'wcmo_enctype_custom_registration_forms' );

/**
 * Validate extra fields
 * @since 1.9.0
 */
function wcmo_register_post( $username, $email, $errors ) {

	if( is_checkout() ) {
		// We only do this validation on the registration form, not the checkout form
		return;
	}

	$registration_fields = wcmo_get_registration_fields();
	$enabled_fields = wcmo_get_enabled_registration_fields();
	$upload_field_ids = array();

	if( ! empty( $enabled_fields['fields'] ) ) {

		foreach( $enabled_fields['fields'] as $field_id=>$field ) {

			if( ! isset( $registration_fields[$field_id] ) ) {
				continue;
			}

			$value = isset( $_POST[$field_id] ) ? $_POST[$field_id] : '';
			$required = ( isset( $enabled_fields['required'][$field_id] ) && $enabled_fields['required'][$field_id] == 'on' ) ? true : false;

			if( $registration_fields[$field_id]['type'] == 'upload' ) {
				// Validate upload fields separately
				if( $required ) {
					$upload_field_ids[$field_id] = $field;
				}
				continue;
			}

			// Check for any empty required fields
			if( $required && empty( $_POST[$field_id] ) ) {
				$errors->add(
					$field_id . '_error',
					apply_filters(
						'wcmo_registration_validation_error_' . $field_id,
						sprintf(
							__( 'Please enter a value for %s', 'wcmo' ),
							$registration_fields[$field_id]['label']
						),
						$field
					)
				);
			}

		}

		// Check for any uploads to validate
		if( $upload_field_ids ) {
			foreach( $upload_field_ids as $field_id=>$field ) {
				if( empty( $_FILES[$field_id]['name'] ) ) {
					$errors->add(
						$field_id . '_error',
						apply_filters(
							'wcmo_registration_upload_validation_error_' . $field_id,
							sprintf(
								__( 'Please upload a file for %s', 'wcmo' ),
								$registration_fields[$field_id]['label']
							),
							$field
						)
					);
				}
			}
		}

	}

}
add_action( 'woocommerce_register_post', 'wcmo_register_post', 10, 3 );

/**
 * Save the extra field data
 * @since 1.9.0
 */
function wcmo_created_customer( $user_id ) {

	// Update user roles
	$user = get_user_by( 'id', $user_id );
	$roles = $user->roles;

	// Check for default user roles
	$default_roles = wcmo_get_default_user_roles();

	// Check if a role has been set in the registration form
	if( isset( $_POST['wcmo_registration_role'] ) ) {

		$registered_role = $_POST['wcmo_registration_role'];
		// Add it to the list of default roles
		$default_roles[] = $registered_role;

	}

	$default_roles = apply_filters( 'wcmo_default_registration_roles', $default_roles, $user_id, $_POST );

	if( $roles ) {
		// Remove existing roles
		foreach( $roles as $role ) {
			$user->remove_role( $role );
		}
		// Add new default roles
		foreach( $default_roles as $role ) {
			$user->add_role( $role );
		}
		$roles = $default_roles;
	}

	// Check for any additional registration fields
	$registration_fields = wcmo_get_registration_fields();
	$enabled_fields = wcmo_get_enabled_registration_fields();

	if( ! empty( $enabled_fields['fields'] ) ) {

		foreach( $enabled_fields['fields'] as $field_id=>$field ) {

			$value = isset( $_POST[$field_id] ) ? $_POST[$field_id] : '';
			$required = ( isset( $enabled_fields['required'][$field_id] ) && $enabled_fields['required'][$field_id] == 'on' ) ? true : false;

			if ( isset( $_POST[$field_id] ) ) {
				update_user_meta( $user_id, $field_id, wc_clean( $_POST[$field_id] ) );
			}

		}

	}

	if( ! empty( $_FILES ) ) {

		$upload_fields = wcmo_get_upload_registration_fields( $registration_fields, $enabled_fields );
		if( $upload_fields ) {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}
			$upload_overrides = array(
				'test_form' => false
			);

			foreach( $upload_fields as $upload_field ) {

				// Upload each file to the uploads directory
				$file = $_FILES[$upload_field];
				$move_file = wp_handle_upload( $file, $upload_overrides );
				if( $move_file && ! isset( $move_file['error'] ) ) {

					update_user_meta( $user_id, $upload_field, $move_file['url'] );

				} else {

					wc_add_notice(
						$move_file['error'],
						'notice'
					);

				}

			}

		}

	}

	// If user approval is required, save the roles as meta and set role to pending
	if( wcmo_get_user_approval() == 'yes' ) {

		$needs_approval = true;

		// If the user is selecting their role from the registration form
		// Then we need to confirm if it needs to be approved
		if( isset( $_POST['wcmo_registration_role'] ) ) {

			// Check if the role we're applying for needs to be approved
			$enabled_roles = wcmo_get_enabled_registration_roles();

			if( isset( $enabled_roles['approve'] ) && empty( $enabled_roles['approve'][$registered_role] ) ) {

				// This role doesn't need to be approved
				$needs_approval = false;

			}

		}

		$needs_approval = apply_filters( 'wcmo_user_needs_approval', $needs_approval, $user );

		if( $needs_approval ) {

			// Save these roles until the user is approved
			update_user_meta( $user_id, 'wcmo_user_roles', $roles );

			// Reset the role to pending
			$user->set_role( 'pending' );

			do_action( 'wcmo_before_new_registration_email', $user_id, $user, $_POST );

			// Tell the admin
			wcmo_new_registration_email( $user_id );

			wc_add_notice(
				apply_filters(
					'wcmo_pending_user_registration_notice',
					__( 'Please note that your registration requires approval. You\'ll receive an email when your account has been approved.', 'wcmo' )
				),
				'notice'
			);

		}

	}

}
add_action( 'woocommerce_created_customer', 'wcmo_created_customer' );
