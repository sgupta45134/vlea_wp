<?php

/**
 * Plugin Name: WooCommerce Direct Checkout PRO
 * Description: Simplifies the checkout process to improve your sales rate.
 * Version:     2.4.0
 * Author:      QuadLayers
 * Author URI:  https://www.quadlayers.com
 * Copyright:   2021 QuadLayers (https://www.quadlayers.com)
 * Text Domain: woocommerce-direct-checkout-pro
 * WC requires at least: 3.1.0
 * WC tested up to: 5.4
 */
if (!defined('ABSPATH')) {
  die('-1');
}
if (!defined('QLWCDC_PLUGIN_NAME')) {
  define('QLWCDC_PLUGIN_NAME', 'WooCommerce Direct Checkout');
}
if (!defined('QLWCDC_PRO_PLUGIN_NAME')) {
  define('QLWCDC_PRO_PLUGIN_NAME', 'WooCommerce Direct Checkout PRO');
}
if (!defined('QLWCDC_PRO_PLUGIN_VERSION')) {
  define('QLWCDC_PRO_PLUGIN_VERSION', '2.4.0');
}
if (!defined('QLWCDC_PRO_PLUGIN_FILE')) {
  define('QLWCDC_PRO_PLUGIN_FILE', __FILE__);
}
if (!defined('QLWCDC_PRO_PLUGIN_DIR')) {
  define('QLWCDC_PRO_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR);
}
if (!defined('QLWCDC_PRO_DOMAIN')) {
  define('QLWCDC_PRO_DOMAIN', 'qlwcdc');
}
if (!defined('QLWCDC_PRO_WORDPRESS_URL')) {
  define('QLWCDC_PRO_WORDPRESS_URL', 'https://wordpress.org/plugins/woocommerce-direct-checkout/');
}
if (!defined('QLWCDC_PRO_REVIEW_URL')) {
  define('QLWCDC_PRO_REVIEW_URL', 'https://wordpress.org/support/plugin/woocommerce-direct-checkout/reviews/?filter=5#new-post');
}
if (!defined('QLWCDC_PRO_DEMO_URL')) {
  define('QLWCDC_PRO_DEMO_URL', 'https://quadlayers.com/portfolio/woocommerce-direct-checkout/?utm_source=qlwcdc_admin');
}
if (!defined('QLWCDC_PRO_PURCHASE_URL')) {
  define('QLWCDC_PRO_PURCHASE_URL', QLWCDC_PRO_DEMO_URL);
}
if (!defined('QLWCDC_PRO_SUPPORT_URL')) {
  define('QLWCDC_PRO_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwcdc_admin');
}
if (!defined('QLWCDC_PRO_LICENSES_URL')) {
  define('QLWCDC_PRO_LICENSES_URL', 'https://quadlayers.com/account/licenses/?utm_source=qlwcdc_admin');
}
if (!defined('QLWCDC_PRO_GROUP_URL')) {
  define('QLWCDC_PRO_GROUP_URL', 'https://www.facebook.com/groups/quadlayers');
}

if (!class_exists('QLWCDC_PRO')) {
  include_once(QLWCDC_PRO_PLUGIN_DIR . 'includes/qlwcdc-pro.php');
}