<?php
/**
 * Class for settings tab
 * @package WCMO
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'WCMO_Settings' ) ) {

	class WCMO_Settings {

		public function __construct() {
		}

		public function init() {
			// do_action( 'wcmo_settings_init' );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_wcmo', array( $this, 'settings_tab' ) );
			add_action( 'woocommerce_sections_wcmo', array( $this, 'output_sections' ) );
			add_action( 'woocommerce_update_options_wcmo', array( $this, 'update_settings' ) );

			add_action( 'admin_init', array( $this, 'create_password_page' ) );
			add_action( 'admin_init', array( $this, 'create_pending_role' ) );

			add_action( 'woocommerce_admin_field_wcmo_licence_key', array( $this, 'licence_key' ) );
			add_action( 'woocommerce_admin_field_wcmo_payment_methods', array( $this, 'payment_methods' ) );
			add_action( 'woocommerce_admin_field_wcmo_shipping_methods', array( $this, 'shipping_methods' ) );

			add_filter( 'woocommerce_admin_settings_sanitize_option_wcmo_payment_methods', array( $this, 'sanitize_payment_methods' ), 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wcmo_shipping_methods', array( $this, 'sanitize_shipping_methods' ), 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wcmo_registration_fields', array( $this, 'sanitize_registration_fields' ), 10, 3 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wcmo_registration_roles', array( $this, 'sanitize_registration_roles' ), 10, 3 );

			add_action( 'woocommerce_admin_field_wcmo_duplicate_user_role', array( $this, 'duplicate_user_role' ) );
			add_action( 'woocommerce_admin_field_wcmo_edit_user_role', array( $this, 'edit_user_role' ) );
			add_filter( 'woocommerce_admin_settings_sanitize_option_wcmo_edit_user_role', array( $this, 'sanitize_edit_user_role' ), 10, 3 );

			add_action( 'woocommerce_admin_field_wcmo_registration_fields', array( $this, 'registration_fields' ) );
			add_action( 'woocommerce_admin_field_wcmo_registration_roles', array( $this, 'registration_roles' ) );

			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			add_filter( 'user_row_actions', array( $this, 'add_user_ids' ), 10, 2);
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ), 10, 1 );
		}

		function enqueue_scripts( $hook ) {
			global $post;
			$enqueue_scripts = false;

			if( ( $hook == 'term.php' || $hook == 'edit-tags.php' ) && isset( $_GET['taxonomy'] ) && ( $_GET['taxonomy'] == 'product_cat' || $_GET['taxonomy'] == 'category' ) ) {
				$enqueue_scripts = true;
			} else if( $hook == 'user-edit.php' && wcmo_get_user_approval() == 'yes' ) {
				$enqueue_scripts = true;
			}

			$is_product = ( isset( $post->post_type ) && $post->post_type == 'product' ) ? true : false;
			if( $hook == 'woocommerce_page_wc-settings' || $is_product || $enqueue_scripts ) {
				if( ! is_customize_preview() ) {
					wp_enqueue_style( 'wcmo-admin-style', trailingslashit( WCMO_PLUGIN_URL ) . 'assets/css/wcmo-admin-style.css', array( 'woocommerce_admin_styles' ), time() );
					wp_register_script( 'wcmo-admin-script', trailingslashit( WCMO_PLUGIN_URL ) . 'assets/js/wcmo-admin-script.js', array( 'jquery', 'select2' ), time(), true );
					wp_localize_script(
						'wcmo-admin-script',
						'wcmo_vars',
						array(
							'restriction_method'	=> wcmo_get_restriction_method()
						)
					);
					wp_enqueue_script( 'wcmo-admin-script' );
				}
			}
		}

		public static function add_settings_tab( $settings_tabs ) {
			$settings_tabs['wcmo'] = __( 'Members Only', 'wcmo' );
			return $settings_tabs;
		}

		public function settings_tab() {
			woocommerce_admin_fields( $this->get_settings() );
		}

		public function update_settings() {
			woocommerce_update_options( $this->get_settings() );
			// After changing settings, delete the transients which store users' lists of restricted products
			wcmo_delete_current_user_archive_transients();
		}

		public function get_settings() {
			global $current_section;

			$settings = array();

			if( $current_section == 'wcmo' || ! $current_section ) {

				$settings = array(
					'section_title' => array(
						'name'     	=> __( 'Members Only', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_settings_title',
						'class'			=> ''
					),
					'wcmo_restriction_method' => array(
						'name'			=> __( 'Restriction Method', 'wcmo' ),
						'type'			=> 'select',
						'desc_tip'	=> true,
						'desc'			=> __( 'How you\'d like to restrict content', 'wcmo' ),
						'id'				=> 'wcmo_restriction_method',
						'options'		=> wcmo_get_restriction_methods(),
						'default'		=> 'no-restriction'
					),
					'wcmo_settings_title_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_settings_title'
					),

					'wcmo_content_settings' => array(
						'name'     	=> __( 'Content Settings', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_content_settings'
					),
					'wcmo_redirect_to' => array(
						'name'			=> __( 'After Logging In', 'wcmo' ),
						'type'			=> 'select',
						'desc_tip'	=> true,
						'desc'			=> __( 'Action after the user successfully logs in', 'wcmo' ),
						'id'				=> 'wcmo_redirect_to',
						'options'		=> wcmo_get_redirect_options(),
						'default'		=> 'stay'
					),
					// 'wcmo_redirect_page' => array(
					// 	'name'			=> __( 'Redirect Page', 'wcmo' ),
					// 	'type'			=> 'single_select_page',
					// 	'desc_tip'	=> true,
					// 	'desc'			=> __( 'Where to send the user after successfully logging in', 'wcmo' ),
					// 	'id'				=> 'wcmo_redirect_page',
					// 	'default'		=> '',
					// 	'class'			=> 'wcmo_show_if_redirect_to_page'
					// ),
					'wcmo_redirect_page' => array(
						'name'			=> __( 'After Log In, Redirect To', 'wcmo' ),
						'type'			=> 'select',
						'options'		=> wcmo_get_all_pages(),
						'desc_tip'	=> true,
						'desc'			=> __( 'Where to send the user after successfully logging in', 'wcmo' ),
						'id'				=> 'wcmo_redirect_page',
						'default'		=> '',
						'class'			=> 'wcmo_show_if_redirect_to_page'
					),
					'wcmo_restricted_content' => array(
						'name'			=> __( 'Restricted Content', 'wcmo' ),
						'type'			=> 'select',
						'desc_tip'	=> true,
						'desc'			=> __( 'Which content will be restricted', 'wcmo' ),
						'id'				=> 'wcmo_restricted_content',
						'options'		=> wcmo_get_restricted_content_types(),
						'default'		=> 'products'
					),
					'wcmo_redirect_restricted' => array(
						'name'			=> __( 'If Restricted, Redirect To', 'wcmo' ),
						'type'			=> 'single_select_page',
						'desc_tip'	=> true,
						'desc'			=> __( 'Where to send the user after trying to access a restricted page', 'wcmo' ),
						'id'				=> 'wcmo_redirect_restricted',
						'default'		=> '',
						'class'			=> ''
					),
					'wcmo_restricted_categories' => array(
						'name'			=> __( 'Restricted Categories', 'wcmo' ),
						'type'			=> 'multiselect',
						'desc_tip'	=> true,
						'desc'			=> __( 'The restricted categories', 'wcmo' ),
						'id'				=> 'wcmo_restricted_categories',
						'options'		=> wcmo_get_all_taxonomy_terms(),
						'default'		=> '',
						'class'			=> 'wcmo_show_if_categories wcmo_multiselect'
					),
					'content_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_content_settings'
					),

					'wcmo_password_settings' => array(
						'name'     	=> __( 'Password Settings', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_password_settings'
					),
					'wcmo_passwords' => array(
						'name'			=> __( 'Passwords', 'wcmo' ),
						'type'			=> 'textarea',
						'desc_tip'	=> true,
						'desc'			=> __( 'Enter each password on a new line', 'wcmo' ),
						'id'				=> 'wcmo_passwords',
						'class'			=> 'wcmo_show_if_password'
					),
					'wcmo_form_page' => array(
						'name'			=> __( 'Password Form Page', 'wcmo' ),
						'type'			=> 'single_select_page',
						'desc_tip'	=> true,
						'desc'			=> __( 'The location of the password form', 'wcmo' ),
						'id'				=> 'wcmo_form_page',
						'default'		=> '',
						'class'			=> 'wcmo_show_if_form_page wcmo_show_if_password'
					),
					'password_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_password_settings'
					),

					'wcmo_role_settings' => array(
						'name'     	=> __( 'User Role Settings', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_role_settings'
					),
					'wcmo_user_roles' => array(
						'name'			=> __( 'Permitted User Roles', 'wcmo' ),
						'type'			=> 'multiselect',
						'desc_tip'	=> true,
						'desc'			=> __( 'The user roles that can access content', 'wcmo' ),
						'id'				=> 'wcmo_user_roles',
						'options'		=> wcmo_get_user_roles(),
						'default'		=> '',
						'class'			=> 'wcmo_show_if_user-role wcmo_multiselect'
					),
					'role_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_role_settings'
					),

					'wcmo_archive_settings' => array(
						'name'     	=> __( 'Archive Settings', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_archive_settings'
					),
					'wcmo_hide_products' => array(
						'name'			=> __( 'Hide in Archives', 'wcmo' ),
						'type'			=> 'checkbox',
						'desc_tip'	=> true,
						'desc'			=> __( 'Select this to remove products/posts from all archives pages and loops', 'wcmo' ),
						'id'				=> 'wcmo_hide_products',
						'default'		=> 'no'
					),
					'wcmo_allow_view_products' => array(
						'name'			=> __( 'Access Product Pages', 'wcmo' ),
						'type'			=> 'checkbox',
						'desc_tip'	=> true,
						'desc'			=> __( 'Select this to allow users to view product pages but not make any purchases', 'wcmo' ),
						'id'				=> 'wcmo_allow_view_products',
						'default'		=> 'no'
					),
					'wcmo_excerpt' => array(
						'name'			=> __( 'Excerpt', 'wcfad' ),
						'type'			=> 'textarea',
						'desc_tip'	=> true,
						'desc'			=> __( 'Enter a replacement excerpt for restricted posts', 'wcmo' ),
						'id'				=> 'wcmo_excerpt'
					),
					'wcmo_add_to_cart' => array(
						'name'			=> __( 'Add To Cart Text', 'wcfad' ),
						'type'			=> 'text',
						'desc_tip'	=> true,
						'desc'			=> __( 'Replace the \'Add To Cart\' text on restricted products', 'wcmo' ),
						'id'				=> 'wcmo_add_to_cart',
						'default'		=> ''
					),
					'wcmo_link_redirect' => array(
						'name'			=> __( 'Redirect from Add to Cart Button', 'wcmo' ),
						'type'			=> 'checkbox',
						'desc_tip'	=> true,
						'desc'			=> __( 'Select this to link Add to Cart buttons to the page specified in \'If Restricted, Redirect To\' above', 'wcmo' ),
						'id'				=> 'wcmo_link_redirect',
						'default'		=> 'no'
					),
					'wcmo_hide_price' => array(
						'name'			=> __( 'Hide Price', 'wcfad' ),
						'type'			=> 'checkbox',
						'desc_tip'	=> true,
						'desc'			=> __( 'Select this to hide the price for restricted products', 'wcmo' ),
						'id'				=> 'wcmo_hide_price',
						'default'		=> 'no'
					),
					'archive_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_archive_settings'
					),

					'wcmo_widgets_settings' => array(
						'name'     	=> __( 'Widget and Menu Settings', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_widgets_settings'
					),
					'wcmo_enable_widget_whitelist' => array(
						'name'			=> __( 'Enable Widget Whitelist', 'wcfad' ),
						'type'			=> 'checkbox',
						'desc_tip'	=> true,
						'desc'			=> __( 'Select this to block widgets not on the widget whitelist below', 'wcmo' ),
						'id'				=> 'wcmo_enable_widget_whitelist',
						'default'		=> 'no'
					),
					'wcmo_widget_whitelist' => array(
						'name'			=> __( 'Widget Whitelist', 'wcmo' ),
						'type'			=> 'textarea',
						'desc_tip'	=> true,
						'desc'			=> __( 'Enter the name of widgets, e.g. Search, that can be displayed to users who are restricted from viewing content. Enter each name on a new line', 'wcmo' ),
						'id'				=> 'wcmo_widget_whitelist'
					),
					'wcmo_menu_exclusions' => array(
						'name'			=> __( 'Exclude from Menu', 'wcmo' ),
						'type'			=> 'textarea',
						'desc_tip'	=> true,
						'desc'			=> __( 'Enter menu item names, e.g. Shop, to exclude from the menu when a user does not have access rights. Enter each name on a new line', 'wcmo' ),
						'id'				=> 'wcmo_menu_exclusions'
					),
					'widgets_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_widgets_settings'
					)
				);
			} else if( $current_section == 'wcmo_lk' ) {
				$settings = array(
					'section_title' => array(
						'name'     	=> __( 'Licence', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_lk_settings_title'
					),
					'wcmo_licence_key' => array(
						'name'			=> __( 'Licence key', 'wcmo' ),
						'type'			=> 'wcmo_licence_key',
						'desc_tip'	=> true,
						'desc'			=> __( 'Enter your licence key', 'wcmo' ),
						'id'				=> 'wcmo_licence_key',
						'default'		=> ''
					),
					'section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_lk_settings_title'
					)
				);
			} else if( $current_section == 'wcmo_roles' ) {
				$settings = array(
					'duplicate_section_title' => array(
						'name'     	=> __( 'User Roles', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_roles_settings_title'
					),
					'wcmo_duplicate_user_role' => array(
						'name'			=> __( 'User roles', 'wcmo' ),
						'type'			=> 'wcmo_duplicate_user_role',
						'desc_tip'	=> true,
						'desc'			=> __( 'Current user roles on this site', 'wcmo' ),
						'id'				=> 'wcmo_duplicate_user_role',
						'default'		=> ''
					),
					'duplicate_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_roles_settings_title'
					),

					'edit_section_title' => array(
						'name'     	=> __( 'Edit Role', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_edit_roles_settings_title'
					),
					'wcmo_edit_user_role' => array(
						'name'			=> __( 'User roles', 'wcmo' ),
						'type'			=> 'wcmo_edit_user_role',
						'desc_tip'	=> true,
						'desc'			=> __( 'Edit an existing user role', 'wcmo' ),
						'id'				=> 'wcmo_edit_user_role',
						'default'		=> ''
					),
					'edit_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_edit_roles_settings_title'
					)
				);
			} else if( $current_section == 'wcmo_registration' ) {
				$settings = apply_filters(
					'wcmo_registration_settings',
					array(
						'registration_section_title' => array(
							'name'     	=> __( 'Registration', 'wcmo' ),
							'type'     	=> 'title',
							'desc'     	=> '',
							'id'       	=> 'wcmo_registration_settings_title'
						),
						'wcmo_user_approval' => array(
							'name'			=> __( 'Enable User Approval', 'wcfad' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'New registrations will need to be approved', 'wcmo' ),
							'id'				=> 'wcmo_user_approval',
							'default'		=> 'no'
						),
						'wcmo_prevent_pending' => array(
							'name'			=> __( 'Prevent Pending Users', 'wcfad' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'Prevent pending users from logging in to the site', 'wcmo' ),
							'id'				=> 'wcmo_prevent_pending',
							'default'		=> 'no'
						),
						'wcmo_prevent_rejected' => array(
							'name'			=> __( 'Prevent Rejected Users', 'wcfad' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'Prevent rejected users from logging in to the site', 'wcmo' ),
							'id'				=> 'wcmo_prevent_rejected',
							'default'		=> 'no'
						),
						'wcmo_multiple_user_roles' => array(
							'name'			=> __( 'Enable Multiple Roles', 'wcmo' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'Allow multiple roles per user', 'wcmo' ),
							'id'				=> 'wcmo_multiple_user_roles',
							'default'		=> 'no'
						),
						'wcmo_default_user_roles' => array(
							'name'			=> __( 'Default User Roles', 'wcmo' ),
							'type'			=> 'multiselect',
							'desc_tip'	=> true,
							'desc'			=> __( 'The default user roles assigned on registration', 'wcmo' ),
							'id'				=> 'wcmo_default_user_roles',
							'options'		=> wcmo_get_assignable_user_roles(),
							'default'		=> array( 'customer' ),
							'class'			=> 'wcmo_multiselect'
						),
						'wcmo_prevent_auto_login' => array(
							'name'			=> __( 'Prevent Auto Log-In', 'wcmo' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'Disable automatic log-in when a new user registers', 'wcmo' ),
							'id'				=> 'wcmo_prevent_auto_login',
							'default'		=> 'no'
						),
						'wcmo_assign_roles_order_status' => array(
							'name'			=> __( 'Assign Roles Order Status', 'wcmo' ),
							'type'			=> 'select',
							'desc_tip'	=> true,
							'desc'			=> __( 'Choose at what point a role is assigned to a user after purchasing a product', 'wcmo' ),
							'id'				=> 'wcmo_assign_roles_order_status',
							'options'		=> array(
								'woocommerce_order_status_processing'	=> __( 'Order Processing', 'wcmo' ),
								'woocommerce_order_status_completed'	=> __( 'Order Completed', 'wcmo' ),
							),
							'default'		=> array( 'woocommerce_order_status_processing' ),
							'class'			=> ''
						),
						'registration_section_end' => array(
							'type' => 'sectionend',
							'id' => 'wcmo_registration_settings_title'
						),

						'registration_fields_section_title' => array(
							'name'     	=> __( 'Registration Fields', 'wcmo' ),
							'type'     	=> 'title',
							'desc'     	=> '',
							'id'       	=> 'wcmo_registration_fields_settings_title'
						),
						'wcmo_registration_fields' => array(
							'name'			=> __( 'Registration Fields', 'wcfad' ),
							'type'			=> 'wcmo_registration_fields',
							'desc_tip'	=> true,
							'desc'			=> __( 'New registrations will need to be approved', 'wcmo' ),
							'id'				=> 'wcmo_registration_fields',
							'options'		=> array(
								'h'	=> 'aa'
							)
						),
						'wcmo_enable_registration_roles' => array(
							'name'			=> __( 'Enable Roles Field', 'wcmo' ),
							'type'			=> 'checkbox',
							'desc_tip'	=> true,
							'desc'			=> __( 'Add a select field to the registration form for users to select their role', 'wcmo' ),
							'id'				=> 'wcmo_enable_registration_roles',
							'default'		=> 'no'
						),
						'wcmo_registration_roles' => array(
							'name'			=> __( 'Registration Roles', 'wcfad' ),
							'type'			=> 'wcmo_registration_roles',
							'desc_tip'	=> true,
							'desc'			=> __( 'Which roles to display in the registration field', 'wcmo' ),
							'id'				=> 'wcmo_registration_roles',
							'options'		=> array(
								'h'	=> 'aa'
							)
						),
						'registration_fields_section_end' => array(
							'type' => 'sectionend',
							'id' => 'wcmo_registration_fields_settings_title'
						),
					)
				);
			} else if( $current_section == 'wcmo_payments' ) {
				$settings = array(
					'payments_section_title' => array(
						'name'     	=> __( 'Payment Methods', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_payments_settings_title'
					),
					'wcmo_payment_methods' => array(
						'name'			=> __( 'Payment Methods', 'wcmo' ),
						'type'			=> 'wcmo_payment_methods',
						// 'desc_tip'	=> true,
						// 'desc'			=> __( 'Enter your licence key', 'wcmo' ),
						'id'				=> 'wcmo_payment_methods',
						'default'		=> ''
					),
					'payments_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_payments_settings_title'
					)
				);
			}else if( $current_section == 'wcmo_shipping' ) {
				$settings = array(
					'payments_shipping_title' => array(
						'name'     	=> __( 'Shipping Methods', 'wcmo' ),
						'type'     	=> 'title',
						'desc'     	=> '',
						'id'       	=> 'wcmo_shipping_settings_title'
					),
					'wcmo_shipping_methods' => array(
						'name'			=> __( 'Shipping Methods', 'wcmo' ),
						'type'			=> 'wcmo_shipping_methods',
						// 'desc_tip'	=> true,
						// 'desc'			=> __( 'Enter your licence key', 'wcmo' ),
						'id'				=> 'wcmo_shipping_methods',
						'default'		=> ''
					),
					'shipping_section_end' => array(
						'type' => 'sectionend',
						'id' => 'wcmo_shipping_settings_title'
					)
				);
			}

			update_option( 'wcmo_display_form', 'page' );

			return $settings;

		}

		/**
		 * If 'Pending' users are barred from logging in, create a Pending role
		 */
		public function create_pending_role() {

			// Check if Pending users are barred
			$prevent_pending = wcmo_get_prevent_pending();
			if( $prevent_pending != 'yes' ) {
				return;
			}

			$roles = wcmo_get_user_roles();
			if( is_array( $roles ) && ! in_array( 'pending', $roles ) ) {

				// We need to create a role for 'Pending'
				global $wp_roles;
				if( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}

				// Create a Pending role
				$wp_roles->add_role( 'pending', __( 'Pending', 'wcom' ), false );

			}

		}

		/**
		 * Output sections.
		 */
		public function output_sections() {

			global $current_section;

			echo '<ul class="subsubsub">';

				// Main settings tab
				$sections = array( 'wcmo' => __( 'Members Only', 'wcmo' ) );

				$sections['wcmo_roles'] = __( 'User Roles', 'wcmo' );
				$sections['wcmo_registration'] = __( 'Registration', 'wcmo' );
				$sections['wcmo_payments'] = __( 'Payment Methods', 'wcmo' );
				$sections['wcmo_shipping'] = __( 'Shipping Methods', 'wcmo' );
				$sections['wcmo_lk'] = __( 'Licence', 'wcmo' );

				$array_keys = array_keys( $sections );

				foreach ( $sections as $id=>$label ) {
					echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=wcmo&section=' . sanitize_title( $id ) ) . '" class="' . ( ( $current_section == $id || ( ! $current_section && $id == 'wcmo' ) ) ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
				}

			echo '</ul><br class="clear" />';
		}

		/**
		 * Custom setting for EDD SL licence key
		 */
		public function licence_key() {
			$key = get_option( 'wcmo_licence_key' ); ?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Licence key', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
					<input name="wcmo_licence_key" id="wcmo_licence_key" type="text" style="" value="<?php echo $key; ?>" class="" placeholder="">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Status', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
					<?php $status = ( false !== get_option( 'wcmo_license_status' ) ) ? get_option( 'wcmo_license_status' ) : 'invalid';
					if( $status == 'valid' ) {
						echo '<span class="dashicons dashicons-yes"></span>&nbsp;';
					} else {
						echo '<span class="dashicons dashicons-no-alt"></span>&nbsp;';
					}
					echo ucfirst( $status ); ?>
				</td>
				<?php if( $status == 'valid' ) { ?>
					<tr>
						<th scope="row" class="titledesc">
							<?php _e( 'Action', 'wcmo' ); ?>
						</th>
						<td class="forminp forminp-text">
							<?php printf(
								'<p><button type="submit" name="wcmo_deactivate_licence_key" class="button button-secondary">%s</button></p>',
								__( 'Deactivate this licence', 'wcmo' )
							); ?>
						</td>
					</tr>
				<?php } else if( $status == 'deactivated' ) { ?>
					<tr>
						<th scope="row" class="titledesc">
							<?php _e( 'Action', 'wcmo' ); ?>
						</th>
						<td class="forminp forminp-text">
							<?php printf(
								'<p><button type="submit" name="wcmo_activate_licence_key" class="button button-secondary">%s</button></p>',
								__( 'Activate this licence', 'wcmo' )
							); ?>
						</td>
					</tr>
				<?php } ?>
			</tr>
			<?php
			wp_nonce_field( 'wcmo_licence_key_nonce', 'wcmo_licence_key_nonce' );
		}

		/**
		 * Custom setting for duplicate roles
		 */
		public function duplicate_user_role() {
			$roles = wcmo_get_assignable_user_roles();
			printf(
				'<p>%s</p>',
				__( 'Select a role to duplicate', 'wcmo' )
			); ?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'User roles', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
					<select name="wcmo_existing_user_roles" id="wcmo_existing_user_roles">
						<?php printf(
							'<option value="">-- %s --</option>',
							__( 'Select a role to duplicate', 'wcmo' )
						);
						if( $roles ) {
							foreach( $roles as $id=>$role ) {
								printf(
									'<option value="%s">%s</option>',
									$id,
									$role
								);
							}
						} ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'New role name', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
					<p><input type="text" name="wcmo_new_role_name" value=""></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" colspan=2 class="titledesc">
					<?php printf(
						'<p><button type="submit" name="wcmo_duplicate_user_role" class="button button-primary" style="font-weight: 400">%s</button></p>',
						__( 'Duplicate role', 'wcmo' )
					); ?>
				</th>

			</tr>
			<?php
			wp_nonce_field( 'wcmo_user_roles_nonce', 'wcmo_user_roles_nonce' );
		}

		/**
		 * Custom setting for editing a user role
		 */
		public function edit_user_role() {

			$roles = wcmo_get_user_roles();

			// Don't edit the admin role, too risky
			unset( $roles['administrator'] );

			$selected_role = isset( $_POST['wcmo_edit_user_role'] ) ? $_POST['wcmo_edit_user_role'] : '';

			// Need to be an admin to do this
			if( ! current_user_can( 'manage_options' ) ) {
				printf(
					'<p>%s</p>',
					__( 'You don\'t have sufficient rights to edit roles', 'wcmo' )
				);
				return;
			}

			printf(
				'<p>%s</p>',
				__( 'Select a role to edit', 'wcmo' )
			); ?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'User roles', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
					<select name="wcmo_edit_user_role" id="wcmo_edit_user_role">
						<?php printf(
							'<option value="">-- %s --</option>',
							__( 'Select a role to edit', 'wcmo' )
						);
						if( $roles ) {
							foreach( $roles as $id=>$role ) {
								printf(
									'<option value="%s" %s>%s</option>',
									$id,
									selected( $role, $selected_role, true ),
									$role
								);
							}
						} ?>
					</select>
				</td>
			</tr>
			<tr valign="top" class="wcmo_hide_if_no_role">
				<th scope="row" class="titledesc">
					<?php _e( 'Capabilities', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">
				<?php

				$capabilities = wcmo_get_all_capabilities();

				foreach( $roles as $id=>$role ) {

					$role_object = get_role( $id ); ?>

						<div class="role-capabilities role-capabilities-<?php echo $id; ?>">

							<?php
							if( $capabilities ) {
								foreach( $capabilities as $capability=>$value ) {

									$checked = $role_object->has_cap( $capability ) ? 'checked="checked"' : '';

									printf(
										'<div><label for="wcmo_capabilities_%s_%s"><input class="wcmo_capability_checkbox" type="checkbox" %s name="wcmo_capabilities[%s][%s]" id="wcmo_capabilities_%s_%s" value="%s">%s</label></div>',
										$capability,
										$id,
										$checked,
										$id,
										$capability,
										$id,
										$capability,
										$capability,
										$capability
									);
								}
							} ?>
						</div>

				<?php } ?>
				</td>
			</tr>
			<tr valign="top" class="wcmo_hide_if_no_role">
				<th scope="row" class="titledesc">
					<?php
					// printf(
					// 	'<p><button type="submit" name="wcmo_update_user_role" class="button button-primary" style="font-weight: 400">%s</button></p>',
					// 	__( 'Update role', 'wcmo' )
					// ); ?>
				</th>
			</tr>
			<?php
			wp_nonce_field( 'wcmo_update_user_roles_nonce', 'wcmo_update_user_roles_nonce' );
		}

		/**
		 * Custom setting for duplicate roles
		 */
		public function registration_fields() {

			$enabled_fields = wcmo_get_enabled_registration_fields();
			$fields = wcmo_get_registration_fields();
			$extra_fields = get_option( 'wcmo_extra_registration_fields', array() ); ?>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Additional Registration Fields', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">

					<table class="wcmo-registration-fields wp-list-table widefat striped table-view-list" id="wcmo-registration-fields">
						<thead>
							<tr>
								<?php printf(
									'<th>%s</th>',
									__( 'Field', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Type', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Enabled', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Required', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Email', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Profile', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Priority', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Description', 'wcmo' )
								);
								printf(
									'<th></th>'
								); ?>
							</tr>
						</thead>
						<tbody>
							<?php if( $fields ) {
								foreach( $fields as $field_id=>$field ) {

									echo '<tr>';

									// Field label
									printf(
										'<td class="wcmo-field-label">%s</td>',
										$field['label']
									);

									printf(
										'<td class="wcmo-field-type">%s</td>',
										$field['type']
									);

									// Select this field
									$checked = ( isset( $enabled_fields['fields'][$field_id] ) && $enabled_fields['fields'][$field_id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_fields[fields][%s]" class="wcmo-field-enabled" id="wcmo_registration_fields_%s" %s>',
										$field_id,
										$field_id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									// Is Required field
									$checked = ( isset( $enabled_fields['required'][$field_id] ) && $enabled_fields['required'][$field_id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_fields[required][%s]" class="wcmo-field-required" id="wcmo_registration_fields_required_%s" %s>',
										$field_id,
										$field_id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									// Add to Admin email
									$checked = ( isset( $enabled_fields['admin_email'][$field_id] ) && $enabled_fields['admin_email'][$field_id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_fields[admin_email][%s]" class="wcmo-field-admin-email" id="wcmo_registration_fields_admin_email_%s" %s>',
										$field_id,
										$field_id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									// Add to Profile
									$checked = ( isset( $enabled_fields['add_to_profile'][$field_id] ) && $enabled_fields['add_to_profile'][$field_id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_fields[add_to_profile][%s]" class="wcmo-field-add-to-profile" id="wcmo_registration_fields_add_to_profile_%s" %s>',
										$field_id,
										$field_id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									$value = ( isset( $enabled_fields['priority'][$field_id] ) ) ? $enabled_fields['priority'][$field_id] : '0';

									$priority = sprintf(
										'<input type="number" name="wcmo_registration_fields[priority][%s]" class="wcmo-field-priority" id="wcmo_registration_fields_priority_%s" value="%s">',
										$field_id,
										$field_id,
										$value
									);

									printf(
										'<td>%s</td>',
										$priority
									);

									$value = ( isset( $enabled_fields['description'][$field_id] ) ) ? $enabled_fields['description'][$field_id] : '';

									$priority = sprintf(
										'<textarea name="wcmo_registration_fields[description][%s]" class="wcmo-field-description" id="wcmo_registration_fields_description_%s">%s</textarea>',
										$field_id,
										$field_id,
										esc_html( $value )
									);

									printf(
										'<td>%s</td>',
										$priority
									);

									// Add the option to delete the field if it's one we've added
									if( isset( $extra_fields[$field_id] ) ) {

										printf(
											'<td><a href="#" class="wcmo-remove-field" data-field-id="%s">%s</a></td>',
											$field_id,
											'&times;'
										);
									} else {
										printf(
											'<td></td>'
										);
									}


									echo '</tr>';

								}
							} ?>
						</tbody>
					</table>
				</td>
			</tr>

			<?php $types = wcmo_get_registration_field_tyoes(); ?>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Create New Field', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">

					<table class="wcmo-registration-fields wp-list-table widefat striped table-view-list wcmo-create-fields">
						<thead>
							<tr>
								<?php printf(
									'<th>%s</th>',
									__( 'Label', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Type', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Enabled', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Required', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Email', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Profile', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Priority', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Description', 'wcmo' )
								);
								printf(
									'<th></th>'
								); ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<input type="text" name="wcmo_create_field[label]" id="wcmo_create_field_label" value="">
								</td>
								<td>
									<?php if( $types ) { ?>
										<select name="wcmo_create_field[type]" id="wcmo_create_field_type">
										<?php foreach( $types as $id=>$value ) {
											printf(
												'<option value="%s">%s</option>',
												$id,
												$value
											);
										}
									} ?>
								</td>
								<td>
									<input type="checkbox" name="wcmo_create_field[enabled]" id="wcmo_create_field_enabled" %s>
								</td>
								<td>
									<input type="checkbox" name="wcmo_create_field[required]" id="wcmo_create_field_required" %s>
								</td>
								<td>
									<input type="checkbox" name="wcmo_create_field[admin_email]" id="wcmo_create_field_admin_email" %s>
								</td>
								<td>
									<input type="checkbox" name="wcmo_create_field[add_to_profile]" id="wcmo_create_field_add_to_profile" %s>
								</td>
								<td>
									<input class="wcmo-field-priority" type="number" name="wcmo_create_field[priority]" id="wcmo_create_field_priority" value="">
								</td>
								<td>
									<textarea name="wcmo_create_field[description]" id="wcmo_create_field_description"></textarea>
								</td>
								<td></td>
							</tr>
							<tr>
								<td colspan=7>
									<a href="#" class="wcmo-add-field button secondary-button"><?php _e( 'Add Field', 'wcmo' ); ?></a>
								</td>
							</tr>
						</tbody>
					</table>

				</td>
			</tr>
			<?php

			wp_nonce_field( 'wcmo_registration_fields_nonce', 'wcmo_registration_fields_nonce' );

		}

		/**
		 * Custom setting for registration roles field
		 */
		public function registration_roles() {

			$roles = wcmo_get_assignable_user_roles();
			$enabled_roles = wcmo_get_enabled_registration_roles(); ?>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Registration Roles', 'wcmo' ); ?>
				</th>
				<td class="forminp forminp-text">

					<table class="wcmo-registration-fields wp-list-table widefat striped table-view-list">

						<thead>
							<tr>
								<?php printf(
									'<th>%s</th>',
									__( 'Role', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Include', 'wcmo' )
								);
								printf(
									'<th>%s</th>',
									__( 'Needs Approval', 'wcmo' )
								); ?>
							</tr>
						</thead>

						<tbody>
							<?php
							if( $roles ) {
								foreach( $roles as $id=>$role ) {

									echo '<tr>';

									// Field label
									printf(
										'<td>%s</td>',
										$role
									);

									// Select this field
									$checked = ( isset( $enabled_roles['include'][$id] ) && $enabled_roles['include'][$id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_roles[include][%s]" id="wcmo_registration_roles_%s" %s>',
										$id,
										$id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									// Select this field
									$checked = ( isset( $enabled_roles['approve'][$id] ) && $enabled_roles['approve'][$id] == 'on' ) ? 'checked' : '';

									$checkbox = sprintf(
										'<input type="checkbox" name="wcmo_registration_roles[approve][%s]" id="wcmo_registration_roles_approve_%s" %s>',
										$id,
										$id,
										$checked
									);

									printf(
										'<td>%s</td>',
										$checkbox
									);

									echo '</tr>';

								}
							} ?>
						</tbody>
					</table>
				</td>
			</tr>
			<?php
		}

		/**
		 * Custom setting for listing payment methods
		 */
		public function payment_methods() {

			$methods = wcmo_get_payment_gateway_sections();
			$roles = wcmo_get_user_roles();
			$restricted_methods = wcmo_get_restricted_payment_methods();
			$permitted_methods = wcmo_get_permitted_payment_methods();

			printf(
				'<p>%s</p>',
				__( 'Restrict or enable specific payment methods by user role', 'wcmo' )
			);

			if( $methods ) {

				foreach( $methods as $method_id=>$method ) { ?>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php echo esc_html( $method ); ?>
						</th>
						<td class="forminp forminp-text"></td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php _e( 'Restricted roles', 'wcmo' ); ?>
						</th>

						<td class="forminp forminp-text">
							<select multiple class="wcmo_multiselect" name="wcmo_payment_methods[restricted][<?php echo $method_id; ?>][]" id="wcmo_restricted_payment_methods_<?php echo $method_id; ?>">

								<?php
								if( $roles ) {
									foreach( $roles as $id=>$role ) {
										$selected = ( isset( $restricted_methods[$method_id] ) && in_array( $id, $restricted_methods[$method_id] ) ) ? 'selected' : '';
										printf(
											'<option value="%s" %s>%s</option>',
											$id,
											$selected,
											$role
										);
									}
								} ?>

							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php _e( 'Permitted roles', 'wcmo' ); ?>
						</th>

						<td class="forminp forminp-text">
							<select multiple class="wcmo_multiselect" name="wcmo_payment_methods[permitted][<?php echo $method_id; ?>][]" id="wcmo_permitted_payment_methods_<?php echo $method_id; ?>">

								<?php
								if( $roles ) {
									foreach( $roles as $id=>$role ) {
										$selected = ( isset( $permitted_methods[$method_id] ) && in_array( $id, $permitted_methods[$method_id] ) ) ? 'selected' : '';
										printf(
											'<option value="%s" %s>%s</option>',
											$id,
											$selected,
											$role
										);
									}
								} ?>

							</select>
						</td>
					</tr>

				<?php }

			} ?>

			<?php

		}

		/**
		 * Sanitise the payment methods field
		 * Ensure we pass back an empty array rather than just a null field
		 */
		public function sanitize_payment_methods( $value, $option, $raw_value ) {

			if( ! $value ) {
				$value = array();
			}
			return $value;

		}

		/**
		 * Sanitise the shipping methods field
		 * Ensure we pass back an empty array rather than just a null field
		 */
		public function sanitize_shipping_methods( $value, $option, $raw_value ) {

			if( ! $value ) {
				$value = array();
			}
			return $value;

		}

		/**
		 * Sanitise the edit user role field
		 * Update the capabilities for the selected role
		 * Ensure we pass back an empty array rather than just a null field
		 */
		public function sanitize_edit_user_role( $value, $option, $raw_value ) {

			if( $value ) {

				$capabilities = wcmo_get_all_capabilities();

				// Iterate through all capabilities
				// Set or remove capability for the selected role
				$role = get_role( $value );

				if( $capabilities ) {
					foreach( $capabilities as $capability=>$setting ) {
						if( ! empty( $_POST['wcmo_capabilities'][$value][$capability] ) ) {
							$role->add_cap( $capability );
						} else {
							$role->remove_cap( $capability );
						}
					}
				}

			}

			return $value;

		}

		/**
		 * Sanitise the registration fields
		 * Ensure we pass back an empty array rather than just a null field
		 */
		public function sanitize_registration_fields( $value, $option, $raw_value ) {

			if( ! $value ) {
				$value = array();
			}
			return $value;

		}

		/**
		 * Sanitise the registration roles field
		 * Ensure we pass back an empty array rather than just a null field
		 */
		public function sanitize_registration_roles( $value, $option, $raw_value ) {

			if( ! $value ) {
				$value = array();
			}
			return $value;

		}

		/**
		 * Custom setting for listing payment methods
		 */
		public function shipping_methods() {

			$methods = wcmo_get_shipping_method_sections();
			$roles = wcmo_get_user_roles();
			$restricted_methods = wcmo_get_restricted_shipping_methods();
			$permitted_methods = wcmo_get_permitted_shipping_methods();

			printf(
				'<p>%s</p>',
				__( 'Restrict or enable specific shipping methods by user role', 'wcmo' )
			);

			if( $methods ) {

				foreach( $methods as $method_id=>$method ) { ?>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php echo esc_html( $method ); ?>
						</th>
						<td class="forminp forminp-text"></td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php _e( 'Restricted roles', 'wcmo' ); ?>
						</th>

						<td class="forminp forminp-text">
							<select multiple class="wcmo_multiselect" name="wcmo_shipping_methods[restricted][<?php echo $method_id; ?>][]" id="wcmo_restricted_shipping_methods_<?php echo $method_id; ?>">

								<?php
								if( $roles ) {
									foreach( $roles as $id=>$role ) {
										$selected = ( isset( $restricted_methods[$method_id] ) && in_array( $id, $restricted_methods[$method_id] ) ) ? 'selected' : '';
										printf(
											'<option value="%s" %s>%s</option>',
											$id,
											$selected,
											$role
										);
									}
								} ?>

							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<?php _e( 'Permitted roles', 'wcmo' ); ?>
						</th>

						<td class="forminp forminp-text">
							<select multiple class="wcmo_multiselect" name="wcmo_shipping_methods[permitted][<?php echo $method_id; ?>][]" id="wcmo_permitted_shipping_methods_<?php echo $method_id; ?>">

								<?php
								if( $roles ) {
									foreach( $roles as $id=>$role ) {
										$selected = ( isset( $permitted_methods[$method_id] ) && in_array( $id, $permitted_methods[$method_id] ) ) ? 'selected' : '';
										printf(
											'<option value="%s" %s>%s</option>',
											$id,
											$selected,
											$role
										);
									}
								} ?>

							</select>
						</td>
					</tr>

				<?php }

			} ?>

			<?php

		}

		/**
		 * Check if a password page has been created
		 */
		public function admin_notices() {
			if( ! isset( $_GET['page'] ) || ! isset( $_GET['tab'] ) || $_GET['page'] !='wc-settings' || $_GET['tab'] != 'wcmo' ) {
				// return;
			}
			$has_shortcode = $this->password_page_exists();

			if( ! $has_shortcode && get_option( 'wcmo_restriction_method', 'log-in-status' ) == 'password' ) {
				$url = menu_page_url( 'wc-settings', false );
				$url = add_query_arg(
					array(
						'tab'					=> 'wcmo',
						'create_page'	=> true
					),
					$url
				); ?>
				<div class="notice notice-warning">
					<?php printf(
						'<p><strong>%s</strong></p><p>%s</p>',
						__( 'WooCommerce Members Only', 'wcmo' ),
						__( 'It looks like your restriction method is set to "Password" but you don\'t have a password form page created. Would you like to automatically create one?', 'wcmo' )
					); ?>
					<?php
					printf(
						'<p><a class="button button-primary" href="%s">%s</a></p>',
						esc_url( $url ),
						__( 'Yes, please create a page for the password form', 'wcmo' )
					); ?>
				</div>
			<?php } else if( isset( $_GET['page_created'] ) ) { ?>
				<div class="notice notice-success">
					<?php
					$message = sprintf(
						__( 'The page has been created. You can <a href="%s" target="_blank">view it here</a>.', 'wcmo' ),
						esc_url( get_permalink( $_GET['page_created'] ) )
					);
					printf(
						'<p>%s</p>',
						$message
					); ?>
				</div>
			<?php } else if( isset( $_GET['page_error'] ) ) { ?>
				<div class="notice notice-error">
					<?php
					printf(
						'<p>%s</p>',
						__( 'There was an error creating the page. You might like to try again.', 'wcmo' )
					); ?>
				</div>
			<?php }
		}

		public function password_page_exists() {
			// Check all pages for the wcmo_password_form shortcode
			$has_shortcode = false;
			$pages = get_pages();
			if( $pages ) {
				foreach( $pages as $page ) {
					if( has_shortcode( $page->post_content, 'wcmo_password_form') ) {
						$has_shortcode = true;
						break;
					}
				}
			}
			return $has_shortcode;
		}

		/**
		 * Create a password page
		 */
		public function create_password_page() {

			$has_shortcode = $this->password_page_exists();
			if( ! $has_shortcode && isset( $_GET['create_page'] ) ) {
				// Create a new page and embed the shortcode
				$post_id = wp_insert_post(
					array(
						'post_title'		=> __( 'Password Form', 'wcmo' ),
						'post_content'	=> '[wcmo_password_form]',
						'post_status'		=> 'publish',
						'post_type'			=> 'page'
					)
				);
				if( $post_id && ! is_wp_error( $post_id ) ) {
					update_option( 'wcmo_form_page', $post_id );
					$url = menu_page_url( 'wc-settings', false );
					$url = add_query_arg(
						array(
							'tab'						=> 'wcmo',
							'page_created'	=> $post_id
						),
						$url
					);
					wp_redirect( $url );
					die;
				} else {
					$url = menu_page_url( 'wc-settings', false );
					$url = add_query_arg(
						array(
							'tab'					=> 'wcmo',
							'page_error'	=> true
						),
						$url
					);
					wp_redirect( $url );
					die;
				}

			}
		}

		function add_user_ids( $actions, $user_object ) {
			$actions['user_id'] = $user_object->ID;
			return $actions;
		}

		function admin_body_class( $classes ) {
			if( isset( $_GET['section'] ) && $_GET['section'] == 'wcmo_roles' ) {
				$classes .= ' wcmo-role-settings-page';
			}
			return $classes;
		}

	}

	$WCMO_Settings = new WCMO_Settings;
	$WCMO_Settings->init();

}
