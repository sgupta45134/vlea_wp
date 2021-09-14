<?php
/**
 * Functions for working with user roles
 * @since 1.3.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate an existing user role
 * @since 1.3.0
 */
function wcmo_duplicate_user_role() {

	if( empty( $_POST['wcmo_new_role_name'] ) ) {
		if( isset( $_POST['wcmo_duplicate_user_role'] ) ) {
			add_action( 'admin_notices', function() {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Please enter a name for the new role.', 'wcmo' )
				);
			} );
		}
		return;
	}

	if( empty( $_POST['wcmo_existing_user_roles'] ) ) {
		if( isset( $_POST['wcmo_duplicate_user_role'] ) ) {
			add_action( 'admin_notices', function() {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Please select the role to duplicate.', 'wcmo' )
				);
			} );
		}
		return;
	}

	if( ! isset( $_POST['wcmo_user_roles_nonce'] ) || ! wp_verify_nonce( $_POST['wcmo_user_roles_nonce'], 'wcmo_user_roles_nonce' ) ) {
		return;
	}

	$selected_role = $_POST['wcmo_existing_user_roles'];
	$new_name = $_POST['wcmo_new_role_name'];
	$slug = sanitize_key( $new_name );

	// Duplicate the selected role
	global $wp_roles;
	if( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	// Check if a role already exists with this name
	$role = $wp_roles->get_role( $new_name );
	if( $role ) {
		add_action( 'admin_notices', function() {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				__( 'A role with that name already exists.', 'wcmo' )
			);
		} );
		return;
	}

	// Create a duplicate role
	$duplicate = $wp_roles->get_role( $selected_role );
	$wp_roles->add_role( $slug, $new_name, $duplicate->capabilities );

	// Save a list of user created roles
	$user_roles = get_option( 'wcmo_user_created_roles', array() );
	$user_roles[$slug] = $new_name;
	update_option( 'wcmo_user_created_roles', $user_roles );

	add_action( 'admin_notices', function() {
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			__( 'User role created.', 'wcmo' )
		);
	} );

}
add_action( 'admin_init', 'wcmo_duplicate_user_role' );

/**
 * Edit an existing user role
 * @since 1.3.0
 */
function wcmo_edit_user_role() {

	if( empty( $_POST['wcmo_edit_user_role'] ) ) {
		if( isset( $_POST['wcmo_update_user_role'] ) ) {
			add_action( 'admin_notices', function() {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Please select the role to edit.', 'wcmo' )
				);
			} );
		}
		return;
	}

	if( empty( $_POST['wcmo_edit_role_name'] ) ) {
		if( isset( $_POST['wcmo_update_user_role'] ) ) {
			add_action( 'admin_notices', function() {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( 'Please enter the name of the role.', 'wcmo' )
				);
			} );
		}
		return;
	}

	if( ! isset( $_POST['wcmo_update_user_roles_nonce'] ) || ! wp_verify_nonce( $_POST['wcmo_update_user_roles_nonce'], 'wcmo_update_user_roles_nonce' ) ) {
		return;
	}

	$selected_role = $_POST['wcmo_edit_user_role'];
	$specified_capabilities = ! empty( $_POST['wcmo_capabilities'] ) ? $_POST['wcmo_capabilities'] : array();

	global $wp_roles;
	if( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	// Get the role we've picked
	$role = $wp_roles->get_role( $selected_role );

	// Reset the capabilities
	$capabilities = wcmo_get_all_capabilities();

	if( $capabilities ) {
		foreach( $capabilities as $capability=>$value ) {
			// Iterate through each capability and remove it from the role, unless it's specified
			if( in_array( $capability, $specified_capabilities ) ) {
				$role->add_cap( $capability );
			} else {
				$role->remove_cap( $capability );
			}
		}
	}

	add_action( 'admin_notices', function() {
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			__( 'User role updated.', 'wcmo' )
		);
	} );

}
add_action( 'init', 'wcmo_edit_user_role' );

/**
 * Get role capabilities and other details
 * @since 1.3.0
 */
function wcmo_get_role_details() {

	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'wcmo_update_user_roles_nonce' ) ) {
		wp_send_json_error( array( 'nonce_fail' => 1 ) );
	}

	$selected_role = $_POST['role'];
	$slug = '';
	$name = '';
	$capabilities = array();

	if( $selected_role ) {
		// Get details
		global $wp_roles;
		if( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$role = $wp_roles->get_role( $selected_role );
		$capabilities = $role->capabilities;
		$slug = $role->name;
		$name = translate_user_role( $wp_roles->roles[$slug]['name'] ); // The title of the role
	}

	wp_send_json_success( array( 'name' => $name, 'capabilities' => $capabilities, 'slug' => $slug ) );

}
add_action( 'wp_ajax_wcmo_get_role_details', 'wcmo_get_role_details' );

/**
 * Get all role
 * @since 1.3.0
 */
function wcmo_get_all_capabilities() {
	$capabilities = array();
	global $wp_roles;
	$roles = $wp_roles->roles;
	if( $roles ) {
		foreach( $roles as $role ) {
			if( $role['capabilities'] ) {
				$capabilities = array_merge( $capabilities, $role['capabilities'] );
			}
		}
	}
	return $capabilities;
}
