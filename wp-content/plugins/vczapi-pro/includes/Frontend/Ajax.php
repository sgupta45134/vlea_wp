<?php

namespace Codemanas\ZoomPro\Frontend;

/**
 * Class Ajax
 *
 * Template Hook Register for all ajax methods
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class Ajax {

	private $actions;

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		$this->register_actions();
		$this->process_hooks();
	}

	/**
	 * Register all Ajax Actions here
	 */
	public function register_actions() {
		$this->actions = [
			'register_user'           => [ Registrations::get_instance(), 'register_user' ],
			'create_meeting_frontend' => [ Meetings::get_instance(), 'save' ],
			'get_author_meeting_list' => [ Meetings::get_instance(), 'get_author_meetings' ],
			'get_meeting_registrants' => [ Registrations::get_instance(), 'get_meeting_registrants' ]
		];
	}

	/**
	 * Process WP AJAX
	 */
	public function process_hooks() {
		foreach ( $this->actions as $k => $action ) {
			add_action( 'wp_ajax_nopriv_' . $k, $action );
			add_action( 'wp_ajax_' . $k, $action );
		}
	}
}