<?php

namespace Codemanas\ZoomPro;

use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Fields;
use DateTime;
use DateTimeZone;

/**
 * Class Helpers
 *
 * Helper functions
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Helpers {

	public static $message = '';

	/**
	 * Check if a meeting is Recurring or a Recurring Webinar
	 *
	 * @param $type
	 *
	 * @return bool
	 * @since  1.2.3
	 *
	 * @author Deepen
	 */
	public static function is_webinar( $type ) {
		if ( ! empty( $type ) && ( $type === 5 || $type === 6 || $type === 9 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $post_id
	 *
	 * @return mixed|string
	 */
	public static function get_first_occurrence_date( $post_id ) {
		$meeting_details  = get_post_meta( $post_id, '_meeting_zoom_details', true );
		$occurrences      = ( isset( $meeting_details->occurrences ) && is_array( $meeting_details->occurrences ) ) ? $meeting_details->occurrences : '';
		$first_occurrence = is_array( $occurrences ) ? $occurrences[0]->start_time : 'now';

		return $first_occurrence;
	}

	/**
	 * Get Current Page URI
	 *
	 * @param $without_query_var
	 *
	 * @return string
	 */
	public static function get_current_page_uri( $without_query_var = false ) {
		$pageURL = 'http';
		if ( isset( $_SERVER["HTTPS"] ) ) {
			if ( $_SERVER["HTTPS"] == "on" ) {
				$pageURL .= "s";
			}
		}
		$pageURL .= "://";
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

		if ( $without_query_var ) {
			$url     = parse_url( $pageURL );
			$pageURL = $url['scheme'] . '://' . $url['host'] . ':' . $url['port'] . $url['path'];
		}

		return $pageURL;
	}

	/**
	 * Get Meeting Type based on the occurences
	 *
	 * @param        $type
	 * @param        $timezone
	 * @param        $occurrences
	 * @param string $time_to_compare_against
	 *
	 * @return bool
	 */
	public static function get_latest_occurence_by_type( $type, $timezone, $occurrences, $time_to_compare_against = 'now -1 hour' ) {
		try {
			//$time_to_compare_against parameter added to allow purchase when using WooCommerce
			$now        = Helpers::date_convert_by_timezone( $time_to_compare_against, $timezone );
			$start_time = false;
			if ( ! empty( $type ) && ( $type === 8 || $type === 9 ) && ! empty( $occurrences ) ) {
				foreach ( $occurrences as $occurrence ) {
					if ( $occurrence->status === "available" ) {
						$start_date = Helpers::date_convert_by_timezone( $occurrence->start_time, $timezone );
						if ( $start_date >= $now ) {
							$start_time = $occurrence->start_time;
							break;
						}

						//ENDED SESSION
						/*$start_time = date( 'Y-m-d H:i', strtotime( '-2 days' ) );
						break;*/
					}
				}
			} else if ( ! empty( $type ) && ( $type === 3 || $type === 6 ) ) {
				//No time fixed meeting
				$start_time = false;
			} else {
				//Set Past date
				// TO satisfy WooCommerce purchasable product after the occurence has been completed
				$start_time = date( 'Y-m-d H:i', strtotime( '-2 days' ) );
			}

			return $start_time;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Check if woocommerce addon plugin dependency is active
	 *
	 * @return bool
	 */
	public static function checkWooAddonActive() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		return in_array( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php', $active_plugins ) || array_key_exists( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php', $active_plugins );
	}

	/**
	 * Check if woocommerce bookings addon plugin dependency is active
	 *
	 * @return bool
	 */
	public static function checkWooBookingsAddonActive() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		return in_array( 'vczapi-woo-addon/vczapi-woo-addon.php', $active_plugins ) || array_key_exists( 'vczapi-woo-addon/vczapi-woo-addon.php', $active_plugins );
	}

	/**
	 * Two Date compare helper function
	 *
	 * @param        $start
	 * @param        $compare
	 * @param        $timezone
	 * @param string $operator
	 *
	 * @return bool
	 */
	public static function date_compare( $start, $compare, $timezone, $operator = '>=' ) {
		try {
			$tz = new \DateTimeZone( $timezone );

			$compare1 = new \DateTime( $start );
			$compare1->setTimezone( $tz );

			$compare2 = new \DateTime( $compare );
			$compare2->setTimezone( $tz );
			$result = false;
			switch ( $operator ) {
				case '>=':
					if ( $compare1 >= $compare2 ) {
						$result = true;
					}
					break;
				case '<=':
					if ( $compare1 <= $compare2 ) {
						$result = true;
					}
					break;
				case '=':
					if ( $compare1 === $compare2 ) {
						$result = true;
					}
					break;
				case '>':
					if ( $compare1 > $compare2 ) {
						$result = true;
					}
					break;
				case '<':
					if ( $compare1 < $compare2 ) {
						$result = true;
					}
					break;
			}
		} catch ( \Exception $e ) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Ouput Date by converting to timezone
	 *
	 * @param $date
	 * @param $timezone
	 *
	 * @return DateTime|string
	 */
	public static function date_convert_by_timezone( $date, $timezone ) {
		try {
			$meeting_date = new \DateTime( $date );
			$tz           = new \DateTimeZone( $timezone );
			$meeting_date->setTimezone( $tz );

			return $meeting_date;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Compile query args values
	 *
	 * @param      $args
	 * @param bool $post_id
	 * @param bool $url
	 *
	 * @return bool|string
	 */
	public static function get_url_query( $args, $post_id = false, $url = false ) {
		if ( $post_id ) {
			return add_query_arg( $args, get_permalink( $post_id ) );
		}

		if ( $url ) {
			return add_query_arg( $args, get_permalink( $url ) );
		}

		return false;
	}

	/**
	 * Reset Registered Users based on API response
	 *
	 * @param      $meeting_id
	 * @param      $post_id
	 * @param bool $cache_get Set Cache data to be TRUE or FALSE
	 */
	public static function reset_registered_users( $meeting_id, $post_id, $cache_get = true ) {
		//Create Zoom API instance
		$zoom_api = API::get_instance();
		if ( false === is_int( $meeting_id ) ) {
			return;
		}

		//IF user is not logged in - Avoid the check because its useless and resource consuming.
		if ( ! is_user_logged_in() ) {
			return;
		}

		$registered_user_ids = Fields::get_meta( $post_id, 'registered_user_ids' );
		//If no registered users then don't run this.
		if ( empty( $registered_user_ids ) ) {
			return;
		}

		//Avoid Cache Get and Directly Load VIA API
		if ( $cache_get ) {
			//Get Caching
			$registrants = Fields::get_cache( $post_id, 'registrants' );
			if ( empty( $registrants ) ) {
				$registrants = json_decode( $zoom_api->getMeetingRegistrant( $meeting_id ) );
				//Set Cache for 2 minutes to avoid API call overload
				Fields::set_cache( $post_id, 'registrants', $registrants, 60 * 2 );
			}
		} else {
			$registrants = json_decode( $zoom_api->getMeetingRegistrant( $meeting_id ) );
		}

		//IF ERROR OCCURS
		if ( ! empty( $registrants ) && ! empty( $registrants->code ) ) {
			return;
		}

		//If everything is good then go ahead and filter the unnecssary results in DB
		if ( ! empty( $registrants ) && empty( $registrants->registrants ) ) {
			foreach ( $registered_user_ids as $k => $user_id ) {
				$registrant_detail = Fields::get_user_meta( $user_id, 'registration_details' );
				if ( ! empty( $registrant_detail ) && ! empty( $registrant_detail[ $meeting_id ] ) ) {
					unset( $registrant_detail[ $meeting_id ] );
					unset( $registered_user_ids[ $k ] );
					Fields::set_user_meta( $user_id, 'registration_details', $registrant_detail );
				}
			}

			Fields::set_post_meta( $post_id, 'registered_user_ids', $registered_user_ids );
		}
	}

	/**
	 * Get Admin Notice
	 */
	public static function get_admin_notice() {
		return self::$message;
	}

	public static function set_admin_notice( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
	}

	/**
	 * Get Country list
	 *
	 * @return array
	 */
	public static function get_country_list() {
		$list = [
			"AF" => "Afghanistan",
			"AL" => "Albania",
			"DZ" => "Algeria",
			"AS" => "American Samoa",
			"AD" => "Andorra",
			"AO" => "Angola",
			"AI" => "Anguilla",
			"AQ" => "Antarctica",
			"AG" => "Antigua and Barbuda",
			"AR" => "Argentina",
			"AM" => "Armenia",
			"AW" => "Aruba",
			"AU" => "Australia",
			"AT" => "Austria",
			"AZ" => "Azerbaijan",
			"BS" => "Bahamas",
			"BH" => "Bahrain",
			"BD" => "Bangladesh",
			"BB" => "Barbados",
			"BY" => "Belarus",
			"BE" => "Belgium",
			"BZ" => "Belize",
			"BJ" => "Benin",
			"BM" => "Bermuda",
			"BT" => "Bhutan",
			"BO" => "Bolivia",
			"BA" => "Bosnia and Herzegovina",
			"BW" => "Botswana",
			"BV" => "Bouvet Island",
			"BR" => "Brazil",
			"IO" => "British Indian Ocean Territory",
			"BN" => "Brunei Darussalam",
			"BG" => "Bulgaria",
			"BF" => "Burkina Faso",
			"BI" => "Burundi",
			"KH" => "Cambodia",
			"CM" => "Cameroon",
			"CA" => "Canada",
			"CV" => "Cape Verde",
			"KY" => "Cayman Islands",
			"CF" => "Central African Republic",
			"TD" => "Chad",
			"CL" => "Chile",
			"CN" => "China",
			"CX" => "Christmas Island",
			"CC" => "Cocos (Keeling) Islands",
			"CO" => "Colombia",
			"KM" => "Comoros",
			"CG" => "Congo",
			"CD" => "Congo, the Democratic Republic of the",
			"CK" => "Cook Islands",
			"CR" => "Costa Rica",
			"CI" => "Cote D'Ivoire",
			"HR" => "Croatia",
			"CU" => "Cuba",
			"CY" => "Cyprus",
			"CZ" => "Czech Republic",
			"DK" => "Denmark",
			"DJ" => "Djibouti",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"EC" => "Ecuador",
			"EG" => "Egypt",
			"SV" => "El Salvador",
			"GQ" => "Equatorial Guinea",
			"ER" => "Eritrea",
			"EE" => "Estonia",
			"ET" => "Ethiopia",
			"FK" => "Falkland Islands (Malvinas)",
			"FO" => "Faroe Islands",
			"FJ" => "Fiji",
			"FI" => "Finland",
			"FR" => "France",
			"GF" => "French Guiana",
			"PF" => "French Polynesia",
			"TF" => "French Southern Territories",
			"GA" => "Gabon",
			"GM" => "Gambia",
			"GE" => "Georgia",
			"DE" => "Germany",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GR" => "Greece",
			"GL" => "Greenland",
			"GD" => "Grenada",
			"GP" => "Guadeloupe",
			"GU" => "Guam",
			"GT" => "Guatemala",
			"GN" => "Guinea",
			"GW" => "Guinea-Bissau",
			"GY" => "Guyana",
			"HT" => "Haiti",
			"HM" => "Heard Island and Mcdonald Islands",
			"VA" => "Holy See (Vatican City State)",
			"HN" => "Honduras",
			"HK" => "Hong Kong",
			"HU" => "Hungary",
			"IS" => "Iceland",
			"IN" => "India",
			"ID" => "Indonesia",
			"IR" => "Iran, Islamic Republic of",
			"IQ" => "Iraq",
			"IE" => "Ireland",
			"IL" => "Israel",
			"IT" => "Italy",
			"JM" => "Jamaica",
			"JP" => "Japan",
			"JO" => "Jordan",
			"KZ" => "Kazakhstan",
			"KE" => "Kenya",
			"KI" => "Kiribati",
			"KP" => "Korea, Democratic People's Republic of",
			"KR" => "Korea, Republic of",
			"KW" => "Kuwait",
			"KG" => "Kyrgyzstan",
			"LA" => "Lao People's Democratic Republic",
			"LV" => "Latvia",
			"LB" => "Lebanon",
			"LS" => "Lesotho",
			"LR" => "Liberia",
			"LY" => "Libyan Arab Jamahiriya",
			"LI" => "Liechtenstein",
			"LT" => "Lithuania",
			"LU" => "Luxembourg",
			"MO" => "Macao",
			"MK" => "Macedonia, the Former Yugoslav Republic of",
			"MG" => "Madagascar",
			"MW" => "Malawi",
			"MY" => "Malaysia",
			"MV" => "Maldives",
			"ML" => "Mali",
			"MT" => "Malta",
			"MH" => "Marshall Islands",
			"MQ" => "Martinique",
			"MR" => "Mauritania",
			"MU" => "Mauritius",
			"YT" => "Mayotte",
			"MX" => "Mexico",
			"FM" => "Micronesia, Federated States of",
			"MD" => "Moldova, Republic of",
			"MC" => "Monaco",
			"MN" => "Mongolia",
			"MS" => "Montserrat",
			"MA" => "Morocco",
			"MZ" => "Mozambique",
			"MM" => "Myanmar",
			"NA" => "Namibia",
			"NR" => "Nauru",
			"NP" => "Nepal",
			"NL" => "Netherlands",
			"AN" => "Netherlands Antilles",
			"NC" => "New Caledonia",
			"NZ" => "New Zealand",
			"NI" => "Nicaragua",
			"NE" => "Niger",
			"NG" => "Nigeria",
			"NU" => "Niue",
			"NF" => "Norfolk Island",
			"MP" => "Northern Mariana Islands",
			"NO" => "Norway",
			"OM" => "Oman",
			"PK" => "Pakistan",
			"PW" => "Palau",
			"PS" => "Palestinian Territory, Occupied",
			"PA" => "Panama",
			"PG" => "Papua New Guinea",
			"PY" => "Paraguay",
			"PE" => "Peru",
			"PH" => "Philippines",
			"PN" => "Pitcairn",
			"PL" => "Poland",
			"PT" => "Portugal",
			"PR" => "Puerto Rico",
			"QA" => "Qatar",
			"RE" => "Reunion",
			"RO" => "Romania",
			"RU" => "Russian Federation",
			"RW" => "Rwanda",
			"SH" => "Saint Helena",
			"KN" => "Saint Kitts and Nevis",
			"LC" => "Saint Lucia",
			"PM" => "Saint Pierre and Miquelon",
			"VC" => "Saint Vincent and the Grenadines",
			"WS" => "Samoa",
			"SM" => "San Marino",
			"ST" => "Sao Tome and Principe",
			"SA" => "Saudi Arabia",
			"SN" => "Senegal",
			"CS" => "Serbia and Montenegro",
			"SC" => "Seychelles",
			"SL" => "Sierra Leone",
			"SG" => "Singapore",
			"SK" => "Slovakia",
			"SI" => "Slovenia",
			"SB" => "Solomon Islands",
			"SO" => "Somalia",
			"ZA" => "South Africa",
			"GS" => "South Georgia and the South Sandwich Islands",
			"ES" => "Spain",
			"LK" => "Sri Lanka",
			"SD" => "Sudan",
			"SR" => "Suriname",
			"SJ" => "Svalbard and Jan Mayen",
			"SZ" => "Swaziland",
			"SE" => "Sweden",
			"CH" => "Switzerland",
			"SY" => "Syrian Arab Republic",
			"TW" => "Taiwan, Province of China",
			"TJ" => "Tajikistan",
			"TZ" => "Tanzania, United Republic of",
			"TH" => "Thailand",
			"TL" => "Timor-Leste",
			"TG" => "Togo",
			"TK" => "Tokelau",
			"TO" => "Tonga",
			"TT" => "Trinidad and Tobago",
			"TN" => "Tunisia",
			"TR" => "Turkey",
			"TM" => "Turkmenistan",
			"TC" => "Turks and Caicos Islands",
			"TV" => "Tuvalu",
			"UG" => "Uganda",
			"UA" => "Ukraine",
			"AE" => "United Arab Emirates",
			"GB" => "United Kingdom",
			"US" => "United States",
			"UM" => "United States Minor Outlying Islands",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VU" => "Vanuatu",
			"VE" => "Venezuela",
			"VN" => "Viet Nam",
			"VG" => "Virgin Islands, British",
			"VI" => "Virgin Islands, U.s.",
			"WF" => "Wallis and Futuna",
			"EH" => "Western Sahara",
			"YE" => "Yemen",
			"ZM" => "Zambia",
			"ZW" => "Zimbabwe"
		];

		return $list;
	}
}