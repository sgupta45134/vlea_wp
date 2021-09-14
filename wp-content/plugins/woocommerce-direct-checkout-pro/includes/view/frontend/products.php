<?php

class QLWCDC_PRO_Products {

  protected static $instance;

  public function __construct() {
    add_filter('body_class', array($this, 'add_class'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), -20);
    add_action('wp_ajax_qlwcdc_add_to_cart_action', array($this, 'add_to_cart_action'));
    add_action('wp_ajax_nopriv_qlwcdc_add_to_cart_action', array($this, 'add_to_cart_action'));
    add_action('wp_ajax_qlwcdc_add_product_cart_ajax_message', array($this, 'add_product_cart_ajax_message'));
    add_action('wp_ajax_nopriv_qlwcdc_add_product_cart_ajax_message', array($this, 'add_product_cart_ajax_message'));
    add_filter('woocommerce_add_to_cart_redirect', array($this, 'add_to_cart_redirect'), 10);
    add_action('woocommerce_after_add_to_cart_button', array($this, 'add_quick_purchase_button'), -5);
    add_action('woocommerce_before_single_product_summary', array($this, 'add_product_default_attributes'));
    add_action('wp_loaded', array($this, 'remove_redirect_url'));
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function enqueue_scripts() {

    global $post;
    if (did_action('before_woocommerce_init')) {
      if (function_exists('is_product') && is_product()) {
        if ('yes' === QLWCDC::instance()->get_product_option($post->ID, 'qlwcdc_add_product_ajax', 'no')) {

          QLWCDC_PRO::instance()->register_scripts();

          // wp_enqueue_script('wc-add-to-cart');

          // wp_enqueue_script('wc-add-to-cart-variation');
        }
      }
    }
  }

  function remove_redirect_url() {

    if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'qlwcdc_add_to_cart_action')) {
      add_filter('woocommerce_add_to_cart_redirect', '__return_false');
    }

    if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'qlwcdc_add_product_cart_ajax_message')) {
      remove_action('wp_loaded', array('WC_Form_Handler', 'add_to_cart_action'), 20);
    }
  }

  function add_to_cart_action() {
    WC_AJAX::get_refreshed_fragments();
    wc_clear_notices();
  }

  function add_class($classes) {

    global $post;

    if (function_exists('is_product') && is_product()) {
      if ('no' != QLWCDC::instance()->get_product_option($post->ID, 'qlwcdc_add_product_ajax', 'no')) {
        $classes[] = 'qlwcdc-product-ajax';
      }
      if ('no' != QLWCDC::instance()->get_product_option($post->ID, 'qlwcdc_add_product_ajax_alert', 'no')) {
        $classes[] = 'qlwcdc-product-ajax-alert';
      }
    }
    return $classes;
  }

  function add_product_cart_ajax_message() {

    global $wp_query;

    if (!isset($_REQUEST['add-to-cart'])) {
      return;
    }

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['add-to-cart']));
    $product_id = wp_get_post_parent_id($product_id) ? wp_get_post_parent_id($product_id) : $product_id;

    $args = array(
        'p' => $product_id,
        'post_type' => 'any'
    );

    $wp_query = new WP_Query($args);

    ob_start();

    woocommerce_output_all_notices();

    $data = ob_get_clean();

    wp_send_json_success($data);
  }

  function get_quick_purchase_link($product_id = 0) {

    if ('checkout' === QLWCDC::instance()->get_product_option($product_id, 'qlwcdc_add_product_quick_purchase_to', 'checkout')) {
      return wc_get_checkout_url();
    }

    return wc_get_cart_url();
  }

  function add_to_cart_redirect($url) {

    if (isset($_GET['add-to-cart']) && absint($_GET['add-to-cart']) > 0 && strpos(home_url($_SERVER['REQUEST_URI']), $this->get_quick_purchase_link()) !== false) {
      return false;
    }

    return $url;
  }

  function add_quick_purchase_button() {

    global $product;

    static $instance = 0;

    if (!$instance && 'yes' === QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_quick_purchase', 'no') && $product->get_type() != 'external') {
      ?>
      <button type="submit" class="single_add_to_cart_button button qlwcdc_quick_purchase <?php echo esc_attr(QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_quick_purchase_type')); ?> <?php echo esc_attr(QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_quick_purchase_class')); ?>" data-href="<?php echo $this->get_quick_purchase_link($product->get_id()); ?>"><?php esc_html_e(QLWCDC::instance()->get_product_option($product->get_id(), 'qlwcdc_add_product_quick_purchase_text', esc_html__('Purchase Now', 'woocommerce-direct-checkout-pro'))); ?></button>
      <?php
      $instance++;
    }
  }

  function add_product_default_attributes() {

    if ('yes' === get_option('qlwcdc_add_product_default_attributes')) {

      global $product;

      if (!count($default_attributes = get_post_meta($product->get_id(), '_default_attributes'))) {

        $new_defaults = array();

        $product_attributes = $product->get_attributes();

        if (count($product_attributes)) {

          foreach ($product_attributes as $key => $attributes) {

            $values = explode(',', $product->get_attribute($key));

            if (isset($values[0]) && !isset($default_attributes[$key])) {
              $new_defaults[$key] = sanitize_key($values[0]);
            }
          }

          update_post_meta($product->get_id(), '_default_attributes', $new_defaults);
        }
      }
    }
  }

}

QLWCDC_PRO_Products::instance();
