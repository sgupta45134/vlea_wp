<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Helpers;

/**
 * Class Shortcodes
 *
 * Handler for all shortcodes
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Shortcodes {

	private $shortcodes;

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		$this->shortcodes = array(
			'vczapi_pro_author_meeting'     => array( Meetings::get_instance(), 'list_author_meeting_render' ),
			'vczapi_pro_author_registrants' => array( Registrations::get_instance(), 'list_author_registrants' ),
			'vczapi_zoom_calendar'          => array( FullCalendar::get_instance(), 'generate_calendar' ),
			'vczapi_list_meetings'          => array( Meetings::get_instance(), 'list_all_meetings' ),
			'vczapi_registered_meetings'    => array( Registrations::get_instance(), 'list_registered_meetings' ),
		);

		//filter to show countdown for [zoom_join_via_browser meeting_id="meetingID" login_required="no" help="no" title="" height="500px" disable_countdown="no"]
		add_filter( 'vczapi_join_via_browser_shortcode_meetings', [ $this, 'append_start_date_for_shortcode_countdown' ] );
		$this->init_shortcodes();
	}

	/**
	 * Init the Shortcode adding function
	 */
	public function init_shortcodes() {
		foreach ( $this->shortcodes as $shortcode => $callback ) {
			add_shortcode( $shortcode, $callback );
		}
	}

	/**
	 * For join via browser countdown
	 *
	 * @param $meeting
	 *
	 * @return mixed
	 */
	public function append_start_date_for_shortcode_countdown( $meeting ) {
		if ( is_object( $meeting ) && ! empty( $meeting->type ) && ( $meeting->type == 8 || $meeting->type == 9 ) && ! isset( $meeting->start_time ) ) {
			$start_time          = Helpers::get_latest_occurence_by_type( $meeting->type, $meeting->timezone, $meeting->occurrences );
			$meeting->start_time = $start_time;
		}

		return $meeting;
	}
}