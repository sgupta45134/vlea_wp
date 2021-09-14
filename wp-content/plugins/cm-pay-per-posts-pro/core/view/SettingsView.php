<?php

namespace com\cminds\payperposts\view;

use com\cminds\payperposts\helper\SettingsListKeyValue;
use com\cminds\payperposts\helper\SettingsMicropaymentsBulk;
use com\cminds\payperposts\view\backend\micropayments\SettingsMicropayments;
use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Settings;

require_once dirname( __FILE__ ) . '/SettingsViewAbstract.php';

class SettingsView extends SettingsViewAbstract {

	public function renderSubcategory( $category, $subcategory ) {
		$content = parent::renderSubcategory( $category, $subcategory );
		if ( strlen( strip_tags( $content ) ) > 0 ) {
			return sprintf( '<table><caption>%s</caption>%s</table>',
				esc_html( $this->getSubcategoryTitle( $category, $subcategory ) ),
				$content
			);
		}

		return '';
	}


	public function renderOption( $name, array $option = array() ) {

		$required_class      = '';
		$required_dependency = '';
		if ( isset( $option['required'] ) ) {
			$required_class      = 'required';
			$required_dependency = "data-required-dependency='" . json_encode( $option['required_dependency'] ) . "'";
		}

		return sprintf( '<tr class="%s__option_wrapper %s" %s>%s</tr>', $name, $required_class, $required_dependency, parent::renderOption( $name, $option ) );
	}

	public function renderOptionTitle( $option ) {
		return sprintf( '<th scope="row" class="option-title">%s:</th>', parent::renderOptionTitle( $option ) );
	}


	public function renderOptionDescription( $option ) {
		return sprintf( '<td class="option-description">%s</td>', parent::renderOptionDescription( $option ) );
	}


	protected function getSubcategoryTitle( $category, $subcategory ) {
		$subcategories = $this->getSubcategories();
		if ( isset( $subcategories[ $category ] ) and isset( $subcategories[ $category ][ $subcategory ] ) ) {
			return __( $subcategories[ $category ][ $subcategory ] );
		} else {
			return $subcategory;
		}
	}


	protected function getCategories() {
		return apply_filters( App::MENU_SLUG . '_settings_pages', Settings::$categories );
	}


	protected function getSubcategories() {
		return apply_filters( App::MENU_SLUG . '_settings_pages_groups', Settings::$subcategories );
	}


	public function renderOptionControls( $name, array $option = array() ) {
		if ( empty( $option ) ) {
			$option = Settings::getOptionConfig( $name );
		}
		switch ( $option['type'] ) {
			case Settings::TYPE_MP_PRICE_GROUPS:
				$result = SettingsMicropayments::render( $name, $option );
				break;
			case Settings::TYPE_LIST_KEY_VALUE:
				$result = SettingsListKeyValue::render( $name, $option );
				break;
			case Settings::TYPE_MP_PRICE_GROUPS_BULK:
				$result = SettingsMicropaymentsBulk::render( $name, $option );
				break;
			default:
				$result = parent::renderOptionControls( $name, $option );
		}

		return sprintf( '<td class="option-controls">%s</td>', $result );
	}


}
