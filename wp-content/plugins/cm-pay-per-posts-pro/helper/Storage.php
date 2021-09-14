<?php

namespace com\cminds\payperposts\helper;

class Storage {

	protected static function getKey() {
		return ( is_user_logged_in() ) ? self::getUserKey() : self::getGuestKey();
	}

	public static function set( $key, $value, $expiration = 0 ) {
		$key = self::getKey() . '_' . $key;
		set_transient( $key, $value, $expiration );
	}

	private static function getUserKey() {
		$key = md5( "user_" . get_current_user_id() );

		return $key;
	}

	private static function getGuestKey() {
		if ( isset( $_COOKIE['PHPSESSID'] ) ) {
			$key = $_COOKIE['PHPSESSID'];
		} else {
			$key = md5( $_SERVER['HTTP_USER_AGENT'] ) . md5( $_SERVER['REMOTE_ADDR'] );
		}

		return $key;
	}

	public static function getUserData( $key, $default = '' ) {
		$res = get_transient( self::getUserKey() . '_' . $key );

		if ( ! isset( $res ) || empty( $res ) ) {
			return $default;
		}

		return $res;
	}

	public static function getGuestData( $key, $default = '' ) {
		$res = get_transient( self::getGuestKey() . '_' . $key );

		if ( ! isset( $res ) || empty( $res ) ) {
			return $default;
		}

		return $res;
	}

	public static function get( $key, $default = '' ) {
		$res = get_transient( self::getKey() . '_' . $key );

		if ( ! isset( $res ) || empty( $res ) ) {
			return $default;
		}

		return $res;
	}

	public static function search( $key ) {
		global $wpdb;

		$key = self::getKey() . '_' . $key;

		$query = "SELECT * FROM {$wpdb->prefix}options
				  WHERE option_name LIKE '_transient_{$key}%'";

		$res = $wpdb->get_results( $query );

		if ( ! empty( $res ) ) {
			foreach ( $res as $i => $r ) {
				$name          = str_replace( '_transient_', '', $r->option_name );
				$transient_res = get_transient( $name );

				if ( ! $transient_res ) {
					unset( $res[ $i ] );
				}
			}
		}

		return $res;
	}

	public static function get_user_time_offset( $user_id = 0 ) {
		if ( is_user_logged_in() ) {
			$user_id = ( $user_id ) ? $user_id : get_current_user_id();

			// save to user metadata
			return get_user_meta( $user_id, 'user_time_offset', true );
		}

		return self::get( 'user_time_offset', false );
	}

	public static function set_user_time_offset( $user_time_offset = false ) {
		if ( is_user_logged_in() ) {
			// save to user metadata
			update_user_meta( get_current_user_id(), 'user_time_offset', $user_time_offset );
		}
		self::set( 'user_time_offset', $user_time_offset );
	}
}
