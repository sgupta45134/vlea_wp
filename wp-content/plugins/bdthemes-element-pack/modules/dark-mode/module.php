<?php
namespace ElementPack\Modules\DarkMode;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'dark-mode';
	}

	public function get_widgets() {

		$widgets = ['Dark_Mode'];

		return $widgets;
	}

}
