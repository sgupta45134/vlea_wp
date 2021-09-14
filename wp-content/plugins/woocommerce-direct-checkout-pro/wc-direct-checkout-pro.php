<?php

if ($active_plugins = get_option('active_plugins', array())) {

  foreach ($active_plugins as $key => $active_plugin) {

    if (strstr($active_plugin, '/wc-direct-checkout-pro.php')) {

      $active_plugins[$key] = str_replace('/wc-direct-checkout-pro.php', '/woocommerce-direct-checkout-pro.php', $active_plugin);
    }
  }

  update_option('active_plugins', $active_plugins);
}