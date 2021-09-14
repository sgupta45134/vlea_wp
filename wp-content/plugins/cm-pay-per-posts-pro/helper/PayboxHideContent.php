<?php

namespace com\cminds\payperposts\helper;

use com\cminds\payperposts\model\Settings;
use DOMDocument;
use DOMXPath;

if ( ! function_exists( 'is_plugin_active' ) ) {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

}

class PayboxHideContent {

	public static function init() {
		$is_hide = self::isHidePageContent();
		if ( $is_hide == Settings::HIDE_FULL_PAGE_CONTENT || $is_hide == Settings::HIDE_SPECIFIED_BLOCK ) {
			add_action( 'init', array( __CLASS__, 'buffer_start' ), 20 );
		}
	}

	public static function isHidePageContent() {
		$is_hide = Settings::getOption( Settings::OPTION_HIDE_PAGE_CONTENT );

		if ( $is_hide ) {
			// check is the current page is a page or some post where we can hide the content

			$skip_for_this_urls = [
				'admin-ajax.php',
				'index.php',
				'.php',
				'.css',
				'.js',
			];

			foreach ( $skip_for_this_urls as $mask ) {
				if ( strpos( $_SERVER['REQUEST_URI'], $mask ) !== false
				     || $_SERVER['REQUEST_URI'] === '/' ) {
					return false;
				}
			}

			$request_uri_arr = array_values( array_filter( explode( '/', $_SERVER['REQUEST_URI'] ), function ( $value ) {
				return ! is_null( $value ) && $value !== '';
			} ) );


			$key = count( $request_uri_arr ) - 1;

			$post_name = $request_uri_arr[ $key ];

			$supported_post_types = Settings::getOption( Settings::OPTION_SUPPORTED_POST_TYPES );
			foreach ( $supported_post_types as &$item ) {
				$item = "'$item'";
			}

			global $wpdb;
			$query = $wpdb->prepare( "
				SELECT * FROM {$wpdb->prefix}posts 
				WHERE post_type IN (" . implode( ',', $supported_post_types ) . ") 
				AND post_name='%s'
			", $post_name );

			$res = $wpdb->get_results( $query );

			return ( ! empty( $res ) ) ? $is_hide : false;
		}

		return false;
	}

	public static function buffer_start() {
		/*
		* Fix for W3TC
		*/
		$W3TC_enabled = defined( 'W3TC' );
		if ( ! $W3TC_enabled ) {
			add_action( 'shutdown', array( __CLASS__, 'buffer_end' ), 0 );
			add_filter( 'final_output', array( __CLASS__, 'parse' ), get_option( 'cmaf_priority', 20000 ) );
			ob_start();
		} else {
			add_filter( 'w3tc_processed_content', array( __CLASS__, 'parse' ), get_option( 'cmaf_priority', 20000 ) );
		}
	}

	public static function buffer_end() {
		$hide_content = self::isHidePageContent();

		if ( $hide_content ) {
			$final = '';

			// We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
			// that buffer's output into the final output.
			$levels = ob_get_level();

			for ( $i = 0; $i < $levels; $i ++ ) {
				$final .= ob_get_clean();
			}

			// Apply any filters to the final output
			if ( ! empty( $final ) && is_string( $final ) ) {
				$html = $final;
				$dom  = new DOMDocument();
				/*
				 * loadXml needs properly formatted documents, so it's better to use loadHtml, but it needs a hack to properly handle UTF-8 encoding
				 */
				libxml_use_internal_errors( true );
				if ( ! $dom->loadHtml( mb_convert_encoding( $html, 'HTML-ENTITIES', "UTF-8" ) ) ) {
					libxml_clear_errors();
				}
				$xpath = new DOMXPath( $dom );

				$query    = "//div[contains(@class, 'cmppp_content_container cmppp-not-allowed')]";
				$payboxes = $xpath->query( $query );

				if ( ! empty( $payboxes ) && $payboxes->length ) {
					$payboxesNode = $payboxes->item( 0 );

					if ( $payboxesNode->attributes->length ) {
						$payboxes_parent_class         = ( $hide_content == Settings::HIDE_FULL_PAGE_CONTENT ) ? "cmppp-paybox-with-empty-page-body" : "cmppp-paybox-with-empty-block";
						$current_class_attr            = $payboxesNode->attributes->getNamedItem( 'class' );
						$current_class_attr->nodeValue .= ' ' . $payboxes_parent_class;
					}

					$nodes_length = $payboxesNode->childNodes->length;

					for ( $i = 0; $i < $nodes_length; $i ++ ) {
						$item = $payboxesNode->childNodes->item( $i );
						if ( isset( $item->attributes ) && $item->attributes->length ) {
							$class_attr = $item->attributes->getNamedItem( 'class' );
							if ( strpos( $class_attr->nodeValue, 'cmppp_content_inner_container_with_paybox' ) !== false ) {
								$item->textContent = "";
							}
						}
					}

					$bodyNode = $xpath->query( '//body' )->item( 0 );

					if ( $bodyNode !== null && $hide_content == Settings::HIDE_FULL_PAGE_CONTENT ) {
						$bodyNode->textContent = '';
						$bodyNode->appendChild( $payboxesNode );

						$current_body_class_attr            = $bodyNode->attributes->getNamedItem( 'class' );
						$current_body_class_attr->nodeValue .= ' ' . 'cmppp-hidden-full-content';

						$newDom = new DOMDocument();
						$newDom->appendChild( $newDom->importNode( $bodyNode, true ) );
						$html = $newDom->saveHTML();

						$prefix  = 'cmppp-';
						$scripts = ScriptsHelper::printScripts(
							ScriptsHelper::getScriptList( $prefix ),
							$prefix,
							[ 'logout-heartbeat' ]
						);

						// if cm register plugin is enabled
						if ( is_plugin_active( 'cm-invitation-codes/cm-registration.php' ) ) {
							$scripts .= ScriptsHelper::printCmRegisterScripts();
						}

						// if cm register pro plugin is enabled
						if ( is_plugin_active( 'cm-registration-pro/cm-registration-pro.php' ) ) {
							$scripts .= ScriptsHelper::printCmRegisterProScripts();
						}

						$html  = str_replace( "</body>", $scripts . '</body>', $html );
						$final = $html;
					}

					if ( $hide_content == Settings::HIDE_SPECIFIED_BLOCK ) {
						$class_prefix       = 'cmppp-hidden-';
						$element_id         = trim( Settings::getOption( Settings::OPTION_HIDE_PAGE_CONTENT_ID ) );
						$additional_classes = [ $class_prefix . $element_id ];
						if ( ! empty( $element_id ) ) {
							$final = self::clearElementContent( $final, $element_id, $dom, $xpath, $payboxesNode );
						}

						$additional_element_ids = trim( Settings::getOption( Settings::OPTION_HIDE_PAGE_CONTENT_ADDITIONAL_BLOCKS ) );
						if ( ! empty( $additional_element_ids ) ) {
							$additional_element_ids_arr = explode( ',', $additional_element_ids );

							foreach ( $additional_element_ids_arr as $additional_element_id ) {
								$additional_element_id = trim( $additional_element_id );
								$additional_classes[]  = $class_prefix . $additional_element_id;
								$final                 = self::clearElementContent( $final, $additional_element_id, $dom, $xpath, null );
							}
						}


						if ( $bodyNode !== null ) {
							if ( ! is_null( $bodyNode ) ) {
								$current_class_attr            = $bodyNode->attributes->getNamedItem( 'class' );
								$current_class_attr->nodeValue .= ' ' . implode( ' ', $additional_classes );
								$final                         = $dom->saveHTML();
							}
						}

					}
				}
			}

			echo apply_filters( 'final_output', $final );
		}
	}

	public static function clearElementContent( $final, $element_id, $dom, $xpath, $payboxesNode = null ) {
		$html_tags = [
			'div',
			'span',
			'main',
			'header',
			'footer',
			'aside',
			'article',
			'p',
			'a',
			'section',
			'ul',
			'form',
			'iframe',
			'map',
			'nav',
			'object',
			'table',
			'video'
		];

		foreach ( $html_tags as $html_tag ) {
			$query     = "//{$html_tag}[contains(@id, '{$element_id}')]";
			$foundNode = $xpath->query( $query );
			if ( ! is_null( $foundNode ) && $foundNode->length ) {
				$foundNode              = $foundNode->item( 0 );
				$foundNode->textContent = "";
				if ( isset( $payboxesNode ) ) {
					$foundNode->appendChild( $payboxesNode );
				}
				$final = $dom->saveHTML();
			}
		}

		return $final;
	}

	public static function parse( $content ) {
		global $post, $wp_query;

		$is_feed = is_feed();
		if ( $is_feed ) {
			return $content;
		}

		if ( is_admin() ) {
			return $content;
		}

		$seo = doing_action( 'wpseo_head' );
		if ( $seo ) {
			return $content;
		}

		if ( ! is_object( $post ) ) {
			$post = $wp_query->post;
		}

		$runParser = apply_filters( 'cmaf_parse', true );
		if ( ! $runParser ) {
			return $content;
		}

		do_action( 'cmaf_after_parsed_content', $post, $content );

		return $content;
	}
}
