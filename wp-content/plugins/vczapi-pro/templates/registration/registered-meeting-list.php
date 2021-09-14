<?php
/**
 * The Template for displaying all registered meetings based on USER ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/registration/registered-meeting-list.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.0.0
 */

global $zoom_meetings;

if ( ! is_user_logged_in() ) {
	return "<p>" . __( "User must be logged in to view this page", "vczapi-pro" ) . "</p>";
}

?>
<div class="vczapi-pro-datatable">
    <table id="vczapi-pro-datatable-render" class="display vczapi-pro-datatable-render">
        <thead>
        <tr>
            <th><?php _e( 'Topic', 'vczapi-pro' ); ?></th>
            <th><?php _e( 'Start Date', 'vczapi-pro' ); ?></th>
            <th><?php _e( 'Timezone', 'vczapi-pro' ); ?></th>
            <th><?php _e( 'Action', 'vczapi-pro' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $zoom_meetings as $zoom_meeting ) { ?>
            <tr>
                <td><a class="vczapi-pro-registered-post-tile" href="<?php echo get_permalink( $zoom_meeting->ID ); ?>" rel="nofollow"><?php echo $zoom_meeting->post_title; ?></a></td>
                <td data-sort="<?php echo $zoom_meeting->api->start_time; ?>"><?php echo vczapi_dateConverter( $zoom_meeting->api->start_time, $zoom_meeting->api->timezone ); ?></td>
                <td><?php echo $zoom_meeting->api->timezone; ?></td>
                <td><a href="<?php echo $zoom_meeting->registration->join_url; ?>" rel="nofollow"><?php _e( 'Join Event', 'vczapi-pro' ); ?></a></td>
            </tr>
		<?php } ?>
        </tbody>
    </table>
</div>

