<?php
namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\controller\PayboxController;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\SubscriptionReport;

class AvailabilityShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmppp_availability';
	
	static function shortcode($atts=array()) {
		
		$atts = shortcode_atts(array(
			'postid' => 0
		), $atts);
		
		$limit_enable = Settings::getOption(Settings::OPTION_SUBSCRIPTION_LIMIT_ENABLE);
		$limit_number = Settings::getOption(Settings::OPTION_SUBSCRIPTION_LIMIT_NUMBER);
		
		if($limit_enable == '1' && $limit_number > 0 && $atts['postid'] > 0) {

			$available = Labels::getLocalized('post_availability_available');
			$not_available = Labels::getLocalized('post_availability_not_available');
			$already_own = Labels::getLocalized('post_availability_already_own');

			$subscriptions_count = SubscriptionReport::getCountByPostId($atts['postid']);
			
			$flag = false;
			if(is_user_logged_in() && get_current_user_id()) {
				$alreadyown = SubscriptionReport::getAlreadyOwn($atts['postid'], get_current_user_id());
				if($alreadyown == '1') {
					$flag = true;
				}
			}

			if($subscriptions_count >= $limit_number) {
				if($flag == true) {
					return '<i class="cmppp_already_own">'.$already_own.'</i>';
				} else {
					return '<i class="cmppp_not_available">'.$not_available.'</i>';
				}
			}
			else {
				if($flag == true) {
					return '<i class="cmppp_already_own">'.$already_own.'</i>';
				} else {
					return '<i class="cmppp_available">'.$available.'</i>';
				}
			}
		} else {
			return '';
		}

	}
	
}
?>