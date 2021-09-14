<?php

namespace Codemanas\ZoomPro\Elementor\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Elementor widget for Meeting Lists
 *
 * @since 3.4.0
 * @author CodeManas
 */
class MeetingList extends Widget_Base {

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
		return 'vczapi_pro_list_meetings';
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
		return __( 'Zoom Events List (PRO)', 'vczapi-pro' );
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
			'category',
			[
				'name'        => 'category',
				'label'       => __( 'Meetings Category', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $this->get_taxnomies(),
				'default'     => ''
			]
		);

		$this->add_control(
			'order',
			[
				'name'        => 'order',
				'label'       => __( 'Meetings Order By', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'ASC'  => 'Ascending',
					'DESC' => 'Descending'
				],
				'default'     => 'DESC'
			]
		);

		$this->add_control(
			'type',
			[
				'name'        => 'type',
				'label'       => __( 'Meetings Type', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					''         => 'Show All',
					'upcoming' => 'Upcoming',
					'past'     => 'Past'
				],
				'default'     => ''
			]
		);

		$this->add_control(
			'filter',
			[
				'name'        => 'filter',
				'label'       => __( 'Show Filter', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'yes' => 'Yes',
					'no'  => 'No'
				],
				'default'     => 'no'
			]
		);

		$this->add_control(
			'count',
			[
				'name'        => 'count',
				'label'       => __( 'Count of meetings', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => 3
			]
		);

		$this->add_control(
			'show',
			[
				'name'        => 'show',
				'label'       => __( 'Show Type of Meeting', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					'' => 'Show Both',
					'meeting' => 'Meeting',
					'webinar' => 'Webinar'
				],
				'default'     => ''
			]
		);

		$this->add_control(
			'cols',
			[
				'name'        => 'cols',
				'label'       => __( 'Column Layout', 'vczapi-pro' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => [
					1 => '1 column layout',
					2 => '2 column layout',
					3 => '3 column layout',
					4 => '4 column layout',
				],
				'default'     => 3
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Taxonomies for Zoom meeting
	 *
	 * @return array
	 */
	private function get_taxnomies() {
		$args   = array(
			'taxonomy'   => 'zoom-meeting',
			'hide_empty' => false
		);
		$terms  = get_terms( $args );
		$result = [];
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->slug ] = $term->name;
			}
		}

		return $result;
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

		$count        = ! empty( $settings['count'] ) ? $settings['count'] : 5;
		$category     = ! empty( $settings['category'] ) ? implode( ',', $settings['category'] ) : '';
		$type         = ! empty( $settings['type'] ) ? $settings['type'] : '';
		$order        = ! empty( $settings['order'] ) ? $settings['order'] : 'DESC';
		$filter       = ! empty( $settings['filter'] ) ? $settings['filter'] : 'no';
		$meeting_type = ! empty( $settings['show'] ) ? $settings['show'] : '';
		$columns      = ! empty( $settings['cols'] ) ? 'cols=' . $settings['cols'] : 3;

		echo do_shortcode( '[vczapi_list_meetings filter="' . esc_attr( $filter ) . '" per_page="' . esc_attr( $count ) . '" category="' . esc_attr( $category ) . '" order="' . esc_attr( $order ) . '" ' . $columns . ' type="' . esc_attr( $type ) . '"  show="' . esc_attr( $meeting_type ) . '"]' );
	}
}