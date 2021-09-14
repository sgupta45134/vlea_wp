<?php

namespace com\cminds\payperposts\controller;

use com\cminds\payperposts\App;
use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model\Labels;
use com\cminds\payperposts\model\Settings;

abstract class Controller {

	static protected $instance;
	protected static $actions = array();
	protected static $filters = array();
	protected static $ajax = array();

	static function bootstrap() {

		foreach ( static::$actions as $i => $action ) {
			if ( ! is_array( $action ) ) {
				$action = array( 'name' => $action, 'priority' => 10, 'args' => 0 );
			}
			if ( empty( $action['name'] ) and ! is_numeric( $i ) ) {
				$action['name'] = $i;
			}
			if ( empty( $action['priority'] ) ) {
				$action['priority'] = 10;
			}
			if ( empty( $action['args'] ) ) {
				$action['args'] = 0;
			}
			if ( empty( $action['method'] ) ) {
				$action['method'] = strtr( $action['name'], '-', '_' );
			}
			add_action( $action['name'], array(
				get_called_class(),
				$action['method']
			), $action['priority'], $action['args'] );
		}

		foreach ( static::$filters as $i => $filter ) {
			if ( ! is_array( $filter ) ) {
				$filter = array( 'name' => $filter, 'priority' => 10, 'args' => 1 );
			}
			if ( empty( $filter['name'] ) and ! is_numeric( $i ) ) {
				$filter['name'] = $i;
			}
			if ( empty( $filter['priority'] ) ) {
				$filter['priority'] = 10;
			}
			if ( empty( $filter['args'] ) ) {
				$filter['args'] = 1;
			}
			if ( empty( $filter['method'] ) ) {
				$filter['method'] = strtr( $filter['name'], '-', '_' );
			}
			add_filter( $filter['name'], array(
				get_called_class(),
				$filter['method']
			), $filter['priority'], $filter['args'] );
		}

		foreach ( static::$ajax as $ajax ) {
			add_action( 'wp_ajax_' . $ajax, array( get_called_class(), $ajax ) );
			add_action( 'wp_ajax_nopriv_' . $ajax, array( get_called_class(), $ajax ) );
		}

// 		add_action('init', array(get_called_class(), 'init'), 3);
		add_action( is_admin() ? 'init' : 'template_redirect', array(
			get_called_class(),
			'processRequest'
		), PHP_INT_MAX );

	}

	static function processRequest() {

	}

	static function getInstance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	static function loadView( $_viewName, $_params = array() ) {
		$_viewPath = App::path( 'view/' . $_viewName . '.php' );
		if ( file_exists( $_viewPath ) ) {
			extract( $_params );
			ob_start();
			include $_viewPath;

			return ob_get_clean();
		} else {
			trigger_error( '[' . App::PREFIX . '] View not found: ' . $_viewName, E_USER_WARNING );
		}
	}

	static function shortClassName() {
		return App::shortClassName( get_called_class(), 'Controller' );
	}


	static function loadFrontendView( $_viewName, $_params = array() ) {
		$_viewName = 'frontend' . DIRECTORY_SEPARATOR . static::getViewNameControllerPart() . DIRECTORY_SEPARATOR . $_viewName;

		return static::loadView( $_viewName, $_params );
	}


	static function loadBackendView( $_viewName, $_params = array() ) {
		$_viewName = 'backend' . DIRECTORY_SEPARATOR . static::getViewNameControllerPart() . DIRECTORY_SEPARATOR . $_viewName;

		return static::loadView( $_viewName, $_params );
	}


	static function getViewNameControllerPart() {
		return strtolower( static::shortClassName() );
	}


	static function isAjax() {
		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' );
	}


	static function getBackendNav() {
		return '';
	}


	static function createBackendUrl( $page, $params = array(), $nonce = false ) {
		$params['page'] = $page;
		if ( $nonce !== false ) {
			$params['nonce'] = wp_create_nonce( $nonce );
		}

		return admin_url( 'admin.php' ) . ( $params ? '?' . http_build_query( $params ) : '' );
	}

	static function getBackLinkPost( $back_link = '' ) {
		if ( empty( $back_link ) ) {
			$Storage  = new Storage();
			$back_url = $Storage->get( 'cmppp_back_post_url', '/' );
		}

		if ( ! empty( $back_url ) && $back_url !== '/' ) {

			$arr = explode( '/', $back_url );

			$slug = $arr[ count( $arr ) - 1 ];
			if ( empty( $slug ) ) {
				$slug = $arr[ count( $arr ) - 2 ];
			}

			$posts = get_posts( [
				'post-name'   => $slug,
				'numberposts' => 1,
				'fields'      => 'post_type'
			] );

			$post = array_shift( $posts );

			return $post;

		}

		return null;
	}

	static function getBackLinkHtmlToPaidPost( $payment_id ) {

		$cmppp_back_post_id = get_post_meta( $payment_id, 'cmppp_back_post_id', true );

		if ( $cmppp_back_post_id ) {

			$back_link = get_post_permalink( $cmppp_back_post_id, false );

			if ( strpos( $back_link, '%' ) !== false ) {
				$back_link = get_post_permalink( $cmppp_back_post_id, true );
			}

		} else {
			$back_link = '/';
		}

		if ( $back_link ) {
			$post_type = 'Post';

			if ( $cmppp_back_post_id ) {
				$post = get_post( $cmppp_back_post_id );

				if ( ! empty( $post ) ) {
					$postTypeObject = get_post_type_object( $post->post_type );
					$post_type      = $postTypeObject->labels->singular_name;
				}
			}

			$text = '<a class="cmppp__go-back-to-the-post" style="display: inline-block; margin-bottom: 20px;" href="' . $back_link . '">
						<span class="dashicons dashicons-undo"></span> 
						Go back to the paid ' . $post_type .
			        '</a>';


			if ( Settings::getOption( Settings::OPTION_AUTOREDIRECT_TO_PAID_POST ) ) {
				$delay = Settings::getOption( Settings::OPTION_AUTOREDIRECT_TO_PAID_POST_SECONDS ) * 1000;

				if ( ! $delay ) {
					$delay = 5000;
				}

				$script = "<script>(function ($) { $(document).ready( function() {
    							let reloadDelay = parseInt({$delay});
    							
    							let backLinkWrapper = $('#cmppp_backlink');
    							let backlinkUrl = backLinkWrapper.find('a.cmppp__go-back-to-the-post').attr('href');
    							let backLinkLabelCountdown = backLinkWrapper.find('.backlink_label_countdown');
    							
								function countDownRun(reloadDelay, backlinkUrl, backLinkLabelCountdown) {
								    if (reloadDelay > 0) {
								        reloadDelay -= 1000;
								        backLinkLabelCountdown.text(reloadDelay/1000);
								        setTimeout(function () {
								            countDownRun(reloadDelay, backlinkUrl, backLinkLabelCountdown);
						                }, 1000)
								    } else {
								        backLinkLabelCountdown.text(0);
				                    	location.href = backlinkUrl;
								    }
								}
								
    							countDownRun(reloadDelay, backlinkUrl, backLinkLabelCountdown);
								
				            }); })(jQuery); </script>";


				$count_down_html = '<span class="backlink_label_countdown">' . ( $delay / 1000 ) . '</span>';
				$label           = str_replace( '%d', '%s', Labels::getLocalized( 'autoredirect_text' ) );
				$text            .= '<div class="backlink_label">' . sprintf( $label, $count_down_html ) . '</div>' . $script;

			}

			return $text;
		}

		return '';
	}

}
