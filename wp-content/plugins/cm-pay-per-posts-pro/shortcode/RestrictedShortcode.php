<?php
namespace com\cminds\payperposts\shortcode;

use com\cminds\payperposts\controller\PayboxController;

class RestrictedShortcode extends Shortcode {

	const SHORTCODE_NAME = 'cmppp_restricted';
	
	static function shortcode($atts = array(), $content='') {
		
		$atts = shortcode_atts(array(

		), $atts);

		return PayboxController::loadFrontendView('paybox-shortcode', compact('atts', 'content'));

	}
	
}