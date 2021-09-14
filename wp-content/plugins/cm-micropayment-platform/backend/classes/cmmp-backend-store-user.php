<?php
class CMMicropaymentPlatformBackendStorePurchasesUser {

	public function __construct() {

		if($this->isStorePurchasesActive() && !self::getStorePurchasesUserId()){
			$this->installStorePurchasesUser();
		}

	}

	private function isStorePurchasesActive() {
		if(CMMicropaymentPlatform::get_option( 'cm_micropayment_store_purchases_wallet', '0' ))
		{
			return true;
		}
		else {
			return false;
		}
	}

	private static function installStorePurchasesUser()
	{
		$storePurchasesUser = username_exists('CMMicropaymentStorePurchases');
		if( empty($storePurchasesUser) )
		{
			$id = wp_insert_user(array(
				'user_login'   => 'CMMicropaymentStorePurchases',
				'user_pass'    => md5(mt_rand()),
				'nickname'     => 'MicropaymentStorePurchases',
				'display_name' => 'MicropaymentStorePurchases',
				'role'         => 'subscriber'
			));

			if( is_int($id) )
			{
				add_option('cmmp_CMMicropaymentStorePurchasesUserId', $id);
			}
		}
		else
		{
			$id = $storePurchasesUser;
		}

		return $id;
	}

	public static function getStorePurchasesUserId() {
		$id = get_option('cmmp_CMMicropaymentStorePurchasesUserId');
		return $id ? : 0;
	}

	public static function getStorePurchasesUserWallet() {
		$wallet = new CMMicropaymentPlatformWallet();
		if($storePurchasesWallet = $wallet->getWalletByUserID(self::getStorePurchasesUserId())) {
			return $storePurchasesWallet;
		}
		else {
			return false;
		}
	}
}