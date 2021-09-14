<?php

namespace Codemanas\ZoomPro\Backend\Settings;

use Codemanas\ZoomPro\Core\Fields;

/**
 * Activator Class for register the key of the Plugin
 *
 * @package Codemanas\ZoomPro\Admin
 * @author CodeManas, 2020. All Rights reserved.
 * @since 1.0.0
 */
class Licensing {

	/**
	 * @var $status
	 */
	private $status;

	/**
	 * @var $license
	 */
	private $license;

	/**
	 * Hold my beer
	 *
	 * @var Fields
	 */
	private $fields;

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( new Fields );
		}

		return self::$_instance;
	}

	/**
	 * Licensing constructor.
	 *
	 * @param Fields $fields
	 */
	public function __construct( Fields $fields ) {
		$this->fields = $fields;
		$this->call_hooks();
	}

	/**
	 * Calling WordPress hooks
	 */
	public function call_hooks() {
		add_action( 'admin_init', array( $this, 'save_licensing' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Show license tab with forms
	 */
	public function show_license_form() {
		$this->license = Fields::get_option( 'license_key' );
		$this->status  = Fields::get_option( 'license_key_status' );
		include_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Backend/Settings/tpl-licensing.php';
	}

	/**
	 * Check Activate license or deactivate or reset
	 */
	function save_licensing() {
		// run a quick security check
		if ( ( isset( $_POST['vczapi_addon_recurring_activate'] ) || isset( $_POST['vczapi_addon_recurring_deactivate'] ) ) && ! check_admin_referer( '_vczapi_addon_licensing_nonce', 'vczapi_addon_licensing_nonce' ) ) {
			return;
		}

		// listen for our activate button to be clicked
		if ( isset( $_POST['vczapi_addon_recurring_activate'] ) ) {
			$this->activate_license();
		}

		// listen for our deactivate button to be clicked
		if ( isset( $_POST['vczapi_addon_recurring_deactivate'] ) ) {
			$this->deactivate_license();
		}
	}

	/**
	 * Activate the License
	 *
	 * @since 1.0.0
	 * @author Deepen
	 */
	private function activate_license() {
		//Update License Key First
		$license_field = sanitize_text_field( filter_input( INPUT_POST, 'vczapi_addon_recurring_license_key' ) );
		Fields::set_option( 'license_key', $license_field );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => trim( $license_field ),
			'item_id'    => $this->fields->item_id(),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( $this->fields->store_url(), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		$license_data = false;
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'vczapi-pro' );
			}
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $license_data ) && false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired' :
						$message = sprintf( __( 'Your license key expired on %s. Please check your email for renew notice related to your existing license.', 'vczapi-pro' ), date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) ) );
						break;
					case 'disabled' :
					case 'revoked' :
						$message = __( 'Your license key has been disabled.', 'vczapi-pro' );
						break;
					case 'missing' :
						$message = __( 'Invalid license.', 'vczapi-pro' );
						break;
					case 'invalid' :
					case 'site_inactive' :
						$message = __( 'Your license is not active for this URL.', 'vczapi-pro' );
						break;
					case 'item_name_mismatch' :
						$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'vczapi-pro' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN );
						break;
					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.', 'vczapi-pro' );
						break;
					default :
						$message = __( 'An error occurred, please try again.', 'vczapi-pro' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( $this->fields->options_page() );
			$redirect = add_query_arg( array( 'vczapi_pro_addon_msg' => 'false', 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		Fields::set_option( 'license_key_status', ! empty( $license_data->license ) ? $license_data->license : false );
		wp_redirect( admin_url( $this->fields->options_page() ) );
		exit();
	}

	/**
	 * Deactivate licnse
	 *
	 * @since 1.0.0
	 * @author Deepen
	 */
	private function deactivate_license() {
		// retrieve the license from the database
		$license = trim( Fields::get_option( 'license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_id'    => $this->fields->item_id(),
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( $this->fields->store_url(), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'vczapi-pro' );
			}
			$base_url = admin_url( $this->fields->options_page() );
			$redirect = add_query_arg( array( 'vczapi_pro_addon_msg' => 'false', 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( 200 === wp_remote_retrieve_response_code( $response ) && $license_data->success === false && $license_data->license === 'failed' ) {
			$message  = __( 'An error occurred, please try again.', 'vczapi-pro' );
			$base_url = admin_url( $this->fields->options_page() );
			$redirect = add_query_arg( array( 'vczapi_pro_addon_msg' => 'false', 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}

		if ( $license_data->license == 'deactivated' ) {
			Fields::delete_option( 'license_key_status' );
		}

		wp_redirect( admin_url( $this->fields->options_page() ) );
		exit();
	}

	/**
	 * Print Admin Notices
	 */
	function notices() {
		$status = @Fields::get_option( 'license_key_status' );
		if ( empty( $status ) || $status === "invalid" ) {
			?>
            <div class="error">
                <p><strong><?php echo VZAPI_ZOOM_PRO_ADDON_PLUGIN; ?></strong>: Invalid License Key. Add your keys from:
                    <a href="<?php echo admin_url( $this->fields->options_page() ); ?>">Here</a></p>
            </div>
			<?php
		}

		if ( ! empty( $status ) && $status === "expired" ) {
			//Breaks if something is off here.
			?>
            <div class="error">
                <p><strong><?php echo VZAPI_ZOOM_PRO_ADDON_PLUGIN; ?></strong>: Your license key has expired. License key is required to receive
                    future updates and support. Please check your email for renewal notices.</p>
            </div>
			<?php
		}

		if ( isset( $_GET['vczapi_pro_addon_msg'] ) && ! empty( $_GET['message'] ) ) {
			switch ( $_GET['vczapi_pro_addon_msg'] ) {
				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
                    <div class="error">
                        <p><?php echo $message; ?></p>
                    </div>
					<?php
					break;
				case 'true':
				default:
					break;
			}
		}
	}
}