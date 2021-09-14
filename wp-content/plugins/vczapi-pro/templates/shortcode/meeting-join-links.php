<?php
/**
 * The Template for displaying join links for meeting by ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/shortcode/meeting-join-links.php.
 *
 * @package    Video Conferencing with Zoom API PRO/Templates
 * @version    1.4.0
 */

use Codemanas\ZoomPro\Helpers;

global $meetings;

$meeting_id           = ! empty( $meetings ) && $meetings['zoom'] ? $meetings['zoom']->id : false;
$pro_details          = ! empty( $meetings ) && $meetings['pro'] ? $meetings['pro'] : false;
$registration_details = ! empty( $meetings ) && $meetings['registration'] ? $meetings['registration'] : false;
$current_user         = ! empty( $meetings ) && $meetings['current_user'] ? $meetings['current_user'] : false;
$wp_post              = ! empty( $meetings ) && $meetings['wp_post'] ? $meetings['wp_post'] : false;
if ( ! empty( $meeting_id ) && ! empty( $pro_details ) && ! empty( $pro_details['registration'] ) && ! empty( $registration_details[ $meeting_id ] ) && ! empty( $registration_details[ $meeting_id ]->join_url ) ) {
	?>
    <tr>
        <td><?php _e( 'Join via Zoom App', 'vczapi-pro' ); ?></td>
        <td>
            <a href="<?php echo $registration_details[ $meeting_id ]->join_url; ?>" class="btn-join-register-shortcode-link" title="<?php _e( 'Join Meeting', 'vczapi-pro' ); ?>"><?php _e( 'Join Meeting', 'vczapi-pro' ); ?></a>
        </td>
    </tr>
	<?php
} else {
	if ( ! empty( $pro_details ) && ! empty( $pro_details['registration'] ) ) {
		$registration_link = Helpers::get_url_query( array( 'register' => $current_user->ID, 'from' => urlencode( Helpers::get_current_page_uri() ) ), $wp_post->ID );
		?>
        <tr>
            <td><?php _e( 'Join Meeting', 'vczapi-pro' ); ?></td>
            <td>
                <a href="<?php echo $registration_link; ?>" class="btn-join-register-shortcode-link" title="<?php _e( 'Register Now', 'vczapi-pro' ); ?>"><?php _e( 'Register Now', 'vczapi-pro' ); ?></a>
            </td>
        </tr>
	<?php }
}