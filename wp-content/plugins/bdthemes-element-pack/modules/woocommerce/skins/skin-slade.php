<?php
namespace ElementPack\Modules\Woocommerce\Skins;

use Elementor\Controls_Manager;
use Elementor\Skin_Base;
use ElementPack\Base\Module_Base;
use ElementPack\Modules\QueryControl\Controls\Group_Control_Posts;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Slade extends Skin_Base {

	public function get_id() {
		return 'wc-slider-slade';
	}

	public function get_title() {
		return esc_html__( 'Slade', 'bdthemes-element-pack' );
	}

	public function render_thumbnav() {
		$settings = $this->parent->get_settings();

		?>
		<?php if ($settings['show_thumbnav']) : ?>
		<div class="thumbnav bdt-position-center-right bdt-position-small">
            <ul class="bdt-thumbnav bdt-thumbnav-vertical">


        	    <?php		
        		$bdt_counter = 0;
        		      
        		$wp_query = $this->parent->render_query();

        		while ( $wp_query->have_posts() ) : $wp_query->the_post();

        			$image_src = wp_get_attachment_image_url(get_post_thumbnail_id(), 'thumbnail');
        			?>

        			<li data-bdt-slideshow-item="<?php echo esc_html($bdt_counter); ?>">
        				<a href="#">
        					<img src="<?php echo esc_url($image_src); ?>" width="100" alt="<?php echo get_the_title(); ?>">
        				</a>
        			</li>


        			<?php


    				$bdt_counter++;
    			endwhile; 

    			wp_reset_postdata(); 

    			?>
            </ul>
        </div>
    	<?php endif; ?>

		<?php
	}

	public function render_header() {
		$settings        = $this->parent->get_settings_for_display();
		$slides_settings = [];

		$ratio = ($settings['slider_size_ratio']['width'] && $settings['slider_size_ratio']['height']) ? $settings['slider_size_ratio']['width'].":".$settings['slider_size_ratio']['height'] : '1920:750';

		$slider_settings['data-bdt-slideshow'] = wp_json_encode(array_filter([
			"animation"         => $settings["slider_animations"],
			"ratio"             => $ratio,
			"min-height"        => $settings["slider_min_height"]["size"],
			"autoplay"          => ($settings["autoplay"]) ? true : false,
			"autoplay-interval" => $settings["autoplay_interval"],
			"pause-on-hover"    => ("yes" === $settings["pause_on_hover"]) ? true : false,
	    ]));

    	$slider_settings['class'][] = 'bdt-wc-slider';
    	$slider_settings['class'][] = 'bdt-wc-slider-slade-skin';

	    if ('both' == $settings['navigation']) {
	    	$slider_settings['class'][] = 'bdt-arrows-dots-align-'. $settings['both_position'];
		} elseif ('arrows' == $settings['navigation']) {
	    	$slider_settings['class'][] = 'bdt-arrows-align-'. $settings['arrows_position'];
		} elseif ('dots' == $settings['navigation']) {
	    	$slider_settings['class'][] = 'bdt-dots-align-'. $settings['dots_position'];
		}

	    $slider_fullscreen = ( $settings['slider_fullscreen'] ) ? ' data-bdt-height-viewport="offset-top: true"' : '';

		?>
		<div <?php echo \element_pack_helper::attrs($slider_settings); ?>>
			<div class="bdt-position-relative bdt-visible-toggle">
				<ul class="bdt-slideshow-items"<?php echo esc_attr($slider_fullscreen); ?>>
		<?php
	}

	public function render_footer() {
		$settings = $this->parent->get_settings_for_display();

				?>
				</ul>
				
				<?php $this->render_thumbnav(); ?>
				
				<?php if ('both' == $settings['navigation']) : ?>
					<?php $this->parent->render_both_navigation(); ?>

					<?php if ( 'center' === $settings['both_position'] ) : ?>
						<?php $this->parent->render_dotnavs(); ?>
					<?php endif; ?>

				<?php elseif ('arrows' == $settings['navigation']) : ?>			
					<?php $this->parent->render_navigation(); ?>
				<?php elseif ('dots' == $settings['navigation']) : ?>			
					<?php $this->parent->render_dotnavs(); ?>
				<?php endif; ?>


			</div>
		</div>
		<?php
	}

	public function render_item_content() {
		$settings        = $this->parent->get_settings_for_display();

		?>
		<div class="bdt-grid">
			<div class="bdt-width-2-5 bdt-flex bdt-flex-<?php echo esc_attr( $settings['vertical_align'] ); ?>">
		        <div class="bdt-slide-img">
		            <?php $this->parent->render_item_image(); ?>
		        </div>
			</div>
			<div class="bdt-width-3-5 bdt-flex bdt-flex-<?php echo esc_attr( $settings['vertical_align'] ); ?>">
		        <div class="bdt-text-<?php echo esc_attr($settings['text_align']); ?>">
		            <div class="bdt-slideshow-content-wrapper bdt-slider-content" data-bdt-slideshow-parallax="scale: 1,1,0.8">

		            	<?php if ($settings['show_title']) : ?>
		                <h2 class="bdt-wc-slider-title"  data-bdt-slideshow-parallax="y: -100,0,0; opacity: 1,1,0"><?php the_title(); ?></h2>
		            	<?php endif; ?>

		            	<?php if ($settings['show_text']) : ?>
		                <div class="bdt-wc-slider-text" data-bdt-slideshow-parallax="x: 200,0,-200;"><?php the_excerpt(); ?></div>
		                <?php endif; ?>

		                <?php if ($settings['show_price']) : ?>
		                <div data-bdt-slideshow-parallax="x: 300,0,-200">
							<span class="bdt-slider-skin-price"><?php woocommerce_template_single_price(); ?></span>
						</div>
						<?php endif; ?>

		                <?php if ($settings['show_cart']) : ?>
                		<div data-bdt-slideshow-parallax="y: 100,0,0; opacity: 1,1,0" class="bdt-wc-add-to-cart">
							<?php woocommerce_template_loop_add_to_cart();?>
						</div>
						<?php endif; ?>

		            </div>
		        </div>
			</div>
		</div>
		<?php
	}

    public function render() {
		$settings  = $this->parent->get_settings_for_display();

		$content_reverse = $settings['content_reverse'] ? ' bdt-flex-first' : '';

		$this->render_header('slade');

		$wp_query = $this->parent->render_query();

		while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $product; ?>
				    
	        <li class="bdt-slideshow-item">

                <?php $this->render_item_content(); ?>

	        </li>

		<?php endwhile; wp_reset_postdata();

		$this->render_footer();
	}
}