<?php
namespace ElementPack\Modules\CustomGallery\Skins;
use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Fedara extends Elementor_Skin_Base {
	public function get_id() {
		return 'bdt-fedara';
	}

	public function get_title() {
		return __( 'Fedara', 'bdthemes-element-pack' );
	}

	public function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/bdt-custom-gallery/section_design_layout/after_section_end', [ $this, 'register_fedara_overlay_animation_controls'   ] );
	}

	public function register_fedara_overlay_animation_controls( Module_Base $widget ) {

		$this->parent = $widget;
		$this->start_controls_section(
			'section_style_fedara',
			[
				'label' => __( 'Fedara Style', 'bdthemes-element-pack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'desc_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-custom-gallery-skin-fedara-desc' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-custom-gallery-skin-fedara-desc *' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'desc_padding',
			[
				'label'      => __( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .bdt-custom-gallery-skin-fedara-desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'fedara_item_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-custom-gallery .bdt-custom-gallery-skin-fedara-desc' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				], 
			]
		); 

		$this->add_responsive_control(
			'desc_alignment',
			[
				'label'       => __( 'Alignment', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
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
				'prefix_class' => 'bdt-custom-gallery-skin-fedara-style-',
				'selectors' => [
					'{{WRAPPER}} .bdt-custom-gallery-skin-fedara-desc' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_overlay($content, $element_key) {
		$settings                    = $this->parent->get_settings();

        if ( ! $settings['show_lightbox'] ) {
            return;
        }

		$this->parent->add_render_attribute(
			[
				'overlay-settings' => [
					'class' => [
						'bdt-overlay',
						'bdt-overlay-default',
						'bdt-position-cover',
						$settings['overlay_animation'] ? 'bdt-transition-' . $settings['overlay_animation'] : ''
					],
				],
			], '', '', true
		);

		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'overlay-settings' ); ?>>
			<div class="bdt-custom-gallery-content">
				<div class="bdt-custom-gallery-content-inner">
				
					
					<?php if ( 'yes' == $settings['show_lightbox'] )  : 

						//$this->parent->rendar_link( $content, $element_key );

						$image_url = wp_get_attachment_image_src( $content['gallery_image']['id'], 'full');
						$this->parent->add_render_attribute($element_key, 'class', ['bdt-gallery-item-link', 'bdt-gallery-lightbox-item'], true );						

						$icon = $settings['icon'] ? : 'plus';

						?>
						<div class="bdt-flex-inline bdt-gallery-item-link-wrapper">
							<a <?php echo $this->parent->get_render_attribute_string( $element_key ); ?>>
								<?php if ( 'icon' == $settings['link_type'] ) : ?>
									<span data-bdt-icon="icon: <?php echo esc_attr($icon); ?>; ratio: 1.6"></span>
								<?php elseif ( 'text' == $settings['link_type'] ) : ?>
									<span class="bdt-text"><?php esc_html_e( 'ZOOM', 'bdthemes-element-pack' ); ?></span>
								<?php endif;?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_title($title) {
		if ( ! $this->parent->get_settings( 'show_title' ) ) {
			return;
		}

		$tag = $this->parent->get_settings( 'title_tag' );
		?>
		<<?php echo esc_html($tag) ?> class="bdt-gallery-item-title">
			<?php echo esc_html($title['image_title']); ?>
		</<?php echo esc_html($tag) ?>>
		<?php
	}

	public function render_text($text) {
		if ( ! $this->parent->get_settings( 'show_text' ) ) {
			return;
		}

		?>
		<div class="bdt-gallery-item-text"><?php echo wp_kses_post($text['image_text']); ?></div>
		<?php
	}

	public function render_desc($content) {

		$settings = $this->parent->get_settings();

        if ( '' == $settings['show_title'] and '' == $settings['show_text'] ) {
            return;
        }

		?>
		<div class="bdt-custom-gallery-skin-fedara-desc bdt-padding-small">
			
			<?php
			$this->render_title($content); 
			$this->render_text($content);
			?>
			
		</div>
		<?php
	}

	public function render() {
		$settings = $this->parent->get_settings();

		$this->parent->add_render_attribute('custom-gallery-item', 'class', 'bdt-gallery-item');
		$this->parent->add_render_attribute('custom-gallery-item', 'class', 'bdt-width-1-'. $settings['columns_mobile']);
		$this->parent->add_render_attribute('custom-gallery-item', 'class', 'bdt-width-1-'. $settings['columns_tablet'] .'@s');
		$this->parent->add_render_attribute('custom-gallery-item', 'class', 'bdt-width-1-'. $settings['columns'] .'@m');

		$this->parent->render_header('fedara');
		
		$this->parent->add_render_attribute('custom-gallery-item-inner', 'class', 'bdt-custom-gallery-item-inner');
		
		if ('yes' === $settings['tilt_show']) {
			$this->parent->add_render_attribute('custom-gallery-item-inner', 'data-tilt', '');
		}

		foreach ( $settings['gallery'] as $index => $item ) :

			?>
			<div <?php echo $this->parent->get_render_attribute_string( 'custom-gallery-item' ); ?>>
				<div <?php echo $this->parent->get_render_attribute_string( 'custom-gallery-item-inner' ); ?>>

					<?php $this->parent->rendar_link($item, 'gallery-item-' . $index); ?>
					
					<?php if ($settings['direct_link']) : ?>
						<?php 
							if ( $settings['external_link'] ) {
								$this->parent->add_render_attribute( 'gallery-item-' . $index, 'target', '_blank' );
							} 
						?>
						<a <?php echo $this->parent->get_render_attribute_string( 'gallery-item-' . $index ); ?>>
					<?php endif; ?>

					<div class="bdt-custom-gallery-inner bdt-image-mask bdt-transition-toggle bdt-position-relative">
						<?php 
						$this->parent->render_thumbnail($item, 'gallery-item-' . $index);
						$this->render_overlay($item, 'gallery-item-' . $index);
						?>
					</div>

					<?php if ($settings['direct_link']) : ?>
						</a>
					<?php endif; ?>

					<?php $this->render_desc($item); ?>
				</div>
			</div>

		<?php endforeach; ?>

		<?php $this->parent->render_footer($item);
	}
}

