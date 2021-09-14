<?php

class CMMicropaymentPlatformFrontend {

    protected static $instance = NULL;
    public static $calledClassName;
    private static $dbWallet;

    public static function instance() {
        $class = __CLASS__;
        if ( !isset( self::$instance ) && !( self::$instance instanceof $class ) ) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct() {
        if ( empty( self::$calledClassName ) ) {
            self::$calledClassName = __CLASS__;
        }

        if ( !class_exists( 'CMMicropaymentPlatformWallet' ) ) {
            require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
        }

        self::$dbWallet = new CMMicropaymentPlatformWallet();
        add_action( 'plugins_loaded', array( self::$calledClassName, '_init' ) );
    }

    public static function _init() {
        self::registerShortcodes();
//        self::registerScripts();
        self::registerActions();
        self::registerFilters();
        self::applyFilters();
    }

    public static function applyFilters() {
        if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
            add_filter( 'edd_enabled_payment_gateways', array( self::$calledClassName, 'disallowPointsForPointsPayment' ), PHP_INT_MAX );
            if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_payment_gateway_override', FALSE ) ) {
                add_filter( 'edd_enabled_payment_gateways', array( self::$calledClassName, 'overrideEDDPaymentGateways' ), PHP_INT_MAX );
            }
        }
    }

    /**
     * Checks if there's a CM Micropaymnt Platform product in cart
     * @return boolean
     */
    public static function isAPointsPurchase() {
        $result                 = FALSE;
        $hasMicropaymentProduct = FALSE;
        $cart_items             = edd_get_cart_contents();

        if ( empty( $cart_items ) ) {
            return $result;
        }

        foreach ( $cart_items as $item ) {
            $hasMicropaymentProduct = has_term( 'CM Micropayment Platform', 'download_category', $item[ 'id' ] );
            if ( $hasMicropaymentProduct ) {
                $result = TRUE;
                break;
            }
        }

        return $result;
    }

    /**
     * Makes sure that the MicropaymentPoints are not in the cart together with other products
     * @return boolean
     */
    public static function removeNonPointsFromCart() {
        $result     = FALSE;
        $cart_items = edd_get_cart_contents();

        if ( empty( $cart_items ) ) {
            return $result;
        }

		$keysToRemove = array();

        foreach ( $cart_items as $cart_key => $item ) {
            $hasMicropaymentProduct = has_term( 'CM Micropayment Platform', 'download_category', $item[ 'id' ] );
            if ( !$hasMicropaymentProduct ) {
                $keysToRemove[] = $cart_key;
            }
        }

        //make sure to remove the items from the biggest index
        rsort( $keysToRemove );

        if ( !empty( $keysToRemove ) ) {
            /*
             * Some products were removed
             */
            $result = TRUE;
            foreach ( $keysToRemove as $cart_key ) {
                edd_remove_from_cart( $cart_key );
            }
        }

        return $result;
    }

    /**
     * Remove the option to pay with MicropaymentPoints for MicropaymentPoints bundles
     * @param type $enabledGateways
     * @return type
     */
    public static function disallowPointsForPointsPayment( $enabledGateways ) {
        $hasMicropaymentProduct = self::isAPointsPurchase();
        if ( $hasMicropaymentProduct && array_key_exists( 'cm_micropaymnent_platform', $enabledGateways ) ) {
            unset( $enabledGateways[ 'cm_micropaymnent_platform' ] );
        }

        return $enabledGateways;
    }

    /**
     * Remove all of the Easy Digital Downloads active payment gateways for products which are not
     * MicropaymentPoints
     * @param type $enabledGateways
     * @return array
     */
    public static function overrideEDDPaymentGateways( $enabledGateways ) {
        $hasMicropaymentProduct = self::isAPointsPurchase();

        if ( !$hasMicropaymentProduct ) {
            if ( array_key_exists( 'cm_micropaymnent_platform', $enabledGateways ) ) {
                $gateway_list[ 'cm_micropaymnent_platform' ] = $enabledGateways[ 'cm_micropaymnent_platform' ];
                $enabledGateways                             = $gateway_list;
            } else {
                $enabledGateways = array();
            }
        } else {
            self::removeNonPointsFromCart();
        }

        return $enabledGateways;
    }

    public static function registerShortcodes() {
        add_shortcode( 'create_wallet_button', array( get_class(), 'getCreateWalletButton' ) );
        add_shortcode( 'cmmp_tip_button', array( get_class(), 'getTipButton' ) );
        add_shortcode( 'get_transaction_wallet', array( get_class(), 'getTransactionWalletID' ) );
        add_shortcode( 'get_transaction_wallet_points', array( get_class(), 'getTransactionWalletPoints' ) );
        add_shortcode( 'cm_micropayment_checkout', array( get_class(), 'getCheckOutTemplate' ) );
        add_shortcode( 'cm_check_wallet', array( get_class(), 'getWalletData' ) );
        add_shortcode( 'cm_wallet_id', array( get_class(), 'getUserWalletId' ) );
        add_shortcode( 'cm_user_wallet', array( get_class(), 'getUserWalletHistory' ) );
        add_shortcode( 'cm_user_balance', array( get_class(), 'getUserWalletBalance' ) );
        add_shortcode( 'cm_user_balance_value', array( get_class(), 'getUserWalletBalanceValue' ) );
        add_shortcode( 'cm_micropayment_buy_more_link', array( get_class(), 'getBuyMorePointsLink' ) );
        add_shortcode( 'transfer_wallet_points', array( get_class(), 'transferPointsShortcode' ) );

        add_filter( 'wp_enqueue_scripts', array( get_class(), 'precheckForShortcodes' ) );
    }

    public static function precheckForShortcodes() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cm_micropayment_checkout' ) ) {
            include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-checkout.php';
            CMMicropaymentPlatformFrontendCheckout::handlePost();
        }
    }

    public static function registerActions() {
        add_action('init', array(get_class(), 'finalizeTransaction'));

        if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
            add_action( 'edd_update_payment_status', array( self::$calledClassName, 'onEddCompletedPurchase' ), 100, 3 );
        } else {
            add_action( 'init', array( get_class(), 'processCheckout' ) );
        }
		add_action( 'wp_enqueue_scripts', array( get_class(), 'registerScripts') );
    }

    public static function registerFilters() {
        add_filter( 'wallet_has_enough_points', array( get_class(), 'hasWalletEnoughPoints' ) );
        add_filter( 'user_has_enough_points', array( get_class(), 'hasUserEnoughPoints' ) );
        add_filter( 'user_has_enough_points_by_user_id', array( get_class(), 'hasUserEnoughPointsById' ) );
        add_filter( 'withdraw_wallet_points', array( get_class(), 'withdrawWalletPoints' ) );
        add_filter( 'withdraw_wallet_points_external', array( get_class(), 'withdrawWalletPointsExternal' ) );
        add_filter( 'transfer_points_by_wallet_id', array( get_class(), 'transferPointsByWallet' ) );
        add_filter( 'charge_wallet', array( get_class(), 'chargeWallet' ) );
        add_filter( 'charge_user_wallet', array( get_class(), 'chargeUserWallet' ) );
        add_filter( 'cmmt_grant_for_purchase', array( get_class(), 'grantPointsForPurchase' ) );
        add_filter( 'cmmt_get_wallet_points_by_id', array( get_class(), 'getUserWalletBallanceById' ) );
    }
	
	public static function ajaxPayTip() {
		
		$action = $_POST['action'];
		$tipamount = $_POST['tipamount'];
		$account = $_POST['account'];
		$to_user_arr = get_user_by('login', $account);
		$to_user_id = $to_user_arr->ID;
		$from_user_id = $_POST['user_id'];
		$nonce = $_POST['nonce'];

		if($tipamount == '' || $tipamount == '0') {
			echo "Tip amount should be greater than 0";
		} else if($account == '') {
			echo "User account not defined";
		} else if(wp_create_nonce('cmmp_tip_user_'.$account) != $nonce) {
			echo "Something wrong!";
		} else {
			
			$wallet = CMMicropaymentPlatformFrontendWallet::instance();
			$ret = $wallet->transferPointsByUserId( $from_user_id, $to_user_id, $tipamount );
			if($ret['success']) {
				echo "Tip successfully credited to user";
			} else {
				echo $ret['message'];
			}
		}
		
		die();
	}

    public static function ajaxCreateWalletID() {
        if ( !class_exists( 'CMMicropaymentPlatformWallet' ) ) {
            require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';
        }
        $dbWallet = new CMMicropaymentPlatformWallet();

		$user_id = get_current_user_id();
		if ( $user_id !== 0 ) {
			if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer' ) ) {
					$wallet = $dbWallet->getWalletNameByUserID( $user_id );
					if ( !$wallet ) {
						echo json_encode( array( 'success' => true, 'wallet_name' => $dbWallet->createWallet( $user_id ) ) );
					} else {
//						echo json_encode( array( 'success' => true, 'wallet_name' => $wallet ) );
						die( json_encode( array( 'error' => 'Wallets quantity has reached its limit' ) ) );
					}
			} else {
				$wallets = $dbWallet->getWalletsByUserID( $user_id );
				if ( count($wallets) < CMMicropaymentPlatform::get_option( 'cm_micropayment_number_of_wallets', 1 ) ) {
					echo json_encode( array( 'success' => true, 'wallet_name' => $dbWallet->createWallet( $user_id ) ) );
				} else {
					die( json_encode( array( 'error' => 'Wallets quantity has reached its limit' ) ) );
				}
			}
		} else {
			die( json_encode( array( 'error' => 'User does not exist' ) ) );
		}
        die();
    }

    public static function hasWalletEnoughPoints( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->hasEnoughPoints( $args );
    }

    public static function hasUserEnoughPoints( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->hasUserEnoughPoints( $args );
    }

    public static function hasUserEnoughPointsById( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->hasUserEnoughPointsByUserId( $args );
    }

    public static function ajaxHasWalletEnoughPoints() {
        if ( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) {
            include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

            $wallet = CMMicropaymentPlatformFrontendWallet::instance();
            $result = $wallet->hasEnoughPoints( $_POST, true );

            echo json_encode( $result );
            die();
        }
    }

    public static function ajaxWithdrawWalletPoints($args = false) {
        if ( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) {
            include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

            $wallet = CMMicropaymentPlatformFrontendWallet::instance();

            $result = $wallet->withdraw( $args ?? $_POST, true );

            echo json_encode( $result );
            die();
        }
    }

    public static function grantPointsForPurchase( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->grantForPurchase( $args );
    }

    public static function chargePointsForPayment( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->chargeForPayment( $args );
    }

    public static function withdrawWalletPoints( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        $return = $wallet->withdraw( $args );
		if( $return[ 'success' ] ) {
			$currentWallet = $wallet->getWalletByCode( $args[ 'wallet_id' ] );
			$res = $wallet->chargeTransactionFee( $currentWallet->wallet_id, false, $args[ 'points' ] );
		}

		return $return;
    }
    public static function withdrawWalletPointsExternal( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        $return = $wallet->withdrawExternal( $args );
		if( $return[ 'success' ] ) {
			$currentWallet = $wallet->getWalletByCode( $args[ 'wallet_id' ] );
			$res = $wallet->chargeTransactionFee( $currentWallet->wallet_id, false, $args[ 'points' ] );
		}

		return $return;
    }

    public static function chargeWallet( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
		$result = $wallet->chargeWallet( $args[ 'wallet_id' ], $args[ 'amount' ] );

		if ( $result ) {
			$result = $wallet->chargeTransactionFee( $args[ 'wallet_id' ], true, $args[ 'points' ] );
		}

		return (bool) $result;
    }

    public static function chargeUserWallet( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();

        $args[ 'transfer' ] = isset( $args[ 'transfer' ] ) ? $args[ 'transfer' ] : false;
        $args[ 'price' ]    = isset( $args[ 'price' ] ) ? $args[ 'price' ] : 0;
        $args[ 'return_id' ]    = isset( $args[ 'return_id' ] ) ? $args[ 'return_id' ] : false;

        return $wallet->chargeUserWallet( $args[ 'user_id' ], $args[ 'amount' ], $args[ 'transfer' ], $args[ 'price' ], $args[ 'return_id' ], $args[ 'comment' ] );
    }

    public static function transferPointsByWallet( $args ) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->transferPointsByWalletCode( $args[ 'from' ], $args[ 'to' ], $args[ 'amount' ], $args[ 'comment' ] );
    }

    public static function getCreateWalletButton() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->createWalletID();
    }

    public static function getTipButton($atts) {

		$atts = shortcode_atts(
        array(
            'tipamount' => '0',
            'account' => '',
            'class' => '',
            'text' => '',
            'label' => 'Pay Tip',
        ), $atts);

        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
		
        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->createTipButton($atts);
    }

    public static function getTransactionWalletID() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->getTransactionWalletID();
    }

    public static function getTransactionWalletPoints() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $checkout       = CMMicropaymentPlatformFrontendWallet::instance();
        $pointsInWallet = $checkout->getTransactionWalletPoints();
        return ($pointsInWallet > 1 ? $pointsInWallet . ' ' . CMMicropaymentPlatformLabel::getLabel( 'cm_micropayment_plural_name' ) : $pointsInWallet . ' ' . CMMicropaymentPlatformLabel::getLabel( 'cm_micropayment_singular_name' ) );
    }

    public static function getCheckOutTemplate($atts) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-checkout.php';

        $checkout = CMMicropaymentPlatformFrontendCheckout::instance();
		$manual = isset($atts['manual']) ? boolval($atts['manual']) : 0;
        return $checkout->render($manual);
    }

    public static function processCheckout() {
        $postData = $_POST;
        if ( !isset( $postData[ 'referrer' ] ) || $postData[ 'referrer' ] != 'checkout-page-cmmicropayment' ) {
            return;
        }

        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-checkout.php';

        $checkout = CMMicropaymentPlatformFrontendCheckout::instance();
        $checkout->processcheckout();
    }

    public static function finalizeTransaction() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-checkout.php';

        $checkout = CMMicropaymentPlatformFrontendCheckout::instance();
        $checkout->finalize();
    }

    public static function getWalletData() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->render();
    }

    public static function getWalletInfo() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        return $wallet->getFormatedInfo();
    }

    public static function getUserWalletId(){
        if($user = wp_get_current_user()){
			if (!is_admin()) {
				if ( ob_start() ) {
					$wallet = CMMicropaymentPlatformFrontendWallet::instance();
					$wallet_id = $wallet->getWalletNameByUserID($user->ID);
					include CMMP_PLUGIN_DIR . '/frontend/views/wallet/wallet_id.phtml';
					$content = ob_get_clean();
					return $content;
				}
			}
        }
        return;
    }

    public static function getUserWalletHistory( $args = array() ) {
		$default_args = array(
			'show_wallet_id' => 'false',
			'wallet_id'      => null,
		);
		$args = shortcode_atts( $default_args, $args );
		
		if ( isset($_GET['wallet_id']) && empty($args['wallet_id']) ) {
			$args['wallet_id'] = $_GET['wallet_id'];
		}
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

		$show_wallet_id = isset($args['show_wallet_id']) ? (bool) $args['show_wallet_id'] : false;

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
		if ( is_user_logged_in() ) {
			if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer' ) || $args['wallet_id'] !== null ) {
				return $wallet->getWalletHistory( $show_wallet_id );
			} else {
//				if ( current_user_can('mange_options') ) {
					return $wallet->render();
//				}
			}
		} else {
			return wp_login_form(['echo' => false]);
		}
    }

    public static function getUserWalletBalance($args = array()) {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $user_id = get_current_user_id();
        $walletPoints = self::getUserWalletBallanceById($user_id);
        $pointlabel = true;
        if(isset($args['pointlabel'])){
            $pointlabel = (bool) $args['pointlabel'];
        }

        if($pointlabel){
            return sprintf(
                '%s %s',
                $walletPoints,
                ($walletPoints > 0 ?
                CMMicropaymentPlatformLabel::getLabel( 'cm_micropayment_plural_name' )
                : CMMicropaymentPlatformLabel::getLabel( 'cm_micropayment_singular_name' )
                )
            );
        }else{
            return sprintf(
                '%d',
                $walletPoints);
        }

    }

    public static function getUserWalletBallanceById( $user_id ) {
        $wallet     = CMMicropaymentPlatformFrontendWallet::instance();
        $userWallet = $wallet->getWalletByUserID( $user_id );
        return $userWallet->points ?? 0;
    }

    /**
     * Returns the value of the points in the currency
     * uses the ratio set up in the Micropayment Gateway Settings
     * @return type
     */
    public static function getUserWalletBalanceValue() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $user_id = get_current_user_id();

        $wallet     = CMMicropaymentPlatformFrontendWallet::instance();
        $userWallet = $wallet->getWalletByUserID( $user_id );

        if ( CMMicropaymentPlatform::isEddIntegrationActive() ) {
            $ratio = (float) CMMPEddGateway::gatewayToPointsRatio();
        } else if ( CMMicropaymentPlatform::isWOOIntegrationActive() ) {
            $ratio = (float) CMMPWooGenerateDiscount::getRatio();
        } else if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_use_paypal' ) ) {
            $ratio = (float) CMMicropaymentPlatform::get_option( 'cm_micropayment_paypal_points_ratio', 10 );
        } else {
            $ratio = 1;
        }

        if ( $ratio <= 0 ) {
            $ratio = 1;
        }

        $currency       = ((CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency' ) != '') ? CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency' ) : 'USD');
        $currencySymbol = CMMicropaymentPlatform::get_option( 'cm_micropayment_unit_currency_symbol', '' );
        if ( !empty( $currencySymbol ) ) {
            $currency = $currencySymbol;
        }
        $value = ($userWallet->points ?? 0) / $ratio;

        //return sprintf( '%.2f %s', $value, $currency );

		if(__cm('format_price_in_wallet') == '%.2f %s' || __cm('format_price_in_wallet') == '%.2f%s') {
			return sprintf(__cm('format_price_in_wallet'), $value, $currency );
		} else {
			return sprintf(__cm('format_price_in_wallet'), $value, $currency );
		}
    }

    public static function getBuyMorePointsLink( $args = array() ) {
        ob_start();
        ?>
        <div class="checkout_link">
            <span class="checkout_link"><a href="<?php echo get_page_link( CMMicropaymentPlatform::get_option( 'cm_micropayment_checkout_page_id' ) ); ?>"><?php echo __cm( 'wallet_checkout_link_text' ) ?></a></span>
        </div>
        <?php
        $result = ob_get_clean();

        return $result;
    }

    public static function transferPointsShortcode( $args = array() ) {
		$result = '';
        $is_logged = is_user_logged_in();
        $currentUser    = wp_get_current_user();

        $fromOwnWallet  = false;
        $toUserWallet   = false;

        $comment_enable		= false;
        $comment_required   = false;

        $walletCode     = '';

		if ( ! $is_logged ) {
			return $result;
		} elseif ( !empty( $currentUser ) ) {
            $walletCode    = apply_filters( 'cm_micropayments_user_wallet_code', $currentUser->ID );
			if ( ! current_user_can('manage_options') ) {
				$fromOwnWallet = true;
			}
        }

        if ( CMMicropaymentPlatform::get_option( 'cm_micropayment_assign_wallet_to_customer' ) ) {
            $toUserWallet = true;
        }

		if(isset($args['users_dropdown']) && $args['users_dropdown'] == '0') {
			$toUserWallet = false;
		}

		if(isset($args['comment_enable']) && $args['comment_enable'] == '1') {
			$comment_enable = true;
		}
		if(isset($args['comment_required']) && $args['comment_required'] == '1') {
			$comment_required = true;
		}

        $result						= [];
		$from_wallet_id				= get_option('cmmp_label_from_wallet_id_placeholder', 'From Wallet ID');
		$to_wallet_id				= get_option('cmmp_label_to_wallet_id_placeholder', 'To Wallet ID');
		$plural_name				= get_option('cmmp_label_transfer_points_placeholder', 'Points');
		$comment_placeholder		= get_option('cmmp_label_transfer_points_message_placeholder', 'Message');

        $post         = filter_input_array( INPUT_POST );
        $nonce_verify = ( !empty( $post ) ) ? wp_verify_nonce( $post[ 'cmmp_action' ], 'cm_transfer_wallet_points' ) : false;

        if ( !empty( $post ) && $nonce_verify ) {
            $walletToCode = apply_filters( 'cm_micropayments_user_wallet_code', $post[ 'wallet_id_to' ] );
            $from   = 'own_wallet' === $post[ 'wallet_id_from' ] ? $walletCode : $post[ 'wallet_id_from' ];
			if($walletToCode == '') {
				$args = array(
					'from'		=> $from,
					'to'		=> $post['wallet_id_to'],
					'amount'	=> $post['points'],
					'comment'	=> $post['transfer_wallet_points_comment']
				);
			} else {
				$args = array(
					'from'		=> $from,
					'to'		=> $walletToCode,
					'amount'	=> $post['points'],
					'comment'	=> $post['transfer_wallet_points_comment']
				);
			}

			if( $post['points'] < 1) {
				$result[ 'message' ] = CMMicropaymentPlatformLabel::getLabel( 'transfer_wallet_points_error_msg' );
			} else {
				 $result = CMMicropaymentPlatformFrontend::transferPointsByWallet( $args );
			}

            ?>
            <script>
			if ( window.history.replaceState ) { window.history.replaceState( null, null, window.location.href ); }
            </script>
            <?php
        }
        ob_start();
        ?>
        <div>
            <?php if ( !empty( $result ) && !empty( $result[ 'message' ] ) ) : ?>
                <style>
                    div.cmmp_message { margin: 10px 0; }
                </style>
                <div class="cmmp_message">
                    <?php echo $result[ 'message' ]; ?>
                </div>
            <?php endif; ?>
            <form method="post" class="transfer_wallet_points_form">
                <?php wp_nonce_field( 'cm_transfer_wallet_points', 'cmmp_action' ); ?>

                <?php if ( !$fromOwnWallet ): ?>
                    <div class="cmmp_field">
                        <span><?php echo __cm( 'from_wallet_id' ); ?></span>
                        <input type="text" name="wallet_id_from" value="<?php echo esc_attr( $walletCode ); ?>" placeholder="<?php echo $from_wallet_id; ?>" required />
                    </div>
                <?php else: ?>
                    <input type="hidden" name="wallet_id_from" value="own_wallet" />
                <?php endif; ?>
                <?php if ( !$toUserWallet ): ?>
                    <div class="cmmp_field">
                        <span><?php echo __cm( 'to_wallet_id' ); ?></span>
                        <input type="text" name="wallet_id_to" value="" placeholder="<?php echo $to_wallet_id; ?>" required />
                    </div>
                <?php else: ?>
                    <div class="cmmp_field wallet_to_user_section">
                        <span><?php echo __cm( 'to_user' ); ?></span>
                        <?php
                        $wallets = self::$dbWallet->getWallets();
                        $fees_user_id = CMMicropaymentPlatformBackendFeesUser::getFeesUserId();
                        $store_purchases_user_id = CMMicropaymentPlatformBackendStorePurchasesUser::getStorePurchasesUserId();
                        if ( !empty( $wallets ) ) : ?>
                            <select id="sel_wallet_id_to" name="wallet_id_to">
                                <?php
                                foreach ( $wallets as $key => $value ) {
	                                if($value->user_id == $fees_user_id || $value->user_id == $store_purchases_user_id) {
		                                continue;
	                                }
	                                if ( !empty( $value->user_id ) ) {
                                        $user = get_userdata( $value->user_id );
                                        if ( !empty( $user ) ) {
                                            $userName = sprintf( '%s %s(%s)', $user->user_firstname, $user->user_lastname, $user->user_nicename );
                                            echo sprintf( '<option value="%s">%s</option>', $value->user_id, $userName );
                                        }
                                    }
                                }
                                ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="cmmp_field">
                    <span><?php echo sprintf( __cm( 'points_to_transfer' ), __cm( 'cm_micropayment_plural_name' ) ); ?></span>
					<input type="text" name="points" value="" placeholder="<?php echo $plural_name; ?>" required />
                </div>
				<?php
				if($comment_enable == true) {
					?>
					<div class="cmmp_field transfer_wallet_points_comment_container">
						<span><?php echo __cm('transfer_points_message_label'); ?></span>
						<textarea name="transfer_wallet_points_comment" placeholder="<?php echo $comment_placeholder; ?>" <?php if($comment_required == true) { echo 'required="required"'; } ?>></textarea>
					</div>
					<?php
				} else {
					echo '<textarea name="transfer_wallet_points_comment" style="display:none;"></textarea>';
				}
				?>
				<div>
					<input type="submit" value="<?php echo sprintf( __cm( 'transfer_points' ), __cm( 'cm_micropayment_plural_name' ) ); ?>" class="transfer_wallet_points_submit" style="cursor:pointer;" />
				</div>
            </form>
        </div>
        <?php
        $result = ob_get_clean();
        return $result;
    }

    public static function checkWalletID() {
        include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';

        $wallet = CMMicropaymentPlatformFrontendWallet::instance();
        $wallet->checkWalletID();
    }

    public static function onEddCompletedPurchase( $payment_id, $new_status, $old_status ) {
        /*
         *  Make sure that payments are only completed once
         */
        if ( $old_status == 'publish' || $old_status == 'complete' ) {
            return;
        }

        /*
         *  Make sure the payment completion is only processed when new status is complete
         */
        if ( $new_status != 'publish' && $new_status != 'complete' ) {
            return;
        }

        $eddDownloads = edd_get_payment_meta_downloads( $payment_id );
        if ( !empty( $eddDownloads ) ) {
            foreach ( $eddDownloads as $eddDownload ) {
                $pointsValue = get_post_meta( $eddDownload[ 'id' ], 'cmmp_points_value', true );
                $pointsPrice = edd_get_download_price( $eddDownload[ 'id' ] );

                if ( $pointsValue && CMMicropaymentPlatform::numericOrFloat( $pointsValue ) )  {

	                $pointsValue = CMMicropaymentPlatform::convertType($pointsValue);

                    /*
                     * The MP points have been bought - we have to add them to the wallet
                     */
                    $user_id = edd_get_payment_user_id( $payment_id );
                    if ( $user_id ) {
                        $args = array(
                            'user_id' => $user_id,
                            'amount'  => $eddDownload[ 'quantity' ] * floatval( $pointsValue ),
                            'price'   => $eddDownload[ 'quantity' ] * floatval( $pointsPrice ),
                            'return_id' => true
                        );

                        $chargeResult = self::chargeUserWallet( $args );

                        /*
                         * Successfully charged the wallet
                         */
                        if ( $chargeResult ) {
                            if ( !class_exists( 'CMMicropaymentPlatformTransactions' ) ) {
                                require_once CMMP_PLUGIN_DIR . 'shared/models/transactions.php';
                            }

                            /*
                             * Save the PayPal transaction in the log
                             */

                            $transactionId = CMMicropaymentPlatformTransactions::createTransaction( $chargeResult );
                            if ( $transactionId ) {
                                CMMicropaymentPlatformTransactions::updateStatus( $transactionId, 1 );
                            }
                        }
                    }
                }
            }
        }
    }

	public static function registerScripts() {
//		wp_register_script( 'jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/js/jquery-ui/jquery-ui-1.10.4.custom.min.js', array( 'jquery' ) );
		wp_register_script( 'cm-select2-script', CMMP_PLUGIN_URL . 'frontend/assets/js/select2.min.js', array('jquery'), CMMicropaymentPlatform::version() );
		wp_register_script( 'cm-micropayment-scripts-table-sorter', CMMP_PLUGIN_URL . 'frontend/assets/js/jquery.tablesorter.min.js', array('jquery'), CMMicropaymentPlatform::version() );

		wp_enqueue_script( 'cm-micropayment-scripts', CMMP_PLUGIN_URL . 'frontend/assets/js/scripts.js', array('jquery', 'cm-micropayment-scripts-table-sorter','cm-select2-script'), CMMicropaymentPlatform::version() );
		
		wp_localize_script('cm-micropayment-scripts', 'cmmp_data', array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'hideCreateButtonAfterAction' => CMMicropaymentPlatform::get_option( 'cm_micropayment_hide_create_button_after_action' ),
								'l18n'		 => array( 'missing_wallet_id' => __( 'Missing wallet ID' )),
							));

		wp_enqueue_style( 'cm-select2-style', CMMP_PLUGIN_URL . 'frontend/assets/css/select2.min.css' );
		wp_register_style( 'cm-micropayment-frontend-style', CMMP_PLUGIN_URL . 'frontend/assets/css/style.css', array(), CMMicropaymentPlatform::version() );
		wp_enqueue_style( 'cm-micropayment-frontend-style' );
	}
}
