<?php

require_once "transactions-history.php";

class CMMicropaymentPlatformTransactions extends CMMicropaymentPlatformTransactionsHistory {

	protected static $_tableName;

	public static function setTableName() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		self::$_tableName = $tablePrefix . "_transactions";
	}

	public function __construct() {
		self::setTableName();
	}

	public static function createTransaction( $transaction_id ) {
		global $wpdb;
		self::setTableName();

		$data = array(
			'transaction_id' => esc_sql( $transaction_id ),
			'status'		 => 0,
			'datetime'		 => current_time('mysql')
		);

		$wpdb->insert( self::$_tableName, $data );
		parent::log( $transaction_id );

		return $wpdb->insert_id;
	}

	public static function updateStatus( $transaction_id, $status = 0 ) {
		global $wpdb;
		self::setTableName();

		$data = array(
			'status' => $status
		);

		$wpdb->update( self::$_tableName, $data, array( 'transaction_id' => esc_sql( $transaction_id ) ) );
		parent::log( $transaction_id, $status );
	}

	public static function isPending( $transaction_id ) {
		global $wpdb;
		self::setTableName();
		$result				 = $wpdb->get_var(
		$wpdb->prepare( "SELECT status FROM " . self::$_tableName . " WHERE transaction_id = %s", esc_sql( $transaction_id ) )
		);
		return $result;
	}

	public function count() {
		global $wpdb;
		self::setTableName();
		return $wpdb->get_var( "SELECT COUNT(*) FROM " . self::$_tableName );
	}

	public function getCountByStatus( $status ) {
		global $wpdb;
		self::setTableName();
		return $wpdb->get_var( "SELECT COUNT(*) FROM " . self::$_tableName . " WHERE status = " . $status );
	}

}
