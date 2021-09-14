<?php

use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\PeriodLabels;
use com\cminds\payperposts\model\Micropayments;

if ( $groupEnabled ) {
	$groups = $groups ?? [];
	?>
  <div class="cmppp-paybox cmppp-paybox-micropayments">
    <h3><?php echo Labels::getLocalized( 'activate_subscription_header' ); ?></h3>
    <p><?php echo Labels::getLocalized( 'activate_subscription_text' ); ?></p>

	  <?php foreach ($groups as $index => $group): ?>
      <?php $plans = $group['prices']; ?>
      <form class="cmppp-paybox-form cmppp-micropayments-form">

        <?php if ( count( $plans ) == 1 ):
          reset( $plans );
          $priceIndex = key( $plans );
          ?>
          <input type="hidden" name="priceIndex" value="<?php echo $priceIndex; ?>" />

        <?php else: ?>

              <p><?php foreach ( $plans as $priceIndex => $price ): ?>
                  <label>
                    <input type="radio" name="priceIndex" value="<?php echo $priceIndex; ?>">
	                  <?php
	                  $periodName = PeriodLabels::getLocalizedArray( $price );
	                  printf( Labels::getLocalized( 'period_for_points' ), $periodName, $price['price'] );
	                  ?>
                  </label>
            <?php endforeach; ?></p>
        <?php endif; ?>

        <p>
          <input type="hidden" name="action" value="cmppp_subscription_activate"/>
          <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>"/>
          <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
          <input type="hidden" name="priceGroupIndex" value="<?php echo esc_attr( $group['groupIndex'] ); ?>"/>

          <?php
          if ( count( $plans ) > 1 ) {
            $label = Labels::getLocalized( 'paybox_pay_btn' );
          } else {
            $price      = reset( $plans );
	          $periodName = PeriodLabels::getLocalizedArray( $price );
            $label      = sprintf( Labels::getLocalized( 'paybox_pay_single_btn' ), $price['price'], $periodName );
          }
          ?>
          
          <input type="submit" value="<?php echo esc_attr( $label ); ?>"/>
        </p>
      </form>

	    <?php if ($index !== count($groups)-1): ?>
        <hr>
	    <?php endif; ?>
	  <?php endforeach; ?>
  </div>
	<?php
}

if ( $singleEnabled == '1' ) {

    $periodText  = PeriodLabels::getLocalizedArray($cmppp_mp_pricing_single );
    $periodInSec = Micropayments::period2seconds( $cmppp_mp_pricing_single['number'] . $cmppp_mp_pricing_single['unit'] );
    $submitLabel = sprintf( Labels::getLocalized( 'paybox_pay_single_btn' ), $cmppp_mp_pricing_single['price'], $periodText );
	?>

  <?php if ($periodInSec !== 0): ?>
      <div class="cmppp-paybox cmppp-paybox-micropayments-single">
        <form class="cmppp-paybox-form cmppp-micropayments-form">
          <h3><?php echo Labels::getLocalized( 'activate_subscription_header' ); ?></h3>
          <p><?php echo Labels::getLocalized( 'activate_subscription_text' ); ?></p>

          <div class="cmppp-mp-payment-buttons">
            <input type="hidden" name="period" value="<?php echo $periodInSec; ?>"/>
            <input type="hidden" name="product_id" value="<?php echo $cmppp_mp_pricing_single['product_id']; ?>"/>
            <input type="hidden" name="priceIndex" value="0"/>
            <input type="hidden" name="number" value="<?php echo $cmppp_mp_pricing_single['number']; ?>"/>
            <input type="hidden" name="unit" value="<?php echo $cmppp_mp_pricing_single['unit']; ?>"/>
            <input type="hidden" name="price" value="<?php echo $cmppp_mp_pricing_single['price']; ?>"/>
            <input type="hidden" name="period" value="<?php echo $cmppp_mp_pricing_single['number'] . $cmppp_mp_pricing_single['unit']; ?>"/>
            <input type="hidden" name="seconds" value="<?php echo $periodInSec; ?>"/>
            <input type="hidden" name="provider" value="<?php echo esc_attr( $provider ?? 0 ); ?>"/>
            <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>"/>
            <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce_single ); ?>"/>
            <input type="hidden" name="action" value="cmppp_subscription_activate_single"/>
            <input type="hidden" name="callbackUrl" value="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>"/>
            <input type="submit" value="<?php echo esc_attr( $submitLabel ); ?>"/>
          </div>
        </form>
      </div>
  <?php endif; ?>

	<?php
}

