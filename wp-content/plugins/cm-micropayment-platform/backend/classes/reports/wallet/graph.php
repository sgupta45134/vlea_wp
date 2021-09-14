<?php

class CMMicropaymentPlatformBackendReportsWalletGraph
{

    public function render()
    {
        self::registerScripts();

        if( ob_start() )
        {
            if(!class_exists("CMMicropaymentPlatformBackendReportWalletData"))
            {
                include_once CMMP_PLUGIN_DIR . '/backend/classes/reports/wallet/data.php';
            }
            $walletData = new CMMicropaymentPlatformBackendReportWalletData();
            $preparedData = $walletData->prepareData();
            $data = json_encode($preparedData['data']);
            $content_name = 'graph';

            $viewName = isset($_GET['view']) ? $_GET['view'] : 'amount';
            switch($viewName)
            {
                case 'amount':
                    $unitName = ((CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency','') != '') ? CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency','') : 'USD');
					$currencySymbol	 = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
                    if(!empty($currencySymbol)){
                        $unitName = $currencySymbol;
                    }
                    break;
                case 'count':
                    $unitName = ($preparedData['totals'] > 1) ? __('transactions') : __('transaction');
                    break;
                case 'points':
                    $unitName = ($preparedData['totals'] > 1) ? CMMicropaymentPlatform::get_option('cm_micropayment_plural_name') : CMMicropaymentPlatform::get_option('cm_micropayment_singular_name');
                    break;
                default:
                    break;
            }

            include CMMP_PLUGIN_DIR . '/backend/views/reports.phtml';
            $content = ob_get_clean();
            echo $content;
        }
    }

    private static function registerScripts()
    {
        wp_register_script('cm-micropayment-admin-scripts-flot', CMMP_PLUGIN_URL . '/backend/assets/js/flot/jquery.flot.min.js');
        wp_register_script('cm-micropayment-admin-scripts-flot-time', CMMP_PLUGIN_URL . '/backend/assets/js/flot/jquery.flot.time.min.js');
//        wp_register_script('cm-micropayment-admin-reports-scripts', CMMP_PLUGIN_URL . '/backend/assets/js/scripts.js');

        wp_enqueue_script( 'cm-micropayment-admin-scripts-flot' );
        wp_enqueue_script( 'cm-micropayment-admin-scripts-flot-time' );
//        wp_enqueue_script( 'cm-micropayment-admin-reports-scripts' );
    }


}