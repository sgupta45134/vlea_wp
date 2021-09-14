<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use DateTime;
	use ElementPack\Base\Condition;
	use Elementor\Controls_Manager;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	class Date extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 * @since  5.3.0
		 */
		public function get_name() {
			return 'date';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 * @since  5.3.0
		 */
		public function get_title() {
			return esc_html__( 'Date Range', 'bdthemes-element-pack' );
		}
		
		/**
		 * Get the control value
		 * @return array as per condition control value
		 * @since  5.3.0
		 */
		public function get_control_value() {
			$default_date_start = date( 'Y-m-d', strtotime( '-3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			$default_date_end   = date( 'Y-m-d', strtotime( '+3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			$default_interval   = $default_date_start . ' to ' . $default_date_end;
			
			return [
				'label'          => esc_html__( 'In interval', 'bdthemes-element-pack' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => [
					'enableTime' => false,
					'mode'       => 'range',
				],
				'label_block'    => true,
				'default'        => $default_interval,
			];
		}
		
		/**
		 * Check the condition
		 *
		 * @param string $relation Comparison operator for compare function
		 * @param mixed $val will check the control value as per condition needs
		 * @since 5.3.0
		 */
		public function check( $relation, $val ) {
			// Default returned bool to false
			$show = false;
			
			// Split control value into two dates
			$intervals = explode( 'to', preg_replace( '/\s+/', '', $val ) );
			
			// Make sure the explode return an array with exactly 2 indexes
			if ( ! is_array( $intervals ) || 2 !== count( $intervals ) ) {
				return;
			}
			
			// Set start and end dates
			$today = new DateTime();
			$start = DateTime::createFromFormat( 'Y-m-d', $intervals[0] );
			$end   = DateTime::createFromFormat( 'Y-m-d', $intervals[1] );
			
			// Check vars
			if ( ! $start || ! $end ) { // Make sure it's a date
				return;
			}
			
			if ( function_exists( 'wp_timezone' ) ) {
				$timezone = wp_timezone();
				
				// Set timezone
				$today->setTimeZone( $timezone );
			}
			
			// Get timestamps for comparison
			$start_ts = $start->format( 'U' );
			$end_ts   = $end->format( 'U' );
			$today_ts = $today->format( 'U' ) + $today->getOffset(); // Adding the offset
			
			// Check that user date is between start & end
			$show = ( ( $today_ts >= $start_ts ) && ( $today_ts <= $end_ts ) );
			
			return $this->compare( $show, true, $relation );
		}
	}
