<?php
	
	namespace ElementPack\Modules\VisibilityControls\Conditions;
	
	use ElementPack\Base\Condition;
	use Elementor\Controls_Manager;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	
	class Static_Page extends Condition {
		
		/**
		 * Get the name of condition
		 * @return string as per our condition control name
		 * @since  5.3.0
		 */
		public function get_name() {
			return 'static_page';
		}
		
		/**
		 * Get the title of condition
		 * @return string as per condition control title
		 * @since  5.3.0
		 */
		public function get_title() {
			return esc_html__( 'Page', 'bdthemes-element-pack' );
		}
		
		/**
		 * Get the page
		 * @return array of different types of page
		 * @since  5.3.0
		 */
		public function get_control_value() {
			return [
				'type'        => Controls_Manager::SELECT,
				'default'     => 'home',
				'label_block' => true,
				'options'     => [
					'home'   => esc_html__( 'Homepage', 'bdthemes-element-pack' ),
					'static' => esc_html__( 'Front Page', 'bdthemes-element-pack' ),
					'blog'   => esc_html__( 'Blog', 'bdthemes-element-pack' ),
					'404'    => esc_html__( '404 Page', 'bdthemes-element-pack' ),
				],
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
			if ( 'home' === $val ) {
				return $this->compare( ( is_front_page() && is_home() ), true, $relation );
			} elseif ( 'static' === $val ) {
				return $this->compare( ( is_front_page() && ! is_home() ), true, $relation );
			} elseif ( 'blog' === $val ) {
				return $this->compare( ( ! is_front_page() && is_home() ), true, $relation );
			} elseif ( '404' === $val ) {
				return $this->compare( is_404(), true, $relation );
			}
		}
	}
