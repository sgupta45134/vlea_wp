<?php
if ( !class_exists( 'CMMPEddGenerateDiscount' ) ) {

    class CMMPEddGenerateDiscount {

        public static function init() {
            add_shortcode( 'cm_micropayment_points_to_discount', array( __CLASS__, 'exchangePointsForDiscountForm' ) );
            add_shortcode( 'cm_micropayment_points_discounts', array( __CLASS__, 'discountsFromExchangedPointsTable' ) );
            add_action( 'init', array( __CLASS__, 'exchangePointsForDiscount' ) );
        }

        public static function exchangePointsForDiscountForm( $args = array() ) {
            $result = '';

            $featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_discount_code_exchange', false );
            if ( !$featureEnabled ) {
                return $result;
            }

            $postArr = filter_input_array( INPUT_POST );

            $walletObj         = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getCurrentUserWallet();

            $currentUserWalletPoints      = 0;
            $currentUserWalletPointsValue = 0;

            if ( class_exists( 'CMMPEddGateway' ) ) {
                $ratio = (float) CMMPEddGateway::gatewayToPointsRatio();
            } else {
                $ratio = 1;
            }

            if ( $ratio <= 0 ) {
                $ratio = 1;
            }

            $pointsToExchange = isset( $postArr[ 'cmmp_points_to_exchange' ] ) ? $postArr[ 'cmmp_points_to_exchange' ] : 0;
            $discountValue    = 0;
            $currency         = ((CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) != '') ? CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) : 'USD');
            $currencySymbol   = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
            if ( !empty( $currencySymbol ) ) {
                $currency = $currencySymbol;
            }

            if ( $currentUserWallet ) {
                $currentUserWalletId          = $currentUserWallet->wallet_name;
                $currentUserWalletPoints      = $currentUserWallet->points;
                $currentUserWalletPointsValue = number_format( $currentUserWalletPoints / $ratio, 2 );

                $discountValue = number_format( $pointsToExchange / $ratio, 2 );
            }

            ob_start();
            ?>
            <form method="post" id="cmmp_exchange_points_for_discount_form">
                <input type="hidden" name="cmmp_points_exchange_nonce" value="<?php echo wp_create_nonce( 'cmmp_points_exchange' ); ?>" />
                <input type="hidden" name="cmmp_points_exchange_ratio" id="cmmp_points_exchange_ratio" value="<?php echo $ratio ?>" />
                <div><label><?php echo __cm( 'discount_wallet_id' ); ?> <input type="text" size="36" name="cmmp_wallet_id" value="<?php echo $currentUserWalletId; ?>" /></label></div>
                <div>
                    <label><?php echo __cm( 'discount_points_to_exchange' ); ?> <input class="cmmp_points_to_exchange" type="text" size="4" name="cmmp_points_to_exchange" value="<?php echo $pointsToExchange; ?>" /></label>
                    <span><?php echo sprintf( __cm( 'discount_remaining_points' ), $currentUserWalletPoints, $currentUserWalletPointsValue, $currency ); ?></span>
                </div>
                <div>
                    <span><?php echo __cm( 'discount_value' ); ?></span> <span id="cmmt_discount_value_placeholder"><?php echo $discountValue; ?></span> <span><?php echo $currency; ?></span>
                </div>
                <input type="submit" name="cmmp_points_exchange" value="<?php echo __cm( 'discount_exchange' ); ?>" />
            </form>
            <script>
                jQuery( '#cmmp_exchange_points_for_discount_form .cmmp_points_to_exchange' ).on( 'keyup', function () {
                    var discountValue = 0;
                    var ratio = jQuery( '#cmmp_points_exchange_ratio' ).val();
                    var currentValue = jQuery( this ).val();

                    if ( !isNaN( parseFloat( currentValue ) ) && isFinite( currentValue ) && currentValue > 0 )
                    {
                        discountValue = ( currentValue / ratio );
                        discountValue = discountValue.toFixed( 2 );
                    }
                    jQuery( '#cmmt_discount_value_placeholder' ).html( discountValue );
                } );
            </script>
            <?php
            $result .= ob_get_clean();

            return $result;
        }

        public static function discountsFromExchangedPointsTable( $args = array() ) {
            $result = '';

            $featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_discount_code_exchange', false );
            if ( !$featureEnabled ) {
                return $result;
            }

            $loggedUser = wp_get_current_user();
            if ( empty( $loggedUser ) ) {
                return $result;
            }
            $userId = $loggedUser->ID;
            $array  = self::getUsersDiscounts( $userId );

            if ( empty( $array ) ) {
                return $result;
            }

            $queryArgs = array(
                'post__in' => $array
            );
            $discounts = edd_get_discounts( $queryArgs );

            if ( empty( $discounts ) ) {
                return $result;
            }

            $currency = ((CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) != '') ? CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency', '' ) : 'USD');
            $currencySymbol   = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
            if ( !empty( $currencySymbol ) ) {
                $currency = $currencySymbol;
            }
            
            ob_start();
            ?>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __cm( 'discount_code' ); ?></th>
                        <th><?php echo __cm( 'discount_edd_value' ); ?></th>
                        <th><?php echo __cm( 'discount_status' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $discounts as $discount ): ?>
                        <tr>
                            <td><?php echo edd_get_discount_code( $discount->ID ); ?></td>
                            <td><?php echo sprintf( '%s %s', edd_get_discount_amount( $discount->ID ), $currency ); ?></td>
                            <td><?php echo edd_is_discount_maxed_out( $discount->ID ) ? __cm( 'discount_status' ) : __cm( 'discount_active' ) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $result .= ob_get_clean();

            return $result;
        }

        public static function exchangePointsForDiscount() {
            $postArr = filter_input_array( INPUT_POST );

            // verify nonce
            if ( !isset( $postArr[ 'cmmp_points_exchange_nonce' ] ) || !wp_verify_nonce( $postArr[ 'cmmp_points_exchange_nonce' ], 'cmmp_points_exchange' ) ) {
                return;
            }

            $walletId = $postArr[ 'cmmp_wallet_id' ];
            $amount   = $postArr[ 'cmmp_points_to_exchange' ];

            if ( $amount <= 0 ) {
                return;
            }

            $args = array(
                'points'    => $amount,
                'wallet_id' => $walletId,
                'type'      => CMMicropaymentPlatformWalletCharges::TYPE_CHARGE
            );

            $walletObj         = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getWalletByCode( $walletId );

            if ( empty( $currentUserWallet ) ) {
                return;
            }

            $withdrawResult = CMMicropaymentPlatformFrontend::withdrawWalletPoints( $args );

            if ( isset( $withdrawResult[ 'success' ] ) && $withdrawResult[ 'success' ] ) {
                if ( class_exists( 'CMMPEddGateway' ) ) {
                    $ratio = (float) CMMPEddGateway::gatewayToPointsRatio();
                } else {
                    $ratio = 1;
                }

                if ( $ratio <= 0 ) {
                    $ratio = 1;
                }

                $couponValue = number_format( $amount / $ratio, 2 );
                $couponCode  = self::generateUniqueCode();

                if ( $couponCode && $couponValue > 0 ) {
                    $discount = self::createDiscount( $couponCode, $couponValue );

                    if ( !$discount ) {
                        /*
                         * Need to give the points back
                         */
                    } else {
                        /*
                         * Add the discount for the user
                         */
                        $walletUserId = $currentUserWallet->user_id;
                        $walletUser   = get_user_by( 'id', $walletUserId );

                        if ( !empty( $walletUser ) ) {
                            $userDiscounts   = self::getUsersDiscounts( $walletUserId );
                            $userDiscounts[] = $discount;
                            /*
                             * Remove duplicates
                             */
                            $userDiscounts   = array_unique( $userDiscounts );
                            self::setUsersDiscounts( $walletUserId, $userDiscounts );
                        }
                    }
                }
            }
        }

        public static function getUsersDiscounts( $userId ) {
            $userDiscounts = get_user_meta( $userId, '_cmmp_discounts', true );
            if ( !is_array( $userDiscounts ) ) {
                $userDiscounts = array();
            }
            return $userDiscounts;
        }

        public static function setUsersDiscounts( $userId, $discounts = array() ) {
            $userDiscounts = update_user_meta( $userId, '_cmmp_discounts', $discounts );
            return $userDiscounts;
        }

        public static function createDiscount( $coupon_code, $amount ) {
            if ( !is_callable( 'edd_store_discount' ) ) {
                return;
            }

            $args = array(
                'name'   => $coupon_code,
                'code'   => $coupon_code,
                'amount' => $amount,
            );

            $meta = wp_parse_args( $args, array(
                'name'              => $coupon_code,
                'code'              => $coupon_code,
                'type'              => 'flat',
                'amount'            => $amount,
                'excluded_products' => array(),
                'expiration'        => '',
                'is_not_global'     => false,
                'is_single_use'     => true,
                'max_uses'          => '1',
                'min_price'         => '',
                'product_condition' => '',
                'product_reqs'      => array(),
                'start'             => '',
                'uses'              => '',
            ) );

            // EDD will set it's own defaults in the edd_store_discount() so let's filter out our own empty defaults (their just here for easier reference)
            $meta = array_filter( $meta );

            // EDD takes a $details array which has some different keys than the meta, let's map the keys to the expected format
            $edd_post_keys = array(
                'max_uses'          => 'max',
                'product_reqs'      => 'products',
                'excluded_products' => 'excluded-products',
                'is_not_global'     => 'not_global',
                'is_single_use'     => 'use_once'
            );

            foreach ( $meta as $key => $value ) {
                $mod_key = isset( $edd_post_keys[ $key ] ) ? $edd_post_keys[ $key ] : '';
                if ( $mod_key ) {
                    $meta[ $mod_key ] = $value;
                }
            }

            $discount = edd_store_discount( $meta );
            return $discount;
        }

        /**
         * Generates the unique discount code
         * @return string
         */
        public static function generateUniqueCode() {
            $length = 14;
            $chars  = '1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
            $prefix = 'CMMP';

            $unique       = '';
            $chars_length = strlen( $chars ) - 1;

            for ( $i = 0; $i < $length; $i++ ) {
                $unique .= $chars[ rand( 0, $chars_length ) ];
            }

            do {
                $unique = $prefix . str_shuffle( $unique );
            } while ( edd_get_discount_by_code( $unique ) );

            return $unique;
        }

    }

}

CMMPEddGenerateDiscount::init();
