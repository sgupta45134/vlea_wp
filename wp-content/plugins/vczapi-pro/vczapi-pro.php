<?php
/**
 * @link              http://www.deepenbajracharya.com.np
 * @since             1.0.0
 * @package           vczapi-pro
 *
 * Plugin Name:       Video Conferencing with Zoom Pro
 * Plugin URI:        https://www.codemanas.com
 * Description:       Enable recurring meetings as well as enhance more features to zoom from your WordPress dashboard.
 * Version:           1.5.11
 * Author:            CodeManas
 * Author URI:        https://www.codemanas.com/
 * Text Domain:       vczapi-pro
 * Requires PHP:      7.0
 * Requires at least: 5.0
 * Domain Path:       /languages
 */

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies !' );

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_NAME' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_PLUGIN' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_PLUGIN', 'Video Conferencing with Zoom Pro' );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_OVERRIDE_SLUG' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_OVERRIDE_SLUG', 'video-conferencing-zoom-pro' );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION' ) ) {
		define( 'VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION', '1.5.11' );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_FILE_PATH' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_FILE_PATH', __FILE__ );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_DIR_FILE_PATH', plugin_dir_path( __DIR__ ) );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_DIR_PATH' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'VZAPI_ZOOM_PRO_ADDON_DIR_URI' ) ) {
	define( 'VZAPI_ZOOM_PRO_ADDON_DIR_URI', plugin_dir_url( __FILE__ ) );
}

define( 'VIDEO_CONFERENCING_HOST_ASSIGN_PAGE', true );

require_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . '/includes/Bootstrap.php';
function vczapi_recurring_addon_load_textdomain() {
	load_plugin_textdomain( 'vczapi-pro', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'vczapi_recurring_addon_load_textdomain' );