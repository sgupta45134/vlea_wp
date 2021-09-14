<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use ElementPack\Base\Condition;
	use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	class Post extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 * @since  5.3.0
		 */
		public function get_name() {
			return 'post';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 * @since  5.3.0
		 */
		public function get_title() {
			return esc_html__( 'Post', 'bdthemes-element-pack' );
		}
		
		/**
		 * Get the control value
		 * @return array as per condition control value
		 * @since  5.3.0
		 */
		public function get_control_value() {
			return [
				'label'       => esc_html__( 'Search & Select', 'bdthemes-element-pack' ),
				'type'        => Dynamic_Select::TYPE,
				'default'     => '',
				'placeholder' => esc_html__( 'Any', 'bdthemes-element-pack' ),
				'description' => esc_html__( 'Leave blank or select all for any post type.', 'bdthemes-element-pack' ),
				'label_block' => true,
				'multiple'    => true,
				'query_args'  => [
					'query' => 'only_post',
				],
			];
		}
		
		/**
		 * Check the condition
		 * @param string $relation Comparison operator for compare function
		 * @param mixed $val will check the control value as per condition needs
		 * @since 5.3.0
		 */
		public function check( $relation, $val ) {
			$show = false;
			
			if ( is_array( $val ) && ! empty( $val ) ) {
				foreach ( $val as $_key => $_value ) {
					if ( is_single( $_value ) || is_singular( $_value ) ) {
						$show = true;
						break;
					}
				}
			} else {
				$show = is_single( $val ) || is_singular( $val );
			}
			
			return $this->compare( $show, true, $relation );
		}
	}
