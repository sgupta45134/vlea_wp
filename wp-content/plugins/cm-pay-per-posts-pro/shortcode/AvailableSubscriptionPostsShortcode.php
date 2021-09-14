<?php

namespace com\cminds\payperposts\shortcode;


use com\cminds\payperposts\controller\PayboxController;
use com\cminds\payperposts\helper\TimeHelper;
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Labels;

class AvailableSubscriptionPostsShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmppp-available-subscription-posts';

	static function shortcode( $atts ) {

		wp_enqueue_script( 'cmppp-utils' );
		wp_enqueue_style( 'cmppp-frontend' );
		wp_enqueue_script( 'cmppp-frontend' );

		$view = '';

		$paymentMethod = null;


		// TODO: refactor it
		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			$paymentMethod = new PostInstantPayment();
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$paymentMethod = new PostWooPayment();
		}

		// TODO: Add Micropayments here
//			if ( $payment == 'Micropayments' ) {
//				$paymentInstance = new Micropayments( $post );
//			}

		if ( ! $paymentMethod ) {
			return '';
		}


		$posts = $paymentMethod->getAllProtectedPosts();

		foreach ( $posts as $p ) {

			$post                 = Post::getInstance( $p );
			$paymentMethod->post  = $post;
			$cmppp_pricing_single = $paymentMethod::getPricingSingle( $p->ID );
			$singleEnabled        = ( isset( $cmppp_pricing_single['allow'] ) && $cmppp_pricing_single['allow'] ) ? 1 : 0;

			if ( $singleEnabled ) {
				$form  = PayboxController::getSinglePayboxForm( $post, $paymentMethod, $cmppp_pricing_single );
				$title = '<h3>' . $p->post_title . '</h3>';

				$img   = get_the_post_thumbnail( $p->ID, 'thumbnail' );

				$view .= '<div class="cmppp-post-card">' . $title . $img . $form . '</div>';
			}
		}


		return '<div class="cmppp-post-card__list">' . $view . '</div>';
	}

}
