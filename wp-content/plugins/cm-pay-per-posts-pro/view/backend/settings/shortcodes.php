<?php
use com\cminds\payperposts\shortcode\SubscriptionsShortcode;
?>
<p><strong>Notice: shortcodes are case-sensitive</strong></p>
<article class="cmppp-shortcode-desc">
	<header>
		<h4>[<?php echo SubscriptionsShortcode::SHORTCODE_NAME; ?>]</h4>
	</header>
	<div class="cmppp-shortcode-desc-inner">
		<h5>Shortcode content</h5>
		<p>Useful for display user's subscriptions</p>
		<h5>Examples</h5>
		<p><kbd>[<?php echo SubscriptionsShortcode::SHORTCODE_NAME; ?>]</kbd></p>
	</div>
</article>
<article class="cmppp-shortcode-desc">
	<header>
		<h4>[cmppp-author-subscriptions]</h4>
	</header>
	<div class="cmppp-shortcode-desc-inner">
		<h5>Shortcode content</h5>
		<p>Useful for display author's subscriptions</p>
		<h5>Examples</h5>
		<p><kbd>[cmppp-author-subscriptions]</kbd></p>
	</div>
</article>
<article class="cmppp-shortcode-desc">
	<header>
		<h4>[cmppp_restricted]</h4>
	</header>
	<div class="cmppp-shortcode-desc-inner">
		<h5>Shortcode content</h5>
		<p>Restrict content from being viewed using a shortcode</p>
		<h5>Examples</h5>
		<p><kbd>[cmppp_restricted] any content [/cmppp_restricted]</kbd></p>
	</div>
</article>
<article class="cmppp-shortcode-desc">
	<header>
		<h4>[cmppp_availability]</h4>
	</header>
	<div class="cmppp-shortcode-desc-inner">
		<h5>Shortcode content</h5>
		<p>Return "Available", "Not Available" or "You already own this post" from post id</p>
		<h5>Examples</h5>
		<p><kbd>[cmppp_availability postid='ID']</kbd></p>
	</div>
</article>
<article class="cmppp-shortcode-desc">
	<header>
		<h4>[cmppp-group-paybox]</h4>
	</header>
	<div class="cmppp-shortcode-desc-inner">
		<h5>Shortcode content</h5>
		<p>Shows the paybox with subscription options for the group identified in the "id" attribute.
      This shortcode can be copied easily with the right id when editing groups in the Pricing tab.</p>
		<h5>Examples</h5>
		<p><kbd>[cmppp-group-paybox group_id='1']</kbd></p>
	</div>
</article>
<article class="cmppp-shortcode-desc">
  <header>
    <h4>[cmppp-available-subscription-groups]</h4>
  </header>
  <div class="cmppp-shortcode-desc-inner">
    <h5>Shortcode content</h5>
    <p>
      Shows all available subscription groups (payboxes) as tiles.
      You can setup columns amount using attribute "col". By default "col" equals 2.
    </p>
    <h5>Examples</h5>
    <p><kbd>[cmppp-available-subscription-groups col='3']</kbd></p>
  </div>
</article>
<style>
.cmppp-shortcode-desc {margin: 2em 0;}
.cmppp-shortcode-desc header {background: #f0f0f0; padding: 0.5em; display: flex;}
.cmppp-shortcode-desc header h4 {font-size: 150%; flex: 0 0 1; margin: 0; padding: 0;}
.cmppp-shortcode-desc span {flex: 1; text-align: right;}
.cmppp-shortcode-desc-inner {margin: 0 2em;}
.cmppp-shortcode-desc-inner h5 {font-size: 150%; font-weight: normal; border-bottom: 1px dashed #c0c0c0; padding-bottom: 0.2em; margin: 1em 0;}
.cmppp-shortcode-desc-inner ul li {margin-left: 2em; list-style-type: disc;}
.cmppp-shortcode-desc-inner p {margin: 1em 0;}
</style>
