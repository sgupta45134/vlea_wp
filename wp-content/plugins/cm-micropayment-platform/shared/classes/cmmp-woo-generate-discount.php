<?php
add_action('plugins_loaded', 'cminds_woocommerce_discounts_init', 0);

function cminds_woocommerce_discounts_init() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    CMMPWooGenerateDiscount::init();
}

if (!class_exists('CMMPWooGenerateDiscount')) {

    class CMMPWooGenerateDiscount {

        public static function init() {
            add_shortcode('cm_micropayment_points_to_woo_discount', array(__CLASS__, 'exchangePointsForDiscountForm'));
            add_shortcode('cm_micropayment_points_woo_discounts', array(__CLASS__, 'discountsFromExchangedPointsTable'));
            add_action('init', array(__CLASS__, 'exchangePointsForDiscount'), 1000);
        }

        public static function getRatio() {
            $gateway = new WC_CM_Micro_Payment();
            $ratio = (float) $gateway->gatewayToPointsRatio();
            return $ratio;
        }

        public static function exchangePointsForDiscountForm($args = array()) {
            $result = '';

            $featureEnabled = CMMicropaymentPlatform::get_option('cm_micropayment_discount_woo_code_exchange', false);
            if (!$featureEnabled) {
                return $result;
            }

            $postArr = filter_input_array(INPUT_POST);

            $walletObj = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getCurrentUserWallet();

            $currentUserWalletPoints = 0;
            $currentUserWalletPointsValue = 0;

            if (class_exists('WC_CM_Micro_Payment')) {
                $ratio = self::getRatio();
            } else {
                $ratio = 1;
            }

            if ($ratio <= 0) {
                $ratio = 1;
            }

            $pointsToExchange = isset($postArr['cmmp_points_to_exchange']) ? $postArr['cmmp_points_to_exchange'] : 0;
            $discountValue = 0;
            $currency = ((CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency') != '') ? CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency') : 'USD');
            $currencySymbol = CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency_symbol', '');
            if (!empty($currencySymbol)) {
                $currency = $currencySymbol;
            }

            $currentUserWalletId = '';
            if ($currentUserWallet) {
                $currentUserWalletId = $currentUserWallet->wallet_name;
                $currentUserWalletPoints = $currentUserWallet->points;
                $currentUserWalletPointsValue = number_format($currentUserWalletPoints / $ratio, 2);

                $discountValue = number_format($pointsToExchange / $ratio, 2);
            }

            ob_start();
            ?>
            <form method="post" id="cmmp_exchange_points_for_discount_form">
                <input type="hidden" name="cmmp_woo_points_exchange_nonce" value="<?php echo wp_create_nonce('cmmp_points_exchange'); ?>" />
                <input type="hidden" name="cmmp_points_exchange_ratio" id="cmmp_points_exchange_ratio" value="<?php echo $ratio ?>" />
                <div><label><?php echo __cm('discount_wallet_id'); ?> <input type="text" size="36" name="cmmp_wallet_id" value="<?php echo $currentUserWalletId; ?>" /></label></div>
                <div>
                    <label><?php echo __cm('discount_points_to_exchange'); ?> <input class="cmmp_points_to_exchange" type="text" size="4" name="cmmp_points_to_exchange" value="<?php echo $pointsToExchange; ?>" /></label>
                    <span><?php echo sprintf(__cm('discount_remaining_points'), $currentUserWalletPoints, $currentUserWalletPointsValue, $currency); ?></span>
                </div>
                <div>
                    <span><?php echo __cm('discount_woo_value'); ?></span> <span id="cmmt_discount_value_placeholder"><?php echo $discountValue; ?></span> <span><?php echo $currency; ?></span>
                </div>
                <input type="submit" name="cmmp_points_exchange" value="<?php echo __cm('discount_woo_exchange'); ?>" />
            </form>
            <script>
                jQuery('#cmmp_exchange_points_for_discount_form .cmmp_points_to_exchange').on('keyup', function () {
                    var discountValue = 0;
                    var ratio = jQuery('#cmmp_points_exchange_ratio').val();
                    var currentValue = jQuery(this).val();

                    if (!isNaN(parseFloat(currentValue)) && isFinite(currentValue) && currentValue > 0)
                    {
                        discountValue = (currentValue / ratio);
                        discountValue = discountValue.toFixed(2);
                    }
                    jQuery('#cmmt_discount_value_placeholder').html(discountValue);
                });
            </script>
            <?php
            $result .= ob_get_clean();

            return $result;
        }

        public static function discountsFromExchangedPointsTable($args = array()) {
            $result = '';

            $featureEnabled = CMMicropaymentPlatform::get_option('cm_micropayment_discount_woo_code_exchange', false);
            if (!$featureEnabled) {
                return $result;
            }

            $loggedUser = wp_get_current_user();
            if (empty($loggedUser)) {
                return $result;
            }
            $userId = $loggedUser->ID;
            $array = self::getUsersDiscounts($userId);

            if (empty($array)) {
                return $result;
            }

            $discounts['coupons'] = [];
            foreach ($array as $key => $coupon_id) {
                $couponData = new WC_Coupon($coupon_id);
                if(!is_wp_error($couponData)){
                    $discounts['coupons'][] = reset($couponData);
                }
            }

            if (empty($discounts['coupons'])) {
                return $result;
            }

            $currency = ((CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency') != '') ? CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency') : 'USD');
            $currencySymbol = CMMicropaymentPlatform::get_option('cm_micropayment_unit_currency_symbol', '');
            if (!empty($currencySymbol)) {
                $currency = $currencySymbol;
            }
            
            ob_start();
            
            ?>
            <table>
                <thead>
                    <tr>
                        <th><?php echo __cm('discount_woo_code'); ?></th>
                        <th><?php echo __cm('discount_woo_g_value'); ?></th>
                        <th><?php echo __cm('discount_woo_status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discounts['coupons'] as $discount): ?>
                        <tr>
                            <td><?php echo $discount['code']; ?></td>
                            <td><?php echo sprintf('%s %s', $discount['amount'], $currency); ?></td>
                            <td><?php echo 0 == $discount['usage_limit'] ? __cm('discount_woo_used') : __cm('discount_woo_active') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $result .= ob_get_clean();

            return $result;
        }

        public static function exchangePointsForDiscount() {
            $postArr = filter_input_array(INPUT_POST);

            // verify nonce
            if (!isset($postArr['cmmp_woo_points_exchange_nonce']) || !wp_verify_nonce($postArr['cmmp_woo_points_exchange_nonce'], 'cmmp_points_exchange')) {
                return;
            }

            $walletId = $postArr['cmmp_wallet_id'];
            $amount = $postArr['cmmp_points_to_exchange'];

            if ($amount <= 0) {
                return;
            }

            $args = array(
                'points' => $amount,
                'wallet_id' => $walletId,
                'type' => CMMicropaymentPlatformWalletCharges::TYPE_CHARGE
            );

            $walletObj = CMMicropaymentPlatformFrontendWallet::instance();
            $currentUserWallet = $walletObj->getWalletByCode($walletId);

            if (empty($currentUserWallet)) {
                return;
            }

            $withdrawResult = CMMicropaymentPlatformFrontend::withdrawWalletPoints($args);

            if (isset($withdrawResult['success']) && $withdrawResult['success']) {
                if (class_exists('WC_CM_Micro_Payment')) {
                    $ratio = self::getRatio();
                } else {
                    $ratio = 1;
                }

                if ($ratio <= 0) {
                    $ratio = 1;
                }

                $couponValue = number_format($amount / $ratio, 2);
                $couponCode = self::generateUniqueCode();

                if ($couponCode && $couponValue > 0) {
                    $discount = self::createDiscount($couponCode, $couponValue);

                    if (!$discount) {
                        /*
                         * Need to give the points back
                         */
                    } else {
                        /*
                         * Add the discount for the user
                         */
                        $walletUserId = $currentUserWallet->user_id;
                        $walletUser = get_user_by('id', $walletUserId);

                        if (!empty($walletUser)) {
                            $userDiscounts = self::getUsersDiscounts($walletUserId);
                            $userDiscounts[] = $discount;
                            /*
                             * Remove duplicates
                             */
                            $userDiscounts = array_unique($userDiscounts);
                            self::setUsersDiscounts($walletUserId, $userDiscounts);
                        }
                    }
                }
            }
        }

        public static function getUsersDiscounts($userId) {
            $userDiscounts = get_user_meta($userId, '_cmmp_discounts', true);
            if (!is_array($userDiscounts)) {
                $userDiscounts = array();
            }
            return $userDiscounts;
        }

        public static function setUsersDiscounts($userId, $discounts = array()) {
            $userDiscounts = update_user_meta($userId, '_cmmp_discounts', $discounts);
            return $userDiscounts;
        }

        public static function createDiscount($coupon_code, $amount) {

            $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product

            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => ' ',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'shop_coupon',
                'post_excerpt' => 'Discount code generated by CM Micropayment Platform'
            );

            $new_coupon_id = wp_insert_post($coupon);

            if ($new_coupon_id) {
                /*
                 * Add meta
                 */
                update_post_meta($new_coupon_id, 'discount_type', $discount_type);
                update_post_meta($new_coupon_id, 'coupon_amount', $amount);
                update_post_meta($new_coupon_id, 'individual_use', 'yes');
                update_post_meta($new_coupon_id, 'product_ids', '');
                update_post_meta($new_coupon_id, 'exclude_product_ids', '');
                update_post_meta($new_coupon_id, 'usage_limit', '1');
                update_post_meta($new_coupon_id, 'usage_limit_per_user', '1');
                update_post_meta($new_coupon_id, 'expiry_date', '');
                update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
                update_post_meta($new_coupon_id, 'free_shipping', 'no');
            }

            return $new_coupon_id;
        }

        /**
         * Generates the unique discount code
         * @return string
         */
        public static function generateUniqueCode() {

            // include & load API classes
            WC()->api->includes();
            WC()->api->register_resources(new WC_API_Server('/'));

            $length = 14;
            $chars = '1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
            $prefix = 'CMMP';

            $unique = '';
            $chars_length = strlen($chars) - 1;

            for ($i = 0; $i < $length; $i++) {
                $unique .= $chars[rand(0, $chars_length)];
            }

            do {
                $unique = $prefix . str_shuffle($unique);
            } while (is_int(WC()->api->WC_API_Coupons->get_coupon_by_code($unique)));

            return $unique;
        }

    }

}


