<?php

namespace Codemanas\ZoomPro\Backend;

use Codemanas\ZoomPro\Backend\Registrations\Registrations;
use Codemanas\ZoomPro\Backend\Sync\Sync;
use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Fields;

/**
 * Class Ajax
 *
 * All Admin Ajax calls
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro\Admin
 */
class Ajax {

	private $actions;

	/**
	 * @var null
	 */
	private $zoom_api = null;

	/**
	 * Build the instance
	 */
	public function __construct() {
		$this->zoom_api = API::get_instance();
		$this->register_actions();
		$this->process_hooks();
	}

	/**
	 * Register all Ajax Actions here
	 */
	public function register_actions() {
		$registration  = Registrations::get_instance();
		$this->actions = [
			'get_registrants_lists'     => [ $registration, 'meeting_registrants' ],
			'update_registrants_status' => [ $registration, 'update_registrants_status' ],
			'sync_meeting_id'           => [ Sync::class, 'syncByMeetingID' ],
			'sync_user'                 => [ Sync::class, 'syncByUser' ]
		];
	}

	/**
	 * Process WP AJAX
	 */
	public function process_hooks() {
		foreach ( $this->actions as $k => $action ) {
			add_action( 'wp_ajax_' . $k, $action );
		}
	}
}