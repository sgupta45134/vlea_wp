<?php
namespace ElementPack\Modules\InteractiveTabs;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'interactive-tabs';
	}

	public function get_widgets() {
		$widgets = [
			'Interactive_Tabs',
		];

		return $widgets;
	}
}
