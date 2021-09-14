<?php

namespace Codemanas\ZoomPro\Elementor;

use Codemanas\ZoomPro\Elementor\Widgets\Calendar;
use Codemanas\ZoomPro\Elementor\Widgets\MeetingList;
use Codemanas\ZoomPro\Elementor\Widgets\MeetingRegistrants;
use Elementor\Plugin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Invoke Elementor Dependency Class
 *
 * Register new elementor widget.
 *
 * @since 1.3.2
 * @author CodeManas
 */
class Elementor {

	/**
	 * Constructor
	 *
	 * @since 1.3.2
	 * @author CodeManas
	 *
	 * @access public
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Add Actions
	 *
	 * @since 1.3.2
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function add_actions() {
		// Register widget scripts.
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'on_widgets_registered' ] );
	}

	/**
	 * On Widgets Registered
	 *
	 * @since 1.3.2
	 * @author CodeManas
	 *
	 * @access public
	 */
	public function on_widgets_registered() {
		$this->includes();
		$this->register_widget();
	}

	/**
	 * Includes
	 *
	 * @since 1.3.2
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function includes() {
		require VZAPI_ZOOM_PRO_ADDON_DIR_PATH . '/includes/Elementor/Widgets/MeetingList.php';
		require VZAPI_ZOOM_PRO_ADDON_DIR_PATH . '/includes/Elementor/Widgets/Calendar.php';
		require VZAPI_ZOOM_PRO_ADDON_DIR_PATH . '/includes/Elementor/Widgets/MeetingRegistrants.php';
	}

	/**
	 * Register Widget
	 *
	 * @since 1.3.2
	 * @author CodeManas
	 *
	 * @access private
	 */
	private function register_widget() {
		Plugin::instance()->widgets_manager->register_widget_type( new MeetingList() );
		Plugin::instance()->widgets_manager->register_widget_type( new Calendar() );
		Plugin::instance()->widgets_manager->register_widget_type( new MeetingRegistrants() );
	}
}