<?php

namespace com\cminds\payperposts\helper;

class TimeHelper {

	static function period2seconds( $period ) {
		$period = preg_replace( '/\s/', '', $period );
		$units  = array(
			'min' => 60,
			'h'   => 3600,
			'd'   => 3600 * 24,
			'w'   => 3600 * 24 * 7,
			'm'   => 3600 * 24 * 30,
			'y'   => 3600 * 24 * 365,
			'l'   => 3600 * 24 * 365 * 100
		);
		$unit   = preg_replace( '/[0-9]/', '', $period );
		if ( isset( $units[ $unit ] ) ) {
			$number = preg_replace( '/[^0-9]/', '', $period );

			return $number * $units[ $unit ];
		}
	}

	static function seconds2period( $seconds ) {
		$units    = array(
			'minute'   => 60,
			'hour'     => 3600,
			'day'      => 3600 * 24,
			'week'     => 3600 * 24 * 7,
			'month'    => 3600 * 24 * 30,
			'year'     => 3600 * 24 * 365,
			'lifetime' => 3600 * 24 * 365 * 100
		);
		$units    = array_reverse( $units );
		$result   = $seconds;
		$lastUnit = 'year';
		foreach ( $units as $unit => $sec ) {
			$lastUnit = $unit;
			if ( $seconds % $sec == 0 ) {
				$result = $seconds / $sec;
				break;
			}
		}
		//return $result .' '. \__($lastUnit . ($result == 1 ? '' : 's'));
		if ( $lastUnit == 'lifetime' ) {
			return \__( $lastUnit );
		} else {
			return $result . ' ' . \__( $lastUnit . ( $result == 1 ? '' : 's' ) );
		}
	}

	static function period2date( $period ) {
		$units = array(
			'min' => 'minute',
			'h'   => 'hour',
			'd'   => 'day',
			'w'   => 'week',
			'm'   => 'month',
			'y'   => 'year',
			'l'   => 'lifetime'
		);
		$unit  = preg_replace( '/[0-9\s]/', '', $period );
		if ( isset( $units[ $unit ] ) ) {
			$number = preg_replace( '/[^0-9]/', '', $period );
			//return $number .' '. \__($units[$unit] . ($number == 1 ? '' : 's'));
			if ( $units[ $unit ] == 'lifetime' ) {
				$unitname = $units[ $unit ];
			} else {
				$unitname = $units[ $unit ] . ( $number == 1 ? '' : 's' );
			}
			if ( $units[ $unit ] == 'lifetime' ) {
				return \__( $unitname );
			} else {
				return $number . ' ' . \__( $unitname );
			}
		}
	}

	static function showUserLocalDatetime( $datetime, $user_id = 0, $human_readable = false ) {

		// $datetime should be in seconds
		$user_time_offset = Storage::get_user_time_offset( $user_id ); // in seconds

		if ( $user_time_offset !== false ) {
			// get server time offset
			$server_time_offset = date( 'Z' ); // in seconds
			$datetime = $datetime - $server_time_offset + $user_time_offset;
		}

		return ( $human_readable ) ? date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $datetime ) : $datetime;
	}
}
