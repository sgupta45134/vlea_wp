<?php

class QLWCDC_PRO_Checkout
{

  protected static $instance;

  public function __construct()
  {
    //add_action('wp_enqueue_scripts', array($this, 'add_product_js'), 99);
    add_action('wp_ajax_qlwcdc_update_cart', array($this, 'ajax_update_cart'));
    add_action('wp_ajax_nopriv_qlwcdc_update_cart', array($this, 'ajax_update_cart'));
    add_action('woocommerce_checkout_init', array($this, 'add_checkout_coupon'), 20);

    if (isset($_GET['add-to-cart']) && absint($_GET['add-to-cart']) > 0) {
      add_filter('wc_add_to_cart_message_html', '__return_false');
    }

    if ('yes' === get_option('qlwcdc_remove_checkout_columns')) {
      add_action('wp_head', array($this, 'remove_checkout_columns'));
    }

    if ('yes' === get_option('qlwcdc_remove_checkout_gateway_icon')) {
      add_filter('woocommerce_gateway_icon', '__return_false');
    }

    if ('remove' === get_option('qlwcdc_remove_checkout_coupon_form')) {
      remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
    } elseif ('toggle' === get_option('qlwcdc_remove_checkout_coupon_form')) {
      add_action('wp_head', array($this, 'remove_coupon_toggle'));
    } elseif ('checkout' === get_option('qlwcdc_remove_checkout_coupon_form')) {
      add_action('woocommerce_review_order_after_order_total', array($this, 'add_checkout_coupon_form'));
      remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
    }

    //add_action('woocommerce_before_checkout_form', array($this, 'review_offer'));

    add_filter('wc_get_template', array($this, 'wc_get_template'), 10, 5);
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function ajax_update_cart()
  {

    if (!check_ajax_referer(QLWCDC_PRO_DOMAIN, 'nonce', false)) {
      wp_send_json_error(esc_html__('Please reload page.', 'woocommerce-direct-checkout-pro'));
    }

    $cart_item_key = $_POST['hash'];

    $threeball_product_values = WC()->cart->get_cart_item($cart_item_key);

    $threeball_product_quantity = apply_filters('woocommerce_stock_amount_cart_item', apply_filters('woocommerce_stock_amount', preg_replace("/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT))), $cart_item_key);

    $passed_validation = apply_filters('woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity);

    if ($passed_validation) {
      WC()->cart->set_quantity($cart_item_key, $threeball_product_quantity, true);
    }

    ob_start();
?>
    <div id="order_review" class="woocommerce-checkout-review-order">
      <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>
    <?php
    $data = ob_get_clean();

    wp_send_json($data);

    wp_die();
  }

  function add_checkout_coupon()
  {
    if (!empty($_GET['coupon_code'])) {

      $coupon_code = wc_format_coupon_code(wp_unslash($_GET['coupon_code']));

      if (!in_array($coupon_code, WC()->cart->get_applied_coupons())) {
        WC()->cart->add_discount($coupon_code);
      }
    }
  }

  function remove_checkout_columns()
  {
    if (function_exists('is_checkout') && is_checkout()) {
    ?>
      <style>
        .woocommerce .col2-set .col-1,
        .woocommerce-page .col2-set .col-1,
        .woocommerce-checkout .woocommerce #payment,
        .woocommerce-checkout .woocommerce #order_review,
        .woocommerce-checkout .woocommerce #order_review_heading,
        .woocommerce-checkout .woocommerce #customer_details {
          width: 100% !important;
          float: none !important;
        }
      </style>
    <?php
    }
  }

  function remove_coupon_toggle()
  {
    if (function_exists('is_checkout') && is_checkout()) {
    ?>
      <style>
        .woocommerce-form-coupon-toggle .woocommerce-info {
          display: none !important;
        }

        .woocommerce-form-coupon {
          display: block !important;
        }
      </style>
    <?php
    }
  }

  function add_checkout_coupon_form()
  {
    ?>
    <tr id="qlwcdc_order_coupon_code" class="coupon-code">
      <td colspan="100%">
        <p class="form-row" style="margin: 0;">
          <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Click here to enter your coupon code', 'woocommerce-direct-checkout-pro'); ?>" id="qlwcdc_coupon_code" value="" />
        </p>
      </td>
    </tr>
<?php
  }

  function wc_get_template($located, $template_name, $args, $template_path, $default_path)
  {

    if ('checkout/review-order.php' == $template_name) {
      if ('yes' === get_option('qlwcdc_add_checkout_cart') && count(get_option('qlwcdc_add_checkout_cart_fields', array()))) {
        $located = QLWCDC_PRO_PLUGIN_DIR . 'templates/checkout/review-order.php';
      }
    }

    if ('order/order-details-customer.php' == $template_name) {
      if ('yes' === get_option('qlwcdc_remove_order_details_address')) {
        $located = QLWCDC_PRO_PLUGIN_DIR . 'templates/order/order-details-customer.php';
      }
    }

    return $located;
  }

  //    function review_offer() {
  //
  //      //if ($licenses = QLWDD_Tables::get_order_licenses($order->get_id())) {
  //      wc_get_template('templates/checkout/review-offer.php', array(), '', QLWCDC_PRO_PLUGIN_DIR);
  //      //}
  //    }
}

QLWCDC_PRO_Checkout::instance();
