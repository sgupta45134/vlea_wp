<?php
/**
 * The template for displaying meeting countdown timer
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/fragments/countdown-timer.php.
 *
 * @author Deepen.
 * @created April 17, 2020
 * @modified 1.0.0
 * @copyright CodeManas
 */

global $zoom;

if ( ! empty( $zoom['start_date'] ) ) {
	?>
    <div class="dpn-zvc-sidebar-box">
        <div class="dpn-zvc-timer" id="dpn-zvc-timer" data-date="<?php echo $zoom['start_date']; ?>" data-state="<?php echo ! empty( $zoom['api']->state ) ? $zoom['api']->state : false; ?>" data-tz="<?php echo $zoom['api']->timezone; ?>">
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-days"></div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'days', 'vczapi-pro' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-hours"></div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'hours', 'vczapi-pro' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-minutes"></div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'minutes', 'vczapi-pro' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-seconds"></div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'seconds', 'vczapi-pro' ); ?></div>
            </div>
        </div>
    </div>
	<?php
}