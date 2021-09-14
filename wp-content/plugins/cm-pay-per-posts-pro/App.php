<?php

namespace com\cminds\payperposts;

use com\cminds\payperposts\controller\MainPayboxController;
use com\cminds\payperposts\controller\SettingsController;
use com\cminds\payperposts\helper\ScriptsHelper;
use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\helper\PayboxHideContent;

class App {

	const VERSION = '2.5.3';
	const PREFIX = 'CMPPP';
	const MENU_SLUG = 'cmppp';
	const BASE_NAMESPACE = 'com\cminds\payperposts';
	const PLUGIN_NAME = 'CM Pay Per Posts';
	const PLUGIN_WEBSITE = 'https://plugins.cminds.com/cm-pay-per-post-plugin-for-wordpress/';
	const TEXT_DOMAIN = 'cm-pay-per-posts';
	const LICENSING_SLUG = 'c-m-pay-per-posts-pro';

	static private $path;
	static private $pluginFile;

	static function bootstrap( $pluginFile ) {

		static::$pluginFile = $pluginFile;
		static::$path       = dirname( $pluginFile );

		add_action( 'activated_plugin', function () {
			update_option( App::prefix( 'plugin_error' ), ob_get_contents() );
		} );

		// Auto-load
		spl_autoload_register( array( __CLASS__, 'autoload' ) );

		// Licensing API
		if ( static::isPro() ) {
			require App::path( 'package/cminds-pro.php' );
		}

		// Class bootstraping
		$classToBootstrap = array_merge( static::getClassNames( 'controller' ), static::getClassNames( 'model' ) );
		if ( static::isLicenseOk() ) {
			$classToBootstrap = array_merge( $classToBootstrap, static::getClassNames( 'shortcode' ), static::getClassNames( 'metabox' ) );
		}
		foreach ( $classToBootstrap as $className ) {
			$method = array( $className, 'bootstrap' );
			if ( method_exists( $className, 'bootstrap' ) and is_callable( $method ) ) {
				call_user_func( $method );
			}
		}

		// Other actions
		add_action( 'init', array( get_called_class(), 'init' ), 1 );
		add_action( 'admin_menu', array( get_called_class(), 'admin_menu' ) );

		add_action( "wp_ajax_cmppp_init_user_time_offset", array( get_called_class(), 'cmppp_init_user_time_offset' ) );
		add_action( "wp_ajax_nopriv_cmppp_init_user_time_offset", array(
			get_called_class(),
			'cmppp_init_user_time_offset'
		) );

		add_action( 'show_user_profile', array( get_called_class(), 'displayAdminCutPoints' ) );
		add_action( 'edit_user_profile', array( get_called_class(), 'displayAdminCutPoints' ) );
		add_action( 'profile_update', array( get_called_class(), 'saveAdminCutPoints' ) );

		$is_hide_content = PayboxHideContent::isHidePageContent();
		if ( $is_hide_content == Settings::HIDE_FULL_PAGE_CONTENT || $is_hide_content == Settings::HIDE_SPECIFIED_BLOCK ) {
			PayboxHideContent::init();
		}
	}

	static function init() {
		wp_enqueue_style( 'dashicons' );

		$prefix = 'cmppp-';
		ScriptsHelper::registerScripts( ScriptsHelper::getScriptList( $prefix ), $prefix );

		wp_enqueue_style( 'cmppp-frontend' );
		wp_enqueue_style( 'cmppp-common' );

		wp_enqueue_script( 'cmppp-utils' );
		wp_enqueue_script( 'cmppp-frontend' );

		MainPayboxController::init();

		// Add default admin cut when points charging to the author's wallet after buying post
        // But only for users who can publish posts
		if(Settings::getOption(Settings::OPTION_PERCENTAGE) == 'percentage_on'){

		    $args = array (
			    'number' => -1
            );
		    $users_query = new \WP_User_Query($args);
		    $users = $users_query->get_results();

		    $defaultPercent = Settings::getOption(Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR);
			if ($defaultPercent > 100) {
				$defaultPercent = 100;
			} else if ($defaultPercent < 1) {
				$defaultPercent = 1;
			}

		    foreach ($users as $user) {
			    if (isset($user->allcaps['publish_posts']) && empty(get_user_meta($user->ID, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR)[0]) ) {
				    update_user_meta( $user->ID, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR, $defaultPercent );
                }
            }
        }
	}


