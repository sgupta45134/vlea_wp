<?php

class CMMicropaymentPlatformWalletCharges {

	/**
	 * Added points to wallet by system.
	 */
	const TYPE_GRANT = 0;

	/**
	 * Taken points from wallet by system.
	 */
	const TYPE_CHARGE = 1;

	/**
	 * Taken points from wallet to other wallet (outgoing transfer).
	 */
	const TYPE_OUTGOING = 2;

	/**
	 * Added points to wallet from other wallet (incoming transfer).
	 */
	const TYPE_INCOMING = 3;

	/**
	 * Added points to wallet by PayPal transaction?
	 */
	const TYPE_GRANTED_MANUALLY = 4;

	/**
	 * Taken points from the wallet by the gateway payment
	 */
	const TYPE_EDD_PAYMENT_CHARGE = 5;

	/**
	 * Added points to wallet for Easy Digital Download purchase
	 */
	const TYPE_EDD_PURCHASE_GRANT = 6;

	/**
	 * PayPal payout
	 */
	const TYPE_PAYPAL_PAYOUT = 7; 

	/**
	 * Stripe payout
	 */
	const TYPE_STRIPE_PAYOUT = 7; 

	/**
	 * Added points to wallet for Woocommerce purchase
	 */
	const TYPE_WOO_PURCHASE_GRANT = 10;

	/**
	 * Added points to wallet for Woocommerce purchase
	 */
	const TYPE_WOO_PAYMENT_CHARGE = 11;
        
    /**
	 * Added points to wallet by CSV import
	 */
	const TYPE_IMPORT_OPERATION = 12;

    /**
	 * Transaction commission
	 */
	const TYPE_TRANSACTION_COMMISSION = 13;

    /**
     * Wallet exchange
     */
    const TYPE_WALLET_EXCHANGE = 14;


    private static $_tableName;

	public static function setTableName() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		self::$_tableName = $tablePrefix . "_wallet_charges";
	}

	public function __construct() {
		self::setTableName();
	}

	/**
	 * Log the wallet transaction
	 * @global type $wpdb
	 * @param float $points - amount of points
	 * @param float $price - price of points
	 * @param string $wallet_id - wallet id
	 * @param int $type - transaction type
	 * @param int $status - transaction status
	 * @param string $comment - transaction comment/reason
	 * @return int insert_id
	 */
	public static function log( $points, $price, $wallet_id, $type = 0, $status = 0, $comment = '' ) {
		global $wpdb;
		$data = array(
			'wallet_id'	 => $wallet_id,
			'status'	 => $status,
			'points'	 => $points,
			'amount'	 => $price,
			'order_date' => current_time('mysql'),
			'type'		 => $type,
			'ip'		 => isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : ''
		);

		/*
		 * Customization on July 5th (customer wanted it to be called reason
		 */
		if ( !empty( $comment ) && is_string( $comment ) ) {
			$data[ 'comment' ] = $comment;
		}

		$wpdb->insert( self::$_tableName, $data );
		return $wpdb->insert_id;
	}

	public function confirm( $transaction_id ) {
		global $wpdb;
		$data = array(
			'status' => 1,
		);

		$wpdb->update( self::$_tableName, $data, array( 'transaction_id' => esc_sql( $transaction_id ) ) );
		return $wpdb->insert_id;
	}

	public function getConfirmedTransaction( $transaction_id ) {
		global $wpdb;

		$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM " . self::$_tableName . "	WHERE transaction_id = %s AND status = 1", esc_sql( $transaction_id ) )
		);

		return $result;
	}

	public function getTransaction( $transaction_id ) {
		global $wpdb;

		$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM " . self::$_tableName . "	WHERE transaction_id = %s", esc_sql( $transaction_id ) )
		);

		return $result;
	}

	public function getTransactionsByWalletID( $wallet_id, $onlyConfirmed = true ) {
		global $wpdb;

		$page = (int)$_GET['pagei'];
		if ( !$page OR !is_numeric( $page ) ) {
			$page				 = 1;
		}
		$transactionsPerPage = intval( CMMicropaymentPlatform::get_option( 'cm_micropayment_number_of_transactions', 10 ) );
		$offset				 = ($page - 1) * $transactionsPerPage;
		$sql				 = $wpdb->prepare( "SELECT * FROM " . self::$_tableName . "
        			WHERE wallet_id = %s
        				AND status = 1
        			ORDER BY order_date DESC
        			LIMIT %d, %d", $wallet_id, $offset, $transactionsPerPage
		);

		return $wpdb->get_results( $sql );
	}

	public function countTransactionsByWalletID( $wallet_id, $onlyConfirmed = true ) {
		global $wpdb;

		$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM " . self::$_tableName . "	WHERE wallet_id = %s AND status = 1", esc_sql( $wallet_id ) )
		);

		return $result;
	}

	public function getAmountReport( $group = 'date', $from = null, $to = null ) {
		global $wpdb;
		$where = '';

		if ( $from != null ) {
			$where .= ' AND order_date >= "' . $from . '"';
		}

		if ( $to != null ) {
			$where .= ' AND order_date <= "' . $to . '"';
		}

		$result = $wpdb->get_results(
		"SELECT EXTRACT(YEAR_MONTH FROM order_date) AS YearMonth,
             EXTRACT(YEAR FROM order_date) AS Year,
             EXTRACT(HOUR FROM order_date) AS Hour,
            DATE(order_date) AS date,
             SUM(amount) AS total_amount,
             order_date
              FROM " . self::$_tableName . " WHERE status = 1" . $where . "  GROUP BY " . $group
		);

		return $result;
	}

	public function getPointsReport( $group = 'date', $from = null, $to = null ) {
		global $wpdb;
		$where = '';

		if ( $from != null ) {
			$where .= ' AND order_date >= "' . $from . '"';
		}

		if ( $to != null ) {
			$where .= ' AND order_date <= "' . $to . '"';
		}

		$result = $wpdb->get_results(
		"SELECT EXTRACT(YEAR_MONTH FROM order_date) AS YearMonth,
             EXTRACT(YEAR FROM order_date) AS Year,
             EXTRACT(HOUR FROM order_date) AS Hour,
            DATE(order_date) AS date,
             SUM(points) AS total_amount,
             order_date
              FROM " . self::$_tableName . " WHERE status = 1" . $where . "  GROUP BY " . $group
		);

		return $result;
	}

	public function getCountReport( $group = 'date', $from = null, $to = null ) {
		global $wpdb;
		$where = '';

		if ( $from != null ) {
			$where .= ' AND order_date >= "' . $from . '"';
		}

		if ( $to != null ) {
			$where .= ' AND order_date <= "' . $to . '"';
		}

		$result = $wpdb->get_results(
		"SELECT EXTRACT(YEAR_MONTH FROM order_date) AS YearMonth,
             EXTRACT(YEAR FROM order_date) AS Year,
             EXTRACT(HOUR FROM order_date) AS Hour,
              DATE(order_date) AS date,
              order_date,
               COUNT(transaction_id) AS total_amount FROM " . self::$_tableName . " WHERE status = 1" . $where . " GROUP BY " . $group
		);
		return $result;
	}

}
