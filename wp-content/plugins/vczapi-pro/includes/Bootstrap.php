<?php

namespace Codemanas\ZoomPro;

use Codemanas\ZoomPro\Backend\Duplicator;
use Codemanas\ZoomPro\Backend\MetaHandler;
use Codemanas\ZoomPro\Backend\Recurring\Recurring;
use Codemanas\ZoomPro\Backend\Registrations\Registrations;
use Codemanas\ZoomPro\Backend\Settings\Settings;
use Codemanas\ZoomPro\Backend\Sync\Sync;
use Codemanas\ZoomPro\Core\Container;
use Codemanas\ZoomPro\Core\CronRegistrar;
use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Core\Listener;
use Codemanas\ZoomPro\Core\Updater;
use Codemanas\ZoomPro\Elementor\Elementor;
use Codemanas\ZoomPro\Frontend\FullCalendar;
use Codemanas\ZoomPro\Frontend\iCal;
use Codemanas\ZoomPro\Frontend\Meetings;
use Codemanas\ZoomPro\Frontend\Shortcodes;
use Codemanas\ZoomPro\Frontend\Webinars;
use Codemanas\ZoomPro\WC\WooCommerce;

/**
 * Class Bootstrap
 *
 * Bootstrap our plugin
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Bootstrap {

	/**
	 * Hold my container
	 *
	 * @var object
	 */
	private $container;

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @var $plugin_dependency_loaded
	 */
	private $plugin_dependency_loaded;

	/**
	 * MINIUM ZOOM PLUGIN VERSION REQUIRED
	 */
	const MINIMUM_ZOOM_VERSION = '3.8.10';

	/**
	 * MINIMUM ZOOM ADDON FOR WOOCOMMERCE VERSION REQUIRED
	 */
	const MINIMUM_WC_ZOOM_ADDON_VERSION = '2.2.4';

	/**
	 * MINIMUM ZOOM ADDON FOR WOOCOMMERCE BOOKINGS VERSION REQUIRED
	 */
	const MINIMUM_WC_BOOKING_ZOOM_ADDON_VERSION = '2.1.8';

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		$this->autoloader();

		register_activation_hook( VZAPI_ZOOM_PRO_ADDON_FILE_PATH, [ $this, 'activate' ] );
		add_filter( 'plugin_action_links', array( $this, 'action_link' ), 10, 2 );

		$loaded = $this->load_dependences();
		if ( $loaded ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}
	}

	/**
	 * Bootstrap the plugin and load necessary components from here on.
	 */
	public function load_dependences() {
		$this->plugin_dependency_loaded = true;

		if ( ! in_array( 'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php', get_option( 'active_plugins' ) ) ) {
			add_action( 'admin_notices', array( $this, 'zoom_plugin_notice' ) );

			return false;
			//return early - so we don't have to go on with other things
		}

		$zoom_plugin              = get_file_data( VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH . 'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php', array( 'Version' => 'Version' ), 'plugin' );
		$woocommerce_addon_exists = file_exists( VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH . 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php' );
		if ( $woocommerce_addon_exists ) {
			$woocommerce_addon = get_file_data( VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH . 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php', array( 'Version' => 'Version' ), 'plugin' );

		}

		$wooBookings_addon = file_exists( VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH . 'vczapi-woocommerce-addon/vczapi-woo-addon.php' );
		if ( $wooBookings_addon ) {
			$wooBookings_addon = get_file_data( VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH . 'vczapi-woo-addon/vczapi-woo-addon.php', array( 'Version' => 'Version' ), 'plugin' );
		}


		if ( ! empty( $zoom_plugin ) && ! version_compare( $zoom_plugin['Version'], self::MINIMUM_ZOOM_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'zoom_plugin_dependency_notice' ) );
			$this->plugin_dependency_loaded = false;
		}

		if ( ! empty( $zoom_plugin ) && in_array( 'vczapi-woo-addon/vczapi-woo-addon.php', get_option( 'active_plugins' ) ) && $wooBookings_addon && ! version_compare( $wooBookings_addon['Version'], self::MINIMUM_WC_BOOKING_ZOOM_ADDON_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_booking_addon_dependency_notice' ) );
			$this->plugin_dependency_loaded = false;
		}

		if ( ! empty( $woocommerce_addon ) && in_array( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php', get_option( 'active_plugins' ) ) && $woocommerce_addon_exists && ! version_compare( $woocommerce_addon['Version'], self::MINIMUM_WC_ZOOM_ADDON_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_addon_dependency_notice' ) );
			$this->plugin_dependency_loaded = false;
		}


		return $this->plugin_dependency_loaded;
	}

	/**
	 * PLUGIN REQUIRES FREE VERSION BUT WITH SPECIFIC VERSION NOTICE
	 */
	public function zoom_plugin_dependency_notice() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater. Please Update or Activate or Download from %4$s', 'vczapi-pro' ),
			'<strong>' . VZAPI_ZOOM_PRO_ADDON_PLUGIN . '</strong>',
			'<strong>Video Conferencing with Zoom</strong>',
			self::MINIMUM_ZOOM_VERSION,
			'<a href="https://wordpress.org/plugins/video-conferencing-with-zoom-api/">' . esc_html__( 'WordPress repository', 'vczapi-pro' ) . '</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * PLUGIN REQUIRES ZOOM WC ADDON VERSION NOTICE
	 */
	public function wc_addon_dependency_notice() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater. Please update your existing plugin version from plugins page.', 'vczapi-pro' ),
			'<strong>' . VZAPI_ZOOM_PRO_ADDON_PLUGIN . '</strong>',
			'<strong>Zoom Integration for WooCommerce</strong>',
			self::MINIMUM_WC_ZOOM_ADDON_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * PLUGIN REQUIRES ZOOM WC BOOKING ADDON VERSION NOTICE
	 */
	public function wc_booking_addon_dependency_notice() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater. Please update your existing plugin version from plugins page.', 'vczapi-pro' ),
			'<strong>' . VZAPI_ZOOM_PRO_ADDON_PLUGIN . '</strong>',
			'<strong>Zoom Integration for WooCommerce Bookings</strong>',
			self::MINIMUM_WC_BOOKING_ZOOM_ADDON_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Plugin requires FREE VERSION NOTICE
	 */
	public function zoom_plugin_notice() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			esc_html__( '"%1$s" requires "%2$s" plugin. Please Download and Activate from %3$s', 'vczapi-pro' ),
			'<strong>' . VZAPI_ZOOM_PRO_ADDON_PLUGIN . '</strong>',
			'<strong>Video Conferencing with Zoom</strong>',
			'<a href="https://wordpress.org/plugins/video-conferencing-with-zoom-api/">' . esc_html__( 'WordPress repository', 'vczapi-pro' ) . '</a>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Trigger on plugin activation
	 */
	public function activate() {
		$this->load_dependences();

		$exists = Fields::get_option( 'settings' );
		//If already defined the do no run again
		if ( empty( $exists ) ) {
			$options = array(
				'registraion_email' => '',
				'emails'            => [
					'confirmation_email' => '<p>Hi {customer_name},</p><p>Thank you for registering for {meeting_topic}.</p><p><strong>Start Time:</strong> {meeting_time}</p><p>Join from PC, Mac, Linux, iOS or Android: <a href="{meeting_join_link}">Join</a></p><p><strong>Password:</strong> {meeting_password}</p><p><strong>Note:</strong> This link should not be shared with others; it is unique to you.</p>'
				]
			);
			Fields::set_option( 'settings', $options );
		}

		//Install Cron Tasks
		CronRegistrar::activate_cron();
	}

	/**
	 * Load Dependencies
	 */
	public function init() {
		$this->container = new Container();
		$this->container->get( Factory::class );
		$this->container->get( CronRegistrar::class );
		$this->container->get( TemplateFunctions::class );
		if ( is_admin() ) {
			$this->container->get( MetaHandler::class );
			$this->container->get( Recurring::class );
			$this->container->get( Registrations::class );
			$this->container->get( Duplicator::class );

			if ( current_user_can( 'manage_options' ) ) {
				$this->container->get( Settings::class );
				//Run Updater
				$this->updater( $this->container->get( Fields::class ) );
			}

			$this->container->get( Backend\Ajax::class );
			$this->container->get( Sync::class );
		}

		$this->container->get( Elementor::class );
		$this->container->get( Shortcodes::class );
		$this->container->get( Frontend\Ajax::class );
		FullCalendar::get_instance();
		Webinars::get_instance();
		Meetings::get_instance();
		$this->container->get( iCal::class );

		if ( Helpers::checkWooBookingsAddonActive() || Helpers::checkWooAddonActive() ) {
			WooCommerce::get_instance();
		}

		//Set Listener Class
		$this->container->get( Listener::class );

		$settings = Fields::get_option( 'settings' );
		if ( ! empty( $settings ) && ! empty( $settings['create_user_on_registration'] ) ) {
			$this->container->get( User::class );
		}
	}

	/**
	 * Run the updater in admin
	 *
	 * @param Fields $fields
	 */
	private function updater( Fields $fields ) {
		$updater = new Updater( $fields->store_url(), VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'vczapi-pro.php', array(
			'version' => VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION,
			'license' => Fields::get_option( 'license_key' ),
			'author'  => 'CodeManas',
			'item_id' => $fields->item_id(),
			'beta'    => false,
		) );

		$updater->check();
	}

	/**
	 * Enqueue Admin Scripts
	 *
	 * @param $hook
	 */
	public function admin_scripts( $hook ) {
		if ( get_post_type() === "zoom-meetings" || get_post_type() === "product" || $hook === "zoom-meetings_page_zoom-video-conferencing-settings" ) {
			wp_enqueue_style( 'vczapi-pro-admin-style', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/backend/css/style.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, 'all' );

			wp_register_script( 'video-conferencing-with-zoom-api-moment', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/moment/moment.min.js', array( 'jquery' ), ZVC_PLUGIN_VERSION, true );
			wp_enqueue_script( 'vczapi-pro-admin-script',
				VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/backend/js/script.min.js',
				[ 'jquery', 'video-conferencing-with-zoom-api-moment' ],
				VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION,
				true );

			// Localize the script with new data
			$translation_array = apply_filters( 'vczapi_recurring_addon_txtext', array(
				'repeat_every_day'   => __( 'Day', 'vczapi-pro' ),
				'repeat_every_week'  => __( 'Week', 'vczapi-pro' ),
				'repeat_every_month' => __( 'Month', 'vczapi-pro' ),
			) );
			wp_localize_script( 'vczapi-pro-admin-script', 'recurring_strings', $translation_array );
		}
	}

	public function autoloader() {
		require_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'vendor/autoload.php';
	}

	/**
	 * Enqueue  Scripts
	 */
	public function scripts() {
		$min = ( SCRIPT_DEBUG == true ) ? '' : '.min';
		$ver = VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION;
		wp_enqueue_style( 'vczapi-pro', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/frontend/css/style' . $min . '.css', false, $ver, 'all' );
		wp_register_script( 'vczapi-pro', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/frontend/js/script' . $min . '.js', array( 'jquery' ), $ver, true );

		$translations = apply_filters( 'vczapi_pro_translation_strings', [
			'first_name_required' => __( 'First Name is required !', 'vczapi-pro' ),
			'last_name_required'  => __( 'Last Name is required !', 'vczapi-pro' ),
			'email_required'      => __( 'Valid email address is required !', 'vczapi-pro' )
		] );

		//Load ajax url and curcial data.
		$ajax      = array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'current_page' => esc_url( Helpers::get_current_page_uri() )
		);
		$localized = array_merge( $translations, $ajax );
		wp_localize_script( 'vczapi-pro', 'vczapi_pro', $localized );
		if ( is_singular( 'zoom-meetings' ) ) {
			wp_enqueue_script( 'vczapi-pro' );
		}
	}

	/**
	 * Add Action links to plugins page.
	 *
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	public function action_link( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = VZAPI_ZOOM_PRO_ADDON_NAME;
		}

		if ( $plugin == $plugin_file ) {
			$settings = array( 'settings' => '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=pro-licensing' ) . '">' . __( 'Settings', 'vczapi-pro' ) . '</a>' );

			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}
}

add_action( 'plugins_loaded', 'Codemanas\ZoomPro\Bootstrap::get_instance', 991 );