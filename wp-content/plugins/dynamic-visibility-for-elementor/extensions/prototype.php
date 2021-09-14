<?php

namespace DynamicVisibilityForElementor\Extensions;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Extension
 *
 * Class to easify extend Elementor controls and functionality
 *
 */

class DCE_Extension_Prototype {

	public $name = 'Extension';

	public $docs = 'https://www.dynamic.ooo';

	private $is_common = true;

	private $depended_scripts = [];

	private $depended_styles = [];

	public $common_sections_actions = array(
		array(
			'element' => 'common',
			'action' => '_section_style',
		),
	);

	public function __construct() {
		$this->init();
	}

	public function init( $param = null ) {
		// Enqueue scripts
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Enqueue styles
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

		// Elementor hooks

		if ( $this->is_common ) {
			// Add the advanced section required to display controls
			$this->add_common_sections_actions();
		}

		$this->add_actions();
	}

	public static function is_enabled() {
		return true;
	}

	public function get_docs() {
		return $this->docs;
	}

	public function add_script_depends( $handler ) {
		$this->depended_scripts[] = $handler;
	}

	public function add_style_depends( $handler ) {
		$this->depended_styles[] = $handler;
	}

	public function get_script_depends() {
		return $this->depended_scripts;
	}

	final public function enqueue_scripts() {
		foreach ( $this->get_script_depends() as $script ) {
			wp_enqueue_script( $script );
		}
	}

	final public function get_style_depends() {
		return $this->depended_styles;
	}

	public static function get_description() {
		return '';
	}

	final public function enqueue_styles() {
		foreach ( $this->get_style_depends() as $style ) {
			wp_enqueue_style( $style );
		}
	}

	public function get_low_name() {
		return 'visibility';
	}

	final public function add_common_sections( $element, $args ) {
		$low_name = $this->get_low_name();
		$section_name = 'dce_section_' . $low_name . '_advanced';

		if ( ! $this->has_controls ) {
			// no need settings
			return false;
		}

		// Check if this section exists
		$section_exists = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), $section_name );

		if ( ! is_wp_error( $section_exists ) ) {
			// We can't and should try to add this section to the stack
			return false;
		}

		$this->get_control_section( $section_name, $element );
	}

	public function add_common_sections_actions() {

		//_section_style
		//section_advanced
		//section_custom_css
		//section_custom_css_pro

		foreach ( $this->common_sections_actions as $action ) {

			// Activate action for elements
			add_action('elementor/element/' . $action['element'] . '/' . $action['action'] . '/after_section_end', function ( $element, $args ) {
				$this->add_common_sections( $element, $args );
			}, 10, 2);
		}
	}

	protected function add_actions() {
	}

	protected function remove_controls( $element, $controls = null ) {
		if ( empty( $controls ) ) {
			return;
		}

		if ( is_array( $controls ) ) {
			$control_id = $controls;

			foreach ( $controls as $control_id ) {
				$element->remove_control( $control_id );
			}
		} else {
			$element->remove_control( $controls );
		}
	}
}
