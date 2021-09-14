<?php

namespace Codemanas\ZoomPro\Core;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Factory
 *
 * Get all factory methods
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Factory {

	/**
	 * @var string
	 */
	private static $post_type = 'zoom-meetings';

	/**
	 * @var int
	 */
	protected static $per_page = 10;

	/**
	 * @var int
	 */
	protected static $paged = 1;

	/**
	 * @var string
	 */
	protected static $order = 'DESC';

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;

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
	 * Factory constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get Zoom Meeting posts
	 *
	 * @param $args
	 *
	 * @return \WP_Query
	 */
	public static function get_posts( $args = false ) {
		$post_arr = array(
			'post_type'      => self::$post_type,
			'posts_per_page' => ! empty( $args['per_page'] ) ? $args['paged'] : self::$per_page,
			'post_status'    => ! empty( $args['status'] ) ? $args['status'] : 'publish',
			'paged'          => ! empty( $args['paged'] ) ? $args['paged'] : self::$paged,
			'order'          => self::$order,
		);

		if ( ! empty( $args['author'] ) ) {
			$post_arr['author'] = absint( $args['author'] );
		}

		//If meeting type is not defined then pull all zoom list regardless of webinar or meeting only.
		if ( ! empty( $args['meeting_type'] ) ) {
			$post_arr['meta_query'] = array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_vczapi_meeting_type',
						'value'   => $args['meeting_type'] === "meeting" ? 'meeting' : 'webinar',
						'compare' => '='
					)
				)
			);
		}

		if ( ! empty( $args['taxonomy'] ) ) {
			$category              = array_map( 'trim', explode( ',', $args['taxonomy'] ) );
			$post_arr['tax_query'] = [
				[
					'taxonomy' => 'zoom-meeting',
					'field'    => 'slug',
					'terms'    => $category,
					'operator' => 'IN'
				]
			];
		}

		$query  = apply_filters( 'vczapi_pro_get_posts_query_args', $post_arr );
		$result = new \WP_Query( $query );

		return $result;
	}

	/**
	 * Get Users by SQL query
	 *
	 * @return array|object|null
	 */
	public static function get_registered_users() {
		$query = array(
			'number'   => - 1,
			'meta_key' => '_vczapi_pro_registration_details'
		);

		$users = new \WP_User_Query( $query );
		$users = $users->get_results();

		return $users;
	}

	/**
	 * Update Actual Zoom Meeting POST TYPES
	 *
	 * @param $meeting_obj
	 * @param $meeting
	 * @param $post_status
	 */
	public function update_meeting_post_type( $meeting_obj, $meeting = true, $post_status = false ) {
		$old_post = $this->get_posts_by_meeting_id( $meeting_obj->id, false );
		if ( ! empty( $old_post ) ) {
			//Update Post Meta Values
			$post_arr = array(
				'ID'           => $old_post[0]->ID,
				'post_title'   => $meeting_obj->topic,
				'post_content' => ! empty( $meeting_obj->agenda ) ? $meeting_obj->agenda : '',
				'post_status'  => ! empty( $post_status ) ? $post_status : $old_post[0]->post_status,
			);
			$post_id  = wp_update_post( $post_arr );
			if ( ! empty( $post_id ) ) {
				//Prepare Meeting Insert Data
				$mtg_param = array(
					'userId'                    => esc_html( $meeting_obj->host_id ),
					'meeting_type'              => $meeting ? absint( 1 ) : absint( 2 ),
					'timezone'                  => ! empty( $meeting_obj->timezone ) ? esc_html( $meeting_obj->timezone ) : '',
					'duration'                  => ! empty( $meeting_obj->duration ) ? esc_html( $meeting_obj->duration ) : '',
					'password'                  => ! empty( $meeting_obj->password ) ? esc_html( $meeting_obj->password ) : false,
					'meeting_authentication'    => ! empty( $meeting_obj->settings->meeting_authentication ) ? absint( $meeting_obj->settings->meeting_authentication ) : false,
					'join_before_host'          => ! empty( $meeting_obj->settings->join_before_host ) ? absint( $meeting_obj->settings->join_before_host ) : false,
					'option_host_video'         => ! empty( $meeting_obj->settings->host_video ) ? absint( $meeting_obj->settings->host_video ) : false,
					'option_participants_video' => ! empty( $meeting_obj->settings->participant_video ) ? absint( $meeting_obj->settings->participant_video ) : false,
					'option_mute_participants'  => ! empty( $meeting_obj->settings->mute_upon_entry ) ? absint( $meeting_obj->settings->mute_upon_entry ) : false,
					'option_auto_recording'     => ! empty( $meeting_obj->settings->auto_recording ) ? esc_html( $meeting_obj->settings->auto_recording ) : 'none',
					'alternative_host_ids'      => ! empty( $meeting_obj->settings->alternative_hosts ) ? $meeting_obj->settings->alternative_hosts : ''
				);

				if ( ! empty( $meeting_obj->occurrences ) ) {
					$mtg_param['start_date'] = vczapi_dateConverter( $meeting_obj->occurrences[0]->start_time, $meeting_obj->timezone, 'Y-m-d H:i', false );
				} else {
					$mtg_param['start_date'] = ! empty( $meeting_obj->start_time ) ? vczapi_dateConverter( $meeting_obj->start_time, $meeting_obj->timezone, 'Y-m-d H:i', false ) : '';
				}

				update_post_meta( $post_id, '_meeting_fields', $mtg_param );
				if ( ! empty( $mtg_param['start_date'] ) ) {
					try {
						//converted saved time from the timezone provided for meeting to UTC timezone so meetings can be better queried
						$savedDateTime     = new DateTime( $mtg_param['start_date'], new DateTimeZone( $mtg_param['timezone'] ) );
						$startDateTimezone = $savedDateTime->setTimezone( new DateTimeZone( 'UTC' ) );
						update_post_meta( $post_id, '_meeting_field_start_date_utc', $startDateTimezone->format( 'Y-m-d H:i:s' ) );
					} catch ( Exception $e ) {
						update_post_meta( $post_id, '_meeting_field_start_date_utc', $e->getMessage() );
					}
				}

				$meeting_type = ! empty( $mtg_param['meeting_type'] ) && $mtg_param['meeting_type'] === 2 ? 'webinar' : 'meeting';
				update_post_meta( $post_id, '_vczapi_meeting_type', $meeting_type );
				update_post_meta( $post_id, '_meeting_zoom_details', $meeting_obj );
				if ( ! empty( $meeting_obj->join_url ) ) {
					update_post_meta( $post_id, '_meeting_zoom_join_url', $meeting_obj->join_url );
				}

				if ( ! empty( $meeting_obj->start_url ) ) {
					update_post_meta( $post_id, '_meeting_zoom_start_url', $meeting_obj->start_url );
				}

				if ( ! empty( $meeting_obj->id ) ) {
					update_post_meta( $post_id, '_meeting_zoom_meeting_id', $meeting_obj->id );
				}

				//PRO VERSION DATA
				if ( ! empty( $meeting_obj->recurrence ) ) {
					$registrationData = array(
						'frequency'                  => ! empty( $meeting_obj->recurrence->type ) ? $meeting_obj->recurrence->type : '',
						'interval'                   => ! empty( $meeting_obj->recurrence->repeat_interval ) ? $meeting_obj->recurrence->repeat_interval : '',
						'weekly_occurence'           => ! empty( $meeting_obj->recurrence->weekly_days ) ? explode( ',', $meeting_obj->recurrence->weekly_days ) : array(),
						'monthly_occurence_type'     => ! empty( $meeting_obj->recurrence->monthly_day ) ? '1' : '2',
						'monthly_occurence_day'      => ! empty( $meeting_obj->recurrence->monthly_day ) ? $meeting_obj->recurrence->monthly_day : '',
						'monthly_occurence_week'     => ! empty( $meeting_obj->recurrence->monthly_week ) ? $meeting_obj->recurrence->monthly_week : '',
						'monthly_occurence_week_day' => ! empty( $meeting_obj->recurrence->monthly_week_day ) ? $meeting_obj->recurrence->monthly_week_day : '',
						'end_type'                   => 'by_occurrence',
						'end_occurence'              => ! empty( $meeting_obj->recurrence->end_times ) ? $meeting_obj->recurrence->end_times : ''
					);
				}

				if ( $meeting_obj->type === 3 || ! empty( $meeting_obj->recurrence ) ) {
					$registrationData['enabled_recurring'] = 'on';

					if ( $meeting_obj->type === 3 ) {
						$registrationData['frequency'] = '4';
					}
				}

				if ( ! empty( $meeting_obj->registration_url ) ) {
					$registrationData['registration'] = ! empty( $meeting_obj->registration_url ) ? 'on' : '';
				}

				if ( ! empty( $meeting_obj->settings->use_pmi ) ) {
					$registrationData['use_pmi'] = ! empty( $meeting_obj->settings->use_pmi ) ? '2' : '1';
				}

				if ( ! empty( $registrationData ) ) {
					Fields::set_post_meta( $post_id, 'meeting_details', $registrationData );
				}
			}
		}
	}

	/**
	 * Create Actual Zoom Meeting POST TYPES
	 *
	 * @param $meeting_obj
	 * @param $json Boolean
	 * @param $meeting
	 * @param $post_status
	 */
	public function create_meeting_post_type( $meeting_obj, $json = true, $meeting = true, $post_status = 'publish' ) {
		//Update Post Meta Values
		$post_arr = array(
			'post_title'   => $meeting_obj->topic,
			'post_content' => ! empty( $meeting_obj->agenda ) ? $meeting_obj->agenda : '',
			'post_status'  => $post_status,
			'post_type'    => self::$post_type,
		);
		$post_id  = wp_insert_post( $post_arr );
		if ( ! empty( $post_id ) ) {
			//Prepare Meeting Insert Data
			$mtg_param = array(
				'userId'                    => esc_html( $meeting_obj->host_id ),
				'meeting_type'              => $meeting ? absint( 1 ) : absint( 2 ),
				'timezone'                  => ! empty( $meeting_obj->timezone ) ? esc_html( $meeting_obj->timezone ) : '',
				'duration'                  => ! empty( $meeting_obj->duration ) ? esc_html( $meeting_obj->duration ) : '',
				'password'                  => ! empty( $meeting_obj->password ) ? esc_html( $meeting_obj->password ) : false,
				'meeting_authentication'    => ! empty( $meeting_obj->settings->meeting_authentication ) ? absint( $meeting_obj->settings->meeting_authentication ) : false,
				'join_before_host'          => ! empty( $meeting_obj->settings->join_before_host ) ? absint( $meeting_obj->settings->join_before_host ) : false,
				'option_host_video'         => ! empty( $meeting_obj->settings->host_video ) ? absint( $meeting_obj->settings->host_video ) : false,
				'option_participants_video' => ! empty( $meeting_obj->settings->participant_video ) ? absint( $meeting_obj->settings->participant_video ) : false,
				'option_mute_participants'  => ! empty( $meeting_obj->settings->mute_upon_entry ) ? absint( $meeting_obj->settings->mute_upon_entry ) : false,
				'option_auto_recording'     => ! empty( $meeting_obj->settings->auto_recording ) ? esc_html( $meeting_obj->settings->auto_recording ) : 'none',
				'alternative_host_ids'      => ! empty( $meeting_obj->settings->alternative_hosts ) ? $meeting_obj->settings->alternative_hosts : ''
			);

			if ( ! empty( $meeting_obj->occurrences ) ) {
				$mtg_param['start_date'] = vczapi_dateConverter( $meeting_obj->occurrences[0]->start_time, $meeting_obj->timezone, 'Y-m-d H:i', false );
			} else {
				$mtg_param['start_date'] = ! empty( $meeting_obj->start_time ) ? vczapi_dateConverter( $meeting_obj->start_time, $meeting_obj->timezone, 'Y-m-d H:i', false ) : '';
			}

			update_post_meta( $post_id, '_meeting_fields', $mtg_param );
			if ( ! empty( $mtg_param['start_date'] ) ) {
				try {
					//converted saved time from the timezone provided for meeting to UTC timezone so meetings can be better queried
					$savedDateTime     = new DateTime( $mtg_param['start_date'], new DateTimeZone( $mtg_param['timezone'] ) );
					$startDateTimezone = $savedDateTime->setTimezone( new DateTimeZone( 'UTC' ) );
					update_post_meta( $post_id, '_meeting_field_start_date_utc', $startDateTimezone->format( 'Y-m-d H:i:s' ) );
				} catch ( Exception $e ) {
					update_post_meta( $post_id, '_meeting_field_start_date_utc', $e->getMessage() );
				}
			}

			$meeting_type = ! empty( $mtg_param['meeting_type'] ) && $mtg_param['meeting_type'] === 2 ? 'webinar' : 'meeting';
			update_post_meta( $post_id, '_vczapi_meeting_type', $meeting_type );
			update_post_meta( $post_id, '_meeting_zoom_details', $meeting_obj );
			if ( ! empty( $meeting_obj->join_url ) ) {
				update_post_meta( $post_id, '_meeting_zoom_join_url', $meeting_obj->join_url );
			}

			if ( ! empty( $meeting_obj->start_url ) ) {
				update_post_meta( $post_id, '_meeting_zoom_start_url', $meeting_obj->start_url );
			}

			if ( ! empty( $meeting_obj->id ) ) {
				update_post_meta( $post_id, '_meeting_zoom_meeting_id', $meeting_obj->id );
			}

			//PRO VERSION DATA
			if ( ! empty( $meeting_obj->recurrence ) ) {
				$registrationData = array(
					'frequency'                  => ! empty( $meeting_obj->recurrence->type ) ? $meeting_obj->recurrence->type : '',
					'interval'                   => ! empty( $meeting_obj->recurrence->repeat_interval ) ? $meeting_obj->recurrence->repeat_interval : '',
					'weekly_occurence'           => ! empty( $meeting_obj->recurrence->weekly_days ) ? explode( ',', $meeting_obj->recurrence->weekly_days ) : array(),
					'monthly_occurence_type'     => ! empty( $meeting_obj->recurrence->monthly_day ) ? '1' : '2',
					'monthly_occurence_day'      => ! empty( $meeting_obj->recurrence->monthly_day ) ? $meeting_obj->recurrence->monthly_day : '',
					'monthly_occurence_week'     => ! empty( $meeting_obj->recurrence->monthly_week ) ? $meeting_obj->recurrence->monthly_week : '',
					'monthly_occurence_week_day' => ! empty( $meeting_obj->recurrence->monthly_week_day ) ? $meeting_obj->recurrence->monthly_week_day : '',
					'end_type'                   => 'by_occurrence',
					'end_occurence'              => ! empty( $meeting_obj->recurrence->end_times ) ? $meeting_obj->recurrence->end_times : ''
				);
			}

			if ( $meeting_obj->type === 3 || ! empty( $meeting_obj->recurrence ) ) {
				$registrationData['enabled_recurring'] = 'on';

				if ( $meeting_obj->type === 3 ) {
					$registrationData['frequency'] = '4';
				}
			}

			if ( ! empty( $meeting_obj->registration_url ) ) {
				$registrationData['registration'] = ! empty( $meeting_obj->registration_url ) ? 'on' : '';
			}

			if ( ! empty( $meeting_obj->settings->use_pmi ) ) {
				$registrationData['use_pmi'] = ! empty( $meeting_obj->settings->use_pmi ) ? '2' : '1';
			}

			if ( ! empty( $registrationData ) ) {
				Fields::set_post_meta( $post_id, 'meeting_details', $registrationData );
			}

			if ( $json ) {
				$data = array(
					'msg'        => __( "Successfully imported meeting with ID", 'vczapi-pro' ) . ': <strong>' . $meeting_obj->id . '</strong>',
					'meeting_id' => $meeting_obj->id
				);
				wp_send_json_success( $data );
			}
		} else {
			if ( $json ) {
				$data = array(
					'msg'        => __( "Failed to import meeting with ID", 'vczapi-pro' ) . ': <strong>' . $meeting_obj->id . '</strong>',
					'meeting_id' => $meeting_obj->id
				);
				wp_send_json_success( $data );
			}
		}
	}

	/**
	 * Get a Meeting Object
	 *
	 * @param $meeting_id
	 * @param $wp_posts
	 * @param $status
	 *
	 * @return bool|int[]|\WP_Post[]
	 */
	public function get_posts_by_meeting_id( $meeting_id, $wp_posts = true, $status = array( 'publish', 'pending', 'draft', 'future' ) ) {
		$args = array(
			'post_type'   => self::$post_type,
			'post_status' => $status,
			'meta_query'  => array(
				array(
					'key'     => '_meeting_zoom_meeting_id',
					'value'   => $meeting_id,
					'compare' => '='
				)
			)
		);

		$result = new \WP_Query( $args );
		if ( $wp_posts ) {
			return $result->have_posts();
		} else {
			return $result->get_posts();
		}
	}

	/**
	 * Get Registration fields and assemble them to fit the data
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public static function get_registration_additional_fields( $fields ) {
		$questions = [];
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $k => $field ) {
				if ( ! empty( $field['enable'] ) ) {
					$questions[] = [
						'field_name' => $k,
						'required'   => !empty( $field['required'] ) && $field['required'] == true ? true : false
					];
				}
			}
		}

		return $questions;
	}

	/**
	 * Filter the submitted registration fields data
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public static function filter_registration_submitted_data( $fields ) {
		$questions = [];
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $k => $field ) {
				if ( ! empty( $field['enable'] ) ) {
					$questions[ $k ] = [
						'enable'   => 'on',
						'required' => isset( $field['required'] ) ? true : false
					];
				}
			}
		}

		return $questions;
	}
}