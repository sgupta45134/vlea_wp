<?php

use com\cminds\payperposts\App;

$cminds_plugin_config = array(
	'plugin-is-pro'				 => TRUE,
	'plugin-has-addons'      => TRUE,
	'plugin-addons'        => array(
		array(
			'title' => 'Pay Per Post Direct Payments',
			'description' => 'Allow users to pay for post or group of past using Easy digital downloads cart.',
			'link' => 'https://www.cminds.com/store/purchase-cm-pay-per-post-plugin-for-wordpress/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=96647&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1'
		),
		array(
			'title' => 'CM MicroPayment Platform',
			'description' => 'Add your own “virtual currency“ and allow to charge for posting and answering questions.',
			'link' => 'https://www.cminds.com/store/micropayments/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=11388&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=0'
		),
	),
	'plugin-show-shortcodes'	 => TRUE,
	'plugin-shortcodes'			 => '<p>You can use the following available shortcodes.</p>',
	'plugin-shortcodes-action'	 => 'cmppp_display_supported_shortcodes',
	'plugin-version'			 => App::VERSION,
	'plugin-abbrev'				 => App::PREFIX,
	'plugin-parent-abbrev'		 => '',
	'plugin-settings-url'		 => admin_url( 'admin.php?page=cmppp' ),
	'plugin-file'				 => App::getPluginFile(),
	'plugin-dir-path'			 => plugin_dir_path( App::getPluginFile() ),
	'plugin-dir-url'			 => plugin_dir_url( App::getPluginFile() ),
	'plugin-basename'			 => plugin_basename( App::getPluginFile() ),
	'plugin-icon'				 => '',
	'plugin-name'				 => App::getPluginName(true),
	'plugin-license-name'		 => App::getPluginName(),
	'plugin-slug'				 => App::LICENSING_SLUG,
	'plugin-short-slug'			 => App::PREFIX,
	'plugin-parent-short-slug'	 => '',
	'plugin-menu-item'			 => App::MENU_SLUG,
	'plugin-textdomain'			 => '',
	'plugin-userguide-key'		 => '296-cm-pay-per-post-cmppp',
	'plugin-store-url'			 => 'https://www.cminds.com/store/purchase-cm-pay-per-post-plugin-for-wordpress/',
	'plugin-support-url'		 => 'https://www.cminds.com/wordpress-plugin-customer-support-ticket/',
	'plugin-review-url'			 => '',
	'plugin-changelog-url'		 => 'https://www.cminds.com/store/purchase-cm-pay-per-post-plugin-for-wordpress/#changelog',
	'plugin-licensing-aliases'	 => array( App::getPluginName(false), App::getPluginName(true) ),
);
