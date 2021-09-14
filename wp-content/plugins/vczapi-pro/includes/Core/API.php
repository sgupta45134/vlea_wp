<?php

namespace Codemanas\ZoomPro\Core;

/**
 * Class API
 *
 * @since 1.0.0
 * @author Deepen
 * @copyright 2020. All rights reserved. CodeManas
 */
class API extends \Zoom_Video_Conferencing_Api {

	/**
	 * Hold my instance
	 *
	 * @var
	 */
	protected static $_instance;

	/**
	 * Zoom API KEY
	 *
	 * @var
	 */
	public $zoom_api_key;

	/**
	 * Zoom API Secret
	 *
	 * @var
	 */
	public $zoom_api_secret;

	/**
	 * @return API|\Zoom_Video_Conferencing_Api
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->zoom_api_key    = get_option( 'zoom_api_key' );
		$this->zoom_api_secret = get_option( 'zoom_api_secret' );

		parent::__construct( $this->zoom_api_key, $this->zoom_api_secret );
	}

	/**
	 * Add a meeting registrant
	 *
	 * @param $meetingId
	 * @param bool $postData
	 *
	 * @return array|bool|mixed|string|
	 */
	public function addMeetingRegistrant( $meetingId, $postData = false ) {
		$postData = apply_filters( 'vczapi_pro_api_addMeetingRegistrant', $postData );

		return $this->sendRequest( 'meetings/' . $meetingId . '/registrants', $postData, "POST" );
	}

	/**
	 * Add a Webinar registrant
	 *
	 * @param $webinar_id
	 * @param bool $postData
	 *
	 * @return array|bool|mixed|string|
	 */
	public function addWebinarRegistrant( $webinar_id, $postData = false ) {
		$postData = apply_filters( 'vczapi_pro_api_addWebinarRegistrant', $postData );

		return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants', $postData, "POST" );
	}

	/**
	 * Get Meeting Registrants
	 *
	 * @param $meetingId
	 *
	 * @return array|bool|string|
	 */
	public function getMeetingRegistrant( $meetingId ) {
		$postData['page_size'] = 300;

		return $this->sendRequest( 'meetings/' . $meetingId . '/registrants', $postData, "GET" );
	}

	/**
	 * Get Webinar Registrants
	 *
	 * @param $webinar_id
	 *
	 * @return array|bool|string|
	 */
	public function getWebinarRegistrants( $webinar_id ) {
		$postData['page_size'] = 300;

		return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants', $postData, "GET" );
	}

	/**
	 * Update Meeting Registrants
	 *
	 * @param $meeting_id
	 * @param bool $postData
	 *
	 * @return array|bool|string|\WP_Error
	 */
	public function updateMeetingRegistrants( $meeting_id, $postData = false ) {
		$postData = apply_filters( 'vczapi_pro_api_updateMeetingRegistrants', $postData );

		return $this->sendRequest( 'meetings/' . $meeting_id . '/registrants/status', $postData, "PUT" );
	}

	/**
	 * Update Webinar registrants
	 *
	 * @param $webinar_id
	 * @param bool $postData
	 *
	 * @return array|bool|string|\WP_Error
	 */
	public function updateWebinarRegistrants( $webinar_id, $postData = false ) {
		$postData = apply_filters( 'vczapi_pro_api_updateWebinarRegistrants', $postData );

		return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants/status', $postData, "PUT" );
	}

	/**
	 * Update registration questions
	 *
	 * @param $meeting_id
	 * @param $postData
	 *
	 * @return array|bool|string|\WP_Error
	 */
	public function updateRegistrationQuestions( $meeting_id, $postData ) {
		$postData = apply_filters( 'vczapi_pro_api_updateRegistrationQuestions', $postData );

		return $this->sendRequest( 'meetings/' . $meeting_id . '/registrants/questions', $postData, "PATCH" );
	}
}