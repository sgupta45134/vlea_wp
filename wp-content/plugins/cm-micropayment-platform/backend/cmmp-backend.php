<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CMMicropaymentPlatformBackend {

	public static $calledClassName;
	protected static $instance = null;

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

		add_action( 'wp_ajax_exw_check_status', array( self::$calledClassName, 'checkExwStatus' ) );
		add_action( 'wp_ajax_exw_update_external_wallets', array( self::$calledClassName, 'updateExternalWallets' ) );
		add_action( 'admin_init', array( self::$calledClassName, 'filterCSVExport' ) );
		add_action( 'admin_init', array( self::$calledClassName, 'handleExport' ) );
		add_action( 'admin_init', array( self::$calledClassName, 'addFeesUser' ) );
		add_action( 'admin_init', array( self::$calledClassName, 'addStorePurchases' ) );
		add_action( 'wp_dashboard_setup', array( self::$calledClassName, 'registerDashboardWidgets' ) );
		add_action( 'wp_enqueue_scripts', array( self::$calledClassName, 'registerGlobalStyles' ) );
		add_action( 'admin_enqueue_scripts', array( self::$calledClassName, 'registerGlobalStyles' ) );
		add_action( 'admin_menu', array( self::$calledClassName, 'registerSettings' ) );
		add_action( 'cmmicropayment_delete_points', array( self::$calledClassName, 'removePoints' ) );

		add_action( 'admin_notices', array( self::$calledClassName, 'displayErrorsMessages' ) );
		add_action( 'show_user_profile', array( self::$calledClassName, 'displayUsersInfo' ) );
		add_action( 'edit_user_profile', array( self::$calledClassName, 'displayUsersInfo' ) );

		add_action( 'personal_options_update', array( self::$calledClassName, 'updateUserProfile' ) );
		add_action( 'edit_user_profile_update', array( self::$calledClassName, 'updateUserProfile' ) );

		add_action( 'edd_meta_box_settings_fields', array( self::$calledClassName, 'addEddPointsConnection' ) );
		add_filter( 'edd_metabox_fields_save', array( self::$calledClassName, 'addEddPointsConnectionFields' ) );

		add_action( 'wp_ajax_cm_micropayment_platform_save_wallet_points', array(
			self::$calledClassName,
			'saveWalletPoints'
		) );
		add_action( 'wp_ajax_cm_micropayment_platform_accept_payment', array(
			self::$calledClassName,
			'acceptPayment'
		) );
	}

	public static function updateExternalWallets() {
		$localWallets = CMMicropaymentPlatformWallet::getWalletsNames();
		CMMicropaymentPlatformWallet::syncBulkExternalsWallet($localWallets);
		die;
	}

	public static function checkExwStatus() {

		$wallet_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
		$enabled    = CMMicropaymentPlatform::get_option( "cmmp-external-wallet" );
		$URL        = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );

		$status = $URL && $wallet_key && $enabled && CMMicropaymentPlatform::getExternalsWallets();

		if ( $status ) {
			?>
			<?php if ( $status ): ?>
                <span class="exw-status enabled">
        <?php _e( 'Enabled' ); ?></span>
			<?php else: ?>
                <span class="exw-status disabled">
       <?php _e( 'Disabled' ); ?>  </span>
			<?php endif; ?>

			<?php
		} else {
		    ?>
            <span class="exw-status disabled">
       <?php _e( 'Disabled' ); ?>  </span>
            <?php
        }
		wp_die();
	}

	public static function registerDashboardWidgets() {
		if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_show_dashboard_widget' ) == 1 ) {
			wp_add_dashboard_widget( 'cm_micropayments_user_wallet', __( 'Your wallet' ), array(
				self::$calledClassName,
				'registerWalletWidget'
			) );
		}
	}

	public static function registerSettings() {
		global $submenu;
		$current_user = wp_get_current_user();

		self::handlePointsPricePost();
		add_menu_page( 'Micro Payments - Reports', 'CM Micropayment Platform', 'manage_options', 'cm_micropayments', array(
			self::$calledClassName,
			'renderReports'
		), '', 76 );

		add_submenu_page( 'cm_micropayments', 'CM Micropayment Platform - Settings', __( 'Reports' ), 'manage_options', 'cm_micropayments', array(
			self::$calledClassName,
			'renderReports'
		) );
		add_submenu_page( 'cm_micropayments', 'CM Micropayment Platform - Transactions', __( 'Transactions' ), 'manage_options', 'cm-micropayment-platform-transactions', array(
			self::$calledClassName,
			'renderPaypalTransactions'
		) );
		add_submenu_page( 'cm_micropayments', 'CM Micropayment Platform - Manage Wallets', __( 'Manage Wallets' ), 'manage_options', 'cm-micropayment-platform-wallet', array(
			self::$calledClassName,
			'renderSettingsWallet'
		) );
		add_submenu_page( 'cm_micropayments', 'CM Micropayment Platform - Import/Export', __( 'Import/Export' ), 'manage_options', 'cm-micropayment-import-axport', array(
			self::$calledClassName,
			'renderImportExport'
		) );
		add_submenu_page( 'cm_micropayments', 'CM Micropayment Platform - Settings', __( 'Settings' ), 'manage_options', 'cm-micropayment-platform-settings', array(
			self::$calledClassName,
			'renderPluginSettings'
		) );
	}

	public static function registerGlobalStyles() {
		//		wp_enqueue_style( 'cm-micropayment-backend-global-style', CMMP_PLUGIN_URL . '/backend/assets/css/style.css' );
		/*
		* Scripts
		*/
	//	wp_enqueue_script( 'jquery-ui' );
		// if(isset($_GET['page']) && $_GET['page'] != 'pmxi-admin-import') {
			wp_enqueue_script( 'jquery-ui' ,'https://code.jquery.com/ui/1.12.1/jquery-ui.js');
		// }
		// wp_enqueue_script( 'jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/js/jquery-ui/jquery-ui-1.10.4.custom.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'cm-micropayment-admin-scripts', CMMP_PLUGIN_URL . '/backend/assets/js/scripts.js', array( 'jquery','jquery-ui' ), CMMicropaymentPlatform::version() );
		$jsData         = array(
			'ajaxurl'                 => admin_url( 'admin-ajax.php?action=cm_micropayment_platform_save_wallet_points' ),
			'ajaxurlforacceptpayment' => admin_url( 'admin-ajax.php?action=cm_micropayment_platform_accept_payment' ),
			'l18n'                    => array(
				'save'   => __( 'Save' ),
				'cancel' => __( 'Cancel' ),
				'label'  => __( 'Change button points value' ),
			)
		);
		$jsDataFiltered = apply_filters( 'cmmp_wallet_render_js_data', $jsData );
		wp_localize_script( 'cm-micropayment-admin-scripts', 'cmmp_data', $jsDataFiltered );
		/*
		 * Styles
		 */
		wp_enqueue_style( 'cm-micropayment-backend-jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/css/jquery-ui/ui-lightness/jquery-ui-1.10.4.custom.min.css' );
		wp_enqueue_style( 'cm-micropayment-backend-style', CMMP_PLUGIN_URL . '/backend/assets/css/style.css' );
	}

	public static function updateUserProfile( $user_id ) {
		update_user_meta( $user_id, 'stripe_account_id', $_POST['stripe_account_id'] );
		if(isset($_POST['cmmp_change_user_wallet']) && $_POST['cmmp_change_wallet_userID']) {
            CMMicropaymentPlatformWallet::exchangeUsersWallets($user_id, $_POST['cmmp_change_wallet_userID']);
		}
	}

	public static function displayUsersInfo( $user ) {
		$model = new CMMicropaymentPlatformWallet();
		$user  = wp_get_current_user();

		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] != '' && $_GET['user_id'] > 0 ) {
			$user = get_user_by( 'id', $_GET['user_id'] );
		}

		$wallet = $model->getWalletByUserID( $user->ID );
		?>
        <h3>
			<?php
			echo CMMP_NAME;
			?>
        </h3>
        <table class="form-table">
			<?php
			if(current_user_can('administrator')) {
			?>
            <tr>
                <th>
					<?php _e( 'Your Stripe account id (needs for Stripe payout)' ); ?>
                </th>
                <td>
                    <input
                            name="stripe_account_id"
                            type="text"
                            id="stripe_account_id"
                            value="<?php echo ( $user->stripe_account_id ) ? $user->stripe_account_id : ''; ?>"
                    />
                </td>
            </tr>
			<?php
			}
			?>
            <tr>
                <th>
					<?php _e( 'Your Wallet Name' ); ?>
                </th>
                <td>
					<?php
					echo $wallet->wallet_name;
					?>
                    <?php if(current_user_can('manage_options')) {
                        self::renderExchangeWallet($wallet);
                    } ?>
                </td>
            </tr>
            <tr>
                <th>
					<?php echo sprintf( '%s %s', 'Amount of ', __cm( 'cm_micropayment_plural_name' ) ); ?>
                </th>
                <td>
					<?php
					echo $wallet->points;
					?>
                </td>
            </tr>
        </table>
		<?php
	}

	public static function addFeesUser() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-fees-user.php';
		new CMMicropaymentPlatformBackendFeesUser();
	}

	public static function addStorePurchases() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-store-user.php';
		new CMMicropaymentPlatformBackendStorePurchasesUser();
	}

	public static function registerWalletWidget() {
		require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
		$model = new CMMicropaymentPlatformWallet();
		$user  = wp_get_current_user();

		$wallet = $model->getWalletByUserID( $user->ID );

		if ( ob_start() ) {
			include CMMP_PLUGIN_DIR . '/backend/views/widget/wallet.phtml';

			$content = ob_get_clean();
			echo $content;
		}
	}

	public static function renderPluginAbout() {
		if ( ob_start() ) {
			include CMMP_PLUGIN_DIR . '/backend/views/about.phtml';
			$content = ob_get_clean();
			echo $content;
		}
	}

	public static function renderExchangeWallet($wallet) {
		if ( ob_start() ) {
			include CMMP_PLUGIN_DIR . '/backend/views/exchange-wallet.phtml';
			$content = ob_get_clean();
			echo $content;
		}
	}

	public static function renderPluginSettings() {
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'points-prices' ) {
			self::renderPointsSettings();
		} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] == 'external-wallet' ) {
			self::renderExternalWalletSettings();
		} else {
			require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-settings.php';
			$component = new CMMicropaymentPlatformBackendSettings();
			$component->render();
		}
	}

	public static function renderReports() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-reports.php';
		$component = new CMMicropaymentPlatformBackendReports();
		$component->render();
	}

	public static function renderSettingsWallet() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-wallets.php';
		$component = new CMMicropaymentPlatformBackendWallets();
		$component->render();
	}

	public static function renderImportExport() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-import-export.php';
		$component = new CMMicropaymentPlatformBackendImport();
		if ( ! empty( $_POST['cmmp_doImport'] ) && ! empty( $_FILES['importCSV'] ) && is_uploaded_file( $_FILES['importCSV']['tmp_name'] ) && ! empty( $_POST['action-type'] ) ) {
			$component->importAction();
		}
		$component->render();
	}

	public static function renderPointsSettings() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$component = new CMMicropaymentPlatformBackendPointsPrices();
		$component->render();
	}

	public static function renderExternalWalletSettings() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-external-wallet.php';
		$component = new CMMicropaymentPlatformBackendExternalWallet();

		$component->render();
	}

	public static function renderPaypalTransactions() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-transactions.php';
		$component = new CMMicropaymentPlatformBackendTransactions();
		$component->render();
	}

	public static function saveWalletPoints() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-wallets.php';
		$component = new CMMicropaymentPlatformBackendWallets();
		$component->_ajaxSavePoints();
	}

	public static function acceptPayment() {
		global $wpdb;
		$transaction_id = ( isset( $_POST['t_id'] ) ) ? $_POST['t_id'] : 0;
		if ( $transaction_id ) {

			if ( ! class_exists( 'CMMicropaymentPlatformWalletCharges' ) ) {
				require_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
			}

			$dbWalletCharges = new CMMicropaymentPlatformWalletCharges();
			$dbWalletCharges->confirm( $transaction_id );
			$transaction = $dbWalletCharges->getConfirmedTransaction( $transaction_id );

			if ( ! class_exists( 'CMMicropaymentPlatformWallet' ) ) {
				require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
			}
			$dbWallet = new CMMicropaymentPlatformWallet();
			$dbWallet->chargeWallet( $transaction->wallet_id, $transaction->points, false, false );
			$dbWallet->chargeTransactionFee( $transaction->wallet_id, true, $transaction->points );

			if ( ! class_exists( 'CMMicropaymentPlatformTransactions' ) ) {
				require_once CMMP_PLUGIN_DIR . 'shared/models/transactions.php';
			}
			CMMicropaymentPlatformTransactions::updateStatus( $transaction_id, 1 );

		}
		die();
	}

	public static function filterCSVExport() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'cm_micropayments' ) {
			if ( isset( $_GET['export'] ) && in_array( $_GET['export'], array( 'pdf', 'csv' ) ) ) {
				require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-reports.php';
				$component = new CMMicropaymentPlatformBackendReports();
				$component->export();
				die();
			}
			if ( isset( $_GET['export_to_csv'] ) ) {
				require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-reports.php';
				$component = new CMMicropaymentPlatformBackendReports();
				$component->export_wallets();
				die();
			}
		}
	}

	private static function handlePointsPricePost() {
		if ( isset( $_POST ) && count( $_POST ) > 0 ) {
			if ( isset( $_POST['cancel'] ) ) {
				wp_redirect( admin_url( 'admin.php?page=cm-micropayment-platform-settings&tab=points-prices' ), 301 );
				exit;
			}

			if ( isset( $_POST['sender'] ) == 'settings-points-values-form' ) {
				require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
				$handlePostResult = CMMicropaymentPlatformBackendPointsPrices::handlePost();
			}

			/*
			 * Saved successfully
			 */
			if ( ! empty( $handlePostResult ) ) {
				if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
					self::addEddProductPages();
				}

				wp_redirect( admin_url( 'admin.php?page=cm-micropayment-platform-settings&tab=points-prices' ), 301 );
				exit;
			}
		}

		if ( isset( $_GET['cmm-action'] ) && isset( $_GET['tab'] ) && $_GET['cmm-action'] == 'remove' && $_GET['tab'] == 'points-prices' ) {
			require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
			if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
				$pointObj = CMMicropaymentPlatformBackendPointsPrices::getOne( $_GET['cmm-id'], OBJECT );
				if ( ! empty( $pointObj ) ) {
					self::removeEddProductPage( $pointObj->points_value );
				}
			}

			CMMicropaymentPlatformBackendPointsPrices::remove( $_GET['cmm-id'] );
			exit;
		}
	}

	public static function useWOOCheckout( $on ) {
		$wooIntegrationState = CMMicropaymentPlatform::isWOOIntegrationActive();

		/*
		 * No change - do nothing
		 */
		if ( $on == $wooIntegrationState ) {
			return;
		}

		/*
		 * Turning ON
		 */
		if ( $on ) {
			/*
			 * Add the pages after the other settings have been saved
			 */
			add_action( 'cmmp_after_settings_save', array( __CLASS__, 'connectWOOPages' ) );
		}
		/*
		 * Turning ON
		 */
		if ( $on ) {
			self::addWOOProductPages();
		} /*
         * Turning OFF
         */ else {
			self::removeWOOProductPages();
		}

		return;
	}

	/**
	 * Add All Points to WOO Commerce as product shop
	 */
	public static function connectWOOPages() {
		$woo_checkout_page_id = CMMicropaymentPlatform::get_option( 'woocommerce_checkout_page_id' );
		if ( ! empty( $woo_checkout_page_id ) ) {
			CMMicropaymentPlatform::get_option( 'cm_micropayment_checkout_page_id', $woo_checkout_page_id );
		}

		$woo_thanks_page_id = CMMicropaymentPlatform::get_option( 'woocommerce_thanks_page_id' );
		if ( ! empty( $woo_thanks_page_id ) ) {
			CMMicropaymentPlatform::get_option( 'cm_micropayment_success_page_id', $woo_checkout_page_id );
		}
	}

	/**
	 * Add All Points to WOO Commerce as product shop
	 */
	public static function addWOOProductPages() {

		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$pointsObj = new CMMicropaymentPlatformBackendPointsPrices();
		$points    = $pointsObj->fetchAll();

		if ( ! empty( $points ) ) {
			if ( ! term_exists( 'CM Micropayment Platform', 'product_cat' ) ) {
				$taxonomyResult = wp_insert_term( 'CM Micropayment Platform', 'product_cat' );
				$termId         = $taxonomyResult['term_id'];
			} else {
				$term   = get_term_by( 'name', 'CM Micropayment Platform', 'product_cat' );
				$termId = $term->term_id;
			}

			foreach ( $points as $pointObj ) {
				$label = $pointObj->points_value;
				$name  = sanitize_title_with_dashes( $label );

				//$eddDownload = edd_get_download($name);
				//$eddDownloadId = $eddDownload ? $eddDownload->ID : NULL;

				$downloadArr = array(
					//	'ID'             => $eddDownloadId, // Are you updating an existing post?
					'post_content'   => $label,
					// The full text of the post.
					'post_name'      => $name,
					// The name (slug) for your post
					'post_title'     => $label,
					// The title of your post.
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'post_excerpt'   => '',
					// For all your post excerpt needs.
					'comment_status' => 'closed',
					'tax_input'      => array( 'product_cat' => $termId )
					// For custom taxonomies. Default empty. [array( <taxonomy> => <array | string> )]
				);

				$newId = wp_insert_post( $downloadArr );

				if ( $newId ) {
					update_post_meta( $newId, '_regular_price', $pointObj->cost );
					update_post_meta( $newId, '_price', $pointObj->cost );
					update_post_meta( $newId, '_virtual', 'yes' );
					update_post_meta( $newId, 'cmmp_points_value', $pointObj->points_value );
					update_post_meta( $newId, 'cmmp_points_cost_id', $pointObj->points_cost_id );
				}
			}
		}
	}

	/**
	 * Remoce All Points to WOO Commerce as product shop
	 */
	public static function removeWOOProductPages() {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$pointsObj = new CMMicropaymentPlatformBackendPointsPrices();
		$points    = $pointsObj->fetchAll();
		if ( ! empty( $points ) ) {
			foreach ( $points as $pointObj ) {
				$idPost = self::getWOOProductIDByMetaKey( $pointObj->points_cost_id );
				if ( $idPost ) {
					wp_delete_post( $idPost );
				}
			}
			$term = get_term_by( 'name', 'CM Micropayment Platform', 'product_cat' );
			wp_delete_term( $term->term_id, 'product_cat' );
		}
	}

	/**
	 * Get Woo Product ID by id points cost
	 *
	 * @param id points cost
	 *
	 * @return id post
	 */
	public static function getWOOProductIDByMetaKey( $id ) {
		$args      = array(
			'post_type'  => 'product',
			'meta_query' => array(
				array(
					'key'   => 'cmmp_points_cost_id',
					'value' => $id,
				)
			)
		);
		$postslist = get_posts( $args );
		if ( ! empty( $postslist ) ) {
			return $postslist[0]->ID;
		} else {
			return false;
		}
	}

	/**
	 * Remoce simple Points to WOO Commerce as product shop
	 */
	public static function useEddCheckout( $on ) {
		$eddIntegrationState = CMMicropaymentPlatform::isEddIntegrationActive();

		/*
		 * No change - do nothing
		 */
		if ( $on == $eddIntegrationState ) {
			return;
		}

		/*
		 * Turning ON
		 */
		if ( $on ) {
			self::addEddProductPages();
		} /*
         * Turning OFF
         */ else {
			self::removeEddProductPages();
		}

		return;
	}

	public static function addEddProductPages() {
		/*
		 * Create download pages for all Points
		 */
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$pointsObj = new CMMicropaymentPlatformBackendPointsPrices();
		$points    = $pointsObj->fetchAll();

		if ( ! empty( $points ) ) {

			if ( ! term_exists( 'CM Micropayment Platform', 'download_category' ) ) {
				$taxonomyResult = wp_insert_term( 'CM Micropayment Platform', 'download_category' );
				$termId         = $taxonomyResult['term_id'];
			} else {
				$term   = get_term_by( 'name', 'CM Micropayment Platform', 'download_category' );
				$termId = $term->term_id;
			}

			foreach ( $points as $pointObj ) {
				$label = CMMicropaymentPlatform::getEddProductLabel( $pointObj->points_value );
				$name  = sanitize_title_with_dashes( $label );

				$eddDownload   = edd_get_download( $name );
				$eddDownloadId = $eddDownload ? $eddDownload->ID : null;

				$downloadArr = array(
					'ID'             => $eddDownloadId,
					// Are you updating an existing post?
					'post_content'   => $label,
					// The full text of the post.
					'post_name'      => $name,
					// The name (slug) for your post
					'post_title'     => $label,
					// The title of your post.
					'post_status'    => 'publish',
					'post_type'      => 'download',
					'post_excerpt'   => '',
					// For all your post excerpt needs.
					'comment_status' => 'closed',
					'tax_input'      => array( 'download_category' => $termId )
					// For custom taxonomies. Default empty. [array( <taxonomy> => <array | string> )]
				);

				$newDownloadId = wp_insert_post( $downloadArr );

				if ( $newDownloadId ) {
					update_post_meta( $newDownloadId, 'edd_price', edd_sanitize_amount( $pointObj->cost ) );
					update_post_meta( $newDownloadId, 'cmmp_points_value', $pointObj->points_value );
					update_post_meta( $newDownloadId, 'cmmp_points_cost_id', $pointObj->points_cost_id );
				}
			}
		}
	}

	public static function removeEddProductPages() {
		/*
		 * Delete download pages for all Points
		 */
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$pointsObj = new CMMicropaymentPlatformBackendPointsPrices();
		$points    = $pointsObj->fetchAll();

		if ( ! empty( $points ) ) {
			foreach ( $points as $pointObj ) {
				self::removeEddProductPage( $pointObj->points_value );
			}

			$term = get_term_by( 'name', 'CM Micropayment Platform', 'download_category' );
			if ( ! empty( $term ) ) {
				wp_delete_term( $term->term_id, 'download_category' );
			}
		}
	}

	/**
	 * Remove the EDD page based on the points value
	 *
	 * @param type $pointsValue
	 */
	public static function removeEddProductPage( $pointsValue ) {
		$label = CMMicropaymentPlatform::getEddProductLabel( $pointsValue );
		$name  = sanitize_title_with_dashes( $label );

		$eddDownload   = edd_get_download( $name );
		$eddDownloadId = $eddDownload ? $eddDownload->ID : null;

		if ( $eddDownloadId ) {
			wp_delete_post( $eddDownloadId );
		}
	}

	public static function addEddPointsConnectionFields( $fields ) {
		$fields[] = 'cmmp_points_cost_id';

		return $fields;
	}

	public static function addEddPointsConnection( $post_id ) {
		require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-points-prices.php';
		$pointsValue            = get_post_meta( $post_id, 'cmmp_points_value', true );
		$pointsCostId           = get_post_meta( $post_id, 'cmmp_points_cost_id', true );
		$isMicropaymentDownload = has_term( 'CM Micropayment Platform', 'download_category', $post_id );

		if ( ! $isMicropaymentDownload ) {
			return;
		}
		$options = CMMicropaymentPlatformBackendPointsPrices::getAll();

		$selectOptions = array();
		if ( ! empty( $options ) ) {
			foreach ( $options as $value ) {
				$selectOptions[ $value->points_cost_id ] = $value->points_value;
				if ( empty( $pointsCostId ) && ! empty( $pointsValue ) && $pointsValue == $value->points_value ) {
					$pointsCostId = $value->points_cost_id;
					update_post_meta( $post_id, 'cmmp_points_cost_id', $pointsCostId );
				}
			}
		}
		?>
        <p><strong><?php _e( 'CM Micropayment Platform Options:', 'edd' ); ?></strong></p>
        <p>
            <label for="cmmp_points_cost_id">
				<?php
				echo EDD()->html->select( array(
					'name'             => 'cmmp_points_cost_id',
					'options'          => $selectOptions,
					'show_option_all'  => null,
					'show_option_none' => __( 'None', 'edd' ),
					'selected'         => $pointsCostId
				) );
				?>
				<?php _e( 'CM Micropayments Association', 'edd' ); ?>
            </label>
        </p>
		<?php
	}

	public static function displayErrorsMessages() {
		CMMicropaymentPlatform::checkMicropaymentPlatformIsWorking( false );

		if ( ! empty( CMMicropaymentPlatform::$errors ) ) {
			foreach ( CMMicropaymentPlatform::$errors as $message ) {
				$message = CMMP_NAME . ' : ' . $message;
				cminds_show_message( $message, true );
			}
		}
		if ( ! empty( CMMicropaymentPlatform::$messages ) ) {
			foreach ( CMMicropaymentPlatform::$messages as $message ) {
				$message = CMMP_NAME . ' : ' . $message;
				cminds_show_message( $message, false );
			}
		}
	}

	public static function handleExport() {
		if ( ! empty( $_POST['cmmp_doExportHidden'] ) ) {
			self::__cmmp_exportWallets();
		}
	}

	/**
	 * Exports the wallets
	 */
	public static function __cmmp_exportWallets() {
		$exportData   = array( 0 => array( 'email', 'points' ) );
		$walletObject = new CMMicropaymentPlatformWallet();
		$wallets      = $walletObject->getWallets();
		if ( $wallets ) {
			foreach ( $wallets as $oneWallet ) {
				$user = get_user_by( 'ID', $oneWallet->user_id );
				if ( ! empty( $user->data->user_email ) ) {
					$walletDetails = $walletObject->getWallet( $oneWallet->wallet_id );
					$exportData[]  = array( $user->data->user_email, $walletDetails->points );
				}
			}
		}
		$outstream = fopen( "php://temp", 'r+' );

		foreach ( $exportData as $line ) {
			fputcsv( $outstream, $line, ',', '"' );
		}
		rewind( $outstream );

		header( 'Content-Encoding: UTF-8' );
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=wallets_export_' . date( 'Ymd_His', current_time( 'timestamp' ) ) . '.csv' );
		/*
		 * Why including the BOM? - Marcin
		 */
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		while ( ! feof( $outstream ) ) {
			echo fgets( $outstream );
		}
		fclose( $outstream );
		exit;
	}

}
