<?php
use com\cminds\payperposts\model;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\helper\HtmlHelper;
$options = '';
$priceGroups = $priceGroups ?? [];
$currentPricingGroups = $currentPricingGroups ?? [];
?>
<!-- EDD Pricing Meta Box -->
<div>
	<label>Pricing groups:</label>
  <div class="cmppp-pricing-group">
    <?php foreach ( $priceGroups as $groupIndex => $group ): ?>
        <label>
          <input type="checkbox" name="cmppp-edd-price-group[]"
                 value="<?php echo $groupIndex; ?>"
                <?php if ( in_array( $groupIndex, $currentPricingGroups ) ) {
                  echo "checked";
                } ?>
          >
          <span><?php echo $group['name']; ?></span>
        </label><br>
    <?php endforeach; ?>
  </div>
  <input type="hidden" name="cmppp-edd-price-group[]" value="">

	<input type="hidden" name="cmppp-post-edd-nonce" value="<?php echo wp_create_nonce('cmppp-post-edd-nonce'); ?>" >
</div>
<div class="cmppp-edd-pricing-allow-single" style="margin-top:10px">
	Allow buying as individual:
	<input type="checkbox" name="cmppp_edd_pricing_single_enabled" value="1" <?php echo (isset($currentPricingSingle['allow']) && $currentPricingSingle['allow'] == '1')?'checked="checked"':''; ?> />
	<div class="cmmp-single-meta-price" style="margin-top:10px;">
		<label style="vertical-align:initial;">
			<input type="number" name="cmppp_edd_pricing_single_number" placeholder="Period" min="0" value="<?php echo (isset($currentPricingSingle['number']) && $currentPricingSingle['number'] != '')?$currentPricingSingle['number']:0; ?>" />
			<select name="cmppp_edd_pricing_single_unit" style="vertical-align:initial;">
				<option value="min" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'min')?'selected="selected"':''; ?>>minutes</option>
				<option value="h" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'h')?'selected="selected"':''; ?>>hours</option>
				<option value="d" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'd')?'selected="selected"':''; ?>>days</option>
				<option value="w" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'w')?'selected="selected"':''; ?>>weeks</option>
				<option value="m" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'm')?'selected="selected"':''; ?>>months</option>
				<option value="y" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'y')?'selected="selected"':''; ?>>years</option>
				<option value="l" <?php echo (isset($currentPricingSingle['unit']) && $currentPricingSingle['unit'] == 'l')?'selected="selected"':''; ?>>lifetime</option>
			</select>
		</label>
		<label style="vertical-align:initial;">
			for <input type="number" name="cmppp_edd_pricing_single_price" placeholder="Price" step="0.01" min="0" value="<?php echo (isset($currentPricingSingle['price']) && $currentPricingSingle['price'] != '')?$currentPricingSingle['price']:0; ?>" />
			<?php echo (function_exists('edd_get_currency'))?edd_get_currency():''; ?>
		</label>
	</div>
	<p style="margin:5px 0 0 0;">* Sets period one (1) if you choose <strong>lifetime</strong> subscription.</p>
</div>
