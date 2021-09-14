<?php
namespace ElementPack\Modules\FeaturedBox\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use ElementPack\Utils;

use ElementPack\Modules\FeaturedBox\Skins;
use ElementPack\Traits\Global_Mask_Controls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Featured_Box extends Module_Base {

	use Global_Mask_Controls;

	public function get_name() {
		return 'bdt-featured-box';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Featured Box', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-featured-box';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'advanced', 'features', 'image', 'services', 'card', 'box' ];
	}

	public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-all-styles'];
        } else {
            return [ 'ep-featured-box' ];
        }
    }

	public function get_custom_help_url() {
        return 'https://youtu.be/Qe4yYXajhQg';
    }

	public function _register_skins() {
		$this->add_skin( new Skins\Skin_Split( $this ) );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content_featured_layout',
			[
				'label' => __( 'Layout', 'bdthemes-element-pack' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image',
			[
				'label'       => __( 'Image', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::MEDIA,
				'render_type' => 'template',
				'default'     => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'         => 'thumbnail_size',
				'default'      => 'full',
			]
		);

		$this->add_control(
            'image_mask_popover',
            [
                'label'        => esc_html__('Image Mask', 'bdthemes-element-pack') . BDTEP_NC,
                'type'         => Controls_Manager::POPOVER_TOGGLE,
                'render_type'  => 'ui',
                'return_value' => 'yes',
            ]
        );

        //Global Image Mask Controls
        $this->register_image_mask_controls();

		$this->add_control(
			'title_text',
			[
				'label'   => __( 'Title', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default'     => __( 'Featured Box Title', 'bdthemes-element-pack' ),
				'placeholder' => __( 'Enter your title', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'title_link',
			[
				'label'        => __( 'Title Link', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'bdt-title-link-'
			]
		);


		$this->add_control(
			'title_link_url',
			[
				'label'       => __( 'Title Link URL', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'http://your-link.com',
				'condition'   => [
					'title_link' => 'yes'
				]
			]
		);

		$this->add_control(
			'show_sub_title',
			[
				'label'        => __( 'Show Sub Title', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'sub_title_text',
			[
				'label'   => __( 'Sub Title', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default'     => __( 'This is a Label', 'bdthemes-element-pack' ),
				'placeholder' => __( 'Enter your sub title', 'bdthemes-element-pack' ),
				'label_block' => true,
				'condition'	  => [
					'show_sub_title'	=> 'yes',
				],
			]
		);

		$this->add_control(
			'description_text',
			[
				'label'   => __( 'Text', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => [
					'active' => true,
				],
				'default'     => __( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'bdthemes-element-pack' ),
				'placeholder' => __( 'Enter your description', 'bdthemes-element-pack' ),
				'rows'        => 10,
			]
		);

		$this->add_control(
			'readmore',
			[
				'label'     => __( 'Read More Button', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'badge',
			[
				'label' => __( 'Badge', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'title_size',
			[
				'label'   => __( 'Title HTML Tag', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => element_pack_title_tags(),
			]
		);

		$this->add_control(
			'hr_divider',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'text_align',
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
					'justify' => [
						'title' => __( 'Justified', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content' => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'content_position',
			[
				'label'   => esc_html__( 'Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center-left',
				'options' => element_pack_thumbnavs_position(),
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'skin_content_position',
			[
				'label'   => __( 'Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'   => __( 'Left', 'bdthemes-element-pack' ),
					'right'  => __( 'Right', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'_skin' => 'split',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_readmore',
			[
				'label'     => __( 'Read More', 'bdthemes-element-pack' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'readmore' => 'yes',
				],
			]
		);

		$this->add_control(
			'readmore_text',
			[
				'label'       => __( 'Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => __( 'Read More', 'bdthemes-element-pack' ),
				'placeholder' => __( 'Read More', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'readmore_link',
			[
				'label'     => __( 'Link to', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::URL,
				'separator' => 'before',
				'dynamic'   => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'bdthemes-element-pack' ),
				'default'     => [
					'url' => '#',
				],
				'condition' => [
					'readmore'       => 'yes',
				]
			]
		);

		$this->add_control(
			'onclick',
			[
				'label'     => esc_html__( 'OnClick', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'readmore'       => 'yes',
				]
			]
		);

		$this->add_control(
			'onclick_event',
			[
				'label'       => esc_html__( 'OnClick Event', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'myFunction()',
				'description' => sprintf( esc_html__('For details please look <a href="%s" target="_blank">here</a>'), 'https://www.w3schools.com/jsref/event_onclick.asp' ),
				'condition' => [
					'readmore'       => 'yes',
					'onclick'        => 'yes'
				]
			]
		);

		$this->add_control(
			'advanced_readmore_icon',
			[
				'label'       => __( 'Icon', 'bdthemes-element-pack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'readmore_icon',
				'separator'   => 'before',
				'label_block' => true,
				'condition'   => [
					'readmore'       => 'yes'
				]
			]
		);

		$this->add_control(
			'readmore_icon_align',
			[
				'label'   => __( 'Icon Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left'   => __( 'Left', 'bdthemes-element-pack' ),
					'right'  => __( 'Right', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'advanced_readmore_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'readmore_icon_indent',
			[
				'label' => __( 'Icon Spacing', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'default' => [
					'size' => 8,
				],
				'condition' => [
					'advanced_readmore_icon[value]!' => '',
					'readmore_text!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-feature-readmore .bdt-button-icon-align-right' => is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-feature-readmore .bdt-button-icon-align-left'  => is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_badge',
			[
				'label'     => __( 'Badge', 'bdthemes-element-pack' ),
				'condition' => [
					'badge' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_text',
			[
				'label'       => __( 'Badge Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'POPULAR',
				'placeholder' => 'Type Badge Title',
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'badge_position',
			[
				'label'   => esc_html__( 'Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top-right',
				'options' => element_pack_position(),
			]
		);

		$this->add_control(
            'badge_offset_toggle',
            [
                'label' => __( 'Offset', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::POPOVER_TOGGLE,
                'label_off' => __( 'None', 'bdthemes-element-pack' ),
                'label_on' => __( 'Custom', 'bdthemes-element-pack' ),
                'return_value' => 'yes',
            ]
        );

        $this->start_popover();

        $this->add_responsive_control(
			'badge_horizontal_offset',
			[
				'label' => __( 'Horizontal Offset', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
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
						'min'  => -300,
						'step' => 2,
						'max'  => 300,
					],
                ],
                'condition' => [
                    'badge_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
			]
		);

		$this->add_responsive_control(
			'badge_vertical_offset',
			[
				'label' => __( 'Vertical Offset', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
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
						'min'  => -300,
						'step' => 2,
						'max'  => 300,
					],
                ],
                'condition' => [
                    'badge_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
			]
		);

		$this->add_responsive_control(
			'badge_rotate',
			[
				'label'   => esc_html__( 'Rotate', 'bdthemes-element-pack' ),
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
						'min'  => -360,
						'max'  => 360,
						'step' => 5,
					],
                ],
                'condition' => [
                    'badge_offset_toggle' => 'yes'
                ],
                'render_type' => 'ui',
				'selectors' => [
					'(desktop){{WRAPPER}} .bdt-featured-box-badge' => 'transform: translate({{badge_horizontal_offset.SIZE}}px, {{badge_vertical_offset.SIZE}}px) rotate({{SIZE}}deg);',
					'(tablet){{WRAPPER}} .bdt-featured-box-badge' => 'transform: translate({{badge_horizontal_offset_tablet.SIZE}}px, {{badge_vertical_offset_tablet.SIZE}}px) rotate({{SIZE}}deg);',
					'(mobile){{WRAPPER}} .bdt-featured-box-badge' => 'transform: translate({{badge_horizontal_offset_mobile.SIZE}}px, {{badge_vertical_offset_mobile.SIZE}}px) rotate({{SIZE}}deg);',
				],
			]
		);

        $this->end_popover();

		$this->end_controls_section();

		//Style
		$this->start_controls_section(
			'section_style_feature',
			[
				'label'      => __( 'Image', 'bdthemes-element-pack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label'      => esc_html__('Spacing', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_control(
			'hr_divider_2',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->start_controls_tabs( 'tabs_feature_image' );

		$this->start_controls_tab(
			'tab_image_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'      => 'css_filters',
				'selector'  => '{{WRAPPER}} .bdt-featured-box .bdt-feature-image img',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'img_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-image img'
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'image_border',
				'selector'    => '{{WRAPPER}} .bdt-featured-box .bdt-feature-image img'
			]
		);

		$this->add_responsive_control(
			'iamge_radius',
			[
				'label'      => esc_html__('Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_opacity',
			[
				'label' => __( 'Opacity', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-image img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'image_hover_transition',
			[
				'label' => __( 'Transition Duration', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'max' => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-image img' => 'transition-duration: {{SIZE}}s',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_image_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'      => 'css_filters_hover',
				'selector'  => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-image img',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'icon_hover_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-image img'
			]
		);

		$this->add_control(
			'image_hover_border_color',
			[
				'label'     => __( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-image img'  => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'image_border_border!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'icon_hover_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				]
			]
		);

		$this->add_control(
			'image_opacity_hover',
			[
				'label' => __( 'Opacity', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-image img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Title', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		); 

		$this->start_controls_tabs( 'tabs_title_style' );

		$this->start_controls_tab(
			'tab_title_style_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'title_bottom_space',
			[
				'label' => __( 'Spacing', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-title' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_text_stroke' => 'yes'
				]
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-title' => 'color: {{VALUE}}; -webkit-text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow',
				'label' => __( 'Text Shadow', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_style_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow_hover',
				'label' => __( 'Text Shadow', 'bdthemes-element-pack' ),
				'selector' => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-title',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_sub_title',
			[
				'label' => __( 'Sub Title', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'show_sub_title'	=> 'yes',
				],
			]
		); 

		$this->start_controls_tabs( 'tabs_sub_title_style' );

		$this->start_controls_tab(
			'tab_sub_title_style_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'sub_title_bottom_space',
			[
				'label' => __( 'Spacing', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-sub-title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'sub_title_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-sub-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_title_typography',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-sub-title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_sub_title_style_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'sub_title_color_hover',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-sub-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sub_title_typography_hover',
				'selector' => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-sub-title',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_description',
			[
				'label' => __( 'Text', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		); 

		$this->start_controls_tabs( 'tabs_description_style' );

		$this->start_controls_tab(
			'tab_description_style_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'description_color',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-description',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_description_style_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'description_color_hover',
			[
				'label'     => __( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'description_typography_hover',
				'selector' => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content .bdt-feature-description',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_readmore',
			[
				'label'     => __( 'Read More', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'readmore'       => 'yes',
				],				
			]
		);

		$this->add_control(
			'readmore_attention',
			[
				'label' => __( 'Attention', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->add_responsive_control(
			'button_top_space',
			[
				'label' => __( 'Spacing', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button' => 'padding-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hr_divider_3',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->start_controls_tabs( 'tabs_readmore_style' );

		$this->start_controls_tab(
			'tab_readmore_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'readmore_text_color',
			[
				'label'     => __( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'readmore_background',
				'selector'  => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'readmore_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore'
			]
		);

		$this->add_responsive_control(
			'readmore_radius',
			[
				'label'      => __( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'readmore_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore',
			]
		);

		$this->add_responsive_control(
			'readmore_padding',
			[
				'label'      => __( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'readmore_typography',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_readmore_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'readmore_hover_text_color',
			[
				'label'     => __( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'readmore_hover_background',
				'selector'  => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore:hover',
			]
		);

		$this->add_control(
			'readmore_hover_border_color',
			[
				'label'     => __( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'readmore_border_border!' => ''
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'readmore_hover_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content .bdt-feature-button .bdt-feature-readmore:hover',
			]
		);

		$this->add_control(
			'readmore_hover_animation',
			[
				'label' => __( 'Hover Animation', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_badge',
			[
				'label'     => __( 'Badge', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'badge' => 'yes',
				],
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label'     => __( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box-badge span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'badge_background',
				'selector'  => '{{WRAPPER}} .bdt-featured-box-badge span',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'badge_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-featured-box-badge span'
			]
		);

		$this->add_responsive_control(
			'badge_radius',
			[
				'label'      => __( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box-badge span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'badge_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box-badge span',
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label'      => __( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box-badge span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_margin',
			[
				'label'      => __( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-featured-box-badge.bdt-position-small' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .bdt-featured-box-badge span',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_additional',
			[
				'label' => __( 'Additional', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_heading',
			[
				'label'      => esc_html__('Content Style', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'hr_divider_5',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'content_max_width',
			[
				'label' => __( 'Max Width', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 270,
						'max' => 1200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__('Padding', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
				]
			]
		);

		$this->add_control(
			'hr_divider_4',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->start_controls_tabs( 'tabs_content_style' );

		$this->start_controls_tab(
			'tab_content_normal',
			[
				'label' => __( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
            'glassmorphism_effect',
            [
                'label' => esc_html__('Glassmorphism', 'bdthemes-element-pack') . BDTEP_NC,
				'type'  => Controls_Manager::SWITCHER,
				'description' => sprintf( __( 'This feature will not work in the Firefox browser untill you enable browser compatibility so please %1s look here %2s', 'bdthemes-element-pack' ), '<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter#Browser_compatibility" target="_blank">', '</a>' ),
            
            ]
		);
		
		$this->add_control(
            'glassmorphism_blur_level',
            [
                'label'       => __('Blur Level', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [
                    'px' => [
                        'min'  => 0,
                        'step' => 1,
                        'max'  => 50,
                    ]
                ],
                'default'     => [
                    'size' => 5
                ],
                'selectors'   => [
                    '{{WRAPPER}} .bdt-featured-box .bdt-feature-content' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
				],
				'condition' => [
					'glassmorphism_effect' => 'yes',
				]
            ]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'content_background',
				'selector'  => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'content_border',
				'selector'    => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content'
			]
		);

		$this->add_responsive_control(
			'content_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-featured-box .bdt-feature-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box .bdt-feature-content'
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_content_hover',
			[
				'label' => __( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'content_hover_background',
				'selector'  => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content',
			]
		);

		$this->add_control(
			'content_hover_border_color',
			[
				'label'     => __( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content'  => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'content_border_border!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_hover_shadow',
				'selector' => '{{WRAPPER}} .bdt-featured-box:hover .bdt-feature-content'
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function render_featured_badge() {
		$settings  = $this->get_settings_for_display();

		?>
		<?php if ( $settings['badge'] and '' != $settings['badge_text'] ) : ?>
			<div class="bdt-featured-box-badge bdt-position-small bdt-position-<?php echo esc_attr($settings['badge_position']); ?>">
				<span class="bdt-badge bdt-padding-small"><?php echo esc_html($settings['badge_text']); ?></span>
			</div>
		<?php endif; ?>
		<?php
	}

	public function render_featured_image() {
		$settings  = $this->get_settings_for_display();

		$thumb_url = Group_Control_Image_Size::get_attachment_image_src( $settings['image']['id'], 'thumbnail_size', $settings);

        if ( ! $thumb_url ) {
        	$thumb_url = $settings['image']['url'];
		}
		?>
		<div class="bdt-feature-image">
			<div class="bdt-position-relative bdt-image-mask">
				<img src="<?php echo esc_url( $thumb_url); ?>" alt="<?php echo esc_html( $settings['title_text'] ); ?>">
				<?php $this->render_featured_badge(); ?>
			</div>
		</div>
		<?php
	}

	public function render_featured_content() {
		$settings  = $this->get_settings_for_display();

		$this->add_render_attribute( 'feature-title', 'class', 'bdt-feature-title' );
		if ('yes' == $settings['title_link'] and $settings['title_link_url']['url']) {

			$target = $settings['title_link_url']['is_external'] ? '_blank' : '_self';

			$this->add_render_attribute( 'feature-title', 'onclick', "window.open('" . $settings['title_link_url']['url'] . "', '$target')" );
		}

		$this->add_render_attribute( 'feature-sub-title', 'class', 'bdt-feature-sub-title' );

		$this->add_render_attribute( 'description_text', 'class', 'bdt-feature-description' );

		$this->add_inline_editing_attributes( 'title_text', 'none' );
		$this->add_inline_editing_attributes( 'description_text' );


		$this->add_render_attribute( 'readmore', 'class', ['bdt-feature-readmore', 'bdt-display-inline-block'] );
		
		if ( ! empty( $settings['readmore_link']['url'] ) ) {
			$this->add_render_attribute( 'readmore', 'href', $settings['readmore_link']['url'] );

			if ( $settings['readmore_link']['is_external'] ) {
				$this->add_render_attribute( 'readmore', 'target', '_blank' );
			}

			if ( $settings['readmore_link']['nofollow'] ) {
				$this->add_render_attribute( 'readmore', 'rel', 'nofollow' );
			}

		}

		if ($settings['readmore_attention']) {
			$this->add_render_attribute( 'readmore', 'class', 'bdt-ep-attention-button' );
		}		

		if ( $settings['readmore_hover_animation'] ) {
			$this->add_render_attribute( 'readmore', 'class', 'elementor-animation-' . $settings['readmore_hover_animation'] );
		}

		if ($settings['onclick']) {
			$this->add_render_attribute( 'readmore', 'onclick', $settings['onclick_event'] );
		}
		
		?>
		<?php if ( 'yes' == $settings['show_sub_title'] ) : ?>
			<div <?php echo $this->get_render_attribute_string( 'feature-sub-title' ); ?>>
				<?php echo wp_kses( $settings['sub_title_text'], element_pack_allow_tags('title') ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $settings['title_text'] ) : ?>
			<<?php echo Utils::get_valid_html_tag($settings['title_size']); ?> <?php echo $this->get_render_attribute_string( 'feature-title' ); ?>>
				<span <?php echo $this->get_render_attribute_string( 'title_text' ); ?>>
					<?php echo wp_kses_post( $settings['title_text'], element_pack_allow_tags('title') ); ?>
				</span>
			</<?php echo Utils::get_valid_html_tag($settings['title_size']); ?>>
		<?php endif; ?>

		<?php if ( $settings['description_text'] ) : ?>
			<div <?php echo $this->get_render_attribute_string( 'description_text' ); ?>>
				<?php echo wp_kses( $settings['description_text'], element_pack_allow_tags('text') ); ?>
			</div>
		<?php endif; ?>

		<?php if ($settings['readmore']) : ?>
			<div class="bdt-feature-button">
				<a <?php echo $this->get_render_attribute_string( 'readmore' ); ?>>
					<?php echo esc_html($settings['readmore_text']); ?>
					<?php if ($settings['advanced_readmore_icon']['value']) : ?>
						<span class="bdt-button-icon-align-<?php echo $settings['readmore_icon_align'] ?>">
							<?php Icons_Manager::render_icon( $settings['advanced_readmore_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] ); ?>
						</span>
					<?php endif; ?>
				</a>
			</div>
		<?php endif ?>
		<?php
	}

	public function render() {
		$settings  = $this->get_settings_for_display();

		$this->add_render_attribute( 'featured-box', 'class', ['bdt-featured-box', 'bdt-featured-box-default'] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'featured-box' ); ?>>

			<?php $this->render_featured_image(); ?>
			
			<div class="bdt-feature-content bdt-position-<?php echo esc_attr($settings['content_position']); ?>">

				<?php $this->render_featured_content(); ?>
				
			</div>

		</div>

		<?php
	}
}
