<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model;
use com\cminds\payperposts\model\PostInstantPayment;
use com\cminds\payperposts\model\PostWooPayment;
use com\cminds\payperposts\model\Settings;

class PayboxController extends Controller {

	const NONCE_ACTIVATE = 'pppedd_init';
	const AJAX_ACTION = 'cmppp_init_payment';

	static $actions = array(
		'cmppp_labels_init' => array( 'priority' => 20 ),
	);
	static $ajax = array( 'cmppp_init_payment' );

	static function cmppp_labels_init() {
		model\Labels::loadLabelFile( App::path( 'asset/labels/instantpayments.tsv' ) );
	}

	public static function getGuestBox( model\Post $post ) {
		if ( is_null( $post ) ) {
			return '';
		}

		$paymentMethodEdd = new PostInstantPayment( $post );
		$paymentMethodWoo = new PostWooPayment( $post );
		$is_paid_edd      = $paymentMethodEdd->isPaid();
		$is_paid_woo      = $paymentMethodWoo->isPaid();

		if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_FORM_FOR_GUEST_USER ) &&
		     ( PostInstantPayment::isAvailable() || PostWooPayment::isAvailable() ) &&
		     ( $is_paid_edd || $is_paid_woo ) ) {

			$cmppp_woo_pricing_group_index = get_post_meta( $post->getID(), 'cmppp_woo_pricing_group_index', true );
			$cmpppedd_pricing_group        = get_post_meta( $post->getID(), 'cmpppedd_pricing_group', true );
			$groupEnabled                  = 0;

			if ( ! empty( $cmppp_woo_pricing_group_index ) || ! empty( $cmpppedd_pricing_group ) ) {
				$groupEnabled = '1';
			}

			$payments = [ $paymentMethodWoo, $paymentMethodEdd ];

			$views = '';

			foreach ( $payments as $paymentMethod ) {
				if ( $paymentMethod->isPaid() ) {

					// groups
					$form = static::getPayboxFormForGuest( $post, $paymentMethod );

					// categories - only for EDD
					$categoryform = '';

					if ( get_class( $paymentMethod ) == 'com\cminds\payperposts\model\PostInstantPayment' &&
					     Settings::getOption( Settings::OPTION_ENABLE_CATEGORIES_PRICES ) ) {

						$categoryform = static::getPayboxCategoryForm( $post, $paymentMethod );
					}

					// single
					if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_MODE ) == Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST ) {

						$cmppp_pricing_single = [];

						// if EDD
						if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
							$cmppp_pricing_single = get_post_meta( $post->getID(), 'cmpppedd_pricing_single', true );
						}

						// if WooCommerce enabled
						if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
							$cmppp_pricing_single = get_post_meta( $post->getID(), 'cmppp_woo_pricing_single', true );
						}

						$singleEnabled = ( isset( $cmppp_pricing_single['allow'] ) && $cmppp_pricing_single['allow'] == '1' ) ? 1 : 0;

						if ( $singleEnabled ) {
							$global_price_for_each_post = ( isset( $cmppp_pricing_single['price'] ) && $cmppp_pricing_single['price'] != '' ) ? $cmppp_pricing_single['price'] : 0;
							$singleform                 = static::getSinglePayboxFormForGuest( $post, $paymentMethod, $cmppp_pricing_single );
						}
					} else {
						$singleEnabled = '0';
						$singleform    = '';
					}

					$view = static::loadFrontendView( 'paybox', compact( 'groupEnabled', 'form', 'singleEnabled', 'singleform', 'categoryform' ) );

					if ( ! empty( $view ) ) {
						$views .= $view;
					}
				}
			}

			return $views;
		} else {
			return static::loadFrontendView( 'paybox-guest' );
		}
	}

	static function displayPaybox( model\Post $post, model\IPaymentMethod $paymentMethod ) {

		if ( is_user_logged_in() ) {

			if ( ! $post->isSubscriptionActive() ) {

				$cmppp_pricing_group = 0;


				// if Edd enabled
				if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
					$cmppp_pricing_group = get_post_meta( $post->getID(), 'cmpppedd_pricing_group', true );
				}

				// if Woo enabled
				if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					$cmppp_pricing_group = get_post_meta( $post->getID(), 'cmppp_woo_pricing_group_index', true );
				}

				$groupEnabled = 0;
				if ( ! empty( $cmppp_pricing_group ) ) {
					$groupEnabled = '1';
				}

				$form = static::getGroupPayboxForm( $post, $paymentMethod );


				if ( Settings::getOption( Settings::OPTION_ENABLE_CATEGORIES_PRICES ) ) {
					$categoryform = static::getPayboxCategoryForm( $post, $paymentMethod );
				} else {
					$categoryform = '';
				}

				$singleform    = '';
				if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_MODE ) == Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST ) {

					$singleEnabled        = 0;
					$cmppp_pricing_single = null;

					// if EDD
					if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
						$cmppp_pricing_single = get_post_meta( $post->getID(), 'cmpppedd_pricing_single', true );
					}

					// if Woo enabled
					if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
						$cmppp_pricing_single = get_post_meta( $post->getID(), 'cmppp_woo_pricing_single', true );
					}

					if ( ! is_null( $cmppp_pricing_single ) ) {
						$singleEnabled              = ( isset( $cmppp_pricing_single['allow'] ) && $cmppp_pricing_single['allow'] == '1' ) ? 1 : 0;
						$global_price_for_each_post = ( isset( $cmppp_pricing_single['price'] ) && $cmppp_pricing_single['price'] != '' ) ? $cmppp_pricing_single['price'] : 0;
					}

					if ( $singleEnabled ) {
						$singleform = static::getSinglePayboxForm( $post, $paymentMethod, $cmppp_pricing_single );
					}
				} else {
					$singleEnabled = '0';
					$singleform    = '';
				}

				echo static::loadFrontendView( 'paybox', compact( 'groupEnabled', 'form', 'singleEnabled', 'singleform', 'categoryform' ) );
			}
		} else {

			if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_FORM_FOR_GUEST_USER ) ) {
				$cmppp_woo_pricing_group_index = get_post_meta( $post->getID(), 'cmppp_woo_pricing_group_index', true );
				$cmpppedd_pricing_group        = get_post_meta( $post->getID(), 'cmpppedd_pricing_group', true );
				$groupEnabled                  = 0;
				if ( ! empty( $cmppp_woo_pricing_group_index ) || ! empty( $cmpppedd_pricing_group ) ) {
					$groupEnabled = '1';
				}
				$form = static::getPayboxFormForGuest( $post, $paymentMethod );
				if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_MODE ) == Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST ) {
					$cmppp_woo_pricing_single   = get_post_meta( $post->getID(), 'cmppp_woo_pricing_single', true );
					$singleEnabled              = ( isset( $cmppp_woo_pricing_single['allow'] ) && $cmppp_woo_pricing_single['allow'] == '1' ) ? 1 : 0;
					$global_price_for_each_post = ( isset( $cmppp_woo_pricing_single['price'] ) && $cmppp_woo_pricing_single['price'] != '' ) ? $cmppp_woo_pricing_single['price'] : 0;
					$singleform                 = static::getSinglePayboxFormForGuest( $post, $paymentMethod, $cmppp_woo_pricing_single );

					if ( $singleEnabled == '0' ) {
						$cmppp_woo_pricing_single   = get_post_meta( $post->getID(), 'cmpppedd_pricing_single', true );
						$singleEnabled              = ( isset( $cmppp_woo_pricing_single['allow'] ) && $cmppp_woo_pricing_single['allow'] == '1' ) ? 1 : 0;
						$global_price_for_each_post = ( isset( $cmppp_woo_pricing_single['price'] ) && $cmppp_woo_pricing_single['price'] != '' ) ? $cmppp_woo_pricing_single['price'] : 0;
						$singleform                 = static::getSinglePayboxFormForGuest( $post, $paymentMethod, $cmppp_woo_pricing_single );
					}
				} else {
					$singleEnabled = '0';
					$singleform    = '';
				}
				echo static::loadFrontendView( 'paybox', compact( 'groupEnabled', 'form', 'singleEnabled', 'singleform', 'categoryform' ) );
			} else {

				echo static::loadFrontendView( 'paybox-guest' );
			}
		}
	}

	static function getSinglePayboxFormForGuest( model\Post $post, model\IPaymentMethod $paymentMethod, $cmppp_woo_pricing_single = array() ) {
		if ( $post and $paymentMethod and $paymentMethod->isPaid() ) {
			$postId     = $post->getId();
			$nonce      = wp_create_nonce( static::NONCE_ACTIVATE );
			$provider   = get_class( $paymentMethod );
			$ajaxAction = static::AJAX_ACTION;
			if ( ! $post->isSubscriptionActive() ) {
				return static::loadFrontendView( 'paybox-singleform', compact( 'postId', 'nonce', 'provider', 'ajaxAction', 'cmppp_woo_pricing_single' ) );
			}
		}

		return "";
	}

	static function getSinglePayboxForm( model\Post $post, model\IPaymentMethod $paymentMethod, $cmppp_woo_pricing_single = array() ) {
		if ( $post and $paymentMethod and $paymentMethod->isPaid() ) {
			if ( is_user_logged_in() ) {
				$postId     = $post->getId();
				$nonce      = wp_create_nonce( static::NONCE_ACTIVATE );
				$provider   = get_class( $paymentMethod );
				$ajaxAction = static::AJAX_ACTION;
				$plans      = []; // TODO: mb we no need it
				if ( ! $post->isSubscriptionActive() ) {
					return static::loadFrontendView( 'paybox-singleform', compact( 'plans', 'postId', 'nonce', 'provider', 'ajaxAction', 'cmppp_woo_pricing_single' ) );
				}
			}
		}

		return "";
	}

	public static function getGroupPayboxForm( model\Post $post, model\IPaymentMethod $paymentMethod ) {
		$html = "";
		if ( is_user_logged_in() && $post and $paymentMethod and $paymentMethod->isPaid() ) {

			// get post's group list
			// for each group get plans
			// merge results and return the html

			$groups = $paymentMethod::getPostPricingGroupsIndexes( $post->getId() );

			if ( ! empty( $groups ) ) {
				$forms = [];
				foreach ( $groups as $group_index ) {
					$plans      = $paymentMethod::getSubscriptionPlansForGroup( $group_index );
					$postId     = $post->getId();
					$nonce      = wp_create_nonce( static::NONCE_ACTIVATE );
					$provider   = get_class( $paymentMethod );
					$ajaxAction = static::AJAX_ACTION;
					if ( ! $post->isSubscriptionActive() ) {
						$forms[] = static::loadFrontendView( 'paybox-form', compact( 'plans', 'postId', 'nonce', 'provider', 'ajaxAction' ) );
					}
				}
				$html = implode( '', $forms );
			}
		}

		return $html;
	}

	static function getPayboxCategoryForm( model\Post $post, model\IPaymentMethod $paymentMethod ) {

		if ( $post and $paymentMethod and $paymentMethod->isPaid() ) {
			// get post's categories
			global $wpdb;
			$query = $wpdb->prepare(
				"SELECT tt.term_taxonomy_id as category_id FROM {$wpdb->prefix}term_relationships as tr
						  LEFT JOIN {$wpdb->prefix}term_taxonomy as tt ON tt.term_taxonomy_id=tr.term_taxonomy_id
						  WHERE tt.taxonomy='category' AND tr.object_id='%d'", $post->getId()
			);


			$downloads    = [];
			$category_ids = $wpdb->get_results( $query, ARRAY_A );


			if ( ! empty( $category_ids ) ) {

				$category_ids = array_column( $category_ids, 'category_id' );

				$category_ids_str = implode( ',', $category_ids );

				$query = "SELECT pm.post_id as download_id, p.post_title as title, pm2.meta_value as price
							FROM {$wpdb->prefix}postmeta as pm
							LEFT JOIN {$wpdb->prefix}posts as p ON p.ID=pm.post_id
							LEFT JOIN {$wpdb->prefix}postmeta as pm2 ON pm2.post_id=pm.post_id
							WHERE pm.meta_key='cmppp_category_id' AND pm.meta_value IN ({$category_ids_str})
							AND pm2.meta_key='edd_price'";
				unset( $category_ids_str );

				$downloads = $wpdb->get_results( $query, ARRAY_A );
			}

			if ( empty( $downloads ) ) {
				return "";
			}

			$postId     = $post->getId();
			$nonce      = wp_create_nonce( static::NONCE_ACTIVATE );
			$provider   = get_class( $paymentMethod );
			$ajaxAction = static::AJAX_ACTION;

			if ( ! get_current_user_id() || ! $post->isSubscriptionActive() ) {
				return static::loadFrontendView( 'paybox-categoryform', compact( 'downloads', 'postId', 'nonce', 'provider', 'ajaxAction' ) );
			}
		}

		return "";
	}

	static function getPayboxFormForGuest( model\Post $post, $paymentMethod ) {

		$html = '';
		if ( $post and $paymentMethod and $paymentMethod->isPaid() ) {
			$groups = $paymentMethod::getPostPricingGroupsIndexes( $post->getId() );

			if ( ! empty( $groups ) ) {
				$forms = [];
				foreach ( $groups as $group_index ) {
					$plans      = $paymentMethod::getSubscriptionPlansForGroup( $group_index );
					$postId     = $post->getId();
					$nonce      = wp_create_nonce( static::NONCE_ACTIVATE );
					$provider   = get_class( $paymentMethod );
					$ajaxAction = static::AJAX_ACTION;
//					if ( ! $post->isSubscriptionActive() ) {
					$forms[] = static::loadFrontendView( 'paybox-form', compact( 'plans', 'postId', 'nonce', 'provider', 'ajaxAction' ) );
//					}
				}

				$html = implode( '', $forms );
			}
		}

		return $html;
	}

	public static function cmppp_init_payment() {

		header( 'content-type: application/json' );
		$response = array( 'success' => 0, 'msg' => 'An error occurred.' );

		$postId           = intval( filter_input( INPUT_POST, 'postId' ) );
		$nonce            = filter_input( INPUT_POST, 'nonce' );
		$product_id       = filter_input( INPUT_POST, 'product_id' );
		$priceGroupIndex  = filter_input( INPUT_POST, 'priceGroupIndex' );
		$priceIndex       = filter_input( INPUT_POST, 'priceIndex' );
		$providerClass    = filter_input( INPUT_POST, 'provider' );
		$callbackUrl      = filter_input( INPUT_POST, 'callbackUrl' );
		$user_time_offset = filter_input( INPUT_POST, 'user_time_offset' );

		if ( $user_time_offset ) {
			Storage::set_user_time_offset( $user_time_offset );
		}

		$number  = filter_input( INPUT_POST, 'number' );
		$unit    = filter_input( INPUT_POST, 'unit' );
		$price   = filter_input( INPUT_POST, 'price' );
		$period  = filter_input( INPUT_POST, 'period' );
		$seconds = filter_input( INPUT_POST, 'seconds' );

		$download_id = filter_input( INPUT_POST, 'download_id' );

		$allowedProviders = array( model\PostInstantPayment::class, model\PostWooPayment::class );

		if ( empty( $nonce ) or ! wp_verify_nonce( $nonce, static::NONCE_ACTIVATE ) ) {
			$response['msg'] = 'Invalid nonce';
		} else if ( empty( $providerClass ) or ! in_array( $providerClass, $allowedProviders ) ) {
			$response['msg'] = 'Invalid provider';
		} else {

			/**
			 * @var IPaymentMethod $provider
			 */
			if ( ! empty( $postId ) ) {
				$post = model\Post::getInstance( $postId );
			}

			if ( ! empty( $postId ) ) {
				$provider     = call_user_func( array( $providerClass, 'getInstance' ), $post );
				$subscription = new model\Subscription( $post );
			} else {
				$provider = new $providerClass();
			}


			if ( ! empty( $postId ) && ! $provider->isPaid() ) {
				$response['msg'] = 'Post is not paid';
			} else if ( ! empty( $postId ) && $subscription->isSubscriptionActive() ) {
				$response['msg'] = 'Subscription is already active';
			} else {

				// single
				if ( $priceIndex == '0' ) {
					$singleplan = array(
						'product_id' => $product_id,
						'number'     => $number,
						'unit'       => $unit,
						'price'      => $price,
						'period'     => $period,
						'seconds'    => $seconds
					);

					$redirectUrl = $provider->initSinglePayment( $singleplan, $callbackUrl );

					if ( is_array( $redirectUrl ) && isset( $redirectUrl['msg'] ) ) {
						$response['msg'] = $redirectUrl['msg'];
					} else {
						$response = array(
							'success'  => 1,
							'msg'      => model\Labels::getLocalized( 'eddpay_checkout_redirection' ),
							'redirect' => $redirectUrl
						);
					}
				} else {

					// category

					if ( isset( $download_id ) && ! empty( $download_id ) ) {
						// this is edd download_id

						$redirectUrl = $provider->initPayment( [ 'download_id' => $download_id ], $callbackUrl );

						if ( is_array( $redirectUrl ) && isset( $redirectUrl['msg'] ) ) {
							$response['msg'] = $redirectUrl['msg'];
						} else {
							$response = array(
								'success'  => 1,
								'msg'      => model\Labels::getLocalized( 'eddpay_checkout_redirection' ),
								'redirect' => $redirectUrl
							);
						}
					} else {

						// groups

						$group_plans = $provider::getSubscriptionPlansForGroup( $priceGroupIndex );
						$plans       = $group_plans['prices'];

						if ( empty( $plans ) or empty( $priceIndex ) or empty( $plans[ $priceIndex ] ) ) {
							$response['msg'] = 'Invalid price index';
						} else {

							$plan                        = $plans[ $priceIndex ];
							$plan['pricing_group_index'] = $priceGroupIndex;
							$redirectUrl                 = $provider->initPayment( $plan, $callbackUrl );

							if ( is_array( $redirectUrl ) && isset( $redirectUrl['msg'] ) ) {
								$response['msg'] = $redirectUrl['msg'];
							} else {
								$response = array(
									'success'  => 1,
									'msg'      => model\Labels::getLocalized( 'eddpay_checkout_redirection' ),
									'redirect' => $redirectUrl
								);
							}
						}
					}
				}
			}
		}


		// backlink
		if ( isset( $provider ) ) {

			// for EDD and WooCommerce
			if ( PostInstantPayment::isPluginActive() || PostWooPayment::isPluginActive() ) {

				if ( ! $postId ) {
					$callbackUrl_arr = explode( '/', $callbackUrl );
					$length          = count( $callbackUrl_arr );

					if ( empty( $callbackUrl_arr[ $length - 1 ] ) ) {
						$slug = $callbackUrl_arr[ $length - 2 ];
					} else {
						$slug = $callbackUrl_arr[ $length - 1 ];
					}

					if ( ! empty( $slug ) ) {
						$pages = get_posts( [
							'name'        => $slug,
							'post_type'   => 'page',
							'numberposts' => 1
						] );


						if ( ! empty( $pages ) ) {
							$postId = $pages[0]->ID;
						}
					}
				}

				$Storage = new Storage();
				$Storage->set( 'cmppp_back_post_id', $postId, 86400 );
			}
		}


		echo json_encode( $response );
		exit;
	}

}
