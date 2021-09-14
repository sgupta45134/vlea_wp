<?php
/**
 * Functions for approval user registrations
 * @since 1.7.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create pending and rejected user roles
 */
function wcmo_create_new_roles() {

	if( wcmo_get_user_approval() != 'yes' ) {
		return;
	}

	$customer_capabilities = get_role( 'customer' )->capabilities;

	// Pending role
	add_role(
		'pending',
		__( 'Pending', 'wcmo'),
		$customer_capabilities
	);

	// Rejected
	add_role(
		'rejected',
		__( 'Rejected', 'wcmo'),
		array()
	);

}
add_action( 'init', 'wcmo_create_new_roles' );

/**
 * Send an email to the admin informing them of a new registration to review
 * @param $user_id
 * @param $role
 */
function wcmo_new_registration_email( $user_id ) {

	if( wcmo_get_user_approval() != 'yes' ) {
		return;
	}

	// add_filter( 'woocommerce_email_footer_text', 'wcmo_email_footer_text' );

	$to = apply_filters( 'wcmo_new_registration_email_recipient', get_bloginfo( 'admin_email' ) );

	$subject = apply_filters(
		'wcmo_new_registration_email_subject',
		__( 'New user registration', 'wcmo' )
	);

	ob_start();

	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) );

	$content = ob_get_clean();

	$url = admin_url( 'user-edit.php' );
	//
	$url = add_query_arg(
		array(
			'user_id'	=> absint( $user_id )
		),
		$url
	);

	// $url = wp_nonce_url(
	// 	$url,
	// 	'wcmo_approval_nonce',
	// 	'wcmo_approval_nonce'
	// );

	$message = sprintf(
		'<p>%s<p>',
		sprintf(
			__( 'A new user has registered on %s. Please follow <a href="%s">this link to review their registration</a>.', 'wcmo' ),
			get_bloginfo( 'name' ),
			esc_url( $url )
		)
	);

	$user = get_user_by( 'id', $user_id );

	// Add some fields from the registration form
	$enabled_fields = wcmo_get_enabled_registration_fields();
	$fields = wcmo_get_registration_fields();

	if( ! empty( $enabled_fields['admin_email'] ) ) {
		$message .= sprintf(
			'<table><thead><tr><th>%s</th><th>%s</th></thead><tbody>',
			__( 'Registration Field', 'wcmo' ),
			__( 'Value', 'wcmo' )
		);
		foreach( $enabled_fields['admin_email'] as $id=>$value ) {

			$message .= sprintf(
				'<tr><td>%s</td><td>%s</td></tr>',
				$fields[$id]['label'],
				get_user_meta( $user_id, $id, true )
			);
		}
		$message .= '</tbody></table>';
	}

	$message = apply_filters( 'wcmo_new_registration_email_content', $message, $user, get_bloginfo( 'name' ), $url );

	$content .= sprintf(
		'<p>%s</p>',
		$message
	);

	ob_start();

	wc_get_template( 'emails/email-footer.php' );

	$content .= ob_get_clean();

	wc_mail( $to, $subject, $content );

	// remove_filter( 'woocommerce_email_footer_text', 'wcmo_email_footer_text' );

}

function wcmo_user_admin_head() {

	if( wcmo_get_user_approval() != 'yes' ) {
		return;
	}

	if( isset( $_GET['user_id'] ) ) {

		$user_id = $_GET['user_id'];
		$roles = wcmo_get_user_roles_by_id( $user_id );

		// Check if the user is still pending
		if( in_array( 'pending', $roles ) ) {

			printf(
				'<div class="notice notice-success"><p>%s</p></div>',
				sprintf(
					'<a class="wcmo-update-user" data-status="approve" href="#">%s</a>',
					__( 'Approve this user', 'wcmo' )
				)
			);

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				sprintf(
					'<a class="wcmo-update-user" data-status="reject" href="#">%s</a>',
					__( 'Reject this user', 'wcmo' )
				)
			);

		} else if( in_array( 'rejected', $roles ) ) {

			printf(
				'<div class="notice notice-success"><p>%s</p></div>',
				sprintf(
					'<a class="wcmo-update-user" data-status="approve" href="#">%s</a>',
					__( 'Approve this user', 'wcmo' )
				)
			);

		}

		wp_nonce_field( 'wcmo_approve_reject_user_nonce', 'wcmo_approve_reject_user_nonce' );

	}

}
add_action( 'admin_notices', 'wcmo_user_admin_head' );

/**
 * Either approve or reject the user
 */
