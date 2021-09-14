<?php
namespace com\cminds\payperposts\helper;

use com\cminds\payperposts\model\Settings;

class SettingsMicropaymentsBulk {
	
	static function render($name, $option) {
		$output  = '';
		$output .= '<div class="cmppp-bulk-options-container" data-name="'. esc_attr($name) .'">';
		$output .= '<input type="number" name="cmppp_pricing_bulk_period" id="cmppp_pricing_bulk_period" placeholder="Period" min="0" value="0" style="width:100px;">';
		$output .= '<select name="cmppp_pricing_bulk_unit" id="cmppp_pricing_bulk_unit" style="vertical-align:initial;">';
		$output .= '<option value="min">minutes</option>';
		$output .= '<option value="h">hours</option>';
		$output .= '<option value="d">days</option>';
		$output .= '<option value="w">weeks</option>';
		$output .= '<option value="m">months</option>';
		$output .= '<option value="y">years</option>';
		$output .= '<option value="l">lifetime</option>';
		$output .= '</select>';
		$output .= ' for ';
		$output .= '<input type="number" name="cmppp_pricing_bulk_price" id="cmppp_pricing_bulk_price" placeholder="Price" step="0.01" min="0" value="0" style="width:100px;">';
		$output .= '</div>';
		$output .= '<div>';
		$output .= '<input type="button" class="button" name="cmppp_pricing_bulk_submit" id="cmppp_pricing_bulk_submit" value="Bulk" style="margin-top:10px; cursor:pointer;" />';
		$output .= '</div>';
		return $output;
	}

}
