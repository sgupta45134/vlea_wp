<?php
namespace DynamicVisibilityForElementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Main Plugin Class
 *
 * Register new elementor widget.
 *
 * @since 1.0.0
 */
class Plugin {


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Add Actions
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function add_actions() {
		add_action( 'elementor/init', array( $this, 'dve_elementor_init' ) );
		add_filter( 'plugin_action_links_' . DVE_PLUGIN_BASE, array( $this, 'add_action_links' ) );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

	}

	public function dve_elementor_init() {
		// Traits
		include DVE_PATH . '/class/trait/date.php';
		include DVE_PATH . '/class/trait/elementor.php';
		include DVE_PATH . '/class/trait/filesystem.php';
		include DVE_PATH . '/class/trait/form.php';
		include DVE_PATH . '/class/trait/image.php';
		include DVE_PATH . '/class/trait/meta.php';
		include DVE_PATH . '/class/trait/navigation.php';
		include DVE_PATH . '/class/trait/notice.php';
		include DVE_PATH . '/class/trait/plugin.php';
		include DVE_PATH . '/class/trait/static.php';
		include DVE_PATH . '/class/trait/string.php';
		include DVE_PATH . '/class/trait/woo.php';
		include DVE_PATH . '/class/trait/wp.php';

		// Classes
		include DVE_PATH . 'class/ajax.php';
		include DVE_PATH . 'class/helper.php';
		include DVE_PATH . 'class/elements.php';

		new Ajax();
		new Elements();

		add_action('elementor/frontend/after_register_styles', function () {
			wp_register_style(
				'dce-style',
				plugins_url( '/assets/css/style.css', __FILE__ ),
				[],
				DVE_VERSION
			);
			// Enqueue DCE Elementor Style
			wp_enqueue_style( 'dce-style' );
		});

		// DCE Custom Icons - in Elementor Editor
		add_action('elementor/preview/enqueue_styles', function () {
			wp_register_style(
				'dce-preview',
				plugins_url( '/assets/css/preview.css', __FILE__ ),
				[],
				DVE_VERSION
			);
			// Enqueue DCE Elementor Style
			wp_enqueue_style( 'dce-preview' );
		});

		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'dce_editor' ) );

				// Controls
				require_once DVE_PATH . 'controls/ooo-query.php';
				require_once DVE_PATH . 'class/controls.php';
				$this->controls = new Controls();
				add_action( 'elementor/controls/controls_registered', [ $this->controls, 'on_controls_registered' ] );

		//Query Control
		require_once DVE_PATH . 'modules/query-control/DCE_QueryControl.php';
		
		//DCE Query
		require_once DVE_PATH . 'override/dce-query.php';

		// Extension
		require_once DVE_PATH . 'extensions/prototype.php';
		require_once DVE_PATH . 'extensions/visibility.php';

		new Extensions\DCE_Extension_Visibility();

	}

	public function add_action_links( $links ) {
		$my_links[] = sprintf( '<a href="https://www.dynamic.ooo/upgrade/visibility-to-premium?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash" target="_blank"">' . __( 'Go Premium', 'dynamic-visibility-for-elementor' ) . '</a>' );
		return array_merge( $links, $my_links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( 'dynamic-visibility-for-elementor/dynamic-visibility-for-elementor.php' === $plugin_file ) {
			$row_meta = [
				'docs' => '<a href="https://help.dynamic.ooo/en/articles/4954083-dynamic-visibility" aria-label="' . esc_attr( __( 'View Dynamic Visibility Documentation', 'dynamic-visibility-for-elementor' ) ) . '" target="_blank">' . __( 'Docs', 'dynamic-visibility-for-elementor' ) . '</a>',
				'community' => '<a href="http://facebook.com/groups/dynamic.ooo" aria-label="' . esc_attr( __( 'Facebook Community', 'dynamic-visibility-for-elementor' ) ) . '" target="_blank">' . __( 'FB Community', 'dynamic-visibility-for-elementor' ) . '</a>',
			];

			$plugin_meta = array_merge( $plugin_meta, $row_meta );
		}

		return $plugin_meta;
	}

	/**
	 * Enqueue admin styles
	 *
	 * @since 0.7.0
	 *
	 * @access public
	 */
	public function dce_editor() {
		// Register styles
		wp_register_style( 'dce-icons', plugins_url( '/assets/css/dce-icon.css', DVE__FILE__ ), [], DVE_VERSION );
		// Enqueue styles Icons
		wp_enqueue_style( 'dce-icons' );

		// Register styles
		wp_register_style(
			'dce-editor',
			DVE_URL . 'assets/css/editor.css',
			[],
			DVE_VERSION
		);
		wp_enqueue_style( 'dce-editor' );

		wp_register_script(
			'dce-script-editor',
			DVE_URL . 'assets/js/editor.js',
			[],
			DVE_VERSION
		);
		wp_enqueue_script( 'dce-script-editor' );

		wp_register_script( 'dce-visibility', DVE_URL . 'assets/js/visibility.js', [], DVE_VERSION );
		wp_enqueue_script( 'dce-visibility' );

		// select2
		wp_enqueue_style( 'dce-select2', DVE_URL . 'assets/lib/select2/select2.min.css', [], DVE_VERSION );
		wp_enqueue_script( 'dce-select2', DVE_URL . 'assets/lib/select2/select2.full.min.js', array( 'jquery' ), DVE_VERSION, true );
	}
}
