<?php

/**
 * Class WPML_ElementPack_Hover_Video
 */
class WPML_ElementPack_Hover_Video extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'hover_video_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'hover_video_title' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {

			case 'hover_video_title':
				return esc_html__( 'Title', 'bdthemes-element-pack' );

			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'hover_video_title':
                return 'LINE';
                
			default:
				return '';
		}
	}

}
