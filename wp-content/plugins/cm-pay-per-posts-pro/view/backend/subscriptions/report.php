<?php
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\controller\SubscriptionsController;
use com\cminds\payperposts\model\Micropayments;
echo $addForm;
?>
<?php if ($pageUrl != preg_replace('/\&p=[0-9]+/', '', $currentUrl)): ?>
	<p><a href="<?php echo esc_attr($pageUrl); ?>">&laquo; Back to full report</a></p>
<?php endif; ?>
<form method="GET" class="cmppp-report-filter">
	<input type="hidden" name="page" value="<?php echo $pageMenuSlug; ?>" />
	<table class="wp-list-table widefat fixed cmppp-report-table">
        <?php if(!isset($_GET['status']) || $_GET['status'] == ''): ?>
            <select name="actions" id="bulk_actions">
                <option value="">Bulk Actions</option>
                <option value="bulk-remove">Remove</option>
                <option value="bulk-deactivate">Deactivate</option>
            </select>
            <a href="<?php echo esc_attr(add_query_arg(urlencode_deep(array(
                'nonce' => $nonceAction,
            )), $currentUrl)); ?>" id="bulk_remove" >Apply</a>
            <div class="cmppp-export-subscriptions__wrap">
                <label for="cmppp-page-from">From page:</label>
                <input type="number" name="cmppp-page-from" class="cmppp-page-from" value="1">
                <label for="cmppp-page-to">To page:</label>
                <input type="number" name="cmppp-page-to" class="cmppp-page-to" value="50">
                <a href="<?php echo esc_attr( $downloadCSVUrl ); ?>" class="cmppp-export-subscriptions"><span class="dashicons dashicons-download"></span>Export Subscriptions</a>
            </div>
           <?php endif; ?>
        <thead>
			<tr>
                <th  class="cmppp-select-all-bulk">
                    <input type="checkbox" class="bulk_select_all cmppp-select-all-bulk-input" name="checkbox_all" value="select_all">
                </th>
				<th>Pricing group</th>
				<th>Post</th>
				<th>User</th>
				<th>Start</th>
				<th>End</th>
				<th>Duration</th>
				<th>Amount paid</th>
				<th>Payment plugin</th>
				<th>Status
					<select name="status" id="status_select">
						<option value="">any</option>
						<option value="active"<?php selected($filter['status'], 'active'); ?>><?php echo Labels::getLocalized('status_active'); ?></option>
						<option value="past"<?php selected($filter['status'], 'past'); ?>><?php echo Labels::getLocalized('status_past'); ?></option>
						<option value="refund"<?php selected($filter['status'], 'refund'); ?>><?php echo Labels::getLocalized('status_refund'); ?></option>
					</select>
				</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody><?php if (!empty($data)) foreach ($data as $row): ?>
			<tr>
                <td>
                    <input type="checkbox" class="bulk_checkbox" name="checkbox_<?php echo $row['user_name']; ?>" value="<?php echo $row['meta_id']; ?>">
                </td>
				<td>
					<?php echo $row['pricing_group_name']; ?>
					<a href="<?php echo esc_attr(add_query_arg('pricing_group', urlencode($row['pricing_group_index']), $pagination['firstPageUrl'])); ?>" class="cmppp-report-row-filter">Filter</a>
				</td>
				<td>
					<a href="<?php echo esc_attr(admin_url('post.php?action=edit&post=' . $row['post_id'])); ?>"><?php echo $row['post_title']; ?></a>
					<a href="<?php echo esc_attr(add_query_arg('post_id', urlencode($row['post_id']), $pagination['firstPageUrl'])); ?>" class="cmppp-report-row-filter">Filter</a>
				</td>
				<td>
					<a href="<?php echo esc_attr(admin_url('profile.php?user_id=' . $row['user_id'])); ?>"><?php echo $row['user_name']; ?></a>
					<a href="<?php echo esc_attr(add_query_arg('user_id', urlencode($row['user_id']), $pagination['firstPageUrl'])); ?>" class="cmppp-report-row-filter">Filter</a>
				</td>
				<td>
					<?php
					$gmt_offset = get_option('gmt_offset', '');
					if($gmt_offset != '' && !is_float($gmt_offset) && $gmt_offset != 0) {
                        $hours_sec   = (int) $gmt_offset * 60 * 60;
                        $minutes_sec = ( $gmt_offset - floor( $gmt_offset ) ) * 60 * 60;
                        $row['start'] = $row['start'] + ($hours_sec + $minutes_sec);

					}
					echo date(get_option('date_format').' '.get_option('time_format'), $row['start']);
					?>
				</td>
				<td>
					<?php
					if($gmt_offset != '' && !is_float($gmt_offset) && $gmt_offset != 0) {
                        $hours_sec   = (int) $gmt_offset * 60 * 60;
                        $minutes_sec = ( $gmt_offset - floor( $gmt_offset ) ) * 60 * 60;
                        $row['end'] = $row['end'] + ($hours_sec + $minutes_sec);
					}
					echo date(get_option('date_format').' '.get_option('time_format'), $row['end']);
					?>
				</td>
				<td><?php echo Micropayments::seconds2period($row['duration']); ?></td>
				<td><?php echo apply_filters('cmppp_format_amount_payed', $row['amount'], $row['plugin']); ?></td>
				<td><?php echo (empty($row['plugin']) ? Micropayments::PAYMENT_PLUGIN_NAME : $row['plugin']); ?></td>
				<td><?php $offset_in_sec = $gmt_offset * 3600;
                    $status = (($row['end'] <= (time() + $offset_in_sec)) ? (empty($row['refund']) ? 'past' : 'refund') : 'active');
					$statusLabel = Labels::getLocalized('status_'. $status);
					if ('refund' == $status) {
						if ('other' == $row['refund']['key']) {
							$reason = $row['refund']['comment'];
						} else $reason = $row['refund']['label'];
						printf('<a href="#" class="cmppp-show-refund-reason" title="Show refund reason">%s</a><div class="cmppp-refund-reason">%s</div>',
							$statusLabel, esc_html($reason));
					}
					else echo $statusLabel;
				?></td>
				<td>
                    <ul class="cmppp-actions">
                        <li>
                            <a href="<?php echo esc_attr(add_query_arg(urlencode_deep(array(
                                'action' => 'remove',
                                'id' => $row['meta_id'],
                                'nonce' => $nonceAction,
                            )), $currentUrl)); ?>" data-confirm="<?php
                            echo htmlspecialchars('Do you really want to remove this subscription? It won\'t be possible to undo this action.');
                            ?>">Remove</a>
                        </li>
                        <?php if ($row['end'] > (time() + $offset_in_sec)): ?>
                            <li>
                                <a href="<?php echo esc_attr(add_query_arg(urlencode_deep(array(
                                    'action' => 'deactivate',
                                    'id' => $row['meta_id'],
                                    'nonce' => $nonceAction,
                                )), $currentUrl)); ?>" data-confirm="<?php
                                echo htmlspecialchars('Do you really want to deactivate this subscription? It won\'t be possible to undo this action.');
                                ?>">Deactivate</a>
                            </li>
                        <?php endif; ?>
				    </ul>
                </td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>
</form>
<?php if ($pagination['lastPage'] > 1): ?>
	<ul class="cmppp-pagination"><?php for ($page=1; $page<=$pagination['lastPage']; $page++): ?>
		<li<?php if ($page == $pagination['page']) echo ' class="current-page"';
			?>><a href="<?php echo esc_attr(add_query_arg('p', urlencode($page), $pagination['firstPageUrl'])); ?>"><?php echo $page; ?></a></li>
	<?php endfor; ?></ul>
<?php endif; ?>
<?php if (empty($data)): ?>
	<p>No data.</p>
<?php endif; ?>
