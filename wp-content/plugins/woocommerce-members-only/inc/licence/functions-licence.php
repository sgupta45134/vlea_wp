<?php

/**
 * Functions for EDD SL
 */

define( 'WCMO_STORE_URL', 'https://pluginrepublic.com' );

// the download ID for the product in Easy Digital Downloads
define( 'WCMO_ITEM_ID', 33399 );
define( 'WCMO_ITEM_NAME', 'WooCommerce Members Only' );

// the name of the settings page for the license input to be displayed
define( 'WCMO_LICENSE_PAGE', 'wcmo_lk' );

if( ! class_exists( 'WCMO_Updater' ) ) {
	// load our custom updater
	include( WCMO_PLUGIN_DIR . '/inc/licence/class-wcmo-updater.php' );
}

function wcmo_plugin_updater( $license_key='' ) {

	if( ! $license_key ) {
		$license_key = trim( get_option( 'wcmo_licence_key' ) );
	}

	// setup the updater
	$wcmo_updater = new WCMO_Updater( WCMO_STORE_URL, WCMO_FILE,
		array(
			'version' => WCMO_PLUGIN_VERSION,
			'license' => $license_key,
			'item_id' => WCMO_ITEM_ID,
			'author'  => 'Plugin Republic',
			'beta'    => false,
		)
	);
}
add_action( 'admin_init', 'wcmo_plugin_updater', 0 );

/**
 * Activate the licence
 */
function wcmo_activate_license() {
	if( ! isset( $_POST['wcmo_licence_key'] ) || ! isset( $_POST['wcmo_licence_key_nonce'] ) || ! wp_verify_nonce( $_POST['wcmo_licence_key_nonce'], 'wcmo_licence_key_nonce' ) ) {
		return;
	}
	$license = trim( $_POST['wcmo_licence_key'] );
	wcmo_do_license_activation( $license );
}
add_action( 'admin_init', 'wcmo_activate_license' );

/**
 * Activate the license
 */
function wcmo_daily_check_license() {
	$license = trim( get_option( 'wcmo_licence_key' ) );
	wcmo_do_license_activation( $license );
}
// Changed to weekly 1.9.6
add_action( 'wp_site_health_scheduled_check', 'wcmo_daily_check_license' );

/**
 * Activate the licence
 */
function wcmo_do_license_activation( $license ) {

	wcmo_plugin_updater( $license );

	// data to send in our API request
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => $license,
		'item_name'  => urlencode( WCMO_ITEM_NAME ),
		'url'        => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( WCMO_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$message = __( 'An error occurred, please try again.' );
		}

	} else {

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( false === $license_data->success ) {

			$message = __( 'Licence activation: ', 'wcmo' );

			switch( $license_data->error ) {

				case 'expired' :

					$message .= sprintf(
						__( 'your licence key expired on %s.' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;

				case 'disabled' :
				case 'revoked' :

					$message .= __( 'your licence key has been disabled.' );
					break;

				case 'missing' :

					$message .= __( 'the licence key is missing.' );
					break;

				case 'invalid' :
				case 'site_inactive' :

					$message .= __( 'the licence is not active for this URL.' );
					break;

				case 'item_name_mismatch' :

					$message .= sprintf( __( 'this appears to be an invalid licence key for %s.' ), WCMO_ITEM_NAME );
					break;

				case 'no_activations_left':

					$message .= __( 'your licence key has reached its activation limit.' );
					break;

				default :

					$message .= __( 'an error occurred, please try again.' );
					break;
			}

			$message .= __( ' Please ensure you enter the correct licence key in order to receive updates.', 'wcmo' );


		} else {
			$message = false;
		}

		update_option( 'wcmo_license_message', $message );

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'wcmo_license_status', $license_data->license );

	}
}


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function wcmo_deactivate_license() {

	if( ! isset( $_POST['wcmo_deactivate_licence_key'] ) || ! isset( $_POST['wcmo_licence_key_nonce'] ) || ! wp_verify_nonce( $_POST['wcmo_licence_key_nonce'], 'wcmo_licence_key_nonce' ) ) {
		return;
	}

	// retrieve the license from the database
	$license = trim( get_option( 'wcmo_licence_key' ) );

	// data to send in our API request
	$api_params = array(
		'edd_action' => 'deactivate_license',
		'license'    => $license,
		'item_name'  => urlencode( WCMO_ITEM_NAME ), // the name of our product in EDD
		'url'        => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( WCMO_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$message = __( 'An error occurred, please try again.' );
		}

	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// $license_data->license will be either "deactivated" or "failed"
	if( $license_data->license == 'deactivated' ) {
		update_option( 'wcmo_license_status', 'deactivated' );
	}

}
add_action('admin_init', 'wcmo_deactivate_license');

function wcmo_check_license() {
	$status = trim( get_option( 'wcmo_license_status' ) );
	return $status;
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function wcmo_admin_notices() {

	$status = wcmo_check_license();
	$message = get_option( 'wcmo_license_message' );

	if ( ! empty( $message ) ) {

		$licence = sprintf(
			'<p>%s</p>',
			sprintf(
				__( 'Your licence number will be on the email you were sent with the download link or on <a target="_blank" href="%s">your account page</a>.', 'pewc' ),
				esc_url( WCMO_STORE_URL . '/my-account/' )
			)
		);

		$settings_url = wcmo_get_settings_url();
		$link = sprintf(
			__( '<p><a href="%s">Enter your licence key here</a></p>', 'pewc' ),
			$settings_url
		);

		printf(
			'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p>%s%s</div>',
			__( 'WooCommerce Members Only', 'wcmo' ),
			$message,
			$licence,
			$link
		);

	} else if( $status != 'valid' ) {
		// printf(
		// 	'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p></div>',
		// 	__( 'WooCommerce Members Only', 'wcmo' ),
		// 	__( 'Your licence is not currently activated. Please ensure you activate your licence in order to use all the features of this plugin. Your licence number will be on the email you were sent with the download link.', 'wcmo' )
		// );

		$message = sprintf(
			'<p><strong>%s</strong></p>',
			__( 'WooCommerce Members Only', 'wcmo' ),
			$message
		);

		$message .= sprintf(
			'<p>%s</p>',
			sprintf(
				__( 'Your licence is not currently activated. Please ensure you activate your licence in order to receive updates. Your licence number will be on the email you were sent with the download link or on <a target="_blank" href="%s">your account page</a>.', 'pewc' ),
				esc_url( WCMO_STORE_URL . '/my-account/' )
			)
		);

		$settings_url = wcmo_get_settings_url();
		$link = sprintf(
			__( '<p><a href="%s">Enter your licence key here</a></p>', 'wcmo' ),
			$settings_url
		);

		printf(
			'<div class="notice notice-error"><p>%s</p>%s</div>',
			$message,
			$link
		);

	}
}
// add_action( 'admin_notices', 'wcmo_admin_notices' );

function wcmo_get_settings_url() {
	$settings_url = get_admin_url( null, 'admin.php' );
	$settings_url = add_query_arg(
		array(
			'page'		=> 'wc-settings',
			'tab'			=> 'wcmo',
			'section'	=> 'wcmo_lk'
		),
		$settings_url
	);
	return $settings_url;
}
