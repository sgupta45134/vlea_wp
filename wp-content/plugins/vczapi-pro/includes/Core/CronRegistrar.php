<?php

namespace Codemanas\ZoomPro\Core;

use Codemanas\ZoomPro\Helpers;

/**
 * Class CronRegistrar
 *
 * Register cron events here
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class CronRegistrar {

	/**
	 * Frequency of the cron
	 *
	 * @var array
	 */
	private static $cron = array(
		'daily'  => 'vczapi_pro_daily_cron',
		'hourly' => 'vczapi_pro_daily_hourly'
	);

	/**
	 * CronRegistrar constructor.
	 */
	public function __construct() {
		//Cron Task
		add_action( 'vczapi_pro_daily_cron', [ $this, 'cleanup' ] );
		add_action( 'vczapi_pro_daily_cron', [ $this, 'send_reminders' ] );
		#$this->send_reminders();
	}

	/**
	 * Send Reminders for registrations
	 */
	public function send_reminders() {
		$settings = Fields::get_option( 'settings' );
		//IF Email reminders are disabled
		if ( ! empty( $settings ) && ! empty( $settings['reminder_emails_registrants'] ) && in_array( '24', $settings['reminder_emails_registrants'] ) ) {
			return;
		}

		$factory = Factory::get_instance();
		$users   = $factory::get_registered_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$registration_details = Fields::get_user_meta( $user->ID, 'registration_details' );
				if ( ! empty( $registration_details ) ) {
					foreach ( $registration_details as $k => $registration_detail ) {
						if ( isset( $registration_detail->code ) ) {
							continue;
						}

						if ( ! isset( $registration_detail->id ) ) {
							continue;
						}

						$wp_post = $factory->get_posts_by_meeting_id( $registration_detail->id, false, 'publish' );
						if ( ! empty( $wp_post ) ) {
							$post_id         = $wp_post[0]->ID;
							$post_content    = $wp_post[0]->post_content;
							$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
							if ( ! empty( $meeting_details->occurrences ) ) {
								$meeting_time = Helpers::get_latest_occurence_by_type( $meeting_details->type, $meeting_details->timezone, $meeting_details->occurrences );
							} else {
								$meeting_time = vczapi_dateConverter( $meeting_details->start_time, $meeting_details->timezone, 'F j, Y, g:i a' );
							}

							$customer_name = ! empty( $user->first_name ) ? $user->first_name . ' ' . $user->last_name : $user->display_name;
							//Replace dynamic variables
							$data = apply_filters( 'vczapi_pro_registration_reminder_email_content_args', array(
								'customer_name'       => $customer_name,
								'meeting_topic'       => $registration_detail->topic,
								'meeting_time'        => $meeting_time,
								'meeting_join_link'   => $registration_detail->join_url,
								'meeting_password'    => $meeting_details->password,
								'customer_first_name' => $user->first_name,
								'customer_last_name'  => $user->last_name,
								'meeting_id'          => $meeting_details->id,
								'meeting_timezone'    => $meeting_details->timezone,
								'meeting_duration'    => ! empty( $meeting_details->duration ) ? $meeting_details->duration : 60,
								'meeting_description' => ! empty( $post_content ) ? $post_content : ''
							), $meeting_details, $registration_details, $user, $post_id );

							//Prepare mail details
							$email_details = array(
								'email_to' => $user->user_email,
								'subject'  => apply_filters( 'vczapi_pro_registration_reminder_title', __( 'Meeting Reminder for', 'vczapi-pro' ) . ' ' . $registration_detail->topic, $registration_details )
							);

							$current_time           = new \DateTime( 'now', new \DateTimeZone( $meeting_details->timezone ) );
							$send_notification_time = new \DateTime( $meeting_time . ' -1 day', new \DateTimeZone( $meeting_details->timezone ) );
							$start_date             = new \DateTime( $meeting_time, new \DateTimeZone( $meeting_details->timezone ) );

							$already_sent = Fields::get_meta( $post_id, 'cron_one_day' );
							if ( empty( $already_sent ) && $start_date >= $current_time && $send_notification_time <= $current_time ) {
								Mailer::send_email( $email_details, $data, false, 'registration-reminder-email', true );
								Fields::set_post_meta( $post_id, 'cron_one_day', true );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Cleanup registration data after the meeting is passed
	 *
	 * @throws \Exception
	 */
	public function cleanup() {
		$users = Factory::get_registered_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$registration_details = Fields::get_user_meta( $user->ID, 'registration_details' );
				if ( ! empty( $registration_details ) ) {
					foreach ( $registration_details as $k => $registration_detail ) {
						if ( ! empty( $registration_detail ) && ! empty( $registration_detail->start_time ) ) {
							$event_past = Helpers::date_compare( $registration_detail->start_time, date( 'Y-m-d\TH:i:s\Z', strtotime( '+2 hours' ) ), 'UTC', '<=' );
							//Meeting date has passed so remove
							if ( $event_past ) {
								unset( $registration_details[ $k ] );
							}
						}
					}
				}

				Fields::set_user_meta( $user->ID, 'registration_details', $registration_details );
			}
		}
	}

	/**
	 * Add cron when plugin is activated
	 */
	public static function activate_cron() {
		foreach ( self::$cron as $k => $cron ) {
			if ( ! wp_next_scheduled( $cron ) ) {
				wp_schedule_event( time(), $k, $cron );
			}
		}
	}

	/**
	 * Remove crons on plugin deactivation
	 */
	public static function deactivate_cron() {
		foreach ( self::$cron as $k => $cron ) {
			$timestamp = wp_next_scheduled( $cron );
			wp_unschedule_event( $timestamp, $cron );
		}
	}
}