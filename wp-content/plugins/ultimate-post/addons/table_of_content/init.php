<?php
defined( 'ABSPATH' ) || exit;

add_filter('ultp_addons_config', 'ultp_toc_config');
function ultp_toc_config( $config ) {
	$configuration = array(
		'name' => __( 'Table of Content', 'ultimate-post' ),
		'desc' => __( 'Add a Customizable Table of Contents into your blog posts and custom post types.', 'ultimate-post' ),
		'img' => ULTP_URL.'/assets/img/addons/table-of-content.svg',
		'is_pro' => false
	);
	$config['ultp_table_of_content'] = $configuration;
	return $config;
}