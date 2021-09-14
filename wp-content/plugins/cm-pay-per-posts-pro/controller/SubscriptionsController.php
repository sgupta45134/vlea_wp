<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\helper\CSVHelper;
use com\cminds\payperposts\model\PaymentMethodFactory;
use com\cminds\payperposts\model\SubscriptionReport;
use com\cminds\payperposts\model\Subscription;
use com\cminds\payperposts\model\Post;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Micropayments;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\App;

class SubscriptionsController extends Controller {

	const TITLE = 'Subscriptions';
	const NONCE_ADD = 'cmppp_subscription_add';
	const NONCE_ACTION = 'cmppp_subscription_action';
    const PARAM_ACTION = 'cmppp_action';
    const ACTION_DOWNLOAD_CSV = 'cmppp_download_csv';
    const CRON_NOTIFICATION = 'cmppp_expired_subscription_notification';

    protected static $actions = array(
	    array( 'name' => 'admin_menu', 'priority' => 15 ),
        'admin_init',
	    self::CRON_NOTIFICATION
    );
	protected static $ajax = array( 'cmppp_user_suggest', 'cmppp_post_suggest', 'cmppp_download_subscriptions_csv');
	protected static $filters = array(
		'heartbeat_received'        => array( 'args' => 2 ),
		'heartbeat_nopriv_received' => array( 'args' => 2 ),
	);

	protected static $csv_data = array();

	static function admin_init() {

		if(Settings::getOption(Settings::OPTION_SUBSCRIPTION_EXPIRE_ENABLE)){
			if(!wp_get_schedule( self::CRON_NOTIFICATION )){
				wp_schedule_event(time() + rand(0, 60), 'twicedaily' , self::CRON_NOTIFICATION);
			}
		}
		else {
			if(wp_get_schedule( self::CRON_NOTIFICATION )) {
				wp_clear_scheduled_hook(self::CRON_NOTIFICATION);
			}
		}

        $action = filter_input(INPUT_GET, static::PARAM_ACTION);
        switch ($action) {
            case static::ACTION_DOWNLOAD_CSV:
                static::downloadSubscriptionsCSV();
                break;
        }
    }

	static function admin_menu() {
		add_submenu_page( App::MENU_SLUG, App::getPluginName() . ' ' . static::TITLE, static::TITLE, 'manage_options',
			self::getMenuSlug(), array( get_called_class(), 'render' ) );
	}

	static function getMenuSlug() {
		return App::MENU_SLUG . '-subscriptions';
	}

	static function getUrl() {
		return admin_url( 'admin.php?page=' . self::getMenuSlug() );
	}

	static function render() {

		if ( $timezone = get_option( 'timezone_string' ) ) {
			date_default_timezone_set( $timezone );
		}

		$filter = array(
			'user_id'       => ( empty( $_GET['user_id'] ) ? null : $_GET['user_id'] ),
			'post_id'       => ( empty( $_GET['post_id'] ) ? null : $_GET['post_id'] ),
			'blog_id'       => get_current_blog_id(),
			'pricing_group' => ( empty( $_GET['pricing_group'] ) ? null : $_GET['pricing_group'] ),
			'status'        => ( empty( $_GET['status'] ) ? null : $_GET['status'] ),
		);

		$pageUrl = self::getUrl();

		$limit = 20;
		$page  = ( empty( $_GET['p'] ) ? 1 : $_GET['p'] );

		$firstPageArgs = $_GET;
		unset( $firstPageArgs['success'] );
		unset( $firstPageArgs['msg'] );
		$currentUrl = add_query_arg( urlencode_deep( $firstPageArgs ), $pageUrl );

		unset( $firstPageArgs['p'] );

		$pagination             = array(
			'page'         => $page,
			'count'        => SubscriptionReport::getCount( $filter ),
			'firstPageUrl' => add_query_arg( urlencode_deep( $firstPageArgs ), $pageUrl ),
		);
		$pagination['lastPage'] = ceil( $pagination['count'] / $limit );

		$viewData            = array(
			'pageUrl'      => $pageUrl,
			'currentUrl'   => $currentUrl,
			'data'         => SubscriptionReport::getData( $filter, $limit, $page ),
			'pageMenuSlug' => self::getMenuSlug(),
			'filter'       => $filter,
			'pagination'   => $pagination,
			'nonceAdd'     => wp_create_nonce( self::NONCE_ADD ),
			'nonceAction'  => wp_create_nonce( self::NONCE_ACTION ),
		);
		$viewData['addForm'] = self::loadBackendView( 'add', $viewData );

		wp_enqueue_style( 'cmppp-backend' );
		wp_enqueue_script( 'cmppp-backend' );
		wp_enqueue_script( 'suggest' );
		wp_enqueue_script( 'user-suggest' );

        $viewData['downloadCSVUrl'] = add_query_arg(self::PARAM_ACTION, self::ACTION_DOWNLOAD_CSV, $_SERVER['REQUEST_URI']);

        echo self::loadView( 'backend/template', array(
			'title'   => App::getPluginName() . ' ' . static::TITLE,
			'nav'     => self::getBackendNav(),
			'content' => self::loadBackendView( 'report', $viewData ),
		) );

	}

