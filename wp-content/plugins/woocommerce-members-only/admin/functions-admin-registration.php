<?php
/**
 * Functions for registration on the admin
 * @since 1.10.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The list of additional registration fields
 * @since 1.9.0
 */
function wcmo_get_registration_fields() {

	$fields = array(
		'first_name'	=> array(
			'label'			=> __( 'First Name', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 0
		),
		'last_name'		=> array(
			'label'			=> __( 'Last Name', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 10
		),
		'billing_company'		=> array(
			'label'			=> __( 'Billing Company', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 20
		),
		'billing_address_1'		=> array(
			'label'			=> __( 'Billing Address Line 1', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 30
		),
		'billing_address_2'		=> array(
			'label'			=> __( 'Billing Address Line 2', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 40
		),
		'billing_city'		=> array(
			'label'			=> __( 'Billing City', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 50
		),
		'billing_state'		=> array(
			'label'			=> __( 'Billing State', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 55
		),
		'billing_country'		=> array(
			'label'			=> __( 'Billing Country', 'wcmo' ),
			'type'			=> 'country',
			'priority'	=> 60
		),
		'billing_postcode'		=> array(
			'label'			=> __( 'Billing Postcode', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 70
		),
		'billing_phone'		=> array(
			'label'			=> __( 'Billing Phone', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 80
		),
		'shipping_company'		=> array(
			'label'			=> __( 'Shipping Company', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 120
		),
		'shipping_address_1'		=> array(
			'label'			=> __( 'Shipping Address Line 1', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 130
		),
		'shipping_address_2'		=> array(
			'label'			=> __( 'Shipping Address Line 2', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 140
		),
		'shipping_city'		=> array(
			'label'			=> __( 'Shipping City', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 150
		),
		'shipping_state'		=> array(
			'label'			=> __( 'Shipping State', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 155
		),
		'shipping_country'		=> array(
			'label'			=> __( 'Shipping Country', 'wcmo' ),
			'type'			=> 'country',
			'priority'	=> 160
		),
		'shipping_postcode'		=> array(
			'label'			=> __( 'Shipping Postcode', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 170
		),
		'shipping_phone'		=> array(
			'label'			=> __( 'Shipping Phone', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 180
		),
		'vat_number'		=> array(
			'label'			=> __( 'VAT Number', 'wcmo' ),
			'type'			=> 'text',
			'priority'	=> 200
		),

	);

	return apply_filters( 'wcmo_registration_fields', $fields );

}

/**
 * The list of additional registration field types
 * @since 1.10.0
 */
function wcmo_get_registration_field_tyoes() {

	$types = array(
		'checkbox'	=> __( 'Checkbox', 'wcmo' ),
		'text'			=> __( 'Text', 'wcmo' ),
		'upload'		=> __( 'Upload', 'wcmo' ),
	);

	return apply_filters( 'wcmo_registration_field_types', $types );

}

/**
 * Create a new registration field
 * @since 1.10.0
 */
function wcmo_add_registration_field() {

	// Check nonce
	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'wcmo_registration_fields_nonce' ) ) {
		wp_send_json_error( array( 'nonce_fail' => 1 ) );
	}

	// Check permission
	if( ! current_user_can( 'edit_users' ) ) {
		wp_send_json_error( array( 'capability_fail' => 1 ) );
	}

	$label = isset( $_POST['label'] ) ? $_POST['label'] : false;
	$type = isset( $_POST['type'] ) ? $_POST['type'] : 'text';
	$priority = isset( $_POST['priority'] ) ? $_POST['priority'] : 100;
	$description = isset( $_POST['description'] ) ? esc_html( $_POST['description'] ) : '';

	// Save the extra fields we create in a separate array
	$extra_fields = get_option( 'wcmo_extra_registration_fields', array() );
	$key = sanitize_key( $label );
	$extra_fields[$key] = array(
		'label'				=> $label,
		'type'				=> $type,
		'priority'		=> $priority,
		'description'	=> $description
	);

	update_option( 'wcmo_extra_registration_fields', $extra_fields );

	wp_send_json_success( array( 'field_id' => $key, 'priority' => $priority, 'description' => $description ) );

}
add_action( 'wp_ajax_wcmo_add_registration_field', 'wcmo_add_registration_field' );

/**
 * Create a new registration field
 * @since 1.10.0
 */
function wcmo_delete_registration_field() {

	// Check nonce
	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'wcmo_registration_fields_nonce' ) ) {
		wp_send_json_error( array( 'nonce_fail' => 1 ) );
	}

	// Check permission
	if( ! current_user_can( 'edit_users' ) ) {
		wp_send_json_error( array( 'capability_fail' => 1 ) );
	}

	$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : false;
	if( ! $field_id ) {
		wp_send_json_error( array( 'id_fail' => 1 ) );
	}

	// Delete the field
	$extra_fields = get_option( 'wcmo_extra_registration_fields', array() );

	$key = sanitize_key( $field_id );
	unset( $extra_fields[$key] );

	update_option( 'wcmo_extra_registration_fields', $extra_fields );

	wp_send_json_success( array( 'field_id' => $key ) );

}
add_action( 'wp_ajax_wcmo_delete_registration_field', 'wcmo_delete_registration_field' );

/**
 * Get any registration fields we've created
 */
function wcmo_extra_registration_fields( $fields ) {

	$extra_fields = get_option( 'wcmo_extra_registration_fields', array() );
  $fields = array_merge( $fields, $extra_fields );

  return $fields;

}
add_filter( 'wcmo_registration_fields', 'wcmo_extra_registration_fields' );
