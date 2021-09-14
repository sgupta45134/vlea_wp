<?php

/**
 * Class WPML_Jet_Elements_Team_Member
 */
class WPML_ElementPack_Team_Member extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'social_link_list';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'social_link_title' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'social_link_title':
				return esc_html__( 'Social Label', 'bdthemes-element-pack' );

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
			case 'social_link_title':
				return 'LINE';

			default:
				return '';
		}
	}

}
