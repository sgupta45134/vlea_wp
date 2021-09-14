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
<div class="cmppp-shortcode-author-subscriptions">
	<form method="GET" class="cmppp-author-subscriptions-filter">
		<table>
			<caption><?php echo Labels::getLocalized('shortcode_author_subscriptions_table_caption'); ?></caption>
			<thead>
				<tr>
					<th class="col1"><?php echo Labels::getLocalized('shortcode_subscriptions_post'); ?></th>
					<th class="col2"><?php echo Labels::getLocalized('shortcode_author_subscriptions_user_name'); ?></th>
					<th class="col3"><?php echo Labels::getLocalized('shortcode_author_subscriptions_user_email'); ?></th>
					<th class="col4"><?php echo Labels::getLocalized('shortcode_subscriptions_start'); ?></th>
					<th class="col5"><?php echo Labels::getLocalized('shortcode_subscriptions_end'); ?></th>
					<th class="col6"><?php echo Labels::getLocalized('shortcode_subscriptions_duration'); ?></th>
					<th class="col7"><?php echo Labels::getLocalized('shortcode_subscriptions_amount'); ?></th>
					<th class="col8">
						<?php echo Labels::getLocalized('shortcode_subscriptions_status'); ?>
						<select name="status" onchange="this.form.submit()">
							<option value="">any</option>
							<option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected="selected"' : ''; ?>><?php echo Labels::getLocalized('status_active'); ?></option>
							<option value="past" <?php echo (isset($_GET['status']) && $_GET['status'] == 'past') ? 'selected="selected"' : ''; ?>><?php echo Labels::getLocalized('status_past'); ?></option>
							<option value="refund" <?php echo (isset($_GET['status']) && $_GET['status'] == 'refund') ? 'selected="selected"' : ''; ?>><?php echo Labels::getLocalized('status_refund'); ?></option>
						</select>
					</th>
				</tr>
			</thead>
			<tbody><?php foreach ($data as $row): ?>
				<?php $status = (($row['end'] <= time()) ? (empty($row['refund']) ? 'past' : 'refund') : 'active'); ?>
				<tr>
					<td class="col1"><a href="<?php echo esc_attr(get_permalink($row['post_id'])); ?>"><?php echo $row['post_title']; ?></a></td>
					<td class="col2"><?php echo $row['user_name']; ?></td>
					<td class="col3"><?php echo $row['user_email']; ?></td>
					<td class="col4">
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
					<td class="col5">
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
					<td class="col6"><?php echo sseconds2periodlocalized(Micropayments::seconds2period($row['duration'])); ?></td>
					<td class="col7"><?php echo apply_filters('cmppp_format_amount_payed', $row['amount'], $row['plugin']); ?></td>
					<td class="col8"><?php echo Labels::getLocalized('status_'. $status); ?></td>
				</tr>
			<?php endforeach; ?></tbody>
		</table>
	</form>
	<?php if (empty($data)): ?>
		<p><?php echo Labels::getLocalized('shortcode_subscriptions_table_no'); ?></p>
	<?php endif; ?>
</div>
<style>
.cmppp-shortcode-author-subscriptions th {text-align: left !important; vertical-align:top !important; }
</style>