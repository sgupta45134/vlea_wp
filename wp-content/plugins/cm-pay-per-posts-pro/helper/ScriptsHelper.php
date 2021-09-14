<?php

namespace com\cminds\payperposts\helper;

use com\cminds\payperposts\App;
use com\cminds\payperposts\helper\Storage;
use com\cminds\payperposts\model\Settings;
use com\cminds\registration\App as AppRegisterPlugin;
use com\cminds\registration\model\Settings as SettingsRegisterPlugin;
use com\cminds\registration\model\Labels as LabelsRegisterPlugin;

class ScriptsHelper {

	const PREFIX = 'cmppp';

	public static function getScriptList( $prefix = '' ) {

		$scripts = [
			'utils'    => [ 'deps' => [ 'jquery' ] ],
			'backend'  => [ 'deps' => [ 'jquery' ] ],
			'frontend' => [
				'deps'               => [ 'jquery', $prefix . 'utils' ],
				'wp_localize_script' => [
					'objectName' => 'CMPPPSettings',
					'objectData' => [
						'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
						'restrictCopyingContent' => Settings::getOption( Settings::OPTION_RESTRICT_COPYING_CONTENT ),
						'userTimeOffset'         => ( Storage::get_user_time_offset() ) ? Storage::get_user_time_offset() : 'undefined',
					]
				]
			],
		];

		if ( Settings::getOption( Settings::OPTION_RELOAD_EXPIRED_SUBSCRIPTION ) ) {
			$scripts['logout-heartbeat'] = [ 'deps' => [ 'jquery', 'heartbeat' ] ];
		}

		$styles = [
			'settings',
			'backend',
			'frontend',
			'common',
		];

		return [ 'scripts' => $scripts, 'styles' => $styles ];
	}

	public static function registerScripts( $scripts_n_styles = [], $prefix = '' ) {
		if ( empty( $scripts_n_styles ) ) {
			return;
		}

		foreach ( $scripts_n_styles as $key => $arr ) {

			if ( $key == 'scripts' ) {
				$scripts = $arr;
				foreach ( $scripts as $name => $data ) {
					$handle = $prefix . $name;
					$src    = App::url( 'asset/js/' . $name . '.js' );
					$ver    = App::VERSION;
					wp_register_script( $handle . '', $src . '', (array) $data['deps'], $ver . '', true );

					if ( isset( $data['wp_localize_script'] ) && ! empty( $data['wp_localize_script'] ) ) {
						$loc_script = $data['wp_localize_script'];
						$handle     = $prefix . $name;
						$objectName = $loc_script['objectName'];
						wp_localize_script( $handle . '', $objectName . '', (array) $loc_script['objectData'] );
					}
				}
			}

			if ( $key == 'styles' ) {
				$styles = $arr;
				foreach ( $styles as $style ) {
					$handle = "{$prefix}{$style}";
					$src    = App::url( "asset/css/{$style}.css" );
					$ver    = App::VERSION;
					wp_register_style( $handle . '', $src . '', null, $ver . '' );
				}
			}
		}
	}

	public static function printScripts( $scripts_n_styles = [], $prefix = '', $except = [] ) {
		$html = '';
		if ( ! empty( $scripts_n_styles ) ) {
			$ver         = App::VERSION;
			$scripts_str = '';
			$styles_str  = '';

			$jquery_is_added = 0;
			foreach ( $scripts_n_styles as $key => $arr ) {
				if ( $key == 'scripts' ) {
					foreach ( $arr as $name => $data ) {

						if ( in_array( $name, $except ) ) {
							continue;
						}

						if ( isset( $data['wp_localize_script'] ) && ! empty( $data['wp_localize_script'] ) ) {
							$objectDataStr = json_encode( $data['wp_localize_script']['objectData'] );
							$scripts_str   .= '<script type="text/javascript" id="' . $prefix . $name . '-js-extra">
							/* <![CDATA[ */
							var ' . $data['wp_localize_script']['objectName'] . ' = ' . $objectDataStr . ';
							/* ]]> */
							</script>';
						}

						if ( isset( $data['deps'] ) && ! empty( $data['deps'] ) && in_array( 'jquery', $data['deps'] ) && ! $jquery_is_added ) {
							$scripts_str     .= '<script src="/wp-includes/js/jquery/jquery.js"></script>';
							$jquery_is_added = 1;
						}

						$scripts_str .= '<script src="' . App::url( 'asset/js/' . $name . '.js' ) . '?ver=' . $ver . '" id="' . $prefix . $name . '-js"></script>';
					}

					$html .= $scripts_str;
				}
				if ( $key == 'styles' ) {
					foreach ( $arr as $style_name ) {
						$styles_str .= '<link rel="stylesheet" id="' . $prefix . $style_name . '-css" href="' . App::url( 'asset/css/' . $style_name . '.css' ) . '?ver=' . $ver . '" type="text/css" media="all">';
					}

					$html .= $styles_str;
				}
			}
		}

		return $html;
	}

	public static function printCmRegisterScripts() {

		$ver = AppRegisterPlugin::VERSION;

		$objectDataStr = array(
			'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
			'isUserLoggedIn'    => intval( is_user_logged_in() ),
			'logoutUrl'         => wp_logout_url(),
			'logoutButtonLabel' => LabelsRegisterPlugin::getLocalized( 'logout_button' ),
			'overlayPreload'    => intval( SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_OVERLAY_PRELOAD ) ),
		);

