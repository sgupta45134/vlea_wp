<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Helpers;
use Codemanas\ZoomPro\TemplateFunctions;

/**
 * Class Recurrings
 *
 * Template Hook Register Deregister for Recurring Meetings
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Recurrings {

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
	 * Bootstrap constructor.
	 */
	public function __construct() {
		remove_action( 'vczoom_single_content_right', 'video_conference_zoom_countdown_timer', 10 );
		remove_action( 'vczoom_single_content_right', 'video_conference_zoom_meeting_details', 20 );

		add_action( 'vczoom_single_content_right', [ $this, 'countdown_timer' ], 10 );
		add_action( 'vczoom_single_content_right', [ $this, 'meeting_details' ], 20 );

		//Hook for single page recurring start date
		add_filter( 'vczapi_single_meeting_localized_data', [ $this, 'change_start_date' ] );
	}

	/**
	 * Change Start date for recurring meetings in single Zoom meetings page.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function change_start_date( $data ) {
		$zoom = get_post_meta( $data['post_id'], '_meeting_zoom_details', true );
		if ( is_object( $zoom ) && ! empty( $zoom->occurrences ) && ! empty( $zoom->type ) && vczapi_pro_check_type( $zoom->type ) ) {
			$start_date = Helpers::get_latest_occurence_by_type( $zoom->type, $zoom->timezone, $zoom->occurrences );
			if ( $start_date ) {
				$data['start_date'] = $start_date;
			}
		}

		return $data;
	}

	/**
	 * Show Countdown timer based on latest occurence
	 *
	 * @since 1.0.0
	 */
	public function countdown_timer() {
		global $zoom;

		$type               = ! empty( $zoom['api']->type ) ? $zoom['api']->type : false;
		$timezone           = ! empty( $zoom['api']->timezone ) ? $zoom['api']->timezone : false;
		$occurence          = ! empty( $zoom['api']->occurrences ) ? $zoom['api']->occurrences : false;
		$duration           = ! empty( $zoom['duration'] ) ? absint( $zoom['duration'] ) : 40;
		$zoom['start_date'] = Helpers::get_latest_occurence_by_type( $type, $timezone, $occurence, 'now -' . $duration . ' minutes' );

		//Get Template
		TemplateFunctions::get_template( 'fragments/countdown-timer.php', true );
	}

	/**
	 * Fetch Meeting Template
	 */
	public function meeting_details() {
		TemplateFunctions::get_template( 'fragments/meeting-details.php', true );
	}
}