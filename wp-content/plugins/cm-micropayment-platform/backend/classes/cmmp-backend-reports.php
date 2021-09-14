<?php

class CMMicropaymentPlatformBackendReports {

	public function __construct() {

	}

	public static function export() {
		if ( !class_exists( "CMMicropaymentPlatformBackendReportWalletData" ) ) {
			include_once CMMP_PLUGIN_DIR . '/backend/classes/reports/wallet/data.php';
		}

		if ( !class_exists( "CSVExport" ) ) {
			include_once CMMP_PLUGIN_DIR . '/shared/libs/CSV.php';
		}

		$walletData		 = new CMMicropaymentPlatformBackendReportWalletData();
		$preparedData	 = $walletData->prepareData();

		$exporter = new CSVExport;
		$exporter->setColumns( array( array( 'Date', 'Value' ) ) );
		$exporter->setData( $preparedData[ 'data' ] );
		$exporter->setData( array( array( 'TOTAL', $preparedData[ 'totals' ] ) ) );
		$exporter->download();
	}

	public static function export_wallets() {

		if ( ob_start() ) {
			if ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == 'table' ) {
				if ( !class_exists( "CMMicropaymentPlatformBackendReportWalletList" ) ) {
					include_once CMMP_PLUGIN_DIR . '/backend/classes/reports/wallet/list.php';
				}
				$module = new CMMicropaymentPlatformBackendReportWalletList();
				$module->export();
			}
		}
	}

	public function render() {

		if ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == 'table' ) {
			if ( !class_exists( "CMMicropaymentPlatformBackendReportWalletList" ) ) {
				include_once CMMP_PLUGIN_DIR . '/backend/classes/reports/wallet/list.php';
			}
			$module = new CMMicropaymentPlatformBackendReportWalletList();
		} else {
			if ( !class_exists( "CMMicropaymentPlatformBackendReportsWalletGraph" ) ) {
				include_once CMMP_PLUGIN_DIR . '/backend/classes/reports/wallet/graph.php';
			}
			$module = new CMMicropaymentPlatformBackendReportsWalletGraph();
		}
		$module->render();
	}

}
