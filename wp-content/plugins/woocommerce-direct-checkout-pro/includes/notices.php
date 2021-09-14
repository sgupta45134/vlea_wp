<?php

class QLWCDC_PRO_Notices {

  protected static $instance;
  var $free = 'woocommerce-direct-checkout';

  public function __construct() {
    add_action('admin_notices', array($this, 'add_admin_notices'));
    add_filter('plugin_action_links_' . plugin_basename(QLWCDC_PRO_PLUGIN_FILE), array($this, 'add_action_links'));
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function add_action_links($links) {

    $links[] = '<a target="_blank" href="' . QLWCDC_PRO_SUPPORT_URL . '">' . esc_html__('Support', 'woocommerce-direct-checkout-pro') . '</a>';
    $links[] = '<a target="_blank" href="' . QLWCDC_PRO_LICENSES_URL . '">' . esc_html__('License', 'woocommerce-direct-checkout-pro') . '</a>';

    return $links;
  }

  function add_admin_notices() {

    $screen = get_current_screen();

    if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
      return;
    }

    $plugin = "{$this->free}/{$this->free}.php";

    if (is_plugin_active($plugin)) {
      return;
    }

    if ($this->is_installed($plugin)) {

      if (!current_user_can('activate_plugins')) {
        return;
      }
      ?>
      <div class="error">
        <p>
          <a href="<?php echo wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $plugin); ?>" class='button button-secondary'><?php printf(esc_html__('Activate %s', 'woocommerce-direct-checkout-pro'), QLWCDC_PLUGIN_NAME); ?></a>
          <?php printf(esc_html__('%s not working because you need to activate the %s plugin.', 'woocommerce-direct-checkout-pro'), QLWCDC_PRO_PLUGIN_NAME, QLWCDC_PLUGIN_NAME); ?>   
        </p>
      </div>
      <?php
    } else {
      if (!current_user_can('install_plugins')) {
        return;
      }
      ?>
      <div class="error">
        <p>
          <a href="<?php echo wp_nonce_url(self_admin_url("update.php?action=install-plugin&plugin={$this->free}"), "install-plugin_{$this->free}"); ?>" class='button button-secondary'><?php printf(esc_html__('Install %s', 'woocommerce-direct-checkout-pro'), QLWCDC_PLUGIN_NAME); ?></a>
          <?php printf(esc_html__('%s not working because you need to install the %s plugin.', 'woocommerce-direct-checkout-pro'), QLWCDC_PRO_PLUGIN_NAME, QLWCDC_PLUGIN_NAME); ?>
        </p>
      </div>
      <?php
    }
  }

  function is_installed($path) {

    $installed_plugins = get_plugins();

    return isset($installed_plugins[$path]);
  }

}

QLWCDC_PRO_Notices::instance();
