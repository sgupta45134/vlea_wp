<?php
namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\App;

class Shortcode {
	
	const SHORTCODE_NAME = '';

	static function bootstrap() {
		add_action('init', array(get_called_class(), 'init'), 4);
	}
	
	static function init() {
		add_shortcode( static::SHORTCODE_NAME, array(get_called_class(), 'shortcode') );
	}
	
	static function shortcode($atts) {
		return '';
	}
	
	static function wrap($code, $extra = '') {
		$name = strtolower(App::shortClassName(get_called_class(), 'Shortcode'));
		return sprintf('<div class="%s-widget %s-widget-%s"%s>%s</div>', App::MENU_SLUG, App::MENU_SLUG, esc_attr($name), $extra, $code);
	}
	
}
