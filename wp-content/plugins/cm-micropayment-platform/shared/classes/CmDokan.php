<?php


/**
 * Dokan integration
 * Class CmDokan
 */
class CmDokan {

	/**
	 * add WP hooks
	 * CmDokan constructor.
	 */
	public function __construct() {

		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'sendPointsToVendors' ] );
	}

	/**
	 * Send points to vendors wallets
	 * @param $parent_order_id
	 */
	public function sendPointsToVendors( $parent_order_id ) {
		if(!function_exists('dokan_get_sellers_by') || !CMMicropaymentPlatform::get_option( 'cm_micropayment_use_dokan' )){
			return;
		}
		$vendors = dokan_get_sellers_by( $parent_order_id );
		$parent_order = wc_get_order( $parent_order_id );

		foreach ( $vendors as $seller_id => $seller_products ) {
			$price = 0;
			foreach ($seller_products as $product){
				$price += $product->get_subtotal();
			}

			$walletToCode = apply_filters( 'cm_micropayments_user_wallet_code', $seller_id );

			$wallet = CMMicropaymentPlatformFrontendWallet::instance();

			$price = $this->chargeFee($wallet,$price);

			$args = array(
				'wallet_id'      => $wallet->getWalletByCode( $walletToCode )->wallet_id,
				'amount'  => $price,

			);
			$result =	apply_filters('charge_wallet', $args);

		}

	}

	/**
	 * send to admin wallet some percentage fee if it's enabled
	 * @param $wallet
	 * @param $price
	 *
	 * @return float|int
	 */
	public function chargeFee($wallet,$price){
		$enablePercent = CMMicropaymentPlatform::get_option( 'cm_micropayment_dokan_enable_percentage' );
		$percentFee = CMMicropaymentPlatform::get_option( 'cm_micropayment_dokan_percentage_fee' );
		$adminWallet = CMMicropaymentPlatform::get_option( 'cm_micropayment_dokan_percentage_wallet' );

		if($enablePercent){
			$fee = $price * $percentFee;
			$args = array(
				'wallet_id'      => $wallet->getWalletByCode( $adminWallet )->wallet_id,
				'amount'  => $fee,

			);
			apply_filters('charge_wallet', $args);
			$price = $price - $fee;
		}

		return $price;
	}
}