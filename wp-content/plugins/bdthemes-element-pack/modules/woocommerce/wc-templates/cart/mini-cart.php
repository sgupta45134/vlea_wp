<?php

namespace ElementPack\Modules\Woocommerce\WcTemplates\Cart;

defined( 'ABSPATH' ) || exit;

function element_pack_render_mini_cart_item( $cart_item_key, $cart_item ) {
	$_product           = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	$is_product_visible = ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) );

	if ( ! $is_product_visible ) {
		return;
	}

	$product_id        = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
	$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
	$item_permalink    = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
	?>
    <div class="bdt-mini-cart-product-item bdt-flex bdt-flex-middle <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

        <div class="bdt-mini-cart-product-thumbnail">
			<?php
			$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

			if ( ! $item_permalink ) {
				echo wp_kses_post( $thumbnail );
			} else {
				printf( '<a href="%s">%s</a>', esc_url( $item_permalink ), wp_kses_post( $thumbnail ) );
			}
			?>
        </div>

        <div class="bdt-margin-small-left">
            <div class="bdt-mini-cart-product-name bdt-margin-small-bottom">
				<?php
				if ( ! $item_permalink ) {
					echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
				} else {
					echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $item_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
				}

				do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

				// Meta data.
				echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
				?>
            </div>

            <div class="bdt-mini-cart-product-price"
                 data-title="<?php esc_attr_e( 'Price', 'bdthemes-element-pack' ); ?>">
				<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); ?>
            </div>
        </div>

        <div class="bdt-mini-cart-product-remove">
			<?php
			echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
				'<a href="%s" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" data-svg="close-icon"><line fill="none" stroke="#000" stroke-width="1.1" x1="1" y1="1" x2="13" y2="13"></line><line fill="none" stroke="#000" stroke-width="1.1" x1="13" y1="1" x2="1" y2="13"></line></svg></a>',
				esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
				__( 'Remove this item', 'bdthemes-element-pack' ),
				esc_attr( $product_id ),
				esc_attr( $cart_item_key ),
				esc_attr( $_product->get_sku() )
			), $cart_item_key );
			?>
        </div>
    </div>
	<?php
}

$cart_items = WC()->cart->get_cart();

if ( empty( $cart_items ) ) { ?>
    <div class="woocommerce-mini-cart__empty-message"><?php esc_attr_e( 'No products in the cart.', 'bdthemes-element-pack' ); ?></div>
<?php } else { ?>
    <div class="bdt-mini-cart-products woocommerce-mini-cart cart woocommerce-cart-form__contents">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );
		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			element_pack_render_mini_cart_item( $cart_item_key, $cart_item );
		}
		do_action( 'woocommerce_mini_cart_contents' );
		?>
    </div>

    <div class="bdt-mini-cart-subtotal bdt-flex bdt-flex-between">
        <div>
            <strong><?php echo __( 'Subtotal', 'bdthemes-element-pack' ); ?>:</strong>
        </div>
        <div>
			<?php echo WC()->cart->get_cart_subtotal(); ?>
        </div>
    </div>
    <div class="bdt-mini-cart-footer-buttons">
        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="bdt-button bdt-button-view-cart bdt-size-md">
            <span class="bdt-button-text"><?php echo __( 'View cart', 'bdthemes-element-pack' ); ?></span>
        </a>
        <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="bdt-button bdt-button-checkout bdt-size-md">
            <span class="bdt-button-text"><?php echo __( 'Checkout', 'bdthemes-element-pack' ); ?></span>
        </a>
    </div>
	<?php
}
?>
