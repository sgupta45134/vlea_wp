<?php
/**
 * Functions to help with users
 * @since 1.7.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update user roles
 *
 * @param $user_id The ID of a user
 * @param $new_roles The list of new roles being assigned
 * @param $old_roles The list of old roles being assigned
 * @since 1.7.0
 */
function wcmo_update_users_role( $user_id, $new_roles, $old_roles=array() ) {

	$user = new WP_User( $user_id );

	// Never change an admin's role to avoid locking out admins testing the plugin
	if ( ! empty( $user->roles ) && in_array( 'administrator', $user->roles ) ) {
		return $user;
	}

	if( ! is_array( $new_roles ) ) {
		$new_roles = ( array ) $new_roles;
	}

	// Remove existing roles?
	if( apply_filters( 'wcmo_remove_existing_roles', false ) ) {

		// We've opted to remove old roles so if none are specified, remove all old roles
		if ( empty( $old_roles ) ) {
			// Get all user's roles
			$old_roles = ( array ) $user->roles;
		}

		// Remove old roles
		foreach( $old_roles as $role ) {
			$user->remove_role( $role );
		}

	}

	// Add new roles
	foreach( $new_roles as $role ) {
		$user->add_role( $role );
	}

	do_action( 'wcmo_after_update_users_role', $user );

	return $user;

}

/**
 * Prevent users with Pending user roles from logging in
 *
 * @since 1.7.0
 */
function wcmo_prevent_pending_users( $user, $username ) {

	if( wcmo_get_prevent_pending() == 'yes' && is_array( $user->roles ) && in_array( 'pending', $user->roles ) ) {
		return new WP_Error(
			'pending_user',
		 	apply_filters( 'wcmo_pending_user_account_message', __( 'Your user account is still pending', 'wcmo' ), $user )
		);
	}

	return $user;

}
add_filter( 'authenticate', 'wcmo_prevent_pending_users', 1000, 2 );

/**
 * Prevent users with Rejected user roles from logging in
 *
 * @since 1.7.0
 */
function wcmo_prevent_rejected_users( $user, $username ) {

	if( wcmo_get_prevent_rejected() == 'yes' && is_array( $user->roles ) && in_array( 'rejected', $user->roles ) ) {
		return new WP_Error(
			'rejected_user',
		 	apply_filters( 'wcmo_rejected_user_account_message', __( 'Your user account has been rejected', 'wcmo' ), $user )
		);
	}

	return $user;

}
add_filter( 'authenticate', 'wcmo_prevent_rejected_users', 1000, 2 );


/**
 * User roles will expire on this date
 * @param $user_id							The customer's ID
 * @param $expires_after_value	How many of...
 * @param $expires_after_period ... the time period
 * @param $roles								The roles that have been assigned this time
 */
function wcmo_set_role_expiration_date( $user_id, $expires_after_value, $expires_after_period, $roles, $product_id=false ) {

	if( ! $user_id || ! $expires_after_value || ! $expires_after_period ) {
		return;
	}

	// Calculate the expiry date
	$today = strtotime( 'today' );

	// Store list of roles that have expiry dates
	$expiring_roles = get_option( 'wcmo_expiring_roles', array() );

	// Need to round date down to midnight?

	if( $expires_after_period == 'day' ) {

		$expires = $today + ( $expires_after_value * DAY_IN_SECONDS );

	} else if( $expires_after_period == 'week' ) {

		$expires = $today + ( $expires_after_value * WEEK_IN_SECONDS );

	} else if( $expires_after_period == 'month' ) {

		// This is a calendar month so if we bought the product on the 2nd of June, set the expiry for the 2nd of July etc

		$start_date = date( 'Y-m-d', $today ); // select date in Y-m-d format
		$expires = wcmo_get_monthly_expiry_date( $start_date, $expires_after_value ); // output: 2014-07-02

	} else if( $expires_after_period == 'year' ) {

		// This is a calendar year

		$start_date = date( 'Y-m-d', $today ); // select date in Y-m-d format
		$expires = wcmo_get_monthly_expiry_date( $start_date, $expires_after_value * 12 ); // output: 2014-07-02

	}

	if( ! is_array( $roles ) ) {
		$roles = ( array ) $roles;
	}

	$role_expires = array();

	// Iterate through each role and save an expiry date
	foreach( $roles as $role ) {

		// Filter here to allow role-specific filtering
		$expires = apply_filters( 'wcmo_role_expiration_date', $expires, $role, $user_id );

		// Don't need
		$role_expires[$role] = $expires;
		// We use this to query expiring roles
		update_user_meta( $user_id, $role . '_expires', $expires );
		update_user_meta( $user_id, $role . '_expires_product_id', $product_id );
		$expiring_roles[] = $role;

		// Add a scheduled reminder date
		if( $product_id ) {

			$reminder_before_value = get_post_meta( $product_id, 'wcmo_product_reminder_before_value', true );
			$reminder_before_period = get_post_meta( $product_id, 'wcmo_product_reminder_before_period', true );

			if( $reminder_before_value && $reminder_before_period ) {
				// Subtract this period from the expiry date
				if( $reminder_before_period == 'day' ) {

					$remind_on = $expires - ( $reminder_before_value * DAY_IN_SECONDS );

				} else if( $reminder_before_period == 'week' ) {

					$remind_on = $expires - ( $reminder_before_value * WEEK_IN_SECONDS );

				} else if( $reminder_before_period == 'month' ) {

					// This is a calendar month so if it expires on 2 July, we set the reminder for 2 June

					$expires_date = date( 'Y-m-d', $expires ); // select date in Y-m-d format
					$remind_on = wcmo_get_monthly_reminder_date( $expires_date, $reminder_before_value ); // output: 2014-07-02

				} else if( $reminder_before_period == 'year' ) {

					// This is a calendar year

					$expires_date = date( 'Y-m-d', $expires ); // select date in Y-m-d format
					$remind_on = wcmo_get_monthly_reminder_date( $expires_date, $reminder_before_value * 12 ); // output: 2014-07-02

				}

			}

			if( $remind_on ) {
				update_user_meta( $user_id, $role . '_remind_on', $remind_on );
			}

		}

	}

	update_option( 'wcmo_expiring_roles', array_unique( $expiring_roles ) );

	// Don't need this - adjust the user profile page
	update_user_meta( $user_id, 'role_expires', $role_expires );

}

