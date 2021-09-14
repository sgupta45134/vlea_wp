<?php

function __cm( $slug ) {
    return __( __cm_no_transactions( $slug ), 'cm-micropayments' );
}

function __cm_no_transactions( $slug ) {
    return CMMicropaymentPlatformLabel::getLabel( $slug );
}

class CMMicropaymentPlatformLabel {

    const OPTION_LABEL_PREFIX = 'cmmp_label_';

    public static $labelsSettingsLabels       = array(
        'paypal_disabled_message' => 'Built-in PayPal disabled checkout',
    );
    protected static $defaultLabels           = array(
        'cm_micropayment_plural_name'         => 'points',
        'cm_micropayment_singular_name'       => 'point',
        'external_currency_id'                => 'CMMP',
        'create_wallet'                       => 'Create wallet',
        'your_wallet_id_is'                   => 'Your wallet ID is:',
        'new_wallet_id_is'                    => 'New wallet ID is:',  
        'checkout_form_headline'              => 'How much would you like to purchase?',
        'format_price_in_checkout'            => '%s %s per %s %s',
        'checkout_no_points_defined'          => 'No points defined',
        'format_price_in_wallet'			  => '%.2f %s',
        'checkout_form_msg_above_send_button' => 'Credit is non-refundable.',
        'wallet_id_checkout'                  => 'Wallet ID',
        'wallet_data_entered_wallet_id'       => 'Entered Wallet:',
        'no_assigned_wallet'                  => 'This account has no wallet assigned to it.',
        'access_is_not_allowed'               => 'You are not allowed to view this content.',
        'last_transactions'                   => 'Last %s transactions',
        'lo_header'                           => 'No.',
        'transaction_date_header'             => 'Transaction Date',
        'transaction_points_header'           => 'Points',
        'transaction_type_header'             => 'Transaction Type',
        'transaction_comment_header'          => 'Transaction Comment',
        'check_wallet_form_wallet_id'         => 'Wallet ID',
        'check_wallet_form_send_button_label' => 'Check Wallet',
        'checkout_button_send'                => 'Send',
        'gateway_wallet_id'                   => 'Please enter your virtual Wallet ID.',
        'gateway_wallet_id_description'       => 'Please enter your virtual Wallet ID. It has been filled with the Wallet ID of your account.',
        'granted_manually'                    => 'Granted manually',
        'wallet_actual_points'                => 'Actual points',
        'wallet_checkout_link_text'           => 'Purchase more points',
        'withdraw'                            => 'Withdraw',
        'charge'                              => 'Charge',
        'grant'                               => 'Grant',
        'transfer_to_another_wallet'          => 'Outgoing',
        'raceived_from_another_wallet'        => 'Incoming',
        'anonymous_mode_disabled'             => 'Anonymous wallets are not allowed.',
        'only_one_wallet_for_user'            => 'Only one wallet for user is avaliable.',
        'wallets_quantity_limit_reached'      => 'Quantity of wallets for user has reached limit of %d.',
        //'grant_paypal'							 => 'Granted via PayPal',
        'edd_payment'                         => 'EDD payment',
        'edd_purchase_grant'                  => 'EDD purchase grant',
        'woo_purchase_grant'                  => 'WooCommerce purchase grant',
        'woo_payment_charge'                  => 'WooCommerce purchase charge',
        'import_operation'					  => 'Import Operation',
        'paypal_payout'                       => 'PayPal Payout',
		'transaction_commission'			  => 'Commission',
        'undefined_transaction_type'          => 'Undefined transaction type',
        'charged_for_edd_payment'             => 'Charged for EDD payment',
        'charged_for_woo_payment'             => 'Charged for WooCommerce payment',
        'granted_for_edd_purchase'            => 'Granted for EDD purchase',
        'granted_for_woo_purchase'            => 'Granted for WooCommerce purchase',
        'type_granted_manually'               => 'Granted manually',
        'type_incoming'                       => 'Incoming',
        'type_outgoing'                       => 'Outgoing',
        'type_charge'                         => 'Charge',
        'type_import_operation'               => 'Import Grant / Charge',
        'type_grant'                          => 'Grant',
        'wallet_exchange'                     => 'Wallet Exchange',
        'paypal_disabled_message'             => 'The ability to add %s has been disabled. Please contact the site administrator to check how to obtain them.',
        //Gateway specific labels
        'gateway_payment_info'                => 'Payment Info',
        'gateway_edd_wallet_id'               => 'Wallet ID',
        'gateway_purchase_cost'               => 'Purchase cost: ',
        'gateway_remaining_points_in_wallet'  => 'Remaining points in your Wallet: ',
        'gateway_empty_cart'                  => 'Invalid Wallet ID',
        'gateway_ratio_error'                 => 'Cannot convert price to points.',
        'gateway_not_enough_points'           => 'Not enough points in the wallet.',
        'gateway_wallet_error'                => 'Invalid Wallet ID',
        'gateway_optional'                    => '(optional)',
        'from_wallet_id'                      => 'From Wallet ID:',
        'from_wallet_id_placeholder'          => 'From Wallet ID',
        'to_wallet_id'						  => 'To Wallet ID:',
        'to_wallet_id_placeholder'			  => 'To Wallet ID',
        'transfer_points_placeholder'		  => 'Transfer points',
        'transfer_points_message_placeholder' => 'Transfer comment placeholder',
        'transfer_points_message_label'       => 'Comment:',
        'to_user'						      => 'To User:',
        'points_to_transfer'                  => '%s to transfer:',
        'transfer_points'                     => 'Transfer %s',
        'transfer_wallet_points_success_msg'  => 'Transfer successful. Transferred %s points from %s to %s',
        'transfer_wallet_points_error_msg'    => 'Transfer Error. you can\'t transfer less than 1 point',
        'pay_with_cm_micropayment_points'	  => 'Pay with CM MicroPayment Points',
		'discount_wallet_id' 				  => 'Wallet ID:',
		'discount_points_to_exchange' 		  => 'Points to exchange:',
		'discount_value'			 		  => 'Discount value:',
		'discount_exchange'			 		  => 'Exchange',
		'discount_remaining_points'	 		  => 'Remaining points: %s (%s %s)',
		'discount_code'				 		  => 'Discount code',
		'discount_edd_value'			 	  => 'Value',
		'discount_status'				 	  => 'Status',
		'discount_used'				 		  => 'Used',
		'discount_active'				 	  => 'Active',
		'paypal_wallet_id' 				 	  => 'Wallet ID:',
		'paypal_points_to_exchange' 	 	  => 'Points to exchange:',
		'paypal_remaining_points'	 	 	  => 'Remaining points: %s (%s %s)',
		'paypal_payout_value'		 	 	  => 'PayPal Payout value:',
		'paypal_receiver_email:'	 	 	  => 'Receiver e-mail:',
        'paypal_payout_threshold'		 	  => 'Payout threshold is %s$',
		'paypal_exchange'			 	 	  => 'Exchange',
		'paypal_non_negative'		 	 	  => 'Amount must be non-negative!',
		'paypal_valid_email'		 	 	  => 'E-mail must be valid e-mail.',
		'paypal_for_points'			 	 	  => 'PayPal pay for points:',
		'paypal_pp_receiver_email'	 	 	  => 'PayPal receiver e-mail:',
		'paypal_error_obtaining_wallet'	 	  => 'Error: Problem with obtaining the wallet',
		'paypal_error_empty_email'		 	  => 'E-mail cannot be empty!',
		'paypal_error_correct_email'	 	  => 'E-mail must be a correct e-mail address!',
		'paypal_error_empty_points'		 	  => 'Points cannot be empty!',
		'paypal_error_invalid_points'	 	  => 'Invalid value for points!',
		'paypal_error_not_enough_points' 	  => 'Wallet does not have enough points.',
		'paypal_error' 	  					  => 'Error',
		'paypal_payout_for_exchanging' 		  => 'PayPal Payout for exchanging %d %s from Wallet ID: %s for %s',
		'paypal_p_payout'				 	  => '%s Payout',
		'stripe_remaining_points'		 	  => 'Remaining points: %s (%s %s)',
		'stripe_wallet_id'				 	  => 'Wallet ID:',
		'stripe_account_id'				 	  => 'Stripe account ID:',
		'stripe_points_to_exchange'		 	  => 'Points to exchange:',
		'stripe_payout_value'			 	  => 'Stripe Payout value:',
        'stripe_payout_submit_payment'		  => 'Submit payment',
		'stripe_error_negative_amount'	 	  => 'Amount must be non-negative!',
		'stripe_payout_for_exchanging'	 	  => 'Stripe Payout for exchanging %d %s from Wallet ID: %s for %s',
		'discount_wallet_id'			 	  => 'Wallet ID:',
		'discount_points_to_exchange'	 	  => 'Points to exchange:',
		'discount_remaining_points'		 	  => 'Remaining points: %s (%s %s)',
		'discount_woo_value'			 	  => 'Discount value:',
		'discount_woo_exchange'			 	  => 'Exchange',
		'discount_woo_code'				 	  => 'Discount code',
		'discount_woo_g_value'			 	  => 'Value',
		'discount_woo_status'			 	  => 'Status',
		'discount_woo_used'			 		  => 'Used',
        'discount_woo_active'                 => 'Active',
        'stripe_connect_client_id_required'   => 'Please set client id for stripe connect button.',
		'stripe_connect_heading'              => 'Please click the button below to get your Stripe account ID!',
		'stripe_connect_button'               => 'Connect with Stripe',
        'stripe_successfully_charged'         => 'Successfully charged',
        'stripe_error_while_process'          => 'Error while process',
    );
    public static $labelsSettingsDescriptions = array(
        'cm_micropayment_plural_name'         => 'Points plural name',
        'cm_micropayment_singular_name'       => 'Points singular name',
        'external_currency_id'                => 'The ID of the virtual currency added to Easy Digital Downloads/WooCommerce',
        'create_wallet'                       => 'Used in "create_wallet" shortcode',
        'your_wallet_id_is'                   => 'Used in "Create Wallet" shortcode',
        'new_wallet_id_is'                    => 'Used in "Create Wallet" shortcode after creating anonymous wallet',
        'anonymous_mode_disabled'             => 'Anonymous wallets are not allowed.',
        'only_one_wallet_for_user'            => 'Only one wallet for user is avaliable.',
        'wallets_quantity_limit_reached'      => 'Quantity of wallets for user has reached limit of %d.',
        'checkout_form_headline'              => 'The headline message on top of the Checkout form',
        'format_price_in_checkout'            => 'Will show e.g. 10 points per 10 USD',
		'checkout_no_points_defined'          => 'Used in checkout',
        'checkout_form_msg_above_send_button' => 'This message will show about the send button in the Checkout form',
        'wallet_id_checkout'                  => 'Used in cm_micropayment_checkout',
        'wallet_data_entered_wallet_id'       => 'If wallet is not associated with user, label of the input where user enters his wallet name',
        'access_is_not_allowed'               => 'Used in [wallet_id] if not logged in user try to view a content',
        'no_assigned_wallet'                  => 'Used in [wallet_id] shortcode in case when there is no wallet assigned to current user',
        'last_transactions'                   => '%s is number of the transactions',
        'lo_header'                           => 'Ordinal number in the list of the transactions in wallet page',
        'transaction_date_header'             => 'The header of the transaction date column on wallet page',
        'transaction_points_header'           => 'The header of the transaction points column on wallet page',
        'transaction_comment_header'           => 'The header of the transaction comment column on wallet page',
        'transaction_type_header'             => 'The header of transaction type column on wallet page',
        'check_wallet_form_wallet_id'         => 'Used in cm_check_wallet shortcode',
        'checkout_button_send'                => 'Used in checkout on button send',
        'gateway_wallet_id'                   => 'Used in WooCommerce and EDD payment gateways',
        'gateway_wallet_id_description'       => 'Used in WooCommerce and EDD payment gateways',
        'gateway_edd_wallet_id'               => 'Used in WooCommerce and EDD payment gateways',
        'check_wallet_form_send_button_label' => 'Used in shortcode cm_check_wallet in send button',
        'granted_manually'                    => 'Type of transaction: manually granted in admin panel',
        'wallet_actual_points'                => 'In wallet page',
        'wallet_checkout_link_text'           => 'Label of the link which will be a spur customers to purchase more points',
        'withdraw'                            => 'Type of transaction: widthdraw',
        'charge'                              => 'Type of transaction: charge',
        'grant'                               => 'Type of transaction: grant',
        'transfer_to_another_wallet'          => 'Type of transaction: outgoing',
        'raceived_from_another_wallet'        => 'Type of transaction: incoming',
        'undefined_transaction_type'          => 'Phrase used when a trasaction type is unidentified',
        //'grant_paypal'					  => 'Type of transaction: granted via PayPal',
        'edd_payment'                         => 'Easy Digital Downloads payment',
        'edd_purchase_grant'                  => 'Easy Digital Downloads purchase grant',
		'from_wallet_id'                      => 'Used in transfer_wallet_points shortcode',
		'from_wallet_id_placeholder'          => 'Used in transfer_wallet_points shortcode',
		'to_wallet_id'						  => 'Used in transfer_wallet_points shortcode',
		'to_wallet_id_placeholder'		      => 'Used in transfer_wallet_points shortcode',
		'transfer_points_placeholder'		  => 'Used in transfer_wallet_points shortcode',
        'transfer_points_message_placeholder' => 'Used in transfer_wallet_points shortcode',
        'transfer_points_message_label'       => 'Comment label for transfer points page',
        'to_user'						      => '',
        'points_to_transfer'                  => '',
        'transfer_points'                     => '',
        'transfer_wallet_points_success_msg'  => '',
		'pay_with_cm_micropayment_points'	  => '',
		'discount_wallet_id'				  => 'Used in cm_micropayment_points_to_discount shortcode',
		'discount_points_to_exchange'		  => 'Used in cm_micropayment_points_to_discount shortcode',
		'discount_value'					  => 'Used in cm_micropayment_points_to_discount shortcode',
		'discount_exchange'			 		  => 'Used in cm_micropayment_points_to_discount shortcode',
		'discount_remaining_points'	 		  => 'Used in cm_micropayment_points_to_discount shortcode',
		'discount_code'				 		  => '',
		'discount_edd_value'			 	  => '',
		'discount_status'				 	  => '',
		'discount_active'				 	  => '',
		'paypal_wallet_id' 				 	  => 'Used in cm_micropayment_points_to_paypal shortcode',
		'paypal_points_to_exchange'		 	  => 'Used in cm_micropayment_points_to_paypal shortcode',
		'paypal_remaining_points'	 	 	  => 'Used in cm_micropayment_points_to_paypal shortcode',
		'paypal_payout_value'		 	 	  => '',
		'paypal_receiver_email:'	 	 	  => '',
		'paypal_exchange'			 		  => 'Used in cm_micropayment_points_to_paypal shortcode',
		'paypal_non_negative'		 	 	  => '',
		'paypal_valid_email'		 	 	  => '',
		'paypal_for_points'		 	 		  => '',
		'paypal_pp_receiver_email'	 	 	  => '',
		'paypal_error_obtaining_wallet'	 	  => '',
		'paypal_error_empty_email'		 	  => '',
		'paypal_error_correct_email'	 	  => '',
		'paypal_error_empty_points'		 	  => '',
		'paypal_error_invalid_points'	 	  => '',
		'paypal_error_not_enough_points' 	  => '',
		'paypal_payout_for_exchanging' 		  => '',
		'paypal_p_payout'					  => '',
		'stripe_remaining_points'		 	  => 'Used in cm_micropayment_points_to_stripe shortcode',
		'stripe_wallet_id'				 	  => 'Used in cm_micropayment_points_to_stripe shortcode',
		'stripe_account_id'				 	  => '',
		'stripe_points_to_exchange'		 	  => 'Used in cm_micropayment_points_to_stripe shortcode',
		'stripe_payout_value'			 	  => '',
		'stripe_error_negative_amount'	 	  => '',
		'stripe_payout_for_exchanging'	 	  => '',
		'discount_wallet_id'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_points_to_exchange'		  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_remaining_points'		 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_value'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_exchange'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_code'				 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_g_value'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_status'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_used'			 		  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
		'discount_woo_active'			 	  => 'Used in cm_micropayment_points_to_woo_discount shortcode',
        'stripe_connect_button'               => 'Connect with Stripe',
        'stripe_connect_heading'              => 'Heading above Stripe button',
        'stripe_connect_client_id_required'   => 'If client ID not set',
        'stripe_successfully_charged'         => 'Successfully charged',
        'stripe_error_while_process'          => 'Error while process',

    );

    public static function getLabel( $label ) {
        $optionName = self::OPTION_LABEL_PREFIX . $label;
        $dbLabel    = CMMicropaymentPlatform::get_option( $optionName, isset( self::$defaultLabels[ $label ] ) ? self::$defaultLabels[ $label ] : $label  );

        if ( $dbLabel == NULL ) {
            return isset( self::$defaultLabels[ $label ] ) ? self::$defaultLabels[ $label ] : '';
        }

        return $dbLabel;
    }

    public static function getLocalized( $label ) {
        return CMMicropaymentPlatformLabel::__( self::getLabel( $label ) );
    }

    public static function setLabel( $label, $value ) {
        $optionName = self::OPTION_LABEL_PREFIX . $label;
        CMMicropaymentPlatform::get_option( $optionName, $value );
    }

    public static function getDefaultLabels() {
        return self::$defaultLabels;
    }

}