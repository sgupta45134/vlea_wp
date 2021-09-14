<?php

class QLWCDC_PRO_Controller_Products
{

  protected static $instance;

  public function __construct()
  {

    include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/view/frontend/products.php');

    add_action('woocommerce_process_product_meta', array($this, 'save_product_options'));
  }

  public static function instance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  function save_product_options($product_id)
  {

    QLWCDC_Controller_Products::instance()->add_product_fields();

    if ($product = wc_get_product($product_id)) {

      $product_fields = QLWCDC_Controller_Products::instance()->product_fields;

      if ($product_fields && is_array($product_fields)) {

        foreach ($product_fields as $field) {

          if (isset($field['id']) && isset($_POST[$field['id']])) {

            $value = esc_attr(trim(stripslashes($_POST[$field['id']])));

            if ($value != get_option($field['id'], true)) {
              $product->update_meta_data($field['id'], $value);
            } else {
              $product->delete_meta_data($field['id']);
            }
          }
        }
      }

      $product->save();
    }
  }
}

QLWCDC_PRO_Controller_Products::instance();
