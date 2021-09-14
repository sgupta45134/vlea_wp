<?php

namespace Codemanas\ZoomPro\WC;

use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Frontend\Registrations;
use Codemanas\ZoomPro\Helpers;

/**
 * Class WooCommerce
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class WooCommerce {

	/**
	 * Instance property
	 *
	 * @var null
	 */
	public static $instance = null;

	private $zoom_api = null;

	/**
	 * Instance object
	 *
	 * @return WooCommerce|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {

		add_action( 'vczapi_woocommerce_after_zoom_connection_fields', [ $this, 'show_recurring_meeting_options' ] );

		//Check deadline crossed - append functionality - from WooCommerceZoomConnection.php
		add_filter( 'vczapi_woocommerce_check_deadline_crossed_meeting_date', [ $this, 'check_deadline_crossed_for_meetings' ], 10, 2 );

		//Modifying from Cart.php
		add_filter( 'vczapi_woocommerce_cart_meeting_details', array( $this, 'modify_cart_meeting_details' ), 10, 3 );

		//Modify from Orders.php
		add_filter( 'vczapi_woocommerce_order_item_meta', array( $this, 'email_meeting_details' ), 20, 4 );

		add_action( 'woocommerce_order_status_completed', array( $this, 'register_user' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'register_user' ) );

		//Save Meta - dependent on WooCommerceZoomConnection (WooCommerce Addon)
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_meta' ], 20 );

		$this->zoom_api = API::get_instance();
	}

	/**
	 * @param int $product_id
	 */
	public function save_meta( $product_id ) {
		$enable_zoom                             = filter_input( INPUT_POST, '_vczapi_enable_zoom_link' );
		$allow_purchase_after_first_date_crossed = filter_input( INPUT_POST, '_vczapi_pro_allow_purchase_after_first_date_crossed' );
		if ( ! empty( $enable_zoom ) ) {
			Fields::set_post_meta( $product_id, 'allow_purchase_after_first_date_crossed', $allow_purchase_after_first_date_crossed );
		}
	}

	public function show_recurring_meeting_options() {
		woocommerce_wp_checkbox( [
				'id'          => '_vczapi_pro_allow_purchase_after_first_date_crossed',
				'label'       => __( 'Allow Anytime Purchase ', 'vczapi-pro' ),
				'description' => __( 'Check this box to allow purchase of meeting even after first occurrence of Meeting has occurred and before last occurrence has occurred', 'vczapi-pro' ),
				'desc_tip'    => true
			]
		);
	}

	/**
	 * Check for Cossed Dealine Meeting Dates based on meeting iD
	 *
	 * @param $meeting_date
	 * @param $meeting_id
	 *
	 * @return \DateTime|string
	 */
	public function check_deadline_crossed_for_meetings( $meeting_date, $meeting_id ) {
		$product_id      = get_post_meta( $meeting_id, '_vczapi_zoom_product_id', true );
		$meeting_details = get_post_meta( $meeting_id, '_meeting_zoom_details', true );
		if ( ! empty( $meeting_details ) && is_object( $meeting_details ) ) {
			//Recurring Meeting
			// 8 == fixed time recurring meeting
			// 3 == no fixed time recurring meeting
			// 9 == fixe time recurring webinar
			if ( $meeting_details->type === 8 || $meeting_details->type === 3 || $meeting_details->type === 9 ) {
				$meeting_details->occurrences            = ! empty( $meeting_details->occurrences ) ? $meeting_details->occurrences : false;
				$allow_purchase_after_first_date_crossed = Fields::get_meta( $product_id, 'allow_purchase_after_first_date_crossed' );

				if ( $allow_purchase_after_first_date_crossed == 'yes' ) {
					$meeting_date = Helpers::get_latest_occurence_by_type( $meeting_details->type, $meeting_details->timezone, $meeting_details->occurrences, 'now' );
				} else {
					$meeting_date = Helpers::get_first_occurrence_date( $meeting_id );
				}

				$meeting_date = ! empty( $meeting_date ) ? $meeting_date : 'now +1 hour';
				$meeting_date = Helpers::date_convert_by_timezone( $meeting_date, $meeting_details->timezone );
				add_filter( 'vczapi_woocommerce_time_passed_text', [ $this, 'change_woocommerce_validate_cart_text' ], 10, 2 );
			} else {
				$meeting_date = Helpers::date_convert_by_timezone( $meeting_details->start_time, $meeting_details->timezone );
			}

			//$current_date = Helpers::date_convert_by_timezone( 'now', $meeting_details->timezone );
		}

		return $meeting_date;
	}

	/**
	 * Change WooCommerce Validate cart error message.
	 *
	 * @param $text
	 * @param $meeting_date
	 *
	 * @return string
	 */
	public function change_woocommerce_validate_cart_text( $text, $meeting_date ) {
		return sprintf( __( 'Recurring meeting time has passed for %s. Please purchase another occurence.', 'vczapi-pro' ), $meeting_date->format( 'F j, Y, g:i a' ) );
	}

	/**
	 * Show extra details in cart page
	 *
	 * @param $info
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function modify_cart_meeting_details( $info, $cart_item, $cart_item_key ) {
		$product_id = $cart_item['product_id'];
		$post_id    = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
		if ( ! empty( $post_id ) ) {
			$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
			$users           = video_conferencing_zoom_api_get_user_transients();
			if ( ! empty( $meeting_details ) && is_object( $meeting_details ) ) {
				$host_name = 'N/A';
				if ( ! empty( $users ) ) {
					foreach ( $users as $user ) {
						if ( $meeting_details->host_id === $user->id ) {
							$host_name = esc_html( $user->first_name . ' ' . $user->last_name );
							break;
						}
					}
				}

				if ( $meeting_details->type === 8 || $meeting_details->type === 3 ) {
					$meeting_details->occurrences = ! empty( $meeting_details->occurrences ) ? $meeting_details->occurrences : false;
					$next_occurence               = Helpers::get_latest_occurence_by_type( $meeting_details->type, $meeting_details->timezone, $meeting_details->occurrences );
					if ( ! $next_occurence ) {
						$next_occurence = __( 'No Fixed Time Recurring Meeting', 'vczapi-pro' );
					} else {
						$next_occurence = vczapi_dateConverter( $next_occurence, $meeting_details->timezone, 'F j, Y, g:i a' );
					}
					$info = sprintf(
						'<p style="margin-top:5px;"><strong>' . __( 'Type', 'vczapi-pro' ) . ':</strong> %s</p><p style="margin-top:5px;"><strong>' . __( 'Hosted By', 'vczapi-pro' ) . ':</strong><br>%s</p><p style="margin-top:5px;"><strong>' . __( 'Time', 'vczapi-pro' ) . ':</strong><br>%s</p><p><strong>' . __( 'Timezone', 'vczapi-pro' ) . ':</strong><br>%s</p>',
						esc_html__( 'Recurring Meeting', 'vczapi-pro' ),
						$host_name,
						esc_html( $next_occurence ),
						esc_html( $meeting_details->timezone )
					);
				}
			}
		}

		return $info;
	}

	/**
	 * Show in order details
	 *
	 * @param $content
	 * @param $item_id
	 * @param $item
	 * @param $order \WC_Order
	 *
	 * @return mixed
	 */
	public function email_meeting_details( $content, $item_id, $item, $order ) {
		if ( $order->get_status() === "completed" || $order->get_status() === "processing" ) {
			$product_id = $item['product_id'];
			$order_id   = $order->get_id();
			$post_id    = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
			if ( ! empty( $post_id ) ) {
				$fields               = get_post_meta( $post_id, '_meeting_fields_woocommerce', true );
				$meeting_details      = get_post_meta( $post_id, '_meeting_zoom_details', true );
				$registration_details = Fields::get_meta( $order_id, 'registration_details' );
				$pro_meeting_details  = Fields::get_meta( $post_id, 'meeting_details' );
				if ( ! empty( $meeting_details ) && ! empty( $fields['enable_woocommerce'] ) ) {
					ob_start();
					$disabled = get_option( '_vczapi_woocommerce_disable_browser_join' );
					?>
                    <p class="vczapi-woocommerce-email-mtg-details-txt" style="margin-top:10px; margin-bottom:0;">
                        <strong><?php _e( 'Meeting Details', 'vczapi-pro' ); ?>:</strong></p>
                    <ul class="vczapi-woocommerce-email-mtg-details">
                        <li class="vczapi-woocommerce-email-mtg-details--list2"><strong><?php _e( 'Topic', 'vczapi-pro' ); ?>
                                :</strong> <?php echo $meeting_details->topic; ?></li>
						<?php
						if ( $meeting_details->type === 8 || $meeting_details->type === 3 ) {
							$meeting_details->occurrences = ! empty( $meeting_details->occurrences ) ? $meeting_details->occurrences : false;
							$next_occurence               = Helpers::get_latest_occurence_by_type( $meeting_details->type, $meeting_details->timezone, $meeting_details->occurrences );
							if ( ! $next_occurence ) {
								$next_occurence = __( 'No Fixed Time Recurring Meeting', 'vczapi-pro' );
							} else {
								$next_occurence = vczapi_dateConverter( $next_occurence, $meeting_details->timezone, 'F j, Y @ g:i a' );
							}
							?>
                            <li class="vczapi-woocommerce-email-mtg-details--list3"><strong><?php _e( 'Type', 'vczapi-pro' ); ?>
                                    :</strong> <?php _e( 'Recurring Meeting', 'vczapi-pro' ); ?></li>
                            <li class="vczapi-woocommerce-email-mtg-details--list3"><strong><?php _e( 'Next Occurence', 'vczapi-pro' ); ?>
                                    :</strong>
								<?php echo $next_occurence; ?>
                            </li>
							<?php
						} else {
							?>
                            <li class="vczapi-woocommerce-email-mtg-details--list3"><strong><?php _e( 'Start Time', 'vczapi-pro' ); ?>
                                    :</strong>
								<?php echo vczapi_dateConverter( $meeting_details->start_time, $meeting_details->timezone, 'F j, Y @ g:i a' ); ?>
                            </li>
							<?php
						}
						?>
                        <li class="vczapi-woocommerce-email-mtg-details--list3"><strong><?php _e( 'Timezone', 'vczapi-pro' ); ?>
                                :</strong>
							<?php
							echo $meeting_details->timezone;
							?></li>
						<?php
						if ( ! empty( $registration_details ) && ! empty( $registration_details[ $product_id ] ) && ! empty( $pro_meeting_details ) && ! empty( $pro_meeting_details['registration'] ) ) {
							$registration_details = empty( $registration_details[ $product_id ]->code ) ? $registration_details[ $product_id ] : false;
						} else {
							$registration_details = false;
						}

						if ( ! empty( $meeting_details ) && isset( $meeting_details->registration_url ) && $meeting_details->settings->approval_type !== 2 && $registration_details ) {
							?>
                            <li class="vczapi-woocommerce-email-mtg-details--list6"><strong><?php _e( 'Registration ID', 'vczapi-pro' ); ?>
                                    :</strong>
								<?php echo $registration_details->registrant_id; ?>
                            </li>
                            <li class="vczapi-woocommerce-email-mtg-details--list4">
                                <a target="_blank" rel="nofollow" href="<?php echo esc_url( $registration_details->join_url ); ?>"><?php _e( 'Join via App', 'vczapi-pro' ); ?></a>
                            </li>
							<?php
						} else {
							?>
                            <li class="vczapi-woocommerce-email-mtg-details--list4">
                                <a target="_blank" rel="nofollow" href="<?php echo esc_url( $meeting_details->join_url ); ?>"><?php _e( 'Join via App', 'vczapi-pro' ); ?></a>
                            </li>
							<?php if ( empty( $disabled ) && ! empty( $meeting_details->password ) ) { ?>
                                <li class="vczapi-woocommerce-email-mtg-details--list5">
									<?php echo vczapi_get_browser_join_links( $post_id, $meeting_details->id, $meeting_details->password ); ?>
                                </li>
							<?php }
						}
						?>
                    </ul>
					<?php
				}
				$content = ob_get_clean();
			}
		}

		return $content;
	}

	/**
	 * Register the user into Zoom
	 *
	 * @param $order_id
	 */
	public function register_user( $order_id ) {
		$order  = wc_get_order( $order_id );
		$result = false;

		if ( ! empty( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item['product_id'];
				$post_id    = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
				if ( empty( $post_id ) ) {
					return;
				}

				$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
				$fields          = get_post_meta( $post_id, '_meeting_fields_woocommerce', true );
				$meeting_type    = get_post_meta( $post_id, '_vczapi_meeting_type', true );
				if ( empty( $meeting_details ) && ! empty( $fields['enable_woocommerce'] ) ) {
					return;
				}

				$order_details = array(
					'email'      => $order->get_billing_email(),
					'first_name' => $order->get_billing_first_name(),
					'last_name'  => $order->get_billing_last_name(),
					'address'    => $order->get_billing_address_1(),
					'country'    => $order->get_billing_country(),
					'phone'      => $order->get_billing_phone(),
				);

				if ( ! empty( $meeting_details->id ) ) {
					if ( $meeting_type == 'webinar' ) {
						$registered = json_decode( $this->zoom_api->addWebinarRegistrant( $meeting_details->id, $order_details ) );
					} else {
						$registered = json_decode( $this->zoom_api->addMeetingRegistrant( $meeting_details->id, $order_details ) );
					}

					$result[ $product_id ] = $registered;

					$user_id = get_current_user_id();
					if ( $user_id ) {
						$registrants                         = Fields::get_user_meta( $user_id, 'registration_details' );
						$registrants                         = ! empty( $registrants ) ? $registrants : array();
						$registrants[ $meeting_details->id ] = $registered;

						Fields::set_user_meta( $user_id, 'registration_details', $registrants );
						$registered_users = Fields::get_meta( $post_id, 'registered_user_ids' );
						if ( ! empty( $registered_users ) ) {
							$registered_users[] = $user_id;
							$registered_users   = array_unique( $registered_users );
							Fields::set_post_meta( $post_id, 'registered_user_ids', $registered_users );
						} else {
							Fields::set_post_meta( $post_id, 'registered_user_ids', array( $user_id ) );
						}
					}

					$settings = Fields::get_option( 'settings' );
					if ( ! empty( $settings ) && ! empty( $settings['registraion_email'] ) ) {
						//Prepare for Email
						$user_details = [
							'customer_name' => $order_details['first_name'] . ' ' . $order_details['last_name'],
							'user_email'    => $order_details['email']
						];
						//Registrations::get_instance()->send_mail( $user_details, $post_id, $registered );
					}

					//Flush cache
					Fields::flush_cache( $post_id, 'registrants' );
				}
			}

			Fields::set_post_meta( $order_id, 'registration_details', $result );
		}
	}
}