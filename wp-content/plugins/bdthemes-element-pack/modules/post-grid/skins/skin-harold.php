<?php
namespace ElementPack\Modules\PostGrid\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Harold extends Elementor_Skin_Base {

	public function get_id() {
		return 'bdt-harold';
	}

	public function get_title() {
		return __( 'Harold', 'bdthemes-element-pack' );
	}

	public function render_comments() {

		if ( ! $this->parent->get_settings('show_comments') ) {
			return;
		}
		
		echo 
			'<span class="bdt-post-grid-comments"><i class="ep-bubble" aria-hidden="true"></i> '.get_comments_number().'</span>';
	}

	public function render_category() {

		if ( ! $this->parent->get_settings( 'show_category' ) ) { return; }
		?>
		<div class="bdt-post-grid-category bdt-position-small bdt-position-top-right">
			<?php echo get_the_category_list(' '); ?>
		</div>
		<?php
	}

	public function render_post_grid_item( $post_id, $image_size, $excerpt_length ) {
		$settings = $this->parent->get_settings();
		global $post;

		if ('yes' == $settings['global_link']) {

		$this->parent->add_render_attribute( 'grid-item', 'onclick', "window.open('" . esc_url(get_permalink()) . "', '_self')", true );
		}

		$this->parent->add_render_attribute('grid-item', 'class', 'bdt-post-grid-item bdt-transition-toggle bdt-position-relative bdt-box-shadow-small', true);

		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'grid-item' ); ?>>								
			<?php $this->parent->render_image(get_post_thumbnail_id( $post_id ), $image_size ); ?>

	  		
	  		<div class="bdt-post-grid-desc bdt-padding">
				<?php $this->parent->render_title(); ?>

				<?php $this->parent->render_excerpt($excerpt_length); ?>
				<?php $this->parent->render_readmore(); ?>
			</div>

			<?php if ($settings['show_author'] or $settings['show_date'] or $settings['show_comments']) : ?>
				<div class="bdt-post-grid-meta bdt-subnav bdt-flex-middle">
					<?php $this->parent->render_author(); ?>
					<?php $this->parent->render_date(); ?>
					<?php $this->render_comments(); ?>
					<?php $this->parent->render_tags(); ?>
				</div>
			<?php endif; ?>

			<?php $this->render_category(); ?>
		</div>
		<?php
	}

	public function render() {
		
		$settings = $this->parent->get_settings();
		$id       = $this->parent->get_id();

		$this->parent->query_posts($settings['harold_item_limit']['size']);
		$wp_query = $this->parent->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		if ( $page > '1' ) {
			$this->parent->query_posts($settings['harold_item_limit']['size'] - 1);
			$wp_query = $this->parent->get_query();
		}

		$this->parent->add_render_attribute( 'grid-height', 'class', ['bdt-grid', 'bdt-grid-medium', 'bdt-grid-' . esc_attr($settings['column_gap'])] );
		$this->parent->add_render_attribute( 'grid-height', 'data-bdt-grid', '' );

		if ( 'match-height' == $settings['secondary_grid_height'] ) {
			$this->parent->add_render_attribute( 'grid-height', 'bdt-height-match', 'target: > div ~ div .bdt-post-grid-desc' );
		} /*elseif ( 'masonry' == $settings['secondary_grid_height'] ) {
			$this->parent->add_render_attribute( 'grid-height', 'bdt-grid', 'masonry: true' );
		}*/
		

		?> 
		<div id="bdt-post-grid-<?php echo esc_attr($id); ?>" class="bdt-post-grid bdt-post-grid-skin-harold">
	  		<div <?php echo $this->parent->get_render_attribute_string( 'grid-height' ); ?>>

				<?php $bdt_count = 0;
			
				while ($wp_query->have_posts()) :
					$wp_query->the_post();
						
		  			$bdt_count++;

	  				if( $page == '1' ) {
			  			if ( $bdt_count <= 1) {
							$bdt_grid_raw   = ' bdt-width-1-1@m bdt-width-1-1@s bdt-width-1-1';
							$bdt_post_class = ' bdt-primary bdt-text-center';
							$thumbnail_size = $settings['primary_thumbnail_size'];
							$excerpt_length = $settings['primary_excerpt_length'];
			  			} else {
							$bdt_grid_raw   = ' bdt-width-1-' . esc_attr($settings['columns']) . '@m bdt-width-1-' . esc_attr($settings['columns_tablet']) . '@s bdt-width-1-' . esc_attr($settings['columns_mobile']) ;
							$bdt_post_class = ' bdt-secondary';
							$thumbnail_size = $settings['secondary_thumbnail_size'];
							$excerpt_length = $settings['secondary_excerpt_length'];
			  			}
		  			} else {
						$bdt_grid_raw   = ' bdt-width-1-' . esc_attr($settings['columns']) . '@m bdt-width-1-' . esc_attr($settings['columns_tablet']) . '@s bdt-width-1-' . esc_attr($settings['columns_mobile']) ;
						$bdt_post_class = ' bdt-secondary';
						$thumbnail_size = $settings['secondary_thumbnail_size'];
						$excerpt_length = $settings['secondary_excerpt_length'];
		  			}

		  			?>	  			
		  			<div class="<?php echo esc_attr($bdt_grid_raw . $bdt_post_class); ?>">
						<?php $this->render_post_grid_item( get_the_ID(), $thumbnail_size, $excerpt_length); ?>
					</div>
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

