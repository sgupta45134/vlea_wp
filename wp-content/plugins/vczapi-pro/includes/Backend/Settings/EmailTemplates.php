<?php

namespace Codemanas\ZoomPro\Backend\Settings;

use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Helpers;

/**
 * Class EmailTemplates
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro\Backend\Settings
 */
class EmailTemplates {

	/**
	 * Create instance property
	 *
	 * @var null
	 */
	private static $_instance = null;


	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Registration Settings template
	 */
	public function output_html() {
		$this->save();
		$this->render_email_html();
	}

	/**
	 * Save Settings
	 */
	private function save() {
		// run a quick security check
		if ( isset( $_POST['vczapi_email_save_registration_email'] ) && check_admin_referer( '_vczapi_email_templates_nonce', 'vczapi_email_templates' ) ) {
			$settings                                    = Fields::get_option( 'settings' );
			$settings['emails']['confirmation_email']    = wp_kses_post( wpautop( filter_input( INPUT_POST, 'vczapi_registration_email' ) ) );
			$settings['emails']['cancellation_email']    = wp_kses_post( wpautop( filter_input( INPUT_POST, 'vczapi_registration_cancellation_email' ) ) );
			$settings['emails']['new_user_registration'] = wp_kses_post( wpautop( filter_input( INPUT_POST, 'vczapi_new_user_registration' ) ) );
			Fields::set_option( 'settings', $settings );

			Helpers::set_admin_notice( 'updated', "Email contents updated!" );
		}
	}

	/**
	 * Email Strings for Showing in backend
	 *
	 * @return mixed|void
	 */
	public static function email_handles() {
		$strings['confirmation_email'] = array(
			'customer_name'       => __( 'Show Customer Name', 'vczapi-pro' ),
			'meeting_topic'       => __( 'Show Meeting Topic', 'vczapi-pro' ),
			'meeting_time'        => __( 'Show Meeting Start Time', 'vczapi-pro' ),
			'meeting_join_link'   => __( 'Show Meeting Join Link', 'vczapi-pro' ),
			'meeting_password'    => __( 'Show Meeting Password', 'vczapi-pro' ),
			'customer_first_name' => __( 'Show Customer First Name', 'vczapi-pro' ),
			'customer_last_name'  => __( 'Show Customer Last Name', 'vczapi-pro' ),
			'meeting_id'          => __( 'Show Meeting ID', 'vczapi-pro' ),
			'meeting_timezone'    => __( 'Show Meeting Timezone', 'vczapi-pro' ),
			'meeting_duration'    => __( 'Show Meeting Duration', 'vczapi-pro' ),
			'meeting_description' => __( 'Show Meeting Description', 'vczapi-pro' ),
		);

		$strings['cancellation_email'] = array(
			'meeting_topic' => __( 'Show Meeting Topic', 'vczapi-pro' ),
			'meeting_time'  => __( 'Show Meeting Start Time', 'vczapi-pro' )
		);

		$strings['new_user_registration'] = array(
			'username'   => __( 'User Username', 'vczapi-pro' ),
			'password'   => __( 'User Password', 'vczapi-pro' ),
			'site_title' => __( 'Title of your Site', 'vczapi-pro' ),
			'site_url'   => __( 'URL of your site', 'vczapi-pro' ),
		);

		$strings['registration-reminder-email'] = array(
			'customer_name'       => __( 'Show Customer Name', 'vczapi-pro' ),
			'meeting_topic'       => __( 'Show Meeting Topic', 'vczapi-pro' ),
			'meeting_time'        => __( 'Show Meeting Start Time', 'vczapi-pro' ),
			'meeting_join_link'   => __( 'Show Meeting Join Link', 'vczapi-pro' ),
			'meeting_password'    => __( 'Show Meeting Password', 'vczapi-pro' ),
			'customer_first_name' => __( 'Show Customer First Name', 'vczapi-pro' ),
			'customer_last_name'  => __( 'Show Customer Last Name', 'vczapi-pro' ),
			'meeting_id'          => __( 'Show Meeting ID', 'vczapi-pro' ),
			'meeting_timezone'    => __( 'Show Meeting Timezone', 'vczapi-pro' ),
			'meeting_duration'    => __( 'Show Meeting Duration', 'vczapi-pro' ),
			'meeting_description' => __( 'Show Meeting Description', 'vczapi-pro' ),
		);

		return apply_filters( 'vczapi_pro_admin_registration_confirmed_email_handles', $strings );
	}

