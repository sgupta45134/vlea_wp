<?php
namespace ElementPack\Modules\Particles;

use ElementPack\Base\Element_Pack_Module_Base;
use Elementor\Controls_Manager;
use ElementPack;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {
	
	private $sections_data;
	
	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'bdt-particles';
	}

	public function register_controls_particles($section, $args) {
		
		$section->start_injection( [
			'of' => 'background_bg_width_mobile',
		] );

		$section->add_control(
			'section_particles_on',
			[
				'label'        => BDTEP_CP . esc_html__( 'Background Particles Effects', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => __( 'Switch on to enable Particles options! Note that currently particles are not visible in edit/preview mode for better elementor performance. It\'s only can viewed on the frontend. <b>Z-Index Problem: set column z-index 1 so particles will set behind the content.</b>', 'bdthemes-element-pack' ),
				'prefix_class' => 'bdt-particles-',
				'separator'    => ['before'],
				//'render_type'  => 'template',
			]
		);
		
		$section->add_control(
			'section_particles_js',
			[
				'label'     => esc_html__( 'Particles JSON', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					'section_particles_on' => 'yes',
				],
				'description'   => __( 'Paste your particles JSON code here - Generate it from <a href="http://vincentgarreau.com/particles.js/#default" target="_blank">Here</a>.', 'bdthemes-element-pack' ),
				'default'       => '',
				'dynamic'       => [ 'active' => true ],
				//'render_type' => 'template',
			]
		);

		$section->add_control(
			'section_particles_z_index',
			[
				'label'     => esc_html__( 'Z-Index', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'section_particles_on' => 'yes',
				],
				'description'   => __( 'If you need mouse activity, you can fix z-index.', 'bdthemes-element-pack' ),
				'default'       => '',
				'dynamic'       => [ 'active' => true ],
				'selectors' => [
					'{{WRAPPER}} .bdt-particle-container' => 'z-index: {{VALUE}};',
				],
			]
		);
		
		$section->end_injection();

	}


	public function particles_before_render($section) {    		
		$settings = $section->get_settings_for_display();
		$id       = $section->get_id();
		
		if( $settings['section_particles_on'] == 'yes' ) {

			$particle_js = $settings['section_particles_js'];
			
			if (empty($particle_js)) {
				$particle_js = '{"particles":{"number":{"value":80,"density":{"enable":true,"value_area":800}},"color":{"value":"#ffffff"},"shape":{"type":"circle","stroke":{"width":0,"color":"#000000"},"polygon":{"nb_sides":5},"image":{"src":"img/github.svg","width":100,"height":100}},"opacity":{"value":0.5,"random":false,"anim":{"enable":false,"speed":1,"opacity_min":0.1,"sync":false}},"size":{"value":3,"random":true,"anim":{"enable":false,"speed":40,"size_min":0.1,"sync":false}},"line_linked":{"enable":true,"distance":150,"color":"#ffffff","opacity":0.4,"width":1},"move":{"enable":true,"speed":6,"direction":"none","random":false,"straight":false,"out_mode":"out","bounce":false,"attract":{"enable":false,"rotateX":600,"rotateY":1200}}},"interactivity":{"detect_on":"canvas","events":{"onhover":{"enable":false,"mode":"repulse"},"onclick":{"enable":true,"mode":"push"},"resize":true},"modes":{"grab":{"distance":400,"line_linked":{"opacity":1}},"bubble":{"distance":400,"size":40,"duration":2,"opacity":8,"speed":3},"repulse":{"distance":200,"duration":0.4},"push":{"particles_nb":4},"remove":{"particles_nb":2}}},"retina_detect":true}';
			}

			$this->sections_data[$id] = [ 'particles_js' => $particle_js ];
			
			ElementPack\element_pack_config()->elements_data['sections'] = $this->sections_data;
		}
	}

	public function particles_after_render($section) {

		if ( $section->get_settings_for_display( 'section_particles_on' ) == 'yes' ) {
			wp_enqueue_script( 'particles' );
			wp_enqueue_script( 'ep-particles' );
		}

	}


	protected function add_actions() {

			add_action( 'elementor/element/section/section_background/before_section_end', [ $this, 'register_controls_particles' ], 10, 11 );
			
			add_action( 'elementor/frontend/section/before_render', [ $this, 'particles_before_render' ], 10, 1 );
			add_action( 'elementor/frontend/section/after_render', [ $this, 'particles_after_render' ], 10, 1 );

	}
}