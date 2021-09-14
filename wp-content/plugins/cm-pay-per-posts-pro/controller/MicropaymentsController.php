<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\Category;
use com\cminds\payperposts\model\SubscriptionReport;

class MicropaymentsController extends Controller {

	const REFUND_NONCE = 'cmppp_subscription_refund';

	const PARAM_POST_FILTER_PRICING_GROUP = 'cmppp_mp_pricing_group';

	protected static $actions = array(
		'add_meta_boxes',
		array( 'name' => 'save_post', 'args' => 1 ),
		array( 'name' => 'cmppp_labels_init', 'priority' => 20 ),
		array( 'name' => 'cmppp_post_can_view', 'args' => 3 ),
		'cmppp_admin_show_post_prices'        => array( 'args' => 1 ),
		'cmppp_admin_show_post_pricing_group' => array( 'args' => 1 ),
	);
	protected static $filters = array(
		'cmppp_options_config',
		'cmppp_format_amount_payed' => array( 'args' => 2 ),
//		array('name' => 'the_content', 'priority' => PHP_INT_MAX),
		array( 'name' => 'comments_template', 'priority' => PHP_INT_MAX ),
	);
	protected static $ajax = array(
		'cmppp_subscription_activate',
		'cmppp_subscription_activate_single',
		'cmppp_subscription_activate_author',
		'cmppp_subscription_activate_author_donation',
		'cmppp_refund'
	);
	protected static $suspendActions = 0;

	static function cmppp_options_config( $config ) {
		if ( Micropayments::isMicroPaymentsAvailable() ) {

			if ( class_exists( 'CMUserSubmittedPosts' ) ) {
				$config[ Settings::OPTION_MICROPAYMENTS_AUTHORS ]          = array(
					'type'        => Settings::TYPE_BOOL,
					'default'     => false,
					'category'    => 'pricing',
					'subcategory' => 'micropayments',
					'title'       => 'Allow authors to charge for access',
					'desc'        => 'Displays a pay box on each article where authors can assign a price to the content',
				);
				$config[ Settings::OPTION_MICROPAYMENTS_AUTHORS_DONATION ] = array(
					'type'        => Settings::TYPE_BOOL,
					'default'     => false,
					'category'    => 'pricing',
					'subcategory' => 'micropayments',
					'title'       => 'Enable donate points',
					'desc'        => 'If enabled and post set as "Free" then show donation box. This will allow members to donate a point amount of their choosing (but not require it).<br><strong>Notice: This will work if above option is enabled.</strong>',
				);
			}

			$config[ Settings::OPTION_MICROPAYMENTS_GROUPS ] = array(
				'type'         => Settings::TYPE_MP_PRICE_GROUPS,
				'category'     => 'pricing',
				'subcategory'  => 'micropayments',
				'title'        => 'Pricing groups',
				'currency'     => 'points',
				'currencyStep' => 1,
				'desc'         => 'Sets period one (1) if you choose lifetime subscription.',
			);
		}

		return $config;
	}

	static function cmppp_labels_init() {
		if ( Micropayments::isMicroPaymentsAvailable() ) {
			Labels::loadLabelFile( App::path( 'asset/labels/micropayments.tsv' ) );
		}
	}

	static function add_meta_boxes() {
		if ( Micropayments::isMicroPaymentsAvailable() ) {
			foreach ( Subscription::getSupportedPostTypes() as $postType ) {
				add_meta_box( App::prefix( '-micropayments-prices' ), 'CM Pay Per Posts: Micropayments Pricing', array(
					get_called_class(),
					'post_prices_meta_box'
				),
					$postType, 'normal', 'high' );
			}
		}
	}

	static function post_prices_meta_box( $post ) {
		if ( $post = Post::getInstance( $post ) and $mp = new Micropayments( $post ) ) {
			$currentPricingGroups = $mp->getPricingGroupIndex();
			$currentPricingSingle = $mp->getPricingsingleIndex();
			$priceGroups          = Settings::getOption( Settings::OPTION_MICROPAYMENTS_GROUPS );
			if ( ! is_array( $priceGroups ) ) {
				$priceGroups = array();
			}
		} else {
			$currentPricingGroup  = null;
			$currentPricingSingle = null;
			$priceGroups          = array();
		}
		wp_enqueue_script( 'cmppp-backend' );
		echo self::loadBackendView( 'post-prices-meta-box', compact( 'priceGroups', 'currentPricingGroups', 'currentPricingSingle' ) );
	}

