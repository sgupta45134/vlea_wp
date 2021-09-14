<?php
namespace ElementPack\Modules\CallOut\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use ElementPack\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Call_Out extends Module_Base {

	public function get_name() {
		return 'bdt-call-out';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Call Out', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-call-out';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'callout', 'action' ];
	}

	public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-all-styles'];
        } else {
            return [ 'ep-call-out' ];
        }
    }

	public function get_custom_help_url() {
		return 'https://youtu.be/1tNppRHvSvQ';
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content_layout',
			[
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'This is your call to action title', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Call to action title', 'bdthemes-element-pack' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'description',
			[
				'label'       => esc_html__( 'Description', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart.', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Call to action description', 'bdthemes-element-pack' ),
				'label_block' => true,
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_button',
			[
				'label' => esc_html__( 'Button', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => esc_html__( 'Click Here', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'http://your-link.com',
				'default'     => [
					'url'         => '#',
					'is_external' => '',
				],
			]
		);

		$this->add_control(
			'add_custom_attributes',
			[
				'label'     => __( 'Add Custom Attributes', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'custom_attributes',
			[
				'label' => __( 'Custom Attributes', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::TEXTAREA,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'key|value', 'bdthemes-element-pack' ),
				'description' => sprintf( __( 'Set custom attributes for the price table button tag. Each attribute in a separate line. Separate attribute key from the value using %s character.', 'bdthemes-element-pack' ), '<code>|</code>' ),
				'classes' => 'elementor-control-direction-ltr',
				'condition' => ['add_custom_attributes' => 'yes']
			]
		);

		$this->add_control(
			'button_align',
			[
				'label'   => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left'   => esc_html__( 'Left', 'bdthemes-element-pack' ),
					'right'  => esc_html__( 'Right', 'bdthemes-element-pack' ),
					'center' => esc_html__( 'Center', 'bdthemes-element-pack' ),
				],
			]
		);

		$this->add_control(
			'callout_icon',
			[
				'label'       => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::ICONS,
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
					'left'  => esc_html__( 'Left', 'bdthemes-element-pack' ),
					'right' => esc_html__( 'Right', 'bdthemes-element-pack' ),
				],
				'condition' => [
					'callout_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'icon_indent',
			[
				'label' => esc_html__( 'Icon Spacing', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 8,
				],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'condition' => [
					'callout_icon[value]!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-flex-align-right' => is_rtl() ? 'margin-right: {{SIZE}}{{UNIT}};' : 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-callout .bdt-flex-align-left'  => is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};',
				],
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
			'title_heading',
			[
				'label'     => esc_html__( 'Title', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'label' => __( 'Text Shadow', 'bdthemes-element-pack' ) . BDTEP_NC,
				'selector' => '{{WRAPPER}} .bdt-callout .bdt-callout-title',
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'title_typography',
				'selector'  => '{{WRAPPER}} .bdt-callout .bdt-callout-title',
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'description_heading',
			[
				'label'     => esc_html__( 'Description', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'description!' => '',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'description_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-description' => 'color: {{VALUE}};',
				],
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'description_typography',
				'selector'  => '{{WRAPPER}} .bdt-callout .bdt-callout-description',
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'description_spacing',
			[
				'label'     => __( 'Spacing', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type'      => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-description' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'attention_button',
			[
				'label' => __( 'Attention', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label'     => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name'        => 'button_border',
				'label'       => esc_html__( 'Border', 'bdthemes-element-pack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-callout a.bdt-callout-button',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_text_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .bdt-callout a.bdt-callout-button',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'button_typography',
				'label'     => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				'selector'  => '{{WRAPPER}} .bdt-callout a.bdt-callout-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label'     => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label'     => esc_html__( 'Background', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'button_border_border!' => ''
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-callout a.bdt-callout-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .bdt-callout a.bdt-callout-button:hover',
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_icon',
			[
				'label'     => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'callout_icon[value]!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_callout_icon_style' );

		$this->start_controls_tab(
			'tab_callout_icon_normal',
			[
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'callout_button_icon_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon svg *' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'callout_icon_background',
				'selector'  => '{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'callout_icon_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon',
			]
		);

		$this->add_responsive_control(
			'callout_icon_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'callout_icon_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'callout_icon_size',
			[
				'label' => __( 'Icon Size', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 10,
						'max'  => 100,
					],
				],				
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button .bdt-callout-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_callout_icon_hover',
			[
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'callout_button_hover_icon_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button:hover .bdt-callout-button-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-callout .bdt-callout-button:hover .bdt-callout-button-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'callout_icon_hover_background',
				'selector'  => '{{WRAPPER}} .bdt-callout .bdt-callout-button:hover .bdt-callout-button-icon',
			]
		);

		$this->add_control(
			'icon_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'icon_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-callout .bdt-callout-button:hover .bdt-callout-button-icon' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function render() {
		$settings  = $this->get_settings_for_display();
		
		$external  = ($settings['link']['is_external']) ? "_blank" : "_self";
		$link_url  = empty( $settings['link']['url'] ) ? '#' : $settings['link']['url'];
		$animation = ($settings['button_hover_animation']) ? ' elementor-animation-'.$settings['button_hover_animation'] : '';
		$attention = ($settings['attention_button']) ? ' bdt-ep-attention-button' : '';

		if ($settings['attention_button']) {
			$this->add_render_attribute( 'bdt_callout', 'class', 'bdt-ep-attention-button' );
		}

		$this->add_render_attribute( 'callout_title', 'class', 'bdt-callout-title' );

		$this->add_render_attribute( 'callout', 'class', ['bdt-callout', 'bdt-callout-button-align-' . esc_attr($settings['button_align'])] );

		if ( 'center' !== $settings['button_align']) {
			$this->add_render_attribute( 'callout', 'class', ['bdt-grid', 'bdt-grid-large', 'bdt-flex-middle'] );
		}

		if ( 'left' == $settings['icon_align'] or 'right' == $settings['icon_align'] ) {
			$this->add_render_attribute( 'callout-button', 'class', 'bdt-flex bdt-flex-middle' );
		}

		if ( $settings['add_custom_attributes'] and ! empty( $settings['custom_attributes'] ) ) {
			$attributes = explode( "\n", $settings['custom_attributes'] );

			$reserved_attr = [ 'href', 'target' ];

			foreach ( $attributes as $attribute ) {
				if ( ! empty( $attribute ) ) {
					$attr = explode( '|', $attribute, 2 );
					if ( ! isset( $attr[1] ) ) {
						$attr[1] = '';
					}

					if ( ! in_array( strtolower( $attr[0] ), $reserved_attr ) ) {
						$this->add_render_attribute( 'custom-button', trim( $attr[0] ), trim( $attr[1] ) );
					}
				}
			}
		}
		
		if ( ! isset( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['icon'] = 'fas fa-arrow-right';
		}

		$migrated  = isset( $settings['__fa4_migrated']['callout_icon'] );
		$is_new    = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

		?>
        <div <?php echo $this->get_render_attribute_string( 'callout' ); ?>>
            <div class="bdt-width-expand bdt-first-column">
            	<?php if ($settings['title']) : ?>
					<<?php echo Utils::get_valid_html_tag($settings['title_size']); ?> <?php echo $this->get_render_attribute_string( 'callout_title' ); ?>>
						<?php echo esc_html($settings['title']); ?>
					</<?php echo Utils::get_valid_html_tag($settings['title_size']); ?>>
            	<?php endif; ?>
				<?php if ($settings['description']) : ?>
                	<div class="bdt-callout-description"><?php echo strip_tags($settings['description']); ?></div>
				<?php endif; ?>
           </div>

            <div class="bdt-width-auto@m">
                <a <?php echo $this->get_render_attribute_string( 'custom-button' ); ?> class="bdt-callout-button<?php echo esc_html($animation.$attention); ?>" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($external); ?>">

	                <span <?php echo $this->get_render_attribute_string( 'callout-button' ); ?>>

	                	<?php echo esc_html( $settings['button_text'] ); ?>
	                	
	                	<?php if ($settings['callout_icon']['value']) : ?>
							<span class="bdt-callout-button-icon bdt-flex-align-<?php echo esc_html($settings['icon_align']); ?>">

								<?php if ( $is_new || $migrated ) :
									Icons_Manager::render_icon( $settings['callout_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
								else : ?>
									<i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>

							</span>
						<?php endif; ?>
					</span>

                </a>
            </div>
        </div>
		<?php
	}

	public function content_template() {
		?>

		<#
			var animation  = ( settings.button_hover_animation ) ? ' elementor-animation-' + settings.button_hover_animation : '';
			var attention  = ( settings.attention_button ) ? ' bdt-ep-attention-button' : '';
			var grid_class = ( 'center' !== settings.button_align ) ? ' bdt-grid bdt-grid-large bdt-flex-middle' : '';

			if (settings.icon_align == 'left' || settings.icon_align == 'right') {
				view.addRenderAttribute( 'callout-button', 'class', 'bdt-flex bdt-flex-middle' );
			}

			iconHTML = elementor.helpers.renderIcon( view, settings.callout_icon, { 'aria-hidden': true }, 'i' , 'object' );

			migrated = elementor.helpers.isIconMigrated( settings, 'callout_icon' );

		#>

        <div class="bdt-callout bdt-callout-button-align-{{ settings.button_align }}{{ grid_class }}">
            <div class="bdt-width-expand bdt-first-column">
            	<# 
	            	if ('' !== settings.title) { 
	                	print('<' + elementor.helpers.validateHTMLTag(settings.title_size) + ' class="bdt-callout-title">' + settings.title +'</'+ elementor.helpers.validateHTMLTag(settings.title_size) +'>');
	            	}
					if ('' !== settings.description) {
	                	print('<div class="bdt-callout-description">' + settings.description + '</div>');
					}
				#>
           </div>

            <div class="bdt-width-auto@m">
                <a class="bdt-callout-button{{animation}}{{attention}}" href="{{ settings.link }}" target="{{ settings.link.is_external }}">
					<span {{{ view.getRenderAttributeString( 'callout-button' ) }}}>
                	{{ settings.button_text }}
						<# if (settings.callout_icon.value) { #>
							<span class="bdt-callout-button-icon bdt-flex-align-{{settings.icon_align}}">

								<# if ( iconHTML && iconHTML.rendered && ( ! settings.icon || migrated ) ) { #>
									{{{ iconHTML.value }}}
								<# } else { #>
									<i class="{{ settings.icon }}" aria-hidden="true"></i>
								<# } #>

							</span>
						<# } #>
					</span>

                </a>
            </div>
        </div>
        <?php
	}
}
