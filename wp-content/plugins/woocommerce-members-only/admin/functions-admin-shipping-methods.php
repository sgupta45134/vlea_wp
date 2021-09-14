<?php
/**
 * Functions for shipping methods
 * @since 1.8.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the shipping methods
 */
function wcmo_get_shipping_method_sections() {

  $sections = array();
  $methods = WC()->shipping->get_shipping_methods();

  if( $methods ) {
    foreach( $methods as $id=>$method ) {
      $sections[$id] = $method->method_title;
    }
  }

  return apply_filters( 'wcmo_get_shipping_gateway_sections', $sections );

}

function wcmo_get_shipping_methods() {

	$methods = get_option( 'wcmo_shipping_methods', array() );

	return apply_filters( 'wcmo_shipping_methods', $methods );

}

function wcmo_get_restricted_shipping_methods() {

	$methods = wcmo_get_shipping_methods();

	$restricted_methods = ! empty( $methods['restricted'] ) ? $methods['restricted'] : false;

	return apply_filters( 'wcmo_restricted_shipping_methods', $restricted_methods );

}

function wcmo_get_permitted_shipping_methods() {

	$methods = wcmo_get_shipping_methods();

	$permitted_methods = ! empty( $methods['permitted'] ) ? $methods['permitted'] : false;

	return apply_filters( 'wcmo_permitted_shipping_methods', $permitted_methods );

}
