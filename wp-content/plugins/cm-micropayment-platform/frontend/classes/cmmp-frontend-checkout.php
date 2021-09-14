<?php

if ( ! class_exists( 'CMMicropaymentPlatformPointsPrices' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/points.php';
}

if ( ! class_exists( 'CMMicropaymentPlatformWalletCharges' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
}

if ( ! class_exists( 'CMMicropaymentPlatformWallet' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
}

if ( ! class_exists( 'PayPalLib' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/libs/paypal.php';
}

if ( ! class_exists( 'CMMicropaymentPlatformTransactions' ) ) {
	require_once CMMP_PLUGIN_DIR . 'shared/models/transactions.php';
}

if ( ! class_exists( 'CMMicropaymentPlatformTransactionsHistory' ) ) {
	require_once CMMP_PLUGIN_DIR . 'shared/models/transactions-history.php';
}


class CMMicropaymentPlatformFrontendCheckout {

	public static $calledClassName;
	protected static $instance = null;
	private static $dbPoints;
	private static $dbWalletCharges;
	private static $dbWallet;
	private static $error = false;

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
		self::$dbPoints        = new CMMicropaymentPlatformPointsPrices();
		self::$dbWalletCharges = new CMMicropaymentPlatformWalletCharges();
		self::$dbWallet        = new CMMicropaymentPlatformWallet();
	}

	public function prepareData() {
		$return = array();

		$return['points_prices'] = self::$dbPoints->fetchAll();

		return $return;
	}

	public static function handlePost() {
		static $postHandled = null;

		if ( $postHandled === null && isset( $_POST ) && count( $_POST ) > 0 ) {
			if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
				$postData = $_POST;
				if ( $postData['points'] ) {
					$label         = CMMicropaymentPlatform::getEddProductLabel( $postData['points'] );
					$name          = sanitize_title_with_dashes( $label );
					$eddDownload   = edd_get_download( $name );
					$eddDownloadId = $eddDownload ? $eddDownload->ID : null;

					if ( $eddDownloadId ) {
						$checkoutUrl = edd_get_checkout_uri( array(
							'edd_action'  => 'add_to_cart',
							'download_id' => $eddDownloadId,
						) );

						if ( headers_sent() ) {
							?>
							<script>
                                window.location.href = "<?php echo ($checkoutUrl); ?>";

							</script>
							<?php

						} else {
							wp_redirect( $checkoutUrl );
						}
						die();
					}
				}
			} else {
				self::$error = self::processCheckout();
			}

			$postHandled = true;
		}
	}

	public function render($manual = false) {
		return $this->getTemplate( self::$error, $manual );
	}

	public function getTemplate( $error = false, $manual = false ) {
		self::registerScripts();
		$loggedUser = wp_get_current_user();
		$isLogged   = ( $loggedUser->ID != 0 );

		$usePayPal = CMMicropaymentPlatform::get_option( 'cm_micropayment_use_paypal', 1 );
		if ( ! $usePayPal ) {
			$pointsLabel = CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );

			return sprintf( __( __cm( 'paypal_disabled_message' ) ), $pointsLabel );
		}

		if ( $isLogged ) {
			$walletName = self::$dbWallet->getWalletNameByUserID( $loggedUser->ID );
		} else {
			$walletName = '';
		}

		if ( ob_start() ) {
			$data     = $this->prepareData();
			$postData = $_POST;
			include CMMP_PLUGIN_DIR . '/frontend/views/checkout.phtml';
			$content = ob_get_clean();

			return $content;
		}
	}

	public static function processCheckout() {
		global $wpdb;
		$postData = $_POST;

		if ( ! isset( $postData['referrer'] ) || $postData['referrer'] != 'checkout-page-cmmicropayment' ) {
			return;
		}

		if ( ! isset( $postData['points'] ) ) {
			return __( 'No points selected' );
		}
		if ( isset( $_SESSION['transaction_id'] ) ) {
			unset( $_SESSION['transaction_id'] );
		}
		if ( isset( $_SESSION['payment_token'] ) ) {
			unset( $_SESSION['payment_token'] );
		}

		try {
			$wpdb->query( 'START TRANSACTION' );
			$gateway = new PaypalLib();

			$points_price = self::$dbPoints->getPriceByValue( $postData['points'] );
			if ( ! $points_price ) {
				$points_price = self::$dbPoints->getPriceByValue(1) * $postData['points'];
			}

			if ( ! $points_price ) {
				throw new Exception( __( 'Invalid points value' ) );
			}

			$wallet_id = false;

			if ( $postData['wallet_id'] ) {
				$wallet    = self::$dbWallet->getWalletByCode( $postData['wallet_id'] );
				$wallet_id = ( isset( $wallet->wallet_id ) ) ? $wallet->wallet_id : null;

				if ( isset( $wallet->status ) ) {
					if ( (int) $wallet->status !== CMMicropaymentPlatformWallet::STATUS_ACTIVE && CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_charge_inactive_wallets', 0 ) != 1 ) {
						throw new Exception( __( 'Wallet exists but is not active. Please select another wallet.' ) );
					}
				}
			}

			if ( ! $wallet_id ) {
				$wallet_name = self::$dbWallet->createWallet();
				$wallet_id   = self::$dbWallet->getWalletIdByCode( $wallet_name );
			}

			$transaction = CMMicropaymentPlatformWalletCharges::log( $postData['points'], $points_price, $wallet_id );

			$currency = ( ( CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) != '' ) ? CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) : 'USD' );

			CMMicropaymentPlatformTransactions::createTransaction( $transaction );

			$wpdb->query( 'COMMIT' );
			$gateway->setTransactionId( $transaction );
			$description = ( $postData['points'] > 1 ? $postData['points'] . ' ' . CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name', 'points' ) : $postData['points'] . ' ' . CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name', 'point' ) ) . ' purchase on ' . get_bloginfo();
			$gateway->doPayment( $points_price, $description, '', strtoupper( $currency ) );
		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );

			return __( $e->getMessage() );
		}
	}

	public function finalize() {
		global $wpdb;

		if ( ! isset( $_GET['transaction'] ) ) {
			return;
		}

		$transaction_id = base64_decode( $_GET['transaction'] );

		$wpdb->query( 'START TRANSACTION' );

		if ( CMMicropaymentPlatformTransactions::isPending( $transaction_id ) == 0 ) {
			self::$dbWalletCharges->confirm( $transaction_id );

			$transaction = self::$dbWalletCharges->getConfirmedTransaction( $transaction_id );

			self::$dbWallet = new CMMicropaymentPlatformWallet();

			self::$dbWallet->chargeWallet( $transaction->wallet_id, $transaction->points, false, false );

			self::$dbWallet->chargeTransactionFee( $transaction->wallet_id, true, $transaction->points );

			CMMicropaymentPlatformTransactions::updateStatus( $transaction_id, 1 );
			$_SESSION['successful_id'] = $transaction_id;
			$wpdb->query( 'COMMIT' );
			wp_redirect( get_page_link( CMMicropaymentPlatform::get_option( 'cm_micropayment_success_page_id' ) ) . '?s_id=' . $transaction_id );
		} else {
			wp_redirect( get_page_link( CMMicropaymentPlatform::get_option( 'cm_micropayment_error_page_id' ) ) );
		}
	}

	private static function registerScripts() {
		// wp_enqueue_script( 'cm-micropayment-scripts', CMMP_PLUGIN_URL . 'frontend/assets/js/scripts.js', array('jquery'), CMMicropaymentPlatform::version() );
		// wp_localize_script( 'cm-micropayment-scripts', 'cmmp_data', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		// wp_register_style( 'cm-micropayment-frontend-style', CMMP_PLUGIN_URL . 'frontend/assets/css/style.css', array(), CMMicropaymentPlatform::version() );
		// wp_enqueue_style( 'cm-micropayment-frontend-style' );
	}

}
