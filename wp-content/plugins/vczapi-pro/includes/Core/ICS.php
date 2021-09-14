<?php

namespace Codemanas\ZoomPro\Core;

/**
 * ICS.php
 * =============================================================================
 * Use this class to create an .ics file.
 *
 * Usage
 * -----------------------------------------------------------------------------
 * Basic usage - generate ics file contents (see below for available properties):
 *   $ics = new ICS($props);
 *   $ics_file_contents = $ics->to_string();
 *
 * Setting properties after instantiation
 *   $ics = new ICS();
 *   $ics->set('summary', 'My awesome event');
 *
 * You can also set multiple properties at the same time by using an array:
 *   $ics->set(array(
 *     'dtstart' => 'now + 30 minutes',
 *     'dtend' => 'now + 1 hour'
 *   ));
 *
 * Available properties
 * -----------------------------------------------------------------------------
 * description
 *   String description of the event.
 * dtend
 *   A date/time stamp designating the end of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * dtstart
 *   A date/time stamp designating the start of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * location
 *   String address or description of the location of the event.
 * summary
 *   String short summary of the event - usually used as the title.
 * url
 *   A url to attach to the the event. Make sure to add the protocol (http://
 *   or https://).
 */
class ICS {

	/**
	 * DateTime Format
	 */
	const DT_FORMAT = 'Ymd\THis\Z';

	protected $properties = array();

	/**
	 * @var array
	 */
	private $available_properties = array(
		'freq',
		'interval',
		'count',
		'byday',
		'bymonthday',
		'bysetpos',
		'description',
		'dtend',
		'dtstart',
		'location',
		'summary',
		'organizer',
		'url'
	);

	/**
	 * ICS constructor.
	 *
	 * @param $props
	 */
	public function __construct( $props ) {
		$this->set( $props );
	}

