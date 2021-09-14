<?php
/**
 * Enable theme features
 *
 * @author     @retlehs
 * @link 	   http://roots.io
 * @editor     Themovation <themovation@gmail.com>
 * @version    1.0
 */
 
add_theme_support('bootstrap-top-navbar');  // Enable Bootstrap's top navbar
add_theme_support('automatic-feed-links'); // Enable post and comment RSS feed links to head.

// Custom logo.
$logo_width  = 120;
$logo_height = 100;

// If the retina setting is active, double the recommended width and height.
if ( get_theme_mod( 'themo_retinajs_logo', false ) ) {
    $logo_width  = floor( $logo_width * 2 );
    $logo_height = floor( $logo_height * 2 );
}

add_theme_support(
    'custom-logo',
    array(
        'height'      => $logo_height,
        'width'       => $logo_width,
        'flex-height' => true,
        'flex-width'  => true,
    )
);

/* Declare WooCommerce Support */

add_action( 'after_setup_theme', 'th_woocommerce_support' );
function th_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    //add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}

function thmv_remove_widget_block_editor() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'thmv_remove_widget_block_editor' );

/**
 * Configuration values
 */
define('POST_EXCERPT_LENGTH', 40); // Length in words for excerpt_length filter (http://codex.wordpress.org/Plugin_API/Filter_Reference/excerpt_length)
define('PORTFOLIO_EXCERPT_LENGTH', 3); // Length in words for excerpt_length filter (http://codex.wordpress.org/Plugin_API/Filter_Reference/excerpt_length)

/**
 * $content_width is a global variable used by WordPress for max image upload sizes
 * and media embeds (in pixels).
 *
 * Example: If the content area is 640px wide, set $content_width = 620; so images and videos will not overflow.
 * Default: 940px is the default Bootstrap container width.
 */
if (!isset($content_width)) { $content_width = 1140; }

/**
 * Define helper constants
 */
$get_theme_name = explode('/themes/', get_template_directory());

define('RELATIVE_PLUGIN_PATH',  str_replace(home_url('/') , '', plugins_url()));
define('RELATIVE_CONTENT_PATH', str_replace(home_url('/'), '', content_url()));
define('THEME_NAME',            next($get_theme_name));
define('THEME_PATH',            RELATIVE_CONTENT_PATH . '/themes/' . THEME_NAME);
// Set minimum PHP version requirements
define( 'TH_REQUIRED_PHP_VERSION', '5.4' );
define( 'TH_PREVENT_ENTREPRENEUR_UPGRADE', true );