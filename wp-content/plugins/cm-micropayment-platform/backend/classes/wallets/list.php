<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class CMMicropaymentPlatformBackendWalletsList extends WP_List_Table {

	private $_tableName;

	function __construct() {
		global $wpdb;
		if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
			$tablePrefix = $wpdb->base_prefix . "cm_micropayments";
		} else {
			$tablePrefix = $wpdb->prefix . "cm_micropayments";
		}
		$this->_tableName = $tablePrefix . "_wallets";
		self::registerScripts();
		self::registerStyles();
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
			'wallet_name'	 => __( 'Wallet Name/Code' ),
			'wallet_id'		 => __( 'Wallet ID' ),
			'user_id'		 => __( 'User ID' ),
			'user_login'	 => __( 'User login' ),
			'points'		 => __( 'Points' ),
			'status'		 => __( 'Status' ),
		);
	}

	public function get_sortable_columns() {
		return array(
            'wallet_id'		 => array( 'wallet_id', 'DESC' ),
			'wallet_name'	 => array( 'wallet_name', 'DESC' ),
			'user_id'		 => array( 'user_id', 'DESC' ),
			'user_login'	 => array( 'user_login', 'DESC' ),
			'points'		 => array( 'points', 'DESC' ),
			'status'		 => array( 'status', 'DESC' ),
		);
	}

	function get_column_info() {
		return array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			$this->get_primary_column_name()
		);
	}

	function getRowActions( $id ) {

		$rowActions = array(
			'change_points_value'	 => '<a href="javascript:void(0);" class="inlineEditButton" title="' . __( 'Change points value', 'cm-micropayment-platform' ) . '">' . __( 'Change points value', 'cm-micropayment-platform' ) . '</a>',
			'statistic'				 => '<a class="" href="' . esc_url( add_query_arg( array( 'range' => 'this_month', 'walletID' => $id, 'view' => 'amount', 'type' => 'table', 'page' => 'cm_micropayments' ) ) ) . '" title="' . __( 'Charge History', 'cm-micropayment-platform' ) . '">' . __( 'Transactions', 'cm-micropayment-platform' ) . '</a>',
			'active'				 => '<a class="" href="' . esc_url( add_query_arg( array( 'cmm-action' => 'active', 'cmm-id' => $id ) ) ) . '" title="' . __( 'Activate' ) . '">' . __( 'Activate' ) . '</a>',
			'trash'					 => '<a class="" href="' . esc_url( add_query_arg( array( 'cmm-action' => 'deactive', 'cmm-id' => $id ) ) ) . '" title="' . __( 'Deactivate' ) . '">' . __( 'Deactivate' ) . '</a>',
			'delete'				 => '<a class="" href="' . esc_url( add_query_arg( array( 'cmm-action' => 'remove', 'cmm-id' => $id ) ) ) . '" title="' . __( 'Remove' ) . '">' . __( 'Remove' ) . '</a>',
		);
		return apply_filters( 'cmmp_wallet_row_actions', $rowActions );
	}

	function column_default( $item, $column_name ) {

		if ( empty( $item ) ) {
			return '';
		}

		switch ( $column_name ) {
			case 'wallet_id':
				//$result		 = $item->{$column_name} . $this->row_actions( $this->getRowActions( $item->{$column_name} ) );
				$result = $item->wallet_id;
				break;
			case 'user_id':
				$result		 = '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->user_id ) . '">' . $item->user_id . '</a>';
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
			case 'wallet_name':
				$result	 = '<a class="wallet_id_holder" data-wallet-id="' . $item->wallet_id . '" href="' . admin_url( 'admin.php?page=cm_micropayments&range=this_month&walletID=' . $item->wallet_id . '&view=amount&type=table', 'http' ) . '">' . $item->{$column_name} . '</a>' . $this->row_actions( $this->getRowActions( $item->wallet_id ) );
				break;
			case 'points':
				$result	 = CMMicropaymentPlatform::convertType($item->{$column_name});
				break;
			case 'status':
				if ( $item->{$column_name} == 1 ) {
					$result = __( 'Active' );
				} else {
					$result = __( 'Not Active' );
				}
				break;
			default:
				$result = print_r( $item, true );
				break;
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

	public static function registerScripts() {
		wp_enqueue_script( 'jquery-ui');
	}

	public static function registerStyles() {
		wp_enqueue_style( 'cm-micropayment-backend-jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/css/jquery-ui/ui-lightness/jquery-ui-1.10.4.custom.min.css' );
		wp_enqueue_style( 'cm-micropayment-backend-style', CMMP_PLUGIN_URL . '/backend/assets/css/style.css' );
	}

	function column_cb( $item ) {
		return '<input type="checkbox" name="wallet_id[]" value="' . $item->wallet_id . '" />';
	}

	function prepare_items() {
		global $wpdb, $_wp_column_headers;

		$usersTable = $wpdb->users;

		$screen	 = get_current_screen();
		$query	 = "SELECT * FROM $this->_tableName t LEFT JOIN $usersTable u ON t.user_id = u.id";

		if ( isset( $_POST[ 's' ] ) && $_POST[ 's' ] != '' ) {
			$query .= " WHERE t.wallet_name LIKE '%" . $_POST[ 's' ] . "%' OR u.user_login LIKE '%" . $_POST[ 's' ] . "%'";
		}

		$orderby = !empty( $_GET[ "orderby" ] ) ? esc_sql( $_GET[ "orderby" ] ) : 'ASC';
		$order	 = !empty( $_GET[ "order" ] ) ? esc_sql( $_GET[ "order" ] ) : '';

		if ( !empty( $orderby ) & !empty( $order ) ) {
			$query.=' ORDER BY ' . $orderby . ' ' . $order;
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
			$query.=' LIMIT ' . (int) $offset . ',' . (int) $perpage;
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
