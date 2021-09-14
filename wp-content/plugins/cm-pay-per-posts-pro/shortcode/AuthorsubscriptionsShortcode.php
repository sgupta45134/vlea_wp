<?php
namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\model\SubscriptionReport;
use com\cminds\payperposts\controller\SubscriptionsController;

class AuthorsubscriptionsShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmppp-author-subscriptions';
	
	static function shortcode($atts) {
		if (is_user_logged_in() AND $user = get_userdata(get_current_user_id())) {
			return SubscriptionsController::loadFrontendView('author-shortcode', array(
				'data' => SubscriptionReport::getAuthorSubscriptions($user->ID)
			));
		}
	}

}