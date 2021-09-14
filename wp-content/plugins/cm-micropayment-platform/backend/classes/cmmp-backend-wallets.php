<?php

if ( !class_exists( 'CMMicropaymentPlatformWallet' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
}
if ( !class_exists( 'CMMicropaymentPlatformWalletCharges' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
}

class CMMicropaymentPlatformBackendWallets extends CMMicropaymentPlatformWallet {

	private static $_tableName;

	public function __construct() {
		global $wpdb;

		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			self::$_tableName = $wpdb->base_prefix . "cm_micropayments_wallets";
		} else {
			self::$_tableName = $wpdb->prefix . "cm_micropayments_wallets";
		}
	}

	static function getTableName(){
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			return $wpdb->base_prefix . "cm_micropayments_wallets";
		} else {
			return $wpdb->prefix . "cm_micropayments_wallets";
		}
	}
	public function render() {
		global $wpdb;

		echo '<div class="wrap">';

		$action = isset( $_GET[ 'cmm-action' ] ) ? $_GET[ 'cmm-action' ] : null;
		switch ( $action ) {
			case 'active':
				self::changeStatus( $_GET[ 'cmm-id' ], 1 );
				break;
			case 'deactive':
				self::changeStatus( $_GET[ 'cmm-id' ], 0 );
				break;
			case 'remove':
				self::remove( $_GET[ 'cmm-id' ] );
				break;
			default:
				require_once CMMP_PLUGIN_DIR . '/backend/classes/wallets/list.php';

				$wp_list_table = new CMMicropaymentPlatformBackendWalletsList();
				if ( isset( $_POST[ 's' ] ) ) {
					$wp_list_table->prepare_items( $_POST[ 's' ] );
				} else {
					$wp_list_table->prepare_items();
				}

				echo '<div class="icon32" id="icon-plugins"><br /></div>';
				echo '<h2>' . __( 'CM Micropayments' ) . ' - ' . __( 'Wallets' ) . '</h2>';
				include CMMP_PLUGIN_DIR . '/backend/views/common/notifications.phtml';

				echo '<form method="post" action>';
				$wp_list_table->search_box( 'search', 'search_id' );
				$wp_list_table->display();

				echo '</form>';
				break;
		}
		echo '</div>';
	}


	private static function changeStatus( $id, $status_code ) {
		global $wpdb;
		if ( isset( $id ) && $id != '' ) {
			$wpdb->update( self::$_tableName, array( 'status' => $status_code ), array( 'wallet_id' => $id ) );

			if ( (int) $status_code === CMMicropaymentPlatformWallet::STATUS_ACTIVE ) {
				$msg = __( 'Wallet has been activated' );
			} else {
				$msg = __( 'Wallet has been deactivated' );
			}

			$_SESSION[ 'success-message' ] = $msg;
		} else {
			$_SESSION[ 'error-message' ] = __( 'Error occured while changing wallet status' );
		}

		if ( !headers_sent() ) {
			wp_redirect( admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ), 301 );
			exit;
		} else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="' . admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ) . '";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url=' . admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ) . '" />';
			echo '</noscript>';
		}
	}

	private static function remove( $id ) {
		global $wpdb;

		if ( isset( $id ) && $id != '' ) {
			$wpdb->delete( self::$_tableName, array( 'wallet_id' => $id ) );
			$_SESSION[ 'success-message' ] = __( 'Wallet has been removed' );
		} else {
			$_SESSION[ 'error-message' ] = __( 'Error occured while deleting wallet' );
		}

		if ( !headers_sent() ) {
			wp_redirect( admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ), 301 );
			exit;
		} else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="' . admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ) . '";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url=' . admin_url( 'admin.php?page=cm-micropayment-platform-wallet' ) . '" />';
			echo '</noscript>';
		}
	}

	public function _ajaxSavePoints() {
		global $wpdb;
		if ( isset( $_REQUEST[ 'wallet_id' ] ) ) {
			$postData = $_REQUEST;

			if ( !isset( $postData[ 'wallet_id' ] ) || !is_numeric( $postData[ 'wallet_id' ] ) || $postData[ 'wallet_id' ] <= 0 ) {
				$return[ 'error' ] = __( 'Invalid ID' );
			}
//
			if ( !isset( $postData[ 'points' ] ) || !CMMicropaymentPlatform::numericOrFloat( $postData[ 'points' ] )  || $postData[ 'points' ] < 0 ) {
				$return[ 'error' ] = __( 'Invalid points value' );
			}
			$postData[ 'points' ] = CMMicropaymentPlatform::convertType($postData[ 'points' ]);

			if ( !isset( $postData[ 'reason' ] ) || empty( $postData[ 'reason' ] ) ) {
				$return[ 'error' ] = __( 'Reason is mandatory' );
			}
			if ( !isset( $return[ 'error' ] ) ) {
				parent::__construct();

				$wpdb->query( 'START TRANSACTION' );

				$wallet = $this->getWallet( $postData[ 'wallet_id' ] );

				if ( $wallet ) {

					$walletTransactions = new CMMicropaymentPlatformWalletCharges();

					$pointsDiff = $postData[ 'points' ] - $wallet->points;

					$this->setPoints( $wallet->wallet_name, $postData[ 'points' ] );

					$walletTransactions->log( $pointsDiff, 0, $postData[ 'wallet_id' ], 4, 1, $postData[ 'reason' ] );

					$result = $this->getWallet( $postData[ 'wallet_id' ] );

					$wpdb->query( 'COMMIT' );
				}

				if ( isset( $result ) ) {
					$return = (array) $result;
				} else {
					$return[ 'error' ] = __( 'No post found' );
				}
			}
		} else {
			$return[ 'error' ] = __( 'No post found' );
		}

		echo json_encode( $return );
		die();
	}

}
