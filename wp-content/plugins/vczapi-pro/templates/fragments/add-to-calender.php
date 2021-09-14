<?php
/**
 * The template for showing add to calender ICS link
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/fragments/add-to-calender.php.
 *
 * @author Deepen.
 * @created November 25, 2020
 * @modified 1.0.0
 * @copyright CodeManas
 */

global $zoom;

//If you need to show this to only loggedin users then just remove "#" from below line
#if ( is_user_logged_in() ) {
	?>
    <div class="dpn-zvc-sidebar-content-list vczapi-pro-ical-links">
		<?php if ( ! empty( $zoom['export_gcal_link'] ) ) { ?>
            <a class="vczapi-pro-gcal-integration-link" href="<?php echo esc_url( $zoom['export_gcal_link'] ); ?>"><?php _e( 'Google Calendar', 'vczapi-pro' ); ?></a> /
		<?php } ?>
		<?php if ( $zoom['export_cal_link'] ) { ?>
            <a class="vczapi-pro-ical-integration-link" href="<?php echo esc_url( $zoom['export_cal_link'] ); ?>"><?php _e( 'iCal Export', 'vczapi-pro' ); ?></a>
		<?php } ?>
    </div>
	<?php
//If you need to show this to only loggedin users then just remove "#" from below line
#}
?>