		$html = '<script type="text/javascript">
				/* <![CDATA[ */
				var CMREG_Settings = ' . json_encode( $objectDataStr ) . ';
				/* ]]> */
				</script>';

		$base_path = content_url( '/plugins/cm-invitation-codes/' );

		$html .= '<script src="' . $base_path . 'asset/js/utils.js?v=' . $ver . '"></script>';
		$html .= '<script src="//www.google.com/recaptcha/api.js"></script>';
		$html .= '<script src="' . $base_path . 'asset/js/create-invitation-code.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/vendors/form-builder/form-builder.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/vendors/form-builder/form-render.min.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/js/backend-profile-fields.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/js/frontend.js?v=' . $ver . '"></script>';

		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/settings.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/backend.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/frontend.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/common.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/vendors/form-builder/form-builder.min.css?ver=" type="text/css" media="all" />';


		return $html;
	}

	public static function printCmRegisterProScripts() {

		$ver = AppRegisterPlugin::VERSION;

		$objectDataStr = array(
			'toastMessageTime' => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_TOAST_MESSAGE_TIME ),
		);

		$html = '<script type="text/javascript">
				/* <![CDATA[ */
				var CMREG_FrontendUtilsFieldsSettings = ' . json_encode( $objectDataStr ) . ';
				/* ]]> */
				</script>';

		$base_path = content_url( '/plugins/cm-registration-pro/' );

		$html .= '<script src="' . $base_path . 'asset/js/utils.js?v=' . $ver . '"></script>';
		$html .= '<script src="//www.recaptcha.net/recaptcha/api.js"></script>';
		$html .= '<script src="' . $base_path . 'asset/js/create-invitation-code.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/vendors/form-builder/form-builder.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/vendors/form-builder/form-render.min.js?v=' . $ver . '"></script>';
		$html .= '<script src="' . $base_path . 'asset/js/social-login-invitation-code.js?v=' . $ver . '"></script>';

		$html .= '<script type="text/javascript">
				/* <![CDATA[ */
				var CMREG_FrontendFieldsSettings = ' . json_encode( array(
				'toastMessageTimeForRegister' => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_TOAST_MESSAGE_TIME_FOR_REGISTER ),
			) ) . ';
				/* ]]> */
				</script>';


		$exclude_urls = '';
		if ( SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_EXCLUDE_REDIRECT_URL ) != '' ) {
			$exclude_urls = preg_split( '/\r\n|\r|\n/', SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_EXCLUDE_REDIRECT_URL ) );
		}

		if ( get_option( 'show_on_front' ) == 'posts' && is_home() ) {
			$loginAuthenticationPopupEnable = '0';
		} else {
			if ( isset( $post->ID ) && get_post_meta( $post->ID, 'cmreg_login_access', true ) ) {
				$loginAuthenticationPopupEnable = '1';
			} else {
				$loginAuthenticationPopupEnable = '0';
			}
		}

		$objectData = array(
			'ajaxUrl'                         => admin_url( 'admin-ajax.php' ),
			'isUserLoggedIn'                  => intval( is_user_logged_in() ),
			'logoutUrl'                       => wp_logout_url(),
			'logoutButtonLabel'               => LabelsRegisterPlugin::getLocalized( 'logout_button' ),
			'overlayPreload'                  => intval( SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_OVERLAY_PRELOAD ) ),
			'globalSiteAccess'                => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_GLOBAL_SITE_ACCESS ),
			'customRedirectUrl'               => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_CUSTOM_REDIRECT_URL ),
			'excludeRedirectUrl'              => $exclude_urls,
			'siteHomePageRedirectUrl'         => site_url(),
			'loginAuthenticationPopupEnable'  => $loginAuthenticationPopupEnable,
			'loginAuthenticationPopupPostID'  => ( isset( $post->ID ) && get_post_meta( $post->ID, 'cmreg_login_access', true ) ) ? $post->ID : '0',
			'loginAuthenticationInviteEnable' => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_INVITE_AUTO_POPUP_ENABLE ),
			'loginAuthenticationInvite'       => ( isset( $_GET['invite'] ) && $_GET['invite'] != '' ) ? $_GET['invite'] : ( ( isset( $_GET['cmreg_code'] ) && $_GET['cmreg_code'] != '' ) ? $_GET['cmreg_code'] : '' ),
			'loginAuthenticationPopup'        => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_AUTHENTICATION_POPUP ),
			'loginAuthenticationPopupForce'   => SettingsRegisterPlugin::getOption( SettingsRegisterPlugin::OPTION_LOGIN_AUTHENTICATION_POPUP_FORCE ),
		);

		$html .= '<script type="text/javascript">
				/* <![CDATA[ */
				var CMREG_Settings = ' . json_encode( $objectData ) . ';
				/* ]]> */
				</script>';

		$html .= '<script src="' . $base_path . 'asset/js/frontend.js?v=' . $ver . '"></script>';


		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/settings.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/backend.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/css/frontend.css?ver=" type="text/css" media="all" />';
		$html .= '<link rel="stylesheet" href="' . $base_path . 'asset/vendors/form-builder/form-builder.min.css?ver=" type="text/css" media="all" />';


		return $html;
	}
}
