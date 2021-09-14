<?php
if ( !class_exists( 'CMMPEddGateway' ) ) {

    class CMMPEddGateway {

        const GATEWAY_ID             = 'cm_micropaymnent_platform';
        const GATEWAY_NAME           = 'CM Micropayment Gateway';
        const GATEWAY_CHECKOUT_LABEL = 'CM Micropayment Gateway';
        const GATEWAY_WALLET_LABEL   = 'Wallet ID';
        const CODENAME               = 'cmmp_edd';

        public function __construct() {
            add_filter( 'edd_payment_gateways', array( __CLASS__, 'registerGateway' ) );

            $actionName = 'edd_' . self::GATEWAY_ID . '_cc_form';
            add_action( $actionName, array( __CLASS__, 'showForm' ) );

            $actionName = 'edd_gateway_' . self::GATEWAY_ID;
            add_action( $actionName, array( __CLASS__, 'processPayment' ) );

            add_filter( 'edd_settings_gateways', array( __CLASS__, 'addSettings' ) );

            $price_override = CMMicropaymentPlatform::get_option( 'cm_micropayment_edd_price_override', false );
            if ( $price_override ) {
                add_filter( 'edd_' . strtolower( CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) ) . '_currency_filter_before', array( __CLASS__, 'currencyOverride' ), 10, 3 );
                add_action( 'edd_payment_receipt_after_table', array( __CLASS__, 'tempCurrencyOverride' ), 10, 2 );
            }
        }

        static function tempCurrencyOverride( $payment, $edd_receipt_args ) {
            $currencyCode = edd_get_payment_currency_code( $payment->ID );
            $ownCurrency  = CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );
            if ( $ownCurrency == $currencyCode ) {
                add_filter( 'edd_currency', array( __CLASS__, 'currencyReplace' ), 10000 );
            }
        }

        static function currencyReplace( $currency ) {
            return CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );
        }

        static function currencyOverride( $formatted, $currency, $price ) {
            $formatted = $price . ' ' . $currency;
            return $formatted;
        }

        public static function gatewayToPointsRatio() {
            global $edd_options;

//			$ratio = CMMicropaymentPlatform::get_option( 'cmmp_edd_gateway_ratio', FALSE );
            $ratio = isset( $edd_options[ 'cmmp_edd_gateway_ratio' ] ) ? $edd_options[ 'cmmp_edd_gateway_ratio' ] : 1;
            return $ratio;
        }

        public static function gatewayAdminName() {
            global $edd_options;
//			$adminLabelOption = CMMicropaymentPlatform::get_option( 'cmmp_edd_gateway_admin_name', self::GATEWAY_NAME );
            $adminLabelOption = isset( $edd_options[ 'cmmp_edd_gateway_admin_name' ] ) ? $edd_options[ 'cmmp_edd_gateway_admin_name' ] : self::GATEWAY_NAME;
            return __( $adminLabelOption, self::CODENAME );
        }

        public static function gatewayCheckoutName() {
            global $edd_options;
//			$adminLabelOption = CMMicropaymentPlatform::get_option( 'cmmp_edd_gateway_checkout_name', self::GATEWAY_CHECKOUT_LABEL );
            $adminLabelOption = isset( $edd_options[ 'cmmp_edd_gateway_checkout_name' ] ) ? $edd_options[ 'cmmp_edd_gateway_checkout_name' ] : self::GATEWAY_CHECKOUT_LABEL;
            return __( $adminLabelOption, self::CODENAME );
        }

        public static function gatewayCheckoutWalletLabel() {
            global $edd_options;
//			$adminLabelOption = CMMicropaymentPlatform::get_option( 'cmmp_edd_gateway_wallet_label', self::GATEWAY_WALLET_LABEL );
            $adminLabelOption = isset( $edd_options[ 'cmmp_edd_gateway_wallet_label' ] ) ? $edd_options[ 'cmmp_edd_gateway_wallet_label' ] : self::GATEWAY_WALLET_LABEL;
            return __( $adminLabelOption, self::CODENAME );
        }

        /**
         * registers the gateway
         * @param type $gateways
         * @return type
         */
        public static function registerGateway( $gateways ) {
            $gateways[ self::GATEWAY_ID ] = array( 'admin_label' => self::gatewayAdminName(), 'checkout_label' => self::gatewayCheckoutName() );
            return $gateways;
        }

        public static function showForm() {
            $isUserLoggedIn = is_user_logged_in();
            $currentUser    = wp_get_current_user();
            $fieldLabel     = self::gatewayCheckoutWalletLabel();

            if ( $isUserLoggedIn && !empty( $currentUser ) ) {
                $walletCode = apply_filters( 'cm_micropayments_user_wallet_code', $currentUser->ID );

                $anonymousPayments = (int) CMMicropaymentPlatform::get_option( 'cm_micropayment_disable_anonymous_payments' );

                ob_start();

                include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
                $user_id         = get_current_user_id();
                $wallet          = CMMicropaymentPlatformFrontendWallet::instance();
                $userWallet      = $wallet->getWalletByUserID( $user_id );
                $remainingPoints = sprintf( '%d %s', $userWallet->points, ($userWallet->points > 1 ? CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' ) ) );

                $priceTotal   = edd_get_cart_total();
                $pointsAmount = self::convertToPoints( $priceTotal );
                $purchaseCost = sprintf( '%.0f %s', $pointsAmount, ($userWallet->points > 1 ? CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' ) ) );
                ?>
                <fieldset>
                    <legend><?php echo __cm( 'gateway_payment_info' ); ?></legend>
                    <?php
                    if ( !$anonymousPayments ) :
                        ?>
                        <p>
                            <label class="edd-label"><?php sprintf( '%s %s', __cm( 'wallet_id_checkout' ), __cm( 'gateway_optional' ) ); ?></label>
                            <span class="edd-description"><?php echo __cm( 'gateway_wallet_id_description' ); ?></span>
                            <input type="text" autocomplete="off" value="<?php echo $walletCode; ?>" name="wallet_id" class="edd-input" placeholder="<?php echo __cm( 'wallet_id_checkout' ); ?>" />
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
                        <label class="edd-label"><?php echo __cm( 'gateway_edd_wallet_id' ); ?></label>
                        <span class="edd-description"><?php echo __cm( 'gateway_wallet_id' ); ?></span>
                        <input type="text" autocomplete="off" name="wallet_id" class="edd-input required" placeholder="<?php echo __cm( 'wallet_id_checkout' ); ?>" />
                    </p>
                </fieldset>
                <?php
                echo ob_get_clean();
            }
        }

        public static function processPayment( $purchase_data ) {
            global $edd_options;

            /*             * ********************************
             * set transaction mode
             * ******************************** */

            if ( edd_is_test_mode() ) {
                // set test credentials here
            } else {
                // set live credentials here
            }

            /*             * ********************************
             * check for errors here
             * ******************************** */

            $walletCode = '';

            // errors can be set like this
            if ( !isset( $_POST[ 'wallet_id' ] ) || empty( $_POST[ 'wallet_id' ] ) ) {
                $areWalletsAssigned = apply_filters( 'cm_micropayments_are_wallets_assigned', CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer', 0 ) );

                if ( !$areWalletsAssigned ) {
                    // error code followed by error message
                    edd_set_error( 'empty_card', __cm( 'gateway_empty_cart' ) );
                } else {
                    $currentUser = wp_get_current_user();
                    if ( $currentUser && $currentUser->ID ) {
                        $walletCode = apply_filters( 'cm_micropayments_user_wallet_code', $currentUser->ID );
                    }
                }
            } else {
                $walletCode = $_POST[ 'wallet_id' ];
            }

            /*
             * Try to get the wallet object
             */
            $wallet = apply_filters( 'cm_micropayments_get_wallet_by_code', $walletCode );

            if ( !empty( $wallet ) && (is_array( $wallet ) || is_object( $wallet )) ) {
                $ratio = self::gatewayToPointsRatio();
                if ( is_numeric( $ratio ) ) {
                    $pointsAmount = self::convertToPoints( $purchase_data[ 'price' ] );
                    if (!CMMicropaymentPlatform::numericOrFloat( $pointsAmount ) ) {

                        /*
                         *  error code followed by error message
                         */
                        edd_set_error( 'ratio_error', __cm( 'gateway_ratio_error' ) );
                    } else {
	                    $pointsAmount =  CMMicropaymentPlatform::convertType($pointsAmount);
                        $hasEnoughPoints = apply_filters( 'wallet_has_enough_points', array( 'wallet_id' => $wallet->wallet_name, 'points' => $pointsAmount ) );
                        if ( !$hasEnoughPoints || !$hasEnoughPoints[ 'success' ] ) {
                            /*
                             *  error code followed by error message
                             */
                            edd_set_error( 'not_enough_points', __cm( 'gateway_not_enough_points' ) );
                        }
                    }
                }
            } else {
                edd_set_error( 'wallet_error', __cm( 'gateway_wallet_error' ) );
            }

            // check for any stored errors
            $errors = edd_get_errors();
            if ( !$errors ) {
                $purchase_summary = edd_get_purchase_summary( $purchase_data );

                /*                 * ********************************
                 * setup the payment details
                 * ******************************** */

                $price_override = CMMicropaymentPlatform::get_option( 'cm_micropayment_edd_price_override', false );
                if ( $price_override ) {
                    $purchase_data[ 'price' ]  = $pointsAmount;
//                    $edd_options[ 'currency' ] = 'CMMP';
                    $edd_options[ 'currency' ] = CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );

                    if ( !empty( $purchase_data[ 'cart_details' ] ) ) {
                        foreach ( $purchase_data[ 'cart_details' ] as $key => $value ) {
                            $purchase_data[ 'cart_details' ][ $key ][ 'item_price' ] = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'item_price' ] );
                            $purchase_data[ 'cart_details' ][ $key ][ 'subtotal' ]   = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'subtotal' ] );
                            $purchase_data[ 'cart_details' ][ $key ][ 'price' ]      = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'price' ] );

                            $purchase_data[ 'cart_details' ][ $key ][ 'discount' ] = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'discount' ] );
                            $purchase_data[ 'cart_details' ][ $key ][ 'tax' ]      = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'tax' ] );
                            $purchase_data[ 'cart_details' ][ $key ][ 'fees' ]     = self::convertToPoints( $purchase_data[ 'cart_details' ][ $key ][ 'fees' ] );
                        }
                    }
                }

                $paymentArgs = array(
                    'price'        => $purchase_data[ 'price' ],
                    'date'         => $purchase_data[ 'date' ],
                    'user_email'   => $purchase_data[ 'user_email' ],
                    'purchase_key' => $purchase_data[ 'purchase_key' ],
                    'currency'     => isset( $edd_options[ 'currency' ] ) ? $edd_options[ 'currency' ] : 'USD',
                    'downloads'    => $purchase_data[ 'downloads' ],
                    'cart_details' => $purchase_data[ 'cart_details' ],
                    'user_info'    => $purchase_data[ 'user_info' ],
                    'status'       => 'pending'
                );

                // record the pending payment
                $payment = edd_insert_payment( $paymentArgs );

                /*
                 * Processing the payment
                 */
                $result = apply_filters( 'withdraw_wallet_points', array( 'wallet_id' => $wallet->wallet_name, 'points' => $pointsAmount, 'type' => CMMicropaymentPlatformWalletCharges::TYPE_EDD_PAYMENT_CHARGE, 'comment'   => 'EDD product purchase' ));

                // if the merchant payment is complete, set a flag
                $merchant_payment_confirmed = (!empty( $result ) && $result[ 'success' ]);

                if ( $merchant_payment_confirmed ) { // this is used when processing credit cards on site
                    // once a transaction is successful, set the purchase to complete
                    edd_update_payment_status( $payment, 'complete' );

                    // go to the success page
                    edd_send_to_success_page();
                } else {
                    $fail = true; // payment wasn't recorded
                }
            } else {
                $fail = true; // errors were detected
            }

            if ( $fail !== false ) {
                // if errors are present, send the user back to the purchase page so they can be corrected
                edd_send_back_to_checkout( '?payment-mode=' . $purchase_data[ 'post_data' ][ 'edd-gateway' ] );
            }
        }

        /**
         * adds the settings to the Payment Gateways section
         */
        public static function addSettings( $settings ) {
            $sample_gateway_settings = array(
                array(
                    'id'   => self::GATEWAY_ID . '_settings',
                    'name' => '<strong>' . __( self::gatewayAdminName() . ' Settings', self::CODENAME ) . '</strong>',
                    'desc' => __( 'Configure the gateway settings', self::CODENAME ),
                    'type' => 'header'
                ),
                array(
                    'id'   => 'cmmp_edd_gateway_admin_name',
                    'name' => __( 'Gateway Admin Name', self::CODENAME ),
                    'desc' => __( 'The name of the Gateway which will be displayed in this view.', self::CODENAME ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => self::gatewayAdminName()
                ),
                array(
                    'id'   => 'cmmp_edd_gateway_checkout_name',
                    'name' => __( 'Gateway Checkout Name', self::CODENAME ),
                    'desc' => __( 'The name of the Gateway which will be displayed in the Checkout', self::CODENAME ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => self::gatewayCheckoutName()
                ),
                array(
                    'id'   => 'cmmp_edd_gateway_wallet_label',
                    'name' => __( 'Checkout Wallet Label', self::CODENAME ),
                    'desc' => __( 'The label of the Wallet field in the checkout', self::CODENAME ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => self::gatewayCheckoutWalletLabel()
                ),
                array(
                    'id'   => 'cmmp_edd_gateway_ratio',
                    'name' => __( 'Currency to Points Ratio', self::CODENAME ),
                    'desc' => __( 'The number of points 1 unit of the currency is worth. For instance "4" means that e.g. $1 = 4 points.', self::CODENAME ),
                    'type' => 'text',
                    'size' => 'regular',
                    'std'  => 1
                )
            );

            return array_merge( $settings, $sample_gateway_settings );
        }

        /**
         * Converts the price in any currency to the points
         * @param type $purchase_data
         * @return type
         */
        public static function convertToPoints( $price ) {
            $priceNumeric = (float) $price;

            $ratio = self::gatewayToPointsRatio();

            $pointPrice = $priceNumeric * $ratio;
            return $pointPrice;
        }

    }

}
$cmmp_edd_gateway = new CMMPEddGateway();
