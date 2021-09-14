<?php

include_once CMMP_PLUGIN_DIR . '/shared/models/notification.php';
include_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
include_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-fees-user.php';

class CMMicropaymentPlatformWallet {

	private static $_tableName;
	private static $_log;
	private static $instance;
	
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 0;

	public static function instance() {
		$class = __CLASS__;
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof $class ) ) {
			self::$instance = new $class;
		}

		return self::$instance;
	}

	public static function setTableName() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		self::$_tableName = $tablePrefix . "_wallets";
	}

	private static function getTable() {
		self::setTableName();

		return self::$_tableName;
	}

	public function __construct() {
		self::setTableName();
		self::$_log = new CMMicropaymentPlatformWalletCharges();
	}

	public static function syncExternalWallet( $data , $withdraw = false) {
		$enable = CMMicropaymentPlatform::get_option( "cmmp-external-wallet" );
		global $wpdb;

		if ( $enable != 'on' ) {
			return false;
		}

		if ( $withdraw ) {
			CMMicropaymentPlatform::withdrawExternalWallet( $data );
		}

		if ( isset( $data['wallet_name'] ) ) {
			$w_data = array();
			$exw = CMMicropaymentPlatform::getExternalWalletByName( $data['wallet_name'] );
			if ( $exw && isset($exw->wallet_name)) {
				// unset($exw['user_email']);
				// unset($exw['wallet_id']);
				// unset($exw['user_id']);
				$w_data['wallet_name'] = $exw->wallet_name;
				$w_data['points'] = $exw->points;
				$w_data['status'] = $exw->status;
				
				$wpdb->update( self::getTable(), (array)$w_data, array( 'wallet_name' => $w_data['wallet_name'] ) );
			}
		}
	}
	/*
	 * $localWs array of wallet names
	 */
	public static function syncBulkExternalsWallet( $localWs , $withdraw = false) {
		$enable = CMMicropaymentPlatform::get_option( "cmmp-external-wallet" );
		global $wpdb;

		if ( $enable != 'on' ) {
			return false;
		}
		$extWs = CMMicropaymentPlatform::getExternalsWallets();
		$extWs = json_decode(json_encode($extWs), true);
		$extWs = array_filter($extWs, function($w) use ($localWs) {
			return in_array( $w['wallet_name'], $localWs);
		});

		foreach($extWs as $exw) {
			unset($exw['user_email']);
			unset($exw['wallet_id']);
			unset($exw['user_id']);
			$wpdb->update( self::getTable(), (array)$exw, array( 'wallet_name' => $exw['wallet_name'] ) );
		}
	}

	public static function exchangeUsersWallets($old_owner, $new_owner) {
	    if(!$old_owner || !$new_owner) {
            return;
	    }
        $MicropaymentPlatformWallet = new CMMicropaymentPlatformWallet();
        $from_user_wallet = $MicropaymentPlatformWallet->getWalletByUserID($old_owner);
        $to_user_wallet = $MicropaymentPlatformWallet->getWalletByUserID($new_owner);

        if((!$from_user_wallet && !is_object($from_user_wallet)) || (!$to_user_wallet && !is_object($to_user_wallet))) {
            return;
        }
        self::assignUserWallet($from_user_wallet, $to_user_wallet);
        self::assignUserWallet($to_user_wallet, $from_user_wallet);

    }

    public static function assignUserWallet($old_owner, $new_owner) {
	    global $wpdb;
        $res = $wpdb->update(self::getTable(), ['user_id' => $new_owner->user_id], ['wallet_id' => $old_owner->wallet_id]);
        if($res) {
            self::$_log->log( $new_owner->points, 0, $new_owner->wallet_id, CMMicropaymentPlatformWalletCharges::TYPE_WALLET_EXCHANGE, $new_owner->status );

            $sendNotificationEmail = CMMicropaymentPlatform::get_option( 'cm_micropayment_send_wallet_exchange_notifications', FALSE );
            
            if ( $sendNotificationEmail ) {
                CMMicropaymentPlatformNotification::send( $old_owner->user_id, 'email_wallet_exchange', array(
                    'fromName' => get_user_by( 'id', $old_owner->user_id )->data->user_nicename,
                    'fromWalletID' => $old_owner->wallet_name,
                    'toWalletID' => $new_owner->wallet_name
                ) );
            }
        }
    }

	public function getCurrentUserWallet() {
		$wallet     = null;
		$loggedUser = wp_get_current_user();
		if ( $loggedUser->ID > 0 ) {
			$wallet = $this->getWalletByUserID( $loggedUser->ID );
		}

		return $wallet;
	}

	public function createWalletFromRemote( $userID, $data ) {
		global $wpdb;

		$data = array(
			'user_id'     => $userID,
			'wallet_name' => $data->wallet_name,
			'status'      => $data->status,
			'points'      => $data->points
		);

		return $wpdb->insert( self::getTable(), $data );
	}

	public function createWallet( $userID = null ) {
		global $wpdb;
		$walletCode = md5( rand() . time() );
		$userID     = $userID ? $userID : '0';

		$initialAmountOfPoints = CMMicropaymentPlatform::get_option( 'cm_micropayment_initial_wallet_points', 0 );

		$data = array(
			'user_id'     => $userID,
			'wallet_name' => $walletCode,
			'status'      => self::STATUS_ACTIVE,
			'points'      => $initialAmountOfPoints
		);

		$wpdb->insert( self::getTable(), $data );

		return $walletCode;
	}

	public function transferPointsByWalletCode( $walletFromCode, $walletToCode, $pointsAmount, $pointsComment = '' ) {
		global $wpdb;

		$label_transfer_wallet_points_success_msg = CMMicropaymentPlatformLabel::getLabel( 'transfer_wallet_points_success_msg' );


		$pointsAmount =  CMMicropaymentPlatform::convertType($pointsAmount);

		if ( ! is_numeric( $pointsAmount ) ) {
			return array( 'success' => false, 'message' => __( 'Invalid amount of points!' ) );
		}
		$walletFrom = (array) $this->getWalletByCode( $walletFromCode );
		$walletTo   = (array) $this->getWalletByCode( $walletToCode );

		if ( empty( $walletFrom ) || empty( $walletTo ) ) {
			return array( 'success' => false, 'message' => __( 'Wallet does not exists' ) );
		}

		if ( $walletFrom == $walletTo ) {
			return array( 'success' => false, 'message' => __( 'You cannot transfer to the same wallet' ) );
		}

		if ( (int) $walletTo['status'] === self::STATUS_INACTIVE && CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_charge_inactive_wallets', 0 ) != 1 ) {
			return array( 'success' => false, 'message' => __( 'You cannot transfer to inactive wallet' ) );
		}

		if ( (int) $walletFrom['points'] >= (int) $pointsAmount ) {
			$first  = $this->chargeWallet( $walletFrom['wallet_id'], - $pointsAmount, true, true, $pointsComment, $walletTo['wallet_id'] );
			$second = $this->chargeWallet( $walletTo['wallet_id'], $pointsAmount, true, true, $pointsComment, $walletFrom['wallet_id'] );

			$this->chargeTransactionFee( $walletFrom['wallet_id'], false, $pointsAmount );
			$this->chargeTransactionFee( $walletTo['wallet_id'], true, $pointsAmount );

			return array(
				'walletFromCode' => $walletFrom,
				'walletToCode'   => $walletTo,
				'first'          => $first,
				'second'         => $second,
				'success'        => true,
				'message'        => sprintf( __( $label_transfer_wallet_points_success_msg ), '<strong>' . $pointsAmount . '</strong>', '<strong>' . $walletFromCode . '</strong>', '<strong>' . $walletToCode . '</strong>' )
			);
		} else {
			return array( 'success' => false, 'message' => __( 'Wallet does not have enough points' ) );
		}
	}

	public function transferPointsByUserId( $userFromId, $userToId, $pointsAmount ) {
		global $wpdb;

		if ( $userFromId == $userToId ) {
			return array( 'success' => false, 'message' => __( 'You cannot transfer to the same user' ) );
		}

		$walletFrom = $this->getWalletByUserID( $userFromId );
		$walletTo   = $this->getWalletByUserID( $userToId );

		if ( ! isset( $walletFrom ) ) {
			return array( 'success' => false, 'message' => __( 'Wallet does not exists' ) );
		}
		if ( empty( $walletTo ) ) {
			return array( 'success' => false, 'message' => 'No wallet to transfer' );
		} elseif ( $walletTo->status != self::STATUS_ACTIVE && CMMicropaymentPlatform::get_option( 'cm_micropayment_allow_charge_inactive_wallets', 0 ) != 1 ) {
			return array( 'success' => false, 'message' => 'Can not transfer to wallet due to its status is not active' );
		}

		if ( $walletFrom->points >= $pointsAmount ) {
			$this->chargeWallet( $walletTo->wallet_id, $pointsAmount, true, true, '', $walletFrom->wallet_id );
			$this->chargeWallet( $walletFrom->wallet_id, $pointsAmount * (-1), true, true, '', $walletTo->wallet_id );

			$this->chargeTransactionFee( $walletFrom->wallet_id, false, $pointsAmount );
			$this->chargeTransactionFee( $walletTo->wallet_id, true, $pointsAmount );

		} else {
			return array( 'success' => false, 'message' => __( 'Wallet does not have enough points' ) );
		}

		return array( 'success' => true );
	}

	public function getWalletIdByCode( $wallet_name ) {
		global $wpdb;
		$result = $wpdb->get_var(
			$wpdb->prepare( "SELECT wallet_id FROM " . self::getTable() . "	WHERE wallet_name = %s", esc_sql( $wallet_name ) )
		);

		return $result;
	}

	public function getWalletIdByUserID( $userID ) {
		global $wpdb;
		$result = $wpdb->get_var(
			$wpdb->prepare( "SELECT wallet_id FROM " . self::getTable() . "	WHERE user_id = %s", esc_sql( $userID ) )
		);

		return $result;
	}

	public function getWallets() {
		global $wpdb;
		$result = $wpdb->get_results( "SELECT user_id, wallet_id FROM " . self::getTable(), OBJECT_K );

		return $result;
	}
	/*
	 * Returns array of names
	*/
	public static function getWalletsNames() {
		global $wpdb;
		$result = $wpdb->get_col( "SELECT wallet_name FROM " . self::getTable());

		return $result;
	}

	public function getWalletNameByUserID( $userID ) {
		global $wpdb;
		$sql    = $wpdb->prepare( "SELECT wallet_name FROM " . self::getTable() . "	WHERE user_id = %d", intval( $userID ) );
		$result = $wpdb->get_var( $sql );

		return $result;
	}

	public function getWalletsByUserID( $userID ) {
		global $wpdb;
		$sql    = $wpdb->prepare( "SELECT wallet_name FROM " . self::getTable() . "	WHERE user_id = %d", intval( $userID ) );
		$result = $wpdb->get_col( $sql );

		return $result;
	}

	public function getWallet( $wallet_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM " . self::getTable() . "	WHERE wallet_id = %s", ( $wallet_id ) )
		);

		return $result;
	}


	public function getWalletByCode( $wallet_name ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM " . self::getTable() . "	WHERE wallet_name = %s", esc_sql( $wallet_name ) )
		);

		return $result;
	}

	public function getWalletByUserID( $user_id ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM " . self::getTable() . "	WHERE user_id = %s", esc_sql( $user_id ) )
		);
		if ($result) {
			$result->points = CMMicropaymentPlatform::convertType($result->points);
		}
		return $result;
	}

	public function roleChargeWallet( $wallet_id, $points, $transfer = false, $saveLog = true, $customReason = '', $transaction_id = 0 ) {
		global $wpdb;
		$wallet = $this->getWallet( $wallet_id );

		if ( ! $wallet ) {
			return false;
		}

		if ( $transaction_id != 0 ) {
			$data   = array(
				'points' => $wallet->points + $points
			);
			$return = $wpdb->update( self::getTable(), $data, array( 'wallet_id' => $wallet->wallet_id ) );
		}

		if ( $saveLog ) {
			if ( $transaction_id != 0 ) {
				$wpdb->update( $wpdb->prefix . 'cm_micropayments_wallet_charges', array( 'type' => '0' ), array( 'transaction_id' => $transaction_id ) );
				$tid = $transaction_id;
			} else {
				$tid = self::$_log->log( $points, 0, $wallet->wallet_id, CMMicropaymentPlatformWalletCharges::TYPE_INCOMING, 1, $customReason );
			}
		}

		return $tid;
	}

	/**
	 * Charge a wallet
	 *
	 * @param type $wallet_id
	 * @param type $points
	 * @param type $transfer
	 * @param type $saveLog
	 * @param type $customReason
	 *
	 * @return boolean
	 * @global type $wpdb
	 */
	public function chargeWallet( $wallet_id, $points, $transfer = false, $saveLog = true, $customReason = '', $from_wallet_id = '' ) {
		global $wpdb;
		$transaction_type = '';
		$wallet = $this->getWallet( $wallet_id );

		if ( ! $wallet ) {
			$result = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM " . self::getTable() . "	WHERE wallet_name = %s", esc_sql( $wallet_id ) )
			);
			// var_dump($result);
			if ( $result != false ) {
				$wallet = $result;
			} else {
				return false;
			}
		}

		$data = array(
			'points' => $wallet->points + $points
		);

		$return = $wpdb->update( self::getTable(), $data, array( 'wallet_id' => $wallet->wallet_id ) );

		if ( $return ) {
			if ( $points > 0 && ! $transfer ) {
				$transaction_type = CMMicropaymentPlatformWalletCharges::TYPE_GRANT;
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_grant', array(
					'toWalletID' => $wallet->wallet_name,
					'toID'       => $wallet->user_id,
					'amountPoints'   => abs( $points )
				) );
			} elseif ( $points > 0 && $transfer ) {
				$transaction_type = CMMicropaymentPlatformWalletCharges::TYPE_INCOMING;
				$from_wallet  = $this->getWallet( $from_wallet_id );
				$from_user = get_user_by( 'id', $from_wallet->user_id );
				$to_user     = get_user_by( 'id', $wallet->user_id );
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_transfer', array(
					'toWalletID'   => $wallet->wallet_name,
					'toID'         => $wallet->user_id,
					'toName'       => $to_user->display_name,
					'toEmail'      => $to_user->user_email,
					'fromWalletID' => $from_wallet->wallet_name,
					'fromID'       => $from_wallet->user_id,
					'fromName'     => $from_user->display_name,
					'fromEmail'    => $from_user->user_email,
					'amountPoints' => abs( $points ),
					'message'      => $customReason,
				) );
			} elseif ( $points < 0 && ! $transfer ) {
				$transaction_type = CMMicropaymentPlatformWalletCharges::TYPE_CHARGE;
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_charge', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $wallet->user_id,
					'amountPoints' => abs( $points ),
				) );
			} elseif ( $points < 0 && $transfer ) {
				$transaction_type = CMMicropaymentPlatformWalletCharges::TYPE_OUTGOING;
				$to_wallet  = $this->getWallet( $from_wallet_id );
				$to_user = get_user_by( 'id', $to_wallet->user_id );
				$from_wallet = $wallet;
				$from_user     = get_user_by( 'id', $from_wallet->user_id );
				CMMicropaymentPlatformNotification::send( $from_wallet->user_id, 'email_transfer', array(
					'fromWalletID' => $from_wallet->wallet_name,
					'fromID'       => $from_wallet->user_id,
					'fromName'     => $from_user->display_name,
					'fromEmail'    => $from_user->user_email,
					'toWalletID'   => $to_wallet->wallet_name,
					'toID'         => $to_wallet->user_id,
					'toName'       => $to_user->display_name,
					'toEmail'      => $to_user->user_email,
					'amountPoints' => abs( $points ),
					'message'      => $customReason
				) );

				// email to admin
				CMMicropaymentPlatformNotification::send( get_option( 'admin_email' ), 'email_transfer', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $wallet->user_id,
					'fromName'     => $from_user->display_name,
					'fromEmail'    => $from_user->user_email,
					'toWalletID'     => $from_wallet->wallet_name,
					'toID'           => $from_wallet->user_id,
					'toName'         => $to_user->display_name,
					'toEmail'        => $to_user->user_email,
					'amountPoints'       => abs( $points ),
					'message'            => $customReason
				) );

			}

		}
		if ( $saveLog ) {
			self::$_log->log( $points, 0, $wallet->wallet_id, $transaction_type, 1, $customReason );
		}

		return (bool) $return;
	}

	/**
	 * Charge transaction commission from a wallet
	 *
	 * @param type $wallet_id
	 * @param type $add_points : true-adding points transaction fee; false-subtracting points transaction fee;
	 * @param type $customReason
	 *
	 * @return boolean
	 * @global type $wpdb
	 */
	public function chargeTransactionFee( $wallet_id, $is_points_added, $total = 0 ) {
		global $wpdb;
		$wallet = $this->getWallet( $wallet_id );

		if ( ! $wallet ) {
			if ( ! $wallet ) {
				$result = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM " . self::getTable() . "	WHERE wallet_name = %s", esc_sql( $wallet_id ) )
				);

				if ( $result != false ) {
					$wallet = $result;
				} else {
					return false;
				}
			}
		}
		$return = true;
		if ( $is_points_added ) {
			$pointsFee = CMMicropaymentPlatform::get_option( 'cm_micropayment_adding_points_transaction_fee', '0' );
			$customReason = 'Fee for adding';
		} else {
			$pointsFee = CMMicropaymentPlatform::get_option( 'cm_micropayment_subtracting_points_transaction_fee', '0' );
			$customReason = 'Fee for subtracting';
		}

		if ( $pointsFee != '0' && ( $total !== 0 ) ) {
			if ( ( strpos(strval($pointsFee), '%') !== false ) ) {
				$fee = 0;
				$percents = floatval(explode('%', strval($pointsFee))[0]);
				if ( is_numeric($percents) && ($percents !== 0) ) {
					$decimals = $percents - floor($percents);
					$percents = ( $percents % 100 ) + $decimals; // if a pointsFee value > 100%, normalize it to less than 100
					$fee = ( $total / 100 ) * $percents;
				}
				if ( $fee !== 0 ) {
					$pointsFee = abs( $fee ); //CMMicropaymentPlatform::convertType( $fee );
				}
			} else {
				$pointsFee = abs( $pointsFee );
			}

			$data = array(
				'points' => $wallet->points - $pointsFee,
			);

			$return = $wpdb->update( self::getTable(), $data, array( 'wallet_id' => $wallet->wallet_id ) );

			if ( $return ) {
				self::$_log->log( - $pointsFee, $total, $wallet->wallet_id, CMMicropaymentPlatformWalletCharges::TYPE_TRANSACTION_COMMISSION, 1, $customReason );

				if($fee_user = CMMicropaymentPlatformBackendFeesUser::getFeesUserId()) {
					$fee_wallet = $this->getWalletByUserID($fee_user);
					
					$res = $wpdb->update( self::getTable(), ['points' => $pointsFee + $fee_wallet->points], array( 'wallet_id' => $fee_wallet->wallet_id ) );

					if ( $res ) {
						self::$_log->log( $pointsFee, 0, $fee_wallet->wallet_id, CMMicropaymentPlatformWalletCharges::TYPE_TRANSACTION_COMMISSION, 1 );
					}
				}
			}
		}

		return (bool) $return;
	}

	/**
	 * Charge a wallet belonging to user
	 *
	 * @param type $user_id
	 * @param type $points
	 * @param type $transfer
	 * @param type $price
	 *
	 * @return boolean
	 * @global type $wpdb
	 */
	public function chargeUserWallet( $user_id, $points, $transfer = false, $price = 0, $return_id = false , $comment = '') {
		global $wpdb;
		$transfer_type = array(
			'grant' => CMMicropaymentPlatformWalletCharges::TYPE_GRANT,
			'incoming' => CMMicropaymentPlatformWalletCharges::TYPE_INCOMING,
			'charge' => CMMicropaymentPlatformWalletCharges::TYPE_CHARGE,
			'outgoing' => CMMicropaymentPlatformWalletCharges::TYPE_OUTGOING,
			
		);
		$wallet = $this->getWalletByUserID( $user_id );

		if ( ! $wallet ) {
			return false;
		}

		$data = array(
			'points' => $wallet->points + $points
		);

		$return = $wpdb->update( self::getTable(), $data, array( 'wallet_id' => $wallet->wallet_id ) );

		if ( $return ) {
			$args = [
				'comment' => $comment,
				'points' => abs($points),
			];

			$this->addPointsToStorePurchasesWallet($args, self::$_log, 1, $wallet->wallet_name);

			if ( $points > 0 && ! $transfer ) {
				$type = $transfer_type['grant'];
				$return = self::$_log->log( $points, $price, $wallet->wallet_id, $type, 1, $comment );
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_grant', array(
					'toWalletID'   => $wallet->wallet_name,
					'toID'         => $user_id,
					'amountPoints' => abs( $points )
				) );
			} elseif ( $points > 0 && $transfer ) {
				$type = $transfer_type['incoming'];
				$return = self::$_log->log( $points, 0, $wallet->wallet_id, $type, 1, $comment );
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_transfer', array(
					'toWalletID'   => $wallet->wallet_name,
					'toID'         => $user_id,
					'amountPoints' => abs( $points )
				) );
			} elseif ( $points < 0 && ! $transfer ) {
				$type = $transfer_type['charge'];
				$return = self::$_log->log( $points, 0, $wallet->wallet_id, $type, 1, $comment );
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_charge', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $user_id,
					'amountPoints' => abs( $points )
				) );
			} elseif ( $points < 0 && $transfer ) {
				$type = $transfer_type['outgoing'];
				$return = self::$_log->log( $points, 0, $wallet->wallet_id, $type, 1, $comment );
				CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_transfer', array(
					'fromWalletID' => $wallet->wallet_name,
					'fromID'       => $user_id,
					'amountPoints' => abs( $points ),
					'toID'         => $user_id
				) );
			}
			$data['wallet_id'] = $wallet->wallet_name;
			$data['type'] = $type;
			$data['points'] = -$points;
			$this->syncExternalWallet( $data, 1 );

		}

		return ( $return_id ) ? $return : (bool) $return;
	}

	public function addPointsToStorePurchasesWallet($args, $walletTransactions, $transactionStatus, $fromWallet) {
		if(CMMicropaymentPlatform::get_option( 'cm_micropayment_store_purchases_wallet', '0' )) {
			$storePurchasesUserWallet = CMMicropaymentPlatformBackendStorePurchasesUser::getStorePurchasesUserWallet();
			$storeWalletPoints = $storePurchasesUserWallet->points;
			$message = $args['comment'] . '
			From: ' . $fromWallet;

			$storeWalletPoints = $storeWalletPoints + $args[ 'points' ];

			$this->setPoints( $storePurchasesUserWallet->wallet_name, $storeWalletPoints );

			$walletTransactions->log( $args[ 'points' ], 0, $storePurchasesUserWallet->wallet_id, 0, $transactionStatus, $message );
		}
	}

	public function setPoints( $wallet_name, $points ) {
		global $wpdb;
		$wallet = $this->getWalletByCode( $wallet_name );

		if ( ! $wallet ) {
			return false;
		}

		$data = array(
			'points' => $points
		);

		$wpdb->update( self::$_tableName, $data, array( 'wallet_id' => $wallet->wallet_id ) );
	}

	public function deleteAllWallets() {
		global $wpdb;
		$result = $wpdb->get_row( "TRUNCATE TABLE " . self::getTable() );

		return $result;
	}

}
