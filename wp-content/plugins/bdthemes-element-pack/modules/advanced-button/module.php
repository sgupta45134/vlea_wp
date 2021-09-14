<?php
namespace ElementPack\Modules\AdvancedButton;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'advanced-button';
	}

	public function get_widgets() {

		$widgets = ['AdvancedButton'];

		return $widgets;
	}
}
