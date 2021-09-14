<?php

namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;

class Micropayments extends PaymentMethod implements IPaymentMethod {

	const PAYMENT_PLUGIN_NAME = 'CM Micropayments';

	const META_MP_PRICING_GROUP_INDEX = 'cmppp_mp_pricing_group';
	const META_PRICING_GROUP_INDEX = 'cmppp_mp_pricing_group';
	const META_MP_PRICING_SINGLE_INDEX = 'cmppp_mp_pricing_single';

	protected static $checkConfigFilters = array(
		'cm_micropayments_are_points_defined'          => 'Points prices have to be defined to purchase points by users.',
		'cm_micropayments_are_wallets_assigned'        => 'The "Assign wallets to customers" option has to be enabled.',
		'cm_micropayments_are_paypal_settings_defined' => 'PayPal settings are not defined.',
		'cm_micropayments_is_wallet_page'              => 'The Wallet page is not defined.',
		'cm_micropayments_is_checkout_page'            => 'The Checkout page is not defined.',
	);

	public function __construct( $post = null ) {
		if ( ! is_null( $post ) && $post instanceof Post ) {
			$this->post = $post;
		}
	}

	public static function getMetaPricingGroupIndexName() {
		return "cmppp_mp_pricing_group";
	}

	static function getInstance( Post $post ) {
		return new static( $post );
	}


	function setPricingSingleIndex( $singleIndex ) {
		update_post_meta( $this->post->getId(), self::META_MP_PRICING_SINGLE_INDEX, $singleIndex );
	}

	function getPricingSingleIndex() {
		return get_post_meta( $this->post->getId(), self::META_MP_PRICING_SINGLE_INDEX, $single = true );
	}

	function getPricingAuthorPointsMode() {
		return get_post_meta( $this->post->getId(), 'cm-usersp_points_mode', $single = true );
	}

	function getPricingAuthorPoints() {
		return get_post_meta( $this->post->getId(), 'cm-usersp_points', $single = true );
	}

	function isPaid() {
		$pricingGroup = self::getPostPricingGroupsIndexes( $this->post->getId() );

		$pricingSingle           = $this->getPricingSingleIndex();

		$pricingAuthor           = Settings::getOption( Settings::OPTION_MICROPAYMENTS_AUTHORS );
		$pricingAuthorDonation   = Settings::getOption( Settings::OPTION_MICROPAYMENTS_AUTHORS_DONATION );
		$pricingAuthorPointsMode = $this->getPricingAuthorPointsMode();
		$pricingAuthorPoints     = $this->getPricingAuthorPoints();

		if ( Micropayments::isConfigured() ) {
			if ( ! empty( $pricingGroup ) || ( isset( $pricingSingle['allow'] ) && $pricingSingle['allow'] == '1' ) || ( $pricingAuthor == '1' && $pricingAuthorPointsMode == 'points' && $pricingAuthorPoints > 0 ) || ( $pricingAuthorDonation == '1' && $pricingAuthorPointsMode != 'points' ) ) {
				return 1;
			} else {

				return 0;
			}
		} else {
			return 0;
		}
	}

	function canRefund() {
		if ( Settings::getOption( Settings::OPTION_REFUND_ENABLED ) and is_user_logged_in() ) {
			if ( $this->isPaid() and $sub = new Subscription( $this->post ) and $subscriptions = $sub->getSubscriptions( null, 'start_date', 'ASC' ) ) {
				$subscription = reset( $subscriptions );
				if ( $subscription['start_date'] + Settings::getOption( Settings::OPTION_REFUND_TIMEOUT_MINUTES ) * 60 > time() ) {
					return true;
				}
			}
		}

		return false;
	}

	function setRefundReason( $metaKey, $reasonKey, $reasonLabel, $comment ) {
		$data = array( 'key' => $reasonKey, 'label' => $reasonLabel, 'comment' => $comment );
		update_user_meta( $this->post->getId(), Subscription::META_MP_SUBSCRIPTION_REFUND_REASON . '_' . $metaKey, $data );
	}

	static function init() {

		parent::init();

		if ( function_exists( 'CMMicropaymentPlatformInit' ) ) {
			CMMicropaymentPlatformInit();
		}

		if ( static::isMicroPaymentsAvailable() ) { // Setup backend hooks

			add_filter( 'cmppp_settings_pages_groups', function ( $subcategories ) {
				$subcategories['micropayments']['general'] = 'General';

				return $subcategories;
			} );

			if ( static::isConfigured() ) { // Setup frontend hooks

			} else {
				add_action( 'admin_notices', function () {
					if ( Micropayments::isMicroPaymentsAvailable() ) {
						Micropayments::displayAdminWarning();
					}
				} );
			}

		}

	}

	static function isConfigured() {
		return ( static::isMicroPaymentsAvailable() );
	}

