<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class CMMicropaymentPlatformBackendReportWalletList extends WP_List_Table {

	private $_tableName;
	private $_exportItems = array();

	function __construct() {
		global $wpdb;

		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		$this->_tableName = $tablePrefix . "_wallet_charges";

		add_filter( 'page_row_actions', array( $this, 'pageRowActions' ), 10, 2 );

		parent::__construct( array(
			'singular'	 => 'wp_list_text_link',
			'plural'	 => 'wp_list_test_links',
			'ajax'		 => true
		) );
	}

	public function render() {

		self::registerScripts();
		if ( ob_start() ) {
			$content_name	 = 'list';
			$this->prepare_items();
			include CMMP_PLUGIN_DIR . '/backend/views/reports.phtml';
			$content		 = ob_get_clean();
			echo $content;
		}
	}

	public function export() {

		if ( ob_start() ) {

			$this->prepare_items();

			if ( !empty( $this->_exportItems ) ) {

				if ( !class_exists( "CSVExport" ) ) {
					include_once CMMP_PLUGIN_DIR . '/shared/libs/CSV.php';
				}

				$exporter = new CSVExport;

				$columns = $this->get_columns();
				$exporter->setColumns( array_values( array( $this->get_columns() ) ) );

				foreach ( $this->_exportItems as $item ) {
					$dataRow = array();
					foreach ( $columns as $key => $value ) {
						if ( isset( $item[ $key ] ) ) {
							$dataRow[] = $item[ $key ];
						}
					}
					$exporter->setData( array( $dataRow ) );
				}
				$exporter->download();
			}
		}
	}

	private static function registerScripts() {
//		wp_enqueue_script( 'cm-micropayment-admin-reports-scripts', CMMP_PLUGIN_URL . '/backend/assets/js/scripts.js' );
	}

	function get_columns() {
		return $columns = array(
//            'transaction_id' => __('ID'),
			'wallet_name'	 => __( 'Wallet Name / Code' ),
			'wallet_id'		 => __( 'Wallet ID' ),
			'user_id'		 => __( 'User ID' ),
			'user_login'	 => __( 'User login' ),
			'points'		 => __( 'Points' ),
			'amount'		 => __( 'Amount' ),
			'type'			 => __( 'Type' ),
			'comment'		 => __( 'Reason / Message' ),
			'status'		 => __( 'Status' ),
			'order_date'	 => __( 'Date' ),
		);
	}

	public function get_sortable_columns() {
		return array(
//            'transaction_id' => array('t.transaction_id', 'DESC'),
			'wallet_name'	 => array( 'w.wallet_name', 'DESC' ),
			'wallet_id'		 => array( 'w.wallet_id', 'DESC' ),
			'user_id'		 => array( 'w.user_id', 'DESC' ),
			//'user_login'	 => array( 'w.user_login', 'DESC' ),
			'points'		 => array( 't.points', 'DESC' ),
			'amount'		 => array( 't.amount', 'DESC' ),
			'status'		 => array( 't.status', 'DESC' ),
			'order_date'	 => array( 't.order_date', 'DESC' ),
			'type'			 => array( 't.type', 'DESC' ),
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
		return array();
	}

	function column_default( $item, $column_name ) {

		if ( empty( $item ) ) {
			return '';
		}

		switch ( $column_name ) {
			case 'user_id':
				$result	 = '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->user_id ) . '">' . $item->user_id . '</a>';
				break;
			case 'wallet_name' :
				$result	 = '<a href="' . admin_url( 'admin.php?page=cm_micropayments&range=this_month&walletID=' . $item->wallet_id . '&view=amount&type=table', 'http' ) . '">' . $item->{$column_name} . '</a>';
				break;
			case 'user_login':
				$user		 = get_user_by( 'id', $item->user_id );
				$userLogin	 = !empty( $user->data->user_login ) ? $user->data->user_login : __( '-Missing user-' );
				if ( !empty( $user ) ) {
					$result = '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->user_id ) . '">' . $userLogin . '</a>';
				} else {
					$result = $userLogin;
				}
				break;
			case 'wallet_id':
			case 'comment':
			case 'transaction_id':
			case 'order_date':
			case 'points':
			case 'amount':
				$result	 = $item->{$column_name};
				break;
			case 'status':
				if ( $item->{$column_name} == 1 ) {
					$result = __( 'Successful' );
				} else {
					$result = __( 'Failed' );
				}
				break;
			case 'type':
				switch ( $item->{$column_name} ) {
					case CMMicropaymentPlatformWalletCharges::TYPE_GRANTED_MANUALLY:
						$result	 = __cm('type_granted_manually');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_EDD_PAYMENT_CHARGE:
						$result	 = __cm('charged_for_edd_payment');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_WOO_PAYMENT_CHARGE:
						$result	 = __cm('charged_for_woo_payment');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_EDD_PURCHASE_GRANT:
						$result	 = __cm('granted_for_edd_purchase');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_WOO_PURCHASE_GRANT:
						$result	 = __cm('granted_for_woo_purchase');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_INCOMING:
						$result	 = __cm('type_incoming');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_OUTGOING:
						$result	 = __cm('type_outgoing');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_CHARGE:
						$result	 = __cm('type_charge');
						break;
                    case CMMicropaymentPlatformWalletCharges::TYPE_IMPORT_OPERATION:
						$result	 = __cm('type_import_operation');
						break;
					case CMMicropaymentPlatformWalletCharges::TYPE_GRANT:
					default:
						$result = __cm('type_grant');
						break;
				}
				break;
			default:
				return print_r( $item, true );
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

	function prepare_items() {
		global $wpdb, $_wp_column_headers;

		$screen	 = get_current_screen();
		$query	 = "SELECT t.*, w.wallet_name, w.user_id FROM $this->_tableName AS t JOIN " . $wpdb->prefix . "cm_micropayments_wallets AS w ON t.wallet_id = w.wallet_id ";

		$setWhere = false;

		if ( isset( $_POST[ 's' ] ) && $_POST[ 's' ] != '' ) {
			$query .= " WHERE w.wallet_name LIKE '%" . $_POST[ 's' ] . "%'";
			$setWhere = true;
		}

		if ( isset( $_REQUEST[ 'walletID' ] ) && is_numeric( $_REQUEST[ 'walletID' ] ) ) {
			if ( !$setWhere ) {
				$query .= " WHERE t.wallet_id = " . $_REQUEST[ 'walletID' ];
			} else {
				$query .= " AND t.wallet_id = " . $_REQUEST[ 'walletID' ];
			}
			$setWhere = true;
		}

		if ( isset( $_REQUEST[ 'only_successful' ] ) && $_REQUEST[ 'only_successful' ] == 1 ) {
			if ( !$setWhere ) {
				$query .= " WHERE t.status = 1";
			} else {
				$query .= " AND t.status = 1";
			}
		}

		$orderby = !empty( $_GET[ "orderby" ] ) ? esc_sql( $_GET[ "orderby" ] ) : 't.order_date';
		$order	 = !empty( $_GET[ "order" ] ) ? esc_sql( $_GET[ "order" ] ) : 'DESC';

		if ( !empty( $orderby ) & !empty( $order ) ) {
			$query.=' ORDER BY ' . $orderby . ' ' . $order;
		}

		$totalitems	 = $wpdb->query( $query );
		$perpage	 = 10;

		/*
		 * Needed for export
		 */
		if ( isset( $_GET[ 'export_to_csv' ] ) ) {
			$this->_exportItems = $wpdb->get_results( $query, ARRAY_A );
			return;
		}

		$paged = !empty( $_GET[ "paged" ] ) ? esc_sql( $_GET[ "paged" ] ) : '';

		if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}

		$totalpages = ceil( $totalitems / $perpage );

		if ( !empty( $paged ) && !empty( $perpage ) ) {
			$offset = ($paged - 1) * $perpage;
			$query.=' LIMIT ' . (int) $offset . ',' . (int) $perpage;
		}

		$this->set_pagination_args( array(
			"total_items"	 => $totalitems,
			"total_pages"	 => $totalpages,
			"per_page"		 => $perpage,
		) );

		$columns = $this->get_columns();
		if ( isset( $screen ) ) {
			$_wp_column_headers[ $screen->id ] = $columns;
		}

		$this->items = $wpdb->get_results( $query );
	}

}
