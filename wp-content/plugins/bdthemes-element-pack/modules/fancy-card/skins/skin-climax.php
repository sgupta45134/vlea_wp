<?php
namespace ElementPack\Modules\FancyCard\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;

use Elementor\Icons_Manager;
use ElementPack\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Climax extends Elementor_Skin_Base {

	public function get_id() {
		return 'climax';
	}

	public function get_title() {
		return __( 'Climax', 'bdthemes-element-pack' );
	}

	public function render() {
		$settings  = $this->parent->get_settings_for_display();

		$has_icon  = ! empty( $settings['icon'] );
		$has_image = ! empty( $settings['image']['url'] );

		if ( $has_icon and 'icon' == $settings['icon_type'] ) {
			$this->parent->add_render_attribute( 'font-icon', 'class', $settings['selected_icon'] );
			$this->parent->add_render_attribute( 'font-icon', 'aria-hidden', 'true' );			
		} elseif ( $has_image and 'image' == $settings['icon_type'] ) {
			$this->parent->add_render_attribute( 'image-icon', 'src', $settings['image']['url'] );
			$this->parent->add_render_attribute( 'image-icon', 'alt', $settings['title_text'] );
		}

		$this->parent->add_render_attribute( 'description_text', 'class', 'bdt-fancy-card-description' );

		// $this->parent->add_render_attribute( 'title_text', 'none' );
		// $this->parent->add_render_attribute( 'description_text' );


		$this->parent->add_render_attribute( 'readmore', 'class', ['bdt-fancy-card-readmore', 'bdt-display-inline-block'] );
		
		if ( ! empty( $settings['readmore_link']['url'] ) ) {
			$this->parent->add_render_attribute( 'readmore', 'href', $settings['readmore_link']['url'] );

			if ( $settings['readmore_link']['is_external'] ) {
				$this->parent->add_render_attribute( 'readmore', 'target', '_blank' );
			}

			if ( $settings['readmore_link']['nofollow'] ) {
				$this->parent->add_render_attribute( 'readmore', 'rel', 'nofollow' );
			}

		}

		if ($settings['readmore_attention']) {
			$this->parent->add_render_attribute( 'readmore', 'class', 'bdt-ep-attention-button' );
		}		

		if ( $settings['readmore_hover_animation'] ) {
			$this->parent->add_render_attribute( 'readmore', 'class', 'elementor-animation-' . $settings['readmore_hover_animation'] );
		}

		if ($settings['onclick']) {
			$this->parent->add_render_attribute( 'readmore', 'onclick', $settings['onclick_event'] );
		}

		$this->parent->add_render_attribute( 'fancy-card-title', 'class', 'bdt-fancy-card-title' );
		
		if ('yes' == $settings['title_link'] and $settings['title_link_url']['url']) {

			$target = $settings['title_link_url']['is_external'] ? '_blank' : '_self';

			$this->parent->add_render_attribute( 'fancy-card-title', 'onclick', "window.open('" . $settings['title_link_url']['url'] . "', '$target')" );
		}
		
		if ('yes' == $settings['global_link'] and $settings['global_link_url']['url']) {

			$target = $settings['global_link_url']['is_external'] ? '_blank' : '_self';

			$this->parent->add_render_attribute( 'fancy-card', 'onclick', "window.open('" . $settings['global_link_url']['url'] . "', '$target')" );
		}
		
		if ( ! $has_icon && ! empty( $settings['selected_icon']['value'] ) ) {
			$has_icon = true;
		}

		$this->parent->add_render_attribute( 'toggle_input_position', 'class', 'bdt-checkbox' );
		$this->parent->add_render_attribute( 'toggle_input_position', 'class', 'bdt-position-'. $settings['toggle_position'] );

		$this->parent->add_render_attribute( 'toggle_position', 'class', 'bdt-fancy-card-toggole' );
		$this->parent->add_render_attribute( 'toggle_position', 'class', 'bdt-position-'. $settings['toggle_position'] );

		$this->parent->add_render_attribute( 'fancy-card', 'class', 'bdt-fancy-card bdt-fancy-card-skin-climax' );

		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'fancy-card' ); ?>>

				<input <?php echo $this->parent->get_render_attribute_string( 'toggle_input_position' ); ?> type="checkbox">
				<div <?php echo $this->parent->get_render_attribute_string( 'toggle_position' ); ?>>
					<?php Icons_Manager::render_icon( $settings['toggle_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</div>

			<?php if ( $has_icon or $has_image ) : ?>
				<div class="bdt-fancy-card-icon" data-label="<?php echo $settings['title_text']; ?>" >
					<span class="bdt-icon-wrapper">
						<?php if ( $has_icon and 'icon' == $settings['icon_type'] ) { ?>

							<?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>

						<?php } elseif ( $has_image and 'image' == $settings['icon_type'] ) { ?>
							<img <?php echo $this->parent->get_render_attribute_string( 'image-icon' ); ?>>
						<?php } ?>
					</span>
				</div>
			<?php endif; ?>

			<div class="bdt-fancy-card-content">
				<?php if ( $settings['title_text'] ) : ?>
					<<?php echo Utils::get_valid_html_tag($settings['title_size']); ?> <?php echo $this->parent->get_render_attribute_string( 'fancy-card-title' ); ?>>
						<span <?php //echo $this->parent->get_render_attribute_string( 'title_text' ); ?>>
							<?php echo wp_kses( $settings['title_text'], element_pack_allow_tags('title') ); ?>
						</span>
					</<?php echo Utils::get_valid_html_tag($settings['title_size']); ?>>
				<?php endif; ?>

				<?php if ( $settings['description_text'] ) : ?>
					<div <?php echo $this->parent->get_render_attribute_string( 'description_text' ); ?>>
						<?php echo wp_kses( $settings['description_text'], element_pack_allow_tags('text') ); ?>
					</div>
				<?php endif; ?>

				<?php if ($settings['readmore']) : ?>
					<a <?php echo $this->parent->get_render_attribute_string( 'readmore' ); ?>>
						<?php echo esc_html($settings['readmore_text']); ?>
						
						<?php if ($settings['advanced_readmore_icon']['value']) : ?>

							<span class="bdt-button-icon-align-<?php echo $settings['readmore_icon_align'] ?>">

								<?php Icons_Manager::render_icon( $settings['advanced_readmore_icon'], [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] ); ?>
							
							</span>

						<?php endif; ?>
					</a>
				<?php endif ?>
			</div>

		</div>

		<?php if ( $settings['indicator'] ) : ?>
			<div class="bdt-indicator-svg bdt-svg-style-<?php echo esc_attr($settings['indicator_style']); ?>">
				<?php echo element_pack_svg_icon('arrow-' . $settings['indicator_style']); ?>
			</div>
		<?php endif; ?>

		<?php if ( $settings['badge'] and '' != $settings['badge_text'] ) : ?>
			<div class="bdt-fancy-card-badge bdt-position-<?php echo esc_attr($settings['badge_position']); ?>">
				<span class="bdt-badge bdt-padding-small"><?php echo esc_html($settings['badge_text']); ?></span>
			</div>
		<?php endif; ?>

		<?php
	}
}