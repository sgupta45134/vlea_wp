<?php
namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\model\SubscriptionReport;
use com\cminds\payperposts\controller\SubscriptionsController;

class SubscriptionsShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmppp-subscriptions';
	
	static function shortcode($atts) {
		if (is_user_logged_in() AND $user = get_userdata(get_current_user_id())) {
			return SubscriptionsController::loadFrontendView('shortcode', array(
				'data' => SubscriptionReport::getUserSubscriptions($user->ID)
			));
		}
	}

}