<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/frontend/list-meetings.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.2.2
 * @updated     3.6.0
 */

use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;

global $zoom;
global $current_user;

$post_id = get_the_id();
do_action( 'vczapi_before_loop_zoom_listing_shortcode' );

$columns = ! empty( $zoom['columns'] ) ? $zoom['columns'] : 'vczapi-col-4';
?>
    <div class="<?php echo $columns; ?> vczapi-pb-3">
        <div class="vczapi-list-zoom-meetings--item">
			<?php if ( has_post_thumbnail() ) { ?>
                <div class="vczapi-list-zoom-meetings--item__image">
					<?php the_post_thumbnail(); ?>
                </div><!--Image End-->
			<?php } ?>
            <div class="vczapi-list-zoom-meetings--item__details">
                <h3><a href="<?php echo esc_url( get_the_permalink() ) ?>" class="vczapi-list-zoom-title-link"><?php the_title(); ?></a></h3>
                <div class="vczapi-list-zoom-meetings--item__details__meta">
                    <div class="hosted-by meta">
                        <strong><?php _e( 'Hosted By:', 'vczapi-pro' ); ?></strong>
                        <span><?php echo apply_filters( 'vczapi_host_name', $zoom['host_name'] ); ?></span>
                    </div>
					<?php
					if ( ! empty( $zoom['api']->type ) && vczapi_pro_check_type( $zoom['api']->type ) ) {
						$type      = ! empty( $zoom['api']->type ) ? $zoom['api']->type : false;
						$timezone  = ! empty( $zoom['api']->timezone ) ? $zoom['api']->timezone : false;
						$occurence = ! empty( $zoom['api']->occurrences ) ? $zoom['api']->occurrences : false;
						if ( ! empty( $occurence ) ) {
							$start_time = Helpers::get_latest_occurence_by_type( $type, $timezone, $occurence );
							?>
                            <div class="start-date meta">
                                <strong><?php _e( 'Next Occurrence', 'vczapi-pro' ); ?>:</strong>
                                <span><?php echo vczapi_dateConverter( $start_time, $timezone, 'F j, Y @ g:i a' ); ?></span>
                            </div>
							<?php
						} else {
							?>
                            <div class="start-date meta">
                                <strong><?php _e( 'Start Time', 'vczapi-pro' ); ?>:</strong>
                                <span><?php echo vczapi_dateConverter( $zoom['start_date'], 'UTC', 'F j, Y @ g:i a' ); ?></span>
                            </div>
							<?php
						}
						?>
                        <div class="start-date meta">
                            <strong><?php _e( 'Type', 'vczapi-pro' ); ?>:</strong>
                            <span><?php _e( 'Recurring', 'vczapi-pro' ); ?></span>
                        </div>
						<?php
					} else {
						?>
                        <div class="start-date meta">
                            <strong><?php _e( 'Start', 'vczapi-pro' ); ?>:</strong>
                            <span><?php echo vczapi_dateConverter( $zoom['api']->start_time, $zoom['api']->timezone, 'F j, Y @ g:i a' ); ?></span>
                        </div>
					<?php } ?>
                    <div class="timezone meta">
                        <strong><?php _e( 'Timezone', 'vczapi-pro' ); ?>:</strong> <span><?php echo $zoom['api']->timezone; ?></span>
                    </div>

					<?php do_action( 'vczapi_additional_content_inside_zoom_listing_shortcode' ); ?>
                </div>
                <div class="vczapi-pro-join-buttons">
					<?php
					$pro_details          = Fields::get_meta( $post_id, 'meeting_details' );
					$registration_details = Fields::get_user_meta( $current_user->ID, 'registration_details' );
					$meeting_id           = ! empty( $zoom['api']->id ) ? $zoom['api']->id : false;
					if ( ! empty( $meeting_id ) && ! empty( $pro_details ) && ! empty( $pro_details['registration'] ) && ! empty( $registration_details[ $meeting_id ] ) && ! empty( $registration_details[ $meeting_id ]->join_url ) ) {
						?>
                        <a href="<?php echo $registration_details[ $meeting_id ]->join_url; ?>" class="btn vczapi-pro-btn vczapi-btn-link vczapi-pro-register-view-btn-link"><?php _e( 'Join Meeting', 'vczapi-pro' ); ?></a>
                        <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="btn vczapi-pro-btn vczapi-btn-link vczapi-pro-register-view-btn-link"><?php _e( 'View', 'vczapi-pro' ); ?></a>
						<?php
					} else {
						if ( ! empty( $pro_details ) && ! empty( $pro_details['registration'] ) ) {
							$registration_link = Helpers::get_url_query( array( 'register' => get_current_user_id(), 'from' => urlencode( Helpers::get_current_page_uri() ) ), $post_id );
							$registration_link = ( $pro_details['register_on_zoom'] == 'on' ) ? $zoom["api"]->registration_url : $registration_link;
							?>
                            <a href="<?php echo esc_url( $registration_link ); ?>"
                               class="btn vczapi-pro-btn vczapi-pro-register-btn-link"
								<?php echo ( $pro_details['register_on_zoom'] == 'on' ) ? 'target="_blank"' : ''; ?>
                            ><?php _e( 'Register Now', 'vczapi-pro' ); ?></a>
                            <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="btn vczapi-pro-btn vczapi-btn-link vczapi-pro-register-view-btn-link"><?php _e( 'View', 'vczapi-pro' ); ?></a>
						<?php } else { ?>
                            <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="btn vczapi-pro-btn vczapi-btn-link vczapi-pro-register-view-btn-link"><?php _e( 'View', 'vczapi-pro' ); ?></a>
							<?php
						}
					}
					?>
                </div>
            </div><!--Details end-->
        </div><!--List item end-->
    </div>

<?php do_action( 'vczapi_after_loop_zoom_listing_shortcode' ); ?>