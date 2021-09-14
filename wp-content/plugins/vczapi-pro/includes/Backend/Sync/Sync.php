<?php

namespace Codemanas\ZoomPro\Backend\Sync;

use Codemanas\ZoomPro\Core\Factory;

/**
 * Class Sync
 */
class Sync {

	public function __construct() {
		add_action( 'vczapi_admin_after_sync_html', [ $this, 'recurring_sync' ] );
	}

	/**
	 * Recurring Sync
	 *
	 * @param $users
	 */
	public function recurring_sync( $users ) {
		wp_enqueue_script( 'vczapi-pro-admin-script', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/backend/js/script.min.js', array( 'jquery' ), VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION, true );
		// Localize the script with new data
		$translation_array = array(
			'required' => __( 'Required fields are missing !!', 'vczapi-pro' ),
		);
		wp_localize_script( 'vczapi-pro-admin-script', 'vczapi_pro_i10n', $translation_array );
		if ( is_object( $users ) ) {
			$users = [ $users ];
		}
		include_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Backend/Sync/tpl-sync.php';
	}

	/**
	 * Start Sync Process
	 */
	public static function syncByMeetingID() {
		$host_id      = filter_input( INPUT_POST, 'host_id' );
		$meeting_id   = filter_input( INPUT_POST, 'meeting_id' );
		$type         = filter_input( INPUT_POST, 'type' );
		$meeting_type = filter_input( INPUT_POST, 'meeting_type' );

		$zoom_api_key    = get_option( 'zoom_api_key' );
		$zoom_api_secret = get_option( 'zoom_api_secret' );
		if ( empty( $zoom_api_key ) || empty( $zoom_api_secret ) ) {
			wp_send_json_error( __( 'API keys are not configured properly ! Please configure them before syncing.', 'vczapi-pro' ) );
		}

		if ( empty( $host_id ) || empty( $meeting_id ) ) {
			wp_send_json_error( __( 'Please select a user and enter a valid meeting/webinar ID.', 'vczapi-pro' ) );
		}

		$factory = Factory::get_instance();
		if ( $factory->get_posts_by_meeting_id( $meeting_id, true, array( 'pending', 'draft', 'future', 'publish' ) ) ) {
			wp_send_json_error( sprintf( __( 'Meeting/Webinar with ID: %d already exists.', 'vczapi-pro' ), $meeting_id ) );
		}

		if ( $type === "check" ) {
			if ( $meeting_type == "2" ) {
				$meeting = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
			} else {
				$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
			}

			if ( ! empty( $meeting->code ) ) {
				wp_send_json_error( $meeting->message );
			} else {
				update_option( '_vczapi_sync_meetings', json_encode( $meeting ) );
				wp_send_json_success( $meeting );
			}
		}

		if ( $type === "sync" ) {
			$meeting = get_option( '_vczapi_sync_meetings' );
			if ( ! empty( $meeting ) ) {
				$meeting = json_decode( $meeting );

				if ( $meeting_type == "2" ) {
					//Create Webinar in WordPress based on this meeting ID.
					$factory->create_meeting_post_type( $meeting, true, false );
				} else {
					//Create Meeting in WordPress based on this meeting ID.
					$factory->create_meeting_post_type( $meeting );
				}
			}

			wp_send_json_success( [ 'msg' => 'Done', 'step' => 0 ] );
		}

		wp_die();
	}

	/**
	 * Sync By User Method
	 */
	public static function syncByUser() {
		$zoom_api_key    = get_option( 'zoom_api_key' );
		$zoom_api_secret = get_option( 'zoom_api_secret' );
		if ( empty( $zoom_api_key ) || empty( $zoom_api_secret ) ) {
			wp_send_json_error( __( 'API keys are not configured properly ! Please configure them before syncing.', 'vczapi-pro' ) );
		}

		$type = filter_input( INPUT_POST, 'type' );
		if ( $type === "fetch" ) {
			ob_start();
			?>
            <table class="wp-list-table widefat fixed striped table-view-list vczapi-pro-sync-meeting-table">
                <thead>
                <tr>
                    <td class="manage-column column-name"><?php _e( 'Action', 'vczapi-pro' ); ?></td>
                    <td class="manage-column column-name"><?php _e( 'Meeting ID', 'vczapi-pro' ); ?></td>
                    <td class="manage-column column-name"><?php _e( 'Topic', 'vczapi-pro' ); ?></td>
                    <td class="manage-column column-name"><?php _e( 'Type', 'vczapi-pro' ); ?></td>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
			<?php
			$table = ob_get_clean();
			wp_send_json_success( $table );
		}

		if ( $type === "check" ) {
			$page_size    = filter_input( INPUT_POST, 'length' );
			$host_id      = filter_input( INPUT_POST, 'user_id' );
			$page_number  = absint( filter_input( INPUT_POST, 'start' ) );
			$draw         = absint( filter_input( INPUT_POST, 'draw' ) );
			$meeting_type = absint( filter_input( INPUT_POST, 'mtg_type' ) );
			$page_number  = ( $page_number / $page_size ) + 1;

			$failure = array(
				'draw'            => 1,
				'recordsTotal'    => 0,
				'recordsFiltered' => 0,
				'data'            => array()
			);
			if ( empty( $host_id ) ) {
				wp_send_json( $failure );
			}

			if ( $meeting_type == "2" ) {
				$webinars = json_decode( zoom_conference()->listWebinar( $host_id, array( 'page_size' => $page_size, 'page_number' => $page_number ) ) );
				if ( ! empty( $webinars ) ) {
					//Capture Error
					if ( ! empty( $webinars->code ) ) {
						wp_send_json( $failure );
					}

					if ( ! empty( $webinars->webinars ) ) {
						$result = array(
							'draw'            => $draw,
							'recordsTotal'    => $webinars->total_records,
							'recordsFiltered' => $webinars->total_records
						);

						foreach ( $webinars->webinars as $webinar ) {
							$result['data'][] = array(
								'<a href="javascript:void(0);" data-type="2" data-user="' . $webinar->host_id . '" data-meeting="' . $webinar->id . '" class="vczapi-pro-sync-meeting-by-id">' . __( 'Import Webinar', 'vczapi-pro' ) . '</a>',
								$webinar->id,
								$webinar->topic,
								vczapi_pro_check_type( $webinar->type ) ? 'Recurring' : 'Normal'
							);
						}

						wp_send_json( $result );
					} else {
						wp_send_json( $failure );
					}
				} else {
					wp_send_json( $failure );
				}
			} else {
				$meetings = json_decode( zoom_conference()->listMeetings( $host_id, array( 'page_size' => $page_size, 'page_number' => $page_number ) ) );
				if ( ! empty( $meetings ) ) {
					//Capture Error
					if ( ! empty( $meetings->code ) ) {
						wp_send_json( $failure );
					}

					if ( ! empty( $meetings->meetings ) ) {
						$result = array(
							'draw'            => $draw,
							'recordsTotal'    => $meetings->total_records,
							'recordsFiltered' => $meetings->total_records
						);

						foreach ( $meetings->meetings as $meeting ) {
							$result['data'][] = array(
								'<a href="javascript:void(0);" data-type="1" data-user="' . $meeting->host_id . '" data-meeting="' . $meeting->id . '" class="vczapi-pro-sync-meeting-by-id">' . __( 'Import Meeting', 'vczapi-pro' ) . '</a>',
								$meeting->id,
								$meeting->topic,
								vczapi_pro_check_type( $meeting->type ) ? 'Recurring' : 'Normal'
							);
						}

						wp_send_json( $result );
					} else {
						wp_send_json( $failure );
					}
				} else {
					wp_send_json( $failure );
				}
			}
		}

		wp_die();
	}
}