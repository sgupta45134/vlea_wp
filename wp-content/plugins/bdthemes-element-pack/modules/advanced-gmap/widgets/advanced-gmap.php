<?php
namespace ElementPack\Modules\AdvancedGmap\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Advanced_Gmap extends Module_Base {

	public function get_name() {
		return 'bdt-advanced-gmap';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Advanced Google Map', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-advanced-gmap';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'advanced', 'gmap', 'location' ];
	}

	public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-all-styles'];
        } else {
            return ['ep-advanced-gmap'];
        }
    }

	public function get_script_depends() {
		return [ 'gmap-api', 'gmap' ];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/qaZ-hv6UPDY';
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content_gmap',
			[
				'label' => esc_html__( 'Google Map', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'avd_google_map_zoom_control',
			[
				'label'   => esc_html__( 'Zoom Control', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'avd_google_map_default_zoom',
			[
				'label' => esc_html__( 'Default Zoom', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 15,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 24,
					],
				],
				'condition' => ['avd_google_map_zoom_control' => 'yes']
			]
		);

		$this->add_control(
			'avd_google_map_street_view',
			[
				'label'   => esc_html__( 'Street View Control', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'avd_google_map_type_control',
			[
				'label'   => esc_html__( 'Map Type Control', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'avd_google_map_height',
			[
				'label' => esc_html__( 'Map Height', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-gmap'  => 'min-height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'gmap_geocode',
			[
				'label' => esc_html__( 'Search Address', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'search_align',
			[
				'label'   => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-gmap-search-wrapper' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'gmap_geocode' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'search_spacing',
			[
				'label' => esc_html__( 'Spacing', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-gmap-search-wrapper'  => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'gmap_geocode' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_marker',
			[
				'label' => esc_html__( 'Marker', 'bdthemes-element-pack' ),
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'tabs_content_marker' );

		$repeater->start_controls_tab(
			'tab_content_content',
			[
				'label' => esc_html__( 'Content', 'bdthemes-element-pack' ),
			]
		);

		$repeater->add_control(
			'marker_lat',
			[
				'label'   => esc_html__( 'Latitude', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => '24.8238746',
			]
		);

		$repeater->add_control(
			'marker_lng',
			[
				'label'   => esc_html__( 'Longitude', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => '89.3816299',
			]
		);

		$repeater->add_control(
			'marker_title',
			[
				'label'   => esc_html__( 'Title', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => 'Another Place',
			]
		);

		$repeater->add_control(
			'marker_content',
			[
				'label'   => esc_html__( 'Content', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => [ 'active' => true ],
				'default' => esc_html__( 'Your Business Address Here', 'bdthemes-element-pack'),
			]
		);
		
		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'tab_content_marker',
			[
				'label' => esc_html__( 'Marker', 'bdthemes-element-pack' ),
			]
		);

		$repeater->add_control(
			'custom_marker',
			[
				'label'       => esc_html__( 'Custom marker', 'bdthemes-element-pack' ),
				'description' => esc_html__('Use max 32x32 px size icon for better result.', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::MEDIA,
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'marker',
			[
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'marker_lat'     => '24.8248746',
						'marker_lng'     => '89.3826299',
						'marker_title'   => esc_html__( 'BdThemes', 'bdthemes-element-pack' ),
						'marker_content' => esc_html__( '<strong>BdThemes Limited</strong>,<br>Latifpur, Bogra - 5800,<br>Bangladesh', 'bdthemes-element-pack'),
					],
				],
				'title_field' => '{{{ marker_title }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_gmap',
			[
				'label' => esc_html__( 'GMap Style', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'avd_google_map_style',
			[
				'label'   => esc_html__( 'Style Json Code', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => '',
				'description'   => sprintf( __( 'Go to this link: %1s snazzymaps.com %2s and pick a style, copy the json code from first with [ to last with ] then come back and paste here', 'bdthemes-element-pack' ), '<a href="https://snazzymaps.com/" target="_blank">', '</a>' ),
			]
		);

		$this->start_controls_tabs( 'tabs_style_css_filters' );

		$this->start_controls_tab(
			'tab_css_filter_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' )
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} .bdt-advanced-gmap',
			]
		); 

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_css_filter_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' )
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .bdt-advanced-gmap:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();


		$this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'map_border',
                'label'    => esc_html__('Border', 'bdthemes-element-pack'),
                'selector' => '{{WRAPPER}} .bdt-advanced-gmap',
                'separator'=> 'before'
            ]
        );

        $this->add_responsive_control(
            'map_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .bdt-advanced-gmap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        
		$this->end_controls_section();
	
		$this->start_controls_section(
			'section_style_search',
			[
				'label'     => esc_html__( 'Search', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'gmap_geocode' => 'yes',
				],
			]
		);

		$this->add_control(
			'search_background',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'search_placeholder_color',
			[
				'label'     => esc_html__( 'Placeholder Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input::placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-search.bdt-search-default span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'search_shadow',
				'selector' => '{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'search_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input',
			]
		);

		$this->add_responsive_control(
			'search_border_radius',
			[
				'label'      => esc_html__( 'Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'search_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'search_margin',
			[
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-search.bdt-search-default .bdt-search-input' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings           = $this->get_settings_for_display();
		$id                 = 'bdt-advanced-gmap-'.$this->get_id();
		$ep_api_settings    = get_option( 'element_pack_api_settings' );
		
		$map_settings       = [];
		$map_settings['el'] = '#'.$id;
		
		$marker_settings    = [];
		$marker_content     = [];
		$bdt_counter        = 0;
		$all_markers        = [];

		foreach ( $settings['marker'] as $marker_item ) {
			$marker_settings['lat']    = (double)(( $marker_item['marker_lat'] ) ? $marker_item['marker_lat'] : '');
			$marker_settings['lng']    = (double)(( $marker_item['marker_lng'] ) ? $marker_item['marker_lng'] : '');
			$marker_settings['title']  = ( $marker_item['marker_title'] ) ? $marker_item['marker_title'] : '';
			$marker_settings['icon']   = ( $marker_item['custom_marker']['url'] ) ? $marker_item['custom_marker']['url'] : '';
			
			$marker_settings['infoWindow']['content'] = ( $marker_item['marker_content'] ) ? $marker_item['marker_content'] : '';

			$all_markers[] = $marker_settings;

			$bdt_counter++;
			if ( 1 === $bdt_counter ) {
				$map_settings['lat'] = ( $marker_item['marker_lat'] ) ? $marker_item['marker_lat'] : '';
				$map_settings['lng'] = ( $marker_item['marker_lng'] ) ? $marker_item['marker_lng'] : '';
			}
		};


		$map_settings['zoomControl']       = ( $settings['avd_google_map_zoom_control'] ) ? true : false;
		$map_settings['zoom']              =  $settings['avd_google_map_default_zoom']['size'];
		
		$map_settings['streetViewControl'] = ( $settings['avd_google_map_street_view'] ) ? true : false;
		$map_settings['mapTypeControl']    = ( $settings['avd_google_map_type_control'] ) ? true : false;

		?>

		<?php if(empty($ep_api_settings['google_map_key'])) : ?>
			<div class="bdt-alert-warning" data-bdt-alert>
			    <a class="bdt-alert-close" data-bdt-close></a>
			    <?php $ep_setting_url = esc_url( admin_url('admin.php?page=element_pack_options#element_pack_api_settings')); ?>
			    <p><?php printf(__( 'Please set your google map api key in <a href="%s">element pack settings</a> to show your map correctly.', 'bdthemes-element-pack' ), $ep_setting_url); ?></p>
			</div>
		<?php endif; ?>
	
		<?php if($settings['gmap_geocode']) : ?>

			<div class="bdt-gmap-search-wrapper bdt-margin">
			    <form method="post" id="<?php echo esc_attr($id); ?>form" class="bdt-search bdt-search-default">
			        <span data-bdt-search-icon></span>
			        <input id="<?php echo esc_attr($id); ?>address" name="address" class="bdt-search-input" type="search" placeholder="Search...">
			    </form>
			</div>

		<?php endif;

		$this->add_render_attribute( 'advanced-gmap', 'id', $id );
		$this->add_render_attribute( 'advanced-gmap', 'class', 'bdt-advanced-gmap' );
		
		$this->add_render_attribute( 'advanced-gmap', 'data-map_markers', wp_json_encode($all_markers) );

		if( '' != $settings['avd_google_map_style'] ) {
			$this->add_render_attribute( 'advanced-gmap', 'data-map_style', trim(preg_replace('/\s+/', ' ', $settings['avd_google_map_style'])) );
		}

		$this->add_render_attribute( 'advanced-gmap', 'data-map_settings', wp_json_encode($map_settings) );
		$this->add_render_attribute( 'advanced-gmap', 'data-map_geocode', ('yes' == $settings['gmap_geocode']) ? 'true' : 'false' );
		
		?>

		<div <?php echo $this->get_render_attribute_string( 'advanced-gmap' ); ?>></div>
		
		<?php
	}
}
