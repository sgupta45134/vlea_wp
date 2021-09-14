<?php

class QLWCDC_PRO
{

  protected static $instance;

  public function __construct()
  {

    include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/notices.php');

    add_action('admin_init', array($this, 'add_updater'));
    add_action('plugins_loaded', array($this, 'includes'), 99);
    add_action('plugins_loaded', array($this, 'remove_premium'), 99);

    load_plugin_textdomain('woocommerce-direct-checkout-pro', false, QLWCDC_PRO_PLUGIN_DIR . '/languages/');
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function includes()
  {
    if (class_exists('QLWCDC')) {
      include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/controllers/general.php');
      include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/controllers/archives.php');
      include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/controllers/products.php');
      include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/controllers/checkout.php');
      include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/controllers/license.php');
    }
  }

  public function register_scripts()
  {

    wp_register_style('qlwcdc-pro', plugins_url('/assets/frontend/qlwcdc-pro' . QLWCDC_PRO::instance()->is_min() . '.css', QLWCDC_PRO_PLUGIN_FILE), array(), QLWCDC_PRO_PLUGIN_VERSION, 'all');

    wp_register_script('qlwcdc-pro', plugins_url('/assets/frontend/qlwcdc-pro' . QLWCDC_PRO::instance()->is_min() . '.js', QLWCDC_PRO_PLUGIN_FILE), array('jquery', 'wc-add-to-cart-variation'), QLWCDC_PRO_PLUGIN_VERSION, false);

    wp_localize_script('qlwcdc-pro', 'qlwcdc', array(
      'nonce' => wp_create_nonce('qlwcdc'),
      'delay' => 200,
      'timeout' => null
    ));
  }

  public function remove_premium()
  {

    if (class_exists('QLWCDC')) {

      $premium = QLWCDC_Controller_Premium::instance();
      $backend = QLWCDC_Backend::instance();

      remove_action('qlwcdc_sections_header', array($premium, 'add_header'));
      remove_action('admin_menu', array($premium, 'add_menu'));
      remove_action('admin_footer', array($backend, 'remove_premium'));
    }
  }

  public function is_min()
  {
    if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {
      return '.min';
    }
  }

  function add_updater()
  {

    global $qlwcdc_updater;

    include_once QLWCDC_PRO_PLUGIN_DIR . '/includes/3rd/updater.php';

    $qlwcdc_updater = qlwdd_updater(array(
      'api_url' => 'https://quadlayers.com/wc-api/qlwdd/',
      'plugin_url' => QLWCDC_PRO_DEMO_URL,
      'plugin_file' => QLWCDC_PRO_PLUGIN_FILE,
      'license_key' => get_option('qlwcdc_license_key'),
      'license_email' => get_option('qlwcdc_license_email'),
      'license_market' => get_option('qlwcdc_license_market'),
      'license_url' => admin_url('admin.php?page=wc-settings&tab=qlwcdc&section=license'),
      'product_key' => '16cb9ea2107b1ac236800dd5168c3c0f',
      'envato_key' => 'Gn46hMOIcvz8uyVvpe0jB2ge7A1RdH5T',
      'envato_id' => '28179405',
    ));
  }
}

QLWCDC_PRO::instance();
