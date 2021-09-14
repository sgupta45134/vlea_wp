<?php

final class CMMicropaymentPlatformConst {

	public static $paymentStatuses = array(
		0	 => 'Pending',
		1	 => 'Successful',
		2	 => 'Abadoned',
		3	 => 'Invoiced'
	);
	public static $transactionType = array(
		0	 => 'grant',
		1	 => 'charge',
		2	 => 'transfer_to_another_wallet',
		3	 => 'raceived_from_another_wallet',
		4	 => 'granted_manually',
		5	 => 'edd_payment',
		6	 => 'edd_purchase_grant',
		7	 => 'paypal_payout',
		10	 => 'woo_purchase_grant',
		11	 => 'woo_payment_charge',
		12	 => 'import_operation',
		13	 => 'transaction_commission',
        14   => 'wallet_exchange'
	);

	public static function isTestMode() {
		return CMMicropaymentPlatform::get_option( 'cm_micropayment_test_mode' ) == 1;
	}

	public static function getScheme() {
		$scheme = 'http';
		if ( isset( $_SERVER[ 'HTTPS' ] ) and $_SERVER[ 'HTTPS' ] == 'on' ) {
			$scheme .= 's';
		}
		return $scheme;
	}

}
