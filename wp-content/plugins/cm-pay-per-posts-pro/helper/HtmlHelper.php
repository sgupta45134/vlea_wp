<?php

namespace com\cminds\payperposts\helper;

class HtmlHelper {

	static function renderRadioGroup( $name, array $options, $currentValue ) {
		$out = '';
		foreach ( $options as $value => $label ) {
			$out .= sprintf( '<label><input type="radio" name="%s" value="%s"%s /> %s</label>',
				esc_attr( $name ),
				esc_attr( $value ),
				checked( $value, $currentValue, false ),
				esc_html( $label )
			);
		}

		return $out;
	}

	static function renderCheckboxGroup( $name, array $options, $currentValue ) {
		$out = '';
		foreach ( $options as $value => $label ) {
			if ( empty( $value ) ) {
				$out .= sprintf( '<div><label><input type="hidden" name="%s[]" value="%s" /> %s</label></div>',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_html( $label )
				);
			} else {
				$out .= sprintf( '<div><label><input type="checkbox" name="%s[]" value="%s"%s /> %s</label></div>',
					esc_attr( $name ),
					esc_attr( $value ),
					(in_array($value, $currentValue)) ? "checked" : "",
					esc_html( $label )
				);
			}

		}

		return $out;
	}

	static function renderBooleanRadio( $name, $currentValue, $notSetOption = false ) {
		$options = array(
			1 => 'Yes',
			0 => 'No',
		);
		if ( $notSetOption ) {
			$options      = array( 'NULL' => 'Do not set' ) + $options;
			$currentValue = ( is_null( $currentValue ) ? 'NULL' : intval( $currentValue ) );
		} else {
			$currentValue = intval( $currentValue );
		}

		return static::renderRadioGroup( $name, $options, $currentValue );
	}

	static function renderSelect( $name, array $options, $currentValue ) {
		$out = sprintf( '<select name="%s">', esc_attr( $name ) );
		foreach ( $options as $value => $label ) {
			$out .= sprintf( '<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $value, $currentValue, false ),
				esc_html( $label )
			);
		}
		$out .= '</select>';

		return $out;
	}

	static function renderBooleanSelect( $name, $currentValue, $notSetOption = false ) {
		$options = array(
			1 => 'Yes',
			0 => 'No',
		);
		if ( $notSetOption ) {
			$options      = array( 'NULL' => 'Do not set' ) + $options;
			$currentValue = ( is_null( $currentValue ) ? 'NULL' : intval( $currentValue ) );
		} else {
			$currentValue = intval( $currentValue );
		}

		return static::renderSelect( $name, $options, $currentValue );
	}

}
