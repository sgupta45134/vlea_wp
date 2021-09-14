<?php

namespace Codemanas\ZoomPro\Backend;

use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;

/**
 * Class MetaHandler
 *
 * Handler for meta box in zoom meeting section
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro\Admin
 */
class MetaHandler {

	/**
	 * API
	 *
	 * @var API
	 */
	private $zoom_api;

	/**
	 * MetaHandler constructor.
	 */
	public function __construct() {
		add_action( 'vczapi_admin_after_zoom_meeting_is_created', array( $this, 'save' ), 10, 2 );

		//Normal Meetings
		add_filter( 'vczapi_createAmeeting', array( $this, 'create_meeting' ), 1 );
		add_filter( 'vczapi_updateMeetingInfo', array( $this, 'create_meeting' ), 1 );

		//For Webinars
		add_filter( 'vczapi_createAwebinar', array( $this, 'create_webinar' ), 1 );
		add_filter( 'vczapi_updateWebinar', array( $this, 'create_webinar' ), 1 );

		add_filter( 'vczapi_before_fields_admin', [ $this, 'meeting_host' ] );
	}

	/**
	 * Get Post data in admin side
	 *
	 * @return array
	 */
	public function get_posted_data() {
		$postData = array(
			'use_pmi'                      => filter_input( INPUT_POST, 'vczapi-use-pmi' ),
			'enabled_recurring'            => filter_input( INPUT_POST, 'vczapi-enable-recurring-meeting' ),
			'frequency'                    => filter_input( INPUT_POST, 'vczapi-recurrence-frequency' ),
			'interval'                     => filter_input( INPUT_POST, 'vczapi-repeat-interval' ),
			'weekly_occurence'             => filter_input( INPUT_POST, 'vczapi-weekly-occurrence', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
			'monthly_occurence_type'       => filter_input( INPUT_POST, 'vczapi-monthly-occurrence-type' ),
			'monthly_occurence_day'        => filter_input( INPUT_POST, 'vczapi-monthly-occurrence' ),
			'monthly_occurence_week'       => filter_input( INPUT_POST, 'vczapi-monthly-occurence-week' ),
			'monthly_occurence_week_day'   => filter_input( INPUT_POST, 'vczapi-monthly-occurrence-day' ),
			'end_type'                     => filter_input( INPUT_POST, 'vczapi-recurring-end-type' ),
			'end_datetime'                 => filter_input( INPUT_POST, 'vczapi-end-date-time' ),
			'end_occurence'                => filter_input( INPUT_POST, 'vczapi-end-times-occurence' ),
			'registration'                 => filter_input( INPUT_POST, 'vczapi-enable-registration' ),
			'register_on_zoom'             => filter_input( INPUT_POST, 'vczapi-registration-on-zoom' ),
			'registration_type'            => filter_input( INPUT_POST, 'vczapi-registration-type' ),
			'registration_condition'       => filter_input( INPUT_POST, 'vcapi-registration-condition' ),
			'registration_email'           => filter_input( INPUT_POST, 'vczapi-registration-email' ),
			'override_registration_fields' => filter_input( INPUT_POST, 'vczapi-override-registration-fields' ),
		);

		$raw_registration_fields         = filter_input( INPUT_POST, 'meeting_registration_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$postData['registration_fields'] = Factory::filter_registration_submitted_data( $raw_registration_fields );

		//IF RECURRING is no fixed time meeting
		if ( ! empty( $postData['enabled_recurring'] ) && $postData['frequency'] === "4" ) {
			$postData['registration']      = false;
			$postData['registration_type'] = false;
		}

		if ( ! empty( $postData['enabled_recurring'] ) && ( $postData['frequency'] === "1" || $postData['frequency'] === "2" || $postData['frequency'] === "3" ) ) {
			$use_pmi['use_pmi'] = '';
		}

		return $postData;
	}

	/**
	 * Create Meeting link with API
	 *
	 * @param $postData
	 *
	 * @return mixed
	 */
	public function create_meeting( $postData ) {
		$formData = $this->get_posted_data();

		//PMI
		if ( ! empty( $formData['use_pmi'] ) && $formData['use_pmi'] === "2" ) {
			$postData['settings']['use_pmi'] = true;
		} else {
			$postData['settings']['use_pmi'] = false;
		}

		//Registration
		if ( ! empty( $formData['registration'] ) ) {
			$postData['settings']['approval_type'] = 0;
			if ( ! empty( $formData['registration_type'] ) ) {
				$postData['settings']['registration_type'] = absint( $formData['registration_type'] );
			}

			$postData['settings']['registrants_email_notification'] = true;
			if ( ! empty( $formData['registration_email'] ) ) {
				$postData['settings']['registrants_email_notification'] = false;
				$postData['settings']['registrants_confirmation_email'] = false;
			} else {
				$postData['settings']['registrants_email_notification'] = true;
				$postData['settings']['registrants_confirmation_email'] = true;
			}
		} else {
			$postData['settings']['approval_type'] = 2;
		}

		if ( ! empty( $formData['enabled_recurring'] ) ) {
			if ( $formData['frequency'] === "4" ) {
				$postData['type'] = 3;
			} else {
				$postData['type']       = 8;
				$postData['recurrence'] = array(
					'type'            => absint( $formData['frequency'] ),
					'repeat_interval' => absint( $formData['interval'] )
				);

				if ( $formData['frequency'] === "2" ) {
					$postData['recurrence']['weekly_days'] = implode( ', ', $formData['weekly_occurence'] );
				}

				if ( $formData['frequency'] === "3" ) {
					if ( $formData['monthly_occurence_type'] === "1" ) {
						$postData['recurrence']['monthly_day'] = absint( $formData['monthly_occurence_day'] );
					} else {
						$postData['recurrence']['monthly_week']     = absint( $formData['monthly_occurence_week'] );
						$postData['recurrence']['monthly_week_day'] = absint( $formData['monthly_occurence_week_day'] );
					}
				}

				//Prepare end type
				if ( ! empty( $formData['end_type'] ) && $formData['end_type'] === "by_date" ) {
					$postData['recurrence']['end_date_time'] = ! empty( $formData['end_datetime'] ) ? gmdate( "Y-m-d\T12:i:s\Z", strtotime( $formData['end_datetime'] ) ) : date( "Y-m-d\T12:i:s\Z" );
				} else if ( ! empty( $formData['end_type'] ) && $formData['end_type'] === "by_occurrence" ) {
					$postData['recurrence']['end_times'] = ! empty( $formData['end_occurence'] ) ? absint( $formData['end_occurence'] ) : 1;
				}
			}
		} else {
			$postData['type'] = 2;
		}

		return $postData;
	}

	/**
	 * Create Webinar link with API
	 *
	 * @param $postData
	 *
	 * @return mixed
	 */
	public function create_webinar( $postData ) {
		$formData = $this->get_posted_data();

		//Registration
		if ( ! empty( $formData['registration'] ) ) {
			$postData['settings']['approval_type'] = 0;
			if ( ! empty( $formData['registration_type'] ) ) {
				$postData['settings']['registration_type'] = absint( $formData['registration_type'] );
			}

			$postData['settings']['registrants_email_notification'] = true;
			if ( ! empty( $formData['registration_email'] ) ) {
				$postData['settings']['registrants_email_notification'] = false;
				$postData['settings']['registrants_confirmation_email'] = false;
			} else {
				$postData['settings']['registrants_email_notification'] = true;
				$postData['settings']['registrants_confirmation_email'] = true;
			}
		} else {
			$postData['settings']['approval_type'] = 2;
		}

		if ( ! empty( $formData['enabled_recurring'] ) ) {
			if ( $formData['frequency'] === "4" ) {
				$postData['type'] = 6;
			} else {
				$postData['type']       = 9;
				$postData['recurrence'] = array(
					'type'            => absint( $formData['frequency'] ),
					'repeat_interval' => absint( $formData['interval'] )
				);

				if ( $formData['frequency'] === "2" ) {
					$postData['recurrence']['weekly_days'] = implode( ', ', $formData['weekly_occurence'] );
				}

				if ( $formData['frequency'] === "3" ) {
					if ( $formData['monthly_occurence_type'] === "1" ) {
						$postData['recurrence']['monthly_day'] = absint( $formData['monthly_occurence_day'] );
					} else {
						$postData['recurrence']['monthly_week']     = absint( $formData['monthly_occurence_week'] );
						$postData['recurrence']['monthly_week_day'] = absint( $formData['monthly_occurence_week_day'] );
					}
				}

				//Prepare end type
				if ( ! empty( $formData['end_type'] ) && $formData['end_type'] === "by_date" ) {
					$postData['recurrence']['end_date_time'] = ! empty( $formData['end_datetime'] ) ? gmdate( "Y-m-d\T12:i:s\Z", strtotime( $formData['end_datetime'] ) ) : date( "Y-m-d\T12:i:s\Z" );
				} else if ( ! empty( $formData['end_type'] ) && $formData['end_type'] === "by_occurrence" ) {
					$postData['recurrence']['end_times'] = ! empty( $formData['end_occurence'] ) ? absint( $formData['end_occurence'] ) : 1;
				}
			}
		} else {
			$postData['type'] = 5;
		}

		return $postData;
	}

	/**
	 * Run Save Hook in admin area
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save( $post_id, $post ) {
		$postData   = $this->get_posted_data();
		$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
		if ( ! empty( $postData['registration'] ) ) {
			$postData['settings']['approval_type'] = 0;
			if ( ! empty( $postData['registration_type'] ) ) {
				$postData['settings']['registration_type'] = absint( $postData['registration_type'] );
			}

			//Filter registered users and sync with DB
			Helpers::reset_registered_users( $meeting_id, $post_id, false );
			$settings = Fields::get_option( 'settings' );
			$api      = API::get_instance();
			if ( ! empty( $postData['registration_fields'] ) && ! empty( $postData['override_registration_fields'] ) ) {
				$questions['questions'] = Factory::get_registration_additional_fields( $postData['registration_fields'] );
				$api->updateRegistrationQuestions( $meeting_id, $questions );
			} else if ( ! empty( $settings['meeting_registration_fields'] ) ) {
				$questions['questions'] = Factory::get_registration_additional_fields( $settings['meeting_registration_fields'] );
				$api->updateRegistrationQuestions( $meeting_id, $questions );
			} else {
				$api->updateRegistrationQuestions( $meeting_id, [ 'questions' => [] ] );
			}
		}

		if ( empty( $postData['registration'] ) ) {
			$registered_users = Fields::get_meta( $post_id, 'registered_user_ids' );
			if ( ! empty( $registered_users ) ) {
				foreach ( $registered_users as $k => $registered_user ) {
					$registrant_detail = Fields::get_user_meta( $registered_user, 'registration_details' );
					if ( ! empty( $registrant_detail ) && ! empty( $registrant_detail[ $meeting_id ] ) ) {
						unset( $registrant_detail[ $meeting_id ] );
						unset( $registered_users[ $k ] );
						Fields::set_user_meta( $registered_user, 'registration_details', $registrant_detail );
					}
				}

				Fields::set_post_meta( $post_id, 'registered_user_ids', $registered_users );
			}
		}

		Fields::set_post_meta( $post_id, 'meeting_details', $postData );
		Fields::flush_cache( $post_id, 'registrants' );

		//Reset Cron flag on post save.
		Fields::set_post_meta( $post_id, 'cron_one_day', '' );

		//Do Action for storing the meta data
		do_action( 'vczapi_pro_admin_after_save_meetings_meta', $post_id, $post, $postData );
	}

	/**
	 * Choose a Meeting HOST
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	public function meeting_host( $post ) {
		global $current_screen;

		if ( $current_screen->id === "zoom-meetings" && $current_screen->post_type === "zoom-meetings" ) {
			$user_id = get_current_user_id();
			$host_id = get_user_meta( $user_id, 'user_zoom_hostid', true );
			if ( ! empty( $host_id ) ) {
				add_filter( 'vczapi_admin_show_alternative_host_selection', [ $this, 'hide_host_selection' ] );
				add_filter( 'vczapi_admin_show_host_selection', [ $this, 'hide_host_selection' ] );
				echo '<input type="hidden" name="userId" value="' . esc_attr( $host_id ) . '">';
			}
		}
	}

	/**
	 * Disable host selection for vendors
	 *
	 * @return bool
	 */
	public function hide_host_selection() {
		return false;
	}
}