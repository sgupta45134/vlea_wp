<?php

use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Settings;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
?>

<?php
$shortcode_group_paybox = $shortcode_group_paybox ?? false;
$group_name             = $group_name ?? "";
?>


<?php /*  for group payment */ ?>
<?php if ( isset( $groupEnabled ) && $groupEnabled && ! empty( $form ) ): ?>
  <div class="cmppp-paybox cmppp-paybox-groups-list-item">

    <h3><?php echo $group_name; ?></h3>

    <p style="margin-bottom:1em;">
		<?php if ( $shortcode_group_paybox ): ?>

		<?php else: ?>

			<?php if ( is_user_logged_in() ): ?>
				<?php echo Labels::getLocalized( 'eddpay_activate_subscription_text' ); ?>
			<?php else: ?>
				<?php echo Labels::getLocalized( 'eddpay_activate_subscription_text_guest' ); ?>
			<?php endif; ?>

		<?php endif; ?>
    </p>

	  <?php echo $form; ?>
  </div>
<?php endif; ?>