	static function getClassNames( $namespaceFragment ) {
		$files = scandir( App::path( $namespaceFragment ) );
		foreach ( $files as &$name ) {
			if ( preg_match( '/^([a-zA-Z0-9]+)\.php$/', $name, $match ) ) {
				$name = App::namespaced( $namespaceFragment . '\\' . $match[1] );
			} else {
				$name = null;
			}
		}

		return array_filter( $files );
	}

	static function autoload( $name ) {
		if ( substr( $name, 0, strlen( __NAMESPACE__ ) ) == __NAMESPACE__ ) {
			$path  = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $name, strlen( __NAMESPACE__ ) + 1, 9999 ) );
			$check = array( App::path( $path ), App::path( 'core/' . $path ) );
			foreach ( $check as $file ) {
				$file .= '.php';
				if ( file_exists( $file ) and is_readable( $file ) ) {
					require_once $file;

					return;
				}
			}
		}
	}

	static function admin_menu() {
		$name = App::getPluginName( true );
		$page = add_menu_page( $name, $name, 'manage_options', static::MENU_SLUG, function ( $q ) {
			return;
		}, '', 67343 );
	}

	static function path( $path = '' ) {
		return static::$path . DIRECTORY_SEPARATOR . $path;
	}

	static function prefix( $value ) {
		return static::PREFIX . $value;
	}

	static function url( $url ) {
		return trailingslashit( plugins_url( '', static::$pluginFile ) ) . $url;
	}

	static function namespaced( $name ) {
		return static::BASE_NAMESPACE . '\\' . $name;
	}

	static function shortClassName( $name, $suffix = '' ) {
		preg_match( '#^(\w+\\\\)*(\w+)' . $suffix . '$#', $name, $match );
		if ( ! empty( $match[2] ) ) {
			return $match[2];
		}
	}

	static function isPro() {
		return file_exists( App::path( 'package/cminds-pro.php' ) );
	}

	static function isLicenseOk() {
		global $CMPPP_isLicenseOk;

		return ( ! static::isPro() or $CMPPP_isLicenseOk );
	}

	static function getPluginName( $full = false ) {
		return static::PLUGIN_NAME . ( ( $full && App::isPro() ) ? ' Pro' : '' );
	}

	static function getPluginFile() {
		return static::$pluginFile;
	}

	static function cmppp_init_user_time_offset() {
		Storage::set_user_time_offset( $_POST['user_time_offset'] ?? false );
		echo json_encode( [ 'status' => 'ok' ] );
		wp_die();
	}

	static function displayAdminCutPoints ($user) {
		ob_start();
        if(current_user_can('administrator')) {
            if(Settings::getOption( Settings::OPTION_PERCENTAGE) == 'percentage_on' && user_can( $user->ID, 'publish_posts')){ ?>
                <h3>
                    <?php
                    echo self::PLUGIN_NAME;
                    ?>
                </h3>
                <table class="form-table">
                    <tr>
                        <th>
                            <?php _e( 'Author\'s share (%)' ); ?>
                        </th>
                        <td>
                            <input
                                name="admin_cut_percent"
                                type="text"
                                id="admin_cut_percent"
                                value="<?php echo get_user_meta($user->ID, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR)[0] ; ?>"
                            />
                        </td>
                    </tr>
                </table>
            <?php
            }
        }
	}

	static function saveAdminCutPoints ($user_id) {
		if(Settings::getOption( Settings::OPTION_PERCENTAGE) == 'percentage_on' && user_can( $user_id, 'publish_posts')){

		    $userPercent = $_POST['admin_cut_percent'];
		    if ($userPercent > 100) {
		        $userPercent = 100;
		    } else if ($userPercent < 1) {
		        $userPercent = 1;
		    }
			update_user_meta( $user_id, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR, $userPercent );
        }
	}

}
