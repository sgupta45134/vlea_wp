<?php
class CMMicropaymentPlatformBackendFeesUser {

	public function __construct() {

		if($this->isFeesActive() && !self::getFeesUserId()){
			$this->installFeesUser();
		}

	}

	private function isFeesActive() {
		if(CMMicropaymentPlatform::get_option( 'cm_micropayment_adding_points_transaction_fee', '0' ) ||
		   CMMicropaymentPlatform::get_option( 'cm_micropayment_subtracting_points_transaction_fee', '0' ))
		{
		    return true;
		}
		else {
			return false;
		}
	}

	private static function installFeesUser()
	{
		$feesUser = username_exists('CMMicropaymentFee');
		if( empty($feesUser) )
		{
			$id = wp_insert_user(array(
				'user_login'   => 'CMMicropaymentFee',
				'user_pass'    => md5(mt_rand()),
				'nickname'     => 'MicropaymentFee',
				'display_name' => 'MicropaymentFee',
				'role'         => 'subscriber'
			));

			if( is_int($id) )
			{
				add_option('cmmp_CMMicropaymentFeeUserId', $id);
			}
		}
		else
		{
			$id = $feesUser;
		}

		return $id;
	}

	public static function getFeesUserId() {
		$id = get_option('cmmp_CMMicropaymentFeeUserId');
		return $id ? : 0;
	}

	public static function getFeesUserWallet() {
		$wallet = new CMMicropaymentPlatformWallet();
		if($feesWallet = $wallet->getWalletByUserID(self::getFeesUserId())) {
		    return $feesWallet;
		}
		else {
			return false;
		}
	}

}