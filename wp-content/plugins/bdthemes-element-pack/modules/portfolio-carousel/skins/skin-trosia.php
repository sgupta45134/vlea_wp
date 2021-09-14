<?php
namespace ElementPack\Modules\PortfolioCarousel\Skins;

use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Trosia extends Elementor_Skin_Base {
	public function get_id() {
		return 'bdt-trosia';
	}

	public function get_title() {
		return __( 'Trosia', 'bdthemes-element-pack' );
	}

	public function render_post() {
		$settings = $this->parent->get_settings_for_display();
		global $post;

		$element_key = 'portfolio-item-' . $post->ID;

		$this->parent->add_render_attribute('portfolio-item-inner', 'class', 'bdt-portfolio-inner', true);

		$this->parent->add_render_attribute('portfolio-item', 'class', 'swiper-slide bdt-gallery-item bdt-transition-toggle', true);

		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'portfolio-item' ); ?>>
			<div <?php echo $this->parent->get_render_attribute_string( 'portfolio-item-inner' ); ?>>
				<?php
					$this->parent->render_thumbnail();
					$this->parent->render_overlay();
				?>
				<div class="bdt-portfolio-desc bdt-position-z-index bdt-position-bottom">
					<?php
					$this->parent->render_title(); 
					$this->parent->render_excerpt();
					?>
				</div>
				<div>
					<?php $this->parent->render_categories_names(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function render() {
		$settings = $this->parent->get_settings_for_display();

		$this->parent->query_posts();

		$wp_query = $this->parent->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$this->parent->render_header('trosia');

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();

			$this->render_post();
		}

		$this->parent->render_footer();
		
		wp_reset_postdata();

	}
}