	public static function cmppp_expired_subscription_notification() {
		$filter = ['status' => 'active'];

		$subscriptions = SubscriptionReport::getData($filter, 999,1);
		$time_before_notification = Settings::getOption(Settings::OPTION_SUBSCRIPTION_EXPIRE_DATE) * DAY_IN_SECONDS;

		if($time_before_notification > 0 && $subscriptions){
			foreach ($subscriptions as $subscription) {
				$time_left = $subscription['end'] - time();
				$was_sent = get_user_meta($subscription['user_id'], Subscription::META_MP_SUBSCRIPTION_EXPIRATION_NOTIFICATION . '_' . $subscription['meta_id'], true);

				if($time_left <= $time_before_notification && $subscription['duration'] > $time_before_notification && !$was_sent){
					Subscription::notifySubscriptionExpiration($subscription);
					add_user_meta( $subscription['user_id'], Subscription::META_MP_SUBSCRIPTION_EXPIRATION_NOTIFICATION . '_' . $subscription['meta_id'], 'sent', $unique = true );
				}
			}
		}
	}

    public static function downloadSubscriptionsCSV() {

        $filter = array(
            'user_id'       => ( empty( $_GET['user_id'] ) ? null : $_GET['user_id'] ),
            'post_id'       => ( empty( $_GET['post_id'] ) ? null : $_GET['post_id'] ),
            'blog_id'       => get_current_blog_id(),
            'pricing_group' => ( empty( $_GET['pricing_group'] ) ? null : $_GET['pricing_group'] ),
            'status'        => ( empty( $_GET['status'] ) ? null : $_GET['status'] ),
        );

        $page  = ( empty( $_GET['p'] ) ? 1 : $_GET['p'] );

        $range = explode(',', $_GET['from-to']);
        $range = range($range[0], $range[1]);

        $CSV_headers = array();
        $data = [];
        $prepare_data = [];

        foreach ($range as $page) {
            array_push($prepare_data, SubscriptionReport::getData($filter, 20, $page));
        }

        foreach ($prepare_data as $prepare_item) {
            foreach ($prepare_item as $item) {
                array_push($data, $item);
            }
        }

        
        $csv_data = array();
        $filename = 'subscriptions-' . Date('YmdHis');


        foreach ($data[0] as $header => $value) {
            $CSV_headers[] = $header;
        }

        foreach ($data as $key => $csv_array) {
            foreach ($data[$key] as $value) {
                $csv_data[$key][] = $value;
            }
        }

        array_unshift($csv_data, $CSV_headers);

        CSVHelper::downloadCSV($csv_data, $filename);

    }

	static function cmppp_user_suggest() {

		$field  = 'user_login';
		$return = array();

		$users = get_users( array(
			'blog_id'        => false,
			'search'         => '*' . $_REQUEST['term'] . '*',
			//'include' => $include_blog_users,
			//'exclude' => $exclude_blog_users,
			'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
		) );

		foreach ( $users as $user ) {
			$return[] = array(
				/* translators: 1: user_login, 2: user_email */
				'label' => sprintf( __( '%1$s (%2$s)' ), $user->user_login, $user->user_email ),
				'value' => $user->$field,
			);
		}

		wp_die( wp_json_encode( $return ) );

	}

	static function cmppp_post_suggest() {

		$return = array();

		$posts = get_posts( array(
			's'           => $_GET['term'],
			'numberposts' => 100,
			'nopaging'    => true,
			'post_type'   => Subscription::getSupportedPostTypes(),
		) );

		foreach ( $posts as $post ) {
			if ( $post = Post::getInstance( $post ) and $post->isPaid() ) {
				$return[] = array(
					'label' => sprintf( '[%d] %s', $post->getId(), $post->getTitle() ),
					'value' => $post->getId(),
				);
			}
		}

		wp_die( wp_json_encode( $return ) );

	}

	static protected function canViewPage() {
		$page = basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

		return ( is_admin() and
		         current_user_can( 'manage_options' ) and
		         $page == 'admin.php' and
		         ! empty( $_GET['page'] ) and
		         $_GET['page'] == self::getMenuSlug()
		);
	}

