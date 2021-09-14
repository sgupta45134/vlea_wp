<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Core\Factory;
use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\TemplateFunctions;
use DateTime;
use DateTimeZone;

/**
 * Class Webinars
 *
 * Webinars for Frontend
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.4.1
 * @package Codemanas\ZoomPro
 */
class Webinars {

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
	 * Meetings constructor.
	 */
	public function __construct() {
		remove_action( 'vczoom_meeting_shortcode_join_links_webinar', 'video_conference_zoom_shortcode_join_link_webinar', 10 );
		add_action( 'vczoom_meeting_shortcode_join_links_webinar', [ $this, 'webinar_shortcode_join_link' ] );
	}

	/**
	 * Show Webinar join links by Webinar ID
	 *
	 * @param $zoom_webinar
	 *
	 * @throws \Exception
	 */
	public function webinar_shortcode_join_link( $zoom_webinar ) {
		if ( empty( $zoom_webinar ) ) {
			echo "<p>" . __( 'Webinar is not defined. Try updating this Webinar', 'vczapi-pro' ) . "</p>";

			return;
		}

		$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_webinar->timezone ) );
		$closest_occurence = false;
		if ( ! empty( $zoom_webinar->type ) && $zoom_webinar->type === 9 && ! empty( $zoom_webinar->occurrences ) ) {
			foreach ( $zoom_webinar->occurrences as $occurrence ) {
				if ( $occurrence->status === "available" ) {
					$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_webinar->timezone ) );
					if ( $start_date >= $now ) {
						$closest_occurence = $occurrence->start_time;
						break;
					}
				}
			}
		} else if ( empty( $zoom_webinar->occurrences ) ) {
			$zoom_webinar->start_time = false;
		} else if ( ! empty( $zoom_webinar->type ) && $zoom_webinar->type === 6 ) {
			$zoom_webinar->start_time = false;
		}

		$start_time = ! empty( $closest_occurence ) ? $closest_occurence : $zoom_webinar->start_time;
		$start_time = new DateTime( $start_time, new DateTimeZone( $zoom_webinar->timezone ) );
		$start_time->setTimezone( new DateTimeZone( $zoom_webinar->timezone ) );
		if ( $now <= $start_time ) {
			unset( $GLOBALS['webinars'] );

			if ( ! empty( $zoom_webinar->password ) ) {
				$browser_join = vczapi_get_browser_join_shortcode( $zoom_webinar->id, $zoom_webinar->password, true );
			} else {
				$browser_join = vczapi_get_browser_join_shortcode( $zoom_webinar->id, false, true );
			}

			$factory       = Factory::get_instance();
			$zoom_webinars = $factory->get_posts_by_meeting_id( $zoom_webinar->id, false, 'publish' );
			//IF Zoom Webinar exists in the system then only do this else avoid
			if ( ! empty( $zoom_webinars ) && ! empty( $zoom_webinar->registration_url ) ) {
				global $current_user;
				$wp_post              = $zoom_webinars[0];
				$pro_details          = Fields::get_meta( $wp_post->ID, 'meeting_details' );
				$registration_details = Fields::get_user_meta( $current_user->ID, 'registration_details' );
				$GLOBALS['meetings']  = array(
					'zoom'         => $zoom_webinar,
					'pro'          => $pro_details,
					'registration' => $registration_details,
					'current_user' => $current_user,
					'wp_post'      => $wp_post
				);
				TemplateFunctions::get_template( 'shortcode/meeting-join-links.php', true, false );
			} else {
				$join_url            = ! empty( $zoom_webinar->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_webinar->join_url, $zoom_webinar->encrypted_password ) : $zoom_webinar->join_url;
				$GLOBALS['webinars'] = array(
					'join_uri'    => apply_filters( 'vczoom_join_webinar_via_app_shortcode', $join_url, $zoom_webinar ),
					'browser_url' => ! vczapi_check_disable_joinViaBrowser() ? apply_filters( 'vczoom_join_webinar_via_browser_disable', $browser_join ) : false
				);
				vczapi_get_template( 'shortcode/webinar-join-links.php', true, false );
			}
		}
	}
}