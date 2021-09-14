<?php
$page_object = get_queried_object();
if(isset($page_object->ID)){
    $postID=$page_object->ID; // POST OR PAGE
}else{
    $postID = 0; // OTHER
}

?>
<?php get_template_part('templates/head'); ?>

<?php
$boxed_div_open ="";
$boxed_div_close ="";
$boxed_class ="";
if ( function_exists( 'get_theme_mod' ) ) {


    // Get post meta for Groovy Preset (gm_custom_preset_id).
    $themo_gm_preset_option = get_post_meta($postID, 'gm_custom_preset_id', array() );

    // If auto-integration is not enabled, check if we explicitly selected a Groovy Menu preset.
    if (is_array($themo_gm_preset_option) && array_key_exists(0, $themo_gm_preset_option) && $themo_gm_preset_option[0] !== 'none') {
        $themo_gm_preset_selected = true;
    } else {
        $themo_gm_preset_selected = false;
    }

    // If auto-integration IS enabled, check if we explicitly selected the Groovy Menu preset of 'none' and show default stratus menu.
    if (is_array($themo_gm_preset_option) && array_key_exists(0, $themo_gm_preset_option) && $themo_gm_preset_option[0] == 'none') {
        $themo_gm_preset_hide = true;
    }else{
        $themo_gm_preset_hide = false;
    }

    $boxed_mode = get_theme_mod( 'themo_boxed_layout', false );
    if ($boxed_mode){
        $boxed_div_open = '<div id="boxed">';
        $boxed_div_close = '</div><!-- #boxed -->';
        add_filter( 'body_class', function( $classes ) {
            return array_merge( $classes, array( 'boxed-mode' ) );
        } );
    }

    $sticky_header = get_theme_mod( 'themo_sticky_header', true );
    if ($sticky_header == true){
        add_filter( 'body_class', function( $classes ) {
            return array_merge( $classes, array( 'th-sticky-header' ) );
        } );
    }

}
?>

<body <?php body_class(); ?>>

<?php
// Slider preloader enabled?
if ( function_exists( 'get_theme_mod' ) ) {
    $themo_preloader = get_theme_mod( 'themo_preloader', true );
    if ($themo_preloader == true){ ?>
        <!-- Preloader Start -->
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
        <!-- Preloader End -->
    <?php
    }
}
?>

<?php

//-----------------------------------------------------
// demo options
//-----------------------------------------------------
$is_demo = false;
if($is_demo){
	wp_register_script('demo_options', get_template_directory_uri() . '/demo/js/demo_options.js', array(), 1, true);
	wp_enqueue_script('demo_options');
    include( get_template_directory() . '/demo/demo_options.php');
}
?>

<?php
// jquery Animation Variable
global $themo_animation;
?>

<?php echo wp_kses_post($boxed_div_open); // Pre sanitized ?>

  <?php

    // Get Groovy Menu integration setting.
    if (class_exists('GroovyMenuUtils')) {
        $themo_gm_menu_utils = false;
        $themo_gm_menu_utils = new GroovyMenuUtils();
        $themo_gm_saved_auto_integration = $themo_gm_menu_utils::getAutoIntegration();
    }

    // If Groovy Menu is integrated, don't show our header.
    // Setting under Groovy Menu / Integration / Auto Integration.
    if ( isset($themo_gm_saved_auto_integration) && $themo_gm_saved_auto_integration && !$themo_gm_preset_hide) {
      // If Groovy Auto Integration is not enabled but we have explicitly set a preset in the page/post settings. Show it.
    } elseif($themo_gm_preset_selected) {
        if ( function_exists( 'groovy_menu' ) ) { groovy_menu(); }
    } elseif ( function_exists( 'hfe_render_header' ) && function_exists( 'get_hfe_header_id' ) && get_hfe_header_id() ) {
        /**
         * An "Header, Footer & Blocks for Elementor" header is available.
         *
         * @link  https://github.com/Nikschavan/header-footer-elementor/wiki/Adding-Header-Footer-Elementor-support-for-your-theme
         */
        hfe_render_header();
    } elseif ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' )) { // Check for Elementor Header.

        // Run the get_header action.
        do_action('get_header');

        // Default theme header
        if (current_theme_supports('bootstrap-top-navbar')) {
            get_template_part('templates/header-top-navbar');
        } else {
            get_template_part('templates/header');
        }
    }
  ?>
  <div class="wrap" role="document">
  
    <div class="content">

        <?php
        if ( is_archive() || is_home() || is_search() ) {
            // Elementor 'archive' location
            if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
                include roots_template_path();
            }
        } elseif ( is_singular() ) {
            // Elementor single location
            if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
                include roots_template_path();
            }
        } else {
            // Elementor `404` location
            if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
                include roots_template_path();
            }
        }
        ?>

    </div><!-- /.content -->
  </div><!-- /.wrap -->

  <?php
    if ( function_exists( 'hfe_render_before_footer' ) && function_exists( 'hfe_is_before_footer_enabled' ) && hfe_is_before_footer_enabled() ) {
        hfe_render_before_footer();
    }

    if ( function_exists( 'hfe_render_footer' ) && function_exists( 'get_hfe_footer_id' ) && get_hfe_footer_id() ) {
        /**
         * An "Header, Footer & Blocks for Elementor" footer is available.
         *
         * @link  https://github.com/Nikschavan/header-footer-elementor/wiki/Adding-Header-Footer-Elementor-support-for-your-theme
         */
        hfe_render_footer();
    } elseif ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
        get_template_part('templates/footer');
    }?>

<?php echo wp_kses_post($boxed_div_close); ?>
<?php wp_footer(); ?>
</body>
</html>