	static function getPayedPlansIntervals() {
		$intervals = array_filter( (array) Settings::getOption( Settings::OPTION_MICROPAYMENTS_INTERVALS ) );
		$units     = array(
			'min' => 60,
			'h'   => 3600,
			'd'   => 3600 * 24,
			'w'   => 3600 * 24 * 7,
			'm'   => 3600 * 24 * 30,
			'y'   => 3600 * 24 * 365,
			'l'   => 3600 * 24 * 365 * 100
		);
		foreach ( $intervals as &$interval ) {
			$unit   = preg_replace( '/[0-9]/', '', $interval );
			$number = preg_replace( '/[^0-9]/', '', $interval );
			if ( isset( $units[ $unit ] ) and ! empty( $number ) ) {
				$interval = array(
					'value'   => $interval,
					'seconds' => $number * $units[ $unit ],
					'number'  => $number,
					'unit'    => $unit,
				);
			} else {
				$interval = null;
			}
		}

		return array_filter( $intervals );
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
		$unit  = preg_replace( '/[0-9]/', '', $period );
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

		if ( $lastUnit == 'lifetime' ) {
			return \__( $lastUnit );
		} else {
			return $result . ' ' . \__( $lastUnit . ( $result == 1 ? '' : 's' ) );
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
		$unit  = preg_replace( '/[0-9]/', '', $period );
		if ( isset( $units[ $unit ] ) ) {
			$number = preg_replace( '/[^0-9]/', '', $period );
			if ( $units[ $unit ] == 'lifetime' ) {
				$unitname = $units[ $unit ];
			} else {
				$unitname = $units[ $unit ] . ( $number == 1 ? '' : 's' );
			}
			if ( $units[ $unit ] == 'lifetime' ) {
				return \__( $unitname );
			} else {
				return $number . ' ' . \__( $unitname );
			}
		}
	}

	static function displayAdminWarning( $class = null ) {
		if ( empty( $class ) ) {
			$class = 'error';
		}
		$reasons = '';
		foreach ( static::$checkConfigFilters as $filter => $msg ) {
			if ( ! apply_filters( $filter, false ) ) {
				$reasons .= sprintf( '<li>%s</li>', __( $msg ) );
			}
		}
		if ( $reasons ) {
			printf( '<div class="%s"><p>%s</p><ul style="list-style:disc;margin:0 0 1em 2em;">%s</ul><p>%s</p></div>',
				esc_attr( $class ),
				sprintf( __( '<strong>%s</strong> would not integrate with the <strong>CM Micropayments</strong> plugin because of the following reasons:' ), App::getPluginName() ),
				$reasons,
				sprintf( '<a href="%s" class="button">%s</a>',
					esc_attr( admin_url( 'admin.php?page=cm-micropayment-platform-settings' ) ),
					__( 'CM Micropayments Settings' )
				)
			);
		}
	}

	function initPayment( array $subscriptionPlan, $callbackUrl ) {

	}

	function initSinglePayment( array $singlePlan, $callbackUrl ) {

	}

	/**
	 * Check whether MicroPayments platform is available and configured.
	 *
	 * @return boolean
	 */
	static function isMicroPaymentsAvailable() {
		return apply_filters( 'cm_micropayments_is_working', false );
	}

	static function isAvailable() {
		return static::isMicroPaymentsConfigured();
	}

	static function isMicroPaymentsConfigured() {
		if ( static::isMicroPaymentsAvailable() ) {
			foreach ( static::$checkConfigFilters as $filter => $msg ) {
				if ( ! apply_filters( $filter, false ) ) {
					return false;
				}
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if wallet assigned to given user ID exists.
	 *
	 * @param int $userId
	 *
	 * @return boolean
	 */
	static function checkUsersWalletExists( $userId ) {
		$userWallet = apply_filters( 'cm_micropayments_user_wallet_id', $userId );

		return ! empty( $userWallet );
	}

	/**
	 * Check if user has enough points.
	 *
	 * @param int $userId
	 * @param int $points
	 */
	static function hasUserEnoughPoints( $userId, $points ) {
		if ( $user = get_user_by( 'id', $userId ) ) {
			$result = apply_filters( 'user_has_enough_points', array(
				'username' => $user->user_login,
				'points'   => abs( $points )
			) );

			return ( ! empty( $result['success'] ) );
		}

		return false;
	}

	/**
	 * Charge user wallet.
	 *
	 * @param int $userId
	 * @param int $points Positive or negative integer or zero.
	 *
	 * @throws com\cminds\payperposts\model\NotEnoughPointsException
	 */
	static function chargeUserWallet( $userId, $points ) {
		if ( ! static::checkUsersWalletExists( $userId ) ) {
			throw new MissingUserWalletException;
		}
		if ( $points < 0 ) {
			if ( ! static::hasUserEnoughPoints( $userId, abs( $points ) ) ) {
				throw new NotEnoughPointsException;
			}
		}
		$args   = array( 'user_id' => $userId, 'amount' => $points );
		$result = apply_filters( 'charge_user_wallet', $args );
		if ( $result ) {
			return true;
		} else {
			return false;
		}
	}

	public function getUsersWalletURL() {
		return apply_filters( 'cm_micropayments_user_wallet_url', array() );
	}

	public function getPointsPurchaseURL() {
		return apply_filters( 'cm_micropayments_checkout_url', array() );
	}

	public static function getPricingGroups() {
		if ( Settings::getOption( Settings::OPTION_MICROPAYMENTS_GROUPS ) ) {
			return Settings::getOption( Settings::OPTION_MICROPAYMENTS_GROUPS );
		} else {
			return array();
		}
	}

}

// ------------------------------------------------------------------------------------------------------------------------------
// Exceptions

class MicropaymentsException extends \Exception {
	const ERROR_MSG = 'An error occured in the CM MicroPayments module. Please try again.';

	function __construct() {
		parent::__construct( Labels::getLocalized( static::ERROR_MSG ) );
	}

}

class NotEnoughPointsException extends MicropaymentsException {
	const ERROR_MSG = 'mp_error_not_enough_points';
}

class MissingUserWalletException extends MicropaymentsException {
	const ERROR_MSG = 'mp_error_wallet_not_exists';
}
