<?php

class CMMicropaymentPlatformBackendSettings {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( get_class(), 'registerScriptsAndStyles' ) );
    }

    public function render() {

        $this->handlePost();

        $tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '';

        switch ( $tab ) {
            case
                'gateways' : $page_tab = 'gateway';
                break;
            case 'stripes' : $page_tab = 'stripe';
                break;
            case 'emailtemplates' : $page_tab = 'emailtemplates';
                break;
            case 'labels' : $page_tab = 'labels';
                break;
            case 'cron' : $page_tab = 'cron';
                break;
            case '' : $page_tab = 'general';
                break;
            default : $page_tab = $tab;
                break;
        }

        ob_start();
        if ( file_exists( plugin_dir_path( CMMP_PLUGIN_FILE ) . 'backend/views/setting-tabs/' . $page_tab . '.phtml' ) ) {
            include_once plugin_dir_path( CMMP_PLUGIN_FILE ) . 'backend/views/setting-tabs/' . $page_tab . '.phtml';
        }
        echo "";
        $tab_content = ob_get_clean();

        $tab_content_filtered = apply_filters( 'cmmp_tab_content', $tab_content, $page_tab );

        ob_start();
        include CMMP_PLUGIN_DIR . '/backend/views/settings.phtml';
        $content = ob_get_clean();
        echo $content;
    }

    private function handlePost() {
        if ( count( $_POST ) > 0 ) {
            $postData = $_POST;

            $ret = true;

            if ( isset( $postData[ 'cm_micropayment_assign_wallet_to_customer_button' ] ) ) {
                do_action( 'create_wallets_for_existing_users' );
                unset( $postData[ 'cm_micropayment_assign_wallet_to_customer_button' ] );
            }

            if ( isset( $postData[ 'cm_micropayment_use_woo_checkout' ] ) ) {
                CMMicropaymentPlatformBackend::useWOOCheckout( $postData[ 'cm_micropayment_use_woo_checkout' ] );
            }

            if ( isset( $postData[ 'cm_micropayment_use_edd_checkout' ] ) ) {
                CMMicropaymentPlatformBackend::useEddCheckout( $postData[ 'cm_micropayment_use_edd_checkout' ] );
            }

            foreach ( $postData AS $k => $v ) {
                CMMicropaymentPlatform::update_option( $k, wp_unslash($v) );
            }

            if ( !isset( $postData[ 'cm_micropayment_test_mode' ] ) && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'gateways' ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_test_mode', 0 );
            }

            if ( !isset( $postData[ 'cm_micropayment_hide_create_button_after_action' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_hide_create_button_after_action', 0 );
            }

            if ( !isset( $postData[ 'cm_micropayment_assign_wallet_to_customer' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_assign_wallet_to_customer', 0 );
            }

			if ( !isset( $postData[ 'cm_micropayment_grant_points_to_admin_or_seller' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_grant_points_to_admin_or_seller', 0 );
            }

            if ( !isset( $postData[ 'cm_micropayment_disable_anonymous_payments' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_disable_anonymous_payments', 0 );
            }

            if ( !isset( $postData[ 'cm_micropayment_show_input' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_show_input', 0 );
            }
	        if ( !isset( $postData[ 'cm_micropayment_store_purchases_wallet' ] ) && !isset( $_GET[ 'tab' ] ) ) {
		        CMMicropaymentPlatform::update_option( 'cm_micropayment_store_purchases_wallet', 0 );
	        }
            if ( !isset( $postData[ 'cm_micropayment_show_dashboard_widget' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_show_dashboard_widget', 0 );
            }
            if ( !isset( $postData[ 'cm_micropayment_enable_decimal' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_enable_decimal', 0 );
            }
            if ( !isset( $postData[ 'cm_micropayment_use_dokan' ] ) && !isset( $_GET[ 'tab' ] ) ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_use_dokan', 0 );
            }

            if ( !isset( $postData[ 'cm_micropayment_send_notifications' ] ) && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'emailtemplates' ) {
                CMMicropaymentPlatform::update_option( 'cm_micropayment_send_notifications', 0 );
            }

	        if ( !isset( $postData[ 'cm_micropayment_send_wallet_exchange_notifications' ] ) && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'emailtemplates' ) {
		        CMMicropaymentPlatform::update_option( 'cm_micropayment_send_wallet_exchange_notifications', 0 );
	        }


            if ( isset( $postData[ 'cmmp_removeAllItems' ] ) ) {
                self::_cleanupItems();
            }

            do_action( 'cmmp_after_settings_save', $postData );

            $_SESSION[ 'success-message' ] = __( 'Settings has been saved' );
        }
    }

    public static function _cleanupItems(){

        do_action( 'cmmp_do_cleanup_items_before' );

        $walletObj = CMMicropaymentPlatformWallet::instance();
        $walletObj->deleteAllWallets();

        /*
         * Invalidate the list of all glossary items stored in cache
         */
        do_action( 'cmmp_do_cleanup_items_after' );
    }

    public static function registerScriptsAndStyles() {
        /*
         * Scripts
         */
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'cm-micropayment-admin-scripts', CMMP_PLUGIN_URL . '/backend/assets/js/scripts.js', array('jquery', 'jquery-ui'), CMMicropaymentPlatform::version() );
        $jsData         = array(
            'ajaxurl' => admin_url( 'admin-ajax.php?action=cm_micropayment_platform_save_wallet_points' ),
            'l18n'    => array(
                'save'   => __( 'Save' ),
                'cancel' => __( 'Cancel' ),
                'label'  => __( 'Change button points value' ),
            )
        );
        $jsDataFiltered = apply_filters( 'cmmp_wallet_render_js_data', $jsData );
        wp_localize_script( 'cm-micropayment-admin-scripts', 'cmmp_data', $jsDataFiltered );
        /*
         * Styles
         */
        wp_enqueue_style( 'cm-micropayment-backend-jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/css/jquery-ui/ui-lightness/jquery-ui-1.10.4.custom.min.css' );
        wp_enqueue_style( 'cm-micropayment-backend-style', CMMP_PLUGIN_URL . '/backend/assets/css/style.css' );
    }

}
