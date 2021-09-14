<?php
add_action( 'plugins_loaded', 'woocommerce_gateway_name_init', 0 );

function woocommerce_gateway_name_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/**
	 * Gateway class
	 */
	class WC_CM_Micro_Payment extends WC_Payment_Gateway {

		public function __construct() {

			$label_pay_with_cm_micropayment_points = CMMicropaymentPlatformLabel::getLabel( 'pay_with_cm_micropayment_points' );

			$this->id                 = 'cm-micropayment-gateway';
			$this->has_fields         = true;
			$this->order_button_text  = $this->get_option( 'button_text', __( $label_pay_with_cm_micropayment_points, 'cm-micropayment-platform' ) );
			$this->method_title       = __( 'CM MicroPayment', 'cm-micropayment-platform' );
			$this->method_description = __( 'Allow users to pay with the CM MicroPyament Points.', 'cm-micropayment-platform' );
			$this->supports           = array(
				'products',
			);

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {

			$label_pay_with_cm_micropayment_points = CMMicropaymentPlatformLabel::getLabel( 'pay_with_cm_micropayment_points' );

			$this->form_fields = array(
				'enabled'       => array(
					'title'   => __( 'Enable/Disable', 'cm-micropayment-platform' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Payments with CM Micropayment Points', 'cm-micropayment-platform' ),
					'default' => 'yes'
				),
				'title'         => array(
					'title'       => __( 'Title', 'cm-micropayment-platform' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'cm-micropayment-platform' ),
					'default'     => __( 'CM MicroPayment', 'cm-micropayment-platform' ),
					'desc_tip'    => true,
				),
				'button_text'   => array(
					'title'       => __( 'Checkout Button Text', 'cm-micropayment-platform' ),
					'type'        => 'text',
					'description' => __( 'This controls the text on the button on the checkout.', 'cm-micropayment-platform' ),
					'default'     => __( $label_pay_with_cm_micropayment_points, 'cm-micropayment-platform' ),
					'desc_tip'    => true,
				),
//				'description'	 => array(
//					'title'		 => __( 'Customer Message', 'cm-micropayment-platform' ),
//					'type'		 => 'textarea',
//					'default'	 => ''
//				),
				'gateway_ratio' => array(
					'title'       => __( 'Currency to Points Ratio', 'cm-micropayment-platform' ),
					'type'        => 'text',
					'description' => __( 'The number of points 1 unit of the currency is worth. For instance "4" means that e.g. $1 = 4 points.', 'cm-micropayment-platform' ),
					'default'     => 1
				)
			);
		}

		public function payment_fields() {
			$isUserLoggedIn = is_user_logged_in();
			$currentUser    = wp_get_current_user();

			if ( $isUserLoggedIn && ! empty( $currentUser ) ) {
				$walletCode = apply_filters( 'cm_micropayments_user_wallet_code', $currentUser->ID );

				$anonymousPayments = (int) CMMicropaymentPlatform::get_option( 'cm_micropayment_disable_anonymous_payments' );

				ob_start();

				include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
				$user_id         = get_current_user_id();
				$wallet          = CMMicropaymentPlatformFrontendWallet::instance();
				$userWallet      = $wallet->getWalletByUserID( $user_id );
				$remainingPoints = sprintf( '%d %s', $userWallet->points ?? 0, ( ( $userWallet->points ?? 0 ) > 1 ? CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' ) ) );

				$priceTotal   = $this->get_order_total();
				$pointsAmount = $this->convertToPoints( $priceTotal );
				$purchaseCost = sprintf( '%.0f %s', $pointsAmount, ( ( $userWallet->points ?? 0 ) > 1 ? CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' ) ) );
				?>
                <fieldset>
                    <legend><?php echo __cm( 'gateway_payment_info' ); ?></legend>
					<?php
					if ( ! $anonymousPayments ) :
						?>
                        <p>
                            <label class="woo-label"><?php sprintf( '%s %s', __cm( 'wallet_id_checkout' ), __cm( 'gateway_optional' ) ); ?></label>
                            <span class="woo-description"><?php echo __cm( 'gateway_wallet_id_description' ); ?></span>
                            <input type="text" autocomplete="off" value="<?php echo $walletCode; ?>" name="wallet_id"
                                   class="woo-input" placeholder="<?php echo __cm( 'wallet_id_checkout' ); ?>"/>
                        </p>
					<?php endif; ?>
                    <p>
						<?php echo sprintf( '%s %s', __cm( 'gateway_purchase_cost' ), trim( $purchaseCost ) ); ?>
                    </p>
                    <p>
						<?php echo sprintf( '%s %s', __cm( 'gateway_remaining_points_in_wallet' ), trim( $remainingPoints ) ); ?>
                    </p>
                </fieldset>
				<?php
				echo ob_get_clean();
			} else {
				ob_start();
				?>
                <fieldset>
                    <legend><?php echo __cm( 'gateway_payment_info' ); ?></legend>
                    <p>
                        <label class="woo-label"><?php echo __cm( 'wallet_id_checkout' ); ?></label>
                        <span class="woo-description"><?php echo __cm( 'gateway_wallet_id' ); ?></span>
                        <input type="text" autocomplete="off" name="wallet_id" class="woo-input required"
                               placeholder="<?php echo __cm( 'wallet_id_checkout' ); ?>"/>
                    </p>
                </fieldset>
				<?php
				echo ob_get_clean();
			}
		}

		public function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );

			$payment_ok = $this->process_point_payment( $order );

			$order->add_order_note( __( 'Initializing Payment with CM MicroPayment Points', 'cm-micropayment-platform' ) );

			if ( $payment_ok ) {
				$order->payment_complete();

				// Return thankyou redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			} else {
				/*
				 * Notices are already set, they should be displayed
				 */
				return;
			}
		}

		protected function process_point_payment( $order ) {
			//var_dump( $order->get_meta_data('_dokan_vendor_id') );



			$pointsAmount = null;
			$walletCode   = '';
			$walletId     = filter_input( INPUT_POST, 'wallet_id' );

			// errors can be set like this
			if ( ! isset( $walletId ) || empty( $walletId ) ) {
				$areWalletsAssigned = apply_filters( 'cm_micropayments_are_wallets_assigned', CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer', 0 ) );

				if ( ! $areWalletsAssigned ) {
					// error code followed by error message
					wc_add_notice( __cm( 'gateway_empty_cart' ), 'error' );
				} else {
					$currentUser = wp_get_current_user();
					if ( $currentUser && $currentUser->ID ) {
						$walletCode = apply_filters( 'cm_micropayments_user_wallet_code', $currentUser->ID );
					}
				}
			} else {
				$walletCode = $walletId;
			}

			/*
			 * Try to get the wallet object
			 */
			$wallet = apply_filters( 'cm_micropayments_get_wallet_by_code', $walletCode );

			if ( ! empty( $wallet ) && ( is_array( $wallet ) || is_object( $wallet ) ) ) {
				$ratio = $this->get_option( 'gateway_ratio' );

				if ( is_numeric( $ratio ) ) {
					$pointsAmount = self::convertToPoints( $order->get_total() );
					if ( ! is_numeric( $pointsAmount ) ) {
						/*
						 *  error code followed by error message
						 */
						wc_add_notice( __cm( 'gateway_ratio_error' ), 'error' );
					} else {
						$hasEnoughPoints = apply_filters( 'wallet_has_enough_points', array(
							'wallet_id' => $wallet->wallet_name,
							'points'    => $pointsAmount
						) );
						if ( ! $hasEnoughPoints || ! $hasEnoughPoints['success'] ) {
							/*
							 *  error code followed by error message
							 */
							wc_add_notice( __cm( 'gateway_not_enough_points' ), 'error' );
						}
					}
				}
			} else {
				wc_add_notice( __cm( 'gateway_wallet_error' ), 'error' );
			}

			// check for any stored errors
			$errors = wc_get_notices();
			if ( empty( $errors ) && ( is_numeric( $pointsAmount ) || is_float( $pointsAmount ) ) && $pointsAmount >= 0 ) {

				/*
                 * Processing the payment
                 */

				if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_to_admin_or_seller' ) == '1' ) {
					$aresult = apply_filters( 'cmmt_grant_for_purchase', array(
						'user_id' => CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_to_admin' ),
						'points'  => $pointsAmount,
						'type'    => CMMicropaymentPlatformWalletCharges::TYPE_WOO_PURCHASE_GRANT
					) );
				}


				$result = apply_filters( 'withdraw_wallet_points', array(
					'wallet_id' => $wallet->wallet_name,
					'points'    => $pointsAmount,
					'type'      => CMMicropaymentPlatformWalletCharges::TYPE_WOO_PAYMENT_CHARGE,
					'comment'   => 'Woo product purchase'
				) );

				// if the merchant payment is complete, set a flag
				$merchant_payment_confirmed = ( ! empty( $result ) && $result['success'] );


				return $merchant_payment_confirmed;
			}

			/*
			 * Something went wrong
			 */

			return;
		}

		/**
		 * Converts the price in any currency to the points
		 *
		 * @param type $purchase_data
		 *
		 * @return type
		 */
		public function convertToPoints( $amount ) {
			$priceNumeric = (float) $amount;
			$ratio        = $this->gatewayToPointsRatio();

			if ( $ratio < 0 ) {
				$ratio = 1;
			}
			$pointPrice = $priceNumeric * $ratio;

			return $pointPrice;
		}

		public function gatewayToPointsRatio() {
			$ratio = $this->get_option( 'gateway_ratio' );

			if ( function_exists( 'wc_memberships_is_user_member' ) ) {
				$isMember = wc_memberships_is_user_member( get_current_user_id() );
				if ( $isMember ) {
					$memberships = wc_memberships_get_user_active_memberships( get_current_user_id() );
					if ( ! empty( $memberships ) ) {
						$membership   = reset( $memberships );
						$member_ratio = get_post_meta( $membership->plan_id, '_cmmp_membership_ratio', true );
						if ( ! empty( $member_ratio ) ) {
							$ratio = $member_ratio;
						}
					}
				}
			}

			return $ratio;
		}

	}

	/**
	 * Add the Gateway to WooCommerce
	 * */
	function woocommerce_add_gateway_name_gateway( $methods ) {
		$methods[] = 'WC_CM_Micro_Payment';

		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_name_gateway' );
}
