<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class CMMicropaymentPlatformBackendPointsPricesList extends WP_List_Table {

	private $_tableName;

	function __construct() {
		global $wpdb;

		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}

		$this->_tableName = $tablePrefix . "_defined_points_cost";

		add_filter( 'page_row_actions', array( $this, 'pageRowActions' ), 10, 2 );

		parent::__construct( array(
			'singular'	 => 'wp_list_text_link',
			'plural'	 => 'wp_list_test_links',
			'ajax'		 => true
		) );
	}

	function get_columns() {
		return $columns = array(
			'cb'			 => '<input type="checkbox" />',
//            'points_cost_id' => __('ID'),
			'points_value'	 => __( 'Points value' ),
			'cost'			 => __( 'Cost' )
		);
	}

	public function get_sortable_columns() {
		return array(
//            'points_cost_id' => array('points_cost_id', 'DESC'),
			'points_value'	 => array( 'points_value', 'DESC' ),
			'cost'			 => array( 'cost', 'DESC' ),
		);
	}

	function get_column_info() {
		return array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			$this->get_primary_column_name(),
		);
	}

	function getRowActions( $id ) {
		return array(
			'edit'	 => '<a class="" href="' . esc_url( add_query_arg( array( 'cmm-action' => 'edit', 'cmm-id' => $id ) ) ) . '" title="' . __( 'Edit' ) . '">' . __( 'Edit', 'cm-micropayment-platform' ) . '</a>',
			'delete' => '<a class="" href="' . esc_url( add_query_arg( array( 'cmm-action' => 'remove', 'cmm-id' => $id ) ) ) . '" title="' . __( 'Remove' ) . '">' . __( 'Remove', 'cm-micropayment-platform' ) . '</a>'
		);
	}

	function column_default( $item, $column_name ) {

		if ( empty( $item ) ) {
			return '';
		}

		switch ( $column_name ) {
			case 'points_cost_id': {
					$result = $item->{$column_name};
					break;
				}
			case 'cost': {
					$value = CMMicropaymentPlatform::numberToLocale($item->{$column_name});
					$currencySymbol	 = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
                    if(!empty($currencySymbol)){
                        $currency = $currencySymbol;
                    } else {
						$currency = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', 'USD' );
					}

					$result = $value . ' ' . $currency;
					break;
				}
			case 'points_value': {
					$value = CMMicropaymentPlatform::numberToLocale($item->{$column_name}, CMMicropaymentPlatform::get_option( 'cm_micropayment_enable_decimal', false ));
					$result = $value . $this->row_actions( $this->getRowActions( $item->points_cost_id ) );
					break;
				}
			default: {
					$result = $item->{$column_name} . $this->row_actions( $this->getRowActions( $item->points_cost_id ) );
					break;
				}
		}

		if ( isset( $_GET[ 'cminds_debug' ] ) && $_GET[ 'cminds_debug' ] == 3 ) {
			echo '=======';
			var_dump( $item );
			var_dump( $column_name );
			var_dump( $result );
			echo '=======';
		}
		return $result;
	}

	function column_cb( $item ) {
		return '<input type="checkbox" name="points_cost_id[]" value="' . $item->points_cost_id . '" />';
	}

	function prepare_items() {
		global $wpdb, $_wp_column_headers;

		$screen	 = get_current_screen();
		$query	 = "SELECT * FROM $this->_tableName";

		$orderby = !empty( $_GET[ "orderby" ] ) ? esc_sql( $_GET[ "orderby" ] ) : 'ASC';
		$order	 = !empty( $_GET[ "order" ] ) ? esc_sql( $_GET[ "order" ] ) : '';

		if ( !empty( $orderby ) & !empty( $order ) ) {
			$query .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		$totalitems	 = $wpdb->query( $query );
		$perpage	 = 10;

		$paged = !empty( $_GET[ "paged" ] ) ? esc_sql( $_GET[ "paged" ] ) : '';

		if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}

		$totalpages = ceil( $totalitems / $perpage );

		if ( !empty( $paged ) && !empty( $perpage ) ) {
			$offset = ($paged - 1) * $perpage;
			$query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
		}

		$this->set_pagination_args( array(
			"total_items"	 => $totalitems,
			"total_pages"	 => $totalpages,
			"per_page"		 => $perpage,
		) );

		$columns							 = $this->get_columns();
		$_wp_column_headers[ $screen->id ]	 = $columns;

		$this->items = $wpdb->get_results( $query );
	}

}
