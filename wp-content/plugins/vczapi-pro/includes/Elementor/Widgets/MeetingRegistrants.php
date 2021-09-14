<?php

namespace Codemanas\ZoomPro\Elementor\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Meeting Registrants
 *
 * @since 1.5.8
 * @author CodeManas
 */
class MeetingRegistrants extends Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 * @since 1.5.8
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_name() {
		return 'vczapi_pro_list_meeting_registrants';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since 1.5.8
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Registered Events (PRO)', 'vczapi-pro' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since 1.5.8
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'fas fa-video';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @return array Widget categories.
	 * @since 1.5.8
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_categories() {
		return [ 'vczapi-elements' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'vczapi-pro' ),
			]
		);

		$this->add_control(
			'wp_user_id',
			[
				'name'        => 'wp_user_id',
				'label'       => __( 'User ID', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => null,
				'description' => __( 'Enter a user ID to show registered meeting for only the selected User. Leave this field blank to show registered events for the loggedin user.', 'vczapi-pro' )
			]
		);

		$this->add_control(
			'zoom_registrants_show',
			[
				'name'        => 'zoom_registrants_show',
				'label'       => __( 'Show', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'upcoming' => 'Upcoming',
					'past'     => 'Past'
				],
				'default'     => 'upcoming'
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.5.8
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$user_id = ! empty( $settings['wp_user_id'] ) ? $settings['wp_user_id'] : false;
		$show    = ! empty( $settings['zoom_registrants_show'] ) ? $settings['zoom_registrants_show'] : 'upcoming';

		echo do_shortcode( '[vczapi_registered_meetings user_id="' . $user_id . '" show="' . $show . '"]' );
	}
}