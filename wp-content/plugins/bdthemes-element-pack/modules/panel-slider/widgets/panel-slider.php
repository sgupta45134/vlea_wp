<?php
namespace ElementPack\Modules\PanelSlider\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use ElementPack\Utils;

use ElementPack\Modules\PanelSlider\Skins;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Panel Slider
 */
class Panel_Slider extends Module_Base {

	public function get_name() {
		return 'bdt-panel-slider';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Panel Slider', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-panel-slider';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'panel', 'slider' ];
	}

	public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-all-styles'];
        } else {
            return [ 'element-pack-font', 'ep-panel-slider' ];
        }
    }

	public function get_script_depends() {
      	return [ 'imagesloaded' ];
    }

	public function get_custom_help_url() {
		return 'https://youtu.be/_piVTeJd0g4';
	}

	protected function _register_skins() {
		$this->add_skin( new Skins\Skin_Middle( $this ) );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content_layout',
			[
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'bdthemes-element-pack' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'frontend_available' => true,
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_responsive_control(
			'skin_columns',
			[
				'label'          => esc_html__( 'Columns', 'bdthemes-element-pack' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '2',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
				],
				'condition' => [
					'_skin!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'column_space',
			[
				'label' => esc_html__('Column Space', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SLIDER,
			]
		);

		$this->add_responsive_control(
			'slider_height',
			[
				'label'      => esc_html__('Slider Height', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'step' => 20,
						'max'  => 1600
					],
					'vh' => [
						'min'  => 1,
						'step' => 1,
						'max'  => 100
					]
				],
				'default' => [
					'size'  => 620,
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-wrapper' => 'height: {{SIZE}}{{UNIT}};'
				]
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'   => esc_html__( 'Show Title', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'title_tags',
			[
				'label'   => __( 'Title HTML Tag', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => element_pack_title_tags(),
				'condition' => [
					'show_title' => 'yes'
				]
			]
		);

		$this->add_control(
			'global_link',
			[
				'label'        => esc_html__( 'Global Link', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'description'  => 'If it apply then button link wii not work',
				'prefix_class' => 'bdt-global-link-',
			]
		);

		$this->add_control(
			'button',
			[
				'label'       => esc_html__( 'Show Button', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => 'It will work when link field no null.',
				'conditions' => [
					'terms'    => [
						[
							'name'  => '_skin',
							'value' => ''
						],
						[
							'name'     => 'global_link',
							'operator' => '!=',
							'value'    => 'yes'
						]
					]
				]
			]
		);

		$this->add_control(
			'navigation',
			[
				'label'   => esc_html__( 'Navigation', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arrows',
				'options' => [
					'both'   => esc_html__( 'Arrows and Dots', 'bdthemes-element-pack' ),
					'arrows' => esc_html__( 'Arrows', 'bdthemes-element-pack' ),
					'dots'   => esc_html__( 'Dots', 'bdthemes-element-pack' ),
					'none'   => esc_html__( 'None', 'bdthemes-element-pack' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'nav_arrows_icon',
			[
				'label'   => esc_html__( 'Arrows Icon', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type'    => Controls_Manager::SELECT,
				'default' => '5',
				'options' => [
					'1' => esc_html__('Style 1', 'bdthemes-element-pack'),
					'2' => esc_html__('Style 2', 'bdthemes-element-pack'),
					'3' => esc_html__('Style 3', 'bdthemes-element-pack'),
					'4' => esc_html__('Style 4', 'bdthemes-element-pack'),
					'5' => esc_html__('Style 5', 'bdthemes-element-pack'),
					'6' => esc_html__('Style 6', 'bdthemes-element-pack'),
					'7' => esc_html__('Style 7', 'bdthemes-element-pack'),
					'8' => esc_html__('Style 8', 'bdthemes-element-pack'),
					'9' => esc_html__('Style 9', 'bdthemes-element-pack'),
					'10' => esc_html__('Style 10', 'bdthemes-element-pack'),
					'11' => esc_html__('Style 11', 'bdthemes-element-pack'),
					'12' => esc_html__('Style 12', 'bdthemes-element-pack'),
					'13' => esc_html__('Style 13', 'bdthemes-element-pack'),
					'14' => esc_html__('Style 14', 'bdthemes-element-pack'),
					'15' => esc_html__('Style 15', 'bdthemes-element-pack'),
					'16' => esc_html__('Style 16', 'bdthemes-element-pack'),
					'17' => esc_html__('Style 17', 'bdthemes-element-pack'),
					'18' => esc_html__('Style 18', 'bdthemes-element-pack'),
					'circle-1' => esc_html__('Style 19', 'bdthemes-element-pack'),
					'circle-2' => esc_html__('Style 20', 'bdthemes-element-pack'),
					'circle-3' => esc_html__('Style 21', 'bdthemes-element-pack'),
					'circle-4' => esc_html__('Style 22', 'bdthemes-element-pack'),
					'square-1' => esc_html__('Style 23', 'bdthemes-element-pack'),
				],
				'condition' => [
					'navigation' => ['both', 'arrows'],
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'   => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->add_control(
			'slide_skew',
			[
				'label'   => esc_html__( 'Slide Skew', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 30,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .bdt-panel-slide-item' => 'transform: skew(-{{SIZE}}deg);',
					'{{WRAPPER}} .bdt-panel-slider .bdt-panel-slide-desc' => 'transform: skew({{SIZE}}deg);',
					'{{WRAPPER}} .bdt-panel-slider .bdt-panel-slide-link' => 'transform: skew(-{{SIZE}}deg);',
					'{{WRAPPER}} .bdt-panel-slider span'                  => 'transform: skew({{SIZE}}deg); display: inline-block;',
				],
				'condition' => [
					'_skin' => ''
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_sliders',
			[
				'label' => esc_html__( 'Sliders', 'bdthemes-element-pack' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'tab_title', 
			[
				'label'       => esc_html__( 'Title', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'Slide Title' , 'bdthemes-element-pack' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'tab_image', 
			[
				'label'       => esc_html__( 'Image', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => [ 'active' => true ],
				'description' => __('Use same size ratio image', 'bdthemes-element-pack'),
			]
		);

		$repeater->add_control(
			'tab_content', 
			[
				'label'      => esc_html__( 'Content', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::WYSIWYG,
				'dynamic'     => [ 'active' => true ],
				'default'    => esc_html__( 'Slide Content', 'bdthemes-element-pack' ),
				'show_label' => false,
			]
		);

		$repeater->add_control(
			'tab_link', 
			[
				'label'       => esc_html__( 'Link', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'http://your-link.com',
				'default'     => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'tabs',
			[
				'label' => esc_html__( 'Slider Items', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'tab_title'   => esc_html__( 'Slide #1', 'bdthemes-element-pack' ),
						'tab_content' => esc_html__( 'I am item content. Click edit button to change this text.', 'bdthemes-element-pack' ),
					],
					[
						'tab_title'   => esc_html__( 'Slide #2', 'bdthemes-element-pack' ),
						'tab_content' => esc_html__( 'I am item content. Click edit button to change this text.', 'bdthemes-element-pack' ),
					],
					[
						'tab_title'   => esc_html__( 'Slide #3', 'bdthemes-element-pack' ),
						'tab_content' => esc_html__( 'I am item content. Click edit button to change this text.', 'bdthemes-element-pack' ),
					],
					[
						'tab_title'   => esc_html__( 'Slide #4', 'bdthemes-element-pack' ),
						'tab_content' => esc_html__( 'I am item content. Click edit button to change this text.', 'bdthemes-element-pack' ),
					],
				],
				'title_field' => '{{{ tab_title }}}',
			]
		);
		
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail_size',
				'label'     => esc_html__( 'Image Size', 'bdthemes-element-pack' ),
				'exclude'   => [ 'custom' ],
				'default'   => 'full',
				'separator' => 'before'
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_button',
			[
				'label'     => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'condition' => [
					'button' => 'yes',
					'_skin' => ''
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Read More', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Read More', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'panel_slider_icon',
			[
				'label'       => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'label_block' => false,
				'skin' => 'inline'
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'   => esc_html__( 'Icon Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left' => esc_html__( 'Left', 'bdthemes-element-pack' ),
					'right' => esc_html__( 'Right', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'panel_slider_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label'   => esc_html__( 'Icon Spacing', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 8,
				],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'condition' => [
					'panel_slider_icon[value]!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .bdt-button-icon-align-right' => is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-panel-slider .bdt-button-icon-align-left' => is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_slider_settings',
			[
				'label' => esc_html__( 'Slider Settings', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'   => esc_html__( 'Autoplay', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'     => esc_html__( 'Autoplay Speed', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5000,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'   => esc_html__( 'Loop', 'bdthemes-element-pack' ),
				'default' => 'yes',
				'type'    => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'pauseonhover',
			[
				'label' => esc_html__( 'Pause on Hover', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'infinite',
			[
				'label'   => esc_html__( 'Infinite Loop', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'speed',
			[
				'label'   => esc_html__( 'Animation Speed', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$this->add_control(
			'observer',
			[
				'label'       => __( 'Observer', 'bdthemes-element-pack' ),
				'description' => __( 'When you use carousel in any hidden place (in tabs, accordion etc) keep it yes.', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SWITCHER,				
			]
		);

		$this->add_control(
			'centered_slide',
			[
				'label'       => esc_html__( 'Centered Slide', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'If you turn on this option so set column even number for good looking', 'bdthemes-element-pack' ),
				'condition'   => [
					'_skin' => ''
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_slider',
			[
				'label' => esc_html__( 'Slider', 'bdthemes-element-pack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'slider_overlay_background_color',
			[
				'label'     => esc_html__( 'Overlay Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item:before' => 'background-color: {{VALUE}}; transition: all .3s ease;',
				],
			]
		);

		$this->add_control(
			'slider_active_overlay_color',
			[
				'label'     => esc_html__( 'Active Overlay Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item.swiper-slide-active:before' => 'background-color: {{VALUE}};',
				],
				// 'condition'   => [
				// 	'_skin' => 'bdt-middle'
				// ]
			]
		);

		$this->add_control(
			'slider_background_color',
			[
				'label'     => esc_html__( 'Overlay Hover Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-overlay-gradient' => 'background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 40%, {{VALUE}} 100%);',
				],
			]
		);

		$this->add_control(
			'slider_opacity',
			[
				'label'   => esc_html__( 'Opacity', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.4,
				],
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .bdt-panel-slide-item .bdt-panel-slide-thumb img' => 'opacity: {{SIZE}};',
					'{{WRAPPER}} .bdt-panel-slider.bdt-skin-middle .swiper-slide:not(.swiper-slide-active):hover .bdt-panel-slide-thumb img' => 'opacity: {{SIZE}} !important;',

				],
			]
		);

		$this->add_responsive_control(
			'desc_padding',
			[
				'label'     => esc_html__( 'Description Padding', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .bdt-panel-slide-desc' => 'padding: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;',
				],
			]
		);

		$this->add_control(
			'shadow_mode',
			[
				'label'        => esc_html__( 'Shadow Mode', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-ep-shadow-mode-',
			]
		);

		$this->add_control(
			'shadow_color',
			[
				'label'     => esc_html__( 'Shadow Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'shadow_mode' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container:before' => 'background: linear-gradient(to right,
					{{VALUE}} 5%,rgba(255,255,255,0) 100%);',
					'{{WRAPPER}} .elementor-widget-container:after'  => 'background: linear-gradient(to right, rgba(255,255,255,0) 0%, {{VALUE}} 95%);',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button',
			[
				'label'      => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'terms' => [
						[
							'name'  => 'button',
							'value' => 'yes'
						],
						[
							'name'  => '_skin',
							'value' => ''
						],
						[
							'name'     => 'global_link',
							'operator' => '!=',
							'value'    => 'yes'
						],
					]
				]
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
            'btn_spacing',
            [
                'label'     => __('Spacing', 'bdthemes-element-pack') . BDTEP_NC,
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label' => esc_html__( 'Animation', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link',
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link',
			]
		);

		$this->add_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-link',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title',
			[
				'label'     => esc_html__( 'Title', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' =>'yes',
				],
			]
		);

		$this->add_control(
			'show_text_stroke',
			[
				'label'   => esc_html__('Text Stroke', 'bdthemes-element-pack') . BDTEP_NC,
				'type'    => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-text-stroke--',
			]
		);

		$this->add_responsive_control(
			'text_stroke_width',
			[
				'label' => esc_html__('Text Stroke Width', 'bdthemes-element-pack') . BDTEP_NC,
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-title' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_text_stroke' => 'yes'
				]
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-title' => 'color: {{VALUE}}; -webkit-text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-text',
			]
		);

		$this->add_responsive_control(
            'text_top_spacing',
            [
                'label'     => __('Spacing', 'bdthemes-element-pack') . BDTEP_NC,
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .bdt-panel-slide-item .bdt-panel-slide-text' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_navigation',
			[
				'label'     => esc_html__( 'Navigation', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'navigation' => [ 'arrows', 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'heading_style_arrows',
			[
				'label'     => esc_html__( 'Arrows', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'hide_arrow_on_mobile',
			[
				'label'     => __( 'Hide Arrow on Mobile ?', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],				
			]
		);

		$this->add_control(
			'arrows_position',
			[
				'label' => esc_html__( 'Arrows Position', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .bdt-navigation-next' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-panel-slider .bdt-navigation-prev' => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_size',
			[
				'label' => esc_html__( 'Arrows Size', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 25,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev svg, {{WRAPPER}} .bdt-navigation-next svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_background',
			[
				'label'     => __( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev, {{WRAPPER}} .bdt-navigation-next' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_hover_background',
			[
				'label'     => __( 'Hover Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev:hover, {{WRAPPER}} .bdt-navigation-next:hover' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_color',
			[
				'label'     => __( 'Arrows Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev svg, {{WRAPPER}} .bdt-navigation-next svg' => 'color: {{VALUE}}',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_hover_color',
			[
				'label'     => __( 'Arrows Hover Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev:hover svg, {{WRAPPER}} .bdt-navigation-next:hover svg' => 'color: {{VALUE}}',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_responsive_control(
			'arrows_padding',
			[
				'label' => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .bdt-navigation-prev, {{WRAPPER}} .bdt-navigation-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'arrows_border_radius',
			[
				'label'      => __( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'separator'  => 'after',
				'selectors'  => [
					'{{WRAPPER}} .bdt-navigation-prev, {{WRAPPER}} .bdt-navigation-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'arrows', 'both' ],
				],
			]
		);

		$this->add_control(
			'heading_style_dots',
			[
				'label'     => esc_html__( 'Dots', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_position',
			[
				'label' => esc_html__( 'Dots Position', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -80,
						'max' => 80,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .swiper-pagination-bullets' => 'bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_size',
			[
				'label' => esc_html__( 'Dots Size', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-panel-slider .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label'     => __( 'Dots Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}}; opacity: 1;',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->add_control(
			'active_dot_color',
			[
				'label'     => __( 'Active Dots Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'navigation' => [ 'dots', 'both' ],
				],
			]
		);

		$this->end_controls_section();

	}
	
	protected function render_header() {
		$id              = 'bdt-panel-slider-' . $this->get_id();
		$settings        = $this->get_settings_for_display();
		$skin_class      = '';
		
		$elementor_vp_lg = get_option( 'elementor_viewport_lg' );
		$elementor_vp_md = get_option( 'elementor_viewport_md' );
		$viewport_lg     = !empty($elementor_vp_lg) ? $elementor_vp_lg - 1 : 1023;
		$viewport_md     = !empty($elementor_vp_md) ? $elementor_vp_md - 1 : 767;
		
		$columns         = $settings['_skin'] ? $settings['skin_columns'] : $settings['columns'];
		$columns_tablet  = $settings['_skin'] ? $settings['skin_columns_tablet'] : $settings['columns_tablet'];
		$columns_mobile  = $settings['_skin'] ? $settings['skin_columns_mobile'] : $settings['columns_mobile'];

		if ($settings['_skin']) {
			$skin_class = 'bdt-skin-middle';
		} else {
			$skin_class = 'bdt-skin-default';
		}

		$this->add_render_attribute(
			[
				'panel-slider' => [
					'data-settings' => [
						wp_json_encode(array_filter([
							"autoplay"       => $settings["autoplay"] ? [ "delay" => $settings["autoplay_speed"] ] : false,
							"loop"           => $settings["loop"] ? true : false,
							"speed"          => $settings["speed"],
							"pauseOnHover"   => $settings["pauseonhover"] ? true : false,
							"slidesPerView"  => (int) $columns_mobile,
							"observer"       => $settings["observer"] ? true : false,
							"observeParents" => $settings["observer"] ? true : false,
							"spaceBetween"   => $settings['column_space']['size'] ? : 0,
							"centeredSlides" => ( $settings["centered_slide"] or "bdt-middle" == $settings["_skin"] ) ? true : false,
							"breakpoints"    => [
								(int) $viewport_md => [
									"slidesPerView" => (int) $columns_tablet,
								],
								(int) $viewport_lg => [
									"slidesPerView" => (int) $columns,
								]
					      	],
			      	        "navigation" => [
			      				"nextEl" => "#" . $id . " .bdt-navigation-next",
			      				"prevEl" => "#" . $id . " .bdt-navigation-prev",
			      			],
			      			"pagination" => [
			      			  "el"         => "#" . $id . " .swiper-pagination",
			      			  "type"       => "bullets",
			      			  "clickable"  => true,
			      			],
				        ]))
					],
					'class' => [
						'bdt-panel-slider',
						$skin_class,
					],
					'id' => $id
				]
			]
		);

		?>
		<div <?php echo $this->get_render_attribute_string( 'panel-slider' ); ?>>
			<div class="swiper-container">
		<?php
	}

	protected function render_footer($settings) {

		$hide_on_mobile = $settings['hide_arrow_on_mobile'] ? ' bdt-visible@m' : '';

			?>
			</div>
			<?php if ( 'none' !== $settings['navigation'] ) : ?>
				<?php if ( 'arrows' !== $settings['navigation'] ) : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>

				<?php if ( 'dots' !== $settings['navigation'] ) : ?>

					<a class="bdt-navigation-next bdt-icon bdt-position-z-index bdt-position-center-right<?php echo esc_attr( $hide_on_mobile ); ?>">
						<i class="ep-arrow-left-<?php echo esc_attr($settings['nav_arrows_icon']); ?>" aria-hidden="true"></i>
					</a>	

					<a class="bdt-navigation-prev bdt-icon bdt-position-z-index bdt-position-center-left<?php echo esc_attr( $hide_on_mobile ); ?>">
						<i class="ep-arrow-right-<?php echo esc_attr($settings['nav_arrows_icon']); ?>" aria-hidden="true"></i>
					</a>	

				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		$this->render_header();
		$counter   = 1;

		?>
		<div class="swiper-wrapper">
			<?php 
			foreach ( $settings['tabs'] as $item ) :

				$image_src = Group_Control_Image_Size::get_attachment_image_src( $item['tab_image']['id'], 'thumbnail_size', $settings );
				$image_url =  $image_src ? : BDTEP_ASSETS_URL. '/images/panel-slider.svg';

				$this->add_render_attribute(
					[
						'button-attr' => [
							'class' => [
								'bdt-panel-slide-link',
								'bdt-transition-slide-top',
								$settings['button_hover_animation'] ? 'elementor-animation-' . $settings['button_hover_animation'] : ''
							],
							'href'   => $item['tab_link']['url'] ? esc_url($item['tab_link']['url']) : '',
							'target' => $item['tab_link']['is_external'] ? '_blank' : '_self',
						]
					], '', '', true
				);
				
				$this->add_render_attribute( 'panel-slide-item', 'class', ['bdt-panel-slide-item', 'swiper-slide', 'bdt-transition-toggle'], true );

				if ('yes' == $settings['global_link'] and $item['tab_link']['url']) {

					$target = $item['tab_link']['is_external'] ? '_blank' : '_self';

					$this->add_render_attribute( 'panel-slide-item', 'onclick', "window.open('" . $item['tab_link']['url'] . "', '$target')", true );
				}

				if ( ! isset( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
					// add old default
					$settings['icon'] = 'fas fa-arrow-right';
				}

				$migrated  = isset( $settings['__fa4_migrated']['panel_slider_icon'] );
				$is_new    = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

				$this->add_render_attribute( 'panel-slide-item-title', 'class', ['bdt-panel-slide-title bdt-transition-slide-bottom'], true );

				?>
				<div <?php echo $this->get_render_attribute_string( 'panel-slide-item' ); ?>>
		        	<div class="bdt-panel-slide-thumb bdt-background-cover" style="background-image: url(<?php echo esc_url($image_url); ?>);"></div>
		        	<div class="bdt-panel-slide-desc bdt-position-bottom-left bdt-position-z-index">
			        	<?php if (!empty( $item['tab_link']['url']))  : ?>
				        	<?php if ($settings['button'] and '' == $settings['_skin'])  : ?>
								<a <?php echo $this->get_render_attribute_string( 'button-attr' ); ?>>
									<span>
										<?php echo esc_html($settings['button_text']); ?>
									</span>
									<?php if ($settings['panel_slider_icon']['value']) : ?>
										<span class="bdt-button-icon-align-<?php echo esc_attr($settings['icon_align']); ?>">

											<?php if ( $is_new || $migrated ) :
												Icons_Manager::render_icon( $settings['panel_slider_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
											else : ?>
												<i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
											<?php endif; ?>

										</span>
									<?php endif; ?>
								</a>
							<?php endif; ?>

						<?php endif; ?>

						<?php if ( 'yes' == $settings['show_title'] ) : ?>
							<<?php echo Utils::get_valid_html_tag($settings['title_tags']); ?> <?php echo $this->get_render_attribute_string('panel-slide-item-title'); ?>><?php echo esc_html($item['tab_title']); ?></<?php echo Utils::get_valid_html_tag($settings['title_tags']); ?>>
						<?php endif; ?>

						<?php if ( '' !== $item['tab_content'] ) : ?>
							<div class="bdt-panel-slide-text bdt-transition-slide-bottom"><?php echo $this->parse_text_editor( $item['tab_content'] ); ?></div>
						<?php endif; ?>
			  		</div>

			  		<?php if ( '' !== $item['tab_content'] or 'yes' == $settings['show_title'] ) : ?>
					<div class="bdt-transition-fade bdt-position-cover bdt-overlay bdt-overlay-gradient"></div>
					<?php endif; ?>
				</div>
			<?php
				$counter++;
			endforeach; ?>
		</div>
		
		<?php $this->render_footer($settings);
	}
}
