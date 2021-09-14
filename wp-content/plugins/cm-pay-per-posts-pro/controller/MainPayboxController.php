<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\PaymentMethodFactory;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\Subscription;

class MainPayboxController {

	public static $payments;


	public static function init() {
		if ( App::isLicenseOk() ) {

			self::$payments = PaymentMethodFactory::getPaymentsNameList();

			add_filter( 'the_content', [ __CLASS__, 'the_content' ], 1000, 1 );
			add_action( 'woocommerce_before_main_content', [ __CLASS__, 'woocommerce_before_main_content' ], 10 );
			add_action( 'wp_head', [ __CLASS__, 'wp_head' ] );
		}
	}


	// for Woocommerce product -- if post_content is empty
	public static function woocommerce_before_main_content() {
		$hide_page_content_option = Settings::getOption( Settings::OPTION_HIDE_PAGE_CONTENT );

		$post = get_post();

		if ( ! is_null( $post ) && empty( $post->post_content )
		     && ( $hide_page_content_option == Settings::HIDE_FULL_PAGE_CONTENT || $hide_page_content_option == Settings::HIDE_SPECIFIED_BLOCK ) ) {

			echo apply_filters( 'the_content', $post->post_content );
		}
	}


	public static function the_content( $content ) {

		$_post = get_post();

		if ( is_null( $_post ) ) {
			return $content;
		}

		$post     = Post::getInstance( $_post );
		$payments = PaymentMethodFactory::filterPaymentForPost( self::$payments, $post );

		if ( ! is_null( $post ) && ! PostController::isPreviewAllowed( $post ) && ! empty( $payments ) ) {

			$pay_boxes   = "";
			$old_content = $content;

			SubscriptionsController::enqueueLogoutHandler();

			$sub             = new Subscription( $post );
			$current_user_id = get_current_user_id();
			$current_post    = get_post( $post->getId() );


			if ( is_user_logged_in() ) {
				$is_subsciptionActive = $sub->isSubscriptionActive();
			} else {
				$is_subsciptionActive = $sub->isGuestSubscriptionActive( $payments );
			}

			$owner = 0;
			if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_FORM_NOT_FOR_OWNER ) ) {
				if ( ( $current_user_id > 0 && $current_user_id == $current_post->post_author ) || ( $current_user_id > 0 && current_user_can( 'administrator' ) ) ) {
					$owner = 1;
				}
			}

