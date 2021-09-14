<?php
namespace ElementPack\Modules\EqualHeight;

use Elementor\Elementor_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use ElementPack;
use ElementPack\Plugin;
use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'bdt-equal-height';
	}

	public function register_controls_equal_height($section, $section_id, $args) {

		static $layout_sections = [ 'section_advanced'];

		if ( ! in_array( $section_id, $layout_sections ) ) { return; }
		

		$section->start_controls_section(
			'section_equal_height_controls',
			[
				'label' => BDTEP_CP . __( 'Equal Height', 'bdthemes-element-pack' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);


		$section->add_control(
			'section_equal_height_on',
			[
				'label'        => esc_html__( 'Enable Equal Height', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'description'  => esc_html__( 'You can equal your column/widgets height equal by enable this option.', 'bdthemes-element-pack' ),
			]
		);

		$section->add_control(
			'section_equal_height_selector',
			[
				'label'     => esc_html__( 'Equal Height For', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'column'     => 'Columns',
					'widgets'    => 'Widgets',
					'widgets_c1' => 'Widgets > Child',
					'widgets_c2' => 'Widgets > Child > Child',
					'widgets_c3' => 'Widgets > Child > Child > Child',
					'custom'     => 'Custom Selector',
				],
				'default'   => 'widgets',
				'condition' => [
					'section_equal_height_on' => 'yes',
				],
			]
		);

		$section->add_control(
			'section_equal_height_custom_selector',
			[
				'label'       => esc_html__( 'Custom Selector', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '.class-name',
				'condition'   => [
					'section_equal_height_on' => 'yes',
					'section_equal_height_selector' => 'custom',
				],
			]
		);

		$section->end_controls_section();

	}


	public function equal_height_before_render($section) {    		
		$settings = $section->get_settings_for_display();
		if( !empty($settings[ 'section_equal_height_on' ]) == 'yes' ) {
			
			$height_option = '';

			if ( 'column' == $settings['section_equal_height_selector']) {
				$height_option = 'target: .elementor-section .elementor-widget-wrap';
			}

			if ( 'widgets' == $settings['section_equal_height_selector']) {
				$height_option = 'target: .elementor-section .elementor-widget-wrap .elementor-widget > .elementor-widget-container';
			}

			if ( 'widgets_c1' == $settings['section_equal_height_selector']) {
				$height_option = 'target: .elementor-section .elementor-widget-wrap .elementor-widget > .elementor-widget-container > div:nth-of-type(1)';
			}

			if ( 'widgets_c2' == $settings['section_equal_height_selector']) {
				$height_option = 'target: .elementor-section .elementor-widget-wrap .elementor-widget > .elementor-widget-container > div > div:nth-of-type(1)';
			}

			if ( 'widgets_c3' == $settings['section_equal_height_selector']) {
				$height_option = 'target: .elementor-section .elementor-widget-wrap .elementor-widget > .elementor-widget-container > div > div > div:nth-of-type(1)';
			}

			if ( 'custom' == $settings['section_equal_height_selector'] and $settings['section_equal_height_custom_selector']) {
				$height_option = 'target: .elementor-section ' . esc_attr($settings['section_equal_height_custom_selector']) ;
			}
			
			if ($height_option) {
				$section->add_render_attribute( '_wrapper', 'bdt-height-match', $height_option );
			}
		}
	}

	protected function add_actions() {

		add_action( 'elementor/element/after_section_end', [ $this, 'register_controls_equal_height' ], 10, 3 );
		add_action( 'elementor/frontend/section/before_render', [ $this, 'equal_height_before_render' ], 10, 1 );
		
	}
}