<?php
namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Settings;
use com\cminds\payperposts\model\Labels;

class ProController extends Controller {

	protected static $filters = array('cmppp_options_config');
	protected static $actions = array(
		array('name' => 'cmppp_labels_init', 'priority' => 10),
		'cmppp_load_assets_frontend'
	);
	
	
	
	static function getMenuSlug($slug) {
		return App::MENU_SLUG . '-' . $slug;
	}
	
	
	
	static function cmppp_labels_init() {
		Labels::loadLabelFile(App::path('asset/labels/pro.tsv'));
	}
	
	
	static function cmppp_options_config($config) {
		
		return $config;
	}
	
	
	static function cmppp_load_assets_frontend() {
		
	}

}