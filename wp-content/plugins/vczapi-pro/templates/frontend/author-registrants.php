<?php
/**
 * The Template for frontend meeting create
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/frontend/create-meeting.php.
 *
 * We do not recommend to alter or remove the number of columns at the moment as these are controlled via javascript. Use this for changing title and change texts or adding classes or extra elements only.
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom;

if ( is_user_logged_in() ) {
	?>
    <div class="vczapi-pro-datatable vczapi-pro-frontend-author-meeting-list-table">
        <table id="vczapi-pro-frontend-author-meeting-list-table" class="display">
            <thead>
            <tr>
                <th><?php _e( 'Title', 'vczapi-pro' ); ?></th>
                <th><?php _e( 'Start Date', 'vczapi-pro' ); ?></th>
                <th><?php _e( 'Meeting ID', 'vczapi-pro' ); ?></th>
                <th><?php _e( 'Type', 'vczapi-pro' ); ?></th>
                <th><?php _e( 'Action', 'vczapi-pro' ); ?></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
<?php } else {
	echo '<p class="vczapi-pro-error-loggedin">' . __( 'User needs to be logged in to view this page.', 'vczapi-pro' ) . '</p>';
}
?>