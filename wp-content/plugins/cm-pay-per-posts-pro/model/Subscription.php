<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\controller\SubscriptionsController;
use com\cminds\payperposts\lib\Email;
use com\cminds\payperposts\helper\TimeHelper;
use com\cminds\payperposts\helper\Storage;

class Subscription extends Model {

	const META_MP_PRICING_GROUP_INDEX = 'cmppp_mp_pricing_group';
	const META_MP_SUBSCRIPTION = 'cmppp_mp_subscription';
	const META_MP_SUBSCRIPTION_START = 'cmppp_mp_subscription_start';
	const META_MP_SUBSCRIPTION_END = 'cmppp_mp_subscription_end';
	const META_MP_SUBSCRIPTION_DURATION = 'cmppp_mp_subscription_duration';
	const META_MP_SUBSCRIPTION_AMOUNT_PAID = 'cmppp_mp_subscription_points';
	const META_MP_SUBSCRIPTION_PAYMENT_PLUGIN = 'cmppp_mp_subscription_payment_plugin';
	const META_MP_SUBSCRIPTION_POST_ID = 'cmppp_mp_subscription_post_id';
	const META_MP_SUBSCRIPTION_BLOG_ID = 'cmppp_mp_subscription_blog_id';
	const META_MP_SUBSCRIPTION_PRICING_GROUP = 'cmppp_mp_subscription_pricing_group';
	const META_MP_SUBSCRIPTION_REFUND_REASON = 'cmppp_mp_subscription_refund_reason';
	const META_MP_SUBSCRIPTION_EXPIRATION_NOTIFICATION = 'cmppp_mp_subscription_expiration_notification';

	/**
	 * Post instance.
	 *
	 * @var Post
	 */
	protected $post;


	/**
	 * Construct.
	 *
	 * @param Post|int $post Post instance or post ID.
	 */
	public function __construct( $post = null ) {
		if ( ! is_null( $post ) && $post instanceof Post ) {
			$this->post = $post;
		}
	}


	/**
	 * Get instance by post.
	 *
	 * @param int|object|Post $post
	 *
	 * @return \com\cminds\payperposts\model\Subscription
	 */
	static function getInstance( $post = null ) {

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		if ( is_object( $post ) ) {
			if ( $post instanceof Post ) {
				// ok
			} else {
				$post = new Post( $post );
			}
		}

		if ( $post and is_object( $post ) and $post instanceof Post and in_array( $post->getPostType(), static::getSupportedPostTypes() ) ) {
			return new static( $post );
		} else {
			return new static();
		}
	}


	function getPost() {
		return $this->post;
	}


