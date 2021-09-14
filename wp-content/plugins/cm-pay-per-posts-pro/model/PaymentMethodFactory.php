<?php

namespace com\cminds\payperposts\model;

class PaymentMethodFactory {
	public static function getPaymentsNameList() {
		return [
			'EDD',
			'Micropayments',
			'WooCommerce'
		];
	}

	public static function filterPaymentForPost( $payments, $post ) {
		if ( ! empty( $payments ) ) {
			foreach ( $payments as $key => $payment ) {
				$paymentInstance = null;

				if ( $payment == 'EDD' ) {
					$paymentInstance = new PostInstantPayment( $post );
				}

				if ( $payment == 'Micropayments' ) {
					$paymentInstance = new Micropayments( $post );
				}

				if ( $payment == 'WooCommerce' ) {
					$paymentInstance = new PostWooPayment( $post );
				}

				if ( ! $paymentInstance || ! $paymentInstance->isPaid() ) {
					unset( $payments[ $key ] );
				}

				unset( $paymentInstance );
			}
		}

		return $payments;
	}
}
