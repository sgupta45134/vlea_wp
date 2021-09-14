<?php
if ( !class_exists( 'CMMPPaypalPayout' ) ) {

    class CMMPPaypalPayout {

        public static function init() {
            add_filter( 'cmmp_wallet_row_actions', array( __CLASS__, 'addPayoutAction' ) );
            add_filter( 'cmmp_wallet_render_js_data', array( __CLASS__, 'addPayoutJSArgs' ) );
            add_action( 'wp_ajax_cm_micropayment_platform_paypal_payout', array( __CLASS__, 'paypalPayout' ) );
            add_action( 'wp_ajax_nopriv_cm_micropayment_platform_paypal_payout', array( __CLASS__, 'paypalPayout' ) );

            add_shortcode( 'cm_micropayment_points_to_paypal', array( __CLASS__, 'exchangePointsForPayPalForm' ) );
            add_action( 'init', array( __CLASS__, 'exchangePointsForPayPal' ) );
        }

        public static function exchangePointsForPayPalForm( $args = array() ) {
            $result = '';

            $featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_payout_enabled', false );
            if ( !$featureEnabled ) {
                return $result;
            }

            $postArr = filter_input_array( INPUT_POST );

            $walletObj         = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getCurrentUserWallet();

            $currentUserWalletPoints      = 0;
            $currentUserWalletPointsValue = 0;

            $pointsToExchange = isset( $postArr[ 'cmmp_points_to_payout' ] ) ? $postArr[ 'cmmp_points_to_payout' ] : 0;
            $payoutValue      = 0;
            $currency         = ((CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency' ) != '') ? CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency' ) : 'USD');
            $currencySymbol   = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
            $paymentThreshold = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_threshold', '0' );
            if ( !empty( $currencySymbol ) ) {
                $currency = $currencySymbol;
            }
            $payoutEmail = isset( $postArr[ 'cmmp_payout_email' ] ) ? $postArr[ 'cmmp_payout_email' ] : '';

            if ( $currentUserWallet ) {
                $currentUserWalletId     = $currentUserWallet->wallet_name;
                $currentUserWalletPoints = $currentUserWallet->points;

                $user        = get_user_by( 'id', $currentUserWallet->user_id );
                $payoutEmail = $user->user_email;

                $currentUserWalletPointsValue = self::calculateAmount( $currentUserWalletPoints );
                $payoutValue                  = self::calculateAmount( $pointsToExchange );
                $ratio                        = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_points_ratio', 10 );
            }

            ob_start();
            if(isset($GLOBALS["exchangePointsForPayPal_return"])) {
                if($GLOBALS["exchangePointsForPayPal_return"] === true){
                    $GLOBALS["exchangePointsForPayPal_return"] = 'Exchange was successful!';
                }
                echo '<strong class="payout_message">'.$GLOBALS["exchangePointsForPayPal_return"].'</strong>';
                echo "<br><br>";
            }
            ?>
            <form method="post" id="cmmp_exchange_points_for_discount_form">
                <input type="hidden" name="cmmp_paypal_payout_nonce" value="<?php echo wp_create_nonce( 'cmmp_paypal_payout' ); ?>" />
                <input type="hidden" name="cmmp_paypal_payout_ratio" id="cmmp_paypal_payout_ratio" value="<?php echo $ratio ?>" />
                <div id="cmmt-wallet-id-field">
                    <label>
                        <?php echo __cm( 'paypal_wallet_id' ); ?>
                        <input type="text" size="36" name="cmmp_wallet_id" value="<?php echo $currentUserWalletId; ?>" />
                    </label>
                </div>
                <div id="cmmt-points-to-exchange-field">
                    <label>
                        <?php echo __cm( 'paypal_points_to_exchange' ); ?>
                        <input class="cmmp_points_to_payout" type="text" size="4" name="cmmp_points_to_payout" value="<?php echo $pointsToExchange; ?>" />
                    </label>
                    <span>
                        <?php echo sprintf( __cm( 'paypal_remaining_points' ), $currentUserWalletPoints, $currentUserWalletPointsValue, $currency ); ?>
                    </span>
                </div>
                <div id="cmmt-payout-value-field">
                    <span><?php echo __cm( 'paypal_payout_value' ); ?></span>
                    <span id="cmmt_payout_value_placeholder"><?php echo $payoutValue; ?></span>
                    <span><?php echo $currency; ?></span>
                </div>
                <div id="cmmp-payout-email-field">
                    <span><?php echo __cm( 'paypal_receiver_email' ); ?></span>
                    <input class="cmmp_payout_email" type="text" size="30" name="cmmp_payout_email" value="<?php echo $payoutEmail; ?>" />
                </div>

                <?php do_action('cmmp_paypal_payout_before_threshold'); ?>

                <?php if(!empty($paymentThreshold)): ?>
                <?php error_log(gettype($paymentThreshold)); ?>
                    <div id="cmmp-payout-threshold-notification">
                        <?php echo sprintf( __cm( 'paypal_payout_threshold' ), $paymentThreshold ); ?>
                    </div>
                <?php endif; ?>

                <input type="submit" name="cmmp_paypal_payout" value="<?php echo __cm( 'paypal_exchange' ); ?>" style="cursor:pointer;" />
            </form>
            <script>

                jQuery( '#cmmp_exchange_points_for_discount_form .cmmp_points_to_payout' ).on( 'keyup', function () {
                    var discountValue = 0;
                    var ratio = jQuery( '#cmmp_paypal_payout_ratio' ).val();
                    var currentValue = jQuery( this ).val();

                    if ( !isNaN( parseFloat( currentValue ) ) && isFinite( currentValue ) && currentValue > 0 )
                    {
                        discountValue = ( currentValue / ratio );
                        discountValue = discountValue.toFixed( 2 );

                        <?php if(!empty($paymentThreshold)): ?>
                        if (discountValue < <?php echo $paymentThreshold; ?> ){
                            jQuery('input[type="submit"]').attr("disabled", true);
                            jQuery('#cmmp-payout-threshold-notification').show();
                        } else {
                            jQuery('input[type="submit"]').attr("disabled", false);
                            jQuery('#cmmp-payout-threshold-notification').hide();
                        }
                        <?php endif; ?>
                    }
                    jQuery( '#cmmt_payout_value_placeholder' ).html( discountValue );
                } );
            </script>
            <?php
            $result .= ob_get_clean();

            return $result;
        }

        public static function exchangePointsForPayPal() {
            $return  = array();
            $postArr = filter_input_array( INPUT_POST );

            // verify nonce
            if ( !isset( $postArr[ 'cmmp_paypal_payout_nonce' ] ) || !wp_verify_nonce( $postArr[ 'cmmp_paypal_payout_nonce' ], 'cmmp_paypal_payout' ) ) {
                return;
            }

            $walletId = $postArr[ 'cmmp_wallet_id' ];
            $amount   = $postArr[ 'cmmp_points_to_payout' ];
            $email    = $postArr[ 'cmmp_payout_email' ];

            if ( $amount <= 0 ) {
                $return[ 'error' ] = __cm( 'paypal_non_negative' );
                return $return;
            }

            if ( !is_email( $email ) ) {
                $return[ 'error' ] = __cm( 'paypal_valid_email' );
                return $return;
            }

            $args = array(
                'points'    => $amount,
                'wallet_id' => $walletId,
                'email'     => $email,
            );


//			$walletObj = CMMicropaymentPlatformFrontendWallet::instance();
//			$currentUserWallet	 = $walletObj->getWalletByCode( $walletId );
//
//			if ( empty( $currentUserWallet ) ) {
//				return;
//			}
//
//			$user = get_user_by( 'id', $currentUserWallet->user_id );
//			if ( empty( $user ) ) {
//				return;
//			}
//
//			$args[ 'email' ] = $user->user_email;

            $transactionResult = self::createWordpressTransaction( $args );

            //echo "<pre>"; print_r($transactionResult); echo "<pre>";
            if ( !empty( $transactionResult[ 'error' ] ) ) {
                $return = $transactionResult[ 'error' ];
                if ( !empty( $transactionResult[ 'error_details' ]->error_data[ '403' ][ 'name' ] ) ) {
                    $return .= ' ('.$transactionResult[ 'error_details' ]->error_data[ '403' ][ 'name' ].': '.$transactionResult[ 'error_details' ]->error_data[ '403' ][ 'message' ].')';
                }
            } else {
                /*
                 * Everything's fine
                 */
                if ( !empty( $transactionResult[ 'success' ] ) && !empty( $transactionResult[ 'points' ] ) ) {
                    $return = $transactionResult[ 'success' ];
                }
            }


            $GLOBALS["exchangePointsForPayPal_return"] = $return;
        }

        public static function addPayoutAction( $rowActions ) {
            $rowActions[ 'paypal_payout' ] = '<a href="#" class="inlinePayoutButton" title="' . __cm( 'paypal_payout' ) . '">' . __cm( 'paypal_payout' ) . '</a>';
            return $rowActions;
        }

        public static function addPayoutJSArgs( $jsData ) {
            $jsData[ 'paypal_payout_ajaxurl' ]        = admin_url( 'admin-ajax.php?action=cm_micropayment_platform_paypal_payout' );
            $jsData[ 'paypal_payout_nonce' ]          = wp_create_nonce( -1 );
            $jsData[ 'l18n' ][ 'payout_label' ]       = __cm( 'paypal_for_points' );
            $jsData[ 'l18n' ][ 'payout_email_label' ] = __cm( 'paypal_pp_receiver_email' );
            return $jsData;
        }

        public static function paypalPayout() {
            $return = array();

            $post        = filter_input_array( INPUT_POST );
            $verifyNonce = wp_verify_nonce( $post[ '_cmmp_paypal_payout' ] );
            if ( !empty( $post ) && $verifyNonce ) {

                require_once CMMP_PLUGIN_DIR . '/backend/classes/cmmp-backend-wallets.php';
                $component = new CMMicropaymentPlatformBackendWallets();
                $wallet    = $component->getWallet( $post[ 'wallet_id' ] );

                if ( empty( $wallet ) ) {
                    $return[ 'error' ] = __cm( 'paypal_error_obtaining_wallet' );
                }

                if ( empty( $post[ 'email' ] ) ) {
                    $return[ 'error' ] = __cm( 'paypal_error_empty_email' );
                }

                if ( !is_email( $post[ 'email' ] ) ) {
                    $return[ 'error' ] = __cm( 'paypal_error_correct_email' );
                }

                if ( empty( $post[ 'points' ] ) ) {
                    $return[ 'error' ] = __cm( 'paypal_error_empty_points' );
                }

                if ( !is_numeric( $post[ 'points' ] ) || $post[ 'points' ] < 0 ) {
                    $return[ 'error' ] = __cm( 'paypal_error_invalid_points' );
                }

                if ( (int) $post[ 'points' ] > (int) $wallet->points ) {
                    $return[ 'error' ] = __cm( 'paypal_error_not_enough_points' );

                }

                if ( empty( $return[ 'error' ] ) ) {
                    $post[ 'wallet_id' ] = $wallet->wallet_name;
                    $transactionResult   = self::createWordpressTransaction( $post );
                    if ( !empty( $transactionResult[ 'error' ] ) ) {
                        unset( $transactionResult[ 'success' ] );
                        $return = $transactionResult;
                    } else {
                        /*
                         * Everything's fine
                         */
                        if ( !empty( $transactionResult[ 'success' ] ) && !empty( $transactionResult[ 'points' ] ) ) {
                            $return = $transactionResult;
                        }
                    }
                }
            } else {
                $return[ 'error' ] = __cm( 'paypal_error' );
            }

            echo json_encode( $return );
            die();
        }

        public static function createWordpressTransaction( $args ) {
            global $wpdb;

            $walletObj = CMMicropaymentPlatformWallet::instance();
            $wallet    = $walletObj->getWalletByCode( $args[ 'wallet_id' ] );
            if ( empty( $wallet ) ) {
                return array( 'error' => 'No wallet' );
            }

            if ( !class_exists( 'CMMicropaymentPlatformWalletCharges' ) ) {
                require_once CMMP_PLUGIN_DIR . '/shared/models/wallet-charges.php';
            }
            $walletTransactions = new CMMicropaymentPlatformWalletCharges();

            $pointsToSubtract = isset( $args[ 'points' ] ) ? $args[ 'points' ] : 0;
            if ( empty( $pointsToSubtract ) ) {
                return array( 'error' => 'Points amount must be bigger than 0' );
            }

            $points = $wallet->points - $pointsToSubtract;
            if ( $points < 0 ) {
                return array( 'error' => 'Wallet does not have enough points' );
            }

            $wpdb->query( 'START TRANSACTION' );
            $walletObj->setPoints( $wallet->wallet_name, $points );

            $transactionType   = CMMicropaymentPlatformWalletCharges::TYPE_PAYPAL_PAYOUT;
            $transactionStatus = !empty( $args[ 'status' ] ) ? $args[ 'status' ] : 1;

            $walletTransactions->log( $pointsToSubtract, 0, $wallet->wallet_id, $transactionType, $transactionStatus );

            $sendNotificationEmail = CMMicropaymentPlatform::get_option( 'cm_micropayment_send_paypal_payout_notifications', FALSE );
            if ( $sendNotificationEmail ) {
                CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_paypal_payout', array( 'amountPoints' => $pointsToSubtract ) );
            }

            $payoutAmout = self::calculateAmount( $pointsToSubtract );
            $paymentArgs = array(
                'amount'      => $payoutAmout,
                'email'       => $args[ 'email' ],
                'description' => sprintf( __cm( 'paypal_payout_for_exchanging' ), $pointsToSubtract, CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name', 'points' ), $args[ 'wallet_id' ], $payoutAmout ),
                'wallet_id'   => $args[ 'wallet_id' ],
            );

            $errorDetails = '';
            $errorMessage = '.';
            $payoutResult = self::send_payment( $paymentArgs );

            if ( !is_wp_error( $payoutResult ) ) {
                $wpdb->query( 'COMMIT' );

                $wallet = $walletObj->getWalletByCode( $args[ 'wallet_id' ] );
                return array( 'success' => true, 'points' => $wallet->points );
            } else {
                $errorMessage = ': ' . $payoutResult->get_error_message();
                $errorDetails = $payoutResult->get_error_data();
                $wpdb->query( 'ROLLBACK' );
            }

            return array( 'error' => 'PayPal Payout transaction has failed' . $errorMessage, 'error_details' => $payoutResult );
        }

        /**
         * Changes the points to amount based on ratio
         * @param type $points
         */
        public static function calculateAmount( $points ) {
            $ratio = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_points_ratio', 10 );
            if ( !is_numeric( $ratio ) || $ratio <= 0 ) {
                $amount = 0;
            } else {
                $amount = $points / $ratio;
            }
            return number_format( $amount, 2 );
        }

        /**
         * Process a single referral payment
         *
         * @access public
         * @since 1.1
         * @return bool|WP_Error
         */
        public static function send_payment( $args = array() ) {

            $sandbox       = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_test_mode', false );
            $sandboxPrefix = $sandbox ? 'sandbox.' : '';
            $token         = self::get_token();
			$currency = CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency', 'USD');
            if ( is_wp_error( $token ) ) {
                return $token;
            }

            $plural = CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' );

            //$requestUrl  = 'https://api.' . $sandboxPrefix . 'paypal.com/v1/payments/payouts?sync_mode=true';
            $requestUrl  = 'https://api.' . $sandboxPrefix . 'paypal.com/v1/payments/payouts';
            $requestData = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $token->access_token,
                ),
                'timeout' => 45,
                'body'    => json_encode( array(
                    'sender_batch_header' => array(
                        'email_subject' => sprintf( __cm( 'paypal_p_payout' ), $plural )
                    ),
                    'items'               => array(
                        array(
                            'recipient_type' => 'EMAIL',
                            'amount'         => array(
                                'value'    => $args[ 'amount' ],
                                'currency' => $currency
                            ),
                            'receiver'       => $args[ 'email' ],
                            'note'           => $args[ 'description' ],
                            'sender_item_id' => substr( sha1( $args[ 'wallet_id' ] ), 0, 30 )
                        )
                    )
                ) )
            );

            $request = wp_remote_post( $requestUrl, $requestData );

            if ( is_wp_error( $request ) ) {
                $error_data = $request->get_error_data();
                if ( empty( $error_data ) ) {
                    $request->add_data( array(
                        'request_url'  => $requestUrl,
                        'request_data' => $requestData,
                    ) );
                }
                return $request;
            }

            $api_response = json_decode( wp_remote_retrieve_body( $request ), true );
            /*
             * If for some reason it's empty it seems there's a bigger problem and we may want to at least see the whole request
             */
            if ( empty( $api_response ) ) {
                $api_response = $request;
            }

            if ( 201 == $request[ 'response' ][ 'code' ] && 'Created' == $request[ 'response' ][ 'message' ] ) {

                if ( !empty( $api_response[ 'items' ][ 0 ][ 'errors' ][ 'message' ] ) ) {
                    $error_code    = $api_response[ 'items' ][ 0 ][ 'errors' ][ 'name' ];
                    $error_message = $api_response[ 'items' ][ 0 ][ 'errors' ][ 'message' ];
                    return new WP_Error( $error_code, $error_message, $api_response );
                } else {
                    return true;
                }
            } else {
                return new WP_Error( $request[ 'response' ][ 'code' ], $request[ 'response' ][ 'message' ], $api_response );
            }
        }

        /**
         * Retrieve an API access token
         *
         * @access private
         * @since 1.0
         * @return object|WP_Error
         */
        private static function get_token() {

            $sandbox       = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_test_mode', false );
            $sandboxPrefix = $sandbox ? 'sandbox.' : '';

            $prefix              = $sandbox ? 'test_' : '';
            $credentialsClientId = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_' . $prefix . 'client_id' );
            $credentialsSecret   = CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_' . $prefix . 'app_secret' );

            $requestUrl  = 'https://api.' . $sandboxPrefix . 'paypal.com/v1/oauth2/token';
            $requestData = array(
                'headers' => array(
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'en_US',
                    'Authorization'   => 'Basic ' . base64_encode( $credentialsClientId . ':' . $credentialsSecret )
                ),
                'timeout' => 45,
                'body'    => array(
                    'grant_type' => 'client_credentials'
                )
            );

            $requestDetails = array(
                'request_url'  => $requestUrl,
                'request_data' => $requestData,
            );

            $request = wp_remote_post( $requestUrl, $requestData );

            if ( is_wp_error( $request ) ) {
                $error_data = $request->get_error_data();
                if ( empty( $error_data ) ) {
                    $request->add_data( $requestDetails );
                }
                return $request;
            }

            if ( 200 == $request[ 'response' ][ 'code' ] && 'OK' == $request[ 'response' ][ 'message' ] ) {

                return json_decode( $request[ 'body' ] );
            } else {

                $body = json_decode( $request[ 'body' ] );

                if ( !empty( $body->error ) ) {

                    $code  = $body->error;
                    $error = $body->error_description;
                } else {

                    $code  = $request[ 'response' ][ 'code' ];
                    $error = $request[ 'response' ][ 'message' ];
                }

                return new WP_Error( $code, $error, array( 'request' => $request, 'request_details' => $requestDetails ) );
            }
        }

    }

}

CMMPPaypalPayout::init();
