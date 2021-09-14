<?php
	
	namespace ElementPack\Modules\BackgroundParallax;
	
	use Elementor\Controls_Manager;
	use ElementPack;
	use ElementPack\Base\Element_Pack_Module_Base;
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	} // Exit if accessed directly
	
	class Module extends Element_Pack_Module_Base {
		
		public function __construct() {
			parent::__construct();
			$this->add_actions();
		}
		
		public function get_name() { 
			return 'bdt-background-parallax';
		}
		
		public function register_controls( $section, $args ) {
			
			$section->start_injection(
				[
					'type' => 'control',
					'at'   => 'after',
					'of'   => 'background_background',
				] );
			
			$section->add_control(
				'section_parallax_on',
				[
					'label'        => BDTEP_CP . esc_html__( 'Parallax/Scrolling Effects', 'bdthemes-element-pack' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'return_value' => 'yes',
					'description'  => esc_html__( 'Set parallax or scrolling background effects by enable this option.', 'bdthemes-element-pack' ),
					'separator'    => [ 'before' ],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->add_control(
				'section_parallax_x_value',
				[
					'label'       => esc_html__( 'Parallax X', 'bdthemes-element-pack' ),
					'type'        => Controls_Manager::SLIDER,
					'range'       => [
						'px' => [
							'min'  => - 500,
							'max'  => 500,
							'step' => 10,
						],
					],
					'description' => esc_html__( 'How much x parallax move happen on scroll.', 'bdthemes-element-pack' ),
					'condition'   => [
						'section_parallax_on'   => 'yes',
						'background_background' => [ 'classic' ],
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->add_control(
				'section_parallax_value',
				[
					'label'       => esc_html__( 'Parallax Y', 'bdthemes-element-pack' ),
					'type'        => Controls_Manager::SLIDER,
					'range'       => [
						'px' => [
							'min'  => - 500,
							'max'  => 500,
							'step' => 10,
						],
					],
					'default'     => [
						'unit' => 'px',
						'size' => - 200,
					],
					'description' => esc_html__( 'How much y parallax move happen on scroll.', 'bdthemes-element-pack' ),
					'condition'   => [
						'section_parallax_on'   => 'yes',
						'background_background' => [ 'classic' ],
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			
			$section->add_control(
				'ep_parallax_bg_colors',
				[
					'label'       => __( 'Colors', 'bdthemes-element-pack' ),
					'type'        => Controls_Manager::POPOVER_TOGGLE,
					'condition'   => [
						'section_parallax_on' => 'yes',
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->start_popover();
			
			$section->add_control(
				'ep_parallax_bg_border_color_start',
				[
					'label'     => esc_html__( 'Border Color (Start)', 'bdthemes-element-pack' ),
					'type'      => Controls_Manager::COLOR,
					'condition' => [
						'section_parallax_on'   => 'yes',
						'ep_parallax_bg_colors' => 'yes'
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->add_control(
				'ep_parallax_bg_border_color_end',
				[
					'label'     => esc_html__( 'Border Color (End)', 'bdthemes-element-pack' ),
					'type'      => Controls_Manager::COLOR,
					'condition' => [
						'section_parallax_on'   => 'yes',
						'ep_parallax_bg_colors' => 'yes'
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->add_control(
				'ep_parallax_bg_color_start',
				[
					'label'     => esc_html__( 'Background Color (Start)', 'bdthemes-element-pack' ),
					'type'      => Controls_Manager::COLOR,
					'condition' => [
						'section_parallax_on'   => 'yes',
						'ep_parallax_bg_colors' => 'yes'
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			$section->add_control(
				'ep_parallax_bg_color_end',
				[
					'label'     => esc_html__( 'Background Color (End)', 'bdthemes-element-pack' ),
					'type'      => Controls_Manager::COLOR,
					'condition' => [
						'section_parallax_on'   => 'yes',
						'ep_parallax_bg_colors' => 'yes'
					],
					'render_type'        => 'none',
					'frontend_available' => true,
				]
			);
			
			
			$section->end_popover();
			
			$section->end_injection();
			
		}
		
		
		/*
		public function parallax_before_render( $section ) {
			$settings = $section->get_settings_for_display();
			
			if ( $section->get_settings( 'section_parallax_on' ) == 'yes' ) {
				
				$parallax_x             = $settings['section_parallax_x_value'];
				$parallax_y             = $settings['section_parallax_value'];
				$border_color_start     = $settings['ep_parallax_bg_border_color_start'];
				$border_color_end       = $settings['ep_parallax_bg_border_color_end'];
				$background_color_start = $settings['ep_parallax_bg_color_start'];
				$background_color_end   = $settings['ep_parallax_bg_color_end'];
				
				if ( $parallax_x['size'] ) {
					$section->add_render_attribute( '_wrapper', 'data-bdt-parallax', 'bgx: ' . $parallax_x['size'] . ';' );
				}
				if ( $parallax_y['size'] ) {
					$section->add_render_attribute( '_wrapper', 'data-bdt-parallax', 'bgy: ' . $parallax_y['size'] . ';' );
				}
				
				if ( $border_color_start and $border_color_end ) {
					$section->add_render_attribute( '_wrapper', 'data-bdt-parallax', 'border-color: ' . $border_color_start . ',' . $border_color_end . ';' );
				}
				
				if ( $background_color_start and $background_color_end ) {
					$section->add_render_attribute( '_wrapper', 'data-bdt-parallax', 'background-color: ' . $background_color_start . ',' . $background_color_end . ';' );
				}
			}
		}
		*/
		
		protected function add_actions() {
			
			add_action(
				'elementor/element/section/section_background/before_section_end', [
				$this,
				'register_controls'
			], 10, 2 );
			//add_action( 'elementor/element/column/section_style/before_section_end', [ $this, 'register_controls'], 10, 2 );
			
			// add_action( 'elementor/frontend/section/before_render', [ $this, 'parallax_before_render' ], 10, 1 );
			//add_action( 'elementor/frontend/column/before_render', [ $this, 'parallax_before_render' ], 10, 1 );
			
		}
	} 