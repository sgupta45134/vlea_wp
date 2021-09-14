<?php

namespace com\cminds\payperposts\view\backend\micropayments;

use com\cminds\payperposts\model\Settings;

class SettingsMicropayments {

	static $currency = '';
	static $currencyStep = 1;

	static function render( $name, $option ) {
		if ( isset( $option['currency'] ) ) {
			static::$currency = $option['currency'];
		}
		if ( isset( $option['currencyStep'] ) ) {
			static::$currencyStep = $option['currencyStep'];
		}
		$output = '';
		$value  = Settings::getOption( $name );
		if ( ! empty( $value ) and is_array( $value ) ) {
			foreach ( $value as $groupIndex => $group ) {
				$output .= self::renderMicropaymentsGroup( $name, $groupIndex, $group );
			}
		}
		$output = sprintf( '<div class="cmmp-groups">%s</div>', $output );
		$output .= '<p><input type="button" value="Add group" class="button cmmp-group-add" data-template="' .
		           self::templateAttr( self::renderMicropaymentsGroup( $name, '__group_index__', array() ) ) . '" /></p>';

		return $output;
	}


	static function templateAttr( $value ) {
		return htmlspecialchars( strtr( $value, array( "\n" => '', "\r" => '', "\t" => '' ) ) );
	}


	protected static function renderMicropaymentsGroup( $optionName, $groupIndex, $group ) {
		$output = '';
		
		if ( ! empty( $group['prices'] ) and is_array( $group['prices'] ) ) {
			foreach ( $group['prices'] as $priceIndex => $price ) {
				$output .= self::renderMicropaymentsPrice( $optionName, $groupIndex, $priceIndex, $price );
			}
		}

		$output = sprintf( '<label><input type="text" name="%s" value="%s" placeholder="Group name" /> <input type="button" value="Remove group" class="cmmp-group-remove button" /></label>
			<div class="cmmp-prices">%s</div>',
			esc_attr( $optionName . '[' . $groupIndex . '][name]' ),
			esc_attr( ! empty( $group['name'] ) ? $group['name'] : '' ),
			$output );

		$output .= sprintf( '<input type="button" value="Add price" data-template="%s" class="button cmmp-price-add" />',
			self::templateAttr( self::getMicropaymentsItem( $optionName, $groupIndex, '__item_index__' ) ) );

		$output .= '<a href="cmppp_price_group_modal-init-js"
					   onclick="return window.cmpppPriceGroupModalInit(this, \'' . $optionName . '\', ' . $groupIndex . ');" 
					   class="button cmppp-assign-posts-to-group">Manage assigned Posts, Pages etc.</a>';

		if ( $optionName === 'cmppp_edd_pricing_groups' || $optionName === 'cmppp_woo_pricing_groups') {
			$output .= sprintf("<div class='cmmp-group__group-index' style='margin-top:10px;'>
					To show this group's paybox somewhere use this shortcode:
					<b>[cmppp-group-paybox group_id=\"%s\"]</b>
				</div>", $groupIndex );
		}

		return sprintf( '<div class="cmmp-group" style="position: relative" data-group-index="%s">%s</div>', $groupIndex, $output );
	}


	protected static function getMicropaymentsItem( $optionName, $groupIndex, $priceIndex ) {
		$name = $optionName . '[' . $groupIndex . '][prices][' . $priceIndex . ']';

		return '<div class="cmmp-price" data-price-index="' . $priceIndex . '">
			<label><input type="number" name="' . $name . '[number]" value="%d" placeholder="Period" /><select name="' . $name . '[unit]">
					<option value="min"%s>minutes</option>
					<option value="h"%s>hours</option>
					<option value="d"%s>days</option>
					<option value="w"%s>weeks</option>
					<option value="m"%s>months</option>
					<option value="y"%s>years</option>
					<option value="l"%s>lifetime</option>
				</select>
			</label>
			<label> for <input type="number" name="' . $name . '[price]" value="' . ( static::$currencyStep == 1 ? '%d' : '%.2f' ) . '" step="' . static::$currencyStep . '" /> ' . static::$currency . '</label>
			<input type="button" value="Remove" class="button cmmp-price-remove" />
		</div>';
	}


	protected static function renderMicropaymentsPrice( $optionName, $groupIndex, $priceIndex, $price ) {
		$name     = $optionName . sprintf( '[%d][%d]', $groupIndex, $priceIndex );
		$template = self::getMicropaymentsItem( $optionName, $groupIndex, $priceIndex );

		return sprintf( $template,
			$price['number'],
			selected( $price['unit'], 'min', false ),
			selected( $price['unit'], 'h', false ),
			selected( $price['unit'], 'd', false ),
			selected( $price['unit'], 'w', false ),
			selected( $price['unit'], 'm', false ),
			selected( $price['unit'], 'y', false ),
			selected( $price['unit'], 'l', false ),
			$price['price']
		);
	}


}
