<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;
use Codemanas\ZoomPro\TemplateFunctions;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Meetings
 *
 * Meetings for Frontend
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Meetings {

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;

	private $current_page;

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
	 * Meetings constructor.
	 */
	public function __construct() {
		remove_action( 'vczoom_meeting_shortcode_join_links', 'video_conference_zoom_shortcode_join_link', 10 );
		add_action( 'vczoom_meeting_shortcode_join_links', [ $this, 'meetings_shortcode_join_link' ] );
	}

	/**
	 * Shortcode Meeting by ID join links change.
	 *
	 * @param $zoom_meeting
	 *
	 * @throws Exception
	 */
	public function meetings_shortcode_join_link( $zoom_meeting ) {
		if ( empty( $zoom_meeting ) ) {
			echo "<p>" . __( 'Meeting is not defined. Try updating this meeting', 'vczapi-pro' ) . "</p>";

			return;
		}

		if ( empty( $zoom_meeting->timezone ) ) {
			$zoom_meeting->timezone = zvc_get_timezone_offset_wp();
		}

		$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_meeting->timezone ) );
		$closest_occurence = false;
		if ( ! empty( $zoom_meeting->type ) && $zoom_meeting->type === 8 && ! empty( $zoom_meeting->occurrences ) ) {
			foreach ( $zoom_meeting->occurrences as $occurrence ) {
				if ( $occurrence->status === "available" ) {
					$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_meeting->timezone ) );
					if ( $start_date >= $now ) {
						$closest_occurence = $occurrence->start_time;
						break;
					}
				}
			}
		} else if ( empty( $zoom_meeting->occurrences ) ) {
			$zoom_meeting->start_time = false;
		} else if ( ! empty( $zoom_meeting->type ) && $zoom_meeting->type === 3 ) {
			$zoom_meeting->start_time = false;
		} else if ( ! empty( $zoom_meeting->type ) && $zoom_meeting->type === 4 ) {
			$zoom_meeting->start_time = false;
		}

		$start_time = ! empty( $closest_occurence ) ? $closest_occurence : $zoom_meeting->start_time;
		$start_time = new DateTime( $start_time, new DateTimeZone( $zoom_meeting->timezone ) );
		$start_time->setTimezone( new DateTimeZone( $zoom_meeting->timezone ) );
		if ( $now <= $start_time ) {
			unset( $GLOBALS['meetings'] );

			if ( ! empty( $zoom_meeting->password ) ) {
				$browser_join = vczapi_get_browser_join_shortcode( $zoom_meeting->id, $zoom_meeting->password, true );
			} else {
				$browser_join = vczapi_get_browser_join_shortcode( $zoom_meeting->id, false, true );
			}

			$factory       = Factory::get_instance();
			$zoom_meetings = $factory->get_posts_by_meeting_id( $zoom_meeting->id, false, 'publish' );
			//IF Zoom meeting exists in the system then only do this else avoid
			if ( ! empty( $zoom_meetings ) && ! empty( $zoom_meeting->registration_url ) ) {
				global $current_user;
				$wp_post              = $zoom_meetings[0];
				$pro_details          = Fields::get_meta( $wp_post->ID, 'meeting_details' );
				$registration_details = Fields::get_user_meta( $current_user->ID, 'registration_details' );
				$GLOBALS['meetings']  = array(
					'zoom'         => $zoom_meeting,
					'pro'          => $pro_details,
					'registration' => $registration_details,
					'current_user' => $current_user,
					'wp_post'      => $wp_post
				);
				TemplateFunctions::get_template( 'shortcode/meeting-join-links.php', true, false );
			} else {
				$join_url            = ! empty( $zoom_meeting->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_meeting->join_url, $zoom_meeting->encrypted_password ) : $zoom_meeting->join_url;
				$GLOBALS['meetings'] = array(
					'join_uri'    => apply_filters( 'vczoom_join_meeting_via_app_shortcode', $join_url, $zoom_meeting ),
					'browser_url' => ! vczapi_check_disable_joinViaBrowser() ? apply_filters( 'vczoom_join_meeting_via_browser_disable', $browser_join ) : false
				);
				vczapi_get_template( 'shortcode/join-links.php', true, false );
			}
		}
	}

	/**
	 * Create meetings post from frontend directly
	 */
	public function create_meeting_render() {
		unset( $GLOBALS['zoom'] );

		$GLOBALS['zoom'] = array(
			'users'        => video_conferencing_zoom_api_get_user_transients(),
			'current_page' => $this->current_page
		);

		wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/js/select2.min.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-select2', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/css/select2.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-timepicker', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-timepicker-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.full.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_script( 'vczapi-pro' );

		TemplateFunctions::get_template( 'frontend/create-meeting.php', true );
	}

	/**
	 * Edit Meeting from frontend
	 */
	public function edit_meeting_render() {
		$post_id = absint( $_GET['id'] );
		$meeting = get_post( $post_id );
		if ( ! empty( $meeting ) ) {
			$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );
			$fields          = get_post_meta( $post_id, '_meeting_fields', true );
			$GLOBALS['zoom'] = array(
				'users'           => video_conferencing_zoom_api_get_user_transients(),
				'post'            => $meeting,
				'meeting_details' => $meeting_details,
				'meeting_fields'  => $fields,
				'current_page'    => $this->current_page
			);
		} else {
			$GLOBALS['zoom'] = false;
		}

		wp_enqueue_script( 'video-conferencing-with-zoom-api-select2-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/js/select2.min.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-select2', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/select2/css/select2.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-timepicker', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-timepicker-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/dtimepicker/jquery.datetimepicker.full.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_script( 'vczapi-pro' );

		TemplateFunctions::get_template( 'frontend/create-meeting.php', true );
	}

	/**
	 * List Author Meeting List for Frontend
	 */
	public function list_author_meeting_render() {
		ob_start();

		unset( $GLOBALS['zoom'] );
		if ( isset( $_GET['id'] ) && ( isset( $_GET['type'] ) && $_GET['type'] === "edit" ) && ( isset( $_GET['view'] ) && $_GET['view'] === "meeting" ) ) {
			$this->current_page = esc_url( Helpers::get_current_page_uri( true ) );
			$this->edit_meeting_render();
		} else if ( isset( $_GET['type'] ) && $_GET['type'] === "add" && isset( $_GET['view'] ) && $_GET['view'] === "meeting" ) {
			$this->current_page = esc_url( Helpers::get_current_page_uri( true ) );
			$this->create_meeting_render();
		} else {
			$this->current_page = esc_url( Helpers::get_current_page_uri() );
			$GLOBALS['zoom']    = array(
				'add_uri' => add_query_arg( array( 'type' => 'add', 'view' => 'meeting' ), $this->current_page )
			);

			wp_enqueue_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION );
			wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
			wp_enqueue_script( 'vczapi-pro' );

			TemplateFunctions::get_template( 'frontend/author-meetings.php', true );
		}

		return ob_get_clean();
	}

	/**
	 * AJAX Call for Getting Author Meeting informations
	 */
	public function get_author_meetings() {
		$user_id      = get_current_user_id();
		$data         = Factory::get_posts( array( 'author' => $user_id ) );
		$current_page = filter_input( INPUT_GET, 'pg' );
		$response     = array();
		if ( $data->have_posts() ) {
			foreach ( $data->get_posts() as $post ) {
				$meeting_details = get_post_meta( $post->ID, '_meeting_zoom_details', true );
				$type            = get_post_meta( $post->ID, '_vczapi_meeting_type', true );
				$type            = ! empty( $type ) && $type === "meeting" ? __( "Meeting", "vczapi-pro" ) : __( "Webinar", "vczapi-pro" );

				$occurrences = ( isset( $meeting_details->occurrences ) && is_array( $meeting_details->occurrences ) ) ? $meeting_details->occurrences : '';
				if ( ! empty( $occurrences ) ) {
					$timezone   = ! empty( $occurrences[0]->timezone ) ? $occurrences[0]->timezone : $meeting_details->timezone;
					$start_date = vczapi_dateConverter( $occurrences[0]->start_time, $timezone );
				} else if ( ! empty( $meeting_details->start_time ) ) {
					$start_date = vczapi_dateConverter( $meeting_details->start_time, $meeting_details->timezone );
				} else {
					$start_date = 'N/A';
				}

				$response[] = array(
					'post_id'          => $post->ID,
					'title'            => $post->post_title,
					'permalink'        => get_permalink( $post->ID ),
					'start_date'       => $start_date,
					'meeting_id'       => $meeting_details->id,
					'date'             => $post->post_modified,
					'type'             => $type,
					'start_link'       => '<a href="' . $meeting_details->start_url . '" target="_blank" rel="nofollow">' . __( "Start Meeting", "vczapi-pro" ) . '</a>',
					'edit_link'        => add_query_arg( array( 'id' => $post->ID, 'type' => 'edit', 'view' => 'meeting' ), $current_page ),
					'view_registrants' => '<a href="javascript:void(0);" class="vczapi-pro-view-registrants" data-post="' . $post->ID . '" rel="nofollow">' . __( "View Registrants", "vczapi-pro" ) . '</a><div class="vczapi-modal"></div>',
				);
			}
		}

		$response = apply_filters( 'vczapi_pro_get_author_meetings_dtable', $response );
		wp_send_json_success( $response );

		wp_die();
	}

	/**
	 * Meeting Create AJAX call
	 */
	public function save() {
		check_ajax_referer( 'vczapi-pro-create-meeting' );

		//IF POST IS EDITING
		$post_exists = absint( sanitize_text_field( filter_input( INPUT_POST, 'post_id' ) ) );

		//Prepare Post Data
		$postData = array(
			'userId'       => sanitize_text_field( filter_input( INPUT_POST, 'meeting_host' ) ),
			'meetingTopic' => sanitize_text_field( filter_input( INPUT_POST, 'meeting_title' ) ),
			'start_date'   => sanitize_text_field( filter_input( INPUT_POST, 'meeting_start_time' ) ),
			'timezone'     => sanitize_text_field( filter_input( INPUT_POST, 'meeting_timezone' ) ),
			'password'     => sanitize_text_field( filter_input( INPUT_POST, 'meeting_password' ) ),
		);

		if ( ! empty( $post_exists ) ) {
			$postData['topic'] = $postData['meetingTopic'];
		}

		//Validate Before Submission
		if ( empty( $postData['meetingTopic'] ) || empty( $postData['start_date'] ) || empty( $postData['userId'] ) ) {
			wp_send_json_error( __( 'Required fields are missing!', 'vczapi-pro' ) );
		}

		$post_arr = array(
			'post_title'   => $postData['meetingTopic'],
			'post_content' => sanitize_textarea_field( filter_input( INPUT_POST, 'meeting_description' ) ),
			'post_status'  => 'publish',
			'post_type'    => 'zoom-meetings'
		);

		//If Post Exists then update the meeting else create new one
		if ( ! empty( $post_exists ) ) {
			$post_arr['ID'] = $post_exists;
			$post_id        = wp_update_post( $post_arr );
		} else {
			$post_id = wp_insert_post( $post_arr );
		}

		//Created WP_Post
		if ( ! empty( $post_id ) ) {
			//Call before meeting is created.
			do_action( 'vczapi_pro_frontend_zoom_meeting_is_created', $postData );

			//Update Post Meta Values
			update_post_meta( $post_id, '_meeting_fields', $postData );
			$meeting_type = ! empty( $postData['meeting_type'] ) && $postData['meeting_type'] === 2 ? 'webinar' : 'meeting';
			update_post_meta( $post_id, '_vczapi_meeting_type', $meeting_type );

			try {
				//converted saved time from the timezone provided for meeting to UTC timezone so meetings can be better queried
				$savedDateTime     = new \DateTime( $postData['start_date'], new \DateTimeZone( $postData['timezone'] ) );
				$startDateTimezone = $savedDateTime->setTimezone( new \DateTimeZone( 'UTC' ) );
				update_post_meta( $post_id, '_meeting_field_start_date_utc', $startDateTimezone->format( 'Y-m-d H:i:s' ) );
			} catch ( Exception $e ) {
				update_post_meta( $post_id, '_meeting_field_start_date_utc', $e->getMessage() );
			}

			$meeting_id = get_post_meta( $post_id, '_meeting_zoom_meeting_id', true );
			if ( ! empty( $post_exists ) && ! empty( $meeting_id ) ) {
				$postData['meeting_id'] = $meeting_id;
				$this->edit_meeting( $postData, $post_id, $meeting_id );
			} else {
				$this->create_meeting( $postData, $post_id );
			}
		} else {
			wp_send_json_error( __( 'Error occurred when trying to create meeting. Please try again later.', 'vczapi-pro' ) );
		}

		wp_die();
	}

	/**
	 * Edit Meeting
	 *
	 * @param $postData
	 * @param $post_id
	 * @param $meeting_id
	 */
	private function edit_meeting( $postData, $post_id, $meeting_id ) {
		$meeting_updated = json_decode( zoom_conference()->updateMeetingInfo( $postData ) );
		if ( empty( $meeting_updated->code ) ) {
			$meeting_info = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
			if ( ! empty( $meeting_info ) && empty( $meeting_info->code ) ) {
				update_post_meta( $post_id, '_meeting_zoom_details', $meeting_info );
				update_post_meta( $post_id, '_meeting_zoom_join_url', $meeting_info->join_url );
				update_post_meta( $post_id, '_meeting_zoom_start_url', $meeting_info->start_url );
				update_post_meta( $post_id, '_meeting_zoom_meeting_id', $meeting_info->id );

				wp_send_json_success( sprintf( __( 'Meeting has been updated succesfully. Your meeting ID is: %s', 'vczapi-pro' ), $meeting_info->id ) );
			} else {
				//Store Error Message
				update_post_meta( $post_id, '_meeting_zoom_details', $meeting_info );

				wp_send_json_error( sprintf( __( 'Error occurred when trying to update meeting. Error Message: %s', 'vczapi-pro' ), $meeting_info->message ) );
			}
		} else {
			//Store Error Message
			update_post_meta( $post_id, '_meeting_zoom_details', $meeting_updated );

			wp_send_json_error( sprintf( __( 'Error occurred when trying to update meeting. Error Message: %s', 'vczapi-pro' ), $meeting_updated->message ) );
		}
	}

	/**
	 * Create a Meeting
	 *
	 * @param $postData
	 * @param $post_id
	 */
	private function create_meeting( $postData, $post_id ) {
		//Create Meeting
		$created = json_decode( zoom_conference()->createAMeeting( $postData ) );
		if ( ! empty( $created ) && empty( $created->code ) ) {
			update_post_meta( $post_id, '_meeting_zoom_details', $created );
			update_post_meta( $post_id, '_meeting_zoom_join_url', $created->join_url );
			update_post_meta( $post_id, '_meeting_zoom_start_url', $created->start_url );
			update_post_meta( $post_id, '_meeting_zoom_meeting_id', $created->id );

			wp_send_json_success( sprintf( __( 'Meeting has been created succesfully. Your meeting ID is: %s', 'vczapi-pro' ), $created->id ) );
		} else {
			//Store Error Message
			update_post_meta( $post_id, '_meeting_zoom_details', $created );

			wp_send_json_error( sprintf( __( 'Error occurred when trying to create meeting. Error Message: %s', 'vczapi-pro' ), $created->message ) );
		}
	}

	/**
	 * List all meetings
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function list_all_meetings( $atts ) {
		$atts = shortcode_atts(
			array(
				'author'       => '',
				'per_page'     => 5,
				'category'     => '',
				'order'        => 'ASC',
				'type'         => '',
				'filter'       => 'yes',
				'show'         => '',
				'show_on_past' => 'yes',
				'cols'         => 3
			),
			$atts, 'vczapi_list_meetings'
		);
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		$query_args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => $atts['per_page'],
			'post_status'    => 'publish',
			'paged'          => $paged,
			'orderby'        => 'meta_value',
			'meta_key'       => '_meeting_field_start_date_utc',
			'order'          => $atts['order'],
			'caller'         => ! empty( $atts['filter'] ) && $atts['filter'] === "yes" ? 'vczapi' : false,
			'meta_query'     => array()
		);

		if ( ! empty( $atts['author'] ) ) {
			$query_args['author'] = absint( $atts['author'] );
		}

		if ( ! empty( $atts['type'] ) ) {
			//NOTE !!!! When using this filter please correctly send minutes or hours otherwise it will output error
			$threshold_limit = apply_filters( 'vczapi_list_cpt_meetings_threshold', '30 minutes' );
			if ( $atts['show_on_past'] === "yes" && ! empty( $threshold_limit ) ) {
				$threshold = ( $atts['type'] === "upcoming" ) ? vczapi_dateConverter( 'now -' . $threshold_limit, 'UTC', 'Y-m-d H:i:s', false ) : vczapi_dateConverter( 'now +' . $threshold_limit, 'UTC', 'Y-m-d H:i:s', false );
			} else {
				$threshold = vczapi_dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false );
			}

			$type       = ( $atts['type'] === "upcoming" ) ? '>=' : '<=';
			$meta_query = array(
				'key'     => '_meeting_field_start_date_utc',
				'value'   => $threshold,
				'compare' => $type,
				'type'    => 'DATETIME'
			);
			array_push( $query_args['meta_query'], $meta_query );
		}

		if ( ! empty( $atts['show'] ) ) {
			$meeting_type = ! empty( $atts['show'] ) && $atts['show'] === "meeting" ? "meeting" : "webinar";
			array_push( $query_args['meta_query'], array(
				'key'     => '_vczapi_meeting_type',
				'value'   => $meeting_type,
				'compare' => '='
			) );
		}

		if ( ! empty( $atts['category'] ) ) {
			$category                = array_map( 'trim', explode( ',', $atts['category'] ) );
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'zoom-meeting',
					'field'    => 'slug',
					'terms'    => $category,
					'operator' => 'IN'
				]
			];
		}

		$query         = apply_filters( 'vczapi_meeting_list_query_args', $query_args );
		$zoom_meetings = new \WP_Query( $query );
		$content       = '';

		unset( $GLOBALS['zoom_meetings'] );

		//Check for existing occurences
		if ( $zoom_meetings->have_posts() ) {
			foreach ( $zoom_meetings->get_posts() as $meeeting ) {
				$meeting_details = get_post_meta( $meeeting->ID, '_meeting_zoom_details', true );
				if ( ! empty( $meeting_details->start_time ) ) {
					$occurence[ $meeting_details->start_time ] = $meeeting;
				} else if ( ! empty( $meeting_details->occurrences ) && ! empty( $meeting_details->type ) && ! empty( $meeting_details->timezone ) ) {
					$now          = Helpers::date_convert_by_timezone( 'now -1 hour', $meeting_details->timezone );
					$meeting_date = false;
					foreach ( $meeting_details->occurrences as $occurrence ) {
						if ( $occurrence->status === "available" ) {
							$start_date = Helpers::date_convert_by_timezone( $occurrence->start_time, $meeting_details->timezone );
							if ( $start_date >= $now ) {
								$meeting_date = $occurrence->start_time;
								break;
							}
						}
					}

					if ( ! empty( $meeting_date ) ) {
						$occurence[ $meeting_date ] = $meeeting;
					} else if ( ! empty( $meeting_details->occurrences ) ) {
						$last_occurence               = end( $meeting_details->occurrences );
						$last_occurence               = $last_occurence->start_time;
						$occurence[ $last_occurence ] = $meeeting;
					}
				}
			}

			if ( ! empty( $occurence ) ) {
				if ( ! empty( $_GET['orderby'] ) && $_GET['orderby'] == "latest" ) {
					krsort( $occurence );
				} else {
					ksort( $occurence );
				}

				$zoom_meetings->posts      = array_values( $occurence );
				$zoom_meetings->post_count = count( $occurence );
			}
		}

		$GLOBALS['zoom_meetings']          = $zoom_meetings;
		$GLOBALS['zoom_meetings']->columns = ! empty( $atts['cols'] ) ? absint( $atts['cols'] ) : 3;
		ob_start();
		TemplateFunctions::get_template( 'shortcode-listing.php', true, false );
		$content .= ob_get_clean();

		return $content;
	}

	private function date_sort( $a, $b ) {
		return strtotime( $a ) - strtotime( $b );
	}
}