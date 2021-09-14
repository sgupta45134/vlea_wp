<?php
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\PeriodLabels;
?>

<?php if (isset($cmppp_woo_pricing_single) && !empty($cmppp_woo_pricing_single)): ?>
    <form class="cmppp-edd-form" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
    	<?php
      $periodText       = PeriodLabels::getLocalizedArray( $cmppp_woo_pricing_single );
      $periodInSec      = Subscription::period2seconds( $cmppp_woo_pricing_single['number'] . $cmppp_woo_pricing_single['unit'] );
      $submitLabel      = sprintf(
	      Labels::getLocalized( 'eddpay_subscription_checkout_button_period_for_amount' ),
	      $periodText, (float) $cmppp_woo_pricing_single['price']
      );
      $submitLabelGuest = sprintf(
	      Labels::getLocalized( 'eddpay_subscription_checkout_button_period_for_amount_guest' ),
	      $periodText, (float) $cmppp_woo_pricing_single['price']
      );
      ?>

    		<div class="cmppp-edd-payment-buttons">
          <input type="hidden" name="period" value="<?php echo $periodInSec; ?>" />
          <input type="hidden" name="product_id" value="<?php echo $cmppp_woo_pricing_single['product_id']; ?>" />
          <input type="hidden" name="priceIndex" value="0" />
          <input type="hidden" name="number" value="<?php echo $cmppp_woo_pricing_single['number']; ?>" />
          <input type="hidden" name="unit" value="<?php echo $cmppp_woo_pricing_single['unit']; ?>" />
          <input type="hidden" name="price" value="<?php echo $cmppp_woo_pricing_single['price']; ?>" />
          <input type="hidden" name="period" value="<?php echo $cmppp_woo_pricing_single['number'] . $cmppp_woo_pricing_single['unit']; ?>" />
          <input type="hidden" name="seconds" value="<?php echo $periodInSec; ?>" />
          <input type="hidden" name="provider" value="<?php echo esc_attr( $provider ); ?>" />
          <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>" />
          <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
          <input type="hidden" name="action" value="<?php echo esc_attr($ajaxAction); ?>" />
          <input type="hidden" name="callbackUrl" value="<?php echo esc_attr( $_SERVER[ 'REQUEST_URI' ] ); ?>" />
          <input type="submit" value="<?php echo esc_attr( $submitLabel ); ?>" />
        </div>
    </form>
<?php endif; ?>