	/**
	 * Set properties
	 *
	 * @param $key
	 * @param bool $val
	 */
	public function set( $key, $val = false ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->set( $k, $v );
			}
		} else {
			if ( in_array( $key, $this->available_properties ) ) {
				$this->properties[ $key ] = $this->sanitize_val( $val, $key );
			}
		}
	}

	/**
	 * Convert to string
	 *
	 * @return string
	 */
	public function to_string() {
		$rows = $this->build_props();

		return implode( "\r\n", $rows );
	}

	private function build_props() {
		// Build ICS properties - add header
		$ics_props = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//' . get_bloginfo( 'name' ) . '//NONSGML v1.0//EN',
			'CALSCALE:GREGORIAN',
			'BEGIN:VEVENT',
		);

		// Build ICS properties - add header
		$props = array();
		foreach ( $this->properties as $k => $v ) {
			if ( $k === "freq" ) {
				$props['RRULE'] = 'FREQ=' . $v;
			} else if ( $k === "interval" ) {
				$props['RRULE'] .= ';INTERVAL=' . $v;
			} else if ( $k === "count" ) {
				$props['RRULE'] .= ';COUNT=' . $v;
			} else if ( $k === "byday" ) {
				$props['RRULE'] .= ';BYDAY=' . $v;
			} else if ( $k === "bymonthday" ) {
				$props['RRULE'] .= ';BYMONTHDAY=' . $v;
			} else if ( $k === "bysetpos" ) {
				$props['RRULE'] .= ';BYSETPOS=' . $v;
			} else {
				$props[ strtoupper( $k . ( $k === 'url' ? ';VALUE=URI' : '' ) ) ] = $v;
			}
		}

		// Set some default values
		$props['DTSTAMP'] = $this->format_timestamp( 'now' );
		$props['UID']     = uniqid();

		// Append properties
		foreach ( $props as $k => $v ) {
			$ics_props[] = "$k:$v";
		}

		// Build ICS properties - add footer
		$ics_props[] = 'BEGIN:VALARM';
		$ics_props[] = 'ACTION:DISPLAY';
		$ics_props[] = 'DESCRIPTION:REMINDER';
		$ics_props[] = 'TRIGGER;RELATED=START:-PT00H15M00S';
		$ics_props[] = 'END:VALARM';
		$ics_props[] = 'END:VEVENT';
		$ics_props[] = 'END:VCALENDAR';

		return $ics_props;
	}

	private function sanitize_val( $val, $key = false ) {
		switch ( $key ) {
			case 'dtend':
			case 'dtstamp':
			case 'dtstart':
				$val = $this->format_timestamp( $val );
				break;
			default:
				$val = $this->escape_string( $val );
		}

		return $val;
	}

	private function format_timestamp( $timestamp ) {
		$dt = new \DateTime( $timestamp );

		return $dt->format( self::DT_FORMAT );
	}

	private function escape_string( $str ) {
		return preg_replace( '/([\;])/', '\\\$1', $str );
	}

	/**
	 * Get Occurrence Calculated data
	 *
	 * @param array $ics
	 * @param $zoom_obj
	 * @param $skip_date
	 *
	 * @return array
	 */
	public static function get_occurence_data( $ics, $zoom_obj, $skip_date = true ) {
		if ( ! empty( $zoom_obj->recurrence ) ) {
			if ( $skip_date ) {
				$ics['dtstart'] = $zoom_obj->occurrences[0]->start_time;
				$ics['dtend']   = date( 'Y-m-d\TH:i:s\Z', strtotime( $zoom_obj->occurrences[0]->start_time ) + 60 * 60 );
			}

			$ics['freq']     = self::get_recurrence_type( $zoom_obj->recurrence->type );
			$ics['interval'] = $zoom_obj->recurrence->repeat_interval;
			$ics['count']    = $zoom_obj->recurrence->end_times;

			//BY WEEK
			if ( ! empty( $zoom_obj->recurrence->weekly_days ) ) {
				$ics['byday'] = self::get_converted_day_string( $zoom_obj->recurrence->weekly_days );
			}

			//BY MONTH
			if ( ! empty( $zoom_obj->recurrence->monthly_day ) ) {
				$ics['bymonthday'] = $zoom_obj->recurrence->weekly_days;
			}

			//BY MONTH WEEK AND WEEK DAY
			if ( ! empty( $zoom_obj->recurrence->monthly_week ) && ! empty( $zoom_obj->recurrence->monthly_week_day ) ) {
				$ics['bysetpos'] = $zoom_obj->recurrence->monthly_week;
				$ics['byday']    = self::get_converted_day_string( $zoom_obj->recurrence->monthly_week_day, false );
			}
		}

		return $ics;
	}

	/**
	 * Convert Weekly Days string to Value
	 *
	 * @param $days
	 * @param $explode
	 *
	 * @return array
	 */
	public static function get_converted_day_string( $days, $explode = true ) {
		if ( $explode ) {
			$week = explode( ',', $days );
			$day  = array();
			if ( ! empty( $week ) ) {
				foreach ( $week as $wk ) {
					$day[] = self::process_number_to_day_string( $wk );
				}
			}

			if ( ! empty( $day ) ) {
				$day = implode( ',', $day );
			}
		} else {
			$day = self::process_number_to_day_string( $days );
		}

		return $day;
	}

	/**
	 * Return string based on NUMBER input
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function process_number_to_day_string( $value ) {
		if ( $value == "1" ) {
			$day = 'SU';
		} else if ( $value == "2" ) {
			$day = 'MO';
		} else if ( $value == "3" ) {
			$day = 'TU';
		} else if ( $value == "4" ) {
			$day = 'WE';
		} else if ( $value == "5" ) {
			$day = 'TH';
		} else if ( $value == "6" ) {
			$day = 'FR';
		} else if ( $value == "7" ) {
			$day = 'SA';
		} else {
			$day = 'SU';
		}

		return $day;
	}

	/**
	 * Change Type of RECURRNCE
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public static function get_recurrence_type( $type ) {
		if ( $type == "1" ) {
			$type = 'DAILY';
		} else if ( $type == "2" ) {
			$type = "WEEKLY";
		} else {
			$type = "MONTHLY";
		}

		return $type;
	}
}