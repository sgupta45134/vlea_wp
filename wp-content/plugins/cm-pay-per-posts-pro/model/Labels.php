<?php
namespace com\cminds\payperposts\model;

use com\cminds\payperposts\App;

class Labels extends Model {
	
	const FILENAME = 'labels.tsv';
	
	protected static $labels = array();
	protected static $labelsByCategories = array();
	
	public static function init() {
		
		parent::init();
		
		add_action(App::MENU_SLUG . '_load_label_file', array(__CLASS__, 'loadLabelFile'), 1);
		
		static::loadLabelFile();
		do_action(App::MENU_SLUG . '_labels_init');
		
		static::$labels = apply_filters(App::MENU_SLUG . '_labels_init_labels', static::$labels);
		static::$labelsByCategories = apply_filters(App::MENU_SLUG . '_labels_init_labels_by_categories', static::$labelsByCategories);
		
	}

	public static function getLabel($labelKey) {
		$optionName = App::MENU_SLUG .'_label_'. $labelKey;
		$default = static::getDefaultLabel($labelKey);
		return get_option($optionName, (empty($default) ? $labelKey : $default));
	}
	
	public static function setLabel($labelKey, $value) {
		$optionName = App::MENU_SLUG .'_label_'. $labelKey;
		update_option($optionName, $value);
	}
	
	public static function getLocalized($labelKey) {
		return __(static::getLabel($labelKey), App::TEXT_DOMAIN);
	}
	
	
	public static function getDefaultLabel($key) {
		if ($label = static::getLabelDefinition($key)) {
			return $label['default'];
		}
	}
	
	public static function getDescription($key) {
		if ($label = static::getLabelDefinition($key)) {
			return $label['desc'];
		}
	}
	
	public static function getLabelDefinition($key) {
		$labels = static::getLabels();
		return (isset($labels[$key]) ? $labels[$key] : NULL);
	}
	
	public static function getLabels() {
		return static::$labels;
	}
	
	public static function getLabelsByCategories() {
		return static::$labelsByCategories;
	}
	
	public static function getDefaultLabelsPath() {
		return App::path('asset') .'/labels/'. static::FILENAME;
	}
	
	public static function loadLabelFile($path = null) {
		$file = explode("\n", file_get_contents(empty($path) ? static::getDefaultLabelsPath() : $path));
		foreach ($file as $row) {
			$row = explode("\t", trim($row));
			if (count($row) >= 2) {
				$label = array(
					'default' => $row[1],
					'desc' => (isset($row[2]) ? $row[2] : null),
					'category' => (isset($row[3]) ? $row[3] : null),
				);
				static::$labels[$row[0]] = $label;
				static::$labelsByCategories[$label['category']][] = $row[0];
			}
		}
	}
	
	static function processPostRequest() {
		$labels = static::getLabels();
		foreach ($labels as $labelKey => $label) {
			if (isset($_POST['label_'. $labelKey])) {
				static::setLabel($labelKey, stripslashes($_POST['label_'. $labelKey]));
			}
		}
	}
	
	static function __($msg) {
		return \__($msg, App::TEXT_DOMAIN);
	}
	
}