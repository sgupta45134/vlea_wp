<?php

/*
 * View for Group Paybox
 * */

use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\PeriodLabels;

$group           = $plans ?? [];
$priceGroupIndex = $plans['group_index'] ?? 0;
$plans           = $plans['prices'] ?? [];

if ( count( $plans ) == 1 ) {
	$singlePlan       = reset( $plans );
	$singlePriceIndex = key( $plans );
	$submitLabel      = sprintf(
		Labels::getLocalized( 'eddpay_subscription_checkout_button_period_for_amount' ),
	  PeriodLabels::getLocalizedArray( $singlePlan ), (float) $singlePlan['price']
	);
} else {
	$submitLabel = Labels::getLocalized( 'eddpay_subscription_checkout_button' );
}

$singlePriceIndex = $singlePriceIndex ?? 0;
?>

<form class="cmppp-edd-form" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">

    <div class="cmppp-edd-paybox-costs">
      <input type="hidden" name="priceGroupIndex" value="<?php echo $priceGroupIndex; ?>">

    <?php if ( count( $plans ) > 1 ): ?>
			<?php foreach ( $plans as $priceIndex => $plan ): ?>
				<label>
          <input type="radio" name="priceIndex" value="<?php echo $priceIndex; ?>">

    			<?php printf( Labels::getLocalized( 'eddpay_period_for_amount' ), PeriodLabels::getLocalizedArray( $plan ), $plan['price'] ); ?>

				</label>
			<?php endforeach; ?>

    <?php
      else:
	      $singlePlan['seconds'] = isset( $singlePlan['seconds'] ) ? $singlePlan['seconds'] : 0;
	      printf( '<input type="hidden" name="period" value="%d">', $singlePlan['seconds'] );
	      printf( '<input type="hidden" name="priceIndex" value="%s" />', esc_attr( $singlePriceIndex ) );
      endif;
    ?>
	</div>

  <div class="cmppp-edd-payment-buttons">
    <input type="hidden" name="provider" value="<?php echo esc_attr( $provider ); ?>" />
    <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>" />
    <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
    <input type="hidden" name="action" value="<?php echo esc_attr($ajaxAction); ?>" />
    <input type="hidden" name="callbackUrl" value="<?php echo esc_attr( $_SERVER[ 'REQUEST_URI' ] ); ?>" />
    <input type="submit" value="<?php echo esc_attr( $submitLabel ); ?>" />
  </div>

</form>
