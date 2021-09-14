<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\lib\InstantPayment;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\helper\TimeHelper;
use com\cminds\payperposts\model\UserModel;

class EDDController extends Controller {

	const NONCE_ACTIVATE = 'pppedd_init';
	const NONCE_SET_COSTS = 'pppedd_costs_nonce';
	const EDDPAY_CALLBACK_ACTION = 'cmppp_edd_callback';

	const PARAM_POST_FILTER_PRICING_GROUP = 'cmppp_edd_pricing_group';

	protected static $filters = array(
		'cmppp_format_amount_payed' => array( 'args' => 2 ),
		'cmppp_options_config',
		'comments_template'         => array( 'priority' => PHP_INT_MAX ),
	);

	protected static $actions = array(
		'cmppp_after_save_settings',
		'add_meta_boxes',
		'edd_add_email_tags',
		'pre_get_posts'                       => array( 'args' => 1 ),
		'cmppp_admin_show_post_prices'        => array( 'args' => 1 ),
		'cmppp_admin_show_post_pricing_group' => array( 'args' => 1 ),
		'edd_complete_purchase'               => array( 'args' => 1 ), // EDD_Payment
		'edd_receipt_no_files_found_text'     => array( 'args' => 2 ), // EDD
		'edd_payment_receipt_after_table'     => array( 'args' => 4 ), // EDD
		'edd_is_payment_complete'             => array( 'args' => 3 ), // EDD
		array( 'name' => 'save_post', 'args' => 1 ),
		array( 'name' => 'ppp_channel_can_view', 'args' => 3 ),
		'ppp_access_denied_content'           => array( 'method' => 'displayPaybox', 'args' => 1 ),
		self::EDDPAY_CALLBACK_ACTION          => array( 'args' => 1 ),
		'template_redirect'
	);

	//protected static $ajax = array('cmppp_eddpay');
	protected static $suspendActions = 0;

	static function cmppp_options_config( $config ) {
		if ( PostInstantPayment::isAvailable() ) {
			if ( function_exists( '\edd_get_currency' ) ) {
				$currency = \edd_get_currency();
			} else {
				$currency = '';
			}
			$config[ Settings::OPTION_EDD_PRICING_GROUPS ] = array(
				'type'         => Settings::TYPE_MP_PRICE_GROUPS,
				'category'     => 'pricing',
				'subcategory'  => 'edd',
				'title'        => 'EDD pricing groups',
				'currency'     => $currency,
				'currencyStep' => 0.01,
				'desc'         => 'Sets period one (1) if you choose lifetime subscription.',
			);
		}

		return $config;
	}

	static function add_meta_boxes() {
		if ( PostInstantPayment::isAvailable() ) {
			foreach ( Subscription::getSupportedPostTypes() as $postType ) {
				add_meta_box( App::prefix( '-instantpayments-costs' ), 'CM Pay Per Posts: EDD Pricing', array(
					get_called_class(),
					'post_eddpay_costs_meta_box'
				),
					$postType, 'normal', 'high' );
			}
		}
	}

	static function post_eddpay_costs_meta_box( $post ) {
		if ( $post = Post::getInstance( $post ) and $edd = new PostInstantPayment( $post ) ) {
			$currentPricingGroups = $edd->getPricingGroupIndex();
			$currentPricingSingle = $edd->getPricingsingleIndex();
			$priceGroups          = Settings::getOption( Settings::OPTION_EDD_PRICING_GROUPS );
			if ( ! is_array( $priceGroups ) ) {
				$priceGroups = array();
			}
		} else {
			$currentPricingGroups = null;
			$currentPricingSingle = null;
			$priceGroups          = array();
		}
		wp_enqueue_script( 'cmppp-backend' );
		echo self::loadBackendView( 'costs-meta-box', compact( 'priceGroups', 'currentPricingGroups', 'currentPricingSingle' ) );
	}

