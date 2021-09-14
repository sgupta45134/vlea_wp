<?php
namespace ElementPack\Modules\Modal\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

use ElementPack\Element_Pack_Loader;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Modal extends Module_Base {
	public function get_name() {
		return 'bdt-modal';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Modal', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-modal';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'modal', 'lightbox', 'popup' ];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/4qRa-eYDGZU';
	}

	protected function _register_controls() {


		$this->start_controls_section(
			'section_modal_layout',
			[
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Default', 'bdthemes-element-pack' ),
					'splash'  => esc_html__( 'Splash Screen', 'bdthemes-element-pack' ),
					'exit'    => esc_html__( 'Exit Popup', 'bdthemes-element-pack' ),
					'custom'  => esc_html__( 'Custom Link', 'bdthemes-element-pack' ),
				],				
			]
		);

		$this->add_control(
			'modal_custom_id',
			[
				'label'       => esc_html__( 'Modal Selector', 'bdthemes-element-pack' ),
				'description' => __( 'Set your modal selector here. For example: <b>.custom-link</b> or <b>#customLink</b>. Set this selector where you want to link this modal.' , 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '#bdt-custom-modal',
				'condition'   => [
					'layout' => 'custom',
				],
			]
		);

		$this->add_control(
			'splash_after',
			[
				'label'   => esc_html__( 'Splash After (s)', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 5,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 60,
					],
				],
				'condition' => [
					'layout' => 'splash',
				],
			]
		);

		$this->add_control(
			'dev_mode',
			[
				'label'       => __( 'Dev Mode', 'bdthemes-element-pack' ),
				'default'     => 'yes',
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Turn off dev move when your website live so splash screen will show only once.', 'bdthemes-element-pack' ),
				'condition'   => [
					'layout' => ['splash', 'exit'],
				],
			]
		);

		$this->add_control(
			'modal_width',
			[
				'label' => esc_html__( 'Modal Width', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 320,
						'max' => 1200,
					],
				],
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_button',
			[
				'label'     => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'condition' => [
					'layout' => 'default',
				],
			]
		);
		

		$this->add_control(
			'button_text',
			[
				'label'   => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => esc_html__( 'Open Modal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_responsive_control(
			'button_align',
			[
				'label'   => __( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
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
				'prefix_class' => 'elementor%s-align-',
			]
		);

		$this->add_control(
			'size',
			[
				'label'   => __( 'Size', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => element_pack_button_sizes(),
			]
		);

		$this->add_control(
			'modal_button_icon',
			[
				'label'       => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'button_icon',
			]
		);

		$this->add_control(
			'button_icon_align',
			[
				'label'   => esc_html__( 'Icon Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left'  => esc_html__( 'Before', 'bdthemes-element-pack' ),
					'right' => esc_html__( 'After', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'modal_button_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'button_icon_indent',
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
					'modal_button_icon[value]!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button-icon.bdt-button-icon-align-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button-icon.bdt-button-icon-align-left'  => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_modal',
			[
				'label' => esc_html__( 'Modal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'header',
			[
				'label'       => esc_html__( 'Header', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'This is your modal header title', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Modal header title', 'bdthemes-element-pack' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'source',
			[
				'label'   => esc_html__( 'Select Source', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom'    => esc_html__( 'Custom Content', 'bdthemes-element-pack' ),
					"elementor" => esc_html__( 'Elementor Template', 'bdthemes-element-pack' ),
					'anywhere'  => esc_html__( 'AE Template', 'bdthemes-element-pack' ),
				],				
			]
		);

		$this->add_control(
			'content',
			[
				'label'       => esc_html__( 'Custom Content', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::WYSIWYG,
				'dynamic'     => [ 'active' => true ],
				'show_label'  => false,
				'condition'   => ['source' => 'custom'],
				'default'     => esc_html__( 'A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart.', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Modal content goes here', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'template_id',
			[
				'label'       => __( 'Select Template', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '0',
				'options'     => element_pack_et_options(),
				'label_block' => 'true',
				'condition'   => ['source' => "elementor"],
			]
		);

		$this->add_control(
			'anywhere_id',
			[
				'label'       => esc_html__( 'Select Template', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '0',
				'options'     => element_pack_ae_options(),
				'label_block' => 'true',
				'condition'   => ['source' => 'anywhere'],
			]
		);

		$this->add_control(
			'footer',
			[
				'label'       => esc_html__( 'Footer', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'Modal footer goes here', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Modal footer goes here', 'bdthemes-element-pack' ),
				'label_block' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_modal_additional',
			[
				'label' => esc_html__( 'Additional', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'content_overflow',
			[
				'label'       => __( 'Overflow Scroll', 'bdthemes-element-pack' ),
				'description' => __( 'Show scroll bar when you add huge content in modal.', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'close_button',
			[
				'label'       => esc_html__( 'Close Button', 'bdthemes-element-pack' ),
				'description' => esc_html__('When you set modal full screen make sure you don\'t set colse button outside', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => [
					'default' => esc_html__( 'Default', 'bdthemes-element-pack' ),
					'outside' => esc_html__( 'Outside', 'bdthemes-element-pack' ),
					'none'    => esc_html__( 'No Close Button', 'bdthemes-element-pack' ),
				],
			]
		);

		$this->add_control(
			'modal_size',
			[
				'label'        => esc_html__( 'Full screen', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'full',
				'condition'    => [
					'close_button!' => 'outside',
				],
			]
		);

		$this->add_control(
			'modal_center',
			[
				'label'        => esc_html__( 'Center Position', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition'    => [
					'modal_size!' => 'full',
				],
			]
		);

		$this->end_controls_section();

		//Style
		$this->start_controls_section(
			'section_style_button',
			[
				'label'     => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'default',
				],
			]
		);

		$this->add_control(
			'heading_footer_button',
			[
				'label' => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::HEADING,
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
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'        => 'button_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_text_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				//'scheme'   => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button',
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
			'button_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-modal-wrapper .bdt-modal-button:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'tab_content_header',
			[
				'label'     => esc_html__( 'Header', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'header!' => '',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_background',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_align',
			[
				'label'       => esc_html__( 'Titlt Align', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
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
				'default' => 'left',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'        => 'header_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-header',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'header_box_shadow',
				'selector' => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-header',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-title',
				//'scheme'   => Schemes\Typography::TYPOGRAPHY_4,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_modal',
			[
				'label' => esc_html__( 'Modal Content', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-body' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_background',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-body' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector' => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-body',
			]
		);

		$this->end_controls_section();
		
		
		$this->start_controls_section(
			'tab_content_close_button',
			[
				'label'     => esc_html__( 'Close Button', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'close_button!' => 'none',
				],
			]
		);

		$this->add_control(
			'close_button_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'close_button_backgroun_color',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'        => 'close_button_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close',
			]
		);

		$this->add_control(
			'close_button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'close_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'close_button_margin',
			[
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-dialog button.bdt-close' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	
		$this->end_controls_section();

		$this->start_controls_section(
			'tab_content_footer',
			[
				'label'     => esc_html__( 'Footer', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'footer!' => '',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'footer_background',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'footer_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'footer_align',
			[
				'label'       => esc_html__( 'Text Align', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
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
				'default' => 'left',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'        => 'footer_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'footer_box_shadow',
				'selector' => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '.bdt-modal-{{ID}}.bdt-modal .bdt-modal-footer',
				//'scheme'   => Schemes\Typography::TYPOGRAPHY_4,
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$settings  = $this->get_settings_for_display();
		$id        = 'bdt-modal-' . $this->get_id();
		$edit_mode = Element_Pack_Loader::elementor()->editor->is_edit_mode();

        $this->add_render_attribute( 'button', 'class', ['bdt-modal-button', 'elementor-button'] );

        $this->add_render_attribute( 'modal', 'id', $id );
        $this->add_render_attribute( 'modal', 'class', 'bdt-modal-' . $this->get_id() );
        $this->add_render_attribute( 'modal', 'data-bdt-modal', '' );

        if ( $settings['modal_size'] !== 'full' ) {
        	$this->add_render_attribute( 'modal', 'class', 'bdt-modal' );
        } else {
        	$this->add_render_attribute( 'modal', 'class', 'bdt-modal bdt-modal-full' );
        	$this->add_render_attribute( 'modal-body', 'bdt-height-viewport', 'offset-top: .bdt-modal-header; offset-bottom: .bdt-modal-footer' );
        }

        if ( $settings['modal_center'] === 'yes' ) {
        	$this->add_render_attribute( 'modal', 'class', 'bdt-flex-top' );
        }

        $this->add_render_attribute( 'modal-dialog', 'class', 'bdt-modal-dialog' );

        if ($settings['modal_center'] === 'yes' ) {
        	$this->add_render_attribute( 'modal-dialog', 'class', 'bdt-margin-auto-vertical' );
        }

        if ( ! empty( $settings['size'] ) ) {
        	$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['size'] );
        }

        if ( $settings['hover_animation'] ) {
        	$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['hover_animation'] );
        }


        $this->add_render_attribute( 'button', 'data-bdt-toggle', 'target: #'.$id );
        $this->add_render_attribute( 'button', 'href', 'javascript:void(0)' );

        $this->add_render_attribute( 'modal-body', 'class', 'bdt-modal-body' );

        if ( 'yes' === $settings['content_overflow'] ) {
        	$this->add_render_attribute( 'modal-body', 'bdt-overflow-auto', '' );
        }

		$splash_after = ($settings['splash_after']) ?  ($settings['splash_after']['size'] * 1000) : 500;

        $this->add_render_attribute(
        	[
        		'modal' => [
        			'data-settings' => [
        				wp_json_encode(array_filter([
							"id"      => ("custom" == $settings["layout"] and ! empty($settings["modal_custom_id"])) ? $settings["modal_custom_id"] : "bdt-modal-" . $this->get_id(),
							"layout"  => $settings["layout"],
							"delayed" => ("splash" == $settings["layout"]) ? $splash_after : false,
							"dev"     => ("yes" == $settings["dev_mode"]) ? true : false,
        		        ]))
        			]
        		]
        	]
        );

        ?>
		<div class="bdt-modal-wrapper">
		    
		    <?php $this->render_button(); ?>	

	        <div <?php echo $this->get_render_attribute_string( 'modal' ); ?>>
	            <div <?php echo $this->get_render_attribute_string( 'modal-dialog' ); ?>>
	                            
	                <?php if ( $settings['close_button'] != 'none' ) : ?>
	                	<button class="bdt-modal-close-<?php echo esc_attr($settings['close_button']); ?>" type="button" data-bdt-close></button>
	                <?php endif; ?>
	                
	                <?php if ( $settings['header'] ) : ?>
	                    <div class="bdt-modal-header bdt-text-<?php echo esc_attr($settings['header_align']); ?>">
	                    	<h3 class="bdt-modal-title"><?php echo wp_kses_post($settings['header']); ?></h3>
	                    </div>
	                <?php endif; ?>
	                
	                <div <?php echo $this->get_render_attribute_string( 'modal-body' ); ?>>
	                	<?php 
			            	if ( 'custom' == $settings['source'] and !empty( $settings['content'] ) ) {
			            		echo $this->parse_text_editor( $settings['content'] );
			            	} elseif ("elementor" == $settings['source'] and !empty( $settings['template_id'] )) {
			            		echo Element_Pack_Loader::elementor()->frontend->get_builder_content_for_display( $settings['template_id'] );
			            		echo element_pack_template_edit_link( $settings['template_id'] );
			            	} elseif ('anywhere' == $settings['source'] and !empty( $settings['anywhere_id'] )) {
			            		echo Element_Pack_Loader::elementor()->frontend->get_builder_content_for_display( $settings['anywhere_id'] );
			            		echo element_pack_template_edit_link( $settings['anywhere_id'] );
			            	}
			            ?>
	                </div>

	                <?php if ( $settings['footer'] ) : ?>
	                    <div class="bdt-modal-footer bdt-text-<?php echo esc_attr($settings['header_align']); ?>">
	                    	<?php echo wp_kses_post($settings['footer']); ?>
	                    </div>
	                <?php endif; ?>
		        </div>
		    </div>
	    </div>

		<?php
	}

	protected function render_button() {
		$settings = $this->get_settings_for_display();

		if ( 'default' !== $settings['layout'] ) {
			return;
		}

		// $this->add_render_attribute( 'content-wrapper', 'class', 'elementor-button-content-wrapper' );

		// if ( 'left' == $settings['button_icon_align'] or 'right' == $settings['button_icon_align'] ) {
		// 	$this->add_render_attribute( 'content-wrapper', 'class', 'bdt-flex bdt-flex-middle' );
		// }

		$this->add_render_attribute( 'icon-align', 'class', 'bdt-button-icon-align-' . $settings['button_icon_align'] );
		$this->add_render_attribute( 'icon-align', 'class', 'bdt-modal-button-icon elementor-button-icon' );

		$this->add_render_attribute( 'text', 'class', 'elementor-button-text' );

		if ( ! isset( $settings['button_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['button_icon'] = 'fas fa-arrow-right';
		}

		$migrated  = isset( $settings['__fa4_migrated']['modal_button_icon'] );
		$is_new    = empty( $settings['button_icon'] ) && Icons_Manager::is_migration_allowed();

		?>
		<a <?php echo $this->get_render_attribute_string( 'button' ); ?>>
			<!-- <span <?php //echo $this->get_render_attribute_string( 'content-wrapper' ); ?>> -->
				<?php if ( ! empty( $settings['modal_button_icon']['value'] ) ) : ?>

				<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>

					<?php if ( $is_new || $migrated ) :
						Icons_Manager::render_icon( $settings['modal_button_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
					else : ?>
						<i class="<?php echo esc_attr( $settings['button_icon'] ); ?>" aria-hidden="true"></i>
					<?php endif; ?>

				</span>

				<?php endif; ?>
				<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo wp_kses( $settings['button_text'], element_pack_allow_tags('title') ); ?></span>
			<!-- </span> -->
		</a>
		<?php
	}
}
