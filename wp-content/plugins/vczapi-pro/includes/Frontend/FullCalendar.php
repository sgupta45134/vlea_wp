<?php

namespace Codemanas\ZoomPro\Frontend;

use Codemanas\ZoomPro\Helpers;
use Codemanas\ZoomPro\TemplateFunctions;

/**
 * Class FullCalendar
 *
 * @package Codemanas\ZoomPro\Frontend
 */
class FullCalendar {
	/**
	 * @var int
	 */
	public static $count = 1;

	/**
	 * @var null
	 */
	public static $_instance = null;

	/**
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * @return FullCalendar|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * FullCalendar constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_nopriv_vczapi_get_calendar_meetings', [ $this, 'get_meetings' ] );
		add_action( 'wp_ajax_vczapi_get_calendar_meetings', [ $this, 'get_meetings' ] );

		add_filter( 'vczapi-pro-calendar-event-description', [ $this, 'restrict_content_length' ] );
		add_filter( 'vczapi-pro-calendar-event-description', 'wpautop' );
	}

	/**
	 * Restrict Content Length
	 *
	 * @param $content
	 *
	 * @return bool|string
	 */
	public function restrict_content_length( $content ) {
		if ( strlen( $content ) > 150 ) {
			$content = substr( $content, 0, 150 );
			$content = $content . '...';
		}

		return $content;
	}

