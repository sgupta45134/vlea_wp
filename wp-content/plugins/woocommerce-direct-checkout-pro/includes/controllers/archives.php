<?php

class QLWCDC_PRO_Controller_Archives {

  protected static $instance;

  public function __construct() {

    include_once(QLWCDC_PRO_PLUGIN_DIR . '/includes/view/frontend/archives.php');
  }

  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}

QLWCDC_PRO_Controller_Archives::instance();
