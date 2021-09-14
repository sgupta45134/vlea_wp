<?php

namespace Codemanas\ZoomPro\Elementor\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Calender View
 *
 * @since 3.4.0
 * @author CodeManas
 */
class Calendar extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_name() {
		return 'vczapi_pro_calendar';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Zoom Calendar (PRO)', 'vczapi-pro' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 * @since 3.4.0
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
	 * @since 3.4.0
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
			'author_id',
			[
				'name'        => 'author_id',
				'label'       => __( 'Author ID', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
			]
		);

		$this->add_control(
			'css_class',
			[
				'name'        => 'css_class',
				'label'       => __( 'CSS Class', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => 'vczapi-pro-calendar-custom'
			]
		);

		$this->add_control(
			'meeting_type',
			[
				'name'        => 'meeting_type',
				'label'       => __( 'Show Type of Meeting', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'meeting' => 'Meeting',
					'webinar' => 'Webinar'
				],
				'default'     => 'meeting'
			]
		);

		$this->add_control(
			'calendar_default_view',
			[
				'name'        => 'calendar_default_view',
				'label'       => __( 'Calendar Views', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'dayGridMonth' => 'Grid Months',
					'timeGridWeek' => 'Grid Week',
					'timeGridDay'  => 'Grid Day',
					'listWeek'     => 'List'
				],
				'default'     => 'dayGridMonth'
			]
		);

		$this->add_control(
			'show_calendar_views',
			[
				'name'        => 'show_calendar_views',
				'label'       => __( 'Calendar Views', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'no'
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 3.4.0
	 * @author CodeManas
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$author_id             = ! empty( $settings['author_id'] ) ? $settings['author_id'] : false;
		$css_class             = ! empty( $settings['css_class'] ) ? $settings['css_class'] : '';
		$meeting_type          = ! empty( $settings['meeting_type'] ) ? $settings['meeting_type'] : 'meeting';
		$calendar_default_view = ! empty( $settings['calendar_default_view'] ) ? $settings['calendar_default_view'] : 'dayGridMonth';
		$show_calendar_views   = ! empty( $settings['show_calendar_views'] ) ? $settings['show_calendar_views'] : 'no';

		echo do_shortcode( '[vczapi_zoom_calendar class="' . esc_attr( $css_class ) . '" author="' . esc_attr( $author_id ) . '" show="' . esc_attr( $meeting_type ) . '" calendar_default_view="' . esc_attr( $calendar_default_view ) . '" show_calendar_views="' . esc_attr( $show_calendar_views ) . '"]' );
	}
}