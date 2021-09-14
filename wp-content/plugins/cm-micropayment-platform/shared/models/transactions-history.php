<?php

class CMMicropaymentPlatformTransactionsHistory {

	private static $_historyTableName;

	public static function setTableName() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		self::$_historyTableName = $tablePrefix . "_transaction_history";
	}

	public function __construct() {
		self::setTableName();
	}

	public static function log( $transaction_id, $type = 0 ) {
		global $wpdb;
		self::setTableName();

		$data = array(
			'transaction_id' => esc_sql( $transaction_id ),
			'history_type'	 => esc_sql( $type ),
			'datetime'		 => current_time('mysql'),
		);

		$wpdb->insert( self::$_historyTableName, $data );
		return $wpdb->insert_id;
	}

}