	/**
	 * Default Email Texts
	 *
	 * @return mixed
	 */
	public static function default_email_text() {
		$emails['confirmation_email']    = '<p>Hi {customer_name},</p><p>Thank you for registering for {meeting_topic}.</p><p><strong>Start Time:</strong> {meeting_time}</p><p>Join from PC, Mac, Linux, iOS or Android: <a href="{meeting_join_link}">Join</a></p><p><strong>Password:</strong> {meeting_password}</p><p><strong>Note:</strong> This link should not be shared with others; it is unique to you.</p>';
		$emails['cancellation_email']    = '<p>Hi,</p><p>Your registration for the meeting below has been canceled. You will not receive any further emails about this meeting.</p><p><strong>Topic:</strong> {meeting_topic}</p><p><strong>Time:</strong> {meeting_time}</p><p style="padding:10px;vertical-align:top;line-height:25px;background-color:#ffe8e8"><strong>The host has sent you a message:</strong><br>Your registration for {meeting_topic} Meeting has been cancelled. You will not receive any further email about this meeting.</p><p>Thanks</p>';
		$emails['new_user_registration'] = '<p>Hi,</p><p>You have been registered in {site_title}. Your user details are:</p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>Please login from: {site_url}</p><p>Thanks</p>';

		return $emails;
	}

	/**
	 * Render Email HTML FORM
	 */
	private function render_email_html() {
		$settings      = Fields::get_option( 'settings' );
		$default_text  = self::default_email_text();
		$email_strings = $this->email_handles();
		?>
        <div class="message">
			<?php
			$message = Helpers::get_admin_notice();
			if ( ! empty( $message ) ) {
				echo $message;
			}
			?>
        </div>
        <form method="POST" action="">
			<?php
			wp_nonce_field( '_vczapi_email_templates_nonce', 'vczapi_email_templates' );
			$this->render_confirmation_html( $settings, $default_text, $email_strings );
			$this->render_cancellation_html( $settings, $default_text, $email_strings );
			$this->render_new_user_html( $settings, $default_text, $email_strings );
			$this->render_email_reminders( $settings, $default_text, $email_strings );
			?>
        </form>
		<?php
	}

