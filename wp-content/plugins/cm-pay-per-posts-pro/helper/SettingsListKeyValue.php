<?php
namespace com\cminds\payperposts\helper;

use com\cminds\payperposts\model\Settings;

class SettingsListKeyValue {
	
	static function render($name, $option) {
		$value = Settings::getOption($name);
		$output = self::renderOption(0, 'template', $option, '', '');
		foreach ($value as $i => $opt) {
			$fieldName = $name . '['. ($i+1) .']';
			$output .= self::renderOption($i+1, $fieldName, $option, $opt['key'], $opt['value']);
		}
		$output .= '<input type="button" value="Add" class="cmppp-list-key-value-add-btn" />';
		
		return '<div class="cmppp-list-key-value" data-name="'. esc_attr($name) .'">' . $output . '</div>';
	}
	
	protected static function renderOption($num, $name, $option, $key, $val) {
		return sprintf('<div class="cmppp-list-key-value-row" data-num="%d"><input type="text" name="%s[key]" value="%s" placeholder="%s" />'
				. '<input type="text" name="%s[value]" value="%s" placeholder="%s" /><input type="button" value="x" title="Remove" /></div>',
			$num,
			esc_attr($name),
			esc_attr($key),
			esc_attr($option['keyPlaceholder']),
			esc_attr($name),
			esc_attr($val),
			esc_attr($option['valuePlaceholder'])
		);
	}
	
}