	static function save_post( $post_id ) {
		if ( ! static::$suspendActions and $post = Post::getInstance( $post_id ) and $mp = new Micropayments( $post ) ) {
			static::$suspendActions ++;

			$nonceField = 'cmppp-post-mp-nonce';
			if ( ! empty( $_POST[ $nonceField ] ) and wp_verify_nonce( $_POST[ $nonceField ], $nonceField ) ) {

				// GROUPS
				$groups         = $_POST['cmppp-mp-price-group'] ?? [];
				$groups_indexes = [];
				foreach ( $groups as $group_index ) {
					if ( ! empty( $group_index ) ) {
						$groups_indexes[] = $group_index;
					}
				}
				$mp->setPricingGroupIndex( $groups_indexes );


				// SINGLE
//				$pricingSingleIndex = $mp->getPricingSingleIndex();

				$cmppp_mp_pricing_single_enabled = $_POST['cmppp_mp_pricing_single_enabled'] ?? 0;
				$cmppp_mp_pricing_single_number  = $_POST['cmppp_mp_pricing_single_number'] ?? 0;
				if ( $cmppp_mp_pricing_single_number == '' ) {
					$cmppp_mp_pricing_single_number = 0;
				}
				$cmppp_mp_pricing_single_unit  = $_POST['cmppp_mp_pricing_single_unit'];
				$cmppp_mp_pricing_single_price = $_POST['cmppp_mp_pricing_single_price'];
				if ( $cmppp_mp_pricing_single_price == '' ) {
					$cmppp_mp_pricing_single_price = 0;
				}

				$mpsingleIndexArr               = array();
				$mpsingleIndexArr['allow']      = $cmppp_mp_pricing_single_enabled;
				$mpsingleIndexArr['number']     = $cmppp_mp_pricing_single_number;
				$mpsingleIndexArr['unit']       = $cmppp_mp_pricing_single_unit;
				$mpsingleIndexArr['price']      = $cmppp_mp_pricing_single_price;
				$mpsingleIndexArr['product_id'] = 0;
				$mp->setPricingSingleIndex( $mpsingleIndexArr );

			}

			static::$suspendActions --;
		}
	}

