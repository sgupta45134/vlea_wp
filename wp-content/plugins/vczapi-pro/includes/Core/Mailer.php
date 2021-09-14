<?php

namespace Codemanas\ZoomPro\Core;

use Codemanas\ZoomPro\Backend\Settings\EmailTemplates;
use Codemanas\ZoomPro\TemplateFunctions;

/**
 * Class Emails
 *
 * Email Function
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Mailer {

	private static $dir = 'vczapi-pro';

	/**
	 * Prepare the email and Send
	 *
	 * @param $mail
	 * @param bool $rep_with
	 * @param bool $ics
	 * @param string $template_name
	 * @param bool $file_template
	 */
	public static function send_email( $mail, $rep_with = false, $ics = false, $template_name = '', $file_template = false ) {
		$site_title = get_bloginfo( 'name' );
		$site_email = ! empty( $mail['sent_from'] ) ? $mail['sent_from'] : get_bloginfo( 'admin_email' );
		$site_email = apply_filters( 'vczapi_pro_send_email_site_email', $site_email ); // IF you want to change this domain should be same as the site for example: if you site is example.com then email here should be someone@example.com

		//Ready for email
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";
//		$headers[] = 'Reply-To: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

		/*******
		 * **********
		 * ********** Send Email to a Statically Generated HTML template
		 * **********
		 */
		$needles = array();
		if ( ! empty( $template_name ) && $file_template ) {
			$email_template = file_get_contents( TemplateFunctions::get_template( 'emails/' . $template_name . '.html' ) );
		} else {
			$settings               = Fields::get_option( 'settings' );
			$default_email_template = EmailTemplates::default_email_text();
			$db_email_template      = ! empty( $settings ) && ! empty( $settings['emails'] ) && ! empty( $settings['emails'][ $template_name ] ) ? $settings['emails'][ $template_name ] : false;
			$email_template         = ! empty( $db_email_template ) ? $db_email_template : $default_email_template[ $template_name ];
		}

		//Replace Dynamic variables
		$email_handles = EmailTemplates::email_handles();
		if ( ! empty( $email_handles ) ) {
			foreach ( $email_handles[ $template_name ] as $k => $email_handle ) {
				$needles[] = '{' . $k . '}';
			}
		}

		//Search Dyanmic Strings and Replace Here
		$content = str_replace( $needles, $rep_with, $email_template );
		unset( $needles );

		// WPML Integration Starts
		// https://wpml.org/documentation/support/sending-emails-with-wpml/
		// Referred @WARINC352
		if ( function_exists( 'icl_object_id' ) ) {
			do_action( 'wpml_switch_language_for_email', $site_email );
		}

		//IF ICS IS available attach ICS
		if ( $ics ) {
			$attachment = self::attach_ics( $ics );
			wp_mail( $mail['email_to'], $mail['subject'], $content, $headers, $attachment['attachment'] );
			//Clean up
			wp_delete_file( $attachment['path'] );
		} else {
			wp_mail( $mail['email_to'], $mail['subject'], $content, $headers );
		}

		// WPML Integration Ends
		if ( function_exists( 'icl_object_id' ) ) {
			do_action( 'wpml_reset_language_after_mailing' );
		}
	}

	/**
	 * Generate ICS file
	 *
	 * @param $ics
	 * @param $root
	 * @param $file_name
	 *
	 * @return array|bool
	 */
	public static function attach_ics( $ics, $root = false, $file_name = false ) {
		$attach_ics     = new ICS( $ics );
		$uploads_folder = wp_get_upload_dir();
		$upload_dir     = ! empty( $root ) ? $uploads_folder['basedir'] : $uploads_folder['basedir'] . '/' . self::$dir;
		$upload_url     = ! empty( $root ) ? $uploads_folder['baseurl'] : $uploads_folder['baseurl'] . '/' . self::$dir;
		$file_name      = ! empty( $file_name ) ? $file_name . '.ics' : 'meeting.ics';
		$file_path      = $upload_dir . '/' . $file_name;
		$file_url       = $upload_url . '/' . $file_name;

		//Create our directory if it does not exist
		if ( ! file_exists( $upload_dir ) ) {
			mkdir( $upload_dir );
		}

		if ( ! $root ) {
			file_put_contents( $upload_dir . '/.htaccess', 'deny from all' );
			file_put_contents( $upload_dir . '/index.html', '' );
		}

		file_put_contents( $file_path, $attach_ics->to_string() );
		if ( file_exists( $file_path ) ) {
			$attachments = array( $file_path );
		}

		$attachments = ! empty( $attachments ) ? array( 'attachment' => $attachments, 'path' => $file_path, 'url' => $file_url ) : false;

		return $attachments;
	}

}