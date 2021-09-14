<?php
namespace ElementPack\Modules\PostGrid\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Modern extends Elementor_Skin_Base {

	public function get_id() {
		return 'bdt-modern';
	}

	public function get_title() {
		return __( 'Modern', 'bdthemes-element-pack' );
	}

	public function render() {
		
		$settings = $this->parent->get_settings();
		$id       = $this->parent->get_id();
		
		$this->parent->query_posts(5);
		$wp_query = $this->parent->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		?> 
		<div id="bdt-post-grid-<?php echo esc_attr($id); ?>" class="bdt-post-grid bdt-post-grid-skin-modern">
	  		<div class="bdt-grid bdt-grid-<?php echo esc_attr($settings['column_gap']); ?>" data-bdt-grid>

				<?php 
				$bdt_count = 0;
			
				while ($wp_query->have_posts()) :
					$wp_query->the_post();
					$bdt_count++;
		  			?>


					<?php if ( 1 == $bdt_count ) : ?>
					    <div class="bdt-width-2-5@m bdt-primary">
					        <?php $this->parent->render_post_grid_item( get_the_ID(), $settings['primary_thumbnail_size'], $settings['excerpt_length'] ); ?>
					    </div>

					    <div class="bdt-width-2-5@m bdt-secondary">
					        <div class="bdt-grid bdt-grid-<?php echo esc_attr($settings['column_gap']); ?>" data-bdt-grid>

					<?php endif; ?>
					            <?php if ( 2 == $bdt_count ) : ?>
						            <div class="bdt-width-1-3@s bdt-width-1-1@m">
						                <?php $this->parent->render_post_grid_item( get_the_ID(), $settings['secondary_thumbnail_size'], $settings['excerpt_length'] ); ?>
						            </div>
					            <?php endif; ?>

								<?php if ( 3 == $bdt_count or 4 == $bdt_count ) : ?>
						            <div class="bdt-width-1-3@s bdt-width-1-2@m">
						                <?php $this->parent->render_post_grid_item( get_the_ID(), $settings['secondary_thumbnail_size'], $settings['excerpt_length'] ); ?>
						            </div>
					            <?php endif; ?>

					<?php if ( 5 == $bdt_count ) : ?>
					        </div>
					    </div>

					    <div class="bdt-width-1-5@m bdt-primary bdt-tertiary">
					        <?php $this->parent->render_post_grid_item( get_the_ID(), $settings['primary_thumbnail_size'], $settings['excerpt_length'] ); ?>
					    </div>

					<?php endif; ?>

				<?php endwhile; ?>
			</div>
		</div>
	
 		<?php
 		
 		if ($settings['show_pagination']) { ?>
			<div class="ep-pagination">
				<?php element_pack_post_pagination($wp_query, $this->get_id()); ?>
			</div>
			<?php
		}
 		
		wp_reset_postdata();
	}
}

