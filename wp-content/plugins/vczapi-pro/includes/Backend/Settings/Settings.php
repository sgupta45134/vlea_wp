<?php

namespace Codemanas\ZoomPro\Backend\Settings;

use Codemanas\ZoomPro\Backend\Registrations\Registrations;
use Codemanas\ZoomPro\Backend\Settings\Licensing;
use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;
use Codemanas\ZoomWCFM\Frontend\Product;

/**
 * Class Settings
 *
 * Add setting options to settings page
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro\Backend
 */
class Settings {

	private $licensing;

	private $settings;

	/**
	 * Settings constructor.
	 *
	 * @param \Codemanas\ZoomPro\Backend\Settings\Licensing $licensing
	 */
	public function __construct( Licensing $licensing ) {
		$this->licensing = $licensing;
		$this->call_hooks();
	}

	/**
	 * Calling wordpress hooks
	 */
	public function call_hooks() {
		add_action( 'vczapi_admin_tabs_heading', array( $this, 'settings_tab' ) );
		add_action( 'vczapi_admin_tabs_content', array( $this, 'settings_body' ) );
	}

	/**
	 * Add licensing tab to admin area
	 *
	 * @param $active_tab
	 */
	public function settings_tab( $active_tab ) {
		?>
        <a href="<?php echo add_query_arg( array( 'tab' => 'pro-licensing' ) ); ?>" class="nav-tab <?php echo ( 'pro-licensing' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
			<?php esc_html_e( 'PRO', 'vczapi-pro' ); ?>
        </a>
        <a href="<?php echo add_query_arg( array( 'tab' => 'webhooks' ) ); ?>" class="nav-tab <?php echo ( 'webhooks' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
			<?php esc_html_e( 'Webhooks (Beta)', 'vczapi-pro' ); ?>
        </a>
		<?php
	}

	/**
	 * Show Licensing tab body section
	 *
	 * @param $active_tab
	 *
	 * @throws \Exception
	 */
	public function settings_body( $active_tab ) {
		$section = isset( $_GET['section'] ) ? $_GET['section'] : false;
		?>
        <div class="vczapi-settings-admin-wrap vczapi-settings-admin-support">
			<?php
			if ( 'pro-licensing' === $active_tab ) {
				$this->sub_menu_section_head( $active_tab, $section );
				echo '<div class="vczapi-settings-admin-support-bg">';
				$this->sub_menu_section_body( $active_tab, $section );
				echo '</div>';
			} else if ( 'webhooks' === $active_tab ) {
				$this->wehooks_html();
			}
			?>
        </div>
		<?php
	}

	public function wehooks_html() {
		$this->save_settings();
		$this->settings = Fields::get_option( 'settings' );
		include_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Backend/Settings/tpl-webhooks.php';
	}

	/**
	 * Show Sub menu sections
	 *
	 * @param $active_tab
	 * @param $section
	 */
	public function sub_menu_section_head( $active_tab, $section ) {
		?>
        <ul class="subsubsub sub-vczapi-pro-menu-admin">
            <li>
                <a href="<?php echo add_query_arg( array( 'section' => 'settings' ) ); ?>" class="<?php echo empty( $section ) || ( $active_tab === "pro-licensing" && $section === "settings" ) ? 'current' : false; ?>">Settings</a>
            </li>
            <li>
                <a href="<?php echo add_query_arg( array( 'section' => 'email-templates' ) ); ?>" class="<?php echo ! empty( $section ) && $active_tab === "pro-licensing" && $section === "email-templates" ? 'current' : false; ?>">Email</a>
            </li>
            <li>
                <a href="<?php echo add_query_arg( array( 'section' => 'licensing' ) ); ?>" class="<?php echo ! empty( $section ) && $active_tab === "pro-licensing" && $section === "licensing" ? 'current' : false; ?>">Licensing</a>
            </li>
        </ul>
		<?php
	}

	/**
	 * Show sub menu body HTML
	 *
	 * @param $active_tab
	 * @param $section
	 */
	public function sub_menu_section_body( $active_tab, $section ) {
		if ( empty( $section ) || ( $active_tab === "pro-licensing" && $section === "settings" ) ) {
			wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/js/select2.min.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
			wp_enqueue_style( 'video-conferencing-with-zoom-api-select2', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/css/select2.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );

			$this->save_settings();
			$this->settings = Fields::get_option( 'settings' );
			include_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Backend/Settings/tpl-settings.php';
		} else if ( ! empty( $section ) && $active_tab === "pro-licensing" && $section === "email-templates" ) {
			EmailTemplates::get_instance()->output_html();
		} else if ( ! empty( $section ) && $active_tab === "pro-licensing" && $section === "licensing" ) {
			$license = $this->licensing;
			$license = $license::get_instance();
			$license->show_license_form();
		}
	}

	/**
	 * Save settings page
	 */
	public function save_settings() {
		if ( isset( $_POST['save_registration_details'] ) && current_user_can( 'manage_options' ) ) {
			$settings                                = Fields::get_option( 'settings' );
			$settings['registraion_email']           = sanitize_text_field( filter_input( INPUT_POST, 'registration_email' ) );
			$settings['hide_ical_links']             = sanitize_text_field( filter_input( INPUT_POST, 'hide_ical_links' ) );
			$settings['hide_ical_links_woocommerce'] = sanitize_text_field( filter_input( INPUT_POST, 'reminder_emails' ) );
			$settings['inline_registration_form']    = sanitize_text_field( filter_input( INPUT_POST, 'inline_registration_form' ) );
			$settings['create_user_on_registration'] = sanitize_text_field( filter_input( INPUT_POST, 'create_user_on_registration' ) );
			$settings['reminder_emails_registrants'] = filter_input( INPUT_POST, 'reminder_emails', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$raw_registration_fields                 = filter_input( INPUT_POST, 'meeting_registration_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$settings['meeting_registration_fields'] = Factory::filter_registration_submitted_data( $raw_registration_fields );
			Fields::set_option( 'settings', $settings );

			Helpers::set_admin_notice( 'updated', "Settings Updated !" );
		}

		if ( isset( $_POST['save_verification_code'] ) && current_user_can( 'manage_options' ) ) {
			$settings                      = Fields::get_option( 'settings' );
			$settings['verification_code'] = filter_input( INPUT_POST, 'verification_code' );
			Fields::set_option( 'settings', $settings );

			Helpers::set_admin_notice( 'updated', "Settings Updated !" );
		}
	}
}