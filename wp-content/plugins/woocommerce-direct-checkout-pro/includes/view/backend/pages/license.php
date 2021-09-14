<?php include_once( QLWCDC_PLUGIN_DIR . 'includes/view/backend/pages/parts/tabs.php' ); ?>
<?php woocommerce_admin_fields($settings); ?>
<table class="form-table" cellspacing="0">
  <tbody>
    <?php if ($activation = $qlwcdc_updater->get_activation()) : ?>
      <?php if (!empty($activation->success)) : ?>
        <tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Created', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo date(get_option('date_format'), strtotime($activation->license_created)) ?></td>
        </tr>
        <tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Limit', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo $activation->license_limit ? esc_attr($activation->license_limit) : esc_html__('Unlimited', 'woocommerce-direct-checkout-pro'); ?></td>
        </tr>
        <tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Activations', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo esc_attr($activation->activation_count); ?></td>
        </tr>
        <tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Updates', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo ($activation->license_expiration != '0000-00-00 00:00:00' && $activation->license_updates) ? sprintf(esc_html__('Expires on %s', 'woocommerce-direct-checkout-pro'), $activation->license_expiration) : esc_html__('Unlimited', 'woocommerce-direct-checkout-pro'); ?></td>
        </tr>
        <tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Support', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo ($activation->license_expiration != '0000-00-00 00:00:00' && $activation->license_support) ? sprintf(esc_html__('Expires on %s', 'woocommerce-direct-checkout-pro'), $activation->license_expiration) : esc_html__('Unlimited', 'woocommerce-direct-checkout-pro'); ?></td>
        </tr>
        <!--<tr valign="top">
          <th scope="row" class="titledesc"><?php esc_html_e('Expiration', 'woocommerce-direct-checkout-pro') ?></th>
          <td><?php echo ($activation->license_expiration != '0000-00-00 00:00:00') ? date_i18n(get_option('date_format'), strtotime($activation->license_expiration)) : esc_html__('Unlimited', 'woocommerce-direct-checkout-pro'); ?></td>
        </tr>-->
      <?php endif; ?>
      <tr valign="top">
        <th scope="row" class="titledesc"><?php esc_html_e('Status', 'woocommerce-direct-checkout-pro'); ?></th>
        <td><?php echo esc_html($activation->message); ?></td>
      </tr>
    <?php endif; ?>
    <tr valign="top">
      <th scope="row" class="titledesc"><?php esc_html_e('Message', 'woocommerce-direct-checkout-pro'); ?></th>
      <td scope="row" class="titledesc">
        <p class="description">
          <?php if (empty($activation->activation_instance)): ?>
            <?php printf(__('Before you can receive plugin updates, you must first authenticate your license. To locate your License Key, <a href="%s" target="_blank">log in</a> to your account and navigate to the <strong>Account > Licenses</strong> page.', 'woocommerce-direct-checkout-pro'), QLWCDC_PRO_LICENSES_URL); ?>
          <?php else: ?>
            <?php printf(__('Thanks for register your license! If you have doubts you can request <a href="%s" target="_blank">support</a> through our ticket system.', 'woocommerce-direct-checkout-pro'), QLWCDC_PRO_SUPPORT_URL); ?>
          <?php endif; ?>
        </p>
      </td>
    </tr>
  </tbody>
</table>