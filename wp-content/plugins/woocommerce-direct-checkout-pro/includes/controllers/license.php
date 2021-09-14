<?php

class QLWCDC_PRO_Controller_License
{

  protected static $instance;

  public function __construct()
  {
    add_action('qlwcdc_sections_header', array($this, 'add_header'));
    add_action('woocommerce_sections_' . QLWCDC_PREFIX, array($this, 'add_section'), 99);
    add_action('woocommerce_settings_save_' . QLWCDC_PREFIX, array($this, 'save_settings'));
    add_action('admin_init', array($this, 'save_license'));
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function add_header()
  {
    global $current_section;
?>
    <li><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=' . QLWCDC_PREFIX . '&section=license'); ?>" class="<?php echo ($current_section == 'license' ? 'current' : ''); ?>"><?php esc_html_e('License', 'woocommerce-direct-checkout-pro'); ?></a> | </li>
<?php
  }

  function add_section()
  {

    global $current_section, $qlwcdc_updater;

    if ('license' == $current_section) {

      $settings = $this->get_settings();

      include_once(QLWCDC_PRO_PLUGIN_DIR . 'includes/view/backend/pages/license.php');
    }
  }

  function get_settings()
  {

    return array(
      array(
        'name' => esc_html__('License', 'woocommerce-direct-checkout-pro'),
        'type' => 'title',
        'desc' => esc_html__('Add your license key to activate the premium features.', 'woocommerce-direct-checkout-pro'),
        'id' => 'qlwcdc_section_title'
      ),
      array(
        'name' => esc_html__('Market', 'woocommerce-direct-checkout-pro'),
        'id' => 'qlwcdc_license_market',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array(
          '' => esc_html__('QuadLayers', 'woocommerce-direct-checkout'),
          'envato' => esc_html__('Envato', 'woocommerce-direct-checkout'),
        ),
        'placeholder' => esc_html__('Enter your license market', 'woocommerce-direct-checkout-pro'),
      ),
      array(
        'name' => esc_html__('Key', 'woocommerce-direct-checkout-pro'),
        'id' => 'qlwcdc_license_key',
        'type' => 'password',
        'placeholder' => esc_html__('Enter your license key', 'woocommerce-direct-checkout-pro'),
        'default' => ''
      ),
      array(
        'name' => esc_html__('Email', 'woocommerce-direct-checkout-pro'),
        'id' => 'qlwcdc_license_email',
        'type' => 'password',
        'placeholder' => esc_html__('Enter your license email', 'woocommerce-direct-checkout-pro'),
        'default' => ''
      ),
      array(
        'type' => 'sectionend',
        'id' => 'qlwcdc_products_section_end'
      )
    );
  }

  function save_license()
  {

    global $qlwcdc_updater;

    if (
      isset($_POST['save']) &&
      isset($_POST['qlwcdc_license_key']) &&
      isset($_POST['qlwcdc_license_email']) &&
      isset($_POST['qlwcdc_license_market'])
    ) {
      $qlwcdc_updater->request_activation(
        $_POST['qlwcdc_license_key'],
        $_POST['qlwcdc_license_email'],
        $_POST['qlwcdc_license_market']
      );
    }
  }

  function save_settings()
  {

    global $current_section;

    if ('license' == $current_section) {

      woocommerce_update_options($this->get_settings());
    }
  }
}

QLWCDC_PRO_Controller_License::instance();