	function addSubscription( $userId, $periodSeconds, $amount, $plugin, $pricingGroupIndex, $start = null ) {

		if ( empty( $start ) ) {
			$start = time();
		}
		$end    = $start + $periodSeconds;
		$postId = ( ! is_null( $this->post ) ) ? $this->post->getId() : 0;


		$metaId = add_user_meta( $userId, self::META_MP_SUBSCRIPTION, $postId, $unique = false );
		if ( $metaId ) {

			$blogId = ( function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1 );

			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_START . '_' . $metaId, $start, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_END . '_' . $metaId, $end, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_DURATION . '_' . $metaId, $periodSeconds, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_AMOUNT_PAID . '_' . $metaId, $amount, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_PAYMENT_PLUGIN . '_' . $metaId, $plugin, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_POST_ID . '_' . $metaId, $postId, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_BLOG_ID . '_' . $metaId, $blogId, $unique = true );
			add_user_meta( $userId, self::META_MP_SUBSCRIPTION_PRICING_GROUP . '_' . $metaId, $pricingGroupIndex, $unique = true );

			$this->notifyAdmin( $userId, $start, $periodSeconds, $amount, $plugin );
			$this->notifyCustomer( $userId, $start, $periodSeconds, $amount, $plugin );

		}
	}


	function notifyAdmin( $userId, $start, $periodSeconds, $amount, $plugin ) {

		// send email to admin
		if ( Settings::getOption( Settings::OPTION_NEW_SUB_ADMIN_NOTIF_ENABLE ) and $user = get_userdata( $userId ) ) {
			$end = $start + $periodSeconds;

			$startdate = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $start );
			$enddate   = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $end );

			Email::send(
				$receivers = Settings::getOption( Settings::OPTION_NEW_SUB_ADMIN_NOTIF_EMAILS ),
				$subject = Settings::getOption( Settings::OPTION_NEW_SUB_ADMIN_NOTIF_SUBJECT ),
				$body = Settings::getOption( Settings::OPTION_NEW_SUB_ADMIN_NOTIF_TEMPLATE ),
				array(
					'[blogname]'   => get_option( 'blogname' ),
					'[home]'       => get_option( 'home' ),
					'[postname]'   => $this->post->getTitle(),
					'[permalink]'  => $this->post->getPermalink(),
					'[username]'   => $user->display_name,
					'[userlogin]'  => $user->user_login,
					'[startdate]'  => $startdate,
					'[enddate]'    => $enddate,
					'[duration]'   => Micropayments::seconds2period( $periodSeconds ),
					'[points]'     => $amount,
					'[amount]'     => $amount,
					'[plugin]'     => $plugin,
					'[reportlink]' => SubscriptionsController::getUrl(),
				)
			);
		}
	}

	function notifyCustomer($userId, $start, $periodSeconds, $amount, $plugin) {

		// send email for customer
		if ( Settings::getOption( Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_ENABLE ) and $user = get_userdata( $userId ) ) {
			$end = $start + $periodSeconds;

			$startdate = TimeHelper::showUserLocalDatetime( $start, $userId, true );
			$enddate   = TimeHelper::showUserLocalDatetime( $end, $userId, true );

			Email::send(
				$receivers = $user->user_email,
				$subject = Settings::getOption( Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_SUBJECT ),
				$body = Settings::getOption( Settings::OPTION_NEW_SUB_CUSTOMER_NOTIF_TEMPLATE ),
				array(
					'[blogname]'   => get_option( 'blogname' ),
					'[home]'       => get_option( 'home' ),
					'[postname]'   => ( $this->post ) ? $this->post->getTitle() : "",
					'[permalink]'  => ( $this->post ) ? $this->post->getPermalink() : "",
					'[username]'   => $user->display_name,
					'[userlogin]'  => $user->user_login,
					'[startdate]'  => $startdate,
					'[enddate]'    => $enddate,
					'[duration]'   => Micropayments::seconds2period( $periodSeconds ),
					'[points]'     => $amount,
					'[amount]'     => $amount,
					'[plugin]'     => $plugin,
					'[reportlink]' => SubscriptionsController::getUrl(),
				)
			);
		}
	}


	public static function notifyPurchaseConfirmation($userId, $periodSeconds, $amount, $plugin, $post_id, $start = null) {

		if ( Settings::getOption( Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_ENABLE ) and $user = get_userdata( $userId ) ) {

			$post =  Post::getInstance( $post_id );
			if ( empty( $start ) ) {
				$start = time();
			}
			$end = $start + $periodSeconds;

			$startdate = TimeHelper::showUserLocalDatetime( $start, $userId, true );
			$enddate   = TimeHelper::showUserLocalDatetime( $end, $userId, true );

			Email::send(
				$receivers = $user->user_email,
				$subject = Settings::getOption( Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_SUBJECT ),
				$body = Settings::getOption( Settings::OPTION_PURCHASE_CONFIRMATION_NOTIF_TEMPLATE ),
				array(
					'[blogname]'   => get_option( 'blogname' ),
					'[home]'       => get_option( 'home' ),
					'[postname]'   => ( $post) ? $post->getTitle() : "",
					'[permalink]'  => ( $post ) ? $post->getPermalink() : "",
					'[username]'   => $user->display_name,
					'[userlogin]'  => $user->user_login,
					'[startdate]'  => $startdate,
					'[enddate]'    => $enddate,
					'[duration]'   => Micropayments::seconds2period( $periodSeconds ),
					'[points]'     => $amount,
					'[amount]'     => $amount,
					'[plugin]'     => $plugin,
					'[reportlink]' => SubscriptionsController::getUrl(),
				)
			);
		}
	}

	public static function notifySubscriptionExpiration($subscription) {

		$startdate = TimeHelper::showUserLocalDatetime( $subscription['start'], $subscription['user_id'], true );
		$enddate   = TimeHelper::showUserLocalDatetime( $subscription['end'], $subscription['user_id'], true );

		$post =  Post::getInstance( $subscription['post_id'] );
		$user = get_userdata( $subscription['user_id'] );
		$periodSeconds = $subscription['end'] - $subscription['start'];
		Email::send(
			$receivers = $user->user_email,
			$subject = Settings::getOption( Settings::OPTION_SUBSCRIPTION_EXPIRE_SUBJECT ),
			$body = Settings::getOption( Settings::OPTION_SUBSCRIPTION_EXPIRE_TEMPLATE ),
			array(
				'[blogname]'   => get_option( 'blogname' ),
				'[home]'       => get_option( 'home' ),
				'[postname]'   => ( $post) ? $post->getTitle() : "",
				'[permalink]'  => ( $post ) ? $post->getPermalink() : "",
				'[username]'   => $user->display_name,
				'[userlogin]'  => $user->user_login,
				'[startdate]'  => $startdate,
				'[enddate]'    => $enddate,
				'[duration]'   => Micropayments::seconds2period( $periodSeconds ),
				'[points]'     => $subscription['amount'],
				'[amount]'     => $subscription['amount'],
				'[plugin]'     => $subscription['plugin'],
				'[reportlink]' => SubscriptionsController::getUrl(),
			)
		);
	}


	function isSubscriptionActive( $userId = null, $current_post = null ) {

		if ( Settings::getOption( Settings::OPTION_SUBSCRIPTION_FORM_NOT_FOR_OWNER ) && isset( $current_post ) && ! is_null( $current_post ) ) {
			if ( ( $userId > 0 && $userId == $current_post->getPostAuthor() ) || ( $userId > 0 && current_user_can( 'administrator' ) ) ) {
				return true;
			}
		}


		$sub = $this->getSubscriptions( $userId );

		return ( count( $sub ) > 0 );
	}

	function isGuestSubscriptionActive( $payments = [] ) {

		if ( ! empty( $payments ) ) {
			foreach ( $payments as $payment ) {

				if ( $payment == 'WooCommerce' ) {
					if ( ! empty( $_COOKIE ) ) {
						foreach ( $_COOKIE as $key => $ck ) {
							if ( strpos( $key, $payment . '_paid_product_id_' ) !== false ) {

								if ( $this->getGuestSubscriptions( $payment, $_COOKIE[ $key ] ) ) {
									return true;
								}
							}
						}
					}
				}

				if ( $payment == 'EDD' ) {
					$rows = Storage::search( 'EDD_paid_product_' );

					if ( ! empty( $rows ) ) {
						foreach ( $rows as $option ) {
							$download_id = $option->option_value;
							if ( $this->getGuestSubscriptions( $payment, $download_id ) ) {
								return true;
							}
						}
					}
				}

			}
		}

		return false;
	}

	public function getBaseSubscriptionsSql( $userId ) {
		global $wpdb;

		$blogId = ( function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1 );

		return $wpdb->prepare( "
			SELECT
				sub.umeta_id		AS meta_id,
				p.meta_value		AS post_id,
				start.meta_value	AS start_date,
				end.meta_value		AS end_date,
				duration.meta_value	AS duration,
				amount.meta_value	AS amount,
				plugin.meta_value	AS plugin
			FROM $wpdb->usermeta sub
			JOIN $wpdb->usermeta `start` ON start.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta `end` ON end.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta duration ON duration.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta amount ON amount.meta_key = CONCAT(%s, sub.umeta_id)
			LEFT JOIN $wpdb->usermeta plugin ON plugin.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta p ON p.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta b ON b.meta_key = CONCAT(%s, sub.umeta_id)
			JOIN $wpdb->usermeta pg ON pg.meta_key = CONCAT(%s, sub.umeta_id)
			WHERE
				sub.meta_key = %s
				AND sub.user_id = %d
				AND b.meta_value = %s
				AND start.meta_value <= UNIX_TIMESTAMP()
				AND end.meta_value > UNIX_TIMESTAMP()
			",
			self::META_MP_SUBSCRIPTION_START . '_',
			self::META_MP_SUBSCRIPTION_END . '_',
			self::META_MP_SUBSCRIPTION_DURATION . '_',
			self::META_MP_SUBSCRIPTION_AMOUNT_PAID . '_',
			self::META_MP_SUBSCRIPTION_PAYMENT_PLUGIN . '_',
			self::META_MP_SUBSCRIPTION_POST_ID . '_',
			self::META_MP_SUBSCRIPTION_BLOG_ID . '_',
			self::META_MP_SUBSCRIPTION_PRICING_GROUP . '_',
			self::META_MP_SUBSCRIPTION,
			$userId,
			$blogId
		);
	}


	public function getSubscriptions( $userId = null, $orderBy = 'end_date', $order = 'ASC' ) {
		global $wpdb;
		if ( is_null( $userId ) ) {
			$userId = get_current_user_id();
		}

		if ( ! $userId ) {
			return [];
		}

		$sql = $this->getBaseSubscriptionsSql( $userId );

		$mode = Settings::getOption( Settings::OPTION_SUBSCRIPTION_MODE );

		$res = false;

		if ( $mode == Settings::SUBSCRIPTION_MODE_POST ) {
			$res = $this->_mode_post( $sql );
		}

//		if ( $mode == Settings::SUBSCRIPTION_MODE_PRICING_GROUP ) {
//			$res = $this->_mode_group( $sql );
//		}

		if ( $mode == Settings::SUBSCRIPTION_MODE_PRICING_GROUP || $mode == Settings::SUBSCRIPTION_MODE_PRICING_GROUP_OR_POST ) {
			$res_single = $this->_mode_post( $sql );
			$res_group  = $this->_mode_group( $sql );

			$res = array_merge( $res_single, $res_group );
		}

		// if categories are enabled
		if ( Settings::getOption( Settings::OPTION_ENABLE_CATEGORIES_PRICES ) ) {
			$categories_prices = $this->getCategoriesPrices();
			$res               = array_merge( $res, $categories_prices );
		}

		return $res;
	}

	public function getSubscriptionsForUserGroups( $userId = null ) {
		if ( is_null( $userId ) ) {
			$userId = get_current_user_id();
		}

		if ( ! $userId ) {
			return [];
		}

		$groups = [];

		$sql = $this->getBaseSubscriptionsSql( $userId );
		$sql = str_replace( 'SELECT', 'SELECT pg.meta_value AS pricing_group, ', $sql );

		$paymentMethod        = null;
		$payment_plugin_short = '';

		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			$paymentMethod        = new PostInstantPayment();
			$payment_plugin_short = 'edd';
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$paymentMethod        = new PostWooPayment();
			$payment_plugin_short = 'woo';
		}

		if ( ! is_null( $paymentMethod ) ) {
			$plugin = $paymentMethod::PAYMENT_PLUGIN_NAME;

			global $wpdb;
			$sql .= $wpdb->prepare( " AND plugin.meta_value = %s ", $plugin );
			$wpdb->query( 'SET SQL_BIG_SELECTS = 1' );

			$groups = $wpdb->get_results( $sql, ARRAY_A );

			if ( ! empty( $groups ) ) {
				$group_options = get_option( 'cmppp_' . $payment_plugin_short . '_pricing_groups', [] );
				if ( ! empty( $group_options ) ) {
					foreach ( $groups as &$group ) {
						$group['group_name'] = $group_options[ $group['pricing_group'] ]['name'];
					}
				}
			}
		}


		return $groups;
	}

	private function _mode_post( $sql ) {
		global $wpdb;
		$sql .= $wpdb->prepare( " AND p.meta_value = %d", $this->post->getId() );


		$wpdb->query( 'SET SQL_BIG_SELECTS = 1' );

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	private function _mode_group( $sql ) {
		global $wpdb;
		// add pricing group to condition, no matter what post
		$mp                = new Micropayments( $this->post );
		$pricingGroupIndex = null;
		if ( $mp->isPaid() ) { // Micropayment
			$plugin            = Micropayments::PAYMENT_PLUGIN_NAME;
			$sql               .= $wpdb->prepare( " AND (plugin.meta_value = %s OR plugin.meta_value IS NULL) ", $plugin );
			$pricingGroupIndex = $mp->getPricingGroupIndex();

		} else if ( $edd = new PostInstantPayment( $this->post ) and $edd->isPaid() ) { // EDD
			$plugin            = PostInstantPayment::PAYMENT_PLUGIN_NAME;
			$sql               .= $wpdb->prepare( " AND plugin.meta_value = %s ", $plugin );
			$pricingGroupIndex = $edd->getPricingGroupIndex();

		} else if ( $woo = new PostWooPayment( $this->post ) and $woo->isPaid() ) { // WooCommerce
			$plugin            = PostWooPayment::PAYMENT_PLUGIN_NAME;
			$sql               .= $wpdb->prepare( " AND plugin.meta_value = %s ", $plugin );
			$pricingGroupIndex = $woo->getPricingGroupIndex();
		}

		if ( empty( $pricingGroupIndex ) ) {
			return [];
		}

		// TODO: no need this bull shit
//		$sql .= " AND pg.meta_value = '$pricingGroupIndex'";
		// TODO: There will be new way to store GROUPS -- in separated table


		$wpdb->query( 'SET SQL_BIG_SELECTS = 1' );

		return $wpdb->get_results( $sql, ARRAY_A );
	}


	public function getGuestSubscriptions( $payment, $payment_product_id ) {
		$post_id = $this->post->getId();

		if ( ! isset( $payment_product_id ) || empty( $payment_product_id ) ) {
			return false;
		}

		if ( $payment == 'WooCommerce' ) {
			$single = get_post_meta( $post_id, 'cmppp_woo_pricing_single', true );
			$groups = get_post_meta( $post_id, 'cmppp_woo_pricing_group_index', true );

			if ( ! empty( $single ) && isset( $single['product_id'] ) && $single['product_id'] == $payment_product_id ) {
				return true;
			}

			if ( ! empty( $groups ) ) {
				$woo_product_pricing_group = get_post_meta( $payment_product_id, 'cmppp_pricing_group', true );

				if ( ! empty( $woo_product_pricing_group ) && in_array( $woo_product_pricing_group, $groups ) ) {
					return true;
				}
			}
		}

		if ( $payment == 'EDD' ) {
			$EddPayment = PostInstantPayment::getInstance( $this->post );
			$all_prices = $EddPayment->getPostsPrices();

			foreach ( $all_prices as $type => $prices ) {
				// groups
				if ( $type == 'groups' && ! empty( $prices ) ) {
					foreach ( $prices as $group_index ) {
						$post_ids = PostInstantPayment::getProductIdByGroupIndex( $group_index );
						if ( in_array( $payment_product_id, $post_ids ) ) {
							return true;
						}
					}
				}

				// categories
				if ( $type == 'category_downloads' && ! empty( $prices ) ) {
					foreach ( $prices as $price ) {
						if ( $price['download_id'] == $payment_product_id ) {
							return true;
						}
					}
				}

				// single
				if ( $type == 'single' && isset( $prices['allow'] ) && $prices['allow'] == 1 && $prices['product_id'] == $payment_product_id ) {
					return true;
				}

			}
		}

		return false;
	}

	public function getCategoriesPrices( $userId = null ) {
		global $wpdb;

		if ( ! class_exists( 'EDD_Payment' ) ) {
			return [];
		}

		if ( is_null( $userId ) ) {
			$userId = get_current_user_id();
		}

		$categories_prices = [];

		if ( Settings::getOption( Settings::OPTION_ENABLE_CATEGORIES_PRICES ) ) {

			$query = "SELECT p.ID as payment_id, p.post_date
		         FROM {$wpdb->prefix}posts as p
				 LEFT JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
				 WHERE p.post_type='edd_payment' 
				 AND pm.meta_key='_edd_payment_user_id' AND pm.meta_value='{$userId}'";

			$user_payments = $wpdb->get_results( $query, ARRAY_A );


			foreach ( $user_payments as $up ) {
				$payment = new \EDD_Payment( $up['payment_id'] );
				$meta    = $payment->get_meta();

				$download = $meta['downloads'][0];

				$query = "SELECT pm2.meta_value as period, pm3.meta_value as unit, pm4.meta_value as category_id
                         FROM {$wpdb->prefix}postmeta as pm2
                         LEFT JOIN {$wpdb->prefix}postmeta as pm3 ON pm3.post_id=pm2.post_id
                         LEFT JOIN {$wpdb->prefix}postmeta as pm4 ON pm4.post_id=pm2.post_id
                         LEFT JOIN {$wpdb->prefix}term_relationships as tr ON tr.term_taxonomy_id=pm4.meta_value
                         WHERE pm2.post_id='{$download['id']}' 
                         AND pm2.meta_key='cmppp_period' AND pm3.meta_key='cmppp_unit'
                         AND pm4.meta_key='cmppp_category_id' AND tr.object_id='{$this->post->getId()}'
                        ";

				$download_meta = $wpdb->get_row( $query, ARRAY_A );


				if ( empty( $download_meta ) || empty( $download_meta['unit'] ) || empty( $download_meta['period'] ) ) {
					continue;
				}

				$sec = TimeHelper::period2seconds( $download_meta['period'] . $download_meta['unit'] );

				$download['post_date'] = $up['post_date'];

				$date = new \DateTime( $up['post_date'] );
				$date->modify( "+{$sec} seconds" );
				$expired_date = $date->format( "Y-m-d H:i:s" );


				// TODO: save $expired_date to payment metadata and get them.
				// TODO: Mb save it after creating payment in db
				// TODO: (too long to implement, mb someone else implement it, beacause the old one didn't do that. (not me))

				if ( $expired_date < date( "Y-m-d H:i:s" ) ) {
					continue;
				}

				$categories_prices[] = $download;
			}
		}

		return $categories_prices;
	}

	function getSubscription() {
		$subscriptions = $this->getSubscriptions( null, 'amount', 'DESC' );

		return reset( $subscriptions );
	}


	function getMetaId() {
		if ( $subscription = $this->getSubscription() ) {
			return $subscription['meta_id'];
		}

		return 0;
	}


	function remove() {
		if ( $metaId = $this->getMetaId() ) {
			return self::removeSubscription( $metaId );
		}

		return 0;
	}


	static function deactivateSubscription( $metaId ) {
		global $wpdb;
		$metaKey = self::META_MP_SUBSCRIPTION_END . '_' . $metaId;

		return $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->usermeta SET meta_value = UNIX_TIMESTAMP() WHERE meta_key = %s", $metaKey ) );
	}


	static function removeSubscription( $metaId ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE umeta_id = %d OR meta_key IN (%s, %s, %s, %s, %s, %s, %s, %s)",
			$metaId,
			self::META_MP_SUBSCRIPTION_START . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_END . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_DURATION . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_AMOUNT_PAID . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_PAYMENT_PLUGIN . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_POST_ID . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_BLOG_ID . '_' . $metaId,
			self::META_MP_SUBSCRIPTION_PRICING_GROUP . '_' . $metaId
		) );
	}


	static function period2seconds( $period ) {
		$units = array(
			'min' => 60,
			'h'   => 3600,
			'd'   => 3600 * 24,
			'w'   => 3600 * 24 * 7,
			'm'   => 3600 * 24 * 30,
			'y'   => 3600 * 24 * 365,
			'l'   => 3600 * 24 * 365 * 100
		);
		$unit  = preg_replace( '/[0-9 ]/', '', $period );
		if ( isset( $units[ $unit ] ) ) {
			$number = preg_replace( '/[^0-9]/', '', $period );

			return $number * $units[ $unit ];
		}
	}


	static function seconds2period( $seconds ) {
		$units    = array(
			'minute'   => 60,
			'hour'     => 3600,
			'day'      => 3600 * 24,
			'week'     => 3600 * 24 * 7,
			'month'    => 3600 * 24 * 30,
			'year'     => 3600 * 24 * 365,
			'lifetime' => 3600 * 24 * 365 * 100
		);
		$result   = $seconds;
		$lastUnit = 'second';
		foreach ( $units as $unit => $sec ) {
			if ( $seconds / $sec < 1 ) {
				break;
			} else {
				$result   = $seconds / $sec;
				$lastUnit = $unit;
			}
		}
		//return $result .' '. Labels::getLocalized($lastUnit . ($result == 1 ? '' : 's'));
		if ( $lastUnit == 'lifetime' ) {
			return Labels::getLocalized( $lastUnit );
		} else {
			return $result . ' ' . Labels::getLocalized( $lastUnit . ( $result == 1 ? '' : 's' ) );
		}
	}


	static function period2date( $period ) {
		$units = array(
			'min' => 'minute',
			'h'   => 'hour',
			'd'   => 'day',
			'w'   => 'week',
			'm'   => 'month',
			'y'   => 'year',
			'l'   => 'lifetime'
		);
		$unit  = preg_replace( '/[0-9\s]/', '', $period );
		if ( isset( $units[ $unit ] ) ) {
			$number = preg_replace( '/[^0-9]/', '', $period );
			//return $number .' '. Labels::getLocalized($units[$unit] . ($number == 1 ? '' : 's'));
			if ( $units[ $unit ] == 'lifetime' ) {
				$unitname = $units[ $unit ];
			} else {
				$unitname = $units[ $unit ] . ( $number == 1 ? '' : 's' );
			}
			if ( $units[ $unit ] == 'lifetime' ) {
				return Labels::getLocalized( $unitname );
			} else {
				return $number . ' ' . Labels::getLocalized( $unitname );
			}
		}
	}


	static function getSupportedPostTypes() {
		$val = Settings::getOption( Settings::OPTION_SUPPORTED_POST_TYPES );
		if ( ! is_array( $val ) ) {
			$val = array();
		}

		return $val;
	}


}
