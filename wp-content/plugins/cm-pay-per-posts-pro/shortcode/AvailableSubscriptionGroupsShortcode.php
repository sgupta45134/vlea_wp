<?php

namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\controller\PayboxController;
use com\cminds\payperposts\helper\TimeHelper;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\model\Micropayments;

class AvailableSubscriptionGroupsShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmppp-available-subscription-groups';

	static function shortcode( $atts ) {

		wp_enqueue_script( 'cmppp-utils' );
		wp_enqueue_style( 'cmppp-frontend' );
		wp_enqueue_script( 'cmppp-frontend' );

		// get all groups ids

		$groupEnabled  = 1;
		$paymentMethod = null;

		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			$paymentMethod = new PostInstantPayment( new Post( get_post() ) );
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$paymentMethod = new PostWooPayment( new Post( get_post() ) );
		}


//		if ( is_plugin_active( 'cm-micropayment-platform/cm-micropayment-platform.php' ) ) {
//			$paymentMethod = new Micropayments( new Post( get_post() ) );
//		}


		if ( is_null( $paymentMethod ) ) {
			return '';
		}


		$groups_list = $paymentMethod->getPricingGroupsList();

		$style = "";
		$class = "";

		if ( ! wp_is_mobile() ) {
			if ( isset( $atts['col'] ) && ! empty( $atts['col'] ) ) {
				$col = (int) $atts['col'];
			} else {
				$col = '2';
			}


			$style = 'style="grid-template-columns: repeat(' . $col . ', 1fr);"';

		} else {

			$class = "mobile";
		}


		$views = '<div class="cmppp-paybox-groups-list tile ' . $class . '" ' . $style . '>';

		foreach ( $groups_list as $group_index => $group_name ) {

			if ( $group = $paymentMethod->isGroupPaid( $group_index ) ) {

				if ( $group['end_date'] == 'lifetime' || $group['end_date'] - time() > 1577880000 ) {
					$label = str_replace(
						[ '%group_name%' ],
						[ $group['group_name'] ],
						Labels::getLocalized( 'shortcode_group_paybox_subscribed_lifetime' )
					);

				} else {

					$datetime = TimeHelper::showUserLocalDatetime( $group['end_date'], 0, true );

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

					$view = '';

				} else {

					$form = '';

					if ( $paymentMethod ) {
						$plans      = $paymentMethod::getSubscriptionPlansForGroup( $group_index );
						$postId     = null;
						$nonce      = wp_create_nonce( PayboxController::NONCE_ACTIVATE );
						$provider   = get_class( $paymentMethod );
						$ajaxAction = PayboxController::AJAX_ACTION;
						$form       = PayboxController::loadFrontendView( 'paybox-form', compact( 'plans', 'postId', 'nonce', 'provider', 'ajaxAction' ) );
					}

					$shortcode_group_paybox = true;

					$view = PayboxController::loadFrontendView( 'paybox-groups-list', compact( 'groupEnabled', 'form', 'shortcode_group_paybox', 'group_name' ) );
				}
			}

			$views .= "<div class='cmppp-paybox-groups-list__item'>" . $view . "</div>";
		}


		$views .= '</div>';

		return $views;
	}

}
