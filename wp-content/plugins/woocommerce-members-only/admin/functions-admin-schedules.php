<?php
/**
 * Settings functions for the admin
 * @package WCMO
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Do a daily check for any expiring roles
 * @since 1.8.0
 */
function wcmo_check_expired_roles() {

	$expiring_roles = get_option( 'wcmo_expiring_roles', array() );

	if( empty( $expiring_roles ) ) {
		// There are no roles that are set to expire
		return;
	}

	$today = strtotime( 'today midnight' );

	// Find any users to send a reminder to
	foreach( $expiring_roles as $role ) {

		$args = array(
			'role'				=> $role,
			'limit'				=> 9999,
			'meta_query'  => array(
	      'relation'  => 'AND',
	      array(
	        'key'     => $role . '_remind_on',
	        'value'   => $today,
	      )
	    )
		);
		$remind_users_q = new WP_User_Query( $args );
		$remind_users = $remind_users_q->get_results();

		if( $remind_users ) {

			foreach( $remind_users as $user ) {

				/**
				 * @hooked wcmo_send_reminder_email
				 */
				do_action( 'wcmo_remind_user', $user, $role );

			}

		}

	}


	// Find any users that have a role that is due to expire today
	foreach( $expiring_roles as $role ) {

		$args = array(
			'role'				=> $role,
			'limit'				=> 9999,
			'meta_query'  => array(
	      'relation'  => 'AND',
	      array(
	        'key'     => $role . '_expires',
	        'value'   => $today,
	      )
	    )
		);
		$expiring_users_q = new WP_User_Query( $args );
		$expiring_users = $expiring_users_q->get_results();

		if( $expiring_users ) {

			foreach( $expiring_users as $user ) {

				// Remove roles from expiring users
				$user->remove_role( $role );

				// Add expired role, e.g. Gold Member (expired)
				do_action( 'wcmo_after_expired_user_remove_role', $user, $role );

			}

		}

	}

}
add_action( 'woocommerce_scheduled_sales', 'wcmo_check_expired_roles' );

/**
 * Send a reminder to the user that their role is about to expire
 * @since 1.10.0
 */
function wcmo_send_reminder_email( $user, $role ) {

	$expires = get_user_meta( $user->ID, $role . '_expires', true );
	$product_id = get_user_meta( $user->ID, $role . '_expires_product_id', true );

	$to = $user->user_email;

	$subject = apply_filters(
		'wcmo_reminder_email_subject',
		__( 'Your membership will shortly expire', 'wcmo' )
	);

	ob_start();

	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) );

	$content = ob_get_clean();

	$message = sprintf(
		'<p>%s<p>',
		sprintf(
			__( 'Your membership for %s will expire on %s. Please <a href="%s">visit this link to renew your membership.</a>.', 'wcmo' ),
			get_the_title( $product_id ),
			$expires,
			get_permalink( $product_id )
		)
	);

	$message = apply_filters( 'wcmo_reminder_email_content', $message, $user, $role, $expires, $product_id );

	$content .= $message;

	ob_start();

	wc_get_template( 'emails/email-footer.php' );

	$content .= ob_get_clean();

	wc_mail( $to, $subject, $content );

}
add_action( 'wcmo_remind_user', 'wcmo_send_reminder_email', 10, 2 );
