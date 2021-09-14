<?php
/**
 * Functions for payment methods
 * @since 1.8.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the payment gateways by ID=>Title
 */
function wcmo_get_payment_gateway_sections() {

  $sections = array();
  $gateways = WC()->payment_gateways->get_available_payment_gateways();

  if( $gateways ) {
    foreach( $gateways as $id=>$gateway ) {
      $sections[$id] = $gateway->title;
    }
  }

  return apply_filters( 'wcmo_get_payment_gateway_sections', $sections );

}

function wcmo_get_payment_methods() {

	$methods = get_option( 'wcmo_payment_methods', array() );

	return apply_filters( 'wcmo_payment_methods', $methods );

}

function wcmo_get_restricted_payment_methods() {

	$methods = wcmo_get_payment_methods();

	$restricted_methods = ! empty( $methods['restricted'] ) ? $methods['restricted'] : array();

	return apply_filters( 'wcmo_restricted_payment_methods', $restricted_methods );

}

function wcmo_get_permitted_payment_methods() {

	$methods = wcmo_get_payment_methods();

	$permitted_methods = ! empty( $methods['permitted'] ) ? $methods['permitted'] : array();

	return apply_filters( 'wcmo_permitted_payment_methods', $permitted_methods );

}
