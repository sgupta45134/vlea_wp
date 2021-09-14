<?php

class QLWCDC_PRO_General {

  protected static $_instance;

  public function __construct() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), -10);
  }

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function enqueue_scripts() {
    if (did_action('before_woocommerce_init')) {
      QLWCDC_PRO::instance()->register_scripts();

      wp_enqueue_style('qlwcdc-pro');

      wp_enqueue_script('qlwcdc-pro');
    }
  }

}

QLWCDC_PRO_General::instance();
