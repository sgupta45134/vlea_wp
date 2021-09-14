<?php

class CMMicropaymentPlatformPointsPrices {

	protected static $_tableName;

	public function __construct() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		self::$_tableName = $tablePrefix . "_defined_points_cost";
	}

	public function hasPoints() {
		global $wpdb;
		$result = $wpdb->get_var( "SELECT COUNT(*) FROM " . self::$_tableName );
		return $result;
	}

	public function fetchAll() {
		global $wpdb;
		$query = "SELECT * FROM " . self::$_tableName;

		$orderby = !empty( $_GET[ "orderby" ] ) ? esc_sql( $_GET[ "orderby" ] ) : 'ASC';
		$order	 = !empty( $_GET[ "order" ] ) ? esc_sql( $_GET[ "order" ] ) : '';

		if ( !empty( $orderby ) & !empty( $order ) ) {
			$query.=' ORDER BY ' . $orderby . ' ' . $order;
		}

		return $wpdb->get_results( $query );
	}

	public function getPriceByValue( $value ) {
		global $wpdb;

		$result = $wpdb->get_var(
		$wpdb->prepare( "SELECT cost FROM " . self::$_tableName . "	WHERE points_value = %s", esc_sql( $value ) )
		);

		return $result;
	}

}
