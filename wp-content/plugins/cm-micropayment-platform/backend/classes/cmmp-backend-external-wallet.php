<?php

class CMMicropaymentPlatformBackendExternalWallet extends CMMicropaymentPlatformPointsPrices {


	public function __construct() {
		parent::__construct();

	}


	/**
	 * render form
	 */
	public function render() {
		$this->handlePost();

		global $wpdb;
		require_once CMMP_PLUGIN_DIR . '/backend/classes/point-prices/list.php';

		$wp_list_table = new CMMicropaymentPlatformBackendPointsPricesList();
		$wp_list_table->prepare_items();

		echo '<div class="wrap">';
		$action = isset( $_GET['cmm-action'] ) ? $_GET['cmm-action'] : null;
		self::form();

		echo '</div>';
	}

	/**
	 * get form
	 *
	 * @param bool $data
	 */
	private function form( $data = false ) {

		if ( ob_start() ) {
			$wallet_key = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-key" );
			$enabled    = CMMicropaymentPlatform::get_option( "cmmp-external-wallet" );
			$URL    = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-url" );
			$refresh    = CMMicropaymentPlatform::get_option( "cmmp-external-wallet-refresh" );
			$page_tab   = 'external-wallet';

			$status = $enabled && CMMicropaymentPlatform::getExternalsWallets();

			include CMMP_PLUGIN_DIR . '/backend/views/external-wallet.php';
			$content = ob_get_clean();
			echo $content;
		}
	}

	/**
	 * Save date
	 */
	public function handlePost() {

		if(isset($_POST) && !empty($_POST)) {

			$postData = $_POST;
			unset( $postData['sender'] );
			unset( $postData['points_cost_id'] );
			foreach ( $postData AS $k => $v ) {
				CMMicropaymentPlatform::update_option( $k, $v );
			}
		}
		if(!empty($_POST) && !isset($_POST['cmmp-external-wallet'])){
			delete_option('cmmp-external-wallet');
		}


	}


}
