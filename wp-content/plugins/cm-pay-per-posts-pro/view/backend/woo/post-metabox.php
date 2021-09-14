<?php
use com\cminds\payperposts\model;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\helper\HtmlHelper;
?>
<!-- Woo Pricing Meta Box -->
<div class="cmppp-woo-pricing-groups">
	Pricing groups:
	<?php echo HtmlHelper::renderCheckboxGroup($fieldName, $pricingGroupsList, $pricingGroupIndex); ?>
</div>
<div class="cmppp-woo-pricing-allow-single" style="margin-top:10px">
	Allow buying as individual:
	<input type="checkbox" name="cmppp_woo_pricing_single_enabled" value="1" <?php echo (isset($pricingSingleIndex['allow']) && $pricingSingleIndex['allow'] == '1')?'checked="checked"':''; ?> />
	<div class="cmmp-single-meta-price" style="margin-top:10px;">
		<label style="vertical-align:initial;">
			<input type="number" name="cmppp_woo_pricing_single_number" placeholder="Period" min="0" value="<?php echo (isset($pricingSingleIndex['number']) && $pricingSingleIndex['number'] != '')?$pricingSingleIndex['number']:0; ?>" />
			<select name="cmppp_woo_pricing_single_unit" style="vertical-align:initial;">
				<option value="min" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'min')?'selected="selected"':''; ?>>minutes</option>
				<option value="h" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'h')?'selected="selected"':''; ?>>hours</option>
				<option value="d" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'd')?'selected="selected"':''; ?>>days</option>
				<option value="w" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'w')?'selected="selected"':''; ?>>weeks</option>
				<option value="m" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'm')?'selected="selected"':''; ?>>months</option>
				<option value="y" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'y')?'selected="selected"':''; ?>>years</option>
				<option value="l" <?php echo (isset($pricingSingleIndex['unit']) && $pricingSingleIndex['unit'] == 'l')?'selected="selected"':''; ?>>lifetime</option>
			</select>
		</label>
		<label style="vertical-align:initial;">
			for <input type="number" name="cmppp_woo_pricing_single_price" placeholder="Price" step="0.01" min="0" value="<?php echo (isset($pricingSingleIndex['price']) && $pricingSingleIndex['price'] != '')?$pricingSingleIndex['price']:0; ?>" />
			<?php echo model\WooCommerceProduct::getCurrency(); ?>
		</label>
	</div>
	<p style="margin:5px 0 0 0;">* Sets period one (1) if you choose <strong>lifetime</strong> subscription.</p>
</div>
