<?php

/**
 * Class WPML_ElementPack_User_Login
 */
class WPML_ElementPack_User_Login extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'custom_navs';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'custom_nav_title' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {

			case 'custom_nav_title':
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
			case 'custom_nav_title':
				return 'LINE';

			default:
				return '';
		}
	}

}
