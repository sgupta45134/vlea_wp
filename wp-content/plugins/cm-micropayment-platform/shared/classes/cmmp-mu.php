<?php
if ( !class_exists( 'CMMPMultisite' ) ) {

	/**
	 * Class to management functionality Woo
	 * @author mazuu
	 *
	 */
	class CMMPMultisite {

		public static function init() {

			// Lets start with Multisite
			if ( is_multisite() ) {
				if ( !function_exists( 'is_plugin_active_for_network' ) ) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}

				if ( is_plugin_active_for_network( 'cm-micropayment-platform/cm-micropayment-platform.php' ) ) {
					add_action( 'admin_init', array( __CLASS__, 'module_admin_init' ) );
					add_action( 'admin_head', array( __CLASS__, 'admin_menu_styling' ) );
					add_action( 'network_admin_menu', array( __CLASS__, 'add_menu' ) );
				}
			}
		}

		/**
		 * Init
		 * @since 0.1
		 * @version 1.0
		 */
		public static function module_admin_init() {
			register_setting( 'cmmp_network', 'cmmp_network', array( __CLASS__, 'save_network_prefs' ) );
		}

		/**
		 * Add Network Menu Items
		 * @since 0.1
		 * @version 1.2
		 */
		public static function add_menu() {

			$pages[] = add_menu_page(
			__( 'CM Micropayment Platform', 'cmmp' ), __( 'CM Micropayment Platform', 'cmmp' ), 'manage_network_options', 'cmmp_network', '', 'dashicons-star-filled'
			);
			$pages[] = add_submenu_page(
			'cmmp_network', __( 'Network Settings', 'cmmp' ), __( 'Network Settings', 'cmmp' ), 'manage_network_options', 'cmmp_network', array( __CLASS__, 'admin_page_settings' )
			);

			foreach ( $pages as $page ) {
				add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_menu_styling' ) );
			}
		}

		/**
		 * Add Admin Menu Styling
		 * @since 0.1
		 * @version 1.0
		 */
		public static function admin_menu_styling() {
			wp_enqueue_style( 'cmmp-admin' );
		}

		/**
		 * Network Settings Page
		 * @since 0.1
		 * @version 1.1
		 */
		public static function admin_page_settings() {

			// Security
			if ( !current_user_can( 'manage_network_options' ) ) {
				wp_die( __( 'Access Denied', 'cmmp' ) );
			}

			$prefs	 = self::get_settings_network();
			$name	 = CMMP_NAME;
			?>
			<div class="wrap" id="cmmp-wrap">
				<div id="icon-cmmp" class="icon32"><br /></div>
				<h2> <?php echo sprintf( __( '%s Network', 'cmmp' ), $name ); ?></h2>
				<?php
				// Settings Updated
				if ( isset( $_GET[ 'settings-updated' ] ) )
					echo '<div class="updated"><p>' . __( 'Network Settings Updated', 'cmmp' ) . '</p></div>';
				?>
				<p><?php echo sprintf( __( 'Configure network settings for %s.', 'cmmp' ), $name ); ?></p>
				<form method="post" action="<?php echo admin_url( 'options.php' ); ?>" class="">

					<?php settings_fields( 'cmmp_network' ); ?>

					<div class="list-items expandable-li" id="accordion">
						<div class="body" style="display:block;">
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row"><?php _e( 'Networkwide Shared Installation' ) ?>
											<div class="field_help" title="<?php _e( 'If this setting is enabled the EDD checkout will be used instead of builtin Micropayment Checkout.' ) ?>"></div>
										</th>
										<td>
											<input type="hidden" value="0" name="cmmp_network[master]">
											<input type="checkbox" value="1" name="cmmp_network[master]" id="cm_micropayment_use_edd_checkout" <?php checked( $prefs[ 'master' ], 1 ); ?>>
										</td>
										<td>
											<p class="description"><?php echo sprintf( __( "If enabled, %s will use your main site's settings and logging for all sites in your network. ", 'cmmp' ), $name ); ?></p>
										</td>
									</tr>
								</tbody>
							</table>

							<?php do_action( 'cmmp_network_prefs', __CLASS__ ); ?>

						</div>

						<?php do_action( 'cmmp_after_network_prefs', __CLASS__ ); ?>

					</div>

					<?php submit_button( __( 'Save Network Settings', 'cmmp' ), 'primary large', 'submit' ); ?>

				</form>

				<?php do_action( 'cmmp_bottom_network_page', __CLASS__ ); ?>

			</div>
			<?php
		}

		/**
		 * Save Network Settings
		 * @since 0.1
		 * @version 1.1
		 */
		public static function save_network_prefs( $settings ) {

			$new_settings				 = array();
			$new_settings[ 'master' ]	 = ( isset( $settings[ 'master' ] ) ) ? $settings[ 'master' ] : 0;
			$new_settings[ 'central' ]	 = ( isset( $settings[ 'central' ] ) ) ? $settings[ 'central' ] : 0;
			$new_settings[ 'block' ]	 = sanitize_text_field( $settings[ 'block' ] );

			return apply_filters( 'cmmp_save_network_prefs', $new_settings, $settings );
		}

		public static function get_settings_network() {

			if ( !is_multisite() ) {
				return false;
			}

			$defaults	 = array(
				'master'	 => 0,
				'central'	 => 0,
				'block'		 => ''
			);
			$settings	 = get_blog_option( 1, 'cmmp_network', $defaults );

			return $settings;
		}

		/**
		 * Check whether if it's shared network
		 * @return boolean TRUE if it's the shared network
		 */
		public static function is_shared_network() {

			$settings = self::get_settings_network();
			return (false !== $settings && $settings[ 'master' ]);
		}

	}

}

CMMPMultisite::init();
