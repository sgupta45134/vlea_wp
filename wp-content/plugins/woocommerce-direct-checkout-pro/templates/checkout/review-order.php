<?php
if (!defined('ABSPATH')) {
  exit;
}
?>
<?php if ($fields = array_flip(get_option('qlwcdc_add_checkout_cart_fields', false))) : ?>
  <table class="shop_table woocommerce-checkout-review-order-table <?php echo esc_attr(get_option('qlwcdc_add_checkout_cart_class')); ?>">
    <tbody>
      <tr>
        <td colspan="100%" style="
            border: none;
            padding: 0;
            margin: 0;
            ">
          <table class="shop_table shop_table_responsive cart" style="
                 border: 0;
                 margin: 0;
                 padding: 0;
                 ">
            <thead>
              <tr>
                <?php if (isset($fields['remove'])): ?>
                  <th class="product-remove">&nbsp;</th>
                <?php endif; ?>
                <?php if (isset($fields['thumbnail'])): ?>
                  <th class="product-thumbnail">&nbsp;</th>
                <?php endif; ?>
                <?php if (isset($fields['name'])): ?>
                  <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <?php endif; ?>
                <?php if (isset($fields['price'])): ?>
                  <th class="product-price"><?php esc_html_e('Price', 'woocommerce'); ?></th>
                <?php endif; ?>
                <?php if (isset($fields['qty'])): ?>
                  <th class="product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                <?php endif; ?>
                <th class="product-subtotal"><?php esc_html_e('Total', 'woocommerce'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              //do_action('woocommerce_review_order_before_cart_contents');

              foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                  ?>
                  <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                    <?php if (isset($fields['remove'])): ?>
                      <td class="product-remove">
                        <?php echo sprintf('<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">&times;</a>', esc_url(wc_get_cart_remove_url($cart_item_key)), esc_html__('Remove this item', 'woocommerce'), esc_attr($_product->get_id()), esc_attr($_product->get_sku()), $cart_item_key); ?>
                      </td>
                    <?php endif; ?>
                    <?php if (isset($fields['thumbnail'])): ?>
                      <td class="product-thumbnail">
                        <?php
                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                        if (!$product_permalink) {
                          echo $thumbnail; // PHPCS: XSS ok.
                        } else {
                          printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                        }
                        ?>
                      </td>
                    <?php endif; ?>
                    <?php if (isset($fields['name'])): ?>
                      <td class="product-name" data-title="<?php esc_html_e('Product', 'woocommerce'); ?>">
                        <?php echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;'; ?>
                        <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times; %s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                        <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                      </td>
                    <?php endif; ?>
                    <?php if (isset($fields['price'])): ?>
                      <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                        <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                      </td>
                    <?php endif; ?>
                    <?php if (isset($fields['qty'])): ?>
                      <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                        <?php
                        if ($_product->is_sold_individually()) {
                          $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                        } else {
                          $product_quantity = woocommerce_quantity_input(array(
                              'input_name' => "cart[{$cart_item_key}][qty]",
                              'input_value' => $cart_item['quantity'],
                              'max_value' => $_product->get_max_purchase_quantity(),
                              'min_value' => '0',
                              'product_name' => $_product->get_name(),
                                  ), $_product, false);
                        }

                        echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                        ?>
                      </td>
                    <?php endif; ?>
                    <td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                      <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                    </td>
                  </tr>
                  <?php
                }
              }

              //do_action('woocommerce_review_order_after_cart_contents');
              ?>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr class="cart-subtotal">
        <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_subtotal_html(); ?></td>
      </tr>

      <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
        <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
          <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php wc_cart_totals_coupon_label($coupon); ?></th>
          <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

        <?php do_action('woocommerce_review_order_before_shipping'); ?>


        <?php
        ob_start();
        wc_cart_totals_shipping_html();
        echo str_replace('<th>', '<th colspan="' . esc_attr(count($fields)) . '">', ob_get_clean());
        ?>

        <?php do_action('woocommerce_review_order_after_shipping'); ?>

      <?php endif; ?>

      <?php foreach (WC()->cart->get_fees() as $fee) : ?>
        <tr class="fee">
          <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php echo esc_html($fee->name); ?></th>
          <td><?php wc_cart_totals_fee_html($fee); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
        <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
          <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
            <tr class="tax-rate tax-rate-<?php echo sanitize_title($code); ?>">
              <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php echo esc_html($tax->label); ?></th>
              <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr class="tax-total">
            <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>

      <?php do_action('woocommerce_review_order_before_order_total'); ?>

      <tr class="order-total">
        <th colspan="<?php echo esc_attr(count($fields)); ?>"><?php esc_html_e('Total', 'woocommerce'); ?></th>
        <td><?php wc_cart_totals_order_total_html(); ?></td>
      </tr>
      <?php do_action('woocommerce_review_order_after_order_total'); ?>
    </tfoot>
  </table>
<?php endif; ?>