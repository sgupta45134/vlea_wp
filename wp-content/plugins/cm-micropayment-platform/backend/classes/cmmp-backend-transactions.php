<?php

require_once CMMP_PLUGIN_DIR . '/shared/models/transactions.php';

class CMMicropaymentPlatformBackendTransactions extends CMMicropaymentPlatformTransactions {

    public function __construct() {
        parent::__construct();
    }

    public function render() {

        global $wpdb;
        require_once CMMP_PLUGIN_DIR . '/backend/classes/transactions/list.php';
        self::initJs();
        $wp_list_table  = new CMMicropaymentPlatformBackendTransactionsList();
        $wp_list_table->prepare_items();
        $countAll       = $this->count();
        $countCompleted = $this->getCountByStatus( 1 );
        $countPending   = $this->getCountByStatus( 0 );
        echo '<div class="wrap">';
        $action         = isset( $_GET[ 'cmm-action' ] ) ? $_GET[ 'cmm-action' ] : null;

        switch ( $action ) {
            default:

                if ( ob_start() ) {

                    include CMMP_PLUGIN_DIR . '/backend/views/transactions.phtml';

                    $content = ob_get_clean();
                    echo $content;
                }

                break;
        }
        echo '</div>';
    }

    public static function initJs() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    }

}
