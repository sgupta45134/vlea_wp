<?php

use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Labels;

$downloads  = $downloads ?? [];
$provider   = $provider ?? "";
$postId     = $postId ?? 0;
$nonce      = $nonce ?? "";
$ajaxAction = $ajaxAction ?? "";
?>


<?php if ( ! empty( $downloads ) ): ?>

	<?php foreach ( $downloads as $download_item ): ?>
    <form class="cmppp-edd-form for-category" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
      <input type="hidden" name="provider" value="<?php echo esc_attr( $provider ); ?>"/>
      <input type="hidden" name="postId" value="<?php echo esc_attr( $postId ); ?>"/>
      <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
      <input type="hidden" name="action" value="<?php echo esc_attr( $ajaxAction ); ?>"/>
      <input type="hidden" name="callbackUrl" value="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>"/>
      <input type="hidden" name="download_id" value="<?php echo $download_item['download_id']; ?>">

      <div class="cmppp-edd-paybox-costs">
		  <?php $submitLabel = sprintf( Labels::getLocalized( 'category_activate_subscription_button' ), $download_item['title'], $download_item['price'] ); ?>
        <input type="submit" value="<?php echo esc_attr( $submitLabel ); ?>">
      </div>
      <br>
    </form>
	<?php endforeach; ?>

<?php endif; ?>



