<?php

namespace Codemanas\ZoomPro\Core;

/**
 * Interface for fields
 *
 * @author Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since 1.0.0
 * @package Codemanas\ZoomPro
 */
interface FieldsInterface {

	public static function set_option( $key, $value );

	public static function get_option( $key );

	public static function set_post_meta( $post_id, $key, $value );

	public static function get_meta( $post_id, $key );

	public static function delete_option( $key );

	public static function get_user_meta( $user_id, $key );

	public static function set_user_meta( $user_id, $key, $value );

	public static function set_cache( $post_id, $key, $value, $time );

	public static function get_cache( $post_id, $key );

	public static function flush_cache( $post_id, $key );

}