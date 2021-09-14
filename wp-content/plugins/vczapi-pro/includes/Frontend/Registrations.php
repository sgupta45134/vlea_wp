<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\ICS;
use Codemanas\ZoomPro\Core\Mailer;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;
use Codemanas\ZoomPro\TemplateFunctions;

/**
 * Class Registrations
 *
 * Template Hook Register Deregister for Registrations
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Registrations {

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Hold API
	 *
	 * @var $api
	 */
	private $api;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		add_action( 'vczapi_pro_before_registration_form', [ $this, 'html_start' ], 10 );
		add_action( 'vczapi_pro_after_registration_form', [ $this, 'html_end' ], 10 );

		$this->api = API::get_instance();
	}

	/**
	 * HTML START
	 */
	public function html_start() {
		#ob_start( 'self::removeWhitespace' );
		?>
        <!DOCTYPE html>
        <html lang="<?php echo get_locale(); ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="format-detection" content="telephone=no">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
            <title><?php _e( 'Meeting Registration', 'vczapi-pro' ) ?></title><?php wp_head(); ?>
        </head>
        <body class="vczapi-pro-registration-page">
		<?php
		#ob_end_flush();
	}

	/**
	 * REMOVE WHITESPACES
	 *
	 * @param $buffer
	 *
	 * @return string|string[]|null
	 */
	public function removeWhitespace( $buffer ) {
		return preg_replace( '/\s+/', ' ', $buffer );
	}

	/**
	 * HTML END
	 */
	public function html_end() {
		ob_start();
		wp_enqueue_script( 'vczapi-pro' );
		wp_footer();
		?>
        </body>
        </html>
		<?php
		ob_end_flush();
	}

	/**
	 * Get Registration button template
	 *
	 * @param $zoom
	 *
	 * @return bool|mixed|string|void
	 */
	public function get_register_btn( $zoom ) {
		$current_user        = wp_get_current_user();
		$current_user_id     = $current_user->ID;
		$registration_link   = Helpers::get_url_query( array( 'register' => $current_user_id ), $zoom->post_id );
		$regisration_details = Fields::get_user_meta( $current_user_id, 'registration_details' );
		$meeting_details     = Fields::get_meta( $zoom->post_id, 'meeting_details' );
		unset( $GLOBALS['zoom'] );
		$GLOBALS['zoom'] = array(
			'registration_link'                 => $registration_link,
			'register_on_zoom'                  => $meeting_details['register_on_zoom'],
			'registration_fields'               => $meeting_details['registration_fields'],
			'registration_individually_enabled' => $meeting_details['override_registration_fields'],
			'registration_details'              => $regisration_details,
			'api'                               => $zoom,
			'current_user'                      => ! empty( $current_user->data ) ? $current_user : false
		);

		//Filter registered users and sync with DB
		Helpers::reset_registered_users( $zoom->id, $zoom->post_id );

		$settings = Fields::get_option( 'settings' );
		if ( ! empty( $settings ) && $settings['inline_registration_form'] ) {
			return TemplateFunctions::get_template( 'registration/inline-registration.php' );
		} else {
			return TemplateFunctions::get_template( 'fragments/register.php' );
		}
	}

	/**
	 * Get Registration form page
	 *
	 * @param $current_user_id
	 * @param $post
	 *
	 * @return bool|mixed|string|void
	 */
	public function get_registration_page( $current_user_id, $post ) {
		return TemplateFunctions::get_template( 'registration.php' );
	}

	/**
	 * Register the user to Zoom
	 */
	public function register_user() {
		$nonce = filter_input( INPUT_POST, '_nonce_registration_meeting' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, '_registration_zoom_meeting' ) ) {
			wp_send_json_error( __( 'Something went wrong when trying to register as a user !', 'vczapi-pro' ) );
			die;
		}

		$first_name   = sanitize_text_field( filter_input( INPUT_POST, 'first_name' ) );
		$last_name    = sanitize_text_field( filter_input( INPUT_POST, 'last_name' ) );
		$email        = sanitize_email( filter_input( INPUT_POST, 'email_address' ) );
		$meeting_id   = absint( filter_input( INPUT_POST, 'meeting_id' ) );
		$post_id      = absint( filter_input( INPUT_POST, 'post_id' ) );
		$meeting_type = absint( filter_input( INPUT_POST, 'type' ) );

		//Additional Fields
		$address                  = sanitize_text_field( filter_input( INPUT_POST, 'address' ) );
		$city                     = sanitize_text_field( filter_input( INPUT_POST, 'city' ) );
		$country                  = filter_input( INPUT_POST, 'country' );
		$zip                      = sanitize_text_field( filter_input( INPUT_POST, 'zip' ) );
		$state                    = sanitize_text_field( filter_input( INPUT_POST, 'state' ) );
		$phone                    = sanitize_text_field( filter_input( INPUT_POST, 'phone' ) );
		$industry                 = sanitize_text_field( filter_input( INPUT_POST, 'industry' ) );
		$organization             = sanitize_text_field( filter_input( INPUT_POST, 'organization' ) );
		$job_title                = sanitize_text_field( filter_input( INPUT_POST, 'job_title' ) );
		$purchasing_time_frame    = filter_input( INPUT_POST, 'purchasing_time_frame' );
		$role_in_purchase_process = filter_input( INPUT_POST, 'role_in_purchase_process' );
		$no_of_employees          = filter_input( INPUT_POST, 'no_of_employees' );
		$questions_and_comments   = sanitize_textarea_field( filter_input( INPUT_POST, 'questions_and_comments' ) );

		//Validation Checks
		if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
			wp_send_json_error( __( 'Required field is missing. Please fill all the required fields before registering !', 'vczapi-pro' ) );
		}

		//Validation Checks
		if ( empty( $meeting_id ) ) {
			wp_send_json_error( __( 'Event ID does not seem to be valid. Please try again later.', 'vczapi-pro' ) );
		}

		$postData = apply_filters( 'vczapi_pro_register_user_fields', [
			'email'                    => $email,
			'first_name'               => $first_name,
			'last_name'                => $last_name,
			'address'                  => $address,
			'city'                     => $city,
			'country'                  => $country,
			'zip'                      => $zip,
			'state'                    => $state,
			'phone'                    => $phone,
			'industry'                 => $industry,
			'org'                      => $organization,
			'job_title'                => $job_title,
			'purchasing_time_frame'    => $purchasing_time_frame,
			'role_in_purchase_process' => $role_in_purchase_process,
			'no_of_employees'          => $no_of_employees,
			'comments'                 => $questions_and_comments
		] );

		if ( ! empty( $meeting_type ) && $meeting_type === 2 ) {
			$registered = json_decode( $this->api->addWebinarRegistrant( $meeting_id, $postData ) );
		} else {
			$registered = json_decode( $this->api->addMeetingRegistrant( $meeting_id, $postData ) );
		}

		if ( ! empty( $registered ) && empty( $registered->code ) ) {
			//If user is logged in that is;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			} else {
				$fetched_user = get_user_by( 'email', $email );
				if ( ! empty( $fetched_user ) ) {
					$user_id = $fetched_user->ID;
				} else {
					//Trigger this after registration of a user has been completed on zoom.
					$user_id = apply_filters( 'vczapi_pro_trigger_before_user_registered', false, $postData );
				}
			}

			//IF USER ID is detected then store value for that user.
			if ( ! empty( $user_id ) ) {
				$registrants                = Fields::get_user_meta( $user_id, 'registration_details' );
				$registrants                = ! empty( $registrants ) ? $registrants : array();
				$registrants[ $meeting_id ] = $registered;

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
					'customer_name'       => $first_name . ' ' . $last_name,
					'user_email'          => $email,
					'customer_first_name' => $first_name,
					'customer_last_name'  => $last_name,
				];
				$user_details = apply_filters( 'vczapi_pro_registration_before_email_send', $user_details );
				$this->send_mail( $user_details, $post_id, $registered );
			}

			//Flush cache
			Fields::flush_cache( $post_id, 'registrants' );

			//Trigger this after registration of a user has been completed on zoom.
			do_action( 'vczapi_pro_after_user_registered', $postData );

			//Check if inline registration form is enabled
			if ( ! empty( $settings['inline_registration_form'] ) ) {
				$link = apply_filters( 'vczapi_pro_after_user_registered_inline_link', '<a rel="nofollow" href="' . esc_url( $registered->join_url ) . '">' . __( 'here', 'vczapi-pro' ) . '</a>', $post_id );
				wp_send_json_success( sprintf( __( 'You have been succesfully registered to this event. You can join this event from %s.', 'vczapi-pro' ), $link ) );
			} else {
				$link = apply_filters( 'vczapi_pro_after_user_registered_link', '<a class="vczapi-pro-go-backto-event-page" href="' . get_permalink( $post_id ) . '">' . __( 'Go back to event page.', 'vczapi-pro' ) . '</a>', $post_id );
				wp_send_json_success( sprintf( __( 'You have been succesfully registered to this event. Please check your email for join links. %s', 'vczapi-pro' ), $link ) );
			}

		} else {
			wp_send_json_error( $registered->message );
		}

		wp_die();
	}

	/**
	 * Get User detail for the email
	 *
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_user_details_for_email( $user_id ) {
		$user         = get_userdata( $user_id );
		$user_details = [
			'customer_name' => ! empty( $user->first_name ) ? $user->first_name . ' ' . $user->last_name : $user->display_name,
			'user_email'    => $user->user_email
		];

		return $user_details;
	}

	/**
	 * Send Email to customer about the registration
	 *
	 * @param $user_details = []
	 * @param $post_id
	 * @param $registration_details
	 */
	public function send_mail( $user_details, $post_id, $registration_details ) {
		$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
		$post_content    = get_post_field( 'post_content', $post_id );

		if ( ! empty( $meeting_details->occurrences ) ) {
			$meeting_time = '<ul>';
			foreach ( $meeting_details->occurrences as $occurrence ):
				$meeting_time .= '<li>' . vczapi_dateConverter( $occurrence->start_time, $meeting_details->timezone, 'F j, Y, g:i a' ) . '</li>';
			endforeach;
			$meeting_time .= '</ul>';
		} else {
			$meeting_time = vczapi_dateConverter( $registration_details->start_time, $meeting_details->timezone, 'F j, Y, g:i a' );
		}

		//Replace dynamic variables
		$data = array(
			'customer_name'       => $user_details['customer_name'],
			'meeting_topic'       => $registration_details->topic,
			'meeting_time'        => $meeting_time,
			'meeting_join_link'   => $registration_details->join_url,
			'meeting_password'    => $meeting_details->password,
			'customer_first_name' => $user_details['customer_first_name'],
			'customer_last_name'  => $user_details['customer_last_name'],
			'meeting_id'          => $meeting_details->id,
			'meeting_timezone'    => $meeting_details->timezone,
			'meeting_duration'    => ! empty( $meeting_details->duration ) ? $meeting_details->duration : 60,
			'meeting_description' => ! empty( $post_content ) ? $post_content : ''
		);

		$data = apply_filters( 'vczapi_pro_registration_confirmed_email_content', $data, $meeting_details, $registration_details, $user_details, $post_id );

		//Prepare mail details
		$email_details = array(
			'email_to' => $user_details['user_email'],
			'subject'  => apply_filters( 'vczapi_pro_registration_confirm_title', __( 'Confirmation for', 'vczapi-pro' ) . ' ' . $registration_details->topic, $registration_details )
		);

		//OVERRIDE HOST/AUTHOR EMAIL to from instead of the default WordPress email address.
		$user_author_email = apply_filters( 'vczapi_pro_send_email_by_author', false );
		if ( $user_author_email ) {
			$post_author_id             = get_post_field( 'post_author', $post_id );
			$author                     = get_userdata( $post_author_id );
			$email_details['sent_from'] = $author->user_email;
		}

		$duration_minutes = ! empty( $meeting_details->duration ) ? $meeting_details->duration : 60;
		//prepare ICS parameters
		$ics = array(
			'location'    => $data['meeting_join_link'],
			'description' => apply_filters( 'vczapi_pro_registration_confirm_ics_text', 'Hi ' . $data['customer_name'] . ',\n\nThank you for registering for ' . $data['meeting_topic'] . '.\nStart Time: ' . $data['meeting_time'] . '\nJoin from PC, Mac, Linux, iOS or Android: ' . $data['meeting_join_link'] . '\n\Password: ' . $data['meeting_password'] . '\nNote:This link should not be shared with others; it is unique to you.' ),
			'dtstart'     => $registration_details->start_time,
			'dtend'       => date( 'Y-m-d\TH:i:s\Z', strtotime( $registration_details->start_time ) + $duration_minutes * 60 ),
			'summary'     => $registration_details->topic
		);

		//IF RECURRING MEETING
		$ics = ICS::get_occurence_data( $ics, $meeting_details );

		Mailer::send_email( $email_details, $data, $ics, 'confirmation_email' );
	}

	/**
	 * List Author Meeeting Registrants
	 *
	 * @return false|string
	 */
	public function list_author_registrants() {
		ob_start();

		unset( $GLOBALS['zoom'] );

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'vczapi-pro' );
		// Localize the script with new data
		$translation_array = array(
			'list_registrants' => 1,
			'loading'          => __( 'Loading registrants.. Please wait..', 'vczapi-pro' )
		);
		wp_localize_script( 'vczapi-pro', 'vczapi_view', $translation_array );

		TemplateFunctions::get_template( 'frontend/author-registrants.php', true );

		return ob_get_clean();
	}

	/**
	 * Get Meeting Registrants
	 */
	public function get_meeting_registrants() {
		$post_id = filter_input( INPUT_GET, 'post_id' );
		if ( ! empty( $post_id ) ) {
			$meeting_id           = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
			$vczapi_field_details = get_post_meta( $post_id, '_meeting_fields', true );
			$meeting_details      = Fields::get_meta( $post_id, 'meeting_details' );
			if ( ! empty( $meeting_id ) && ! empty( $meeting_details ) && ! empty( $meeting_details['registration'] ) ) {
				$registrants = Fields::get_cache( $post_id, 'registrants' );
				if ( ! $registrants ) {
					if ( ! empty( $vczapi_field_details ) && $vczapi_field_details['meeting_type'] === 2 ) {
						$registrants = json_decode( $this->api->getWebinarRegistrants( $meeting_id ) );
					} else {
						$registrants = json_decode( $this->api->getMeetingRegistrant( $meeting_id ) );
					}

					Fields::set_cache( $post_id, 'registrants', $registrants, 60 * 2 );
				}

				if ( ! empty( $registrants ) && ! empty( $registrants->code ) ) {
					$error = $registrants->message;
				} else if ( empty( $registrants->registrants ) ) {
					$error = __( 'No registrations for this event so far.', 'vczapi-pro' );
				} else {
					$error = __( 'No registrations for this event so far.', 'vczapi-pro' );
				}
			} else {
				$error = __( 'Registration is not enabled for this event.', 'vczapi-pro' );
			}
		} else {
			$error = __( 'Opps! This post does not exist anymore.', 'vczapi-pro' );
		}

		ob_start();
		?>
        <div class="vczapi-modal-content">
            <div class="vczapi-modal-body">
                <span class="vczapi-modal-close">&times;</span>
				<?php if ( ! empty( $registrants ) && ! empty( $registrants->registrants ) ) { ?>
                    <table class="vczapi-registrants-table vczapi-data-table">
                        <thead>
                        <tr role="row">
                            <th><?php _e( 'Email', 'vczapi-pro' ); ?></th>
                            <th><?php _e( 'First Name', 'vczapi-pro' ); ?></th>
                            <th><?php _e( 'Last Name', 'vczapi-pro' ); ?></th>
                            <th><?php _e( 'Joined', 'vczapi-pro' ); ?></th>
                            <th><?php _e( 'Current Status', 'vczapi-pro' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $registrants->registrants as $registrant ) { ?>
                            <tr>
                                <td><?php echo $registrant->email; ?></td>
                                <td><?php echo $registrant->first_name; ?></td>
                                <td><?php echo $registrant->last_name; ?></td>
                                <td><?php echo date( 'F j, Y @ g:i a', strtotime( $registrant->create_time ) ) . ' (UTC)'; ?></td>
                                <td><?php echo $registrant->status; ?></td>
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
                    <script>
                        jQuery('.vczapi-registrants-table').dataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                'csv', 'excel', 'print'
                            ],
                            responsive: true
                        });
                    </script>
				<?php } else { ?>
                    <p style="margin-top:20px;"><?php echo $error; ?></p>
				<?php } ?>
            </div>
        </div>
		<?php
		$result = ob_get_clean();
		wp_send_json_success( $result );

		wp_die();
	}

	/**
	 * List registered meeting for a user or via user id
	 *
	 * @param $atts
	 *
	 * @return false|string
	 */
	public function list_registered_meetings( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => '',
				'show'    => 'upcoming'
			),
			$atts, 'vczapi_registered_meetings'
		);

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'vczapi-pro' );

		$user_id     = ! empty( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
		$registrants = Fields::get_user_meta( $user_id, 'registration_details' );
		$factory     = Factory::get_instance();
		$meetings    = [];

		if ( ! empty( $registrants ) ) {
			foreach ( $registrants as $registrant ) {

				if ( isset ( $registrant->code ) ) {
					continue;
				}

				$zoom_meetings                  = $factory->get_posts_by_meeting_id( $registrant->id, false, 'publish' );
				$meeting_details                = get_post_meta( $zoom_meetings[0]->ID, '_meeting_zoom_details', true );
				$now                            = Helpers::date_convert_by_timezone( 'now -10 minutes', $meeting_details->timezone );
				$zoom_meetings[0]->api          = $meeting_details;
				$zoom_meetings[0]->registration = $registrant;

				if ( ! empty( $meeting_details->start_time ) ) {
					$start_date = Helpers::date_convert_by_timezone( $meeting_details->start_time, $meeting_details->timezone );
					if ( $start_date >= $now && $atts['show'] == "upcoming" ) {
						$meetings[] = $zoom_meetings[0];
					} else if ( $start_date <= $now && $atts['show'] == "past" ) {
						$meetings[] = $zoom_meetings[0];
					}
				} else if ( ! empty( $meeting_details->occurrences ) ) {
					$meeting_date = false;
					foreach ( $meeting_details->occurrences as $occurrence ) {
						if ( $occurrence->status === "available" ) {
							$start_date = Helpers::date_convert_by_timezone( $occurrence->start_time, $meeting_details->timezone );
							if ( $start_date >= $now && $atts['show'] == "upcoming" ) {
								$meeting_date = $occurrence->start_time;
								break;
							} else if ( $start_date <= $now && $atts['show'] == "past" ) {
								$meeting_date = $occurrence->start_time;
								break;
							}
						}
					}

					if ( ! empty( $meeting_date ) ) {
						$zoom_meetings[0]->api->start_time = $meeting_date;
						$meetings[]                        = $zoom_meetings[0];
					}
				}
			}
		}

		$GLOBALS['zoom_meetings'] = $meetings;
		ob_start();
		TemplateFunctions::get_template( 'registration/registered-meeting-list.php', true, false );

		return ob_get_clean();
	}
}