<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom-pro/registration/inline-registration.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     3.0.0
 */

use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom;

if ( empty( $zoom ) && empty( $zoom->registration_url ) ) {
	return;
}

$meeting_id   = $zoom['api']->id;
$webinar_type = ! empty( $zoom['api']->type ) ? \Codemanas\ZoomPro\Helpers::is_webinar( $zoom['api']->type ) : false;

/**
 * vczoom_before_main_content hook.
 *
 * @hooked video_conference_zoom_output_content_wrapper
 */
do_action( 'vczapi_pro_before_registration_inline_form' );
?>
    <div id="vczapi-pro-inline-registration-header" class="vczapi-pro-inline-registration-header">
        <h3><?php _e( 'Registration Details', 'vczapi-pro' ); ?></h3>
    </div>
    <div id="vczapi-pro-inline-registration-container" class="vczapi-pro-inline-registration-container">
		<?php if ( ! empty( $zoom['registration_details'] ) && ! empty( $zoom['registration_details'][ $meeting_id ]->registrant_id ) ) { ?>
            <p><?php _e( 'You are already registered in this event. You can join this event from', 'vczapi-pro' ) ?>:
            <strong><a rel="noreferrer nofollow" href="<?php echo $zoom['registration_details'][ $meeting_id ]->join_url; ?>"><?php _e( 'Here', 'vczapi-pro' ) ?></a></strong>
		<?php } else { ?>
            <div class="vczapi-pro-registration-container--registration-wrap">
                <div class="vczapi-pro-registration-notice"></div>
                <form action="" method="POST" class="vczapi-pro-registration-form" id="vczapi-pro-registration-form">
					<?php wp_nonce_field( '_registration_zoom_meeting', '_nonce_registration_meeting' ) ?>
                    <input type="hidden" value="<?php echo $meeting_id; ?>" name="meeting_id">
                    <input type="hidden" value="<?php echo ! empty( $zoom['api']->post_id ) ? esc_html( $zoom['api']->post_id ) : ''; ?>" name="post_id">
                    <input type="hidden" value="<?php echo ! empty( $webinar_type ) ? 2 : 1; ?>" name="type">
                    <div class="registration-form__control">
                        <label for="first name">*<?php _e( 'First Name', 'vczapi-pro' ) ?>:</label>
                        <input type="text" name="first_name" id="first_name" autofocus placeholder="John" value="<?php echo ! empty( $zoom['current_user'] ) ? $zoom['current_user']->first_name : ''; ?>">
                    </div>
                    <div class="registration-form__control">
                        <label for="last name">*<?php _e( 'Last name', 'vczapi-pro' ) ?>:</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Doe" value="<?php echo ! empty( $zoom['current_user'] ) ? $zoom['current_user']->last_name : ''; ?>">
                    </div>
                    <div class="registration-form__control">
                        <label for="email">*<?php _e( 'E-mail', 'vczapi-pro' ) ?>:</label>
                        <input type="email" name="email_address" id="email_address" placeholder="john.doe@gmail.com" value="<?php echo ! empty( $zoom['current_user'] ) ? $zoom['current_user']->user_email : ''; ?>">
                    </div>

					<?php
					$settings                 = Fields::get_option( 'settings' );
					$show_registration_fields = [];
					if ( ! empty( $zoom['registration_individually_enabled'] ) && ! empty( $zoom['registration_fields'] ) ) {
						$show_registration_fields = $zoom['registration_fields'];
					} else if ( ! empty( $settings['meeting_registration_fields'] ) ) {
						$show_registration_fields = $settings['meeting_registration_fields'];
					}

					//IF meeting fields selector is enabled then show
					if ( $show_registration_fields ) {
						?>
                        <!-- Additonal Questions-->
						<?php if ( array_key_exists( 'address', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="address"><?php _e( 'Address', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="address" id="vczapi-pro-registration-address" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'city', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="city"><?php _e( 'City', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="city" id="vczapi-pro-registration-city" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'country', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="country"><?php _e( 'Country', 'vczapi-pro' ) ?>:</label>
                                <select name="country" id="vczapi-pro-registration-country">
									<?php
									$countries = Helpers::get_country_list();
									foreach ( $countries as $k => $country ) {
										?>
                                        <option value="<?php echo $k; ?>"><?php echo $country; ?></option>
										<?php
									}
									?>
                                </select>
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'zip', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="zip"><?php _e( 'Zip/Postal Code', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="zip" id="vczapi-pro-registration-zip" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'state', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="state"><?php _e( 'State', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="state" id="vczapi-pro-registration-state" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'phone', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="phone"><?php _e( 'Phone', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="phone" id="vczapi-pro-registration-phone" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'industry', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="industry"><?php _e( 'Industry', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="industry" id="vczapi-pro-registration-industry" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'org', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="organization"><?php _e( 'Organization', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="organization" id="vczapi-pro-registration-organization" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'job_title', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="job-title"><?php _e( 'Job Title', 'vczapi-pro' ) ?>:</label>
                                <input type="text" name="job_title" id="vczapi-pro-registration-job-title" placeholder="" value="">
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'purchasing_time_frame', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="purchasing-time-frame"><?php _e( 'Purchasing Time Frame', 'vczapi-pro' ) ?>:</label>
                                <select name="purchasing_time_frame" id="vczapi-pro-registration-purchasing-time-frame">
                                    <option name="Within a month"><?php _e( 'Within a month', 'vczapi-pro' ) ?></option>
                                    <option name="1-3 months"><?php _e( '1-3 months', 'vczapi-pro' ) ?></option>
                                    <option name="4-6 months"><?php _e( '4-6 months', 'vczapi-pro' ) ?></option>
                                    <option name="More than 6 months"><?php _e( 'More than 6 months', 'vczapi-pro' ) ?></option>
                                    <option name="No timeframe"><?php _e( 'No timeframe', 'vczapi-pro' ) ?></option>
                                </select>
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'role_in_purchase_process', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="role_in_purchase_process"><?php _e( 'Role in Purchase Process', 'vczapi-pro' ) ?>:</label>
                                <select name="role_in_purchase_process" id="vczapi-pro-registration-role-in-purchase-process">
                                    <option name="Decision Maker"><?php _e( 'Decision Maker', 'vczapi-pro' ) ?></option>
                                    <option name="Evaluator/Recommender"><?php _e( 'Evaluator/Recommender', 'vczapi-pro' ) ?></option>
                                    <option name="Influencer"><?php _e( 'Influencer', 'vczapi-pro' ) ?></option>
                                    <option name="Not involved"><?php _e( 'Not involved', 'vczapi-pro' ) ?></option>
                                </select>
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'no_of_employees', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="no-of-employees"><?php _e( 'Number of Employees', 'vczapi-pro' ) ?>:</label>
                                <select name="no_of_employees" id="vczapi-pro-registration-no-of-employees">
                                    <option name="1-20">1-20</option>
                                    <option name="21-50">21-50</option>
                                    <option name="51-100">51-100</option>
                                    <option name="101-500">101-500</option>
                                    <option name="500-1,000">500-1,000</option>
                                    <option name="1,001-5,000">1,001-5,000</option>
                                    <option name="5,001-10,000">5,001-10,000</option>
                                    <option name="More than 10,000">More than 10,000</option>
                                </select>
                            </div>
						<?php } ?>
						<?php if ( array_key_exists( 'comments', $show_registration_fields ) ) { ?>
                            <div class="registration-form__control">
                                <label for="questions_and_comments"><?php _e( 'Questions & Comments', 'vczapi-pro' ) ?>:</label>
                                <textarea name="questions_and_comments" class="vczapi-pro-registration-questions-and-comments" rows="5"></textarea>
                            </div>
						<?php } ?>
                        <!-- Additonal Questions-->
					<?php } ?>

                    <div class="registration-form__control">
                        <input type="submit" value="<?php _e( 'Register', 'vczapi-pro' ) ?>" name="registration_submit" class="btn btn-vczapi-pro-registration">
                    </div>
                </form>
            </div>
		<?php } ?>
    </div>
<?php
/**
 * vczoom_after_main_content hook.
 *
 * @hooked video_conference_zoom_output_content_end
 */
do_action( 'vczapi_pro_after_registration_inline_form' );

