<?php
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\model\Labels;
if(!function_exists('sseconds2periodlocalized')) {
	function sseconds2periodlocalized($str) {
		$str = trim($str);
		$str_arr = explode(" ", $str);
		return $str_arr[0].' '.Labels::getLocalized($str_arr[1]);
	}
}
?>
<div class="cmppp-shortcode-subscriptions">
	<table>
		<caption><?php echo Labels::getLocalized('shortcode_subscriptions_table_caption'); ?></caption>
		<thead>
			<tr>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_post'); ?></th>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_start'); ?></th>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_end'); ?></th>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_duration'); ?></th>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_amount'); ?></th>
				<th><?php echo Labels::getLocalized('shortcode_subscriptions_status'); ?></th>
			</tr>
		</thead>
		<tbody><?php foreach ($data as $row): ?>
			<?php $status = (($row['end'] <= time()) ? (empty($row['refund']) ? 'past' : 'refund') : 'active'); ?>
			<tr>
				<td><a href="<?php echo esc_attr(get_permalink($row['post_id'])); ?>"><?php echo $row['post_title']; ?></a></td>
				<td>
					<?php
					$gmt_offset = get_option('gmt_offset', '');
					if($gmt_offset != '') {
						if($gmt_offset > 0) {
							$hours_sec   = (int) $gmt_offset * 60 * 60;
							$minutes_sec = ( $gmt_offset - floor( $gmt_offset ) ) * 60 * 60;
							$row['start'] = $row['start'] + ($hours_sec + $minutes_sec);
						} else {
							$row['start'] = $row['start'] - ($hours_sec + $minutes_sec);
						}
					}
					echo date(get_option('date_format').' '.get_option('time_format'), $row['start']);
					?>
				</td>
				<td>
					<?php
					if($gmt_offset != '') {
						if($gmt_offset > 0) {
							$hours_sec   = (int) $gmt_offset * 60 * 60;
							$minutes_sec = ( $gmt_offset - floor( $gmt_offset ) ) * 60 * 60;
							$row['end'] = $row['end'] + ($hours_sec + $minutes_sec);
						} else {
							$row['end'] = $row['end'] - ($hours_sec + $minutes_sec);
						}
					}
					echo date(get_option('date_format').' '.get_option('time_format'), $row['end']);
					?>
				</td>
				<td><?php echo sseconds2periodlocalized(Micropayments::seconds2period($row['duration'])); ?></td>
				<td><?php echo apply_filters('cmppp_format_amount_payed', $row['amount'], $row['plugin']); ?></td>
				<td><?php echo Labels::getLocalized('status_'. $status); ?></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>
	<?php if (empty($data)): ?>
		<p><?php echo Labels::getLocalized('shortcode_subscriptions_table_no'); ?></p>
	<?php endif; ?>
</div>