/**
 * Add specified number of months to given date
 * @param		$start_date
 * @param		$months
 * @return	Date
 * @since		1.8.0
 */
function wcmo_get_monthly_expiry_date( $start_date, $months	) {

	$start_date_object = new DateTime( $start_date );

	// Find the date interval that we will need to add to the start date
	$date_interval = wcmo_get_date_interval( $months, $start_date_object );

	// Add this date interval to the current date (the DateTime class handles remaining complexity like year-ends)
	$end_date_object = $start_date_object->add( $date_interval );

	// Subtract (sub) 1 day from date
	// $end_date_object->sub( new DateInterval( 'P1D' ) );

	// Format final date to Y-m-d
	$end_date = $end_date_object->format( 'Y-m-d' );

	return strtotime( $end_date );

}

/**
 * Subtract specified number of months to given date
 * @param		$start_date
 * @param		$months
 * @return	Date
 * @since		1.8.0
 */
function wcmo_get_monthly_reminder_date( $start_date, $months	) {

	$start_date_object = new DateTime( $start_date );

	// Find the date interval that we will need to add to the start date
	$date_interval = wcmo_get_date_interval( $months, $start_date_object );

	// Add this date interval to the current date (the DateTime class handles remaining complexity like year-ends)
	$end_date_object = $start_date_object->sub( $date_interval );

	// Subtract (sub) 1 day from date
	// $end_date_object->sub( new DateInterval( 'P1D' ) );

	// Format final date to Y-m-d
	$end_date = $end_date_object->format( 'Y-m-d' );

	return strtotime( $end_date );

}

/**
 * Find the date interval we need to add to start date to get end date
 * @param		$n_months
 * @param		$start_date_object
 * @return	Date
 * @since		1.8.0
 * @link		https://stackoverflow.com/questions/2870295/increment-date-by-one-month
 */
function wcmo_get_date_interval( $n_months, DateTime $start_date_object ) {

	// Create new datetime object identical to inputted one
	$date_of_last_day_next_month = new DateTime( $start_date_object->format( 'Y-m-d' ) );

	// And modify it so it is the date of the last day of the next month
	$date_of_last_day_next_month->modify( 'last day of +' . $n_months . ' month' );

	// If the day of inputted date (e.g. 31) is greater than last day of next month (e.g. 28)
	if($start_date_object->format( 'd' ) > $date_of_last_day_next_month->format( 'd' ) ) {

		// Return a DateInterval object equal to the number of days difference
		return $start_date_object->diff( $date_of_last_day_next_month );
		// Otherwise the date is easy and we can just add a month to it

	} else {

		// Return a DateInterval object equal to a period (P) of 1 month (M)
		return new DateInterval( 'P' . $n_months . 'M' );

	}

}

/**
 * Get a formatted expiry date
 * @since 1.9.14
 */
function wcmo_get_nice_expiry_date( $date ) {
	$date_format = get_option( 'date_format' );
	return date( $date_format, $date );
}
