<?php

namespace Codemanas\ZoomPro\Core;

/**
 * Class Listener
 *
 * @since 1.3.0
 * @author Deepen
 * @copyright 2021. All rights reserved. CodeManas
 */
class Listener {

	private $authorization;

	/**
	 * Listener constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_enpoints' ] );
	}

	/**
	 * Register endpoints
	 */
	public function register_enpoints() {
		register_rest_route( 'vczapi/v1', '/meeting', array(
			'methods'             => [ 'POST' ],
			'callback'            => [ $this, 'meeting' ],
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Triggere here when a meeting request is called
	 * No Authentication required.
	 *
	 * @param \WP_REST_Request $request
	 */
	public function meeting( $request ) {
		$authorized = $this->authorize( $request );

		//Take action after certain seconds because meta values will not be stored instantly.
		sleep( 10 );

		//If Authorization is valid then proceed.
		if ( $authorized ) {
			$response = json_decode( $request->get_body() );
			if ( ! empty( $response ) && ! empty( $response->event ) && ! empty( $response->payload ) ) {
				$payload = ! empty( $response->payload->object ) ? $response->payload->object : false;
				if ( $payload ) {
					$meeting_id = $payload->id;
					$factory    = Factory::get_instance();
					switch ( $response->event ) {
						case  "meeting.updated":
							$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
							if ( ! empty( $meeting ) && empty( $meeting->code ) ) {
								//Update Meeting
								$factory->update_meeting_post_type( $meeting );
							}
							break;
						case  "meeting.created":
							$old_post = $factory->get_posts_by_meeting_id( $meeting_id, false );
							if ( ! empty( $old_post ) ) {
								break;
							}

							$meeting  = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
							if ( ! empty( $meeting ) && empty( $meeting->code ) ) {
								//Create Meeting
								$factory->create_meeting_post_type( $meeting, false, true, 'draft' );
							}
							break;
						case  "meeting.recovered":
							//Get Meeting from Trash as well
							$posts = $factory->get_posts_by_meeting_id( $meeting_id, false, array( 'pending', 'draft', 'future', 'trash' ) );
							if ( ! empty( $posts ) ) {
								foreach ( $posts as $post ) {
									wp_update_post( array(
										'ID'          => $post->ID,
										'post_status' => 'draft'
									) );
								}
							}
							break;
						case  "meeting.deleted":
							$posts = $factory->get_posts_by_meeting_id( $meeting_id, false );
							if ( ! empty( $posts ) ) {
								foreach ( $posts as $post ) {
									wp_trash_post( $post->ID );
								}
							}
							break;
						case  "meeting.permanently_deleted":
							//Get Meeting from Trash as well
							$posts = $factory->get_posts_by_meeting_id( $meeting_id, false );
							if ( ! empty( $posts ) ) {
								foreach ( $posts as $post ) {
									wp_delete_post( $post->ID, true );
								}
							}
							break;
						case  "webinar.updated":
							$meeting = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
							if ( ! empty( $meeting ) && empty( $meeting->code ) ) {
								//Update Meeting
								$factory->update_meeting_post_type( $meeting, false );
							}
							break;
						case  "webinar.created":
							$old_post = $factory->get_posts_by_meeting_id( $meeting_id, false );
							if ( ! empty( $old_post ) ) {
								break;
							}

							$meeting  = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
							if ( ! empty( $meeting ) && empty( $meeting->code ) ) {
								//Create Meeting
								$factory->create_meeting_post_type( $meeting, false, false, 'draft' );
							}
							break;
						case  "webinar.deleted":
							$posts = $factory->get_posts_by_meeting_id( $meeting_id, false );
							if ( ! empty( $posts ) ) {
								foreach ( $posts as $post ) {
									wp_trash_post( $post->ID );
								}
							}
							break;
						default:
							break;
					}
				}
			}

			#file_put_contents( VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Core/result.json', var_export( $response, true ) );
		}
	}

	/**
	 * Check whether authorization key is verified.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return Boolean
	 */
	private function authorize( $request ) {
		$settings            = Fields::get_option( 'settings' );
		$this->authorization = $request->get_header( 'authorization' );
		if ( ! empty( $this->authorization ) && ( ! empty( $settings ) && ! empty( $settings['verification_code'] ) && $settings['verification_code'] === $this->authorization ) ) {
			return true;
		}

		return false;
	}
}