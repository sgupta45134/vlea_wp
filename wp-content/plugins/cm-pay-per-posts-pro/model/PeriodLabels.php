<?php

namespace com\cminds\payperposts\model;

class PeriodLabels {

	private static $units = [
		'min' => 'minutes',
		'h'   => 'hours',
		'd'   => 'days',
		'w'   => 'weeks',
		'm'   => 'months',
		'y'   => 'years',
		'l'   => 'lifetime'
	];

	public static function getLocalized( $number, $period ) {
		$period = trim( $period );

		// if it's a unit
		if ( isset( self::$units[ $period ] ) ) {
			$period = self::unitToPeriod( $period );
		}

		if ( $period === 'lifetime' ) {
			return Labels::getLocalized( $period );
		}

		if ( ( (int) $number ) === 1 ) {
			// remove last letter
			$period = substr( $period, 0, strlen( $period ) - 1 );
		}

		return $number . ' ' . Labels::getLocalized( $period );
	}

	public static function getLocalizedArray( $array ) {
		if ( isset( $array['number'] ) && isset( $array['unit'] ) ) {

			return self::getLocalized( $array['number'], $array['unit'] );

		} else {

			error_log( "\n\n[" . date( "Y-m-d H:i:s" ) . "]\n[File: " . basename( __FILE__ ) . ' -> Function: ' . __FUNCTION__ . ']: ' . "\n" .
			           '[Line]: ' . __LINE__ . "\n" .
			           '[EMPTY FIELDS number or unit in $array]: ' . print_r( $array, true ), 3, 'cm_error.log' );
		}

		return "";
	}


	public static function unitToPeriod( $unit ) {
		return static::$units[ $unit ];
	}

}
