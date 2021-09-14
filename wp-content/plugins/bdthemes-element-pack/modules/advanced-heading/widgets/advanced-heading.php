<?php
namespace ElementPack\Modules\AdvancedHeading\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use ElementPack\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AdvancedHeading extends Module_Base {

	public function get_name() {
		return 'bdt-advanced-heading';
	}

	public function get_title() {
		return BDTEP . __( 'Advanced Heading', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-advanced-heading';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'advanced', 'heading', 'title' ];
	}

	public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-all-styles'];
        } else {
            return ['ep-advanced-heading'];
        }
    }

    public function get_script_depends() {
		return [ 'ep-advanced-heading' ];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/E1jYInKYTR0';
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content_heading',
			[
				'label' => __( 'Heading', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'sub_heading',
			[
				'label'       => __( 'Sub Heading', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => __( 'Enter your prefix title', 'bdthemes-element-pack' ),
				'default'     => __( 'SUB HEADING HERE', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'main_heading',
			[
				'label'       => __( 'Main Heading', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => __( 'Enter your main heading here', 'bdthemes-element-pack' ),
				'default'     => __( 'I am Advanced Heading', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'split_main_heading',
			[
				'label'     => __( 'Split Main Heading', 'bdthemes-element-pack' ),
				'separator' => 'before',
				'type'      => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'split_text',
			[
				'label'       => __( 'Split Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
                'placeholder' => __( 'Enter your split text', 'bdthemes-element-pack' ),
                'default'     => __( 'Split Text', 'bdthemes-element-pack' ),
                'condition'   => [
                    'split_main_heading' => 'yes'
				],
				'separator'	  => 'after',
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => __( 'Link', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'http://your-link.com',
			]
		);

		$this->add_control(
			'header_size',
			[
				'label'   => __( 'HTML Tag', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => element_pack_title_tags(),
				'default' => 'h2',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'   => __( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],

			]
		);

		$this->add_control(
			'advanced_heading_visibility',
			[
				'label'     => __( 'Show Advanced Heading', 'bdthemes-element-pack' ),
				'separator' => 'before',
				'type'      => Controls_Manager::SWITCHER,
				'default'	=> 'yes',
			]
		);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'section_content_advanced_heading',
			[
				'label' 	=> __( 'Advanced Heading', 'bdthemes-element-pack' ),
				'condition' => [
					'advanced_heading_visibility' => 'yes',
				],
			]
		);
		$this->add_control(
			'advanced_heading',
			[
				'label'       => __( 'Advanced Heading', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => __( 'Enter your advanced heading', 'bdthemes-element-pack' ),
				'description' => __( 'This heading will show as style as background and you can move and style many way.', 'bdthemes-element-pack' ),
				'default'     => esc_html__( 'Advanced Heading', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'advanced_heading_align',
			[
				'label'   => __( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'advanced_heading_x_position',
			[
				'label'   => __( 'X Offset', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -800,
						'max' => 800,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ep-advanced-heading-pos-x: {{SIZE}}px;'
				],
			]
		);

		$this->add_responsive_control(
			'advanced_heading_y_position',
			[
				'label'   => __( 'Y Offset', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -800,
						'max' => 800,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ep-advanced-heading-pos-y: {{SIZE}}px;'
				],
			]
		);

		$this->add_control(
			'advanced_heading_origin',
			[
				'label'       => __( 'Rotate Origin', 'bdthemes-element-pack' ),
				'description' => __( 'Origin work when you set rotate value', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'top-left',
				'options'     => element_pack_position(),
			]
		);

		
		$this->add_responsive_control(
			'advanced_heading_rotate',
			[
				'label'   => __( 'Rotate', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'tablet_default' => [
					'size' => 0,
				],
				'mobile_default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ep-advanced-heading-rotate: {{SIZE}}deg;'
				],
			]
		);

		$this->add_control(
			'advanced_heading_hide',
			[
				'label'       => __( 'Hide at', 'bdthemes-element-pack' ),
				'description' => __( 'Some cases you need to hide it because when you set heading at outer position mobile device can show wrong width in that case you can hide it at mobile or tablet device. if you set overflow hidden on section or body so you don\'t need it.', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'm',
				'options'     => [
					''  => esc_html__('Nothing', 'bdthemes-element-pack'),
					'm' => esc_html__('Tablet and Mobile ', 'bdthemes-element-pack'),
					's' => esc_html__('Mobile', 'bdthemes-element-pack'),
				],
			]
		);

		$this->add_control(
			'title_multi_color',
			[
				'label'     => __( 'Title Multi Color', 'bdthemes-element-pack' ),
				'separator' => 'before',
				'type'      => Controls_Manager::SWITCHER, 
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_sub_heading',
			[
				'label'     => __( 'Sub Heading', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'sub_heading!' => '',
				]
			]
		);

		$this->add_control(
			'sub_heading_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_heading_typography',
				//'scheme'   => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'sub_heading_text_shadow',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading',
			]
		);

		$this->add_control(
			'sub_heading_style',
			[
				'label'   => __( 'Style', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					''     => esc_html__('None', 'bdthemes-element-pack'),
					'line' => esc_html__('Line', 'bdthemes-element-pack'),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sub_heading_style_color',
			[
				'label'     => __( 'Style Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading .line:after' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'sub_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'sub_heading_style_width',
			[
				'label' => __( 'Width', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 1,
						'max'  => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading .line:after' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'sub_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'sub_heading_style_height',
			[
				'label' => __( 'Height', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 1,
						'max'  => 48,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-sub-heading .line:after' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'sub_heading_style' => 'line',
				],
			]
		);

		$this->add_control(
			'sub_heading_style_align',
			[
				'label'   => __( 'Style Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'right'      => __( 'After', 'bdthemes-element-pack' ),
					'left'       => __( 'Before', 'bdthemes-element-pack' ),
					'left-right' => __( 'After and Before', 'bdthemes-element-pack' ),
					'bottom'     => __( 'Bottom', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'sub_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'sub_heading_style_indent',
			[
				'label'   => __( 'Style Spacing', 'bdthemes-element-pack' ),
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
					'sub_heading_style' => 'line',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-button-icon-align-right'  => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-advanced-heading .bdt-button-icon-align-left'   => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-advanced-heading .bdt-button-icon-align-bottom' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_main_heading',
			[
				'label'     => __( 'Main Heading', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'main_heading!' => '',
				],
			]
		);

		$this->start_controls_tabs('tabs_style_main_heading');

		$this->start_controls_tab(
			'tab_style_normal',
			[
				'label' => esc_html__('Normal', 'bdthemes-element-pack')
			]
		);

		$this->add_control(
			'show_main_text_stroke',
			[
				'label'   => esc_html__('Text Stroke', 'bdthemes-element-pack') . BDTEP_NC,
				'type'    => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-main-text-stroke--',
			]
		);

		$this->add_responsive_control(
			'main_text_stroke_width',
			[
				'label' => esc_html__('Text Stroke Width', 'bdthemes-element-pack') . BDTEP_NC,
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_main_text_stroke' => 'yes'
				]
			]
		);

		$this->add_control(
			'main_heading_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner' => 'color: {{VALUE}}; -webkit-text-stroke-color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'main_heading_background',
			[
				'label'     => __( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner' => 'background-color: {{VALUE}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'main_heading_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner'
			]
		);

		$this->add_responsive_control(
			'main_heading_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;'
				]
			]
		);

		$this->add_responsive_control(
			'main_heading_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'main_heading_shadow',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner'
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'main_heading_text_shadow',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner'
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'main_heading_typography',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner',
			]
		);

		$this->add_control(
			'heading_mainh_split_text',
			[
				'label'     => __( 'Split Text', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_control(
			'mainh_split_text_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text' => 'color: {{VALUE}}; -webkit-text-stroke-color: {{VALUE}};',
				],
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_control(
			'mainh_split_text_background',
			[
				'label'     => __( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

        $this->add_responsive_control(
            'split_text_space',
            [
                'label'   => __( 'Split Space', 'bdthemes-element-pack' ),
                'type'    => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .bdt-main-heading .bdt-main-heading-inner' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition'   => [
                    'split_main_heading' => 'yes'
                ],
            ]
        );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'mainh_split_text_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text',
				'condition'   => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_responsive_control(
			'mainh_split_text_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;'
				],
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_responsive_control(
			'mainh_split_text_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				],
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'mainh_split_text_shadow',
				'selector'  => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text',
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'mainh_split_text_typography',
				'selector'  => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-mainh-split-text',
				'condition' => [
					'split_main_heading' => 'yes',
					'split_text!'        => ''
				]
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_style_advanced',
			[
				'label' => esc_html__('Advanced', 'bdthemes-element-pack')
			]
		);

		$this->add_control(
			'main_heading_advanced_style_color',
			[
				'label'        => __( 'Advanced Style', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-ep-main-color-',
				'render_type'  => 'template',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'main_heading_advanced_color',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-main-heading-inner',
				'condition' => [
					'main_heading_advanced_style_color' => 'yes'
				],
			]
		);

		$this->add_control(
			'main_heading_link_color',
			[
				'label'     => __( 'Link Hover Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading > a:hover .bdt-main-heading-inner' => 'color: {{VALUE}};',
				],
				'condition' => [
					'main_heading_advanced_style_color!' => 'yes'
				]
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'main_heading_style',
			[
				'label'   => __( 'Style', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					''     => esc_html__('None', 'bdthemes-element-pack'),
					'line' => esc_html__('Line', 'bdthemes-element-pack'),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'main_heading_style_color',
			[
				'label'     => __( 'Style Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .line:after' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'main_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'main_heading_style_width',
			[
				'label' => __( 'Width', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 1,
						'max'  => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .line:after' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'main_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'main_heading_style_height',
			[
				'label' => __( 'Height', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 1,
						'max'  => 48,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .line:after' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'main_heading_style' => 'line',
				],
			]
		);

		$this->add_control(
			'main_heading_style_align',
			[
				'label'   => __( 'Style Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					'right'      => __( 'After', 'bdthemes-element-pack' ),
					'left'       => __( 'Before', 'bdthemes-element-pack' ),
					'left-right' => __( 'After and Before', 'bdthemes-element-pack' ),
					'bottom'     => __( 'Bottom', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'main_heading_style' => 'line',
				],
			]
		);

		$this->add_responsive_control(
			'main_heading_style_indent',
			[
				'label'   => __( 'Style Spacing', 'bdthemes-element-pack' ),
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
					'main_heading_style' => 'line',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-button-icon-align-right'  => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-button-icon-align-left'   => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-advanced-heading .bdt-main-heading .bdt-button-icon-align-bottom' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_advanced_heading',
			[
				'label'     => __( 'Advanced Heading', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'advanced_heading!' => '',
					'advanced_heading_visibility' => 'yes',

				],
			]
		);

		$this->add_control(
			'advanced_heading_advanced_color',
			[
				'label'        => __( 'Advanced Style', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-ep-advanced-color-',
				'render_type'  => 'template',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'advanced_heading_advanced_bg_color',
				'selector'  => '{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div',
				'condition' => [
					'advanced_heading_advanced_color' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_advanced_text_stroke',
			[
				'label'   => esc_html__('Text Stroke', 'bdthemes-element-pack') . BDTEP_NC,
				'type'    => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-advanced-text-stroke--',
				'condition' => [
					'advanced_heading_advanced_color!' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'advanced_text_stroke_width',
			[
				'label' => esc_html__('Text Stroke Width', 'bdthemes-element-pack') . BDTEP_NC,
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_advanced_text_stroke' => 'yes'
				]
			]
		);

		$this->add_control(
			'advanced_heading_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => 'color: {{VALUE}}; -webkit-text-stroke-color: {{VALUE}};',
				],
				'condition' => [
					'advanced_heading_advanced_color!' => 'yes',
				],
			]
		);
		
		$this->add_control(
			'advanced_heading_background_color',
			[
				'label'     => __( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'advanced_heading_advanced_color!' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'advanced_heading_shadow',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'advanced_heading_border',
				'label'       => __( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div',
			]
		);

		$this->add_responsive_control(
			'advanced_heading_border_radius',
			[
				'label'      => __( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'advanced_heading_padding',
			[
				'label'      => __( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'advanced_heading_opacity',
			[
				'label' => __( 'Opacity', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0.05,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'advanced_heading_box_shadow',
				'selector' => '{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'advanced_heading_typography',
				'selector'  => '{{WRAPPER}} .bdt-advanced-heading .bdt-advanced-heading-content > div',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings         = $this->get_settings_for_display();
		$id               = $this->get_id();
		$heading_html     = [];
		$advanced_heading = '';
		$sub_heading      = '';
		$main_heading     = '';
		$split_heading    = '';

		if ( empty( $settings['sub_heading'] ) and empty( $settings['advanced_heading'] ) and empty( $settings['main_heading'] ) ) {
			return;
		}

		$this->add_render_attribute( 'heading', 'class', 'bdt-heading-title' );

		$this->add_render_attribute(
            [
                'advanced_heading_data' => [
                    'data-settings' => [
                        wp_json_encode(array_filter([
                            "titleMultiColor" => (!empty($settings['title_multi_color'])) ? 'yes' : 'no',
                        ])),
                    ],
                ],
            ]
        );


		if ($settings['sub_heading']) {
			$subh_style = '';
			if ('line' === $settings['sub_heading_style']) {
				if ('left-right' === $settings['sub_heading_style_align']) {
					$subh_style = '<div class="line bdt-button-icon-align-left"></div><div class="line bdt-button-icon-align-right"></div>';
				} elseif ('bottom' === $settings['sub_heading_style_align']) {
					$subh_style = '<div class="line bdt-button-icon-align-'.$settings['sub_heading_style_align'].'"></div>';
				} else {
					$subh_style = '<div class="line bdt-button-icon-align-'.$settings['sub_heading_style_align'].'"></div>';
				}
			}

			$sub_heading = '<div class="bdt-sub-heading"><div class="bdt-sub-heading-content">'.$settings['sub_heading'].'</div>'.$subh_style.'</div> ';
		}

		if ($settings['advanced_heading'] && $settings['advanced_heading_visibility'] == 'yes') {

			$this->add_render_attribute(
				[
					'avd-hclass' => [
						'class' => [
							'bdt-advanced-heading-content',
							$settings['advanced_heading_hide'] ? 'bdt-visible@'. $settings['advanced_heading_hide'] : '',
						],
					],
				]
			);

			$this->add_render_attribute(
				[
					'avd-hcclass' => [
						'class' => [
							$settings['advanced_heading_origin'] ? 'bdt-transform-origin-'.$settings['advanced_heading_origin'] : '',
						],
					],
				]
			);

	   		$advanced_heading = '<div ' . $this->get_render_attribute_string( 'avd-hclass' ) . '><div ' . $this->get_render_attribute_string( 'avd-hcclass' ) . '>' .$settings['advanced_heading']. '</div></div>';
		}

		$this->add_render_attribute( 'main_heading', 'class', 'bdt-main-heading-inner' );
		$this->add_inline_editing_attributes( 'main_heading' );

		$this->add_render_attribute( 'split_heading', 'class', 'bdt-mainh-split-text' );

		if ($settings['main_heading']) :

			$mainh_style = '';

			if ('line' === $settings['main_heading_style']) {
				if ('left-right' === $settings['main_heading_style_align']) {
					$mainh_style = '<span class="line bdt-button-icon-align-left"></span><span class="line bdt-button-icon-align-right"></span>';
				} elseif ('bottom' === $settings['main_heading_style_align']) {
					$mainh_style = '<span class="line bdt-button-icon-align-'.$settings['main_heading_style_align'].'"></span>';
				} else {
					$mainh_style = '<span class="line bdt-button-icon-align-'.$settings['main_heading_style_align'].'"></span>';
				}
			}

			if ( ( 'yes' == $settings['split_main_heading'] ) and ( ! empty($settings['split_text']) ) ) {
				$split_heading = '<span '.$this->get_render_attribute_string( 'split_heading' ).'>' . $settings['split_text'] . '</span>';
			}

			$main_heading = '<span '.$this->get_render_attribute_string( 'main_heading' ).'>' . $settings['main_heading'] . '</span>';

			$main_heading = '<span class="bdt-main-heading">' . $main_heading . $split_heading . $mainh_style . '</span>';

		endif;


		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_render_attribute( 'url', 'href', $settings['link']['url'] );

			if ( $settings['link']['is_external'] ) {
				$this->add_render_attribute( 'url', 'target', '_blank' );
			}

			if ( ! empty( $settings['link']['nofollow'] ) ) {
				$this->add_render_attribute( 'url', 'rel', 'nofollow' );
			}

			$main_heading = sprintf( '<a %1$s>%2$s</a>', $this->get_render_attribute_string( 'url' ), $main_heading );
		}

		$heading_html[] = '<div id ="'.$id.'" class="bdt-advanced-heading" '.$this->get_render_attribute_string('advanced_heading_data').'>';
		
		
		$heading_html[] = $advanced_heading;
		$heading_html[] = $sub_heading;
		$heading_html[] = sprintf( '<%1$s %2$s>%3$s</%1$s>', Utils::get_valid_html_tag($settings['header_size']), $this->get_render_attribute_string( 'heading' ), $main_heading );
		
		$heading_html[] = '</div>';

		echo implode("", $heading_html);

	}


	protected function content_template() {
		?>
		<#
		var subh_style    = '';
		var mainh_style   = '';

		view.addRenderAttribute( 'main_heading', 'class', 'bdt-main-heading-inner' );
		view.addInlineEditingAttributes( 'main_heading' );

		view.addRenderAttribute( 'split_text', 'class', 'bdt-mainh-split-text' );
		view.addInlineEditingAttributes( 'split_text' );

		view.addRenderAttribute( 'main_heading_wrapper', 'class', [ 'bdt-heading-title', 'elementor-size-' + settings.size ] );

		view.addRenderAttribute('advanced_heading_content', 'class', ['bdt-advanced-heading-content'] );

		view.addRenderAttribute('advanced_heading', 'class', 'bdt-transform-origin-' + settings.advanced_heading_origin );

		var avdh_content_print = view.getRenderAttributeString( 'advanced_heading_content' );
		var avdh_transform_print = view.getRenderAttributeString( 'advanced_heading' );

        var headerSizeTag = elementor.helpers.validateHTMLTag( settings.header_size );

		if ( 'line' === settings.sub_heading_style ) {
			if ('left-right' === settings.sub_heading_style_align) {
				subh_style = '<div class="line bdt-button-icon-align-left"></div><div class="line bdt-button-icon-align-right"></div>';
			} else if ('bottom' === settings.sub_heading_style_align) {
				subh_style = '<div class="line bdt-button-icon-align-' + settings.sub_heading_style_align + '"></div>';
			} else {
				subh_style = '<div class="line bdt-button-icon-align-' + settings.sub_heading_style_align + '"></div>';
			}
		}

		if ( 'line' === settings.main_heading_style ) {
			if ('left-right' === settings.main_heading_style_align) {
				mainh_style = '<div class="line bdt-button-icon-align-left"></div><div class="line bdt-button-icon-align-right"></div>';
			} else if ('bottom' === settings.main_heading_style_align) {
				mainh_style = '<div class="line bdt-button-icon-align-' + settings.main_heading_style_align + '"></div>';
			} else {
				mainh_style = '<div class="line bdt-button-icon-align-' + settings.main_heading_style_align + '"></div>';
			}
		}

		#>
		<div class="bdt-advanced-heading">
			<# if ( settings.advanced_heading_visibility == 'yes' ) { #>
			<div <# print(avdh_content_print) #> >
				<div <# print(avdh_transform_print) #>>
					<# print(settings.advanced_heading) #>
				</div>
			</div>
			<# } #>
			
			<# if ( settings.sub_heading != '' ) { #>
			<div class="bdt-sub-heading">
				<div class="bdt-sub-heading-content">
					<# print(settings.sub_heading); #>
				</div>
				<# print(subh_style); #>
			</div>
			<# } #>

			<{{headerSizeTag}} <# print(view.getRenderAttributeString( 'main_heading_wrapper' )) #> >
				<span class="bdt-main-heading">

					<# if ( '' !== settings.link.url ) { #>
						<a href="{{{settings.link.url}}}">
					<# } #>

						
						<span {{{view.getRenderAttributeString( 'main_heading' )}}}><# print(settings.main_heading); #></span>

						<# if ( ( 'yes' == settings.split_main_heading ) && ( '' !== (settings.split_text) ) ) { #>
							<span {{{view.getRenderAttributeString( 'split_text' )}}}><# print(settings.split_text); #></span>
						<# } #>

						<# print(mainh_style); #>
						

					<# if ( '' !== settings.link.url ) { #>
						</a>
					<# } #>

				</span>

			</{{headerSizeTag}}>

		</div>
		<?php
	}


}
