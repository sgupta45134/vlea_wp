<?php
namespace ElementPack\Modules\DocumentViewer;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'document-viewer';
	}

	public function get_widgets() {

		$widgets = [
			'Document_Viewer',
		];

		return $widgets;
	}
}
