<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use DateTime;
	use ElementPack\Base\Condition;
	use Elementor\Controls_Manager;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	class Date_Time_Before extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 * @since  5.3.0
		 */
		public function get_name() {
			return 'date_time_before';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 * @since  5.3.0
		 */
		public function get_title() {
			return esc_html__( 'Till Date (Server Time)', 'bdthemes-element-pack' );
		}
		
		/**
		 * Get the control value
		 * @return array as per condition control value
		 * @since  5.3.0
		 */
		public function get_control_value() {
			$default_date = date( 'Y-m-d', strtotime( '+3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			
			return [
				'label'          => esc_html__( 'Before', 'bdthemes-element-pack' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => [
					'enableTime' => false,
				],
				'label_block'    => true,
				'default'        => $default_date,
			];
		}
		
		/**
		 * Check the condition
		 *
		 * @param string $relation Comparison operator for compare function
		 * @param mixed $val will check the control value as per condition needs
		 *
		 * @since 5.3.0
		 */
		public function check( $relation, $val ) {
			// Default returned bool to false
			$show = false;
			
			$today = new DateTime();
			$date  = DateTime::createFromFormat( 'Y-m-d', $val );
			
			// Check vars
			if ( ! $date ) { // Make sure it's a date
				return;
			}
			
			if ( function_exists( 'wp_timezone' ) ) {
				$timezone = wp_timezone();
				
				// Set timezone
				$today->setTimeZone( $timezone );
			}
			
			// Get timestamps for comparison
			$date_ts  = $date->format( 'U' );
			$today_ts = $today->format( 'U' ) + $today->getOffset(); // Adding the offset
			
			// Check that today is before specified date
			$show = $today_ts <= $date_ts;
			
			return $this->compare( $show, true, $relation );
		}
	}
