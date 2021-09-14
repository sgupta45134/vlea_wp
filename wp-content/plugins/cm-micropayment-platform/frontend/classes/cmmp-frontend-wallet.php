<?php

if ( !class_exists( 'CMMicropaymentPlatformWalletCharges' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
}

if ( !class_exists( 'CMMicropaymentPlatformWallet' ) ) {
	require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
}

if ( !class_exists( 'CMMicropaymentPlatformBackendStorePurchasesUser' ) ) {
	require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-store-user.php';
}

class CMMicropaymentPlatformFrontendWallet extends CMMicropaymentPlatformWallet {

	public static $calledClassName;
	protected static $instance = NULL;
	private static $dbWalletCharges;

	public static function instance() {
		$class = __CLASS__;
		if ( !isset( self::$instance ) && !( self::$instance instanceof $class ) ) {
			self::$instance = new $class;
		}
		return self::$instance;
	}

	public function __construct() {
		if ( empty( self::$calledClassName ) ) {
			self::$calledClassName = __CLASS__;
		}
		self::$dbWalletCharges = new CMMicropaymentPlatformWalletCharges();
	}

	public function render() {
		$content = '';
		
//		self::registerScripts();
//		self::registerStyles();

		if ( ob_start() ) {
			// $data	 = array(
				// 'ajaxurl'	 => admin_url( 'admin-ajax.php' ),
				// 'l18n'		 => array(
					// 'missing_wallet_id' => __( 'Missing wallet ID' )
				// )
			// );
			// wp_localize_script( 'cm-micropayment-scripts', 'cmmp_data', $data );
			include CMMP_PLUGIN_DIR . '/frontend/views/wallet/form.phtml';
			$content .= ob_get_clean();
		}

		return $content;
	}

	public function createWalletID() {
		$content = '';
		$is_logged = false;
		$user_wallets = array();

//		self::registerScripts();
//		self::registerStyles();

		// wp_localize_script(
			// 'cm-micropayment-scripts',
			// 'CMMPdata',
			// array(
				// 'ajaxurl' => admin_url( 'admin-ajax.php' ),
				// 'hideCreateButtonAfterAction' => CMMicropaymentPlatform::get_option( 'cm_micropayment_hide_create_button_after_action' )
			// )
		// );

		$only_one_w_allowed = (bool) CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer' );
		$wallets_qty_limit = (int) ( $only_one_w_allowed ? 1 : CMMicropaymentPlatform::get_option( 'cm_micropayment_number_of_wallets', 1 ) );

		$user = wp_get_current_user();
		if ( $user->ID !== 0 ) {
			if ( $only_one_w_allowed || ( $wallets_qty_limit === 1 ) ) {
				$user_wallets[] = $this->getWalletNameByUserID( $user->ID );
			} else {
				$user_wallets = $this->getWalletsByUserID( $user->ID );
			}
			$is_logged = true;
		}

		if ( ob_start() ) {
			$user_wallets = array_filter($user_wallets, function($el) { return !empty($el);});
			include CMMP_PLUGIN_DIR . '/frontend/views/create-wallet-button.phtml';
			$content .= ob_get_clean();
		}

		return $content;
	}
	
	public function createTipButton($atts) {
//		self::registerStyles();

		$user = wp_get_current_user();
		if($user->ID){
			$user_id = $user->ID;
		}else{
			$unlogged = true;
		}

		if ( ob_start() ) {
			include CMMP_PLUGIN_DIR . '/frontend/views/tip-button.phtml';
			$content = ob_get_clean();
			return $content;
		}
	}

	public function getFormatedInfo($show_wallet_id = true) {
		if ( ob_start() ) {
			if ( isset( $_POST[ 'wallet_id' ] ) ) {
				$data = $this->prepareData( $_POST[ 'wallet_id' ] );

				if ( isset( $_SESSION[ 'sent_wallet_id' ] ) ) {
					unset( $_SESSION[ 'sent_wallet_id' ] );
				}

				$_SESSION[ 'sent_wallet_id' ] = $_POST[ 'wallet_id' ];
				$walletName = $_POST[ 'wallet_id' ];
				if ( isset( $data[ 'error' ] ) ) {
					echo json_encode( array( 'success' => false, 'error' => $data[ 'error' ] ) );
				} else {
					include CMMP_PLUGIN_DIR . '/frontend/views/wallet/data.phtml';
					$content = ob_get_clean();

					echo json_encode( array( 'success' => true, 'content' => $content ) );
				}
				die();
			} else {
				echo json_encode( array( 'success' => false, 'error' => __( 'Wallet ID is empty' ) ) );
			}
		}
	}

	public function getWalletHistory($show_wallet_id = false) {
		$content	 = '';
		$loggedUser	 = wp_get_current_user();
		if ( $loggedUser->ID == 0 ) {
			if ( isset( $_SESSION[ 'sent_wallet_id' ] ) ) {
				if ( $show_wallet_id ) {
					$walletName = $_SESSION[ 'sent_wallet_id' ];
				}
				$data			 = $this->prepareData( $_SESSION[ 'sent_wallet_id' ] );
				$isFromSession	 = true;
				if ( ob_start() ) {
					include CMMP_PLUGIN_DIR . '/frontend/views/wallet/data.phtml';
					$content .= ob_get_clean();
				}
			} else {
				$content .= $this->render();
			}
		} else {
			if ( ob_start() ) {
//				self::registerStyles();
				$walletName = $this->getWalletNameByUserID( $loggedUser->ID );
				$data = $this->prepareData( $walletName );
				include CMMP_PLUGIN_DIR . '/frontend/views/wallet/data.phtml';
				$content .= ob_get_clean();
			}
		}

		return $content;
	}

	public function getTransactionWalletID() {
		$wallet = $this->getTransactionWallet();

		return ($wallet) ? $wallet->wallet_name : '';
	}

	public function checkWalletID() {
		parent::__construct();

		if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer' ) ) {
			if ( isset($_POST[ 'wallet_id' ]) && $_POST[ 'wallet_id' ] != '' ) {
				$wallet_id = $_POST[ 'wallet_id' ];
				$wallet	 = $this->getWalletByCode( $wallet_id );
				if ( !$wallet ) {
					$response = array(
						'success'	 => false,
						'message'	 => __( 'Invalid Wallet ID.' )
					);
					echo json_encode( $response );
					die();
				}
			} else {
				$user = wp_get_current_user();
				if ( $user->ID == 0 ) {
					$response = array(
						'success'	 => false,
						'message'	 => __( 'Wallet is inactive. Please choose another one.' )
					);
					echo json_encode( $response );
					die();
				} else {
					$wallet_id = $this->getWalletNameByUserID( $user->ID );
				}
			}
		} else {
			$wallet_id = $_POST[ 'wallet_id' ];
		}


		$wallet	 = $this->getWalletByCode( $wallet_id );
		$success = true;
		$msg	 = '';

		if ( $wallet ) {
			if ( isset( $wallet->status ) ) {
				if ( (int) $wallet->status === self::STATUS_INACTIVE && CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_charge_inactive_wallets', 0 ) != 1 ) {
					$success = false;
					$msg	 = __( 'Wallet is inactive. Please choose another one.' );
				}
			}
		}

		$response = array(
			'success'	 => $success,
			'message'	 => $msg
		);
		echo json_encode( $response );
		die();
	}

	public function getTransactionWalletPoints() {
		$wallet = $this->getTransaction();
		return ($wallet) ? $wallet->points : '(session_error)';
	}

	public function hasEnoughPoints( $args, $assignWallet = false ) {
		parent::__construct();

		$errors	 = array();
		$success = false;

		if ( !isset( $args[ 'wallet_id' ] ) ) {
			$errors[] = __( 'Missing wallet ID' );
		}

		if ( !isset( $args[ 'points' ] ) ) {
			$errors[] = __( 'Missing points' );
		}

		if ( count( $errors ) > 0 ) {
			return array( 'success' => $success, 'message' => $errors );
		}

		$this->syncExternalWallet(['wallet_name'=> $args[ 'wallet_id' ]]);


		$wallet = $this->getWalletByCode( $args[ 'wallet_id' ] );
		if ( $wallet ) {
			if ( (int) $wallet->status === self::STATUS_ACTIVE ) {
				if ( $wallet->points >= $args[ 'points' ] ) {
					$success = true;
				} else {
					$errors[] = __( 'Wallet has not got enough ' . CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) );
				}
			} else {
				$errors[] = __( 'Wallet is inactive' );
			}
		} else {
			$errors[] = __( 'Wallet does not exists' );
		}
		$result = array( 'success' => $success, 'message' => $errors );

		if ( $assignWallet ) {
			$result[ 'wallet' ] = $wallet;
		}
		return $result;
	}

    public function hasUserEnoughPoints( $args ) {
        parent::__construct();

        $errors	 = array();
        $success = false;

        if ( !isset( $args[ 'username' ] ) ) {
            $errors[] = __( 'Missing user name' );
        }

        if ( !isset( $args[ 'points' ] ) ) {
            $errors[] = __( 'Missing points' );
        }

        if ( count( $errors ) > 0 ) {
            return array( 'success' => false, 'message' => $errors );
        }

        $user = get_user_by( 'login', $args[ 'username' ] );

        if (empty($user)){
            $user = wp_get_current_user();
        }

        if ( $user->ID != 0 ) {
            $wallet = $this->getWalletByUserID( $user->ID );

            if ( $wallet ) {
                if ( (int) $wallet->status === self::STATUS_ACTIVE ) {
                    if ( $wallet->points >= $args[ 'points' ] ) {
                        return array( 'success' => true );
                    } else {
                        $errors[] = __( 'Wallet has not got enough ' . CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) );
                    }
                } else {
                    $errors[] = __( 'Wallet is inactive' );
                }
            } else {
                $errors[] = __( 'Wallet does not exists' );
            }
            $result = array( 'success' => false, 'message' => $errors );
        } else {
            $result = array( 'success' => false, 'message' => array( __( 'User not found' ) ) );
        }
        return $result;
    }

	public function hasUserEnoughPointsByUserId( $args ) {
		parent::__construct();

		$errors	 = array();
		$success = false;

		if ( !isset( $args[ 'user_id' ] ) ) {
			$errors[] = __( 'Missing user id' );
		}

		if ( !isset( $args[ 'points' ] ) ) {
			$errors[] = __( 'Missing points' );
		}

		if ( count( $errors ) > 0 ) {
			return array( 'success' => false, 'message' => $errors );
		}

		$wallet = $this->getWalletByUserID( $args[ 'user_id' ] );

		if ( $wallet ) {
			if ( (int) $wallet->status === self::STATUS_ACTIVE ) {
				if ( $wallet->points >= $args[ 'points' ] ) {
					$success = true;
				} else {
					$errors[] = __( 'Wallet has not got enough ' . CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) );
				}
			} else {
				$errors[] = __( 'Wallet is inactive' );
			}
		} else {
			$errors[] = __( 'Wallet does not exists' );
		}
		$result = array( 'success' => $success, 'message' => $errors );
		return $result;
	}

	/**
	 *
	 *
	 * @param unknown $args array (user_id, points, type, status)
	 * @return string[]|boolean[]
	 */
	public function grantForPurchase( $args ) {
		global $wpdb;
		parent::__construct();

		$wallet = $this->getWalletByUserID( $args[ 'user_id' ] );
		if ( empty( $wallet ) ) {
			return array( 'success' => false, 'message' => 'No wallet' );
		} elseif ( (int) $wallet->status !== self::STATUS_ACTIVE && CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_charge_inactive_wallets', 0 ) != 1 ) {
			return array( 'success' => false, 'message' => 'Can not grant wallet due to its status is not active' );
		}

		$wpdb->query( 'START TRANSACTION' );
		$walletTransactions = new CMMicropaymentPlatformWalletCharges();

		$addedPoints = isset( $args[ 'points' ] ) ? $args[ 'points' ] : 0;
		$points		 = $wallet->points + $addedPoints;

		$this->setPoints( $wallet->wallet_name, $points );

		$transactionType	 = !empty( $args[ 'type' ] ) ? $args[ 'type' ] : CMMicropaymentPlatformWalletCharges::TYPE_EDD_PURCHASE_GRANT;
		$transactionStatus	 = !empty( $args[ 'status' ] ) ? $args[ 'status' ] : 1;

		$walletTransactions->log( $args[ 'points' ], 0, $wallet->wallet_id, $transactionType, $transactionStatus );

		$result = $this->getWalletByCode( $wallet->wallet_name );

		$sendNotificationEmail = CMMicropaymentPlatform::get_option( 'cm_micropayment_send_purchase_grant_notifications', FALSE );
		if ( $sendNotificationEmail ) {
			CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_grant_for_purchase', array( 'amountPoints' => $addedPoints ) );
		}
		$wpdb->query( 'COMMIT' );

		return array( 'success' => true );
	}

	public function withdrawExternal( $args ) {
		global $wpdb;
		parent::__construct();

		$validationResult = $this->hasEnoughPoints( $args, true );

		if ( $validationResult[ 'success' ] ) {
			$new_args = $args;
			$new_args['wallet_name'] = $args[ 'wallet_id' ];


			$wpdb->query( 'START TRANSACTION' );

			$wallet				 = $validationResult[ 'wallet' ];
			$walletTransactions	 = new CMMicropaymentPlatformWalletCharges();

			$points = $wallet->points - $args[ 'points' ];

			$this->setPoints( $wallet->wallet_name, $points );

			$transactionType	 = isset( $args[ 'type' ] ) ? $args[ 'type' ] : 2;
			$transactionStatus	 = isset( $args[ 'status' ] ) ? $args[ 'status' ] : 1;
			$args['comment'] .= " External transaction";
			$walletTransactions->log( -$args[ 'points' ], 0, $wallet->wallet_id, $transactionType, $transactionStatus );

			$result = $this->getWalletByCode( $wallet->wallet_name );
			CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_withdraw', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'		 => $wallet->user_id,
					'amountPoints'		 => abs( $args[ 'points' ] ),
				) );
			$wpdb->query( 'COMMIT' );
			return array( 'success' => true );
		} else {
			unset( $validationResult[ 'wallet' ] );
			return $validationResult;
		}
	}

	public function withdraw( $args ) {
		global $wpdb;
		parent::__construct();

		$validationResult = $this->hasEnoughPoints( $args, true );

		if ( $validationResult[ 'success' ] ) {
			$new_args = $args;
			$new_args['wallet_name'] = $args[ 'wallet_id' ];


			$wpdb->query( 'START TRANSACTION' );

			$wallet				 = $validationResult[ 'wallet' ];
			$walletTransactions	 = new CMMicropaymentPlatformWalletCharges();

			$points = $wallet->points - $args[ 'points' ];

			$this->setPoints( $wallet->wallet_name, $points );

			$transactionType	 = !empty( $args[ 'type' ] ) ? $args[ 'type' ] : 2;
			$transactionStatus	 = !empty( $args[ 'status' ] ) ? $args[ 'status' ] : 1;

			$walletTransactions->log( -$args[ 'points' ], 0, $wallet->wallet_id, $transactionType, $transactionStatus, $args['comment'] );

			$this->addPointsToStorePurchasesWallet($args, $walletTransactions, $transactionStatus, $wallet->wallet_name);
			
			$result = $this->getWalletByCode( $wallet->wallet_name );
			CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_withdraw', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $wallet->user_id,
					'amountPoints' => abs( $args[ 'points' ] ),
				) );
			$wpdb->query( 'COMMIT' );

			$this->syncExternalWallet( $new_args, 1 );

			return array( 'success' => true );
		} else {
			unset( $validationResult[ 'wallet' ] );
			return $validationResult;
		}
	}

	public function withdrawByUserName( $args ) {
		global $wpdb;
		parent::__construct();

		$validationResult = $this->hasUserEnoughPoints( $args, true );

		if ( $validationResult[ 'success' ] ) {
			$wpdb->query( 'START TRANSACTION' );

			$wallet	 = $validationResult[ 'wallet' ];
			$walletTransactions	 = new CMMicropaymentPlatformWalletCharges();
			$points = $wallet->points - $args[ 'points' ];
			$this->setPoints( $args[ 'wallet_id' ], $points );
			$walletTransactions->log( $args[ 'points' ], 0, $args[ 'wallet_id' ], 2, 1 );
			$result = $this->getWallet( $args[ 'wallet_id' ] );
			CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_withdraw', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $wallet->user_id,
					'amountPoints' => abs( $points ),
				) );
			$wpdb->query( 'COMMIT' );

			return array( 'success' => true );
		} else {
			unset( $validationResult[ 'wallet' ] );
			return $validationResult;
		}
	}

	private function prepareData( $walletID ) {
		parent::__construct();
		if ( is_numeric($walletID) ) {
			$row = $this->getWallet( $walletID );
		} else {
			$row = $this->getWalletByCode( $walletID );
		}

		if ( !$row ) {
			return array( 'error' => 'No wallet found' );
		} elseif ( ( $row->user_id == get_current_user_id() ) || current_user_can('manage_options') ) {
			$transactions = self::$dbWalletCharges->getTransactionsByWalletID( $row->wallet_id );
			$count = self::$dbWalletCharges->countTransactionsByWalletID( $row->wallet_id );

			return array(
				'walletID'		 => $row->wallet_name,
				'actualPoints'	 => $row->points,
				'transactions'	 => $transactions,
				'count'			 => count( $count ),
				'per_page'		 => CMMicropaymentPlatform::get_option( 'cm_micropayment_number_of_transactions', 3 ),
			);
		} else {
			return array( 'error' => 'No wallet found for user ' . wp_get_current_user()->display_name );
		}
	}

	private static function registerScripts() {
		// wp_enqueue_script( 'jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/js/jquery-ui/jquery-ui-1.10.4.custom.min.js', array( 'jquery' ) );
		// wp_register_script( 'cm-micropayment-scripts-table-sorter', CMMP_PLUGIN_URL . 'frontend/assets/js/jquery.tablesorter.min.js', array('jquery', 'jquery-ui') );
		// wp_register_script( 'cm-micropayment-scripts', CMMP_PLUGIN_URL . 'frontend/assets/js/scripts.js', array('jquery', 'jquery-ui', 'cm-micropayment-scripts-table-sorter'), CMMicropaymentPlatform::version() );

		// wp_enqueue_script( 'cm-micropayment-scripts-table-sorter' );
		// wp_enqueue_script( 'cm-micropayment-scripts' );
	}

	private static function registerStyles() {
		// wp_register_style( 'cm-micropayment-frontend-style', CMMP_PLUGIN_URL . 'frontend/assets/css/style.css' );
		// wp_enqueue_style( 'cm-micropayment-frontend-style' );
	}

	private function getTransactionWallet() {
		$wallet = null;
		$transaction = $this->getTransaction();
		if ( FALSE === $transaction ) {
			$session = '';
			if (function_exists('edd_get_purchase_session')) {
				$session = edd_get_purchase_session();
			}
			if ( isset( $_GET[ 'payment_key' ] ) ) {
				$payment_key = urldecode( $_GET[ 'payment_key' ] );
			} else if ( $session ) {
				$payment_key = $session[ 'purchase_key' ];
			}

			// No key found
			if ( !isset( $payment_key ) ) {
				return;
			}

			$transaction_id	 = edd_get_purchase_id_by_key( $payment_key );
			$user_can_view	 = edd_can_view_receipt( $payment_key );

			// Key was provided, but user is logged out. Offer them the ability to login and view the receipt
			if ( $user_can_view ) {
				self::$dbWalletCharges->confirm( $transaction_id );

				$transaction = self::$dbWalletCharges->getTransaction( $transaction_id );
				$wallet		 = $this->getWallet( $transaction->wallet_id );
			}
		} else {
			$wallet = $this->getWallet( $transaction->wallet_id );
		}
		return $wallet;
	}

	private function getTransaction() {
		$transaction_id = '';
		if(isset($_SESSION['successful_id'])) {
			$transaction_id = $_SESSION['successful_id'];
		} else if(isset($_GET['s_id'])) {
			$transaction_id = $_GET['s_id'];
		}
		if($transaction_id == '') {
			return false;
		}
		self::$dbWalletCharges->confirm( $transaction_id );
		$transaction = self::$dbWalletCharges->getTransaction( $transaction_id );
		return $transaction;
	}

}