			if ( $is_subsciptionActive || $owner ) {

				$content = '<div class="cmppp_content_container">';
				if ( ! $owner && Settings::getOption( Settings::OPTION_SHOW_MESSAGE_YOU_HAVE_BOUGHT_THE_POST ) ) {
					$content .= '<div class="cmppp_messages_block">' .
					            Labels::getLocalized( 'message_' . Settings::OPTION_SHOW_MESSAGE_YOU_HAVE_BOUGHT_THE_POST ) .
					            '</div>';
				}
				$content .= '<div class="cmppp_content_inner_container">';
				$content .= apply_filters( 'cm_tooltip_parse', do_shortcode( $old_content ) );
				if ( Settings::getOption( Settings::OPTION_RESTRICT_COPYING_CONTENT ) ) {
					$content .= '<p class="cmppp_restrict_message">' . Labels::getLocalized( 'restrict_copying_content' ) . '</p>';
				}
				$content .= '</div>';
				$content .= '</div>';

			} else {

				if ( is_user_logged_in() ) {
					foreach ( $payments as $payment ) {
						// $Controller = $payment . 'Controller'; // TODO: it doesn't work, doesn't want to find the class
						if ( $payment == 'EDD' ) {
							$pay_boxes .= EDDController::getPaybox( $post );
						}

						if ( $payment == 'Micropayments' ) {
							$pay_boxes .= MicropaymentsController::getPaybox( $post );
						}

						if ( $payment == 'WooCommerce' ) {
							$is_on_hold = get_user_meta($current_user_id, 'order_on_hold_' . $post->getId());
							if($is_on_hold) {
								$pay_boxes .= Labels::getLocalized( 'eddpay_wait_until_admin_approve' );
							}
							else {
								$pay_boxes .= WooCommerceController::getPaybox( $post );
							}
						}
					}

				} else {

					$pay_boxes .= PayboxController::getGuestBox( $post );
				}

				$pay_boxes = trim( $pay_boxes );


				if ( strpos( $content, 'cmppp_restricted_shortcode_div' ) !== false ) {
					$old_content = $content;
				} else {
					if ( Settings::getOption( Settings::OPTION_HIDE_PAGE_CONTENT ) == Settings::HIDE_POST_THE_CONTENT ) {
						$old_content = '';
					} else {
						$old_content = $post->getPostFragment();
					}
				}

				$fade = '';
				if ( Settings::getOption( Settings::OPTION_FADE_ENABLED ) ) {
					$fade = ' cmppp_content_inner_container_with_paybox';
				}

				$content = '<div class="cmppp_content_container cmppp-not-allowed">';
				$content .= '<div class="cmppp_content_inner_container' . $fade . '">';
				$content .= apply_filters( 'cm_tooltip_parse', do_shortcode( $old_content ) );

				if ( Settings::getOption( Settings::OPTION_RESTRICT_COPYING_CONTENT ) ) {
					$content .= '<p class="cmppp_restrict_message">' . Labels::getLocalized( 'restrict_copying_content' ) . '</p>';
				}

				$content .= '</div>';
				$content .= $pay_boxes;
				$content .= '</div>';
			}

			return do_shortcode( $content );
		}

		return $content;
	}


	public static function wp_head() {

		/* Add styles for checkout button if enabled */
		if ( Settings::getOption( Settings::OPTION_STYLES_PAYBOX_ENABLED ) ) {

			$width = '';
			if ( Settings::getOption( Settings::OPTION_STYLES_PAYBOX_WIDTH ) == 'full' ) {
				$width = 'width: 100% !important; min-width: 100% !important;';
			}

			$text_color       = "color:" . Settings::getOption( Settings::OPTION_STYLES_PAYBOX_TEXT_COLOR ) . " !important;";
			$background_color = "background-color:" . Settings::getOption( Settings::OPTION_STYLES_PAYBOX_BACKGROUND_COLOR ) . " !important;";
			$border_color     = "border-color:" . Settings::getOption( Settings::OPTION_STYLES_PAYBOX_BORDER_COLOR ) . " !important;";

			$styles = ".cmppp-paybox { $text_color $width $background_color $border_color }";
			$styles .= ".cmppp-paybox h3 { $text_color }";

			echo "<style>$styles</style>";
		}

		/* Add styles for checkout button if enabled */
		if ( Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_ENABLED ) ) {
			$text_color = "color:" . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_TEXT_COLOR ) . " !important;";
			$bg_color   = "background-color:" . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_BG_COLOR ) . " !important;";

			$hover_text_color = "color:" . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_TEXT_COLOR ) . " !important;";
			$hover_bg_color   = "background-color:" . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BG_COLOR ) . " !important;";

			if ( Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_HIDE_BORDER ) ) {
				$border       = "border: none !important;";
				$border_hover = "border: none !important;";
			} else {
				$border = "border-color: " . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_BORDER_COLOR ) . " !important;";
				$border .= "border-style: solid;";

				$border_hover = "border-color: " . Settings::getOption( Settings::OPTION_STYLES_CHECKOUT_BUTTON_HOVER_BORDER_COLOR ) . " !important;";
				$border_hover .= "border-style: solid;";
			}

			$styles = ".cmppp-paybox [type='submit'] { $text_color $bg_color $border }";
			$styles .= ".cmppp-paybox [type='submit']:hover { $hover_text_color $hover_bg_color $border_hover }";

			echo "<style>$styles</style>";
		}

	}
}