function wcmo_approve_reject_user() {

	// Check nonce
	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'wcmo_approve_reject_user_nonce' ) ) {
		wp_send_json_error( array( 'nonce_fail' => 1 ) );
	}

	// Check admin capabilities
	if( ! current_user_can( 'edit_users' ) ) {
		wp_send_json_error( array( 'capability_fail' => 1 ) );
	}

	if( ! isset( $_POST['user_id'] ) || ! isset( $_POST['status'] ) ) {
		wp_send_json_error( array( 'variables_fail' => 1 ) );
	}

	$user_id = $_POST['user_id'];
	$user = get_user_by( 'id', $user_id );
	$status = $_POST['status'];
	$roles = wcmo_get_user_roles_by_id( $user_id );
	$saved_roles = get_user_meta( $user_id, 'wcmo_user_roles', true );

	// Approve a pending user
	$user->remove_role( 'pending' );
	if( $status == 'approve' && $saved_roles ) {
		foreach( $saved_roles as $role ) {
			$user->add_role( $role );
		}
	}

	// Reject a user
	if( $status == 'reject' && in_array( 'pending', $roles ) && $saved_roles ) {
		$user->set_role( 'rejected' );
	}

	// Let the user know what's been decided
	wcmo_user_approval_rejection_email( $user_id, $user, $status, $roles );

	wp_send_json_success( array( 'done' => 1 ) );

}
add_action( 'wp_ajax_wcmo_approve_reject_user', 'wcmo_approve_reject_user' );

/**
 * Send an email to the user when they've been approved or rejected
 * @param $user_id
 * @param $user
 * @param $status	Either approve or reject
 * @param $roles	Array of new user roles
 * @since 1.7.1
 */
function wcmo_user_approval_rejection_email( $user_id, $user, $status, $roles ) {

	// add_filter( 'woocommerce_email_footer_text', 'wcmo_email_footer_text' );

	$to = $user->user_email;

	$subject = apply_filters(
		'wcmo_user_approval_rejection_subject',
		sprintf(
			__( 'Your registration on %s', 'wcmo' ),
			get_bloginfo( 'name' )
		)
	);

	ob_start();

	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) );

	$content = ob_get_clean();

	if( $status == 'approve' ) {

		$url = get_permalink( get_option('woocommerce_myaccount_page_id') );

		$message = apply_filters(
			'wcmo_user_approval_message',
			sprintf(
				__( 'Your registration on %s has been approved. Please <a href="%s">click here to log in</a>.', 'wcmo' ),
				get_bloginfo( 'name' ),
				esc_url( $url )
			),
			$url,
			$user
		);

	} else {

		$message = apply_filters(
			'wcmo_user_rejection_message',
			sprintf(
				__( 'Your registration on %s has been rejected.', 'wcmo' ),
				get_bloginfo( 'name' ),
				esc_url( $url )
			),
			$url,
			$user
		);

	}

	$message = apply_filters( 'wcmo_user_approval_rejection_email_content', $message, $user, get_bloginfo( 'name' ), $url );

	$content .= sprintf(
		'<p>%s</p>',
		$message
	);

	ob_start();

	wc_get_template( 'emails/email-footer.php' );

	$content .= ob_get_clean();

	wc_mail( $to, $subject, $content );

	// remove_filter( 'woocommerce_email_footer_text', 'wcmo_email_footer_text' );

}


function wcmo_get_user_roles_by_id( $user_id ) {

	$user = get_user_by( 'id', $user_id );
	$roles = ( array ) $user->roles;

	return $roles;

}

/**
 * Disable automatic logging in after registration
 * @since 1.7.0
 */
function wcmo_registration_auth_new_customer( $auth ) {
	if( wcmo_get_prevent_auto_login() == 'yes' ) {
		$auth = false;
	}
	return $auth;
}
add_filter( 'woocommerce_registration_auth_new_customer', 'wcmo_registration_auth_new_customer' );

/**
 * Add a note on the new user email to confirm that their account is still pending
 * @param $content
 * @since 1.7.1
 */
function wcmo_email_additional_content_customer_new_account( $content, $user, $email ) {

	// Only add note if user approval is enabled and the user role is pending
	if( wcmo_get_user_approval() == 'yes' && is_array( $user->roles ) && in_array( 'pending', $user->roles ) ) {

		$content = sprintf(
			'<p><strong>%s</strong></p>%s',
			apply_filters(
				'wcmo_email_additional_content_customer_new_account',
				sprintf(
					'Please note that your account is still pending. You will receive another email when your account has been approved or rejected.', 'wcmo'
				)
			),
			$content
		);

	}

	return $content;

}
add_filter( 'woocommerce_email_additional_content_customer_new_account', 'wcmo_email_additional_content_customer_new_account', 10, 3 );
