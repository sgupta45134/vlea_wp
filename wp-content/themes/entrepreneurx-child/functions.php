<?php

add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_style', 9999 );
function enqueue_child_theme_style() {
	wp_enqueue_style( 'dtbwp_css_child', get_stylesheet_directory_uri() . '/style.css', array(
		'dtbwp_style',
	), 1.0 );
}


// Remove links to the product details pages from the product listing page of a WooCommerce store
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );


/**
 * @snippet       Remove Cart Item Link - WooCommerce Cart
 * @how-to        Get CustomizeWoo.com FREE
 * @sourcecode    https://businessbloomer.com/?p=80927
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.4.7
 */
add_filter( 'woocommerce_cart_item_permalink', 'my_remove_cart_product_link', 10 );
function my_remove_cart_product_link() {
    return __return_null();
}

/* remove the Downloads tab on the My Account page */
add_filter( 'woocommerce_account_menu_items', 'custom_remove_downloads_my_account', 999 );
 
function custom_remove_downloads_my_account( $items ) {
unset($items['downloads']);
unset($items['dashboard']);
return $items;
}



/**
 * Auto Complete all WooCommerce orders.
 */
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );
function custom_woocommerce_auto_complete_order( $order_id ) { 
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    $order->update_status( 'completed' );
}