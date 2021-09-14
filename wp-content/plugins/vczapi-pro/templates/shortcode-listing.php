<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/shortcode-listing.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     1.3.0
 * @updated     1.4.0
 */

use Codemanas\ZoomPro\TemplateFunctions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom_meetings;

if ( ! is_object( $zoom_meetings ) && ! ( $zoom_meetings instanceof \WP_Query ) ) {
	return;
}
?>
<div class="vczapi-list-zoom-meetings">

	<?php
	/**
	 * BEFORE LOOP HOOK
	 */
	do_action( 'vczapi_before_shortcode_content_post_loop', $zoom_meetings );
	?>

    <div class="vczapi-wrap">
		<?php
		if ( $zoom_meetings->have_posts() ) {
			while ( $zoom_meetings->have_posts() ) {
				$zoom_meetings->the_post();

				do_action( 'vczapi_main_content_post_loop' );

				TemplateFunctions::get_template_part( 'frontend/list', 'meetings' );
			}
		} else {
			echo "<p class='vczapi-no-meeting-found'>" . __( 'No Meetings found.', 'vczapi-pro' ) . "</p>";
		}

		wp_reset_postdata();
		?>
    </div>

	<?php
	/**
	 * AFTER LOOP HOOK
	 */
	do_action( 'vczapi_after_shortcode_content_post_loop' );
	?>

    <div class="vczapi-list-zoom-meetings--pagination">
		<?php \Codemanas\VczApi\Shortcodes\Helpers::pagination( $zoom_meetings ); ?>
    </div>

	<?php do_action( 'vczapi_after_main_content_post_loop_pagination' ); ?>
</div>