if ( $authorEnabled == '1' ) {
	if ( $authorDonationEnabled == '1' && $pricingAuthorPointsMode == 'free' ) {
		?>
      <div class="cmppp-paybox cmppp-paybox-micropayments-author-donation">
        <form class="cmppp-paybox-form cmppp-micropayments-form">
          <h3><?php echo Labels::getLocalized( 'activate_subscription_header' ); ?></h3>
          <p><?php echo Labels::getLocalized( 'activate_subscription_text' ); ?></p>
			<?php
			$author_id = get_post_meta( $postId, 'cm_user_submitted_post', true );
			if ( $author_id == '' || $author_id == '0' ) {
				$post      = get_post( $postId );
				$author_id = $post->post_author;
			}

			$periodInSec     = Micropayments::period2seconds( $cmppp_mp_pricing_author['number'] . $cmppp_mp_pricing_author['unit'] );
			$submitLabel     = 'Donate';
			$skipSubmitLabel = 'Skip';
			?>
          <div class="cmppp-mp-payment-buttons" style="margin-top:15px;">
            <input type="hidden" name="author_id" value="<?php echo $author_id; ?>"/>
            <input type="hidden" name="period" value="<?php echo $periodInSec; ?>"/>
            <input type="hidden" name="product_id" value="<?php echo $cmppp_mp_pricing_author['product_id']; ?>"/>
            <input type="hidden" name="priceIndex" value="0"/>
            <input type="hidden" name="number" value="<?php echo $cmppp_mp_pricing_author['number']; ?>"/>
            <input type="hidden" name="unit" value="<?php echo $cmppp_mp_pricing_author['unit']; ?>"/>
            <input type="hidden" name="donationaction" id="donationaction" value="1"/>
            <input type="number" id="priceNumberField" name="price" value="<?php echo $cmppp_mp_pricing_author['price']; ?>" min="1" step="1" style="height:40px;"/>
            <input type="hidden" name="period" value="<?php echo $cmppp_mp_pricing_author['number'] . $cmppp_mp_pricing_author['unit']; ?>"/>
            <input type="hidden" name="seconds" value="<?php echo $periodInSec; ?>"/>
            <input type="hidden" name="provider" value="<?php echo esc_attr( $provider ); ?>"/>
            <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>"/>
            <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce_author_donation ); ?>"/>
            <input type="hidden" name="action" value="cmppp_subscription_activate_author_donation"/>
            <input type="hidden" name="callbackUrl" value="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>"/>&nbsp;&nbsp;&nbsp;
            <input type="submit" value="<?php echo esc_attr( $submitLabel ); ?>" name="donateSubmitButton" class="donateSubmitButton" style="margin-top:0px !important; padding:0.8em 2em;"/>
            &nbsp;or&nbsp;
            <input type="submit" value="<?php echo esc_attr( $skipSubmitLabel ); ?>" name="skipSubmitButton" class="skipSubmitButton" style="margin-top:0px !important; padding:0.8em 2em;"/></div>
        </form>
        <script>
            jQuery('body').on('click', '.donateSubmitButton', function () {
                jQuery("#priceNumberField").prop('min', 1);
                jQuery("#donationaction").val('1');
            });
            jQuery('body').on('click', '.skipSubmitButton', function () {
                jQuery("#priceNumberField").prop('min', 0);
                jQuery("#priceNumberField").val('0');
                jQuery("#donationaction").val('0');
            });
        </script>
      </div>
		<?php
	} else {
		?>
      <div class="cmppp-paybox cmppp-paybox-micropayments-author">
        <form class="cmppp-paybox-form cmppp-micropayments-form">
          <h3><?php echo Labels::getLocalized( 'activate_subscription_header' ); ?></h3>
          <p><?php echo Labels::getLocalized( 'activate_subscription_text' ); ?></p>
			<?php
			$author_id = get_post_meta( $postId, 'cm_user_submitted_post', true );

			if ( $author_id == '' || $author_id == '0' ) {
				$post      = get_post( $postId );
				$author_id = $post->post_author;
			}

      $periodText  = PeriodLabels::getLocalizedArray( $cmppp_mp_pricing_author );
      $periodInSec = Micropayments::period2seconds( $cmppp_mp_pricing_author['number'] . $cmppp_mp_pricing_author['unit'] );
      $submitLabel = sprintf( Labels::getLocalized( 'paybox_pay_single_btn' ), $cmppp_mp_pricing_author['price'], $periodText );
			?>
          <div class="cmppp-mp-payment-buttons">
            <input type="hidden" name="author_id"
                   value="<?php echo $author_id; ?>"/>
            <input type="hidden"
                   name="period"
                   value="<?php echo $periodInSec; ?>"/>
            <input
                type="hidden" name="product_id" value="<?php echo $cmppp_mp_pricing_author['product_id']; ?>"/>
            <input
                type="hidden" name="priceIndex" value="0"/>
            <input type="hidden" name="number"
                   value="<?php echo $cmppp_mp_pricing_author['number']; ?>"/>
            <input
                type="hidden" name="unit" value="<?php echo $cmppp_mp_pricing_author['unit']; ?>"/>
            <input type="hidden"
                   name="price"
                   value="<?php echo $cmppp_mp_pricing_author['price']; ?>"/>
            <input
                type="hidden" name="period"
                value="<?php echo $cmppp_mp_pricing_author['number'] . $cmppp_mp_pricing_author['unit']; ?>"/>
            <input
                type="hidden" name="seconds" value="<?php echo $periodInSec; ?>"/>
            <input type="hidden" name="provider"
                   value="<?php echo esc_attr( $provider ); ?>"/>
            <input
                type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>"/>
            <input type="hidden"
                   name="nonce"
                   value="<?php echo esc_attr( $nonce_author ); ?>"/>
            <input
                type="hidden" name="action" value="cmppp_subscription_activate_author"/>
            <input type="hidden"
                   name="callbackUrl"
                   value="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>"/>
            <input
                type="submit" value="<?php echo esc_attr( $submitLabel ); ?>"/></div>
        </form>
      </div>
		<?php
	}
}
?>