	static function processRequest() {
		if ( self::canViewPage() ) {
			if ( ! empty( $_POST ) and ! empty( $_POST['nonce'] ) and wp_verify_nonce( $_POST['nonce'], self::NONCE_ADD ) ) {
				self::processAddRequest();
			} else if ( ! empty( $_GET['action'] )
                    and ! empty( $_GET['nonce'] )
                    and wp_verify_nonce( $_GET['nonce'], self::NONCE_ACTION )
                    and ! empty( $_GET['id'] ) and is_numeric( $_GET['id'] ) ) {

				switch ( $_GET['action'] ) {
					case 'deactivate':
						self::processDeactivateRequest( $_GET['id'] );
						break;
					case 'remove':
						self::processRemoveRequest( $_GET['id'] );
						break;
				}
			}
			else if(! empty( $_GET['action'] ) and ! empty( $_GET['nonce'] ) and wp_verify_nonce( $_GET['nonce'], self::NONCE_ACTION )) {

                $ids = array_diff(explode(',', $_GET['ids']), array(''));

                if($_GET['action'] == 'bulk-remove') {
                    foreach ($ids as $id) {
                        Subscription::removeSubscription($id);
                    }
                }
                elseif ($_GET['action'] == 'bulk-deactivate') {
                    foreach ($ids as $id) {
                        Subscription::deactivateSubscription($id);
                    }
                }
            }
		}
	}

	static protected function processDeactivateRequest( $metaId ) {
		Subscription::deactivateSubscription( $metaId );
		self::redirectAfterAction( 'Subscription has been deactivated.' );
	}

	static protected function processRemoveRequest( $metaId ) {
		Subscription::removeSubscription( $metaId );
		self::redirectAfterAction( 'Subscription has been removed.' );
	}

	static protected function processAddRequest() {

		$response = array( 'success' => 0, 'msg' => 'Failed to add subscription.' );

		if ( ! empty( $_POST['user_login'] ) and $user = get_user_by( 'login', $_POST['user_login'] ) ) {
			if ( ! empty( $_POST['post_id'] ) and $post = Post::getInstance( $_POST['post_id'] ) and $post->isPaid() ) {
				if ( ! empty( $_POST['number'] ) and is_numeric( $_POST['number'] ) ) {
					if ( ! empty( $_POST['unit'] ) ) {

						$seconds = Micropayments::period2seconds( $_POST['number'] . $_POST['unit'] );
						$sub     = new Subscription( $post );

						try {
							$sub->addSubscription( $user->ID, $seconds, 0, 'admin', null );
							$response = array(
								'success' => 1,
								'msg'     => Labels::__( 'Subscription has been added.' ),
							);
						} catch ( \Exception $e ) {
							$response['msg'] = $e->getMessage();
						}


					} else {
						$response['msg'] = 'Invalid unit.';
					};
				} else {
					$response['msg'] = 'Invalid number.';
				}
			} else {
				$response['msg'] = 'Unknown post.';
			}
		} else {
			$response['msg'] = 'Unknown user login.';
		}

		$response['page'] = self::getMenuSlug();

		wp_redirect( admin_url( 'admin.php?' . http_build_query( $response ) ) );
		exit;

	}

	static protected function redirectAfterAction( $msg ) {
		$params = $_GET;
		unset( $params['action'] );
		unset( $params['nonce'] );
		unset( $params['id'] );
		$params['success'] = 1;
		$params['msg']     = $msg;
		wp_redirect( admin_url( 'admin.php?' . http_build_query( $params ) ) );
		exit;
	}

	static function enqueueLogoutHandler() {
		if ( Settings::getOption( Settings::OPTION_RELOAD_EXPIRED_SUBSCRIPTION ) and $post = get_post() and $post instanceof \WP_Post ) {
			wp_enqueue_script( 'cmppp-logout-heartbeat' );
			wp_localize_script( 'cmppp-logout-heartbeat', 'CMPPP_Logout_Hearbeat', array(
				'postId' => $post->ID,
			) );
		}
	}

	static function heartbeat_nopriv_received( $response, $data ) {
		$checkPost = false;

		if ( isset( $data['cmppp_check_post'] ) ) {
			$post = Post::getInstance( $data['cmppp_check_post'] );
			$sub  = new Subscription( $post );

			if ( $post && $sub ) {
				$payments  = PaymentMethodFactory::filterPaymentForPost( PaymentMethodFactory::getPaymentsNameList(), $post );
				$checkPost = $sub->isGuestSubscriptionActive( $payments );
			}

			$response['cmppp_check_post'] = $checkPost;
		}

		return $response;
	}

	static function heartbeat_received( $response, $data ) {
		$checkPost = false;

		if ( isset( $data['cmppp_check_post'] ) ) {

			$post = Post::getInstance( $data['cmppp_check_post'] );
			$sub  = new Subscription( $post );

			if ( $post && $sub && is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$checkPost       = $sub->isSubscriptionActive( $current_user_id, $post );
			}

			$response['cmppp_check_post'] = $checkPost;
		}

		return $response;
	}

}
