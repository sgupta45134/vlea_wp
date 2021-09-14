<?php
/*
Plugin Name: CM Micropayment Platform
Plugin URI: https://www.cminds.com/store/micropayments/
Description: Plugin which allows to create and manage your own in-site virtual currency.
Version: 2.0.5
Author: CreativeMindsSolutions
Author URI: https://www.cminds.com/
Text Domain: cmmp
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('CMMP_DEBUG', 0);
define('CMMP_PATH', dirname(__FILE__));

/**
 * Main plugin class file.
 * What it does:
 * - checks which part of the plugin should be affected by the query frontend or backend and passes the control to the right controller
 * - manages installation
 * - manages uninstallation
 * - defines the things that should be global in the plugin scope (settings etc.)
 * @author CreativeMindsSolutions - Wojtek Kaminski
 */
class CMMicropaymentPlatform {

	public static $calledClassName;
	public static $version;
	protected static $instance = null;

	/**
	 * It has to be variable since it's used in many places throughout the plugin
	 * @var string
	 */
	public static $customTaxonomySlug = 'documenttype';

	/**
	 * User meta key for storing info about subscribed categories
	 * @var string
	 */
	public static $userMetaCategories = 'cm-micropayment-platform-categories';

	/**
	 * User meta key for storing info about subscribed custom taxonomy items
	 * @var string
	 */
	public static $userMetaCustomTaxonomy = 'cm-micropayment-platform-custom-taxonomy';

	/*
	 * Errors
	 */
	public static $errors = array();
	/*
	 * Messages
	 */
	public static $messages = array();

