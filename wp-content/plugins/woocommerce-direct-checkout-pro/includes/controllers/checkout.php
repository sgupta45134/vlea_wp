<?php

class QLWCDC_PRO_Controller_Checkout {

  protected static $instance;

  public function __construct() {

    include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/view/frontend/checkout.php');
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}

QLWCDC_PRO_Controller_Checkout::instance();
