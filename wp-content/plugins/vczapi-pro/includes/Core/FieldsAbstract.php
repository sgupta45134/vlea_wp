<?php

namespace Codemanas\ZoomPro\Core;

use Codemanas\ZoomPro\Core\FieldsInterface;

/**
 * Class Fields
 *
 * Get and set necessary fields
 *
 * @author Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since 1.0.0
 * @package Codemanas\ZoomPro
 */
abstract class FieldsAbstract implements FieldsInterface {

	/**
	 * Item ID of this product
	 * @var int
	 */
	private $item_id;

	/**
	 * Validate with us otherwise fails
	 *
	 * @var string
	 */
	private $store_url;

	/**
	 * Settings page link
	 *
	 * @var string
	 */
	private $options_page;

	protected $fields;

	/**
	 * Fields constructor.
	 */
	public function __construct() {
		$this->item_id      = 4773;
		$this->store_url    = 'https://www.codemanas.com';
		$this->options_page = 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=pro-licensing&section=licensing';
	}

	/**
	 * Set ITEM ID
	 *
	 * @param bool $type
	 *
	 * @return array|int|mixed
	 */
	public function item_id( $type = false ) {
		if ( $type ) {
			return $this->item_id[ $type ];
		}

		return $this->item_id;
	}

	/**
	 * SET STORE URL
	 *
	 * @return string
	 */
	public function store_url() {
		return $this->store_url;
	}

	/**
	 * Set options page url
	 *
	 * @return string
	 */
	public function options_page() {
		return $this->options_page;
	}


}