	static function getPaybox( Post $post = null ) {
		$mp = new Micropayments( $post );

		if ( $post && $mp->isPaid() && is_user_logged_in() ) {

			$pricingGroups = $mp::getPostPricingGroupsIndexes( $post->getId() );

			$groups = [];
			if ( ! empty( $pricingGroups ) ) {
				foreach ( $pricingGroups as $group_index ) {
					$plans = $mp::getSubscriptionPlansForGroup( $group_index );

					if ( ! empty( $plans ) ) {
						foreach ( $plans['prices'] as &$price ) {
							$price['seconds'] = Micropayments::period2seconds( $price['number'] . $price['unit'] );
							$price['period']  = $price['number'] . $price['unit'];
						}

						$plans['groupIndex'] = $group_index;

						$groups[] = $plans;
					}
				}
			}

			$cmppp_mp_pricing_group = get_post_meta( $post->getID(), 'cmppp_mp_pricing_group', true );
			$groupEnabled           = ! empty( $cmppp_mp_pricing_group ) ? 1 : 0;

			$cmppp_mp_pricing_single = get_post_meta( $post->getID(), 'cmppp_mp_pricing_single', true );
			$singleEnabled           = ( isset( $cmppp_mp_pricing_single['allow'] ) && $cmppp_mp_pricing_single['allow'] == '1' ) ? 1 : 0;

			$authorEnabled           = Settings::getOption( Settings::OPTION_MICROPAYMENTS_AUTHORS );
			$authorDonationEnabled   = Settings::getOption( Settings::OPTION_MICROPAYMENTS_AUTHORS_DONATION );
			$pricingAuthorPointsMode = $mp->getPricingAuthorPointsMode();
			if ( $pricingAuthorPointsMode == '' ) {
				$pricingAuthorPointsMode = 'free';
			}
			$pricingAuthorPoints = $mp->getPricingAuthorPoints();
			if ( $pricingAuthorPoints == '' ) {
				$pricingAuthorPoints = 0;
			}

			$cmppp_mp_pricing_author               = array();
			$cmppp_mp_pricing_author['product_id'] = '0';
			$cmppp_mp_pricing_author['number']     = '1';
			$cmppp_mp_pricing_author['unit']       = 'l';
			$cmppp_mp_pricing_author['price']      = $pricingAuthorPoints;

			$postId                = $post->getId();
			$nonce                 = wp_create_nonce( 'cmppp_subscription_activate' );
			$nonce_single          = wp_create_nonce( 'cmppp_subscription_activate_single' );
			$nonce_author          = wp_create_nonce( 'cmppp_subscription_activate_author' );
			$nonce_author_donation = wp_create_nonce( 'cmppp_subscription_activate_author_donation' );

			return self::loadFrontendView( 'paybox', compact( 'groups', 'postId', 'nonce', 'groupEnabled', 'singleEnabled', 'cmppp_mp_pricing_single', 'nonce_single', 'authorEnabled', 'cmppp_mp_pricing_author', 'nonce_author', 'authorDonationEnabled', 'pricingAuthorPointsMode', 'nonce_author_donation' ) );
		}

		return "";
	}

	static function getRefundBox( Post $post ) {
		if ( $post and $mp = new Micropayments( $post ) and $mp->isPaid() and is_user_logged_in() ) {
			$sub           = new Subscription( $post );
			$subscriptions = $sub->getSubscriptions( null, 'amount', 'DESC' );
			if ( ! empty( $subscriptions ) ) {
				$postId           = $post->getId();
				$nonce            = wp_create_nonce( self::REFUND_NONCE );
				$reasons          = Settings::getOption( Settings::OPTION_REFUND_REASONS );
				$minutesForRefund = Settings::getOption( Settings::OPTION_REFUND_TIMEOUT_MINUTES );
				$subscription     = reset( $subscriptions );
				$points           = $subscription['amount'];
				$ajaxUrl          = admin_url( 'admin-ajax.php' );

				return self::loadFrontendView( 'refund', compact( 'reasons', 'postId', 'nonce', 'points', 'ajaxUrl', 'minutesForRefund' ) );
			}
		}
	}


