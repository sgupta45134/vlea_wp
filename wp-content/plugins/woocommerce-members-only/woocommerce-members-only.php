<?php
/**
 * Plugin Name: WooCommerce Members Only
 * Author URI: https://pluginrepublic.com
 * Description: Create private stores and membership sites with WooCommerce
 * Author: Plugin Republic
 * Plugin URI: https://pluginrepublic.com/wordpress-plugins/woocommerce-members-only/
 * Version: 1.10.1
 * WC requires at least: 3.0
 * WC tested up to: 5.5
 * @package WCMO
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants
 **/
if ( ! defined( 'WCMO_PLUGIN_VERSION' ) ) {
	define( 'WCMO_PLUGIN_VERSION', '1.10.1' );
}
if ( ! defined( 'WCMO_PLUGIN_URL' ) ) {
	define( 'WCMO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WCMO_PLUGIN_DIR' ) ) {
	define( 'WCMO_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'WCMO_FILE' ) ) {
	define( 'WCMO_FILE', __FILE__ );
}

function wcmo_load_plugin_textdomain() {
  load_plugin_textdomain( 'wcmo', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wcmo_load_plugin_textdomain' );

function wcmo_woocommerce_required_notice() { ?>
	<div class="notice notice-error">
		<p><?php _e( 'WooCommerce Members Only requires WooCommerce to be installed. Please install and activate WooCommerce.', 'pewc' ); ?></p>
	</div>
<?php }

/**
 * Check WooCommerce is active
 * @since 1.9.1
 */
function wcmo_plugins_loaded() {

	if( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wcmo_woocommerce_required_notice' );
	} else {
		add_action( 'init', 'wcmo_init' );
	}

}
add_action( 'plugins_loaded', 'wcmo_plugins_loaded' );

function wcmo_init() {

	require_once dirname( __FILE__ ) . '/admin/class-wcmo-settings.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-categories.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-products.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-multiple-roles.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-payment-methods.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-registration.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-roles.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-schedules.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-settings.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-shipping-methods.php';
	require_once dirname( __FILE__ ) . '/admin/functions-admin-users.php';
	require_once dirname( __FILE__ ) . '/inc/functions-account.php';
	require_once dirname( __FILE__ ) . '/inc/functions-approvals.php';
	require_once dirname( __FILE__ ) . '/inc/functions-categories.php';
	require_once dirname( __FILE__ ) . '/inc/functions-checkout.php';
	require_once dirname( __FILE__ ) . '/inc/functions-helpers.php';
	require_once dirname( __FILE__ ) . '/inc/functions-integrations.php';
	require_once dirname( __FILE__ ) . '/inc/functions-menus.php';
	require_once dirname( __FILE__ ) . '/inc/functions-passwords.php';
	require_once dirname( __FILE__ ) . '/inc/functions-payment-methods.php';
	require_once dirname( __FILE__ ) . '/inc/functions-posts.php';
	require_once dirname( __FILE__ ) . '/inc/functions-products.php';
	require_once dirname( __FILE__ ) . '/inc/functions-restrictions.php';
	require_once dirname( __FILE__ ) . '/inc/functions-registration.php';
	require_once dirname( __FILE__ ) . '/inc/functions-shipping-methods.php';
	require_once dirname( __FILE__ ) . '/inc/functions-subscriptions.php';
	require_once dirname( __FILE__ ) . '/inc/functions-users.php';
	//
	require_once dirname( __FILE__ ) . '/inc/licence/functions-licence.php';

}

/**
 * Enqueue our script
 * @since 1.0.0
 */
function wcmo_enqueue_scripts() {
  wp_register_script( 'wcmo-script', trailingslashit( WCMO_PLUGIN_URL ) . 'assets/js/wcmo-script.js', array( 'jquery' ), WCMO_PLUGIN_VERSION, true );
	wp_enqueue_script( 'wcmo-script' );
}
add_action( 'wp_enqueue_scripts', 'wcmo_enqueue_scripts' );
