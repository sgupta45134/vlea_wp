<?php
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Micropayments;
?>
<div class="cmppp-paybox cmppp-paybox-micropayments">
	<h3><?php echo Labels::getLocalized('activate_subscription_header_guest'); ?></h3>
	<p><?php echo do_shortcode(Labels::getLocalized('activate_subscription_text_guest')); ?></p>
</div>