	static function save_post( $post_id ) {
		if ( ! static::$suspendActions and $post = Post::getInstance( $post_id ) and $edd = new PostInstantPayment( $post ) ) {
			static::$suspendActions ++;

			$nonceField = 'cmppp-post-edd-nonce';

			if ( ! empty( $_POST[ $nonceField ] )
			     && wp_verify_nonce( $_POST[ $nonceField ], $nonceField ) ) {


				// GROUPS
				$groups         = $_POST['cmppp-edd-price-group'] ?? [];
				$groups_indexes = [];
				foreach ( $groups as $group_index ) {
					if ( ! empty( $group_index ) ) {
						$groups_indexes[] = $group_index;
					}
				}
				$edd->setPricingGroupIndex( $groups_indexes );


				// SINGLE
				$pricingSingleIndex = $edd->getPricingSingleIndex();

				$cmppp_edd_pricing_single_enabled = $_POST['cmppp_edd_pricing_single_enabled'] ?? 0;
				$cmppp_edd_pricing_single_number  = $_POST['cmppp_edd_pricing_single_number'] ?? 0;
				if ( $cmppp_edd_pricing_single_number == '' ) {
					$cmppp_edd_pricing_single_number = 0;
				}
				$cmppp_edd_pricing_single_unit  = $_POST['cmppp_edd_pricing_single_unit'];
				$cmppp_edd_pricing_single_price = $_POST['cmppp_edd_pricing_single_price'];
				if ( $cmppp_edd_pricing_single_price == '' ) {
					$cmppp_edd_pricing_single_price = 0;
				}


				$eddsingleIndexArr          = array();
				$eddsingleIndexArr['allow'] = $cmppp_edd_pricing_single_enabled;

				$singleTimeSec = TimeHelper::period2seconds( $cmppp_edd_pricing_single_number . $cmppp_edd_pricing_single_unit );

				if ( $cmppp_edd_pricing_single_enabled == '1' ) {
					if ( isset( $pricingSingleIndex['product_id'] )
					     && $pricingSingleIndex['product_id'] != ''
					     && $pricingSingleIndex['product_id'] > 0 ) {

						$product_id = $pricingSingleIndex['product_id'];
						wp_update_post( array(
							'ID'         => $product_id,
							'post_title' => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')'
						) );
						update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
						update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );

					} else {

						$product_id = wp_insert_post( array(
							'post_title'   => $post->getTitle() . ' (' . TimeHelper::seconds2period( $singleTimeSec ) . ')',
							'post_content' => '',
							'post_status'  => 'publish',
							'post_type'    => 'download',
						) );

						update_post_meta( $product_id, '_edd_bundled_products_conditions', '' );
						update_post_meta( $product_id, '_edd_bundled_products', '' );
						update_post_meta( $product_id, 'edd_download_files', '' );
						update_post_meta( $product_id, 'edd_variable_prices', '' );
						update_post_meta( $product_id, 'edd_price', $cmppp_edd_pricing_single_price );
						update_post_meta( $product_id, '_edd_download_sales', '0' );
						update_post_meta( $product_id, '_edd_download_earnings', '0.000000' );

						update_post_meta( $product_id, 'cmppp_subscription_time_sec', $singleTimeSec );
						update_post_meta( $product_id, 'cmppp_pricing_group', 0 );
					}
				} else {
					$product_id = 0;
				}

				$eddsingleIndexArr['number']     = $cmppp_edd_pricing_single_number;
				$eddsingleIndexArr['unit']       = $cmppp_edd_pricing_single_unit;
				$eddsingleIndexArr['price']      = $cmppp_edd_pricing_single_price;
				$eddsingleIndexArr['product_id'] = $product_id;
				$edd->setPricingSingleIndex( $eddsingleIndexArr );

			}

			static::$suspendActions --;
		}
	}

	static function displayPaybox( Post $post = null ) {
		if ( $post and PostInstantPayment::isAvailable() and $instantPayment = new PostInstantPayment( $post ) and $instantPayment->isPaid() ) {
			return PayboxController::displayPaybox( $post, $instantPayment );
		}

		return '';
	}

	/*
 	static function cmppp_eddpay() {
 		header('content-type: application/json');
			
 		try {
				
 			if (!is_user_logged_in()) throw new \Exception('User is not logged in.');
 			if (empty($_POST['callbackUrl'])) throw new \Exception('Missing callback URL.');
 			if (empty($_POST['postId'])) throw new \Exception('Missing post ID.');
 			$post = Post::getInstance($_POST['postId']);
 			if (!$post) throw new \Exception('Missing post.');
 			$subscription = new Subscription($post);
 			if (!$subscription) throw new \Exception('Invalid Subscription instance.');
 			$instantPayments = new PostInstantPayment($post);
 			if (!$instantPayments) throw new \Exception('Invalid InstantPayments instance.');
 			if (!$instantPayments->isPaid()) throw new \Exception('Post is not payed.');
 			if (empty($_POST['nonce'])) throw new \Exception('Missing nonce.');
 			if (!wp_verify_nonce($_POST['nonce'], self::NONCE_ACTIVATE)) throw new \Exception('Invalid nonce.');
 			$costs = $instantPayments->getSubscriptionPlans();
 			if (!$costs) throw new \Exception('Missing costs data.');
 			if (empty($_POST['priceIndex'])) throw new \Exception('Missing price index param.');
 			if (!isset($costs[$_POST['priceIndex']])) throw new \Exception('Invalid cost.');
 			if ($subscription->isSubscriptionActive()) throw new \Exception('Subscription is already active.');
			
 			$cost = $costs[$_POST['priceIndex']];
  			var_dump($cost);exit;
 			if ($url = $instantPayments->initPayment($cost, $_POST['callbackUrl'], $_POST['priceIndex'])) {
 				$response = array('success' => true, 'msg' => Labels::getLocalized('eddpay_checkout_redirection'), 'redirect' => $url);
 			} else {
 				throw new \Exception('Failed to initialize transaction.');
 			}
 		} catch (\Exception $e) {
 			$response = array('success' => false, 'msg' => Labels::getLocalized($e->getMessage()));
 		}
		
 		echo json_encode($response);
 		exit;
 	}
	*/

	static function cmppp_edd_callback( $params ) {
		//trigger_error("test");
		//var_dump($params);exit;
		if ( isset( $params['userId'] ) and isset( $params['subscription']['subscriptionPlan'] ) and isset( $params['subscription']['postId'] ) ) {
			$requestedSubscription = $params['subscription']['subscriptionPlan'];
			$post                  = Post::getInstance( $params['subscription']['postId'] );
			$subscriptionModel     = new Subscription( $post );
			try {
				$subscriptionModel->addSubscription(
					$params['userId'],
					$requestedSubscription['seconds'],
					$params['price'],
					PostInstantPayment::PAYMENT_PLUGIN_NAME,
					$params['subscription']['pricingGroupIndex']
				);

				Subscription::notifyPurchaseConfirmation(
					$params['userId'],
					$requestedSubscription['seconds'],
					$params['price'],
					PostInstantPayment::PAYMENT_PLUGIN_NAME,
					$params['subscription']['postId']
				);
			} catch ( Exception $e ) {

			}
		}
	}

	static function cmppp_format_amount_payed( $amount, $plugin ) {
		if ( $plugin == PostInstantPayment::PAYMENT_PLUGIN_NAME ) {
			$amount = sprintf( Labels::getLocalized( 'eddpay_amount_payed_format' ), $amount );
		}

		return $amount;
	}

	public static function getPaybox( $post ) {
		$edd         = new PostInstantPayment( $post );
		$edd_is_paid = $edd->isPaid();

		if ( $edd_is_paid ) {
			ob_start();
			static::displayPaybox( $post );
			$pay_box = ob_get_clean();

			return $pay_box;
		}

		return "";
	}

	static function comments_template( $template ) {
		if ( App::isLicenseOk() and $post = Post::getInstance( get_post() ) and $post->isPaid() ) {
			$sub = new Subscription( $post );
			if ( ! $sub->isSubscriptionActive() and Settings::getOption( Settings::OPTION_HIDE_COMMENTS ) ) {
				$template = App::path( 'view/frontend/empty.php' );
			}
		}

		return $template;
	}

	// PPP pricing group price
	static function cmppp_admin_show_post_prices( $postId ) {
		$post = Post::getInstance( $postId );

		if ( $post ) {
			$payments = [
				'EDD'         => new PostInstantPayment( $post ),
				'WooCommerce' => new PostWooPayment( $post )
			];

			foreach ( $payments as $payment_name => $payment ) {
				$paymentInstanse = $payment::getInstance( $post );
				$groups          = $paymentInstanse::getPostPricingGroupsIndexes( $postId );

				if ( ! empty( $groups ) ) {
					foreach ( $groups as $group_index ) {
						$plans = $paymentInstanse::getSubscriptionPlansForGroup( $group_index );
						if ( ! empty( $plans ) ) {
							foreach ( $plans['prices'] as $i => $cost ) {
								if ( isset( $cost['price'] ) && ! empty( $cost['price'] ) ) {
									printf(
										Labels::getLocalized( 'eddpay_period_for_amount' ),
										Subscription::period2date( $cost['period'] ),
										$cost['price']
									);
									echo '<br>';
								}
							}
						}
					}
				}
			}

		}
	}

	// PPP pricing group name
	static function cmppp_admin_show_post_pricing_group( $postId ) {
		$post = Post::getInstance( $postId );

		if ( $post ) {
			$payments = [
				'EDD'         => new PostInstantPayment( $post ),
				'WooCommerce' => new PostWooPayment( $post )
			];

			foreach ( $payments as $payment ) {
				$paymentInstanse = $payment::getInstance( $post );
				$groups          = $paymentInstanse::getPostPricingGroupsIndexes( $postId );

				if ( ! empty( $groups ) ) {
					foreach ( $groups as $group_index ) {
						$plans = $paymentInstanse::getSubscriptionPlansForGroup( $group_index );
						if ( ! empty( $plans ) ) {
							$url = remove_query_arg( self::PARAM_POST_FILTER_PRICING_GROUP, $_SERVER['REQUEST_URI'] );
							$url = add_query_arg( self::PARAM_POST_FILTER_PRICING_GROUP, $group_index, $url );
							printf( '<a href="%s">%s</a>', esc_attr( $url ), esc_html( $payment->getPricingGroupName( $group_index ) ) );
							echo "<br>";
						}
					}
				}
			}
		}

	}

	static function template_redirect() {
		$post_query = get_queried_object();
		$post_content = !empty($post_query) ? $post_query->post_content : '';


		if (!strpos($post_content, 'edd_receipt') && isset($_COOKIE['cmpp_user_login_user'])) {
			$user_id = $_COOKIE['cmpp_user_login_user'];
			$user = get_user_by( 'id', $user_id );

			if( $user && $user->data->ID != get_current_user_id()) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login );
			}
		}
	}

	static function edd_complete_purchase( $payment_id ) {
		// for guests only
		if ( ! is_user_logged_in() && ! is_null( $payment_id ) ) {

			if(Settings::getOption( Settings::OPTION_AUTO_REGISTER_AND_LOGIN_USER )) {

				$payment_meta = edd_get_payment_meta( $payment_id );
				$user_email = $payment_meta['user_info']['email'];
				$post_id = $_COOKIE['cmpp_subscription_post_id'];
				$seconds = get_post_meta($payment_meta['downloads'][0]['id'], 'cmppp_subscription_time_sec', true);
				$user_id = UserModel::registerUserEDD($user_email);

				$pricingGroupIndex = get_post_meta($post_id, 'cmpppedd_pricing_group', true)[0];

				$subscriptionModel = new Subscription( Post::getInstance($post_id) );

				$subscriptionModel->addSubscription(
					$user_id,
					$seconds,
					$payment_meta['cart_details'][0]['price'],
					PostInstantPayment::PAYMENT_PLUGIN_NAME,
					$pricingGroupIndex
				);

				setcookie('cmpp_user_login_user', $user_id, time()+3600, '/');
				unset($_COOKIE['cmpp_subscription_post_id']);

			}
			else {
				$meta = get_post_meta( $payment_id, '_edd_payment_meta', true );

				if ( isset( $meta['downloads'] ) && ! empty( $meta['downloads'] ) ) {
					foreach ( $meta['downloads'] as $download ) {
						$storage_key = 'EDD_paid_product_' . $download['id'];

						$group_index             = get_post_meta( $download['id'], 'cmppp_pricing_group', true );
						$storage_key_group_index = 'EDD_paid_group_' . $group_index;

						if ( ! empty( Storage::get( $storage_key, '' ) ) ) {
							continue;
						}

						$seconds = get_post_meta( $download['id'], 'cmppp_subscription_time_sec', true );

						if ( ! empty( $seconds ) && $seconds > 0 ) {
							Storage::set( $storage_key, $download['id'], $seconds );

							if ( $group_index ) {
								Storage::set( $storage_key_group_index, strtotime( "+$seconds sec" ), $seconds );
							}
						}
					}
				}
			}
		}

		// for backlink
		$Storage            = new Storage();
		$cmppp_back_post_id = $Storage->get( 'cmppp_back_post_id', 0 );

		if ( $cmppp_back_post_id && ! metadata_exists( 'edd_payment', $payment_id, 'cmppp_back_post_id' ) ) {
			update_post_meta( $payment_id, 'cmppp_back_post_id', $cmppp_back_post_id );
			$Storage->set( 'cmppp_back_post_id', 0 );
		}
	}

	static function edd_add_email_tags() {
		edd_add_email_tag( 'cmppp_back_post_url', 'Put back links to paid Post if exists', array(
			__CLASS__,
			'render_cmppp_back_post_url'
		) );
	}

	static function render_cmppp_back_post_url( $payment_id ) {
		$output = '';

		if ( $payment_id ) {
			$cmppp_back_post_id  = get_post_meta( $payment_id, 'cmppp_back_post_id', true );
			$cmppp_back_post_url = get_post_permalink( $cmppp_back_post_id );

			if ( $cmppp_back_post_id && $cmppp_back_post_url ) {
				$output = $cmppp_back_post_url;
			}
		}

		return $output;
	}

	static function edd_receipt_no_files_found_text( $text, $item_id ) {
		return '';
	}

	static function edd_payment_receipt_after_table( $payment, $edd_receipt_args ) {

		$cmppp_back_post_url = self::getBackLinkHtmlToPaidPost( $payment->ID );

		if ( $cmppp_back_post_url ) {
			echo "<div id='cmppp_backlink'>{$cmppp_back_post_url}</div>";
		} else {
			echo "<div id='cmppp_backlink' class='empty'></div>";
		}
	}


	static function edd_is_payment_complete( $ret, $payment_id, $payment_post_status ) {


		return $ret;
	}


	static function pre_get_posts( \WP_Query $query ) {
		if ( is_admin() ) {
			if ( $pricingGroup = filter_input( INPUT_GET, self::PARAM_POST_FILTER_PRICING_GROUP ) ) {
				$query->set( 'meta_key', PostInstantPayment::META_EDD_PRICING_GROUP_INDEX );
				$query->set( 'meta_value', $pricingGroup );
			}
		}
	}

	static function cmppp_after_save_settings() {
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			global $wpdb;

			$productsIdsAfterUpdate = array();
			$productsBeforeUpdate   = array();
			$createdProductsIds     = array();
			$productsMap            = array();

			$sql = "SELECT ID FROM $wpdb->posts p " . PHP_EOL;

			$join = $wpdb->prepare( "JOIN $wpdb->postmeta subtime ON subtime.post_id = p.ID AND subtime.meta_key = %s", 'cmppp_subscription_time_sec' );
			$sql  .= $join . PHP_EOL;
			$sql  .= $wpdb->prepare( "WHERE p.post_type = %s AND p.post_status = 'publish'
						ORDER BY p.menu_order ASC",
				'download' );

			$products = $wpdb->get_col( $sql );
			//echo "<pre>"; print_r($products); echo "</pre>";

			foreach ( $products as $product ) {
				$productsBeforeUpdate[ $product ]                                                                                                        = $product;
				$productsMap[ get_post_meta( $product, 'cmppp_pricing_group', true ) ][ get_post_meta( $product, 'cmppp_subscription_time_sec', true ) ] = $product;
			}
			//echo "<pre>"; print_r($productsMap); echo "</pre>";

			$groups = Settings::getOption( Settings::OPTION_EDD_PRICING_GROUPS );
			//echo "<pre>"; print_r($groups); echo "</pre>";

			if ( is_array( $groups ) ) {
				foreach ( $groups as $pricingGroupIndex => $group ) {
					if ( isset( $group['prices'] ) and is_array( $group['prices'] ) ) {
						foreach ( $group['prices'] as $price ) {
							$timeSec = TimeHelper::period2seconds( $price['number'] . $price['unit'] );
							if ( isset( $productsMap[ $pricingGroupIndex ][ $timeSec ] ) ) {
								// find the product with the same group and time
								$download_id              = $productsMap[ $pricingGroupIndex ][ $timeSec ];
								$productsIdsAfterUpdate[] = $download_id;
								update_post_meta( $download_id, 'edd_price', $price['price'] );
								update_post_meta( $download_id, 'cmppp_subscription_time_sec', $timeSec );
								update_post_meta( $download_id, 'cmppp_pricing_group', $pricingGroupIndex );
							} else {
								// download not found, create new one
								$edd_product_name = sprintf( 'CM Pay Per Posts: %s (%s)', $group['name'], TimeHelper::seconds2period( $timeSec ) );
								if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_MODE ) == 2 ) {
									$edd_product_name = sprintf( 'CM Pay Per Groups: %s (%s)', $group['name'], TimeHelper::seconds2period( $timeSec ) );
								}

								$download_id = wp_insert_post( array(
									'post_title'   => $edd_product_name,
									'post_content' => '',
									'post_status'  => 'publish',
									'post_type'    => 'download',
								) );

								update_post_meta( $download_id, '_edd_bundled_products_conditions', '' );
								update_post_meta( $download_id, '_edd_bundled_products', '' );
								update_post_meta( $download_id, 'edd_download_files', '' );
								update_post_meta( $download_id, 'edd_variable_prices', '' );
								update_post_meta( $download_id, 'edd_price', $price['price'] );
								update_post_meta( $download_id, '_edd_download_sales', '0' );
								update_post_meta( $download_id, '_edd_download_earnings', '0.000000' );

								update_post_meta( $download_id, 'cmppp_subscription_time_sec', $timeSec );
								update_post_meta( $download_id, 'cmppp_pricing_group', $pricingGroupIndex );

								$productsIdsAfterUpdate[] = $createdProductsIds[] = $download_id;
							}
						}
					}
				}
			}

			// Delete unused products
			$toDelete = array_diff( array_keys( $productsBeforeUpdate ), $productsIdsAfterUpdate );

			foreach ( $toDelete as $id ) {
				// skip if it for category
				// TODO: fix it
				// TODO: skip for category with products
//				$query = "SELECT pm.meta_value as category_id";

				if ( isset( $productsBeforeUpdate[ $id ] ) ) {
//					wp_delete_post( $id, true );
				}
			}
		}
	}

}
