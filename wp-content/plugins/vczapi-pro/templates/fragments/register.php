<?php
/**
 * The template for displaying meeting join and start links
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/fragments/join-links.php.
 *
 * @author     Deepen.
 * @created_on 11/19/19
 */

global $zoom;
if ( ! empty( $zoom ) ) {
	$meeting_id = $zoom['api']->id;
	//by default keep it on site
	$registration_link = ( $zoom['register_on_zoom'] == 'on' ) ? $zoom["api"]->registration_url : $zoom['registration_link'];
	?>
    <div class="dpn-zvc-sidebar-box">
        <div class="register-links">
			<?php if ( ! empty( $zoom['registration_details'] ) && ! empty( $zoom['registration_details'][ $meeting_id ] ) && ! empty( $zoom['registration_details'][ $meeting_id ]->registrant_id ) ) { ?>
                <a href="<?php echo esc_url( $zoom['registration_details'][ $meeting_id ]->join_url ); ?>" target="_blank" class="btn btn-register-btn-link"><?php _e( 'Join Meeting', 'vczapi-pro' ); ?></a>
				<?php if ( ! empty( $zoom['api']->start_url ) && vczapi_check_author( $post_id ) ) { ?>
                    <a target="_blank" href="<?php echo esc_url( $zoom['api']->start_url ); ?>" class="btn btn-start-link"><?php _e( 'Start Meeting', 'vczapi-pro' ); ?></a>
				<?php }
			} else {
				if ( ! is_user_logged_in() ) {
					?>
                    <p><?php _e( 'Already registered ? Please check your email for the join links.', 'vczapi-pro' ); ?></p>
				<?php } ?>
                <a rel="nofollow"
                   href="<?php echo esc_url( $registration_link ); ?>"
					<?php echo ( $zoom['register_on_zoom'] == 'on' ) ? 'target="_blank"' : ''; ?>
                   class="btn btn-register-btn-link"><?php _e( 'Register Now', 'vczapi-pro' ); ?></a>
			<?php } ?>
        </div>
    </div>
	<?php
}