	public function render_email_reminders( $settings, $default_text, $email_strings ) {
		?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
					<?php _e( 'Email Reminders', 'vczapi-pro' ); ?>
                </th>
                <td>
					<?php
					echo "<p>" . __( 'Use below strings to replace dynamic text in your email.', 'vczapi-pro' ) . "</p>";
					echo "<ul>";
					foreach ( $email_strings['registration-reminder-email'] as $k => $email_string ) {
						echo "<li><strong>{" . $k . "} : </strong>" . $email_string . "</li>";
					}
					echo "</ul>";
					?>

                    <p style="color:red;"><?php _e( 'To override this email template goto', 'vczapi-pro' ); ?>&nbsp;<strong>wp-content/plugins/vczapi-pro/templates/emails/registration-reminder-email.html</strong>&nbsp;<?php _e( 'and copy to', 'vczapi-pro' ); ?>&nbsp;<strong>wp-content/themes/your-theme/video-conferencing-zoom-pro/emails/registration-reminder-email.html</strong></p>

                    <h3><?php _e( 'Manual Option', 'vczapi-pro' ); ?></h3>
                    <p><?php _e( 'This cron uses WordPress default cronjob so it might not be effective sometimes. If your reminders are not sent for some reason, you can solve this by manual approach. Download and activate', 'vczapi-pro' ); ?> <a href="https://wordpress.org/plugins/advanced-cron-manager/" target="_blank"><?php _e( 'Advacned Cron Manager', 'vczapi-pro' ); ?></a> <?php _e( 'from WordPress repository and goto wp-admin > Tools > Cron Manager - Search for "vczapi_pro_daily_cron" and click on execute now to manually execute the reminders.', 'vczapi-pro' ); ?> </p>
                </td>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Render OUTPUT for new user email section
	 *
	 * @param $settings
	 * @param $default_text
	 * @param $email_strings
	 */
	public function render_new_user_html( $settings, $default_text, $email_strings ) {
		?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
					<?php _e( 'New User Email', 'vczapi-pro' ); ?>
                </th>
                <td>
					<?php
					echo "<p>" . __( 'Use below strings to replace dynamic text in your email.', 'vczapi-pro' ) . "</p>";
					echo "<ul>";
					foreach ( $email_strings['new_user_registration'] as $k => $email_string ) {
						echo "<li><strong>{" . $k . "} : </strong>" . $email_string . "</li>";
					}
					echo "</ul>";

					$email_html = ! empty( $settings ) && ! empty( $settings['emails'] ) && ! empty( $settings['emails']['new_user_registration'] ) ? $settings['emails']['new_user_registration'] : $default_text['new_user_registration'];
					echo wp_editor( $email_html, 'vczapi_new_user_registration' );
					?>
                </td>
            <tr>
                <td></td>
                <td><input type="submit" class="button button-primary" name="vczapi_email_save_registration_email" value="Save"/></td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Confirmation email template
	 *
	 * @param $settings
	 * @param $default_text
	 * @param $email_strings
	 */
	private function render_confirmation_html( $settings, $default_text, $email_strings ) {
		?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
					<?php _e( 'Registration Confirmation', 'vczapi-pro' ); ?>
                </th>
                <td>
					<?php
					echo "<p>" . __( 'Use below strings to replace dynamic text in your email.', 'vczapi-pro' ) . "</p>";
					echo "<ul>";
					foreach ( $email_strings['confirmation_email'] as $k => $email_string ) {
						echo "<li><strong>{" . $k . "} : </strong>" . $email_string . "</li>";
					}
					echo "</ul>";

					$email_html = ! empty( $settings ) && ! empty( $settings['emails'] ) && ! empty( $settings['emails']['confirmation_email'] ) ? $settings['emails']['confirmation_email'] : $default_text['confirmation_email'];
					echo wp_editor( $email_html, 'vczapi_registration_email' );
					?>
                </td>
            <tr>
                <td></td>
                <td><input type="submit" class="button button-primary" name="vczapi_email_save_registration_email" value="Save"/></td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Render Cancellation Email Form
	 *
	 * @param $settings
	 * @param $default_text
	 * @param $email_strings
	 */
	private function render_cancellation_html( $settings, $default_text, $email_strings ) {
		?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
					<?php _e( 'Cancel Registration Status', 'vczapi-pro' ); ?>
                </th>
                <td>
					<?php
					echo "<p>" . __( 'Use below strings to replace dynamic text in your email.', 'vczapi-pro' ) . "</p>";
					if ( ! empty( $email_strings['cancellation_email'] ) ) {
						echo "<ul>";
						foreach ( $email_strings['cancellation_email'] as $k => $email_string ) {
							echo "<li><strong>{" . $k . "} : </strong>" . $email_string . "</li>";
						}
						echo "</ul>";
					}

					$email_html = ! empty( $settings ) && ! empty( $settings['emails'] ) && ! empty( $settings['emails']['cancellation_email'] ) ? $settings['emails']['cancellation_email'] : $default_text['cancellation_email'];
					echo wp_editor( $email_html, 'vczapi_registration_cancellation_email' );
					?>
                </td>
            <tr>
                <td></td>
                <td><input type="submit" class="button button-primary" name="vczapi_email_save_registration_email" value="Save"/></td>
            </tr>
            </tbody>
        </table>
		<?php
	}
}