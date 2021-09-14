<?php
if ( !class_exists( 'CMMPWoo' ) ) {

    /**
     * Class to management functionality Woo
     * @author mazuu
     *
     */
    class CMMPWoo {

        public static function init() {
            add_action( 'add_meta_boxes', array( __CLASS__, 'addWooGrantedPointsMetabox' ), 120 );
            add_action( 'save_post', array( __CLASS__, 'WooPointsGrantedMetaboxSave' ) );

            add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'cm_woocommerce_order_status_processing' ) );
			//add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'handleTransaction' ) );
            add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'handleTransaction' ) );

            add_filter( 'woocommerce_currencies', array( __CLASS__, 'registerCurrency' ) );

            add_action( 'wc_membership_plan_options_membership_plan_data_general', array( __CLASS__, 'membershipSettings' ) );
            add_action( 'wc_memberships_save_meta_box', array( __CLASS__, 'membershipSettingsSave' ), 10, 3 );
			//add_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'add_my_currency_symbol' ), 10, 2 );

            $price_override = CMMicropaymentPlatform::get_option( 'cm_micropayment_woo_price_override', false );
            if ( $price_override ) {
                add_filter( 'woocommerce_order_formatted_line_subtotal', array( __CLASS__, 'emailPriceOverride' ), 10, 3 );
                add_filter( 'woocommerce_order_subtotal_to_display', array( __CLASS__, 'emailPriceSubtotalOverride' ), 10, 3 );
                add_filter( 'woocommerce_get_formatted_order_total', array( __CLASS__, 'emailPriceTotalOverride' ), 10, 2 );
            }
        }

        static function getOwnCurrency() {
            $currencySymbol = CMMicropaymentPlatformLabel::getLabel( 'external_currency_id' );
            $currencyName   = CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' );
            return $currencySymbol;
        }

        static function appendCurrency( $amount, $attributes = array() ) {
            $gatewayObject  = new WC_CM_Micro_Payment();
            $amountOfPoints = $gatewayObject->convertToPoints( $amount );
            $label          = ($amount > 1 ? CMMicropaymentPlatform::get_option( 'cm_micropayment_plural_name' ) : CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' ) );

            $mergedAttributes = array_merge( array( 'currency' => self::getOwnCurrency() ), $attributes );
            $formattedAmount  = wc_price( $amountOfPoints, $mergedAttributes );
            $result           = sprintf( '%s %s', $formattedAmount, $label );
            return $result;
        }

        static function emailPriceOverride( $subtotal, $item, $order ) {

            $paymentMethod = $order->payment_method;
            if ( 'cm-micropayment-gateway' != $paymentMethod ) {
                return $subtotal;
            }

            $gatewayObject = new WC_CM_Micro_Payment();
            $tax_display   = $order->tax_display_cart;

            if ( !isset( $item[ 'line_subtotal' ] ) || !isset( $item[ 'line_subtotal_tax' ] ) ) {
                return '';
            }

            if ( 'excl' == $tax_display ) {
                $ex_tax_label = $order->prices_include_tax ? 1 : 0;
				//$subtotal = wc_price( $gatewayObject->convertToPoints( $order->get_line_subtotal( $item ) ), array( 'ex_tax_label' => $ex_tax_label, 'currency' => self::getOwnCurrency() ) );
                $subtotal = self::appendCurrency( $order->get_line_subtotal( $item ), array( 'ex_tax_label' => $ex_tax_label ) );
            } else {
				//$subtotal = wc_price( $gatewayObject->convertToPoints( $order->get_line_subtotal( $item, true ) ), array( 'currency' => self::getOwnCurrency() ) );
                $subtotal = self::appendCurrency( $order->get_line_subtotal( $item, true ) );
            }

            return $subtotal;
        }

        static function emailPriceSubtotalOverride( $subtotal, $compound, $order ) {

            $paymentMethod = $order->payment_method;
            if ( 'cm-micropayment-gateway' != $paymentMethod ) {
                return $subtotal;
            }

            $tax_display = true;

            $gatewayObject = new WC_CM_Micro_Payment();

            if ( !$compound ) {
                foreach ( $order->get_items() as $item ) {

                    if ( !isset( $item[ 'line_subtotal' ] ) || !isset( $item[ 'line_subtotal_tax' ] ) ) {
                        return '';
                    }

                    $subtotal += $item[ 'line_subtotal' ];

                    if ( 'incl' == $tax_display ) {
                        $subtotal += $item[ 'line_subtotal_tax' ];
                    }
                }

				//$subtotal = wc_price( $gatewayObject->convertToPoints( $subtotal ), array( 'currency' => self::getOwnCurrency() ) );
                $subtotal = self::appendCurrency( $subtotal );

                if ( $tax_display == 'excl' && $order->prices_include_tax ) {
                    $subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                }
            } else {

                if ( 'incl' == $tax_display ) {
                    return '';
                }

                foreach ( $order->get_items() as $item ) {

                    $subtotal += $item[ 'line_subtotal' ];
                }

                // Add Shipping Costs.
                $subtotal += $order->get_total_shipping();

                // Remove non-compound taxes.
                foreach ( $order->get_taxes() as $tax ) {

                    if ( !empty( $tax[ 'compound' ] ) ) {
                        continue;
                    }

                    $subtotal = $subtotal + $tax[ 'tax_amount' ] + $tax[ 'shipping_tax_amount' ];
                }

                // Remove discounts.
                $subtotal = $subtotal - $order->get_total_discount();

				//$subtotal = wc_price( $gatewayObject->convertToPoints( $subtotal ), array( 'currency' => self::getOwnCurrency() ) );
                $subtotal = self::appendCurrency( $subtotal );
            }

            return $subtotal;
        }

        static function emailPriceTotalOverride( $formatted_total, $order ) {
            $paymentMethod = $order->payment_method;
            if ( 'cm-micropayment-gateway' != $paymentMethod ) {
                return $formatted_total;
            }

            $total         = $order->get_total();
            $gatewayObject = new WC_CM_Micro_Payment();
			//$result        = wc_price( $gatewayObject->convertToPoints( $total ), array( 'currency' => self::getOwnCurrency() ) );
            $result        = self::appendCurrency( $total );
            return $result;
        }

        static function membershipSettingsSave( $post, $id, $post_id ) {
            $metaValue = filter_input( INPUT_POST, 'memberhip_ratio' );
            if ( !empty( $metaValue ) ) {
                $ratio = update_post_meta( $post_id, '_cmmp_membership_ratio', $metaValue );
            }
        }

        static function membershipSettings() {
            global $post;
            $ratio = get_post_meta( $post->ID, '_cmmp_membership_ratio', true );
            ?>
            <div class="options_group">
                <?php
                // membership plan slug
                woocommerce_wp_text_input( array(
                    'id'          => 'memberhip_ratio',
                    'label'       => __( 'Currency to Points Ratio:', 'woocommerce-memberships' ),
                    'value'       => $ratio,
                    'placeholder' => 'eg. 1',
                ) );
                ?>
            </div>
            <?php
        }

        static function registerCurrency( $currencies ) {
            $unitName = CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' );
            $unitId   = CMMicropaymentPlatformLabel::getLabel( 'external_currency_id' );
            if ( $unitName && $unitId ) {
                $currencies[ $unitId ] = ucfirst( $unitName );
            }
            return $currencies;
        }

//		function add_my_currency_symbol( $currency_symbol, $currency ) {
//			switch ( $currency ) {
//				case 'ABC': $currency_symbol = '$';
//					break;
//			}
//			return $currency_symbol;
//		}
        // add the action

        /**
         * Add metabox to product in woo
         */
        public static function addWooGrantedPointsMetabox() {
            if ( !CMMicropaymentPlatform::iswooIntegrationActive() ) {
                return;
            }

            global $post;
			
			$featureEnabled = false;

            //$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_woo_grant_points_per_purchase', 0 );
            // if ( !$featureEnabled ) {
                // return;
            // }
            $post_types = array( 'product' );
            global $post;
            $product    = get_product( $post->ID );
            if ( get_post_type( $post ) == 'product' ) {
                $terms = get_the_terms( $post->ID, 'product_cat' );
                if ( !empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        if ( $term->slug == 'cm-micropayment-platform' ) {
							$featureEnabled = true;
							break;
							//return false;
                        }
                    }
                }
				if ( $featureEnabled ) {
					add_meta_box( 'cmmp_woo_product_cart', __( 'Grant MicroPayments Points WOO', 'cm-micropayment-platform' ), array( __CLASS__, 'addWooRenderGrantedPointsMetabox' ), $post_types, 'advanced', 'high' );
				}
            }
        }

        /**
         * Render the granted MicroPayments points metabox
         *
         * @since 1.0
         */
        public static function addWooRenderGrantedPointsMetabox() {
            // $featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_woo_grant_points_per_purchase', 0 );
            // if ( !$featureEnabled ) {
                // return;
            // }

            global $post;
            // Use nonce for verification
            echo '<input type="hidden" name="woo_mp_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

            echo '<table class="form-table">';

            $enabled = get_post_meta( $post->ID, '_woo_cmmp_enabled', true ) ? true : false;
            $points  = get_post_meta( $post->ID, '_woo_cmmp_points', true ) ? get_post_meta( $post->ID, '_woo_cmmp_points', true ) : '0';
            $qty_mult = get_post_meta( $post->ID, '_woo_cmmp_points_qty_mult', false ) ? true : false;

            $display = $enabled ? '' : ' style="display:none;"';

            echo '<script type="text/javascript">jQuery( document ).ready( function($) {$( "#woo_cmmp_enabled" ).on( "click",function() {$( ".woo_mp_toggled_row" ).toggle();} )} );</script>';

            echo '<tr>';
            echo '<td class="woo_field_type_text" colspan="2">';
            echo '<input type="checkbox" name="woo_cmmp_enabled" id="woo_cmmp_enabled" value="1" ' . checked( true, $enabled, false ) . '/>&nbsp;';
            echo '<label for="woo_cmmp_enabled">' . __( 'Check if you want to grant MicroPayments points per each purchase.' ) . '</label>';
            echo '<td>';
            echo '</tr>';

            echo '<tr' . $display . ' class="woo_mp_toggled_row">';
            echo '<td class="woo_field_type_text" colspan="2">';
            echo '<input type="number" class="small-text" name="woo_cmmp_points" id="woo_cmmp_points" value="' . esc_attr( $points ) . '"/>&nbsp;';
            echo __( 'Set the number of points granted per purchase of this product.' );
            echo '<td>';
            echo '</tr>';

            echo '<tr' . $display . ' class="woo_mp_toggled_row">';
            echo '<td class="woo_field_type_text" colspan="2">';
            echo '<input type="checkbox" name="woo_cmmp_points_qty_mult" id="woo_cmmp_points_qty_mult" value="1" ' . checked( true, $qty_mult, false ) . '/>&nbsp;';
            echo '<label for="woo_cmmp_points_qty_mult">' . __( 'Check if you want to multiply points by quantity of product purchased.' ) . '</label>';
            echo '<td>';
            echo '</tr>';

            echo '</table>';
        }

        public static function WooPointsGrantedMetaboxSave( $post_id ) {
            global $post;

            // verify nonce
            if ( !isset( $_POST[ 'woo_mp_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'woo_mp_meta_box_nonce' ], basename( __FILE__ ) ) ) {
                return $post_id;
            }

            // Check for auto save / bulk edit
            if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST[ 'bulk_edit' ] ) ) {
                return $post_id;
            }

            if ( isset( $_POST[ 'post_type' ] ) && 'product' != $_POST[ 'post_type' ] ) {
                return $post_id;
            }

            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }

            if ( isset( $_POST[ 'woo_cmmp_enabled' ] ) ) {
                update_post_meta( $post_id, '_woo_cmmp_enabled', true );
            } else {
                delete_post_meta( $post_id, '_woo_cmmp_enabled' );
            }

            if ( isset( $_POST[ 'woo_cmmp_points' ] ) ) {
                update_post_meta( $post_id, '_woo_cmmp_points', (int) $_POST[ 'woo_cmmp_points' ] );
            } else {
                delete_post_meta( $post_id, '_woo_cmmp_points' );
            }
            if ( isset( $_POST[ 'woo_cmmp_points_qty_mult' ] ) ) {
                update_post_meta( $post_id, '_woo_cmmp_points_qty_mult', (int) $_POST[ 'woo_cmmp_points_qty_mult' ] );
            } else {
                delete_post_meta( $post_id, '_woo_cmmp_points_qty_mult' );
            }
        }

        public static function setWalletPoints( $points, $idUser ) {
            include_once CMMP_PLUGIN_DIR . '/frontend/classes/cmmp-frontend-wallet.php';
            $wallet            = CMMicropaymentPlatformFrontendWallet::instance();
            $args[ 'points' ]  = $points;
            $args[ 'user_id' ] = $idUser;
            $args[ 'type' ]    = CMMicropaymentPlatformWalletCharges::TYPE_WOO_PURCHASE_GRANT;
			
			if($points > 0) {
				return $wallet->grantForPurchase( $args );
			} else {
				return array( 'success' => true );
			}
        }
		
		public static function cm_woocommerce_order_status_processing($order_id) {
			$order = wc_get_order($order_id);
			if (!empty($order)) {
				if ( count( $order->get_items() ) > 0 ) {
					foreach ( $order->get_items() as $item ) {
						$terms   = get_the_terms( $item[ 'product_id' ], 'product_cat' );
						foreach ( $terms as $term ) {
							if ( $term->slug == 'cm-micropayment-platform' || (bool) get_post_meta( $item[ 'product_id' ], '_woo_cmmp_points', true ) ) {
								$order->update_status( CMMicropaymentPlatform::get_option( 'cm_micropayment_woo_points_order_status', 'completed') );
								return;
							}
						}
					}
				}
				// Set status PROCESSING for non-virtual products orders
				// $order->update_status( 'processing' );
				$order->update_status( CMMicropaymentPlatform::get_option( 'cm_micropayment_woo_final_order_status', 'processing') );
			}
		}

        public static function handleTransaction( $order_id ) {
            try {
                $order = new WC_Order( $order_id );
                $post  = get_post( $order_id );

                if ( !empty( $post ) ) {
                    $author_id = $order->get_user_id();
                    $points    = 0;
                    if ( count( $order->get_items() ) > 0 ) {
                        foreach ( $order->get_items() as $item ) {
                            $catProd = '';
                            $terms   = get_the_terms( $item[ 'product_id' ], 'product_cat' );
                            foreach ( $terms as $term ) {
                                if ( $term->slug == 'cm-micropayment-platform' ) {
                                    $catProd = 'cmmp';
                                }
                            }
                            if ( $catProd == 'cmmp' ) {
                                $point = get_post_meta( $item[ 'product_id' ], 'cmmp_points_value', true );
								if($point == '' || $point == '0') {
									$enabled = get_post_meta( $item[ 'product_id' ], '_woo_cmmp_enabled', true ) ? true : false;
									if($enabled) {
										$point = get_post_meta( $item[ 'product_id' ], '_woo_cmmp_points', true );
										$mult_by_qty = get_post_meta( $item[ 'product_id' ], '_woo_cmmp_points_qty_mult', true ) ? $item->get_quantity() : 1;
										$point *= $mult_by_qty;
									}
								}
                            } else {
                                $point = get_post_meta( $item[ 'product_id' ], '_woo_cmmp_points', true );
								$mult_by_qty = get_post_meta( $item[ 'product_id' ], '_woo_cmmp_points_qty_mult', true ) ? $item->get_quantity() : 1;
								$point *= $mult_by_qty;
                            }
                            $points += (int) $point;
                        }
                    }
                    $points = apply_filters('cmmp_before_set_wallet_points', $points, $order);
                    $transactionResult = CMMPWoo::setWalletPoints( $points, $author_id );
                    if ( !empty( $transactionResult[ 'error' ] ) ) {
                        throw new Exception( $transactionResult[ 'error' ] );
                    }
                } else {
                    throw new Exception( 'Empty post' );
                }
            } catch ( Exception $ex ) {
                echo $ex->getMessage();
            }
        }

    }

}
CMMPWoo::init();