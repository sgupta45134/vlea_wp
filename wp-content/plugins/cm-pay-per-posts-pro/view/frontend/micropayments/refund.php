<?php


use com\cminds\payperposts\model\Labels;

$itemTemplate = '<li><label><input type="radio" name="reason" value="%s" /> <span>%s</span></label></li>';

?>
<div class="cmppp-refund-btn-wrapper">

	<a href="#" class="cmppp-refund-btn"><?php echo Labels::getLocalized('refund_btn'); ?></a>
	
	<form action="<?php echo $ajaxUrl; ?>" class="cmppp-refund-box">
		<a href="#" class="cmppp-refund-close" data-cmppp-action="close">&times;</a>
		<h3><?php echo Labels::getLocalized('refund_box_header'); ?></h3>
		<p><?php echo sprintf(Labels::getLocalized('refund_box_text'), $minutesForRefund); ?></p>
		<ul>
			<?php foreach ($reasons as $reason):
				printf($itemTemplate, esc_attr($reason['key']), $reason['value']);
			endforeach;
			printf($itemTemplate, 'other', Labels::getLocalized('refund_box_reason_other'));
			?>
		</ul>
		<textarea name="reason_text" placeholder="<?php echo esc_attr(Labels::getLocalized('refund_other_reason_placeholder')); ?>"></textarea>
		<input type="hidden" name="action" value="cmppp_refund" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>" />
		<input type="hidden" name="postId" value="<?php echo esc_attr($postId); ?>" />
		<div class="cmppp-form-summary">
			<input type="submit" value="<?php echo esc_attr(sprintf(Labels::getLocalized('refund_box_submit_btn'), $points)); ?>" />
			<input type="button" data-cmppp-action="close" value="<?php echo esc_attr(Labels::getLocalized('refund_box_cancel_btn')); ?>" />
		</div>
	</form>
	
</div>