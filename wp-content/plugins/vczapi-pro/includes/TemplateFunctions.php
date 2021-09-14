<?php

namespace Codemanas\ZoomPro;

use Codemanas\ZoomPro\Core\Fields;
use Codemanas\ZoomPro\Frontend\Recurrings;
use Codemanas\ZoomPro\Frontend\Registrations;

/**
 * Class TemplateOverrides
 *
 * Handler for overriding main zoom tempaltes
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro
 */
class TemplateFunctions {

	private $post_type = 'zoom-meetings';

	/**
	 * TemplateFunctions constructor.
	 */
	public function __construct() {
		add_filter( 'single_template', array( $this, 'single' ), 20 );
		add_filter( 'vczapi_get_template', array( $this, 'template_part' ), 10, 2 );
	}

	/**
	 * Ouput single template
	 *
	 * @param $template
	 *
	 * @return mixed
	 */
	public function single( $template ) {
		global $post;

		if ( $post->post_type == $this->post_type ) {
			unset( $GLOBALS['zoom']['pro'] );
			$details = Fields::get_meta( $post->ID, 'meeting_details' );

			//Is recurring meeting
			if ( ! empty( $details ) ) {
				$GLOBALS['zoom']['pro'] = $details;

				if ( ! empty( $details['enabled_recurring'] ) ) {
					Recurrings::get_instance();
				}

				//If registration page then
				if ( isset( $_GET['register'] ) && ! empty( $details['registration'] ) ) {
					$user_id         = absint( $_GET['register'] );
					$current_user_id = get_current_user_id();
					if ( $user_id === $current_user_id ) {
						$regisration_details                    = Fields::get_user_meta( $current_user_id, 'registration_details' );
						$GLOBALS['zoom']['pro']['registration'] = $regisration_details;
						$registration                           = Registrations::get_instance();
						$template                               = $registration->get_registration_page( $current_user_id, $post );
					}
				}
			}
		}

		return $template;
	}

	/**
	 * Selective template parts
	 *
	 * @param $located
	 * @param $template
	 *
	 * @return bool|mixed|string|void
	 */
	public function template_part( $located, $template ) {
		global $zoom;

		if ( empty( $zoom ) ) {
			return $located;
		}

		$details = ! empty( $zoom->post_id ) ? Fields::get_meta( $zoom->post_id, 'meeting_details' ) : false;
		if ( ! empty( $zoom->registration_url ) && get_post_type( $zoom->post_id ) == 'zoom-meetings' && ! empty( $details['registration'] ) ) {
			wp_enqueue_script( 'vczapi-pro' );

			if ( $template === "fragments/join-links.php" ) {
				$registration = Registrations::get_instance();
				//Abort Early if user is Author
				if ( vczapi_check_author( $zoom->post_id ) ) {
					return $located;
				} else if ( ! is_user_logged_in() && empty( $details['registration_condition'] ) ) {
					echo '<p class="access-denied">' . __( 'You need to be logged in to register for this meeting.', 'vczapi-pro' ) . '</p>';

					return false;
				} else if ( Helpers::checkWooAddonActive() || Helpers::checkWooBookingsAddonActive() ) {
					//if WooCommerce or WooCommerce Bookings addon is active
					global $current_user;
					$product_id = get_post_meta( $zoom->post_id, '_vczapi_zoom_product_id', true );
					//Allow only if customer has bought or is the owner of the product.
					if ( empty( $product_id ) ) {
						$located = $registration->get_register_btn( $zoom );
					} else if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
						$located = $registration->get_register_btn( $zoom );
					}
				} else {
					$located = $registration->get_register_btn( $zoom );
				}
			}
		}

		//This is for shortcode
		if ( ! is_object( $zoom ) && ! empty( $zoom['shortcode'] ) && ! empty( $zoom['api'] ) && ! empty( $zoom['api']->occurrences ) ) {
			wp_enqueue_script( 'vczapi-pro' );

			if ( $template === "fragments/countdown-timer.php" ) {
				$located = TemplateFunctions::get_template( 'fragments/countdown-timer.php' );
			}

			if ( $template === "fragments/meeting-details.php" ) {
				$located = TemplateFunctions::get_template( 'fragments/meeting-details.php' );
			}
		}

		return $located;
	}

	/**
	 * Get Templates for the plugin
	 *
	 * @param      $template_name
	 * @param bool $load
	 * @param bool $require_once
	 *
	 * @sine 1.0.0
	 *
	 * @return bool|mixed|string|void
	 */
	static function get_template( $template_name, $load = false, $require_once = true ) {
		if ( empty( $template_name ) ) {
			return false;
		}

		if ( file_exists( STYLESHEETPATH . '/video-conferencing-zoom-pro/' . $template_name ) ) {
			$located = STYLESHEETPATH . '/video-conferencing-zoom-pro/' . $template_name;
		} elseif ( file_exists( TEMPLATEPATH . '/video-conferencing-zoom-pro/' . $template_name ) ) {
			$located = TEMPLATEPATH . '/video-conferencing-zoom-pro/' . $template_name;
		} elseif ( file_exists( VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'templates/' . $template_name ) ) {
			$located = VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'templates/' . $template_name;
		} else {
			//Search in FREE plugin if any not exists.
			$located = ZVC_PLUGIN_DIR_PATH . 'templates/' . $template_name;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'vczapi_pro_addon_get_template', $located, $template_name );
		if ( $load && ! empty( $located ) && file_exists( $located ) ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Get Template Parts
	 *
	 * @param        $slug
	 * @param string $name
	 *
	 * @since  2.3.0
	 * @author Deepen
	 */
	public static function get_template_part( $slug, $name = '' ) {
		$template = false;
		if ( $name ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				VZAPI_ZOOM_PRO_ADDON_OVERRIDE_SLUG . '/' . "{$slug}-{$name}.php",
			) );

			if ( ! $template ) {
				$fallback = VZAPI_ZOOM_PRO_ADDON_DIR_PATH . "templates/{$slug}-{$name}.php";
				$template = file_exists( $fallback ) ? $fallback : '';
			}
		}

		if ( ! $template ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				VZAPI_ZOOM_PRO_ADDON_OVERRIDE_SLUG . '/' . "{$slug}-{$name}.php",
			) );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'vczapi_pro_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}