	/**
	 * Main Instance
	 *
	 * Insures that only one instance of class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return The one true CMMicropaymentPlatform
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 */
	public static function instance() {
		$class = __CLASS__;
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof $class ) ) {
			self::$instance = new $class;
		}

		return self::$instance;
	}

	public function __construct() {
		if ( empty( self::$calledClassName ) ) {
			self::$calledClassName = __CLASS__;
		}

		self::setupOptions();
		self::setupConstants();
		self::registerAjaxFunctions();
		self::registerFilters();

		// commented by ramesh https://secure.helpscout.net/conversation/685150806/61571
		//add_action( 'init', array( get_class(), 'initSession' ), 1 );

		add_action( 'init', array( get_class(), 'checkPeriodicCron' ) );
//		add_action( 'init', array( get_class(), 'periodicWalletChanges' ) );
		add_action( 'periodicWalletChanges', array( get_class(), 'periodicWalletChanges' ) );

		require_once CMMP_PLUGIN_DIR . '/package/cminds-pro.php';
		require_once CMMP_PLUGIN_DIR . '/shared/cmmp-const.php';
		require_once CMMP_PLUGIN_DIR . '/shared/functions.php';
		require_once CMMP_PLUGIN_DIR . '/shared/models/label.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-mu.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-woo.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-woo-gateway.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/CmDokan.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-woo-generate-discount.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-edd-gateway.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-edd-grant-points.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-edd-generate-discount.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-edd-paypal-payout.php';
		require_once CMMP_PLUGIN_DIR . '/shared/classes/cmmp-edd-stripe-payout.php';
		require_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
		require_once CMMP_PLUGIN_DIR . '/shared/libs/Update.php';

		new CmDokan();
		add_action( 'init', array( 'CMMPP_Update', 'run' ), 0 );

		global $cmmp_isLicenseOk;

		if ( is_admin() ) {
			/*
			 * Backend
			 */
			require_once CMMP_PLUGIN_DIR . '/backend/cmmp-backend.php';
			$CMMicropaymentPlatformBackendInstance = CMMicropaymentPlatformBackend::instance();
		}

		if ( $cmmp_isLicenseOk ) { 
			/*
			 * Frontend
			 */
			require_once CMMP_PLUGIN_DIR . '/frontend/cmmp-frontend.php';
			$CMMicropaymentPlatformFrontendInstance = CMMicropaymentPlatformFrontend::instance();
		}

		self::applyFilters();
	}

	/**
	 * Return all wallets from remote site with emails
	 * @return bool | array
	 */
	public static function getExternalsWallets() {
		$api_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$url        = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );

		$response = wp_remote_get( "$url/wp-json/cmmpexapi/v1/get_wallets?key=" . $api_key );

		if ( ! is_wp_error( $response ) ) {
			$res = json_decode( $response['body'] );
			if ( $res->success ) {
				return $res->message;
			}
		}

		return false;
	}

	public static function getExternalWalletByName( $wallet_name ) {
		$wallet_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$url        = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );

		$response = wp_remote_get( "$url/wp-json/cmmpexapi/v1/get_wallet_by_name?key=" . $wallet_key . "&wallet_name=$wallet_name" );

		if ( ! is_wp_error( $response ) ) {
			$res = json_decode( $response['body'], false );
			if ( $res->success ) {
				return $res->message;
			}
		}

		return false;
	}

	public static function withdrawExternalWallet( $args ) {
		$wallet_key   = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$url          = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );
		$type         = isset($args['type']) ? $args['type'] : 1;
		$response     = wp_remote_get( "$url/wp-json/cmmpexapi/v1/withdraw_wallet_points_external?key=$wallet_key&wallet_id={$args['wallet_id']}&type=$type&points={$args['points']}" );


		if ( ! is_wp_error( $response ) ) {
			$res = json_decode( $response['body'] );
			if ( $res->success ) {
				return $res->success;
			}
		}

		return false;
	}

	public static function chargeExternalWallet( $wallet_name ) {
		$wallet_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$url        = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );

		$response = wp_remote_get( "$url/wp-json/cmmpexapi/v1/get_wallet_by_name?key=" . $wallet_key . "&wallet_name=$wallet_name" );

		if ( ! is_wp_error( $response ) ) {
			$res = json_decode( $response['body'] );
			if ( $res->success ) {
				return $res->message;
			}
		}

		return false;
	}


	public static function getExternalWalletByEmail( $email ) {
		$wallet_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$url        = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );


		$response = wp_remote_get( "$url/wp-json/cmmpexapi/v1/get_wallet?key=" . $wallet_key . "&email=" . $email );

		if ( ! is_wp_error( $response ) ) {
			$res = json_decode( $response['body'] );
			if ( $res->success ) {
				return $res->message;
			}
		}

		return false;
	}

	private static function setupOptions() {
		add_option( 'cm_micropayment_use_edd_checkout', 0 ); //Option for EDD Checkout
		add_option( 'cm_micropayment_use_woo_checkout', 0 ); //Option for WOO Checkout
		add_option( 'cm_micropayment_assign_wallet_to_customer', 1 ); //Option for assigning wallets to customers
		add_option( 'cm_micropayment_grant_points_to_admin_or_seller', 0 );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @return void
	 * @since 1.1
	 */
	private static function setupConstants() {
		/**
		 * Define Plugin Version
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_VERSION' ) ) {
			define( 'CMMP_VERSION', '1.7.1' );
		}

		/**
		 * Define Plugin Directory
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_PLUGIN_DIR' ) ) {
			define( 'CMMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Define Plugin URL
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_PLUGIN_URL' ) ) {
			define( 'CMMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define Plugin File Name
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_PLUGIN_FILE' ) ) {
			define( 'CMMP_PLUGIN_FILE', __FILE__ );
		}

		/**
		 * Define Plugin Slug name
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_SLUG_NAME' ) ) {
			define( 'CMMP_SLUG_NAME', 'cm-micropayment-platform' );
		}

		/**
		 * Define Plugin name
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_NAME' ) ) {
			define( 'CMMP_NAME', 'CM Micropayment Platform' );
		}

		/**
		 * Define Plugin canonical name
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_CANONICAL_NAME' ) ) {
			define( 'CMMP_CANONICAL_NAME', 'CM Micropayment Platform' );
		}

		/**
		 * Define Plugin basename
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_PLUGIN' ) ) {
			define( 'CMMP_PLUGIN', plugin_basename( __FILE__ ) );
		}

		/**
		 * Define Plugin basename
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_MENU_OPTION' ) ) {
			define( 'CMMP_MENU_OPTION', 'cm_micropayments' );
		}

		/**
		 * Define Plugin URL
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_URL' ) ) {
			define( 'CMMP_URL', 'https://www.cminds.com/store/micropayments/' );
		}

		/**
		 * Define Plugin URL
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'CMMP_RELEASE_NOTES' ) ) {
			define( 'CMMP_RELEASE_NOTES', 'https://www.cminds.com/store/micropayments/#changelog' );
		}
		/**
		 * Define Time Constants
		 *
		 * @since 1.0
		 */
		if ( ! defined( 'HOURINSECONDS' ) ) {
			define( 'HOURINSECONDS', 3600 );
		}
		if ( ! defined( 'DAYINSECONDS' ) ) {
			define( 'DAYINSECONDS', HOURINSECONDS * 24 );
		}
	}

	public static function _activate() {
		global $wpdb, $user_ID;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tablePrefix = $wpdb->prefix . "cm_micropayments";

		$sql = "CREATE TABLE " . $tablePrefix . "_defined_points_cost (
                points_cost_id INT(11) NOT NULL AUTO_INCREMENT,
                points_value DECIMAL(12,2) NOT NULL,
                cost DECIMAL(10,3) NOT NULL,
                PRIMARY KEY  (points_cost_id)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta( $sql );

		$sql = "CREATE TABLE " . $tablePrefix . "_wallets (
              wallet_id int(11) unsigned NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              wallet_name varchar(36) CHARACTER SET latin1 NOT NULL DEFAULT '',
              points DECIMAL(12,2) NOT NULL DEFAULT '0',
              status int(3) DEFAULT '0',
              PRIMARY KEY  (wallet_id),
              UNIQUE KEY wallet_name (wallet_name)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta( $sql );

		$sql = "CREATE TABLE " . $tablePrefix . "_wallet_charges (
                transaction_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                wallet_id INT(11) NOT NULL,
                points DECIMAL(12,2) NOT NULL,
                order_date DATETIME NOT NULL,
                amount DECIMAL(5,2) NOT NULL,
                ip VARCHAR(32) NULL DEFAULT NULL,
                status TINYINT(4) NOT NULL DEFAULT '0',
                type INT(11) NOT NULL DEFAULT '1' COMMENT '0 - Paypal, 1 - Admin Charge, 2- Withdraw',
                module_id INT(11) NOT NULL DEFAULT '0',
                module_type VARCHAR(255) NOT NULL DEFAULT 'post',
                comment TEXT NOT NULL,
                PRIMARY KEY  (transaction_id),
                KEY type_idx (type),
                KEY module_idx (module_id),
                KEY wallet_idx (wallet_id)
            )   ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta( $sql );

		$sql = "CREATE TABLE " . $tablePrefix . "_transactions (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                transaction_id INT(11) NOT NULL,
                message TEXT NOT NULL,
                type TINYINT(4) NOT NULL,
                token VARCHAR(32) NOT NULL,
                status TINYINT(2) NOT NULL,
                datetime datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY transaction_idx (transaction_id)
            )   ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta( $sql );

		$sql = "CREATE TABLE " . $tablePrefix . "_transaction_history (
                  id int(11) unsigned NOT NULL AUTO_INCREMENT,
                  transaction_id int(11) NOT NULL,
                  history_type int(11) NOT NULL,
                  datetime datetime NOT NULL,
                  PRIMARY KEY  (id),
                  KEY transaction_id (transaction_id),
                  KEY history_type (history_type)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta( $sql );

		$decimal_format = 246;

		$wallets_tbl = $wpdb->get_col( "SELECT `points` FROM `" . $tablePrefix . "_wallets`" );
		$points_format = $wpdb->get_col_info('type', 0);
		if ( $decimal_format !== $points_format ) {
	 		$sql = "ALTER TABLE `" . $tablePrefix . "_wallets` CHANGE `points` `points` DECIMAL(12,2) NOT NULL DEFAULT '0';";
 			$wpdb->query( $sql );
		}

		$charges_tbl = $wpdb->get_col( "SELECT `points` FROM `" . $tablePrefix . "_wallet_charges`" );
		$points_format = $wpdb->get_col_info('type', 0);
		if ( $decimal_format !== $points_format ) {
 			$sql = "ALTER TABLE `" . $tablePrefix . "_wallet_charges` CHANGE `points` `points` DECIMAL(12,2) NOT NULL DEFAULT '0';";
 			$wpdb->query( $sql );
		}

		$costs_tbl = $wpdb->get_col( "SELECT `points_value` FROM `" . $tablePrefix . "_defined_points_cost`" );
		$points_format = $wpdb->get_col_info('type', 0);
		if ( $decimal_format !== $points_format ) {
	 		$sql = "ALTER TABLE `" . $tablePrefix . "_defined_points_cost` CHANGE `points_value` `points_value` DECIMAL(12,2) NOT NULL DEFAULT '0';";
	 		$wpdb->query( $sql );
		}


		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_checkout_page_id' ) ) {
			$page['post_type']      = 'page';
			$page['post_content']   = '[cm_micropayment_checkout]';
			$page['post_parent']    = 0;
			$page['post_author']    = $user_ID;
			$page['post_status']    = 'publish';
			$page['comment_status'] = 'closed';
			$page['post_title']     = 'Checkout';

			$pageid = wp_insert_post( $page );

			add_option( 'cm_micropayment_checkout_page_id', $pageid );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_wallet_page_id' ) ) {
			$page['post_type']      = 'page';
			$page['post_content']   = '[cm_user_wallet]' . PHP_EOL . 'You have [cm_user_balance], which are worth [cm_user_balance_value].';
			$page['post_parent']    = 0;
			$page['post_author']    = $user_ID;
			$page['post_status']    = 'publish';
			$page['comment_status'] = 'closed';
			$page['post_title']     = 'My Wallet';

			$pageid = wp_insert_post( $page );

			add_option( 'cm_micropayment_wallet_page_id', $pageid );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_success_page_id' ) ) {
			$page['post_type']      = 'page';
			$page['post_content']   = 'Your wallet <a href="' . get_page_link( CMMicropaymentPlatform::get_option( 'cm_micropayment_wallet_page_id' ) ) . '">[get_transaction_wallet]</a> has been successfully granted. You bought [get_transaction_wallet_points]. Your wallet balance is now [cm_user_balance].';
			$page['post_parent']    = 0;
			$page['post_author']    = $user_ID;
			$page['comment_status'] = 'closed';
			$page['post_status']    = 'publish';
			$page['post_title']     = 'Success';

			$pageid = wp_insert_post( $page );
			add_option( 'cm_micropayment_success_page_id', $pageid );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_error_page_id' ) ) {
			$page['post_type']      = 'page';
			$page['post_content']   = 'Error while charging wallet';
			$page['post_parent']    = 0;
			$page['post_author']    = $user_ID;
			$page['post_status']    = 'publish';
			$page['comment_status'] = 'closed';
			$page['post_title']     = 'Error';

			$pageid = wp_insert_post( $page );
			add_option( 'cm_micropayment_error_page_id', $pageid );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_withdraw' ) ) {
			$message = '[amountPoints] has been withdrawn from your wallet.';

			add_option( 'cm_micropayment_email_withdraw', $message );
			add_option( 'cm_micropayment_email_withdraw_title', 'Points has been withdrawn from your wallet!' );
		}

		add_option( 'cm_micropayment_send_purchase_grant_notifications', 0 );

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_grant_for_purchase' ) ) {
			$message = '[amountPoints] have been added to your wallet for your purchase.';

			add_option( 'cm_micropayment_email_grant_for_purchase', $message );
			add_option( 'cm_micropayment_email_grant_for_purchase_title', 'Points have been added for your purchase!' );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_charge' ) ) {
			$message = 'Your wallet has been charged with [amountPoints] points.';

			add_option( 'cm_micropayment_email_charge', $message );
			add_option( 'cm_micropayment_email_charge_title', 'Your wallet has been charged' );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_grant' ) ) {
			$message = 'Your wallet has been granted with [amountPoints] points.';

			add_option( 'cm_micropayment_email_grant', $message );
			add_option( 'cm_micropayment_email_grant_title', 'Your wallet has been granted' );
		}

		if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_transfer' ) ) {
			$message = 'Transferred [amountPoints] points from [fromWalletID] to [toWalletID]';

			add_option( 'cm_micropayment_email_transfer', $message );
			add_option( 'cm_micropayment_email_transfer_title', 'Transfer wallet points confirmation' );
		}

        if ( ! CMMicropaymentPlatform::get_option( 'cm_micropayment_email_wallet_exchange' ) ) {
	        $message = 'Hi [fromName] 
Your wallet: [fromWalletID] has been changed to [toWalletID] by the Administrator';

            add_option( 'cm_micropayment_email_wallet_exchange', $message );
            add_option( 'cm_micropayment_email_wallet_exchange_title', 'Your wallet has been exchanged' );
        }

		add_option( 'cm_micropayment_number_of_transactions', 3 );

		return;
	}

	public static function _install( $networkwide ) {
		global $wpdb;
		/*
		 * Added the multisite installation
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ( $networkwide && function_exists( 'switch_to_blog' ) ) {
				// Get all blog ids
				if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
					$sites = get_sites();
					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						self::_activate();
					}
					restore_current_blog();

					return;
				} else {
					$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs}" ) );
					if ( ! is_array( $blogids ) ) {
						$blogids = array( $blogids );
					}
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::_activate();
					}
					restore_current_blog();

					return;
				}
			}
		}

		self::_activate();


	}

	public static function _uninstall() {
		return;
	}

	public static function initSession() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	public static function applyFilters() {
		add_action( 'user_register', array( self::$calledClassName, 'onUserCreated' ), 999 );
		add_action( 'register_new_user', array( self::$calledClassName, 'onUserCreated' ), 999 );
		add_action( 'admin_head', array( self::$calledClassName, 'addScheduled' ), 999 );
		add_action( 'cmmpp-refresh-event2', array( self::$calledClassName, 'runScheduled' ) );
		add_filter( 'cron_schedules', array( self::$calledClassName, 'cronAddMin' ) );
	}

	static function cronAddMin( $schedules ) {
		$schedules['min'] = array(
			'interval' => 65,
			'display'  => 'Every 60s'
		);

		return $schedules;
	}

	static function addScheduled() {
		if ( ! CMMicropaymentPlatform::get_option( "cmmp-external-wallet" ) ) {
			return;
		}
		if ( ! wp_next_scheduled( 'cmmpp-refresh-event2' ) ) {
			$refresh = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-refresh" );
			if ( $refresh == '12_hours' ) {
				wp_schedule_event( time(), 'twicedaily', 'cmmpp-refresh-event2' );
			} elseif ( $refresh == 'day' ) {
				wp_schedule_event( time(), 'daily', 'cmmpp-refresh-event2' );
			}
		}
	}


	/**
	 * update external state
	 */
	static function runScheduled() {

		try {
			CMMicropaymentPlatformBackend::updateExternalWallets();
			// $currentWallets = CMMicropaymentPlatformWallet::getWalletsNames();
			// foreach ( $currentWallets as $wallet_name ) {
				// CMMicropaymentPlatformWallet::syncExternalWallet( [ 'wallet_name' => $wallet_name ] );
			// }
		} catch ( \Exception $e ) {

		}
	}

	public static function onUserCreated( $user_id ) {
		include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

		$wallet = CMMicropaymentPlatformFrontendWallet::instance();
		if ( ! $wallet->getWalletIdByUserID( $user_id ) ) {

			if ( CMMicropaymentPlatform::get_option( "cmmp-external-wallet" ) == 'on' ) {
				$user         = get_userdata( $user_id );
				$email        = $user->user_email;
				$remoteWallet = self::getExternalWalletByEmail( $email );

				if ( $remoteWallet ) {
					$wallet->createWalletFromRemote( $user_id, $remoteWallet );
				} else {
					$wallet->createWallet( $user_id );
				}
			} else {
				$wallet->createWallet( $user_id );
			}
		}
	}

	public function registerAjaxFunctions() {
		require_once CMMP_PLUGIN_DIR . '/frontend/cmmp-frontend.php';
		
		add_action( 'wp_ajax_pay_tip', array( 'CMMicropaymentPlatformFrontend', 'ajaxPayTip' ) );

		add_action( 'wp_ajax_create_wallet_id', array( 'CMMicropaymentPlatformFrontend', 'ajaxCreateWalletID' ) );
		add_action( 'wp_ajax_nopriv_create_wallet_id', array( 'CMMicropaymentPlatformFrontend', 'ajaxCreateWalletID' ) );
		add_action( 'wp_ajax_cmmicropayment_success', array( 'CMMicropaymentPlatformFrontend', 'finalizeTransaction' ) );
		add_action( 'wp_ajax_get_wallet_info', array( 'CMMicropaymentPlatformFrontend', 'getWalletInfo' ) );
		add_action( 'wp_ajax_check_wallet_id', array( 'CMMicropaymentPlatformFrontend', 'checkWalletID' ) );

		add_action( 'wp_ajax_nopriv_get_wallet_info', array( 'CMMicropaymentPlatformFrontend', 'getWalletInfo' ) );
		add_action( 'wp_ajax_nopriv_check_wallet_id', array( 'CMMicropaymentPlatformFrontend', 'checkWalletID' ) );
	}

	public static function registerFilters() {
		add_filter( 'transfer_points', array( self::$calledClassName, 'transferPoints' ) );
		add_action( 'create_wallets_for_existing_users', array(
			self::$calledClassName,
			'createWalletForExistingUsers'
		) );

		add_filter( 'cm_micropayments_checkout_url', array( self::$calledClassName, 'getCheckoutPageUrl' ), 1 );
		add_filter( 'cm_micropayments_user_wallet_url', array( self::$calledClassName, 'getUserWalletPageUrl' ), 1 );
		add_filter( 'cm_micropayments_user_wallet_id', array( self::$calledClassName, 'getUserWalletId' ), 1 );
		add_filter( 'cm_micropayments_user_wallet_code', array( self::$calledClassName, 'getUserWalletCode' ), 1 );

		add_filter( 'cm_micropayments_get_wallet_by_code', array( self::$calledClassName, 'getWalletByCode' ), 1 );

		add_filter( 'cm_micropayments_is_working', array(
			self::$calledClassName,
			'checkMicropaymentPlatformIsWorking'
		), 1 );
		add_filter( 'cm_micropayments_are_points_defined', array( self::$calledClassName, 'arePointsDefinedOK' ), 1 );
		add_filter( 'cm_micropayments_are_wallets_assigned', array(
			self::$calledClassName,
			'areWalletsAssignedOK'
		), 1 );
		add_filter( 'cm_micropayments_are_paypal_settings_defined', array(
			self::$calledClassName,
			'arePayPalSettingsOK'
		), 1 );
		add_filter( 'cm_micropayments_is_checkout_page', array( self::$calledClassName, 'isCheckoutPageOK' ), 1 );
		add_filter( 'cm_micropayments_is_wallet_page', array( self::$calledClassName, 'isWalletPageOK' ), 1 );
	}

	public static function transferPoints( $args ) {
		include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
		include_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';

		$wallet = CMMicropaymentPlatformFrontendWallet::instance();

		$ret = $wallet->transferPointsByUserId( $args['from'], $args['to'], $args['amount'] );

		if ( $ret['success'] ) {
			/*
			$sender   = $wallet->getWalletIdByUserID( $args[ 'from' ] );
			$receiver = $wallet->getWalletIdByUserID( $args[ 'to' ] );

			CMMicropaymentPlatformWalletCharges::log( $args[ 'amount' ], 0, $sender, 2, 1 );
			CMMicropaymentPlatformWalletCharges::log( $args[ 'amount' ], 0, $receiver, 3, 1 );
			*/
		}

		return $ret;
	}

	public static function createWalletForExistingUsers() {
		$remote_enabled = CMMicropaymentPlatform::get_option( "cmmp-external-wallet" );
		$users = get_users(array('fields' => array('ID','user_email')));

		if ( ! class_exists( "CMMicropaymentPlatformWallet" ) ) {
			include_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
		}

		$model   = new CMMicropaymentPlatformWallet();
		$wallets = $model->getWallets();
		// $users = array_filter($users, function($u) {
			// return 
		// });
		// copy remote wallets if they exist
		if ( $remote_enabled === 'on' ) {
			$remoteWallets = self::getExternalsWallets();
			if ( $remoteWallets ) {
				foreach ( $users as $u ) {
					$user_id = $u->ID;
					if ( ( ! isset($wallets[ $user_id ]) ) && ( ! empty($u->user_email) ) ) {
						$userIndex = array_search( $u->user_email, array_column( $remoteWallets, 'user_email' ) );
						if ( $userIndex !== false ) {
							$model->createWalletFromRemote( $u->ID, $remoteWallets[ $userIndex ] );
						}
					}
				}
			}
		} else {
			foreach ( $users as $u ) {
				if ( ! isset( $wallets[ $u->ID ] ) ) {
					$model->createWallet( $u->ID );
				}
			}
		}

		return true;
	}

	/**
	 * If this function returns true then we redirect all the checkout process to the WOO
	 *
	 * @return type
	 */
	public static function isWOOIntegrationActive() {
		$option = CMMicropaymentPlatform::get_option( 'cm_micropayment_use_woo_checkout' );
		$result = $option && class_exists( 'WooCommerce' );

		return $result;
	}

	/**
	 * If this function returns true then we redirect all the checkout process to the EDD
	 *
	 * @return type
	 */
	public static function isEddIntegrationActive() {
		$option = CMMicropaymentPlatform::get_option( 'cm_micropayment_use_edd_checkout' );
		$result = $option && class_exists( 'Easy_Digital_Downloads' );

		return $result;
	}

	public static function getEddProductLabel( $pointsValue ) {
		$plural   = CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );
		$singular = CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' );

		$label = $pointsValue . ' ' . ( ( $pointsValue == 1 ) || ( $pointsValue == "1.0" ) ? $singular : $plural );

		return $label;
	}

	/**
	 * Returns TRUE if the MicropaymentPlatform is configured and working
	 */
	public static function checkMicropaymentPlatformIsWorking( $result ) {
		static $result = null;

		if ( $result === null ) {
			$payPalSettingsOK = self::arePayPalSettingsOK();
			$checkoutPageOk   = self::isCheckoutPageOK();
			$walletPageOK     = self::isWalletPageOK();
			$arePointsDefined = self::arePointsDefinedOK();

			$result = $payPalSettingsOK && $checkoutPageOk && $walletPageOK && $arePointsDefined;

			if ( ! $result ) {
				$hookedPlugins     = apply_filters( 'cm_micropayments_integrations', array() );
				$hookedPluginsList = implode( ', ', $hookedPlugins );

				$message = 'Until the errors are fixed (see other notices) the CM Micropayment Platform integration has been suspended in the following plugins: <strong>' . $hookedPluginsList . '</strong>';
				if ( $hookedPluginsList != '' ) {
					CMMicropaymentPlatform::$errors[] = $message;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns TRUE if any of the points is defined
	 */
	public static function arePointsDefinedOK() {
		if ( ! class_exists( 'CMMicropaymentPlatformPointsPrices' ) ) {
			require_once CMMP_PLUGIN_DIR . '/shared/models/points.php';
		}

		$pointsObj = new CMMicropaymentPlatformPointsPrices();
		$result    = intval( $pointsObj->hasPoints() ) > 0;

		if ( ! $result ) {
			$message                          = __( 'There are no Points defined. Please go to Settings -> Points and "Add New" to add the possibility to buy points.' );
			CMMicropaymentPlatform::$errors[] = $message;
		}

		return $result;
	}

	/**
	 * Returns TRUE if the wallets are assigned to customers
	 */
	public static function areWalletsAssignedOK() {
		static $walletsAssigned = 0;
		static $message = '';
		$walletsAssigned = CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer', 1 );
		/*
		 * Recreate wallets if needed
		 */
		if ( $walletsAssigned ) {
			do_action( 'create_wallets_for_existing_users' );
		}

		if ( ! $walletsAssigned && empty($message) ) {
			$message                          = __( 'The wallets are not assigned to the users. Please go to Settings -> General and make sure to assign the wallets to the users.' );
				CMMicropaymentPlatform::$errors[] = $message;
		}

		return $walletsAssigned;
	}

	/**
	 * Returns TRUE if the PayPal settings are OK
	 */
	public static function arePayPalSettingsOK() {
		$eddIntegration = self::isEddIntegrationActive();
		$usePayPal      = CMMicropaymentPlatform::get_option( 'cm_micropayment_use_paypal' );
		$payPalEmail    = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_email' );

		/*
		 * Not using PayPal or EDD integration or PayPal email not empty
		 */
		$result = ( ! $usePayPal || $eddIntegration || $payPalEmail !== '' );

		if ( ! $result ) {
			CMMicropaymentPlatform::$errors[] = __( 'Missing Paypal credentials. Please go to Settings -> PayPal and fill the credentials.' );
		}

		return $result;
	}

	/**
	 * Returns TRUE if the Checkout page exists and contains the shortcode
	 */
	public static function isCheckoutPageOK() {
		$integrationsActive = ( self::isEddIntegrationActive() || self::isWOOIntegrationActive() );
		if ( ! $integrationsActive ) {
			$checkoutPageId = CMMicropaymentPlatform::get_option( 'cm_micropayment_checkout_page_id' );
			if ( ! $checkoutPageId ) {
				CMMicropaymentPlatform::$errors[] = 'Missing Checkout page!';
			} else {
				$post = get_post( $checkoutPageId );
				if ( $post ) {
					if ( ! shortcode_exists( 'cm_micropayment_checkout' ) ) {
						add_shortcode( 'cm_micropayment_checkout', function () {
							return;
						} );
					}
					$result = has_shortcode( $post->post_content, 'cm_micropayment_checkout' );
					if ( ! $result ) {
						$editPageUrl                      = admin_url( 'post.php?post=' . $checkoutPageId . '&action=edit' );
						CMMicropaymentPlatform::$errors[] = 'Missing shortcode [cm_micropayment_checkout] on the <a href="' . $editPageUrl . '">Checkout page</a>!';
					}
				}
			}
		}

		$result = empty( CMMicropaymentPlatform::$errors );

		return $result;
	}

	/**
	 * Returns TRUE if the Wallet history  page exists and contains the shortcode
	 */
	public static function isWalletPageOK() {
		$result = false;
		$walletPageId = CMMicropaymentPlatform::get_option( 'cm_micropayment_wallet_page_id' );
		if ( ! $walletPageId ) {
			CMMicropaymentPlatform::$errors[] = 'Missing Wallet history page!';
		} else {
			$post = get_post( $walletPageId );
			if ( $post ) {
				if ( ! shortcode_exists( 'cm_user_wallet' ) ) {
					add_shortcode( 'cm_user_wallet', function () {
						return;
					} );
				}
				$result = has_shortcode( $post->post_content, 'cm_user_wallet' );
				if ( ! $result ) {
					$editPageUrl = admin_url( 'post.php?post=' . $walletPageId . '&action=edit' );
					CMMicropaymentPlatform::$errors[] = 'Missing shortcode [cm_user_wallet] on the <a href="' . $editPageUrl . '">Wallet history page</a>!';
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the Checkout page url
	 * @return string
	 */
	public static function getCheckoutPageUrl() {
		$checkoutPageId = CMMicropaymentPlatform::get_option( 'cm_micropayment_checkout_page_id' );
		if ( $checkoutPageId ) {
			return get_permalink( $checkoutPageId );
		}

		return '';
	}

	/**
	 * Returns the Wallet page url
	 * @return string
	 */
	public static function getUserWalletPageUrl() {
		$walletPageId = CMMicropaymentPlatform::get_option( 'cm_micropayment_wallet_page_id' );
		if ( $walletPageId ) {
			return get_permalink( $walletPageId );
		}

		return '';
	}

	/**
	 * Returns the User's Wallet ID
	 * @return string
	 */
	public static function getUserWalletId( $userId ) {
		$walletId = null;
		$model    = new CMMicropaymentPlatformWallet();
		$wallet   = $model->getWalletByUserID( $userId );

		if ( ! empty( $wallet ) ) {
			$walletId = $wallet->wallet_id;
		}

		return $walletId;
	}

	/**
	 * Returns the User's Wallet Name/Code
	 * @return string
	 */
	public static function getUserWalletCode( $userId ) {
		$walletId = null;
		$model    = new CMMicropaymentPlatformWallet();
		$wallet   = $model->getWalletByUserID( $userId );

		if ( ! empty( $wallet ) ) {
			$walletId = $wallet->wallet_name;
		}

		return $walletId;
	}

	/**
	 * Returns the User's Wallet
	 * @return string
	 */
	public static function getWalletByCode( $wallet_name ) {
		if ( empty( $wallet_name ) ) {
			return null;
		}

		$model  = new CMMicropaymentPlatformWallet();
		$wallet = $model->getWalletByCode( $wallet_name );

		return $wallet;
	}

	public static function renderPluginHelp( $what = 'all' ) {
		if ( ob_start() ) {
			include CMMP_PLUGIN_DIR . '/backend/views/help.phtml';
			$content = ob_get_clean();

			return $content;
		}
	}

	public static function always_local( $option_id ) {
		return in_array( $option_id, array(
			'cm_micropayment_wallet_page_id',
			'cm_micropayment_checkout_page_id',
			'cm_micropayment_success_page_id',
			'cm_micropayment_error_page_id',
			'cmmp_edd_gateway_ratio'
		) );
	}

	public static function get_option( $option_id, $default = '' ) {
		if ( is_multisite() ) {
			if ( CMMPMultisite::is_shared_network() && ! self::always_local( $option_id ) ) {
				$settings = get_blog_option( 1, $option_id, $default );
			} else {
				$settings = get_blog_option( $GLOBALS['blog_id'], $option_id, $default );
			}
		} else {
			$settings = get_option( $option_id, $default );
		}

		return $settings;
	}

	public static function update_option( $option_id, $value = '' ) {
		if ( is_multisite() ) {
			if ( CMMPMultisite::is_shared_network() && ! self::always_local( $option_id ) ) {
				return update_blog_option( 1, $option_id, $value );
			} else {
				return update_blog_option( $GLOBALS['blog_id'], $option_id, $value );
			}
		} else {
			return update_option( $option_id, $value );
		}
	}

	public static function delete_option( $option_id ) {
		if ( is_multisite() ) {
			if ( CMMPMultisite::is_shared_network() && ! self::always_local( $option_id ) ) {
				delete_blog_option( 1, $option_id );
			} else {
				delete_blog_option( $GLOBALS['blog_id'], $option_id );
			}
		} else {
			delete_option( $option_id );
		}
	}

	public static function checkPeriodicCron() {
		if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_cron_operations' ) == 1 ) {
			if ( ! wp_next_scheduled( 'periodicWalletChanges' ) ) {
				$interval  = intval( CMMicropaymentPlatform::get_option( 'cm_micropayment_cron_interval' ) );
				$startTime = $interval * DAYINSECONDS;
				if ( $interval ) {
					wp_schedule_single_event( current_time( 'timestamp' ) + $startTime, 'periodicWalletChanges' );
				} else {
					wp_clear_scheduled_hook( 'periodicWalletChanges' );
				}
			}
		} else {
			wp_clear_scheduled_hook( 'periodicWalletChanges' );
		}
	}

	public static function periodicWalletChanges() {
		$amount_type  = CMMicropaymentPlatform::get_option( 'cm_micropayment_cron_amount_type', 'fixed' );
		$amount       = intval( CMMicropaymentPlatform::get_option( 'cm_micropayment_cron_amount' ) );
		$walletObject = new CMMicropaymentPlatformWallet();
		$wallets      = $walletObject->getWallets();
		$fees_user_id = CMMicropaymentPlatformBackendFeesUser::getFeesUserId();
		$store_purchases_user_id = CMMicropaymentPlatformBackendStorePurchasesUser::getStorePurchasesUserId();

		$comment = ( $amount > 0 ) ? CMMicropaymentPlatform::get_option( 'cm_micropayment_cron_grant_reason' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_cron_charge_reason' );

		if ( $wallets ) {
			foreach ( $wallets as $oneWalet ) {

				if($oneWalet->user_id == $fees_user_id || $oneWalet->user_id == $store_purchases_user_id) {
					continue;
				}

				$walletPoints = CMMicropaymentPlatformFrontend::getUserWalletBallanceById($oneWalet->user_id);
				
				if($amount_type == 'percentage') {
					if($amount > 0) {
						$famount = ceil(($walletPoints * $amount) / 100);
					} else {
						$amount = abs($amount);
						$famount = ceil(($walletPoints * $amount) / 100);
						$famount = -$famount;
					}
				} else {
					$famount = $amount;
				}

				$walletObject->chargeWallet( $oneWalet->wallet_id, $famount, false, true, $comment );
			}
		}
		wp_clear_scheduled_hook( 'periodicWalletChanges' );
		self::checkPeriodicCron();
	}


	public static function numericOrFloat( $var ) {
		if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_enable_decimal', false ) ) {
//			return is_numeric( $var ) && strpos( $var, '.' ) !== false;
		}

		return is_numeric( $var );
	}

	public static function convertType( $var ) {
		if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_enable_decimal', false ) ) {
			return floatval( $var );
		} elseif ( CMMicropaymentPlatform::get_option( 'cm_micropayment_rounds_decimal', '' ) == 'lowest' ) {
			return intval( round( $var, 0, PHP_ROUND_HALF_DOWN ) );
		}

		return intval( round( $var, 0, PHP_ROUND_HALF_UP ) );
	}

	public static function numberToLocale( $n, $show_decimals = true ) {
		if ( $show_decimals ) {
			return number_format_i18n( floatval( $n ), 2);
		} elseif ( CMMicropaymentPlatform::get_option( 'cm_micropayment_rounds_decimal', '' ) == 'lowest' ) {
			return number_format_i18n(intval( round( $n, 0, PHP_ROUND_HALF_DOWN ) ));
		}

		return number_format_i18n(intval( round( $n, 0, PHP_ROUND_HALF_UP ) ));
	}

    /**
     * Get plugin's version.
     *
     * @return string|null
     */
    public static function version() {	
		if ( 1 === CMMP_DEBUG ) {
			return time();
		}
    	if( !empty(self::$version) ) {
    		return self::$version;
    	} else {
    		$readme = file_get_contents(CMMP_PATH . '/readme.txt');
    		preg_match('/Stable tag\: ([0-9\.]+)/i', $readme, $match);
    		if( isset($match[1]) ) {
    			self::$version = $match[1];
    			return $match[1];
    		}
    	}
    }

}

/**
 * The main function responsible for returning the one true plugin class
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $CMMicropaymentPlatform = CMMicropaymentPlatformInit(); ?>
 *
 * @return object The one true CM_Micropayment_Platform Instance
 * @since 1.0
 */
function CMMicropaymentPlatformInit() {
	return CMMicropaymentPlatform::instance();
}

$CMMicropaymentPlatform = CMMicropaymentPlatformInit();

register_activation_hook( __FILE__, array( 'CMMicropaymentPlatform', '_install' ) );
register_deactivation_hook( __FILE__, array( 'CMMicropaymentPlatform', '_uninstall' ) );