	/**
	 * Generate Calendar
	 *
	 * @param $args
	 *
	 * @return false|string
	 */
	public function generate_calendar( $args ) {
		$min = ( SCRIPT_DEBUG == true ) ? '' : '.min';
		$ver = VZAPI_ZOOM_PRO_ADDON_PLUGIN_VERSION;
		wp_register_style( 'vczapi-pro-fullcalendar', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/fullcalendar/main' . $min . '.css', $ver );
		wp_register_script( 'vczapi-pro-fullcalendar-js', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/fullcalendar/main' . $min . '.js', '', $ver, true );
		wp_register_script( 'vczapi-pro-fullcalendar-js-locales', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/fullcalendar/locales-all' . $min . '.js', '', $ver, true );

		wp_register_style( 'vczapi-pro-tippy-light-theme', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/tippy/themes/light.css' );
		wp_register_script( 'vczapi-pro-popper-js', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/popper/popper.min.js' );
		wp_register_script( 'vczapi-pro-tippy-js', VZAPI_ZOOM_PRO_ADDON_DIR_URI . 'assets/vendors/tippy/dist/tippy-bundle.umd.js' );

		wp_enqueue_style( 'vczapi-pro-fullcalendar' );
		wp_enqueue_script( 'vczapi-pro-fullcalendar-js' );
		wp_enqueue_script( 'vczapi-pro-fullcalendar-js-locales' );

		wp_enqueue_script( 'vczapi-pro-popper-js' );
		wp_enqueue_script( 'vczapi-pro-tippy-js' );
		wp_enqueue_style( 'vczapi-pro-tippy-light-theme' );
		wp_enqueue_script( 'vczapi-pro' );

		$args = shortcode_atts( [
			'class'                 => 'vczapi-pro-calendar',
			'author'                => '',
			'show'                  => '',
			'calendar_default_view' => 'dayGridMonth',//options => dayGridMonth,timeGridWeek,timeGridDay,listWeek
			'show_calendar_views'   => 'no'
		],
			$args );
		ob_start();
		?>
            <div id="vczapi-pro-calendar-container-<?php echo self::$count; ?>" class="vczapi-pro-calendar-container">
                <div id="vczapi-pro-calendar-<?php echo self::$count; ?>" class="vczapi-pro-calendar" data-author="<?php echo $args['author']; ?>" data-show="<?php echo $args['show']; ?>" data-calendar_default_view="<?php echo $args['calendar_default_view']; ?>" data-show_calendar_views="<?php echo $args['show_calendar_views']; ?>"></div>
	            <?php
	            TemplateFunctions::get_template( 'calendar/popover-template.php', true, false );
	            ?>
            </div>
        

		<?php
		self::$count ++;

		return ob_get_clean();
	}

	/**
	 * Get Meetings
	 *
	 * @throws \Exception
	 */
	public function get_meetings() {
		$start_date_time  = filter_input( INPUT_GET, 'start' );
		$end_date_time    = filter_input( INPUT_GET, 'end' );
		$author_id        = filter_input( INPUT_GET, 'author' );
		$show             = filter_input( INPUT_GET, 'show' );
		$calendarTimezone = filter_input( INPUT_GET, 'timezone' );

		$start_date_time  = new \DateTime( $start_date_time . ' -6 months' );
		$end_date_time    = new \DateTime( $end_date_time );
		$utcTimezone      = new \DateTimeZone( 'UTC' );
		$startDateTimeUTC = $start_date_time->setTimezone( $utcTimezone );
		$endDateTimeUTC   = $end_date_time->setTimezone( $utcTimezone );

		$args = [
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'meta_query'     => [
				[
					'key'     => '_meeting_field_start_date_utc',
					'value'   => [ $startDateTimeUTC->format( 'Y-m-d H:i:s' ), $endDateTimeUTC->format( 'Y-m-d H:i:s' ) ],
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME'
				],
			]
		];

		if ( ! empty( $author_id ) ) {
			$args['author'] = $author_id;
		}

		if ( ! empty( $show ) ) {
			$args['meta_query'][] = [
				'key'   => '_vczapi_meeting_type',
				'value' => $show
			];
		}

		$response = [];
		$meetings = new \WP_Query( $args );
		//echo $meetings->request;
		if ( $meetings->have_posts() ) {
			while ( $meetings->have_posts() ): $meetings->the_post();
				$meeting_post_id = get_the_ID();
				$meeting_details = get_post_meta( $meeting_post_id, '_meeting_zoom_details', true );
				$featuredImage   = apply_filters( 'vczapi-pro-calendar-featured-image', ( has_post_thumbnail() ) ? get_the_post_thumbnail( $meeting_post_id, 'medium' ) : '', $meeting_post_id );

				if ( ! empty( $meeting_details ) && is_object( $meeting_details ) ) {
					$event_description = apply_filters( 'vczapi-pro-calendar-event-description', strip_tags( strip_shortcodes( get_the_content() ) ) );

					if ( ! empty( $meeting_details->occurrences ) && is_array( $meeting_details->occurrences ) ) {
						foreach ( $meeting_details->occurrences as $occurrence ) {
							$response[] = [
								'title'         => html_entity_decode( get_the_title() ),
								'start'         => ! empty( $occurrence->start_time ) ? $occurrence->start_time : '',
								'allDay'        => false,
								'id'            => get_the_ID(),
								'extendedProps' => apply_filters( 'vczapi-pro-calendar-extended-props', [
									'image_html'       => $featuredImage,
									'eventDescription' => $event_description,
									'meetingLink'      => apply_filters( 'vczapi-pro-calendar-meeting-link', '<a href="' . get_the_permalink() . '">' . __( 'See More', 'vczapi-pro' ) . '</a>', $meeting_post_id ),
									'meetingDate'      => ! empty( $occurrence->start_time ) ? vczapi_dateConverter( $occurrence->start_time, $calendarTimezone, apply_filters( 'vczapi-pro-calendar-date-format', 'F j, Y @ g:i a' ), false ) : '',
									'meetingTimezone'  => $meeting_details->timezone
								], $meeting_post_id )
							];
						}
					} else {
						$response[] = [
							'title'         => html_entity_decode( get_the_title() ),
							'start'         => ! empty( $meeting_details->start_time ) ? $meeting_details->start_time : '',
							'allDay'        => false,
							'id'            => get_the_ID(),
							'extendedProps' => apply_filters( 'vczapi-pro-calendar-extended-props', [
								'image_html'       => $featuredImage,
								'eventDescription' => $event_description,
								'meetingLink'      => apply_filters( 'vczapi-pro-calendar-meeting-link', '<a href="' . get_the_permalink() . '">' . __( 'See More', 'vczapi-pro' ) . '</a>', $meeting_post_id ),
								'meetingDate'      => ! empty( $meeting_details->start_time ) ? vczapi_dateConverter( $meeting_details->start_time, $calendarTimezone, apply_filters( 'vczapi-pro-calendar-date-format', 'F j, Y @ g:i a' ), false ) : '',
								'meetingTimezone'  => $meeting_details->timezone
							], $meeting_post_id )
						];
					}
				}
			endwhile;
			wp_reset_postdata();
		}

		wp_send_json( $response );
	}
}