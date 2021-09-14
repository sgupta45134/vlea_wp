<?php
/**
 * The template for displaying meeting details of zoom
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/fragments/meeting-details.php.
 *
 * @author    Deepen.
 * @created   April 17, 2020
 * @modified  1.0.0
 * @copyright CodeManas
 */

use Codemanas\ZoomPro\Helpers;

global $zoom;
?>
<div class="dpn-zvc-sidebar-box">
    <div class="dpn-zvc-sidebar-tile">
        <h3><?php _e( 'Details', 'vczapi-pro' ); ?></h3>
    </div>
    <div class="dpn-zvc-sidebar-content">

		<?php do_action( 'vczapi_html_before_meeting_details' ); ?>

        <div class="dpn-zvc-sidebar-content-list">
            <span><strong><?php _e( 'Topic', 'vczapi-pro' ); ?>:</strong></span> <span><?php the_title(); ?></span>
        </div>
        <div class="dpn-zvc-sidebar-content-list">
            <span><strong><?php _e( 'Type', 'vczapi-pro' ); ?>:</strong></span> <span><?php _e( 'Recurring', 'vczapi-pro' ); ?></span>
        </div>
        <div class="dpn-zvc-sidebar-content-list">
            <span><strong><?php _e( 'Hosted By', 'vczapi-pro' ); ?>:</strong></span>
            <span><?php echo ! empty( $zoom['user'] ) && ! empty( $zoom['user']->first_name ) ? $zoom['user']->first_name . ' ' . $zoom['user']->last_name : get_the_author(); ?></span>
        </div>
		<?php
		if ( $zoom['start_date'] ) {
			$compare = Helpers::date_compare( $zoom['start_date'], 'now', $zoom['timezone'], '>' );
			if ( ! empty( $zoom['start_date'] ) && $compare ) { ?>
                <div class="dpn-zvc-sidebar-content-list">
                    <span><strong><?php _e( 'Next Occurence', 'vczapi-pro' ); ?>:</strong></span>
                    <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom['start_date'], $zoom['timezone'], 'F j, Y @ g:i a' ); ?></span>
                </div>
			<?php } else if ( ! empty( $zoom['api']->type ) && $zoom['api']->type === 3 || $zoom['api']->type === 6 ) { ?>
                <div class="dpn-zvc-sidebar-content-list">
                    <span><strong><?php _e( 'Occurence', 'vczapi-pro' ); ?>:</strong></span>
                    <span class="sidebar-start-time"><?php _e( 'Click below join link to join this meeting.', 'vczapi-pro' ); ?></span>
                </div>
			<?php } else { ?>
                <div class="dpn-zvc-sidebar-content-list">
                    <span><strong><?php _e( 'In Progress Occurence', 'vczapi-pro' ); ?>:</strong></span>
                    <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom['start_date'], $zoom['timezone'], 'F j, Y @ g:i a' ); ?></span>
                </div>
				<?php
			}
		}

		if ( isset( $zoom['api']->occurrences ) & ! empty( $zoom['api']->occurrences ) ):
			?>
            <div class="dpn-zvc-sidebar-content-list zvc-all-occurrences">
                <a href="javascript:void(0);" class="zvc-all-occurrences__toggle-button"><?php _e( 'Click to See All Meeting Occurrences', 'vczapi-pro' ); ?></a>
                <div class="zvc-all-occurrences__list">
                    <ul>
						<?php foreach ( $zoom['api']->occurrences as $occurrence ): ?>
                            <li><?php echo vczapi_dateConverter( $occurrence->start_time, $zoom['timezone'], 'F j, Y @ g:i a' ); ?></li>
						<?php endforeach; ?>
                    </ul>
                </div>
            </div>
		<?php
		endif;
		?>
		<?php if ( ! empty( $zoom['terms'] ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Category', 'vczapi-pro' ); ?>:</strong></span>
                <span class="sidebar-category"><?php echo implode( ', ', $zoom['terms'] ); ?></span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['api']->duration ) ) {
			$duration = vczapi_convertMinutesToHM( $zoom['api']->duration, false );
			?>
            <div class="dpn-zvc-sidebar-content-list vczapi-duration-wrap">
                <span><strong><?php _e( 'Duration', 'vczapi-pro' ); ?>:</strong></span>
                <span>
                    <?php
                    if ( ! empty( $duration['hr'] ) ) {
	                    echo _n( $duration['hr'] . ' hour', $duration['hr'] . ' hours', absint( $duration['hr'] ), 'vczapi-pro' ) . ' ' . _n( $duration['min'] . ' minute', $duration['min'] . ' minutes', absint( $duration['min'] ), 'vczapi-pro' );
                    } else {
	                    echo _n( $duration['min'] . ' minute', $duration['min'] . ' minutes', absint( $duration['min'] ), 'vczapi-pro' );
                    }
                    ?>
                </span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['timezone'] ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Timezone', 'vczapi-pro' ); ?>:</strong></span> <span><?php echo $zoom['timezone']; ?></span>
            </div>
		<?php } ?>

		<?php do_action( 'vczapi_html_after_meeting_details' ); ?>

        <p class="dpn-zvc-display-or-hide-localtimezone-notice"><?php printf( __( '%sNote%s: Countdown time is shown based on your local timezone. Meeting time has 1 hour threshold before another occurrence is shown.', 'vczapi-pro' ), '<strong>', '</strong>' ); ?></p>
    </div>
</div>