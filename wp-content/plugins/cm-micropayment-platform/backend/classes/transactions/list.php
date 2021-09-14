<?php
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class CMMicropaymentPlatformBackendTransactionsList extends WP_List_Table {

    private $_tableName;

    function __construct() {
        global $wpdb;
        if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
            $tablePrefix = $wpdb->base_prefix . "cm_micropayments";
        } else {
            $tablePrefix = $wpdb->prefix . "cm_micropayments";
        }
        $this->_tableName = $tablePrefix . "_transactions";

        add_filter( 'page_row_actions', array( $this, 'pageRowActions' ), 10, 2 );

        parent::__construct( array(
            'singular' => 'wp_list_text_link',
            'plural'   => 'wp_list_test_links',
            'ajax'     => true
        ) );
    }

    function get_columns() {
        return $columns = array(
//            'transaction_id' => __('ID'),
            'cb'          => '<input type="checkbox" />', // this is all you need for the bulk-action checkbox
            'wallet_name' => __( 'Wallet Name/Code' ),
            'wallet_id'   => __( 'Wallet ID' ),
			'user_id'     => __( 'User ID' ),
			'user_login'  => __( 'User login' ),
            'amount'      => __( 'Price' ),
            'points'      => __( 'Amount of Points' ),
            'datetime'    => __( 'Date' ),
            'status'      => __( 'Status' )
        );
    }

    public function get_sortable_columns() {
        return array(
//            'transaction_id' => array('transaction_id', 'DESC'),
            'wallet_id'   => array( 'wallet_id', 'DESC' ),
            'wallet_name' => array( 'wallet_name', 'DESC' ),
            'user_id'     => array( 'user_id', 'DESC' ),
			'user_login'  => array( 'user_login', 'DESC' ),
            'datetime'    => array( 'datetime', 'DESC' ),
            'status'      => array( 'status', 'DESC' ),
            'points'      => array( 'c.points', 'DESC' ),
            'amount'      => array( 'c.amount', 'DESC' ),
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

    function column_default( $item, $column_name ) {

        if ( empty( $item ) ) {
            return '';
        }
        

        $row_actions = ($this->getRowActions(
                $item->{$column_name} ?? '' ))
                            ? $this->row_actions( $this->getRowActions( $item->{$column_name} )) 
                            : '';
        
        $row_actions_name = ($this->getRowActions( $item->transaction_id )) ? $this->row_actions( $this->getRowActions( $item->transaction_id )) : ''; 

        switch ( $column_name ) {
            case 'transaction_id':
                $result = $item->{$column_name} . $row_actions;
                break;
            case 'status':
                $result = isset( CMMicropaymentPlatformConst::$transactionType[ $item->{$column_name} ] ) ? CMMicropaymentPlatformConst::$paymentStatuses[ $item->{$column_name} ] : 'Undefined';
				if($item->{$column_name} != '1') {
					$result .= '<br>';
					$result .= '<a href="javascript:void(0);" class="accept_payment_cls" t_id="'.$item->transaction_id.'">Accept Payment</a>';
				}
                break;
            case 'user_id':
                $result = '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->user_id ) . '">' . $item->user_id . '</a>';
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
                $result = '<a href="' . admin_url( 'admin.php?page=cm_micropayments&range=this_month&walletID=' . $item->wallet_id . '&view=amount&type=table', 'admin' ) . '">' . $item->{$column_name} . '</a>' . $row_actions_name;
                break;
            default:
                $result = $item->{$column_name};
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

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $this->process_bulk_action();

        if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
            $tablePrefix = $wpdb->base_prefix . "cm_micropayments";
        } else {
            $tablePrefix = $wpdb->prefix . "cm_micropayments";
        }
        $walletTableName       = $tablePrefix . "_wallets";
        $walletChargeTableName = $tablePrefix . "_wallet_charges";

        $screen = get_current_screen();
        $query  = "SELECT t.*, w.wallet_name, w.wallet_id, w.user_id, c.points, c.amount FROM " . $this->_tableName . " AS t LEFT JOIN " . $walletChargeTableName . " AS c ON t.transaction_id = c.transaction_id LEFT JOIN " . $walletTableName . " AS w ON c.wallet_id = w.wallet_id ";

        $isWhere = false;

        if ( isset( $_GET[ 'start-date' ] ) && $_GET[ 'start-date' ] != '' ) {
            $date    = new DateTime( $_GET[ 'start-date' ] );
            $query .= " WHERE t.datetime >= '" . $date->format( 'Y-m-d' ) . "'";
            $isWhere = true;
        }

        if ( isset( $_GET[ 'end-date' ] ) && $_GET[ 'end-date' ] != '' ) {
            $date = new DateTime( $_GET[ 'end-date' ] );

            if ( !$isWhere ) {
                $query .= " WHERE t.datetime <= '" . $date->format( 'Y-m-d' ) . " 00:00:00'";
            } else {
                $query .= " AND t.datetime <= '" . $date->format( 'Y-m-d' ) . " 23:59:59'";
            }
            $isWhere = true;
        }

        if ( isset( $_GET[ 'status' ] ) ) {
            if ( !$isWhere ) {
                $query .= " WHERE t.status = " . esc_sql( $_GET[ 'status' ] );
            } else {
                $query .= " AND t.status = " . esc_sql( $_GET[ 'status' ] );
            }
            $isWhere = true;
        }

        $orderby = !empty( $_GET[ "orderby" ] ) ? esc_sql( $_GET[ "orderby" ] ) : 'ASC';
        $order   = !empty( $_GET[ "order" ] ) ? esc_sql( $_GET[ "order" ] ) : '';

        if ( !empty( $orderby ) & !empty( $order ) ) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        } else {
            $query .= ' ORDER BY t.datetime DESC';
        }

        $totalitems = $wpdb->query( $query );
        $perpage    = 5;

        $paged = !empty( $_GET[ "paged" ] ) ? intval( $_GET[ "paged" ] ) : '';

        if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
            $paged = 1;
        }

        $totalpages = ceil( $totalitems / $perpage );

        if ( !empty( $paged ) && !empty( $perpage ) ) {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page"    => $perpage,
        ) );

        $columns                           = $this->get_columns();
        $_wp_column_headers[ $screen->id ] = $columns;
        $this->items = $wpdb->get_results( $query );

    }

    /**
     * Handles the checkbox column output.
     *
     * @since 4.3.0
     *
     * @param WP_Post $post The current WP_Post object.
     */
    public function column_cb( $item ) {
        ?>
        <input id="cb-select-<?php echo $item->transaction_id ?>" type="checkbox" name="entry[]" value="<?php echo $item->transaction_id ?>" />
        <?php
    }

    /**
     * Define our bulk actions
     *
     * @since 1.2
     * @returns array() $actions Bulk actions
     */
    function get_bulk_actions() {
        $actions = array(
            'delete' => __( 'Delete' ),
//            'export-all'      => __( 'Export All', 'visual-form-builder' ),
//            'export-selected' => __( 'Export Selected', 'visual-form-builder' )
        );

        return $actions;
    }

    /**
     * Process our bulk actions
     *
     * @since 1.2
     */
    function process_bulk_action() {
		
		if( isset($_REQUEST[ 'entry' ]) ) {
			$entry_id = ( is_array( $_REQUEST[ 'entry' ] ) ) ? $_REQUEST[ 'entry' ] : array( $_REQUEST[ 'entry' ] );
		} else {
			$entry_id = array();
		}

        if ( 'delete' === $this->current_action() ) {
            global $wpdb;

            if ( !empty( $entry_id ) ) {
                foreach ( $entry_id as $id ) {
                    $id = absint( $id );
                    $wpdb->query( "DELETE FROM $this->_tableName WHERE transaction_id = $id" );
                }
            }
        }
    }

    protected function months_dropdown( $post_type ) {
        return;
    }

}
