<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\PaymentMethodFactory;

$content                      = $content ?? '';
$restricted_shortcode_display = false;

if ( App::isLicenseOk() and
     $post = Post::getInstance( get_post() ) and
     ! PostController::isPreviewAllowed( $post ) ) {

	$sub               = new Subscription( $post );
	$payments          = PaymentMethodFactory::getPaymentsNameList();
	$filtered_payments = PaymentMethodFactory::filterPaymentForPost( $payments, $post );

	if ( empty( $filtered_payments ) ) {

		$is_subsciptionActive = false;

	} else {
		if ( is_user_logged_in() ) {
			$is_subsciptionActive = $sub->isSubscriptionActive();

		} else {

			$is_subsciptionActive = $sub->isGuestSubscriptionActive( $filtered_payments );
		}
	}


	if ( $is_subsciptionActive ) {
		echo $content;
	} else {
		$restricted_shortcode_display = true;
	}
} else {
	$restricted_shortcode_display = true;
}
?>
<?php if ( $restricted_shortcode_display == true ) { ?>
  <div class="cmppp-paybox cmppp_restricted_shortcode_div">
    <h3><?php echo Labels::getLocalized( 'restricted_shortcode_heading' ); ?></h3>
    <p><?php echo Labels::getLocalized( 'restricted_shortcode_content' ); ?></p>
  </div>
<?php } ?>
