<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Core\ICS;
use Codemanas\ZoomPro\Core\Mailer;
use Codemanas\ZoomPro\TemplateFunctions;

/**
 * Class GoogleCalender
 *
 * @package Codemanas\ZoomPro\Frontend
 * @since 1.1.6
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 */
class iCal {

	/**
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	public function __construct() {
		add_action( 'vczapi_html_after_meeting_details', [ $this, 'add_to_calender_ics' ] );
		add_action( 'vczapi_additional_content_inside_zoom_listing_shortcode', [ $this, 'add_to_calender_ics' ] );

		//Download Action
		add_action( 'vczapi_before_single_template_load', [ $this, 'download_ics' ] );
	}

	/**
	 * Track Download ICS trigger
	 */
	public function download_ics() {
		if ( isset( $_GET['ics_export'] ) && $_GET['ics_export'] === "1" && get_post_type() === $this->post_type ) {
			global $zoom;

			if ( empty( $zoom ) ) {
				return;
			}

			$zoom_obj = $zoom['api'];
			//IF MEETING IS RECURRING WITH NO FIXED TIME THEN ESCAPE
			if ( $zoom_obj->type === 3 || $zoom_obj->type === 9 ) {
				return;
			}

			//prepare ICS parameters
			$ics = array(
				'location'    => esc_url( get_the_permalink() ),
				'description' => wp_strip_all_tags( get_the_excerpt() ),
				'summary'     => $zoom_obj->topic,
				'url'         => home_url( '/' )
			);

			$duration = ! empty( $zoom_obj->duration ) ? 60 * $zoom_obj->duration : 60 * 60;
			//check if post is recurring
			if ( vczapi_pro_check_type( $zoom_obj->type ) && ! empty( $zoom_obj->occurrences ) ) {
				$ics = ICS::get_occurence_data( $ics, $zoom_obj );
			} else {
				$ics['dtstart'] = $zoom_obj->start_time;
				$ics['dtend']   = date( 'Y-m-d\TH:i:s\Z', strtotime( $zoom_obj->start_time ) + $duration );
			}

			$ics        = apply_filters( 'vczapi_pro_change_ical_details', $ics, $zoom );
			$attachment = Mailer::attach_ics( $ics, true, 'meeting-' . get_the_id() );
			if ( ! empty( $attachment['url'] ) ) {
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="' . basename( $attachment['url'] ) . '"' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				flush(); // Flush system output buffer
				readfile( $attachment['path'] );

				//Clean up
				wp_delete_file( $attachment['path'] );
			}

			die();
		}
	}

	/**
	 * Add to Google Calendar Logic
	 *
	 * @param $zoom
	 *
	 * @return mixed
	 */
	public function add_to_gcal( $zoom ) {
		$zoom_obj = $zoom['api'];
		//IF MEETING IS RECURRING WITH NO FIXED TIME THEN ESCAPE
		if ( vczapi_pro_check_type( $zoom_obj->type ) ) {
			return false;
		}

		$duration = ! empty( $zoom_obj->duration ) ? 60 * $zoom_obj->duration : 60 * 60;
		$ics      = array(
			'location'    => get_the_permalink(),
			'description' => wp_strip_all_tags( get_the_excerpt() ),
			'summary'     => $zoom_obj->topic,
			'url'         => home_url( '/' ),
			'dtstart'     => $zoom_obj->start_time,
			'dtend'       => date( 'Y-m-d\TH:i:s\Z', strtotime( $zoom_obj->start_time ) + $duration )
		);

		$ics = apply_filters( 'vczapi_pro_change_ical_details', $ics, $zoom );

		$duration_minutes = ! empty( $zoom_obj->duration ) ? $zoom_obj->duration : 60;
		//Generate gCAL link
		$gcal_query_args = array(
			'action'   => 'TEMPLATE',
			'text'     => $zoom_obj->topic,
			'dates'    => vczapi_dateConverter( $zoom_obj->start_time, $zoom_obj->timezone, 'Ymd\THis', false ) . '/' . vczapi_dateConverter( $zoom_obj->start_time . '+' . $duration_minutes . ' minutes', $zoom_obj->timezone, 'Ymd\THis', false ),
			'location' => $ics['location'],
			'details'  => $ics['description'],
			'trp'      => 'false',
			'sprop'    => 'website:' . $ics['url'],
			'ctz'      => $zoom_obj->timezone,
		);

		$result = add_query_arg( $gcal_query_args, 'https://www.google.com/calendar/event' );

		return $result;
	}

	/**
	 * Generate Template File
	 *
	 * @return bool|mixed|string|void
	 */
	public function add_to_calender_ics() {
		global $zoom;
		global $current_user;

		$meeting_id = ! empty( $zoom['api'] ) && ! empty( $zoom['api']->id ) ? $zoom['api']->id : false;
		//IF MEETING ID IS BLANK ABORT
		if ( empty( $meeting_id ) ) {
			return;
		}

		//Check if a user is registered to the meeting - If yes, then show calendar buttons
		$bypass = apply_filters( 'vczapi_pro_force_show_ical_btns', true );
		if ( $bypass ) {
			$regisration_details = Fields::get_user_meta( $current_user->ID, 'registration_details' );
			if ( ! empty( $zoom['api']->registration_url ) && ! empty( $regisration_details ) && empty( $regisration_details[ $meeting_id ]->registrant_id ) ) {
				return;
			} else if ( ! empty( $zoom['api']->registration_url ) && ! is_user_logged_in() ) {
				return;
			}
		}

		$post_id     = get_the_id();
		$current_url = get_the_permalink( $post_id );
		if ( ! empty( $zoom['api'] ) && ( $zoom['api']->type !== 3 || $zoom['api']->type !== 9 ) ) {
			$zoom['export_cal_link'] = add_query_arg( array( 'ics_export' => 1 ), $current_url );
		}

		//IF MEETING IS RECURRING WITH NO FIXED TIME THEN ESCAPE
		if ( ! empty( $zoom['api'] ) && ! vczapi_pro_check_type( $zoom['api']->type ) ) {
			$zoom['export_gcal_link'] = $this->add_to_gcal( $zoom );
		}

		$settings   = Fields::get_option( 'settings' );
		$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );
		if ( ! empty( $settings ) && ( ! empty( $settings['hide_ical_links'] ) || ( ! empty( $settings['hide_ical_links_woocommerce'] ) && ! wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) ) ) {
			return;
		}

		return TemplateFunctions::get_template( 'fragments/add-to-calender.php', true, false );
	}


}