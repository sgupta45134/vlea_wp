<?php

if ( !class_exists('Stripe\Stripe') ) {
	include CMMP_PLUGIN_DIR . '/shared/libs/stripe-php/init.php';
}

//write stripe code here

if ( !class_exists( 'CMMPStripePayout' ) ) {

    class CMMPStripePayout {

        public static function init() {
            add_shortcode( 'cm_micropayment_points_to_stripe', array( __CLASS__, 'exchangePointsForStripeForm' ) );
        }

        public static function exchangePointsForStripeForm( $args = array() ) {

            $test_mode = (bool) CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_test_mode', 0 );

            $stripe = array(
                "secret_key"      => ($test_mode) ? CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_secret_test_key', '' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_secret_key', '' ),
                "publishable_key" => ($test_mode) ? CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_publishable_test_key', '' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_publishable_key', '' ),
                "client_id"       => ($test_mode) ? CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_client_test_id', '' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_client_id', '' )
            );

            if(empty($stripe['secret_key']) || empty($stripe['publishable_key'])){
                return "Please set secret and publishable keys";
            }
              
            try{
                \Stripe\Stripe::setApiKey($stripe['secret_key']);
            } catch (Exception $e) {
              // Since it's a decline, Stripe_CardError will be caught
              $body = $e->getJsonBody();
              $err  = $body['error'];

              echo '<div class="cm-micropayment-alert-no-error">Message is : '.$err['message'].'</div>';
            }

            $result = '';

            $postArr = filter_input_array( INPUT_POST );

            $walletObj         = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getCurrentUserWallet();

            $pointsToExchange = isset( $postArr[ 'cmmp_points_to_payout' ] ) ? $postArr[ 'cmmp_points_to_payout' ] : 0;

            if(!isset($currency) || empty($currency)){
                $currency = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
            }

            if ( $currentUserWallet ) {
                $currentUserWalletId     = $currentUserWallet->wallet_name;
                $currentUserWalletPoints = $currentUserWallet->points;

                $user        = get_user_by( 'id', $currentUserWallet->user_id );
                $payoutEmail = $user->user_email;

                $currentUserWalletPointsValue = self::calculateAmount( $currentUserWalletPoints );
                $payoutValue                  = self::calculateAmount( $pointsToExchange );
                $ratio                        = CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_points_ratio', 10 );
            }else{
                $user = wp_get_current_user();
            }

            $stripe_account_id = $user->stripe_account_id;
            
            if($postArr){
                
                if($test_mode){

                    try {

//                        $test_account = \Stripe\Account::create(array(
//                            "type" => "custom",
//                            "country" => "US",
//                            "requested_capabilities" => array("card_payments", "transfers"),
//                            "external_account" => array(
//                                "object" => "bank_account",
//                                "country" => "US",
//                                "currency" => "usd",
//                                "routing_number" => "110000000",
//                                "account_number" => "000123456789",
//                            ),
//                            "tos_acceptance" => array(
//                                "date" => 1536575784,
//                                "ip" => "192.162.208.56"
//                            )
//                        ));
//                        $destination = $test_account->id;
//                        $token = "tok_visa";
                        $destination = ($postArr['stripe_account_id']) ? $postArr['stripe_account_id'] : $stripe_account_id;
                    } catch (Exception $e) {
                        // Since it's a decline, Stripe_CardError will be caught
                        $body = $e->getJsonBody();
                        $err  = $body['error'];
                        echo '<div class="cm-micropayment-alert-no-error">Message is : '.$err['message'].'</div>';
                    }
                    
                }else{
                    $destination = ($postArr['stripe_account_id']) ? $postArr['stripe_account_id'] : $stripe_account_id;
                    $email = CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_email' );
                    if($email){

                        try{
                            $customer = \Stripe\Customer::create(array('email' => $email,));           
                        } catch (Exception $e) {
                            // Since it's a decline, Stripe_CardError will be caught
                            $body = $e->getJsonBody();
                            $err  = $body['error'];
                            echo '<div class="cm-micropayment-alert-no-error">Message is : '.$err['message'].'</div>';
                        }
                    }
                }
                $currency_name = strtolower(get_option('cm_micropayment_unit_currency', 'usd'));
                if ( $currency_name == 'usd'){
                    // All API requests expect amounts to be provided in a currency’s smallest unit
                    $amount_to_charge = floatval($payoutValue) * 100;
                } else {
                    $amount_to_charge = $payoutValue;
                }
                $charge_args = array(
                    "amount" => $amount_to_charge,
                    "currency" => $currency_name,
                    "transfer_group" => $currentUserWalletId,
                    "destination" => array(
                        "account" => $destination,
                    ),
                    "description" => sprintf("Payout from site: '%s' to user: %s (id: %s)", get_bloginfo('name'), $user->user_login, $user->ID)
                );

                if($test_mode){
                    $charge_args["source"] = "tok_visa";
                } else {
                    $charge_args["customer"] = $customer->id;
                }
                try{
                    $charge = \Stripe\Charge::create($charge_args);

                    if(!wp_verify_nonce($postArr['cm_stripe_nonce'], 'cm_micropayment_points_to_stripe')){
                        print("Sorry, request consist an error.");
                        return;
                    }

                    if($charge->paid){
                        $report = CMMPStripePayout::exchangePointsForStripe(
                            array(
                                'cmmp_wallet_id' => $currentUserWalletId,
                                'cmmp_points_to_stripe' => $pointsToExchange,
                            )
                        );
                        $wallet_balance = CMMicropaymentPlatformFrontend::getUserWalletBalance();
                        $wallet_value   = CMMicropaymentPlatformFrontend::getUserWalletBalanceValue();
                        echo '<h2>' . CMMicropaymentPlatform::get_option('cmmp_label_stripe_successfully_charged','Successfully charged')
                            . ' ' . $currency . $payoutValue . '!</h2>';
                        echo '<input type="hidden" value="' . $wallet_balance . '" name="cmmp-remaining-points" id="cmmp-remaining-points">';
                        echo '<input type="hidden" value="' . $wallet_value . '" name="cmmp-remaining-value" id="cmmp-remaining-value">';
                    }else{
                        echo '<h2>' . CMMicropaymentPlatform::get_option('cmmp_label_stripe_error_while_process','Error while process')
                            . ' ' . $currency . $payoutValue . '!</h2>';
                        print(sprintf( "\n Error message %s: \n Error code: %s \n", $charge->failure_message, $charge->failure_code));
                    }
                }catch (Exception $e) {
                    // Since it's a decline, Stripe_CardError will be caught
                  $body = $e->getJsonBody();
                  $err  = $body['error'];
                  print('Message is:' . $err['message'] . "\n");
                }
                // Prevent form from resubmit
                ?>
                <script>
                    if ( window.history.replaceState ) {
                        window.history.replaceState( null, null, window.location.href );
                    }
                </script>
                <?php 
                return;
            }
                
            ob_start();
            if(!empty($currentUserWallet)):
            
                $stripe_account_id = get_user_meta($user->ID, 'stripe_account_id', true);
                if ($stripe_account_id) {
                    echo '<script>jQuery(document).ready(function(){jQuery("input[name=stripe_account_id]").val("' . $stripe_account_id . '");});</script>';
                } else {

                    if(isset($args['connectbutton']) && !empty($args['connectbutton']) && $args['connectbutton'] == '1'){

                        if(isset($stripe['client_id']) && !empty($stripe['client_id'])){

                            $paymentButtonHeading = CMMicropaymentPlatform::get_option('cmmp_label_stripe_connect_heading','');
                            if(isset($paymentButtonHeading) && !empty($paymentButtonHeading)){
                                echo '<h4>'.$paymentButtonHeading.'</h4>';
                            }

                            $paymentButtonLabel = CMMicropaymentPlatform::get_option('cmmp_label_stripe_connect_button','');
                            if(!isset($paymentButtonLabel) || empty($paymentButtonLabel)){
                                $paymentButtonLabel = 'Connect with Stripe';
                            }

                            
                            echo '<a href="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=' . $stripe['client_id'] . '&scope=read_write" class="stripe-connect"><span>'.$paymentButtonLabel.'</span></a>';
                            if (isset($_GET['error_description'])) {
                                echo $_GET['error_description'];
                            }
                            if (isset($_GET['code'])) {

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://connect.stripe.com/oauth/token");
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS,
                                    "client_secret=" . $stripe['secret_key'] . "&code=" . $_GET['code'] . "&grant_type=authorization_code");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $response = curl_exec($ch);
                                $error = curl_error($ch);
                                curl_close($ch);

                                if ($error) {
                                    echo '<div class="cm-micropayment-alert-no-error">Something went wrong: '. $error.'</div>';
                                } else {
                                    $data = json_decode($response);
                                    if (isset($data->error_description)) {
                                    } else {
                                        update_user_meta($user->ID, 'stripe_account_id', $data->stripe_user_id);
                                        echo '<script>jQuery(document).ready(function(){jQuery("input[name=stripe_account_id]").val("' . $data->stripe_user_id . '");});</script>';
                                    }
                                }
                            }
                        }else{

                            $stripe_connect_client_id_required = CMMicropaymentPlatform::get_option('cmmp_label_stripe_connect_client_id_required','');

                            if(isset($stripe_connect_client_id_required) && !empty($stripe_connect_client_id_required)){

                                echo '<div class="cm-micropayment-alert-no-info">'.$stripe_connect_client_id_required.'</div>';

                            }
                        }

                    }
                }
                
            ?>
                <form action="" method="post" id="payment-form">
                    <input type="hidden" name="cmmp_stripe_payout_ratio" id="cmmp_stripe_payout_ratio" value="<?php echo $ratio ?>" />
                    <?php wp_nonce_field( 'cm_micropayment_points_to_stripe', 'cm_stripe_nonce' ); ?>
                    <div class="form-row">
                        <div>
                            <label>
                                <?php echo __cm( 'stripe_wallet_id' ); ?> 
                                <input type="text" size="36" name="cmmp_wallet_id" value="<?php echo $currentUserWalletId; ?>" />
                            </label>
                        </div>
                        <div>
                            <label>
                                <?php echo __cm( 'stripe_account_id' ); ?> 
                                <input type="text" name="stripe_account_id" value="<?php echo ($stripe_account_id) ? $stripe_account_id : ''; ?>" />
                            </label>
                        </div>
                        <div>
                            <label>
                                <?php echo __cm( 'stripe_points_to_exchange' ); ?> 
                                <input class="cmmp_points_to_payout" type="text" size="4" name="cmmp_points_to_payout" value="<?php echo $pointsToExchange; ?>" />
                            </label>
                            <span>
                                <?php echo sprintf( __cm( 'stripe_remaining_points' ), $currentUserWalletPoints, $currentUserWalletPointsValue, $currency ); ?>
                            </span>
                        </div>
                        <div>
                            <span><?php echo __cm( 'stripe_payout_value' ); ?></span>
                            <span id="cmmt_payout_value_placeholder"><?php echo $payoutValue; ?></span>
                            <span><?php echo $currency; ?></span>
                        </div>
                    </div>

                    <input type="submit" value="<?php echo __cm( 'stripe_payout_submit_payment' ); ?>">
                </form>
                <script>
                    jQuery( '#payment-form .cmmp_points_to_payout' ).on( 'keyup', function () {
                        var discountValue = 0;
                        var ratio = jQuery( '#cmmp_stripe_payout_ratio' ).val();
                        var currentValue = jQuery( this ).val();

                        if ( !isNaN( parseFloat( currentValue ) ) && isFinite( currentValue ) && currentValue > 0 )
                        {
                            discountValue = ( currentValue / ratio );
                            discountValue = discountValue.toFixed( 2 );
                        }
                        jQuery( '#cmmt_payout_value_placeholder' ).html(discountValue);
                    } );
                </script>
            <?php
            else:
                echo "Please, generate your wallet";
            endif;
            $result .= ob_get_clean();

            return $result;
        }

        public static function exchangePointsForStripe($args) {
            $return  = array();

            $walletId = $args[ 'cmmp_wallet_id' ];
            $amount   = $args[ 'cmmp_points_to_stripe' ];

            if ( $amount <= 0 ) {
                $return[ 'error' ] = __cm( 'stripe_error_negative_amount' );
                return $return;
            }

            $args = array(
                'points'    => $amount,
                'wallet_id' => $walletId,
            );

            $transactionResult = self::createWordpressTransaction( $args );
            if ( !empty( $transactionResult[ 'error' ] ) ) {
                $return[ 'error' ] = $transactionResult[ 'error' ];
            } else {
                /*
                 * Everything's fine
                 */
                if ( !empty( $transactionResult[ 'success' ] ) && !empty( $transactionResult[ 'points' ] ) ) {
                    $return = $transactionResult;
                }
            }

            return $return;
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

            $transactionType   = CMMicropaymentPlatformWalletCharges::TYPE_STRIPE_PAYOUT;
            $transactionStatus = !empty( $args[ 'status' ] ) ? $args[ 'status' ] : 1;

            $walletTransactions->log( $pointsToSubtract, 0, $wallet->wallet_id, $transactionType, $transactionStatus );

            $sendNotificationEmail = CMMicropaymentPlatform::get_option( 'cm_micropayment_send_stripe_payout_notifications', FALSE );
            if ( $sendNotificationEmail ) {
                CMMicropaymentPlatformNotification::send( $wallet->user_id, 'email_stripe_payout', array( 'amountPoints' => $pointsToSubtract ) );
            }

            $payoutAmout = self::calculateAmount( $pointsToSubtract );
            $paymentArgs = array(
                'amount'      => $payoutAmout,
                'email'       => $args[ 'email' ],
                'description' => sprintf( __cm( 'stripe_payout_for_exchanging' ), $pointsToSubtract, CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name', 'points' ), $args[ 'wallet_id' ], $payoutAmout ),
                'wallet_id'   => $args[ 'wallet_id' ],
            );

            $wpdb->query( 'COMMIT' );

            $wallet = $walletObj->getWalletByCode( $args[ 'wallet_id' ] );
            return array( 'success' => true, 'points' => $wallet->points );

            return array( 'error' => 'Stripe Payout transaction has failed' . $errorMessage, 'error_details' => $payoutResult );
        }

        /**
         * Changes the points to amount based on ratio
         * @param type $points
         */
        public static function calculateAmount( $points ) {
            $ratio = CMMicropaymentPlatform::get_option( 'cm_micropayment_stripe_points_ratio', 10 );
            if ( !is_numeric( $ratio ) || $ratio <= 0 ) {
                $amount = 0;
            } else {
                $amount = $points / $ratio;
            }
            return CMMicropaymentPlatform::convertType($amount);
        }

    }

}

CMMPStripePayout::init();