	static function cmppp_subscription_activate() {

		header( 'content-type: application/json' );

		$post          = Post::getInstance( $_POST['postId'] );
		$subscription  = new Subscription( $post );
		$mp            = new Micropayments( $post );
		$pricingGroups = $mp::getPostPricingGroupsIndexes( $_POST['postId'] );
		$priceGroupIndex = $_POST['priceGroupIndex'];

		if ( is_user_logged_in() && $mp->isPaid()
		     && ! empty( $pricingGroups )
		     && wp_verify_nonce( $_POST['nonce'], 'cmppp_subscription_activate' )
		     && ! $subscription->isSubscriptionActive() ) {

			$plans   = $mp::getSubscriptionPlansForGroup( $priceGroupIndex );
			$price   = $plans['prices'][ $_POST['priceIndex'] ];
			$userId  = get_current_user_id();
			$points  = $price['price'];
			$seconds = Micropayments::period2seconds( $price['number'] . $price['unit'] );

			$authorId = get_post($_POST['postId'])->post_author;
			$userPercent = get_user_meta($authorId, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR, true);
			$userPercent = $userPercent < 100 ? $userPercent : 100;
			$userPercent = $userPercent > 1 ? $userPercent : 1;
			$pointsToAuthor = round(($userPercent / 100) * $points, 2);


			try {
				/* Charging percent of the payment to author's wallet*/
				if(Settings::getOption( Settings::OPTION_PERCENTAGE) == 'percentage_on'){
					if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {
						if(Micropayments::chargeUserWallet( $authorId, $pointsToAuthor )){
							$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $priceGroupIndex );
							$response = array(
								'success' => true,
								'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
								'postUrl' => $post->getPermalink(),
							);
						}
						else {
							Micropayments::chargeUserWallet( $userId, $points );
							throw new \Exception('Failed to charge author\'s wallet');
						}
					} else {
						throw new \Exception( 'Failed to charge user\'s wallet.' );
					}
				} else {
					if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {
						$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $priceGroupIndex );
						$response = array(
							'success' => true,
							'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
							'postUrl' => $post->getPermalink(),
						);
					} else {
						throw new \Exception( 'Failed to charge user\'s wallet.' );
					}
				}

			} catch ( \Exception $e ) {
				$response = array( 'success' => false, 'msg' => Labels::getLocalized( $e->getMessage() ) );
			}


		} else {
			$response = array( 'success' => false, 'msg' => 'An error occured. Please try again.' );
		}

		echo json_encode( $response );

		exit;

	}

	static function cmppp_subscription_activate_single() {

		header( 'content-type: application/json' );

		if ( is_user_logged_in() and ! empty( $_POST['postId'] )
		                             and $post = Post::getInstance( $_POST['postId'] )
		                                 and $subscription = new Subscription( $post )
		                                     and $mp = new Micropayments( $post )
		                                         and $mp->isPaid()
		                                             and ! empty( $_POST['nonce'] ) and ( wp_verify_nonce( $_POST['nonce'], 'cmppp_subscription_activate_single' ) )
		                                                                                and ! $subscription->isSubscriptionActive() ) {

			$userId  = get_current_user_id();
			$points  = $_POST['price'];
			$seconds = Micropayments::period2seconds( $_POST['number'] . $_POST['unit'] );

			$authorId = get_post($_POST['postId'])->post_author;
			$userPercent = get_user_meta($authorId, Settings::OPTION_PERCENT_OF_POINTS_TO_AUTHOR, true);
			$userPercent = $userPercent < 100 ? $userPercent : 100;
			$userPercent = $userPercent > 1 ? $userPercent : 1;
			$pointsToAuthor = round(($userPercent / 100) * $points, 2);

			try {
				if(Settings::getOption( Settings::OPTION_PERCENTAGE) == 'percentage_on'){
					if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {
						if(Micropayments::chargeUserWallet( $authorId, $pointsToAuthor )){
							$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $mp->getPricingGroupIndex() );
							$response = array(
								'success' => true,
								'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
								'postUrl' => $post->getPermalink(),
							);
						}
						else {
							Micropayments::chargeUserWallet( $userId, $points );
							throw new \Exception('Failed to charge author\'s wallet');
						}
					} else {
						throw new \Exception( 'Failed to charge user\'s wallet.' );
					}
				} else {
					if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {
						$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $mp->getPricingGroupIndex() );
						$response = array(
							'success' => true,
							'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
							'postUrl' => $post->getPermalink(),
						);
					} else {
						throw new \Exception( 'Failed to charge user\'s wallet.' );
					}
				}
			} catch ( \Exception $e ) {
				$response = array( 'success' => false, 'msg' => Labels::getLocalized( $e->getMessage() ) );
			}

		} else {
			$response = array( 'success' => false, 'msg' => 'An error occured. Please try again.' );
		}

		echo json_encode( $response );

		exit;

	}

	static function cmppp_subscription_activate_author() {

		header( 'content-type: application/json' );

		if ( is_user_logged_in() and ! empty( $_POST['postId'] )
		                             and $post = Post::getInstance( $_POST['postId'] )
		                                 and $subscription = new Subscription( $post )
		                                     and $mp = new Micropayments( $post )
		                                         and $mp->isPaid()
		                                             and ! empty( $_POST['nonce'] ) and ( wp_verify_nonce( $_POST['nonce'], 'cmppp_subscription_activate_author' ) )
		                                                                                and ! $subscription->isSubscriptionActive() ) {

			$userId  = get_current_user_id();
			$points  = $_POST['price'];
			$seconds = Micropayments::period2seconds( $_POST['number'] . $_POST['unit'] );

			try {
				if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {

					if ( isset( $_POST['author_id'] ) && $_POST['author_id'] > 0 ) {
						Micropayments::chargeUserWallet( $_POST['author_id'], + $points );
					}

					$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $mp->getPricingGroupIndex() );
					$response = array(
						'success' => true,
						'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
						'postUrl' => $post->getPermalink(),
					);
				} else {
					throw new \Exception( 'Failed to charge user\' wallet.' );
				}
			} catch ( \Exception $e ) {
				$response = array( 'success' => false, 'msg' => Labels::getLocalized( $e->getMessage() ) );
			}

		} else {
			$response = array( 'success' => false, 'msg' => 'An error occured. Please try again.' );
		}

		echo json_encode( $response );

		exit;

	}

	static function cmppp_subscription_activate_author_donation() {

		header( 'content-type: application/json' );

		if ( is_user_logged_in() and ! empty( $_POST['postId'] )
		                             and $post = Post::getInstance( $_POST['postId'] )
		                                 and $subscription = new Subscription( $post )
		                                     and $mp = new Micropayments( $post )
		                                         and $mp->isPaid()
		                                             and ! empty( $_POST['nonce'] ) and ( wp_verify_nonce( $_POST['nonce'], 'cmppp_subscription_activate_author_donation' ) )
		                                                                                and ! $subscription->isSubscriptionActive() ) {

			$userId  = get_current_user_id();
			$points  = $_POST['price'];
			$seconds = Micropayments::period2seconds( $_POST['number'] . $_POST['unit'] );

			try {
				if ( isset( $_POST['donationaction'] ) && $_POST['donationaction'] == '0' ) {
					$subscription->addSubscription( $userId, $seconds, 0, Micropayments::PAYMENT_PLUGIN_NAME, $mp->getPricingGroupIndex() );
					$response = array(
						'success' => true,
						'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), 0 ),
						'postUrl' => $post->getPermalink(),
					);
				} else {
					if ( Micropayments::chargeUserWallet( $userId, - $points ) ) {

						if ( isset( $_POST['author_id'] ) && $_POST['author_id'] > 0 ) {
							Micropayments::chargeUserWallet( $_POST['author_id'], + $points );
						}

						$subscription->addSubscription( $userId, $seconds, $points, Micropayments::PAYMENT_PLUGIN_NAME, $mp->getPricingGroupIndex() );
						$response = array(
							'success' => true,
							'msg'     => sprintf( Labels::getLocalized( 'msg_activation_success' ), $points ),
							'postUrl' => $post->getPermalink(),
						);
					} else {
						throw new \Exception( 'Failed to charge user\' wallet.' );
					}
				}
			} catch ( \Exception $e ) {
				$response = array( 'success' => false, 'msg' => Labels::getLocalized( $e->getMessage() ) );
			}

		} else {
			$response = array( 'success' => false, 'msg' => 'An error occured. Please try again.' );
		}

		echo json_encode( $response );

		exit;

	}

	static function cmppp_refund() {

		header( 'content-type: application/json' );
		$response = array( 'status' => 'error', 'msg' => Labels::getLocalized( 'refund_error_msg' ) );

		if ( ! empty( $_POST['nonce'] ) and wp_verify_nonce( $_POST['nonce'], self::REFUND_NONCE )
		                                    and ! empty( $_POST['postId'] ) and $post = Post::getInstance( $_POST['postId'] ) and $mp = new Micropayments( $post ) and ! empty( $_POST['reason'] ) ) {

			if ( $mp->canRefund() ) {

				$sub           = new Subscription( $post );
				$subscriptions = $sub->getSubscriptions( null, 'amount', 'DESC' );
				if ( $subscription = reset( $subscriptions ) ) {

					$points     = $subscription['amount'];
					$metaId     = $subscription['meta_id'];
					$reasons    = Settings::getOption( Settings::OPTION_REFUND_REASONS );
					$reasonsMap = array();
					foreach ( $reasons as $reason ) {
						$reasonsMap[ $reason['key'] ] = $reason['value'];
					}

					if ( isset( $reasonsMap[ $_POST['reason'] ] ) ) {
						$label = $reasonsMap[ $_POST['reason'] ];
					} else {
						$label = $_POST['reason'];
					}

					try {

						Micropayments::chargeUserWallet( get_current_user_id(), $points );
						$mp->setRefundReason( $metaId, $_POST['reason'], $label, $_POST['reason_text'] );
						Subscription::deactivateSubscription( $metaId );

						$response = array(
							'status' => 'ok',
							'msg'    => sprintf( Labels::getLocalized( 'refund_success_msg' ), $points )
						);

					} catch ( \Exception $e ) {
						$response['msg'] = Labels::getLocalized( 'refund_error_mp_msg' ) . ' ' . $e->getMessage();
					}

				}

			} else {
				$response['msg'] = Labels::getLocalized( 'refund_error_timeout_msg' );
			}

		}

		echo json_encode( $response );
		exit;

	}

	static function cmppp_format_amount_payed( $amount, $plugin ) {
		if ( empty( $plugin ) or $plugin == Micropayments::PAYMENT_PLUGIN_NAME ) {
			$amount = sprintf( Labels::getLocalized( 'mp_amount_payed_format' ), $amount );
		}

		return $amount;
	}

	static function comments_template( $template ) {
		if ( App::isLicenseOk() and $post = Post::getInstance( get_post() ) and $mp = new Micropayments( $post ) and $mp->isPaid() ) {
			$sub = new Subscription( $post );
			if ( ! $sub->isSubscriptionActive() and Settings::getOption( Settings::OPTION_HIDE_COMMENTS ) ) {
				$template = App::path( 'view/frontend/empty.php' );
			}
		}

		return $template;
	}

	static function cmppp_admin_show_post_prices( $postId ) {
		$post = Post::getInstance( $postId );

		if ( $post ) {
			$payments = [
				'Micropayments' => new Micropayments( $post ),
			];

			foreach ( $payments as $payment ) {
				$paymentInstanse = $payment::getInstance( $post );
				$groups          = $paymentInstanse::getPostPricingGroupsIndexes( $postId );

				if ( ! empty( $groups ) ) {
					foreach ( $groups as $group_index ) {
						$plans = $paymentInstanse::getSubscriptionPlansForGroup( $group_index );
						if ($plans['prices']) {
							foreach ( $plans['prices'] as $i => $cost ) {
								if ( isset( $cost['price'] ) && ! empty( $cost['price'] ) ) {
									printf( Labels::getLocalized( 'eddpay_period_for_amount' ), Subscription::period2date( $cost['period'] ), $cost['price'] );
									echo '<br>';
								}
							}
						}
					}
				}
			}

		}
	}

	static function cmppp_admin_show_post_pricing_group( $postId ) {
		if ( $post = Post::getInstance( $postId ) and $mp = new Micropayments( $post ) ) {

			$groups = Micropayments::getPostPricingGroupsIndexes( $postId );

			if ( ! empty( $groups ) ) {
				foreach ( $groups as $group_index ) {
					$plans = Micropayments::getSubscriptionPlansForGroup( $group_index );
					if ( ! empty( $plans ) ) {
						$url = remove_query_arg( self::PARAM_POST_FILTER_PRICING_GROUP, $_SERVER['REQUEST_URI'] );
						$url = add_query_arg( self::PARAM_POST_FILTER_PRICING_GROUP, $group_index, $url );
						printf( '<a href="%s">%s</a>', esc_attr( $url ), esc_html( $mp->getPricingGroupName( $group_index ) ) );
						echo "<br>";
					}
				}
			}
		}
	}

	static function pre_get_posts( \WP_Query $query ) {
		if ( is_admin() ) {
			if ( $pricingGroup = filter_input( INPUT_GET, self::PARAM_POST_FILTER_PRICING_GROUP ) ) {
				$query->set( 'meta_key', Micropayments::META_MP_PRICING_GROUP_INDEX );
				$query->set( 'meta_value', $pricingGroup );
			}
		}
	}

}
