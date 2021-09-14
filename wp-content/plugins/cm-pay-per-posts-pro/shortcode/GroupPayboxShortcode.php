<?php

namespace com\cminds\payperposts\shortcode;


use com\cminds\payperposts\controller\PayboxController;
use com\cminds\payperposts\helper\TimeHelper;
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Labels;

class GroupPayboxShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmppp-group-paybox';

	static function shortcode( $atts ) {

		if ( isset( $atts['group_id'] ) && ! empty( $atts['group_id'] ) ) {

			$group_index = $atts['group_id'];

			$groupEnabled = 1;

			$paymentMethod = null;

			$post = new Post( get_post() );

			if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
				$paymentMethod = new PostInstantPayment( $post );
			}
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$paymentMethod = new PostWooPayment( $post );
			}

			// TODO: Add Micropayments here
//			if ( $payment == 'Micropayments' ) {
//				$paymentInstance = new Micropayments( $post );
//			}

			if ( $group = $paymentMethod->isGroupPaid( $group_index ) ) {

				if ( $group['end_date'] == 'lifetime' || $group['end_date'] - time() > 1577880000) {
					$label = str_replace(
						[ '%group_name%' ],
						[ $group['group_name'] ],
						Labels::getLocalized( 'shortcode_group_paybox_subscribed_lifetime' )
					);

				} else {

					$datetime = TimeHelper::showUserLocalDatetime($group['end_date'], 0, true);

					$label = str_replace(
						[ '%group_name%', '%end_date%' ],
						[ $group['group_name'], $datetime ],
						Labels::getLocalized( 'shortcode_group_paybox_subscribed' )
					);
				}

				$view = "<div class='cmppp-paybox cm_shortcode_group_paybox subscribed'>{$label}</div>";


			} else {

				$group_name = $paymentMethod->getPricingGroupName( $group_index );

				if ( ! $paymentMethod->groupHasAnyContent( $group_index ) ) {

					$label = str_replace(
						'%group_name%',
						$group_name,
						Labels::getLocalized( 'shortcode_group_paybox_has_no_content' )
					);
					$view  = "<div class='cmppp-paybox cm_shortcode_group_paybox has_no_content'>{$label}</div>";

				} else {

					$form = ''; // group form

					if ( $paymentMethod ) {
						$plans      = $paymentMethod::getSubscriptionPlansForGroup( $group_index );
						$postId     = null;
						$nonce      = wp_create_nonce( PayboxController::NONCE_ACTIVATE );
						$provider   = get_class( $paymentMethod );
						$ajaxAction = PayboxController::AJAX_ACTION;
						$form       = PayboxController::loadFrontendView( 'paybox-form', compact( 'plans', 'postId', 'nonce', 'provider', 'ajaxAction' ) );
					}

					$shortcode_group_paybox = true;

					$view = PayboxController::loadFrontendView( 'paybox', compact( 'groupEnabled', 'form', 'shortcode_group_paybox', 'group_name' ) );
				}

			}


			return $view;
		}

		return '';
	}

}
