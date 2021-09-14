<?php

use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Settings;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
?>

<?php

$singleEnabled          = $singleEnabled ?? 0;
$singleform             = $singleform ?? '';
$categoryform           = $categoryform ?? '';
$shortcode_group_paybox = $shortcode_group_paybox ?? false;
$group_name             = $group_name ?? "";

?>


<?php /*  for group payment */ ?>
<?php if ( isset( $groupEnabled ) && $groupEnabled && ! empty( $form ) ): ?>
  <div class="cmppp-paybox cmppp-paybox-edd">

	  <?php
	  // if it's form for shortcode group paybox
	  if ( $shortcode_group_paybox ) {
		  $label = Labels::getLocalized( 'eddpay_activate_subscription_header_shortcode_group_paybox' );
	  } else {
		  $label = Labels::getLocalized( 'eddpay_activate_subscription_header' );
	  }
	  ?>

    <h3><?php echo $label ?></h3>

    <p style="margin-bottom:1em;">
		<?php if ( $shortcode_group_paybox ): ?>

			<?php echo sprintf( Labels::getLocalized( 'eddpay_activate_subscription_text_shortcode_group_paybox' ), $group_name ); ?>

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

<?php /*  for category payment */ ?>
<?php if ( ! empty( $categoryform ) ): ?>

  <div class="cmppp-paybox cmppp-paybox-edd">
    <h3>
		<?php echo Labels::getLocalized( 'eddpay_activate_subscription_header_for_category' ); ?>
    </h3>

	  <?php if ( is_user_logged_in() ): ?>
        <p style="margin-bottom:1em;"><?php echo Labels::getLocalized( 'eddpay_activate_subscription_text' ); ?></p>
	  <?php else: ?>
        <p style="margin-bottom:1em;"><?php echo Labels::getLocalized( 'eddpay_activate_subscription_text_guest' ); ?></p>
	  <?php endif; ?>

	  <?php echo $categoryform; ?>
  </div>
<?php endif; ?>


<?php /*  for single post payment */ ?>
<?php
if ( $singleEnabled && ! empty( $singleform ) ) {
	?>
  <div class="cmppp-paybox cmppp-paybox-edd-single">
    <h3><?php echo Labels::getLocalized( 'eddpay_activate_subscription_for_single' ); ?></h3>
	  <?php
	  if ( is_user_logged_in() ) {
		  ?>
        <p><?php echo Labels::getLocalized( 'eddpay_activate_subscription_text_single' ); ?></p>
		  <?php
	  } else {
		  ?>
        <p><?php echo Labels::getLocalized( 'eddpay_activate_subscription_text_guest' ); ?></p>
		  <?php
	  }
	  echo $singleform;
	  ?>
  </div>
	<?php
}
if ( Settings::getOption( Settings::OPTION_LOGIN_FORM_ENABLE ) && ! is_user_logged_in() ) {
	echo "<div class='cmppp-paybox'>";
	if ( is_plugin_active( 'cm-registration-pro/cm-registration-pro.php' ) ) {
		echo do_shortcode( '[cmreg-login-form]' );
	} else {
		$args = array(
			'echo'           => true,
			'redirect'       => get_permalink( get_the_ID() ),
			'remember'       => true,
			'value_remember' => false,
		);
		wp_login_form( $args );
	}
	echo "</div>";
}
?>
