<?php

namespace Codemanas\ZoomPro;

use Codemanas\ZoomPro\Core\Mailer;

/**
 * Class User
 *
 * Functions related to WP User
 *
 * @author  Deepen Bajracharya, CodeManas, 2021. All Rights reserved.
 * @since   1.3.3
 * @package Codemanas\ZoomPro
 */
class User {

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		add_filter( 'vczapi_pro_trigger_before_user_registered', [ $this, 'create_wp_user' ], 10, 2 );
	}

	/**
	 * Create WP User if not exists on registration form submit
	 *
	 * @param $user_id
	 * @param $user_details
	 *
	 * @return bool|int|\WP_Error
	 */
	public function create_wp_user( $user_id, $user_details ) {
		if ( empty( $user_details ) ) {
			return false;
		}

		$email      = ! empty( $user_details['email'] ) ? $user_details['email'] : false;
		$first_name = ! empty( $user_details['first_name'] ) ? $user_details['first_name'] : false;
		$last_name  = ! empty( $user_details['last_name'] ) ? $user_details['last_name'] : false;

		//IF either of the three does not satisfy then abort the process
		if ( ! $email || ! $first_name || ! $last_name ) {
			return false;
		}

		if ( ! email_exists( $email ) ) {
			$user_data = [
				'user_login' => $email,
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => 'subscriber',
				'user_pass'  => wp_generate_password()
			];
			$user_data = apply_filters( 'vczapi_pro_on_wp_user_create', $user_data );
			if ( empty( $user_data ) ) {
				return false;
			}

			$user_id = wp_insert_user( $user_data );
			if ( ! empty( $user_id ) ) {
				$email_details = [
					'email_to' => $email,
					'subject'  => '[' . get_bloginfo( 'name' ) . '] ' . __( 'Login Details', 'vczapi-pro' )
				];

				$data = array(
					'username'  => $email,
					'password'  => $user_data['user_pass'],
					'site_name' => get_bloginfo( 'name' ),
					'site_url'  => home_url( '/' )
				);
				Mailer::send_email( $email_details, $data, false, 'new_user_registration' );
			}
		}

		return $user_id;
	}
}