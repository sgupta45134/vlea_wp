<?php

namespace DynamicVisibilityForElementor\Extensions;

use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;
use DynamicVisibilityForElementor\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class DCE_Extension_Visibility extends DCE_Extension_Prototype {

	public function __construct() {
		\Elementor\Controls_Manager::add_tab(
			'dce_visibility',
			__( 'Visibility', 'dynamic-visibility-for-elementor' )
		);
		parent::__construct();
	}

	public $name = 'Visibility';
	public $has_controls = true;
	public $common_sections_actions = [
		[
			'element' => 'common',
			'action' => '_section_style',
		],
		[
			'element' => 'section',
			'action' => 'section_advanced',
		],
		[
			'element' => 'column',
			'action' => 'section_advanced',
		],
	];
	public static $tabs = [
		'post' => 'Post',
		'user' => 'User & Role',
		'archive' => 'Term & Archive ',
		'dynamic_tag' => 'Dynamic Tags',
		'device' => 'Device & Browser',
		'datetime' => 'Date & Time',
		'context' => 'Context',
		'woocommerce' => 'WooCommerce',
		'myfastapp' => 'My FastAPP',
		'random' => 'Random',
		'custom' => 'Custom Condition',
		'events' => 'Events',
		'repeater' => 'Advanced',
		'fallback' => 'Fallback',
	];
	public static $triggers = [
		'user' => [
			'label' => 'User & Role',
			'options' => [
				'role',
				'users',
				'usermeta',
			],
		],
		'device' => [
			'label' => 'Device & Browser',
			'options' => [
				'browser',
				'responsive',
			],
		],
		'post' => [
			'label' => 'Current Post',
			'options' => [
				'leaf',
				'parent',
				'node',
				'root',
			],
		],
	];

	public static function compare_options() {
		return [
			'not' => [
				'title' => __( 'Not set or empty', 'dynamic-visibility-for-elementor' ),
				'icon' => 'eicon-circle-o',
			],
			'isset' => [
				'title' => __( 'Valorized with any value', 'dynamic-visibility-for-elementor' ),
				'icon' => 'eicon-dot-circle-o',
			],
			'lt' => [
				'title' => __( 'Less than', 'dynamic-visibility-for-elementor' ),
				'icon' => 'fa fa-angle-left',
			],
			'gt' => [
				'title' => __( 'Greater than', 'dynamic-visibility-for-elementor' ),
				'icon' => 'fa fa-angle-right',
			],
			'contain' => [
				'title' => __( 'Contain', 'dynamic-visibility-for-elementor' ),
				'icon' => 'eicon-check',
			],
			'not_contain' => [
				'title' => __( 'Not Contain', 'dynamic-visibility-for-elementor' ),
				'icon' => 'eicon-close',
			],
			'in_array' => [
				'title' => __( 'In Array', 'dynamic-visibility-for-elementor' ),
				'icon' => 'fa fa-bars',
			],
			'value' => [
				'title' => __( 'Equal to', 'dynamic-visibility-for-elementor' ),
				'icon' => 'fa fa-circle',
			],
			'not_value' => [
				'title' => __( 'Not Equal to', 'dynamic-visibility-for-elementor' ),
				'icon' => 'eicon-exchange',
			],
		];
	}

	public static function condition_satisfy( $field, $status, $value ) {
		switch ( $status ) {
			case 'isset':
				if ( ! empty( $field ) ) {
					return true;
				}
				break;
			case 'not':
				if ( empty( $field ) ) {
					return true;
				}
				break;
			case 'lt':
				if ( is_array( $field ) && count( $field ) < $value ) {
					return true;
				}
				if ( ! empty( $field ) && $field < $value ) {
					return true;
				}
				break;
			case 'gt':
				if ( is_array( $field ) && count( $field ) > $value ) {
					return true;
				}
				if ( ! empty( $field ) && $field > $value ) {
					return true;
				}
				break;
			case 'contain':
				if ( ! empty( $field ) ) {
					if ( is_array( $field ) && in_array( $value, $field ) ) {
						return true;
					}
				}
				if ( is_string( $field ) && strpos( $field, $value ) !== false ) {
					return true;
				}
				break;
			case 'not_contain':
				if ( empty( $field ) ) {
					return true;
				}
				if ( is_array( $field ) && ! in_array( $value, $field ) ) {
					return true;
				}
				if ( is_string( $field ) && strpos( $field, $value ) === false ) {
					return true;
				}
				break;
			case 'in_array':
				if ( ! is_array( $value ) ) {
					$value = Helper::to_string( $value );
					$value = Helper::str_to_array( ',', $value );
				}
				if ( is_array( $value ) && in_array( $field, $value ) ) {
					return true;
				}
				break;
			case 'not_value':
				if ( $field != $value ) {
					return true;
				}
				// no break
			case 'value':
				if ( $field == $value ) {
					return true;
				}
		}
		return false;
	}

	protected function add_actions() {
		add_action('elementor/editor/after_enqueue_scripts', function () {
			wp_register_script(
				'dce-script-editor-visibility',
				plugins_url( '/assets/js/visibility.js', DVE__FILE__ ),
				[ 'dce-script-editor' ],
				DVE_VERSION
			);
			wp_enqueue_script( 'dce-script-editor-visibility' );
		});

		$elements = [ 'common', 'section', 'column' ];
		foreach ( $elements as $el_type ) {
			add_action('elementor/element/' . $el_type . '/dce_section_visibility_advanced/before_section_end', function ( $element, $args ) {
				$this->add_controls( $element, $args );
			}, 10, 2);
			foreach ( self::$tabs as $tkey => $tvalue ) {
				// Activate controls for column
				add_action('elementor/element/' . $el_type . '/dce_section_visibility_' . $tkey . '/before_section_end', function ( $element, $args ) use ( $tkey ) {
					$args['section'] = $tkey;
					$this->add_controls( $element, $args );
				}, 10, 2);
			}
		}

		// WIDGET
		add_action( 'elementor/frontend/widget/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/widget/after_render', [ $this, '_end_element' ], 10, 1 );

		// SECTION
		add_action( 'elementor/frontend/section/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/section/after_render', [ $this, '_end_element' ], 10, 1 );

		// COLUMN
		add_action( 'elementor/frontend/column/before_render', [ $this, '_start_element' ], 10, 1 );
		add_action( 'elementor/frontend/column/after_render', [ $this, '_end_element' ], 10, 1 );
		add_action('elementor/frontend/section/before_render', function ( $element ) {
			$columns = $element->get_children();
			if ( ! empty( $columns ) ) {
				$cols_visible = count( $columns );
				$cols_hidden = 0;
				foreach ( $columns as $acol ) {
					if ( $this->is_hidden( $acol ) ) {
						$fallback = $acol->get_settings_for_display( 'dce_visibility_fallback' );
						if ( empty( $fallback ) ) {
							$cols_visible--;
							$cols_hidden++;
						}
					}
				}
				if ( $cols_hidden ) {
					if ( $cols_visible ) {
						$_column_size = round( 100 / $cols_visible );
						foreach ( $columns as $acol ) {
							$acol->set_settings( '_column_size', $_column_size );
						}
					} else {
						$element->add_render_attribute( '_wrapper', 'class', 'dce-visibility-element-hidden' );
						$element->add_render_attribute( '_wrapper', 'class', 'dce-visibility-original-content' );
					}
				}
			}
		}, 10, 1);
	}

	public function _ob( $settings ) {
		if ( Helper::user_can_elementor() && isset( $_GET['dce-nav'] ) ) {
			return false;
		}
		if ( empty( $settings['dce_visibility_dom'] ) ) {
			return true;
		}
		return false;
	}

	public function _start_element( $element ) {
		$settings = $element->get_settings_for_display();
		if ( ! empty( $settings['enabled_visibility'] ) ) {
			$hidden = $this->is_hidden( $element );
			if ( $hidden ) {
				if ( $this->_ob( $settings ) ) {
					ob_start();
				} else {
					$element->add_render_attribute( '_wrapper', 'class', 'dce-visibility-element-hidden' );
					$element->add_render_attribute( '_wrapper', 'class', 'dce-visibility-original-content' );
				}
			}
			$this->set_element_view_counters( $element, $hidden );
		}
	}

	public function _end_element( $element ) {
		$settings = $element->get_settings_for_display();
		if ( ! empty( $settings['enabled_visibility'] ) ) {
			if ( $this->is_hidden( $element ) ) {
				if ( $this->_ob( $settings ) ) {
					$content = ob_get_clean();
				}
				$fallback = $this->get_fallback( $settings, $element );
				if ( $fallback ) {
					$fallback = str_replace( 'dce-visibility-element-hidden', '', $fallback );
					$fallback = str_replace( 'dce-visibility-original-content', 'dce-visibility-fallback-content', $fallback );
					echo $fallback;
				}
			}
			$this->print_scripts( $element, $settings );
		}
	}

	public function get_control_section( $section_name, $element ) {
		$low_name = $this->get_low_name();

		$element->start_controls_section(
			$section_name,
			[
				'tab' => 'dce_' . $low_name,
				'label' => '<span class="color-dce icon icon-dyn-logo-dce pull-right ml-1"></span> ' . $this->name,
			]
		);
		$element->end_controls_section();

		foreach ( self::$tabs as $tkey => $tlabel ) {
			$section_name = 'dce_section_' . $low_name . '_' . $tkey;

			$condition = [
				'enabled_' . $low_name . '!' => '',
				'dce_' . $low_name . '_hidden' => '',
				'dce_' . $low_name . '_mode' => 'quick',
				'dce_' . $low_name . '_triggers' => [ $tkey ],
			];
			$condition = [];
			$conditions = [
				'terms' => [
					[
						'name' => 'enabled_' . $low_name,
						'operator' => '!=',
						'value' => '',
					],
					[
						'name' => 'dce_' . $low_name . '_hidden',
						'operator' => '==',
						'value' => '',
					],
					[
						'name' => 'dce_' . $low_name . '_mode',
						'operator' => '==',
						'value' => 'quick',
					],
					[
						'name' => 'dce_' . $low_name . '_triggers',
						'operator' => 'contains',
						'value' => $tkey,
					],
				],
			];

			if ( $tkey == 'fallback' ) {
				$condition = [ 'enabled_' . $low_name . '!' => '' ];
				$conditions = [];
			}
			if ( $tkey == 'repeater' ) {
				$condition = [
					'enabled_' . $low_name . '!' => '',
					'dce_' . $low_name . '_hidden' => '',
					'dce_' . $low_name . '_mode' => 'advanced',
				];
				$conditions = [];
			}

			$section_settings = [
				'tab' => 'dce_' . $low_name,
				'label' => $tlabel,
			];
			if ( ! empty( $condition ) ) {
				$section_settings['condition'] = $condition;
			}
			if ( ! empty( $conditions ) ) {
				$section_settings['conditions'] = $conditions;
			}
			$element->start_controls_section(
				$section_name,
				$section_settings
			);
			$element->end_controls_section();
		}
	}

	/**
	 * Add Controls
	 *
	 * @since 0.5.5
	 *
	 * @access private
	 */
	private function add_controls( $element, $args ) {
		$taxonomies = Helper::get_taxonomies();

		if ( isset( $args['section'] ) ) {
			$section = $args['section'];
		} else {
			$section = 'advanced';
		}

		$element_type = $element->get_type();

		if ( $section == 'advanced' ) {
			$element->add_control(
				'enabled_visibility',
				[
					'label' => __( 'Visibility', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'frontend_available' => true,
				]
			);

			$element->add_control(
				'dce_visibility_hidden',
				[
					'label' => __( 'Always hide this element', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'condition' => [
						'enabled_visibility' => 'yes',
					],
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_dom',
				[
					'label' => __( 'Keep HTML', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Keep the HTML element in the DOM and hide this element via CSS.', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'enabled_visibility' => 'yes',
					],
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_mode',
				[
					'label' => __( 'Composition mode', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::HIDDEN,
					'default' => 'quick',
				]
			);

			$element->add_control(
				'dce_visibility_selected',
				[
					'label' => __( 'Display mode', 'dynamic-visibility-for-elementor' ),
					'description' => __( 'Hide or Show an element when a condition is triggered.', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'yes' => [
							'title' => __( 'Show', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-eye',
						],
						'hide' => [
							'title' => __( 'Hide', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-eye-slash',
						],
					],
					'default' => 'yes',
					'toggle' => false,
					'condition' => [
						'enabled_visibility' => 'yes',
						'dce_visibility_hidden' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_logical_connective',
				[
					'label' => __( 'Logical connective', 'dynamic-visibility-for-elementor' ),
					'description' => __( 'This setting determines how the conditions are combined. If OR is selected the condition is satisfied when at least one condition is satisfied. If AND is selected all conditions must be satisfied.', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => 'or',
					'return_value' => 'and',
					'label_on' => __( 'AND', 'dynamic-visibility-for-elementor' ),
					'label_off' => __( 'OR', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'enabled_visibility' => 'yes',
						'dce_visibility_hidden' => '',
					],
				]
			);

			$_triggers = self::$tabs;
			unset( $_triggers['v2'] );
			unset( $_triggers['repeater'] );
			unset( $_triggers['fallback'] );
			$element->add_control(
				'dce_visibility_triggers',
				[
					'label' => __( 'Triggers', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => $_triggers,
					'default' => array_keys( $_triggers ),
					'multiple' => true,
					'separator' => 'before',
					'label_block' => true,
					'condition' => [
						'enabled_visibility' => 'yes',
						'dce_visibility_hidden' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_debug',
				[
					'label' => __( 'Debug', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Get a report of triggered rule', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
					'condition' => [
						'enabled_visibility' => 'yes',
						'dce_visibility_hidden' => '',
					],
				]
			);
			if ( defined( 'DVE_PLUGIN_BASE' ) ) {
				$element->add_control(
					'dce_visibility_review',
					[
						'label' => '<b>' . __( 'Did you enjoy Dynamic Visibility extension?', 'dynamic-visibility-for-elementor' ) . '</b>',
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => __( 'Please leave us a', 'dynamic-visibility-for-elementor' )
						. ' <a target="_blank" href="https://wordpress.org/support/plugin/dynamic-visibility-for-elementor/reviews/?filter=5/#new-post">★★★★★</a> '
						. __( 'rating.<br>We really appreciate your support!', 'dynamic-visibility-for-elementor' ),
						'separator' => 'before',
					]
				);
			}
		}

		if ( $section == 'dynamic_tag' ) {
			$refl = new \ReflectionClass( '\Elementor\Modules\DynamicTags\Module' );
			$element->add_control(
				'dce_visibility_dynamic_tag',
				[
					'label' => __( 'Dynamic Tag', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::MEDIA,
					'dynamic' => [
						'active' => true,
						'categories' => $refl->getConstants(),
					],
					'placeholder' => __( 'Select condition field', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_dynamic_tag_status',
				[
					'label' => __( 'Status', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'label_block' => true,
					'options' => self::compare_options(),
					'default' => 'isset',
					'toggle' => false,
				]
			);
			$element->add_control(
				'dce_visibility_dynamic_tag_value',
				[
					'type' => Controls_Manager::TEXT,
					'label' => __( 'Value', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_dynamic_tag_status!' => [ 'not', 'isset' ],
					],
				]
			);
		}

		if ( $section == 'v2' ) {
			if ( false ) {
				$ctype = Controls_Manager::HIDDEN;
			} else {
				$ctype = Controls_Manager::SWITCHER;
				$element->add_control(
					'dce_visibility_v2_notice',
					[
						'label' => __( 'WARNING', 'dynamic-visibility-for-elementor' ),
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => __( 'If you updated from v2 set everything to Yes and manage it from the main \"Display Mode\", otherwise ignore this control section', 'dynamic-visibility-for-elementor' ),
					]
				);
			}
			$element->add_control(
				'dce_visibility_user_selected',
				[
					'label' => __( 'User Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
			$element->add_control(
				'dce_visibility_device_selected',
				[
					'label' => __( 'Device Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
			$element->add_control(
				'dce_visibility_datetime_selected',
				[
					'label' => __( 'DateTime Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
			$element->add_control(
				'dce_visibility_context_selected',
				[
					'label' => __( 'Context Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
			$element->add_control(
				'dce_visibility_tags_selected',
				[
					'label' => __( 'Tags Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
			$element->add_control(
				'dce_visibility_custom_condition_selected',
				[
					'label' => __( 'Custom Show/Hide', 'dynamic-visibility-for-elementor' ),
					'type' => $ctype,
					'default' => 'yes',
				]
			);
		}

		if ( $section == 'user' ) {

			$element->add_control(
				'dce_visibility_role',
				[
					'label' => __( 'Roles', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Roles', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'multiple' => true,
					'query_type' => 'users',
					'object_type' => 'role',
					'description' => __( 'Limit visualization to specific user roles', 'dynamic-visibility-for-elementor' ),
				]
			);

			$element->add_control(
				'dce_visibility_users',
				[
					'label' => __( 'Selected Users', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'Type here the list of users who will be able to view (or not) this element. You can use their ID, email or username. Simply separate them by a comma. (e.g. \"23, email@yoursite.com, username\")', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_can',
				[
					'label' => __( 'User can', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'Trigger by User capability, for example: "manage_options"', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_usermeta',
				[
					'label' => __( 'User Field', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Meta key or Name', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'query_type' => 'fields',
					'object_type' => 'user',
					'description' => __( 'Triggered by a selected User Field value', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_usermeta_status',
				[
					'label' => __( 'User Field Status', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => self::compare_options(),
					'default' => 'isset',
					'toggle' => false,
					'label_block' => true,
					'condition' => [
						'dce_visibility_usermeta!' => '',
					],
				]
			);
			$element->add_control(
					'dce_visibility_usermeta_value',
					[
						'label' => __( 'User Field Value', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::TEXT,
						'description' => __( 'The specific value of the User Field', 'dynamic-visibility-for-elementor' ),
						'condition' => [
							'dce_visibility_usermeta!' => '',
							'dce_visibility_usermeta_status!' => [ 'not', 'isset' ],
						],
					]
			);

			$element->add_control(
				'dce_visibility_ip',
				[
					'label' => __( 'Remote IP', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'Type here the list of IP who will be able to view this element.<br>Separate IPs by comma. (ex. "123.123.123.123, 8.8.8.8, 4.4.4.4")', 'dynamic-visibility-for-elementor' )
					. '<br><b>' . __( 'Your current IP is: ', 'dynamic-visibility-for-elementor' ) . sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) . '</b>',
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_referrer',
				[
					'label' => __( 'Referrer', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered when previous page is a specific page.', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_referrer_list',
				[
					'label' => __( 'Specific referral site authorized:', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXTAREA,
					'placeholder' => 'facebook.com' . PHP_EOL . 'google.com',
					'description' => __( 'Only selected referral, once per line. If empty it is triggered for all external site.', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_referrer' => 'yes',
					],
				]
			);

			$element->add_control(
					'dce_visibility_max_user',
					[
						'label' => __( 'Max per User', 'dynamic-visibility-for-elementor' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'min' => 1,
						'separator' => 'before',
					]
			);

			if ( Helper::is_plugin_active( 'geoip-detect' ) && function_exists( 'geoip_detect2_get_info_from_current_ip' ) ) {
					$geoinfo = geoip_detect2_get_info_from_current_ip();
					$countryInfo = new \YellowTree\GeoipDetect\Geonames\CountryInformation();
				if ( $countryInfo ) {
					$countries = $countryInfo->getAllCountries();
					$element->add_control(
						'dce_visibility_country',
						[
							'label' => __( 'Country', 'dynamic-visibility-for-elementor' ),
							'type' => Controls_Manager::SELECT2,
							'options' => $countries,
							'description' => __( 'Trigger visibility for a specific country.', 'dynamic-visibility-for-elementor' ),
							'multiple' => true,
							'separator' => 'before',
						]
					);
					$your_city = '';
					if ( ! empty( $geoinfo ) && ! empty( $geoinfo->city ) && ! empty( $geoinfo->city->names ) ) {
						$your_city = '<br>' . __( 'Actually you are in:', 'dynamic-visibility-for-elementor' ) . ' ' . implode( ', ', $geoinfo->city->names );
					}
					$element->add_control(
						'dce_visibility_city',
						[
							'label' => __( 'City', 'dynamic-visibility-for-elementor' ),
							'type' => Controls_Manager::TEXT,
							'description' => __( 'Type here the name of the city which triggers the condition. Insert the city name translated in one of the supported languages (preferable in EN). You can insert multiple cities, comma-separated.', 'dynamic-visibility-for-elementor' ) . $your_city,
						]
					);
				}
			}
		}

		if ( $section == 'device' ) {

			$element->add_control(
					'dce_visibility_responsive',
					[
						'label' => __( 'Responsive', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::CHOOSE,
						'options' => [

							'desktop' => [
								'title' => __( 'Desktop and Tv', 'dynamic-visibility-for-elementor' ),
								'icon' => 'fa fa-desktop',
							],
							'mobile' => [
								'title' => __( 'Mobile and Tablet', 'dynamic-visibility-for-elementor' ),
								'icon' => 'fa fa-mobile',
							],
						],
						'description' => __( 'Not really responsive, remove the element from the code based on the user\'s device. This trigger uses native WP device detection.', 'dynamic-visibility-for-elementor' ) . ' <a href="https://codex.wordpress.org/Function_Reference/wp_is_mobile" target="_blank">' . __( 'Read more.', 'dynamic-visibility-for-elementor' ) . '</a>',

					]
			);
			$element->add_control(
				'dce_visibility_browser',
				[
					'label' => __( 'Browser', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => [
						'is_chrome' => 'Google Chrome',
						'is_gecko' => 'FireFox',
						'is_safari' => 'Safari',
						'is_IE' => 'Internet Explorer',
						'is_edge' => 'Microsoft Edge',
						'is_NS4' => 'Netscape',
						'is_opera' => 'Opera',
						'is_lynx' => 'Lynx',
						'is_iphone' => 'iPhone Safari',
					],
					'description' => __( 'Trigger visibility for a specific browser.', 'dynamic-visibility-for-elementor' ),
					'multiple' => true,
					'separator' => 'before',
				]
			);
		}

		if ( $section == 'datetime' ) {
			if ( time() != current_time( 'timestamp' ) ) {
					$element->add_control(
						'dce_visibility_datetime_important_note',
						[
							'type' => \Elementor\Controls_Manager::RAW_HTML,
							'raw' => '<small><br>' . __( 'Server time and WordPress time are different.', 'dynamic-visibility-for-elementor' ) . '<br>'
							. __( 'Will be used the Wordpress time you set in', 'dynamic-visibility-for-elementor' )
							. ' <a target="_blank" href="' . admin_url( 'options-general.php' ) . '">' . __( 'Wordpress General preferences', 'dynamic-visibility-for-elementor' ) . '</a>.<br>'
							. '<br>'
							. '<strong>SERVER time:</strong><br>' . date( 'r' ) . '<br><br>'
							. '<strong>WORDPRESS time:</strong><br>' . current_time( 'r' )
							. '</small>',
							'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
						]
					);
			}

			$element->add_control(
					'dce_visibility_date_dynamic',
					[
						'label' => __( 'Use Dynamic Dates', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::SWITCHER,
					]
			);
			$element->add_control(
					'dce_visibility_date_dynamic_from',
					[
						'label' => __( 'Date FROM', 'dynamic-visibility-for-elementor' ),
						'label_block' => true,
						'type' => Controls_Manager::TEXT,
						'placeholder' => 'Y-m-d H:i:s',
						'description' => __( 'If set the element will appear after this date', 'dynamic-visibility-for-elementor' ),
						'condition' => [
							'dce_visibility_date_dynamic!' => '',
						],
						'dynamic' => [
							'active' => true,
						],
					]
			);
			$element->add_control(
					'dce_visibility_date_dynamic_to',
					[
						'label' => __( 'Date TO', 'dynamic-visibility-for-elementor' ),
						'label_block' => true,
						'type' => Controls_Manager::TEXT,
						'placeholder' => 'Y-m-d H:i:s',
						'description' => __( 'If set the element will be visible until this date', 'dynamic-visibility-for-elementor' ),
						'condition' => [
							'dce_visibility_date_dynamic!' => '',
						],
						'dynamic' => [
							'active' => true,
						],
					]
			);

			$element->add_control(
					'dce_visibility_date_from',
					[
						'label' => __( 'Date FROM', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::DATE_TIME,
						'description' => __( 'If set the element will appear after this date', 'dynamic-visibility-for-elementor' ),
						'condition' => [
							'dce_visibility_date_dynamic' => '',
						],
					]
			);
			$element->add_control(
					'dce_visibility_date_to',
					[
						'label' => __( 'Date TO', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::DATE_TIME,
						'description' => __( 'If set the element will be visible until this date', 'dynamic-visibility-for-elementor' ),
						'condition' => [
							'dce_visibility_date_dynamic' => '',
						],
					]
			);

			$element->add_control(
					'dce_visibility_period_from',
					[
						'label' => __( 'Period FROM', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::TEXT,
						'description' => __( 'If set the element will appear after this period', 'dynamic-visibility-for-elementor' ),
						'placeholder' => 'mm/dd',
						'separator' => 'before',
						'dynamic' => [
							'active' => true,
						],
					]
			);
			$element->add_control(
					'dce_visibility_period_to',
					[
						'label' => __( 'Period TO', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::TEXT,
						'placeholder' => 'mm/dd',
						'description' => __( 'If set the element will be visible until this period', 'dynamic-visibility-for-elementor' ),
						'dynamic' => [
							'active' => true,
						],
					]
			);

			global $wp_locale;
			$week = [];
			for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
					$week[ esc_attr( $day_index ) ] = $wp_locale->get_weekday( $day_index );
			}
			$element->add_control(
					'dce_visibility_time_week',
					[
						'label' => __( 'Days of the WEEK', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::SELECT2,
						'options' => $week,
						'description' => __( 'Select days in the week.', 'dynamic-visibility-for-elementor' ),
						'multiple' => true,
						'separator' => 'before',
					]
			);

			$element->add_control(
					'dce_visibility_time_from',
					[
						'label' => __( 'Time FROM', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::TEXT,
						'placeholder' => 'H:m',
						'description' => __( 'If set (in H:m format) the element will appear after this time.', 'dynamic-visibility-for-elementor' ),
						'separator' => 'before',
					]
			);
			$element->add_control(
					'dce_visibility_time_to',
					[
						'label' => __( 'Time TO', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::TEXT,
						'placeholder' => 'H:m',
						'description' => __( 'If set (in H:m format) the element will be visible until this time', 'dynamic-visibility-for-elementor' ),

					]
			);
		}

		if ( $section == 'context' ) {
			$element->add_control(
				'dce_visibility_parameter',
				[
					'label' => __( 'Parameter', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'Type here the name of the parameter passed in GET, COOKIE or POST method', 'dynamic-visibility-for-elementor' ),

				]
			);
			$element->add_control(
				'dce_visibility_parameter_method',
				[
					'label' => __( 'Parameter Method', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'GET' => [
							'title' => __( 'GET', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-circle-o',
						],
						'POST' => [
							'title' => __( 'POST', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-circle',
						],
						'REQUEST' => [
							'title' => __( 'REQUEST', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-dot-circle-o',
						],
						'COOKIE' => [
							'title' => __( 'COOKIE', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-pie-chart',
						],
						'SERVER' => [
							'title' => __( 'SERVER', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-server',
						],
					],
					'default' => 'REQUEST',
					'toggle' => false,
					'condition' => [
						'dce_visibility_parameter!' => '',
					],
				]
			);
			$element->add_control(
			'dce_visibility_parameter_status',
			[
				'label' => __( 'Parameter Status', 'dynamic-visibility-for-elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => self::compare_options(),
				'default' => 'isset',
				'toggle' => false,
				'label_block' => true,
				'condition' => [
					'dce_visibility_parameter!' => '',
				],
			]
			);
			$element->add_control(
				'dce_visibility_parameter_value',
				[
					'label' => __( 'Parameter Value', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'The specific value of the parameter', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_parameter!' => '',
						'dce_visibility_parameter_status!' => [ 'not', 'isset' ],
					],
				]
			);

			$element->add_control(
			'dce_visibility_conditional_tags_site',
			[
				'label' => __( 'Site', 'dynamic-visibility-for-elementor' ),
				'type' => Controls_Manager::SELECT2,
				'options' => [
					'is_dynamic_sidebar' => __( 'Dynamic sidebar', 'dynamic-visibility-for-elementor' ),
					'is_active_sidebar' => __( 'Active sidebar', 'dynamic-visibility-for-elementor' ),
					'is_rtl' => __( 'RTL', 'dynamic-visibility-for-elementor' ),
					'is_multisite' => __( 'Multisite', 'dynamic-visibility-for-elementor' ),
					'is_main_site' => __( 'Main site', 'dynamic-visibility-for-elementor' ),
					'is_child_theme' => __( 'Child theme', 'dynamic-visibility-for-elementor' ),
					'is_customize_preview' => __( 'Customize preview', 'dynamic-visibility-for-elementor' ),
					'is_multi_author' => __( 'Multi author', 'dynamic-visibility-for-elementor' ),
					'is feed' => __( 'Feed', 'dynamic-visibility-for-elementor' ),
					'is_trackback' => __( 'Trackback', 'dynamic-visibility-for-elementor' ),
				],
				'multiple' => true,
				'separator' => 'before',
			]
			);

			$element->add_control(
				'dce_visibility_max_day',
				[
					'label' => __( 'Max per Day', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_max_total',
				[
					'label' => __( 'Max Total', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'separator' => 'before',
				]
			);

			$select_lang = [];
			// WPML
			global $sitepress;
			if ( ! empty( $sitepress ) ) {
				$langs = $sitepress->get_active_languages();
				if ( ! empty( $langs ) ) {
					foreach ( $langs as $lkey => $lvalue ) {
						$select_lang[ $lkey ] = $lvalue['native_name'];
					}
				}
			}
			// POLYLANG
			if ( Helper::is_plugin_active( 'polylang' ) && function_exists( 'pll_languages_list' ) ) {
				$translations = pll_languages_list();
				$translations_name = pll_languages_list( [ 'fields' => 'name' ] );
				if ( ! empty( $translations ) ) {
					foreach ( $translations as $tkey => $tvalue ) {
						$select_lang[ $tvalue ] = $translations_name[ $tkey ];
					}
				}
			}
			// TRANSLATEPRESS
			if ( Helper::is_plugin_active( 'translatepress-multilingual' ) ) {
				$settings = get_option( 'trp_settings' );
				if ( $settings && is_array( $settings ) && isset( $settings['publish-languages'] ) ) {
					$languages = $settings['publish-languages'];
					$trp = \TRP_Translate_Press::get_trp_instance();
					$trp_languages = $trp->get_component( 'languages' );
					$published_languages = $trp_languages->get_language_names( $languages, 'english_name' );
					$select_lang = $published_languages;
				}
			}
			// WEGLOT
			if ( Helper::is_plugin_active( 'weglot' ) && function_exists( 'weglot_get_destination_languages' ) ) {
				$select_lang_array = array_column( weglot_get_destination_languages(), 'language_to' );
				// Add current language
				$select_lang_array[] = weglot_get_current_language();
				if ( ! empty( $select_lang_array ) ) {
					foreach ( $select_lang_array as $key => $value ) {
						$select_lang[ $value ] = $value;
					}
				}
			}
			if ( ! empty( $select_lang ) ) {
				$element->add_control(
					'dce_visibility_lang',
					[
						'label' => __( 'Language', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::SELECT2,
						'options' => $select_lang,
						'multiple' => true,
						'separator' => 'before',
					]
				);
			}
		}

		if ( $section == 'post' ) {
			$element->add_control(
				'dce_visibility_post_id',
				[
					'label' => __( 'Post ID', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'current' => [
							'title' => __( 'Current', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-list',
						],
						'global' => [
							'title' => __( 'Global', 'dynamic-visibility-for-elementor' ),
							'icon' => 'fa fa-globe',
						],
						'static' => [
							'title' => __( 'Static', 'dynamic-visibility-for-elementor' ),
							'icon' => 'eicon-pencil',
						],
					],
					'default' => 'current',
					'toggle' => false,
				]
			);
			$element->add_control(
				'dce_visibility_post_id_static',
				[
					'label' => __( 'Set Post ID', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'condition' => [
						'dce_visibility_post_id' => 'static',
					],
				]
			);
			$element->add_control(
				'dce_visibility_post_id_description',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => '<small>' . __( 'In some cases, Current ID and Global ID may be different. For example, if you use a widget with a loop on a page, then Global ID will be Page ID, and Current ID will be Post ID in preview inside the loop.', 'dynamic-visibility-for-elementor' ) . '</small>',
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				]
			);

			$element->add_control(
				'dce_visibility_cpt',
				[
					'label' => __( 'Post Type', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Post Type', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'multiple' => true,
					'query_type' => 'posts',
					'object_type' => 'type',
					'description' => __( 'Visible if current post is one of these Post Types', 'dynamic-visibility-for-elementor' ),
				]
			);

			$element->add_control(
				'dce_visibility_post',
				[
					'label' => __( 'Page/Post', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Post Title', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'query_type' => 'posts',
					'description' => __( 'Visible if current post is one of these Page/Posts', 'dynamic-visibility-for-elementor' ),
					'multiple' => true,
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_tax',
				[
					'label' => __( 'Taxonomy', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => $taxonomies,
					'description' => __( 'Triggered if current post is related to this Taxonomy', 'dynamic-visibility-for-elementor' ),
					'multiple' => false,
					'separator' => 'before',
				]
			);

			foreach ( $taxonomies as $tkey => $atax ) {
				if ( $tkey ) {
					$element->add_control(
					'dce_visibility_term_' . $tkey,
					[
						'label' => __( 'Terms', 'dynamic-visibility-for-elementor' ),
						'type' => 'ooo_query',
						'placeholder' => __( 'Term Name', 'dynamic-visibility-for-elementor' ),
						'label_block' => true,
						'query_type' => 'terms',
						'object_type' => $tkey,
						'description' => __( 'Visible if current post is related to these terms', 'dynamic-visibility-for-elementor' ),
						'multiple' => true,
						'condition' => [
							'dce_visibility_tax' => $tkey,
						],
					]
					);
				}
			}
			$element->add_control(
				'dce_visibility_field',
				[
					'label' => __( 'Post Field', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Meta key or Name', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'query_type' => 'fields',
					'object_type' => 'post',
					'description' => __( 'Triggered by a selected Post Field value', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_field_status',
				[
					'label' => __( 'Post Field Status', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => self::compare_options(),
					'default' => 'isset',
					'toggle' => false,
					'label_block' => true,
					'condition' => [
						'dce_visibility_field!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_field_value',
				[
					'label' => __( 'Post Field Value', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::TEXT,
					'description' => __( 'The specific value of the Post Field', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_field!' => '',
						'dce_visibility_field_status!' => [ 'not', 'isset' ],
					],
				]
			);

			$element->add_control(
				'dce_visibility_meta',
				[
					'label' => __( 'Multiple Metas', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Meta key or Name', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'query_type' => 'metas',
					'object_type' => 'post',
					'description' => __( 'Triggered by specifics metas fields if they are valorized', 'dynamic-visibility-for-elementor' ),
					'multiple' => true,
					'separator' => 'before',
				]
			);

			$element->add_control(
				'dce_visibility_meta_operator',
				[
					'label' => __( 'Meta conditions', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => 'yes',
					'label_on' => __( 'And', 'dynamic-visibility-for-elementor' ),
					'label_off' => __( 'Or', 'dynamic-visibility-for-elementor' ),
					'description' => __( 'How post meta have to satisfy this conditions', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_meta!' => '',
					],
				]
			);

			$element->add_control(
					'dce_visibility_format',
					[
						'label' => __( 'Format', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::SELECT2,
						'options' => Helper::get_post_formats(),
						'description' => __( 'Triggered if current post is set as one of this format', 'dynamic-visibility-for-elementor' ) . '<br><a href="https://wordpress.org/support/article/post-formats/" target="_blank">' . __( 'Read more on Post Format.', 'dynamic-visibility-for-elementor' ) . '</a>',
						'multiple' => true,
						'separator' => 'before',
					]
			);

			$element->add_control(
				'dce_visibility_parent',
				[
					'label' => __( 'Is Parent', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for post with children', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_root',
				[
					'label' => __( 'Is Root', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for first level posts (without parent)', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_leaf',
				[
					'label' => __( 'Is Leaf', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for last level posts (without children)', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_node',
				[
					'label' => __( 'Is Node', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for intermedial level posts (with parent and child)', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_node_level',
				[
					'label' => __( 'Node level', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'condition' => [
						'dce_visibility_node!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_level',
				[
					'label' => __( 'Has Level', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'description' => __( 'Triggered for specific level posts', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_child',
				[
					'label' => __( 'Has Parent', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for children posts (with a parent)', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_child_parent',
				[
					'label' => __( 'Specific Parent Post IDs', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Post Title', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'multiple' => true,
					'separator' => 'before',
					'query_type' => 'posts',
					'condition' => [
						'dce_visibility_child!' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_sibling',
				[
					'label' => __( 'Has Siblings', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for post with siblings', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_friend',
				[
					'label' => __( 'Has Term Buddies', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for posts grouped in taxonomies with other posts', 'dynamic-visibility-for-elementor' ),
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_friend_term',
				[
					'label' => __( 'Terms where find Buddies', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Term Name', 'dynamic-visibility-for-elementor' ),
					'query_type' => 'terms',
					'description' => __( 'Specific a Term for current post has friends.', 'dynamic-visibility-for-elementor' ),
					'multiple' => true,
					'label_block' => true,
					'condition' => [
						'dce_visibility_friend!' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_conditional_tags_post',
				[
					'label' => __( 'Conditional Tags - Post', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => [
						'is_sticky' => __( 'Is Sticky', 'dynamic-visibility-for-elementor' ),
						'is_post_type_hierarchical' => __( 'Is Hierarchical Post Type', 'dynamic-visibility-for-elementor' ),
						'is_post_type_archive' => __( 'Is Post Type Archive', 'dynamic-visibility-for-elementor' ),
						'comments_open' => __( 'Comments are open', 'dynamic-visibility-for-elementor' ),
						'pings_open' => __( 'Pings are open', 'dynamic-visibility-for-elementor' ),
						'has_tag' => __( 'Has Tags', 'dynamic-visibility-for-elementor' ),
						'has_term' => __( 'Has Terms', 'dynamic-visibility-for-elementor' ),
						'has_excerpt' => __( 'Has Excerpt', 'dynamic-visibility-for-elementor' ),
						'has_post_thumbnail' => __( 'Has Post Thumbnail', 'dynamic-visibility-for-elementor' ),
						'has_nav_menu' => __( 'Has Nav menu', 'dynamic-visibility-for-elementor' ),
					],
					'multiple' => true,
					'separator' => 'before',
					'condition' => [
						'dce_visibility_post_id' => 'current',
					],
				]
			);
			$element->add_control(
				'dce_visibility_special',
				[
					'label' => __( 'Conditonal Tags - Page', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => [
						'is_front_page' => __( 'Front Page', 'dynamic-visibility-for-elementor' ),
						'is_home' => __( 'Home', 'dynamic-visibility-for-elementor' ),
						'is_404' => __( '404 Not Found', 'dynamic-visibility-for-elementor' ),
						'is_single' => __( 'Single', 'dynamic-visibility-for-elementor' ),
						'is_page' => __( 'Page', 'dynamic-visibility-for-elementor' ),
						'is_attachment' => __( 'Attachment', 'dynamic-visibility-for-elementor' ),
						'is_preview' => __( 'Preview', 'dynamic-visibility-for-elementor' ),
						'is_admin' => __( 'Admin', 'dynamic-visibility-for-elementor' ),
						'is_page_template' => __( 'Page Template', 'dynamic-visibility-for-elementor' ),
						'is_comments_popup' => __( 'Comments Popup', 'dynamic-visibility-for-elementor' ),
						'is_woocommerce' => __( 'A Woocommerce Page', 'dynamic-visibility-for-elementor' ),
						'is_shop' => __( 'Shop', 'dynamic-visibility-for-elementor' ),
						'is_product' => __( 'Product', 'dynamic-visibility-for-elementor' ),
						'is_product_taxonomy' => __( 'Product Taxonomy', 'dynamic-visibility-for-elementor' ),
						'is_product_category' => __( 'Product Category', 'dynamic-visibility-for-elementor' ),
						'is_product_tag' => __( 'Product Tag', 'dynamic-visibility-for-elementor' ),
						'is_cart' => __( 'Cart', 'dynamic-visibility-for-elementor' ),
						'is_checkout' => __( 'Checkout', 'dynamic-visibility-for-elementor' ),
						'is_add_payment_method_page' => __( 'Add Payment method', 'dynamic-visibility-for-elementor' ),
						'is_checkout_pay_page' => __( 'Checkout Pay', 'dynamic-visibility-for-elementor' ),
						'is_account_page' => __( 'Account page', 'dynamic-visibility-for-elementor' ),
						'is_edit_account_page' => __( 'Edit Account', 'dynamic-visibility-for-elementor' ),
						'is_lost_password_page' => __( 'Lost password', 'dynamic-visibility-for-elementor' ),
						'is_view_order_page' => __( 'Order summary', 'dynamic-visibility-for-elementor' ),
						'is_order_received_page' => __( 'Order complete', 'dynamic-visibility-for-elementor' ),
					],
					'multiple' => true,
					'separator' => 'before',
					'condition' => [
						'dce_visibility_post_id' => 'current',
					],
				]
			);

		}

		if ( $section == 'woocommerce' ) {
			if ( Helper::is_plugin_active( 'woocommerce' ) ) {
				$element->add_control(
					'dce_visibility_woo_product_id_static',
					[
						'label' => __( 'Product in Cart', 'dynamic-visibility-for-elementor' ),
						'type' => 'ooo_query',
						'placeholder' => __( 'Product Name', 'dynamic-visibility-for-elementor' ),
						'label_block' => true,
						'query_type' => 'posts',
						'object_type' => 'product',
						'separator' => 'after',
					]
				);

				if ( Helper::is_plugin_active( 'woocommerce-memberships' ) ) {
					$plans = get_posts([
						'post_type' => 'wc_membership_plan',
						'post_status' => 'publish',
						'numberposts' => -1,
					]);
					if ( ! empty( $plans ) ) {
						$element->add_control(
							'dce_visibility_woo_membership_post',
							[
								'label' => __( 'Use Post Membership settings', 'dynamic-visibility-for-elementor' ),
								'type' => Controls_Manager::SWITCHER,
							]
						);

						$plan_options = [ 0 => __( 'NOT Member', 'dynamic-visibility-for-elementor' ) ];
						foreach ( $plans as $aplan ) {
							$plan_options[ $aplan->ID ] = $aplan->post_title;
						}
						$element->add_control(
							'dce_visibility_woo_membership',
							[
								'label' => __( 'Membership', 'dynamic-visibility-for-elementor' ),
								'type' => Controls_Manager::SELECT2,
								'options' => $plan_options,
								'multiple' => true,
								'label_block' => true,
								'condition' => [
									'dce_visibility_woo_membership_post' => '',
								],
							]
						);
					}
				}
			} else {
				$element->add_control(
				'dce_visibility_woo_notice',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => __( 'You need WooCommerce to use this trigger.', 'dynamic-visibility-for-elementor' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				]
				);
			}
		}

		if ( $section == 'myfastapp' ) {
			if ( ! defined( 'DCE_PATH' ) ) { //  Feature not present in FREE version
				$element->add_control(
					'dce_visibility_myfastapp_hide',
					[
						'raw' => __( 'Feature available only in Dynamic.ooo - Dynamic Content for Elementor, paid version.', 'dynamic-visibility-for-elementor' ),
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					]
				);
			} else {
				if ( Helper::is_myfastapp_active() ) {
					$element->add_control(
						'dce_visibility_myfastapp',
						[
							'label' => __( 'The visitor is', 'dynamic-visibility-for-elementor' ),
							'type' => Controls_Manager::SELECT,
							'options' => [
								'all' => __( 'on the site or in the app', 'dynamic-visibility-for-elementor' ),
								'site' => __( 'on the site', 'dynamic-visibility-for-elementor' ),
								'app' => __( 'in the app', 'dynamic-visibility-for-elementor' ),
							],
							'default' => 'all',
						]
					);
				} else {
					$element->add_control(
						'dce_visibility_myfastapp_notice',
						[
							'type' => Controls_Manager::RAW_HTML,
							'raw' => __( 'You need My FastAPP to use this trigger.', 'dynamic-visibility-for-elementor' ),
							'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
						]
					);
				}
			}
		}

		if ( $section == 'events' ) {
			$element->add_control(
				'dce_visibility_events_note',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'Using an Event trigger is necessary in order to activate "Keep HTML" from base settings', 'dynamic-visibility-for-elementor' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition' => [
						'dce_visibility_dom' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_event',
				[
					'label' => __( 'Event', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'click',
					'options' => [
						'click' => 'click',
						'mouseover' => 'mouseover',
						'dblclick'  => 'dblclick',
					],
					'condition' => [
						'dce_visibility_dom!' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_click',
				[
					'label' => __( 'Trigger on this element', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Type here the Selector in jQuery format. For example #name', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_dom!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_click_show',
				[
					'label' => __( 'Show Animation', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT,
					'options' => Helper::get_jquery_display_mode(),
					'condition' => [
						'dce_visibility_dom!' => '',
						'dce_visibility_click!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_click_other',
				[
					'label' => __( 'Hide other elements', 'dynamic-visibility-for-elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'description' => __( 'Type here the Selector in jQuery format. For example .elements', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_dom!' => '',
						'dce_visibility_click!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_click_toggle',
				[
					'label' => __( 'Toggle', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'condition' => [
						'dce_visibility_dom!' => '',
						'dce_visibility_click!' => '',
					],
				]
			);

			$element->add_control(
				'dce_visibility_load',
				[
					'label' => __( 'On Page Load', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'condition' => [
						'dce_visibility_dom!' => '',
					],
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_load_delay',
				[
					'label' => __( 'Delay time', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::NUMBER,
					'min' => 0,
					'default' => 0,
					'condition' => [
						'dce_visibility_dom!' => '',
						'dce_visibility_load!' => '',
					],
				]
			);
			$element->add_control(
				'dce_visibility_load_show',
				[
					'label' => __( 'Show Animation', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT,
					'options' => Helper::get_jquery_display_mode(),
					'condition' => [
						'dce_visibility_dom!' => '',
						'dce_visibility_load!' => '',
					],
				]
			);
		}

		if ( $section == 'archive' ) {

			// https://codex.wordpress.org/Conditional_Tags
			// https://codex.wordpress.org/Special:SpecialPages

			$element->add_control(
				'dce_visibility_archive',
				[
					'label' => __( 'Archive Type', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => [
						'is_blog' => __( 'Home blog (latest posts)', 'dynamic-visibility-for-elementor' ),
						'posts_page' => __( 'Posts page', 'dynamic-visibility-for-elementor' ),
						'is_tax' => __( 'Taxonomy', 'dynamic-visibility-for-elementor' ),
						'is_category' => __( 'Category', 'dynamic-visibility-for-elementor' ),
						'is_tag' => __( 'Tag', 'dynamic-visibility-for-elementor' ),
						'is_author' => __( 'Author', 'dynamic-visibility-for-elementor' ),
						'is_date' => __( 'Date', 'dynamic-visibility-for-elementor' ),
						'is_year' => __( 'Year', 'dynamic-visibility-for-elementor' ),
						'is_month' => __( 'Month', 'dynamic-visibility-for-elementor' ),
						'is_day' => __( 'Day', 'dynamic-visibility-for-elementor' ),
						'is_time' => __( 'Time', 'dynamic-visibility-for-elementor' ),
						'is_new_day' => __( 'New Day', 'dynamic-visibility-for-elementor' ),
						'is_search' => __( 'Search', 'dynamic-visibility-for-elementor' ),
						'is_paged' => __( 'Paged', 'dynamic-visibility-for-elementor' ),
						'is_main_query' => __( 'Main Query', 'dynamic-visibility-for-elementor' ),
						'in_the_loop' => __( 'In the Loop', 'dynamic-visibility-for-elementor' ),
					],
					'separator' => 'before',
				]
			);

			// TODO: specify what Category, Tag or CustomTax
			$element->add_control(
				'dce_visibility_archive_tax',
				[
					'label' => __( 'Taxonomy', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SELECT2,
					'options' => $taxonomies,
					'description' => __( 'Triggered if current post is related to this Taxonomy.', 'dynamic-visibility-for-elementor' ),
					'multiple' => false,
					'separator' => 'before',
					'condition' => [
						'dce_visibility_archive' => 'is_tax',
					],
				]
			);

			foreach ( $taxonomies as $tkey => $atax ) {
				if ( $tkey ) {
					switch ( $tkey ) {
						case 'post_tag':
							$condition = [
								'dce_visibility_archive' => 'is_tag',
							];
							break;
						case 'category':
							$condition = [
								'dce_visibility_archive' => 'is_category',
							];
							break;
						default:
							$condition = [
								'dce_visibility_archive' => 'is_tax',
								'dce_visibility_archive_tax' => $tkey,
							];
					}
					$element->add_control(
						'dce_visibility_archive_term_' . $tkey,
						[
							'label' => $atax . ' ' . __( 'Terms', 'dynamic-visibility-for-elementor' ),
							'type' => 'ooo_query',
							'placeholder' => __( 'Term Name', 'dynamic-visibility-for-elementor' ),
							'label_block' => true,
							'query_type' => 'terms',
							'object_type' => $tkey,
							'description' => __( 'Visible if current post is related to these terms', 'dynamic-visibility-for-elementor' ),
							'multiple' => true,
							'condition' => $condition,
						]
					);
				}
			}

			$element->add_control(
				'dce_visibility_term',
				[
					'label' => __( 'Taxonomy Term', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$element->add_control(
				'dce_visibility_term_parent',
				[
					'label' => __( 'Is Parent', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for term with children.', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_root',
				[
					'label' => __( 'Is Root', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for term of first level (without parent).', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_leaf',
				[
					'label' => __( 'Is Leaf', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for terms in last level (without children).', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_node',
				[
					'label' => __( 'Is Node', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for terms in intermedial level (with parent and children).', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_child',
				[
					'label' => __( 'Has Parent', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for terms which are children (with a parent).', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_sibling',
				[
					'label' => __( 'Has Siblings', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for terms with siblings.', 'dynamic-visibility-for-elementor' ),
				]
			);
			$element->add_control(
				'dce_visibility_term_count',
				[
					'label' => __( 'Has Posts', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'Triggered for terms has related Posts count.', 'dynamic-visibility-for-elementor' ),
				]
			);
		}

		if ( $section == 'random' ) {
			$element->add_control(
				'dce_visibility_random',
				[
					'label' => __( 'Random', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ '%' ],
					'range' => [
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
				]
			);
		}

		if ( $section == 'custom' ) {
			if ( ! defined( 'DCE_PATH' ) ) { //  Feature not present in FREE version
				$element->add_control(
					'dce_visibility_custom_hide',
					[
						'raw' => __( 'Feature available only in Dynamic.ooo - Dynamic Content for Elementor, paid version.', 'dynamic-visibility-for-elementor' ),
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					]
				);
			} else {
				if ( current_user_can( 'administrator' ) || ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					$element->add_control(
						'dce_visibility_custom_condition_php',
						[
							'label' => __( 'Custom PHP condition', 'dynamic-visibility-for-elementor' ),
							'type' => Controls_Manager::CODE,
							'language' => 'php',
							'default' => '',
							'description' => __( 'Type here a function that returns a boolean value. You can use all WP variables and functions.', 'dynamic-visibility-for-elementor' ),
						]
					);
				}
			}
		}

		if ( $section == 'fallback' ) {
			$element->add_control(
				'dce_visibility_fallback',
				[
					'label' => __( 'Fallback Content', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::SWITCHER,
					'description' => __( 'If you want to show something when the element is hidden', 'dynamic-visibility-for-elementor' ),
				]
			);
			if ( ! defined( 'DCE_PATH' ) ) { // free version doesn't support template shortcode
				$element->add_control(
					'dce_visibility_fallback_type',
					[
						'label' => __( 'Content type', 'dynamic-visibility-for-elementor' ),
						'type' => \Elementor\Controls_Manager::HIDDEN,
						'default' => 'text',
					]
				);
			} else {
				$element->add_control(
					'dce_visibility_fallback_type',
					[
						'label' => __( 'Content type', 'dynamic-visibility-for-elementor' ),
						'type' => Controls_Manager::CHOOSE,
						'options' => [
							'text' => [
								'title' => __( 'Text', 'dynamic-visibility-for-elementor' ),
								'icon' => 'fa fa-align-left',
							],
							'template' => [
								'title' => __( 'Template', 'dynamic-visibility-for-elementor' ),
								'icon' => 'fa fa-th-large',
							],
						],
						'default' => 'text',
						'condition' => [
							'dce_visibility_fallback!' => '',
						],
					]
				);
			}
			$element->add_control(
				'dce_visibility_fallback_template',
				[
					'label' => __( 'Render Template', 'dynamic-visibility-for-elementor' ),
					'type' => 'ooo_query',
					'placeholder' => __( 'Template Name', 'dynamic-visibility-for-elementor' ),
					'label_block' => true,
					'query_type' => 'posts',
					'object_type' => 'elementor_library',
					'description' => 'Use a Elementor Template as content of popup, useful for complex structure.',
					'condition' => [
						'dce_visibility_fallback!' => '',
						'dce_visibility_fallback_type' => 'template',
					],
				]
			);
			$element->add_control(
				'dce_visibility_fallback_text',
				[
					'label' => __( 'Text Fallback', 'dynamic-visibility-for-elementor' ),
					'type' => Controls_Manager::WYSIWYG,
					'default' => 'This element is currently hidden.',
					'description' => __( 'If the element is not visible, insert here your content', 'dynamic-visibility-for-elementor' ),
					'condition' => [
						'dce_visibility_fallback!' => '',
						'dce_visibility_fallback_type' => 'text',
					],
				]
			);
			if ( $element_type == 'section' ) {
					$element->add_control(
						'dce_visibility_fallback_section',
						[
							'label' => __( 'Use section wrapper', 'dynamic-visibility-for-elementor' ),
							'type' => Controls_Manager::SWITCHER,
							'default' => 'yes',
							'description' => __( 'Mantain original section wrapper.', 'dynamic-visibility-for-elementor' ),
							'condition' => [
								'dce_visibility_fallback!' => '',
							],
						]
				);
			}
		}

	}

	public function set_element_view_counters( $element, $hidden = false ) {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$user_id = get_current_user_id();
			$settings = $element->get_settings_for_display();
			if ( ( ! $hidden && $settings['dce_visibility_selected'] == 'yes' ) || ( $hidden && $settings['dce_visibility_selected'] == 'hide' ) ) {
				if ( ! empty( $settings['dce_visibility_max_user'] ) || ! empty( $settings['dce_visibility_max_day'] ) || ! empty( $settings['dce_visibility_max_total'] ) ) {
							$dce_visibility_max = get_option( 'dce_visibility_max', [] );
							// remove elements with no limits
					foreach ( $dce_visibility_max as $ekey => $value ) {
						if ( $ekey != $element->get_id() ) {
							$esettings = Helper::get_elementor_element_settings_by_id( $ekey );
							if ( empty( $esettings['dce_visibility_max_day'] ) && empty( $esettings['dce_visibility_max_total'] ) && empty( $esettings['dce_visibility_max_user'] ) ) {
								unset( $dce_visibility_max[ $ekey ] );
							} else {
								if ( empty( $esettings['dce_visibility_max_day'] ) ) {
									unset( $dce_visibility_max[ $ekey ]['day'] );
								}
								if ( empty( $esettings['dce_visibility_max_total'] ) ) {
									unset( $dce_visibility_max[ $ekey ]['total'] );
								}
								if ( empty( $esettings['dce_visibility_max_user'] ) ) {
									unset( $dce_visibility_max[ $ekey ]['user'] );
								}
							}
						}
					}

					if ( isset( $dce_visibility_max[ $element->get_id() ] ) ) {
						$today = date( 'Ymd' );

						if ( ! empty( $settings['dce_visibility_max_day'] ) ) {
							if ( ! empty( $dce_visibility_max[ $element->get_id() ]['day'][ $today ] ) ) {
								$dce_visibility_max_day = $dce_visibility_max[ $element->get_id() ]['day'];
								$dce_visibility_max_day[ $today ] = intval( $dce_visibility_max_day[ $today ] ) + 1;
							} else {
								$dce_visibility_max_day = [];
								$dce_visibility_max_day[ $today ] = 1;
							}
						} else {
							$dce_visibility_max_day = [];
						}
						if ( ! empty( $settings['dce_visibility_max_total'] ) ) {
							if ( isset( $dce_visibility_max[ $element->get_id() ]['total'] ) ) {
								$dce_visibility_max_total = intval( $dce_visibility_max[ $element->get_id() ]['total'] ) + 1;
							} else {
								$dce_visibility_max_total = 1;
							}
						} else {
							$dce_visibility_max_total = 0;
						}
						if ( $user_id && ! empty( $settings['dce_visibility_max_user'] ) ) {
							if ( ! empty( $dce_visibility_max[ $element->get_id() ]['user'] ) ) {
								$dce_visibility_max_user = $dce_visibility_max[ $element->get_id() ]['user'];
							} else {
								$dce_visibility_max_user = [];
							}
							$dce_visibility_max_user[ $user_id ] = $user_id;
						} else {
							$dce_visibility_max_user = [ $user_id => $user_id ];
						}
					} else {
						$dce_visibility_max_user = [ $user_id => $user_id ];
						$dce_visibility_max_day = [];
						$dce_visibility_max_total = 1;
					}
							$dce_visibility_max[ $element->get_id() ] = [
								'day' => $dce_visibility_max_day,
								'total' => $dce_visibility_max_total,
								'user' => $dce_visibility_max_user,
							];
							update_option( 'dce_visibility_max', $dce_visibility_max );
				}
			}
			if ( $settings['dce_visibility_selected'] ) {
				if ( $user_id && ! empty( $settings['dce_visibility_max_user'] ) ) {
							$dce_visibility_max_user = get_user_meta( $user_id, 'dce_visibility_max_user', true );
					if ( empty( $dce_visibility_max_user[ $element->get_id() ] ) ) {
						if ( empty( $dce_visibility_max_user ) ) {
							$dce_visibility_max_user = [];
						}
						$dce_visibility_max_user[ $element->get_id() ] = 2;
					} else {
						$dce_visibility_max_user[ $element->get_id() ]++;
					}
							update_user_meta( $user_id, 'dce_visibility_max_user', $dce_visibility_max_user );
				}
			}
		}
	}

	public function get_fallback( $settings, $element = null ) {
		if ( ! empty( $settings['dce_visibility_fallback'] ) ) {
			if ( isset( $settings['dce_visibility_fallback_type'] ) && $settings['dce_visibility_fallback_type'] == 'template' ) {
				$fallback_content = do_shortcode( '[dce-elementor-template id="' . $settings['dce_visibility_fallback_template'] . '"]' );
			} else {
				$fallback_content = $settings['dce_visibility_fallback_text'];
			}

			if ( $fallback_content && ( ! isset( $settings['dce_visibility_fallback_section'] ) || $settings['dce_visibility_fallback_section'] == 'yes' ) ) { // BUG - Fix it
				if ( $element->get_type() == 'section' ) {
							$fallback_content = '
                                    <div class="elementor-element elementor-column elementor-col-100 elementor-top-column" data-element_type="column">
                                        <div class="elementor-column-wrap elementor-element-populated">
                                            <div class="elementor-widget-wrap">
                                                <div class="elementor-element elementor-widget">
                                                    <div class="elementor-widget-container dce-visibility-fallback">'
																											. $fallback_content .
																										'</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
				}

				ob_start();
				$element->before_render();
				echo $fallback_content;
				$element->after_render();
				$fallback_content = ob_get_clean();
			}

			return $fallback_content;
		}
		return '';
	}

	public function is_hidden( $element = null, $why = false ) {
		$settings = $element->get_settings_for_display();
		if ( empty( $settings['enabled_visibility'] ) ) {
			return false;
		}
		$triggers_n = 0;
		$conditions = [];
		$triggers = [];
		$post_ID = get_the_ID(); // Current post
		if ( ! empty( $settings['dce_visibility_post_id'] ) ) {
			switch ( $settings['dce_visibility_post_id'] ) {
				case 'global':
							$post_ID = Helper::get_post_id_from_url();
					if ( ! $post_ID ) {
						$queried_object = get_queried_object();
						if ( $queried_object && is_object( $queried_object ) && get_class( $queried_object ) == 'WP_Post' ) {
							$post_ID = get_queried_object_id();
						}
					}
					break;
				case 'static':
							$post_tmp = get_post( $settings['dce_visibility_post_id_static'] );
					if ( is_object( $post_tmp ) ) {
						$post_ID = $post_tmp->ID;
					}
					break;
			}
		}

		// FORCED HIDDEN
		if ( ! empty( $settings['dce_visibility_hidden'] ) ) {
			$conditions['dce_visibility_hidden'] = __( 'Always Hidden', 'dynamic-visibility-for-elementor' );
			$triggers['dce_visibility_hidden'] = $conditions['dce_visibility_hidden'];
		} else {

			// DATETIME
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'datetime', $settings['dce_visibility_triggers'] ) ) {

				$everytimehidden = false;

				if ( $settings['dce_visibility_date_dynamic'] ) {
					if ( $settings['dce_visibility_date_dynamic_from'] && $settings['dce_visibility_date_dynamic_to'] ) {
						$triggers['date'] = __( 'Date Dynamic', 'dynamic-visibility-for-elementor' );
						$triggers['dce_visibility_date_dynamic_from'] = __( 'Date Dynamic From', 'dynamic-visibility-for-elementor' );
						$triggers['dce_visibility_date_dynamic_to'] = __( 'Date Dynamic To', 'dynamic-visibility-for-elementor' );

						// between
						$dateTo = strtotime( $settings['dce_visibility_date_dynamic_to'] );
						$dateFrom = strtotime( $settings['dce_visibility_date_dynamic_from'] );
						$triggers_n++;
						if ( current_time( 'timestamp' ) >= $dateFrom && current_time( 'timestamp' ) <= $dateTo ) {
							$conditions['date'] = __( 'Date Dynamic', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					} else {
						if ( $settings['dce_visibility_date_dynamic_from'] ) {
							$triggers['dce_visibility_date_dynamic_from'] = __( 'Date Dynamic From', 'dynamic-visibility-for-elementor' );

							$dateFrom = strtotime( $settings['dce_visibility_date_dynamic_from'] );
							$triggers_n++;
							if ( current_time( 'timestamp' ) >= $dateFrom ) {
								$conditions['dce_visibility_date_dynamic_from'] = __( 'Date Dynamic From', 'dynamic-visibility-for-elementor' );
								$everytimehidden = true;
							}
						}
						if ( $settings['dce_visibility_date_dynamic_to'] ) {
							$triggers['dce_visibility_date_dynamic_to'] = __( 'Date Dynamic To', 'dynamic-visibility-for-elementor' );

							$dateTo = strtotime( $settings['dce_visibility_date_dynamic_to'] );
							$triggers_n++;
							if ( current_time( 'timestamp' ) <= $dateTo ) {
								$conditions['dce_visibility_date_dynamic_to'] = __( 'Date Dynamic To', 'dynamic-visibility-for-elementor' );
								$everytimehidden = true;
							}
						}
					}
				} else {
					if ( $settings['dce_visibility_date_from'] && $settings['dce_visibility_date_to'] ) {
						$triggers['date'] = __( 'Date', 'dynamic-visibility-for-elementor' );
						$triggers['dce_visibility_date_from'] = __( 'Date From', 'dynamic-visibility-for-elementor' );
						$triggers['dce_visibility_date_to'] = __( 'Date To', 'dynamic-visibility-for-elementor' );

						// between
						$dateTo = strtotime( $settings['dce_visibility_date_to'] );
						$dateFrom = strtotime( $settings['dce_visibility_date_from'] );
						$triggers_n++;
						if ( current_time( 'timestamp' ) >= $dateFrom && current_time( 'timestamp' ) <= $dateTo ) {
							$conditions['date'] = __( 'Date', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					} else {
						if ( $settings['dce_visibility_date_from'] ) {
							$triggers['dce_visibility_date_from'] = __( 'Date From', 'dynamic-visibility-for-elementor' );

							$dateFrom = strtotime( $settings['dce_visibility_date_from'] );
							$triggers_n++;
							if ( current_time( 'timestamp' ) >= $dateFrom ) {
								$conditions['dce_visibility_date_from'] = __( 'Date From', 'dynamic-visibility-for-elementor' );
								$everytimehidden = true;
							}
						}
						if ( $settings['dce_visibility_date_to'] ) {
							$triggers['dce_visibility_date_to'] = __( 'Date To', 'dynamic-visibility-for-elementor' );

							$dateTo = strtotime( $settings['dce_visibility_date_to'] );
							$triggers_n++;
							if ( current_time( 'timestamp' ) <= $dateTo ) {
								$conditions['dce_visibility_date_to'] = __( 'Date To', 'dynamic-visibility-for-elementor' );
								$everytimehidden = true;
							}
						}
					}
				}

				if ( $settings['dce_visibility_period_from'] && $settings['dce_visibility_period_to'] ) {
					$triggers['period'] = __( 'Period', 'dynamic-visibility-for-elementor' );
					$triggers['dce_visibility_period_from'] = __( 'Period From', 'dynamic-visibility-for-elementor' );
					$triggers['dce_visibility_period_to'] = __( 'Period To', 'dynamic-visibility-for-elementor' );

					// between
					$triggers_n++;
					if ( date_i18n( 'm/d' ) >= $settings['dce_visibility_period_from'] && date_i18n( 'm/d' ) <= $settings['dce_visibility_period_to'] ) {
						$conditions['period'] = __( 'Period', 'dynamic-visibility-for-elementor' );
						$everytimehidden = true;
					}
				} else {
					if ( $settings['dce_visibility_period_from'] ) {
						$triggers['dce_visibility_period_from'] = __( 'Period From', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( date_i18n( 'm/d' ) >= $settings['dce_visibility_period_from'] ) {
							$conditions['dce_visibility_period_from'] = __( 'Period From', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					}
					if ( $settings['dce_visibility_period_to'] ) {
						$triggers['dce_visibility_period_to'] = __( 'Period To', 'dynamic-visibility-for-elementor' );
						$triggers_n++;
						if ( date_i18n( 'm/d' ) <= $settings['dce_visibility_period_to'] ) {
							$conditions['dce_visibility_period_to'] = __( 'Period To', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					}
				}

				if ( $settings['dce_visibility_time_week'] && ! empty( $settings['dce_visibility_time_week'] ) ) {
					$triggers['dce_visibility_time_week'] = __( 'Day of Week', 'dynamic-visibility-for-elementor' );

					$triggers_n++;
					if ( in_array( current_time( 'w' ), $settings['dce_visibility_time_week'] ) ) {
						$conditions['dce_visibility_time_week'] = __( 'Day of Week', 'dynamic-visibility-for-elementor' );
						$everytimehidden = true;
					}
				}

				if ( $settings['dce_visibility_time_from'] && $settings['dce_visibility_time_to'] ) {
					$triggers['time'] = __( 'Time', 'dynamic-visibility-for-elementor' );
					$triggers['dce_visibility_time_from'] = __( 'Time From', 'dynamic-visibility-for-elementor' );
					$triggers['dce_visibility_time_to'] = __( 'Time To', 'dynamic-visibility-for-elementor' );

					$timeFrom = $settings['dce_visibility_time_from'];
					$timeTo = ( $settings['dce_visibility_time_to'] == '00:00' ) ? '24:00' : $settings['dce_visibility_time_to'];
					$triggers_n++;
					if ( current_time( 'H:i' ) >= $timeFrom && current_time( 'H:i' ) <= $timeTo ) {
						$conditions['time'] = __( 'Time', 'dynamic-visibility-for-elementor' );
						$everytimehidden = true;
					}
				} else {
					if ( $settings['dce_visibility_time_from'] ) {
						$triggers['dce_visibility_time_from'] = __( 'Time From', 'dynamic-visibility-for-elementor' );

						$timeFrom = $settings['dce_visibility_time_from'];
						$triggers_n++;
						if ( current_time( 'H:i' ) >= $timeFrom ) {
							$conditions['dce_visibility_time_from'] = __( 'Time From', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					}
					if ( $settings['dce_visibility_time_to'] ) {
						$triggers['dce_visibility_time_to'] = __( 'Time To', 'dynamic-visibility-for-elementor' );

						$timeTo = ( $settings['dce_visibility_time_to'] == '00:00' ) ? '24:00' : $settings['dce_visibility_time_to'];
						$triggers_n++;
						if ( current_time( 'H:i' ) <= $timeTo ) {
							$conditions['dce_visibility_time_to'] = __( 'Time To', 'dynamic-visibility-for-elementor' );
							$everytimehidden = true;
						}
					}
				}
			}

			// USER & ROLES
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'user', $settings['dce_visibility_triggers'] ) ) {
				if ( ! isset( $settings['dce_visibility_everyone'] ) || ! $settings['dce_visibility_everyone'] ) {
					$everyonehidden = false;

					//roles
					if ( isset( $settings['dce_visibility_role'] ) && ! empty( $settings['dce_visibility_role'] ) ) {
						$triggers['dce_visibility_role'] = __( 'User Role', 'dynamic-visibility-for-elementor' );

						$current_user = wp_get_current_user();
						if ( $current_user && $current_user->ID ) {
							$user_roles = $current_user->roles; // An user could have multiple roles
							if ( ! is_array( $user_roles ) ) {
								$user_roles = [ $user_roles ];
							}
							if ( is_array( $settings['dce_visibility_role'] ) ) {
								$tmp_role = array_intersect( $user_roles, $settings['dce_visibility_role'] );
								$triggers_n++;
								if ( ! empty( $tmp_role ) ) {
									$conditions['dce_visibility_role'] = __( 'User Role', 'dynamic-visibility-for-elementor' );
									$everyonehidden = true;
								}
							}
						} else {
							$triggers_n++;
							if ( in_array( 'visitor', $settings['dce_visibility_role'] ) ) {
								$conditions['dce_visibility_role'] = __( 'User not logged', 'dynamic-visibility-for-elementor' );
								$everyonehidden = true;
							}
						}
					}

					// user
					if ( isset( $settings['dce_visibility_users'] ) && $settings['dce_visibility_users'] ) {
						$triggers['dce_visibility_users'] = __( 'Specific User', 'dynamic-visibility-for-elementor' );

						$users = Helper::str_to_array( ',', $settings['dce_visibility_users'] );
						$is_user = false;
						if ( ! empty( $users ) ) {
							$current_user = wp_get_current_user();
							foreach ( $users as $key => $value ) {
								if ( is_numeric( $value ) ) {
									if ( $value == $current_user->ID ) {
										$is_user = true;
									}
								}
								if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
									if ( $value == $current_user->user_email ) {
										$is_user = true;
									}
								}
								if ( $value == $current_user->user_login ) {
									$is_user = true;
								}
							}
						}
						$triggers_n++;
						if ( $is_user ) {
							$conditions['dce_visibility_users'] = __( 'Specific User', 'dynamic-visibility-for-elementor' );
							$everyonehidden = true;
						}
					}

					if ( isset( $settings['dce_visibility_can'] ) && $settings['dce_visibility_can'] ) {
						$triggers['dce_visibility_can'] = __( 'User can', 'dynamic-visibility-for-elementor' );

						$user_can = false;
						$user_id = get_current_user_id();
						if ( user_can( $user_id, $settings['dce_visibility_can'] ) ) {
							$user_can = true;
						}
						$triggers_n++;
						if ( $user_can ) {
							$conditions['dce_visibility_can'] = __( 'User can', 'dynamic-visibility-for-elementor' );
							$everyonehidden = true;
						}
					}

					if ( isset( $settings['dce_visibility_usermeta'] ) && ! empty( $settings['dce_visibility_usermeta'] ) ) {
						$triggers['dce_visibility_usermeta'] = __( 'User Field', 'dynamic-visibility-for-elementor' );

						$current_user = wp_get_current_user();
						if ( Helper::is_user_meta( $settings['dce_visibility_usermeta'] ) ) {
							$usermeta = get_user_meta( $current_user->ID, $settings['dce_visibility_usermeta'], true ); // false for visitor
						} else {
							$usermeta = $current_user->{$settings['dce_visibility_usermeta']};
						}
						$condition_result = self::condition_satisfy( $usermeta, $settings['dce_visibility_usermeta_status'], $settings['dce_visibility_usermeta_value'] );
						$triggers_n++;
						if ( $condition_result ) {
							$conditions['dce_visibility_usermeta'] = __( 'User Field', 'dynamic-visibility-for-elementor' );
						}
					}

					// GEOIP
					if ( Helper::is_plugin_active( 'geoip-detect' ) && function_exists( 'geoip_detect2_get_info_from_current_ip' ) ) {
						if ( ! empty( $settings['dce_visibility_country'] ) ) {
							$triggers['dce_visibility_country'] = __( 'Country', 'dynamic-visibility-for-elementor' );
							if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
								$geoinfo = geoip_detect2_get_info_from_current_ip();
								$triggers_n++;
								if ( in_array( $geoinfo->country->isoCode, $settings['dce_visibility_country'] ) ) {
									$conditions['dce_visibility_country'] = __( 'Country', 'dynamic-visibility-for-elementor' );
								}
							}
						}

						if ( ! empty( $settings['dce_visibility_city'] ) ) {
							$triggers['dce_visibility_country'] = __( 'City', 'dynamic-visibility-for-elementor' );
							if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
								$geoinfo = geoip_detect2_get_info_from_current_ip();
								$ucity = array_map( 'strtolower', $geoinfo->city->names );
								$scity = Helper::str_to_array( ',', $settings['dce_visibility_city'], 'strtolower' );
								$icity = array_intersect( $ucity, $scity );
								$triggers_n++;
								if ( ! empty( $icity ) ) {
									$conditions['dce_visibility_country'] = __( 'City', 'dynamic-visibility-for-elementor' );
								}
							}
						}
					}

					// referrer
					if ( isset( $settings['dce_visibility_referrer'] ) && $settings['dce_visibility_referrer'] && $settings['dce_visibility_referrer_list'] ) {
						$triggers['dce_visibility_referrer_list'] = __( 'Referer', 'dynamic-visibility-for-elementor' );

						if ( $_SERVER['HTTP_REFERER'] ) {
							$pieces = explode( '/', sanitize_text_field( $_SERVER['HTTP_REFERER'] ) );
							$referrer = parse_url( sanitize_text_field( $_SERVER['HTTP_REFERER'] ), PHP_URL_HOST );
							$referrers = explode( PHP_EOL, $settings['dce_visibility_referrer_list'] );
							$referrers = array_map( 'trim', $referrers );
							$ref_found = false;
							foreach ( $referrers as $aref ) {
								if ( $aref == $referrer || $aref == str_replace( 'www.', '', $referrer ) || $aref == $_SERVER['HTTP_REFERER'] ) {
									$ref_found = true;
								}
							}
							$triggers_n++;
							if ( $ref_found ) {
								$conditions['dce_visibility_referrer_list'] = __( 'Referer', 'dynamic-visibility-for-elementor' );
								$everyonehidden = true;
							}
						}
					}

					if ( isset( $settings['dce_visibility_ip'] ) && $settings['dce_visibility_ip'] ) {
						$triggers['dce_visibility_ip'] = __( 'Remote IP', 'dynamic-visibility-for-elementor' );

						$ips = explode( ',', $settings['dce_visibility_ip'] );
						$ips = array_map( 'trim', $ips );
						$triggers_n++;
						if ( in_array( $_SERVER['REMOTE_ADDR'], $ips ) ) {
							$conditions['dce_visibility_ip'] = __( 'Remote IP', 'dynamic-visibility-for-elementor' );
							$everyonehidden = true;
						}
					}
				}

				if ( ! empty( $settings['dce_visibility_max_user'] ) ) {
					$triggers['dce_visibility_max_user'] = __( 'Max per User', 'dynamic-visibility-for-elementor' );
					$user_id = get_current_user_id();
					if ( $user_id ) {
						$dce_visibility_max_user = get_user_meta( $user_id, 'dce_visibility_max_user', true );
						$dce_visibility_max_user_count = 0;
						if ( ! empty( $dce_visibility_max_user[ $element->get_id() ] ) ) {
							$dce_visibility_max_user_count = $dce_visibility_max_user[ $element->get_id() ];
						}
						$triggers_n++;
						if ( $settings['dce_visibility_max_user'] >= $dce_visibility_max_user_count ) {
							$conditions['dce_visibility_max_user'] = __( 'Max per User', 'dynamic-visibility-for-elementor' );
						}
					}
				}
			}

			// DEVICE
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'device', $settings['dce_visibility_triggers'] ) ) {
				if ( ! isset( $settings['dce_visibility_device'] ) || ! $settings['dce_visibility_device'] ) {
					$ahidden = false;

					// responsive
					if ( isset( $settings['dce_visibility_responsive'] ) && $settings['dce_visibility_responsive'] ) {
						$triggers['dce_visibility_responsive'] = __( 'Responsive', 'dynamic-visibility-for-elementor' );

						if ( wp_is_mobile() ) {
							$triggers_n++;
							if ( $settings['dce_visibility_responsive'] == 'mobile' ) {
								$conditions['dce_visibility_responsive'] = __( 'Responsive: is Mobile', 'dynamic-visibility-for-elementor' );
								$ahidden = true;
							}
						} else {
							$triggers_n++;
							if ( $settings['dce_visibility_responsive'] == 'desktop' ) {
								$conditions['dce_visibility_responsive'] = __( 'Responsive: is Desktop', 'dynamic-visibility-for-elementor' );
								$ahidden = true;
							}
						}
					}

					// browser
					if ( isset( $settings['dce_visibility_browser'] ) && is_array( $settings['dce_visibility_browser'] ) && ! empty( $settings['dce_visibility_browser'] ) ) {
						$triggers['dce_visibility_browser'] = __( 'Browser', 'dynamic-visibility-for-elementor' );

						$is_browser = false;
						foreach ( $settings['dce_visibility_browser'] as $browser ) {
							global $$browser;
							if ( isset( $$browser ) && $$browser ) {
								$is_browser = true;
							}
						}
						$triggers_n++;
						if ( $is_browser ) {
							$conditions['dce_visibility_browser'] = __( 'Browser', 'dynamic-visibility-for-elementor' );
							$ahidden = true;
						}
					}
				}
			}

			// POST
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'post', $settings['dce_visibility_triggers'] ) ) {
				if ( ! isset( $settings['dce_visibility_context'] ) || ! $settings['dce_visibility_context'] ) {
					$contexthidden = false;

					// cpt
					if ( isset( $settings['dce_visibility_cpt'] ) && ! empty( $settings['dce_visibility_cpt'] ) && is_array( $settings['dce_visibility_cpt'] ) ) {
						$triggers['dce_visibility_cpt'] = __( 'Post Type', 'dynamic-visibility-for-elementor' );

						$cpt = get_post_type();
						$triggers_n++;
						if ( in_array( $cpt, $settings['dce_visibility_cpt'] ) ) {
							$conditions['dce_visibility_cpt'] = __( 'Post Type', 'dynamic-visibility-for-elementor' );
							$contexthidden = true;
						}
					}

					// post
					if ( isset( $settings['dce_visibility_post'] ) && ! empty( $settings['dce_visibility_post'] ) && is_array( $settings['dce_visibility_post'] ) ) {
						$triggers['dce_visibility_post'] = __( 'Post', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( in_array( $post_ID, $settings['dce_visibility_post'] ) ) {
							$conditions['dce_visibility_post'] = __( 'Post', 'dynamic-visibility-for-elementor' );
							$contexthidden = true;
						}
					}

					if ( isset( $settings['dce_visibility_tax'] ) && $settings['dce_visibility_tax'] ) {
						$triggers['dce_visibility_tax'] = __( 'Taxonomy', 'dynamic-visibility-for-elementor' );

						$tax = get_post_taxonomies();
						$triggers_n++;
						if ( ! in_array( $settings['dce_visibility_tax'], $tax ) ) {
							$conditions['dce_visibility_tax'] = __( 'Taxonomy', 'dynamic-visibility-for-elementor' );
							$contexthidden = true;
						} else {
							$triggers['terms'] = __( 'Terms', 'dynamic-visibility-for-elementor' );

							// term
							$terms = get_the_terms( $post_ID, $settings['dce_visibility_tax'] );
							$tmp = [];
							if ( ! empty( $terms ) ) {
								if ( ! is_object( $terms ) && is_array( $terms ) ) {
									foreach ( $terms as $aterm ) {
										if ( $aterm && is_object( $aterm ) && get_class( $aterm ) == 'WP_Term' ) {
											$tmp[ $aterm->term_id ] = $aterm->term_id;
										}
									}
								}
								$terms = $tmp;
							}

							$tkey = 'dce_visibility_term_' . $settings['dce_visibility_tax'];
							if ( ! empty( $settings[ $tkey ] ) && is_array( $settings[ $tkey ] ) ) {
								if ( ! empty( $terms ) ) {
									$triggers_n++;
									if ( Helper::is_wpml_active() ) {
										// WPML Active - Retrieve terms from the current language
										foreach ( $terms as $term ) {
											$terms_current_language[] = apply_filters( 'wpml_object_id', $term, $settings['dce_visibility_tax'], true );
										}
										foreach ( $settings[$tkey] as $term ) {
											$term_searched_current_language[] = apply_filters( 'wpml_object_id', $term, $settings['dce_visibility_tax'], true );
										}
										if ( array_intersect( $terms_current_language, $term_searched_current_language ) ) {
											$conditions[ $tkey ] = __( 'Terms', 'dynamic-visibility-for-elementor' );
											$contexthidden = true;
										}
									} else {
										// WPML not activated
										if ( array_intersect( $terms, $settings[ $tkey ] ) ) {
											$conditions[ $tkey ] = __( 'Terms', 'dynamic-visibility-for-elementor' );
											$contexthidden = true;
										}
									}
								}
							} else {
								$triggers_n++;
								if ( ! empty( $terms ) ) {
									$conditions['terms'] = __( 'Terms', 'dynamic-visibility-for-elementor' );
									$contexthidden = true;
								}
							}
						}
					}

					// meta
					if ( isset( $settings['dce_visibility_meta'] ) && is_array( $settings['dce_visibility_meta'] ) && ! empty( $settings['dce_visibility_meta'] ) ) {
						$triggers['dce_visibility_meta'] = __( 'Post Metas', 'dynamic-visibility-for-elementor' );

						$post_metas = $settings['dce_visibility_meta'];
						$metafirst = true;
						$metavalued = false;
						foreach ( $post_metas as $mkey => $ameta ) {
							if ( is_author() ) {
								$author_id = get_the_author_meta( 'ID' );
								$mvalue = get_user_meta( $author_id, $ameta, true );
							} else {
								$mvalue = get_post_meta( $post_ID, $ameta, true );
								if ( is_array( $mvalue ) && empty( $mvalue ) ) {
									$mvalue = false;
								}
							}
							if ( $settings['dce_visibility_meta_operator'] ) { // AND
								if ( $metafirst && $mvalue ) {
									$metavalued = true;
								}
								if ( ! $metavalued || ! $mvalue ) {
									$metavalued = false;
								}
							} else { // OR
								if ( $metavalued || $mvalue ) {
									$metavalued = true;
								}
							}
							$metafirst = false;
						}

						$triggers_n++;
						if ( $metavalued ) {
							$conditions['dce_visibility_meta'] = __( 'Post Metas', 'dynamic-visibility-for-elementor' );
							$contexthidden = true;
						}
					}

					if ( isset( $settings['dce_visibility_field'] ) && ! empty( $settings['dce_visibility_field'] ) ) {
						$triggers['dce_visibility_field'] = __( 'Post Field', 'dynamic-visibility-for-elementor' );
						$postmeta = Helper::get_post_value( $post_ID, $settings['dce_visibility_field'] );
						$condition_result = self::condition_satisfy( $postmeta, $settings['dce_visibility_field_status'], $settings['dce_visibility_field_value'] );
						$triggers_n++;
						if ( $condition_result ) {
							$conditions['dce_visibility_field'] = __( 'Post Field', 'dynamic-visibility-for-elementor' );
						}
					}
					if ( isset( $settings['dce_visibility_root'] ) && $settings['dce_visibility_root'] ) {
						$triggers['dce_visibility_root'] = __( 'Post is Root', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( ! wp_get_post_parent_id( $post_ID ) ) {
							$conditions['dce_visibility_root'] = __( 'Post is Root', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( isset( $settings['dce_visibility_format'] ) && ! empty( $settings['dce_visibility_format'] ) ) {
						$triggers['dce_visibility_format'] = __( 'Post Format', 'dynamic-visibility-for-elementor' );

						$format = get_post_format( $post_ID ) ?: 'standard';
						$triggers_n++;
						if ( in_array( $format, $settings['dce_visibility_format'] ) ) {
							$conditions['dce_visibility_format'] = __( 'Post Format', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( isset( $settings['dce_visibility_parent'] ) && $settings['dce_visibility_parent'] ) {
						$triggers['dce_visibility_parent'] = __( 'Post is Parent', 'dynamic-visibility-for-elementor' );

						$args = [
							'post_parent' => $post_ID,
							'post_type' => get_post_type(),
							'numberposts' => -1,
							'post_status' => 'publish',
						];
						$children = get_children( $args );
						$triggers_n++;
						if ( ! empty( $children ) && count( $children ) ) {
							$conditions['dce_visibility_parent'] = __( 'Post is Parent', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( isset( $settings['dce_visibility_leaf'] ) && $settings['dce_visibility_leaf'] ) {
						$triggers['dce_visibility_leaf'] = __( 'Post is Leaf', 'dynamic-visibility-for-elementor' );

						$args = [
							'post_parent' => $post_ID,
							'post_type' => get_post_type(),
							'numberposts' => -1,
							'post_status' => 'publish',
						];
						$children = get_children( $args );
						$triggers_n++;
						if ( empty( $children ) ) {
							$conditions['dce_visibility_leaf'] = __( 'Post is Leaf', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( isset( $settings['dce_visibility_node'] ) && $settings['dce_visibility_node'] ) {
						$triggers['dce_visibility_node'] = __( 'Post is Node', 'dynamic-visibility-for-elementor' );

						if ( wp_get_post_parent_id( $post_ID ) ) {
							$args = [
								'post_parent' => $post_ID,
								'post_type' => get_post_type(),
								'numberposts' => -1,
								'post_status' => 'publish',
							];
							$children = get_children( $args );
							if ( ! empty( $children ) ) {
								$parents = get_post_ancestors( $post_ID );
								$node_level = count( $parents ) + 1;
								$triggers_n++;
								if ( empty( $settings['dce_visibility_node_level'] ) || $node_level == $settings['dce_visibility_node_level'] ) {
									$conditions['dce_visibility_node'] = __( 'Post is Node', 'dynamic-visibility-for-elementor' );
								}
							}
						}
					}

					if ( isset( $settings['dce_visibility_level'] ) && $settings['dce_visibility_level'] ) {
						$triggers['dce_visibility_level'] = __( 'Post is Node', 'dynamic-visibility-for-elementor' );

						$parents = get_post_ancestors( $post_ID );
						$node_level = count( $parents ) + 1;
						$triggers_n++;
						if ( $node_level == $settings['dce_visibility_level'] ) {
							$conditions['dce_visibility_level'] = __( 'Post has Level', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( isset( $settings['dce_visibility_child'] ) && $settings['dce_visibility_child'] ) {
						$triggers['dce_visibility_child'] = __( 'Post has Parent', 'dynamic-visibility-for-elementor' );

						if ( $post_parent_ID = wp_get_post_parent_id( $post_ID ) ) {
							$parent_ids = Helper::str_to_array( ',', $settings['dce_visibility_child_parent'] );
							$triggers_n++;
							if ( empty( $settings['dce_visibility_child_parent'] ) || in_array( $post_parent_ID, $parent_ids ) ) {
								$conditions['dce_visibility_child'] = __( 'Post has Parent', 'dynamic-visibility-for-elementor' );
							}
						}
					}

					if ( isset( $settings['dce_visibility_sibling'] ) && $settings['dce_visibility_sibling'] ) {
						$triggers['dce_visibility_sibling'] = __( 'Post has Siblings', 'dynamic-visibility-for-elementor' );

						if ( $post_parent_ID = wp_get_post_parent_id( $post_ID ) ) {
							$args = [
								'post_parent' => $post_parent_ID,
								'post_type' => get_post_type(),
								'posts_per_page' => -1,
								'post_status' => 'publish',
							];
							$children = get_children( $args );
							$triggers_n++;
							if ( ! empty( $children ) && count( $children ) > 1 ) {
								$conditions['dce_visibility_sibling'] = __( 'Post has Siblings', 'dynamic-visibility-for-elementor' );
							}
						}
					}

					if ( isset( $settings['dce_visibility_friend'] ) && $settings['dce_visibility_friend'] ) {
						$triggers['dce_visibility_friend'] = __( 'Post has Friends', 'dynamic-visibility-for-elementor' );

						$posts_ids = [];
						if ( $settings['dce_visibility_friend_term'] ) {
							$term = get_term( $settings['dce_visibility_friend_term'] );
							$terms = [ $term ];
						} else {
							$terms = wp_get_post_terms();
						}
						if ( ! empty( $terms ) ) {
							foreach ( $terms as $term ) {
								$post_args = [
									'posts_per_page' => -1,
									'post_type' => get_post_type(),
									'tax_query' => [
										[
											'taxonomy' => $term->taxonomy,
											'field' => 'term_id', // this can be 'term_id', 'slug' & 'name'
											'terms' => $term->term_id,
										],
									],
								];
								$term_posts = get_posts( $post_args );
								if ( ! empty( $term_posts ) && count( $term_posts ) > 1 ) {
									$posts_ids = wp_list_pluck( $term_posts, 'ID' );
									$triggers_n++;
									if ( in_array( $post_ID, $posts_ids ) ) {
										$conditions['dce_visibility_friend'] = __( 'Post has Friends', 'dynamic-visibility-for-elementor' );
										break;
									}
								}
							}
						}
					}
				}

				if ( isset( $settings['dce_visibility_conditional_tags_post'] ) && is_array( $settings['dce_visibility_conditional_tags_post'] ) && ! empty( $settings['dce_visibility_conditional_tags_post'] ) ) {
					$triggers['dce_visibility_conditional_tags_post'] = __( 'Conditional tags Post', 'dynamic-visibility-for-elementor' );

					$context_conditional_tags = false;
					$post_type = get_post_type();
					foreach ( $settings['dce_visibility_conditional_tags_post'] as $conditional_tags ) {
						if ( ! $context_conditional_tags ) {
							switch ( $conditional_tags ) {
								case 'is_post_type_hierarchical':
								case 'is_post_type_archive':
									if ( is_callable( $conditional_tags ) ) {
										$context_conditional_tags = call_user_func( $conditional_tags, $post_type );
									}
									break;
								case 'has_post_thumbnail':
									if ( is_callable( $conditional_tags ) ) {
										$context_conditional_tags = call_user_func( $conditional_tags, $post_ID );
									}
									break;
								default:
									if ( is_callable( $conditional_tags ) ) {
										$context_conditional_tags = call_user_func( $conditional_tags );
									}
							}
						}
					}
					$triggers_n++;
					if ( $context_conditional_tags ) {
						$conditions['dce_visibility_conditional_tags_post'] = __( 'Conditional tags Post', 'dynamic-visibility-for-elementor' );
						$contexttags = true;
					}
				}

				// specials
				if ( isset( $settings['dce_visibility_special'] ) && is_array( $settings['dce_visibility_special'] ) && ! empty( $settings['dce_visibility_special'] ) ) {
					$triggers['dce_visibility_special'] = __( 'Conditional tags Special', 'dynamic-visibility-for-elementor' );

					$context_special = false;
					foreach ( $settings['dce_visibility_special'] as $special ) {
						if ( ! $context_special ) {
							switch ( $special ) {
								default:
									if ( is_callable( $special ) ) {
										$context_special = call_user_func( $special );
									}
							}
						}
					}
					$triggers_n++;
					if ( $context_special ) {
						$conditions['dce_visibility_special'] = __( 'Conditional tags Special', 'dynamic-visibility-for-elementor' );
						$contexttags = true;
					}
				}
			}

			// CONTEXT
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'context', $settings['dce_visibility_triggers'] ) ) {
				if ( isset( $settings['dce_visibility_parameter'] ) && $settings['dce_visibility_parameter'] ) {
					$triggers['dce_visibility_parameter'] = __( 'Parameter', 'dynamic-visibility-for-elementor' );

					$my_val = null;
					switch ( $settings['dce_visibility_parameter_method'] ) {
						case 'COOKIE':
							if ( isset( $_COOKIE[ $settings['dce_visibility_parameter'] ] ) ) {
								$my_val = sanitize_text_field( $_COOKIE[ $settings['dce_visibility_parameter'] ] );
							}
							break;
						case 'SERVER':
							if ( isset( $_SERVER[ $settings['dce_visibility_parameter'] ] ) ) {
								$my_val = sanitize_text_field( $_SERVER[ $settings['dce_visibility_parameter'] ] );
							}
							break;
						case 'GET':
						case 'POST':
						case 'REQUEST':
						default:
							if ( isset( $_REQUEST[ $settings['dce_visibility_parameter'] ] ) ) {
								$my_val = sanitize_text_field( $_REQUEST[ $settings['dce_visibility_parameter'] ] );
							}
					}
					$condition_result = self::condition_satisfy( $my_val, $settings['dce_visibility_parameter_status'], $settings['dce_visibility_parameter_value'] );
					$triggers_n++;
					if ( $condition_result ) {
						$conditions['dce_visibility_parameter'] = __( 'Parameter', 'dynamic-visibility-for-elementor' );
					}
				}

				// LANGUAGES
				if ( ! empty( $settings['dce_visibility_lang'] ) ) {
					$triggers['dce_visibility_lang'] = __( 'Language', 'dynamic-visibility-for-elementor' );

					$current_language = get_locale();
					// WPML
					global $sitepress;
					if ( ! empty( $sitepress ) ) {
						$current_language = $sitepress->get_current_language(); // return lang code
					}
					// POLYLANG
					if ( Helper::is_plugin_active( 'polylang' ) && function_exists( 'pll_languages_list' ) ) {
						$current_language = pll_current_language();
					}
					// TRANSLATEPRESS
					global $TRP_LANGUAGE;
					if ( ! empty( $TRP_LANGUAGE ) ) {
						$current_language = $TRP_LANGUAGE; // return lang code
					}
					// WEGLOT
					if ( Helper::is_plugin_active( 'weglot' ) ) {
						$current_language = weglot_get_current_language();
					}
					$triggers_n++;
					if ( in_array( $current_language, $settings['dce_visibility_lang'] ) ) {
						$conditions['dce_visibility_lang'] = __( 'Language', 'dynamic-visibility-for-elementor' );
					}
				}

				if ( ! empty( $settings['dce_visibility_max_day'] ) ) {
					$triggers['dce_visibility_max_day'] = __( 'Max Day', 'dynamic-visibility-for-elementor' );
					$dce_visibility_max = get_option( 'dce_visibility_max', [] );
					$today = date( 'Ymd' );
					$triggers_n++;
					if ( isset( $dce_visibility_max[ $element->get_id() ] ) && isset( $dce_visibility_max[ $element->get_id() ]['day'] ) && isset( $dce_visibility_max[ $element->get_id() ]['day'][ $today ] ) ) {
						if ( $settings['dce_visibility_max_day'] >= $dce_visibility_max[ $element->get_id() ]['day'][ $today ] ) {
							$conditions['dce_visibility_max_day'] = __( 'Max per Day', 'dynamic-visibility-for-elementor' );
						}
					} else {
						$conditions['dce_visibility_max_day'] = __( 'Max per Day', 'dynamic-visibility-for-elementor' );
					}
				}
				if ( ! empty( $settings['dce_visibility_max_total'] ) ) {
					$triggers['dce_visibility_max_total'] = __( 'Max Total', 'dynamic-visibility-for-elementor' );
					$dce_visibility_max = get_option( 'dce_visibility_max', [] );
					$triggers_n++;
					if ( isset( $dce_visibility_max[ $element->get_id() ] ) && isset( $dce_visibility_max[ $element->get_id() ]['total'] ) ) {
						if ( $settings['dce_visibility_max_total'] >= $dce_visibility_max[ $element->get_id() ]['total'] ) {
							$conditions['dce_visibility_max_total'] = __( 'Max Total', 'dynamic-visibility-for-elementor' );
						}
					} else {
						$conditions['dce_visibility_max_total'] = __( 'Max Total', 'dynamic-visibility-for-elementor' );
					}
				}

				if ( ! empty( $settings['dce_visibility_conditional_tags_site'] ) && is_array( $settings['dce_visibility_conditional_tags_site'] ) ) {
					$triggers['dce_visibility_conditional_tags_site'] = __( 'Conditional tags Site', 'dynamic-visibility-for-elementor' );

					$context_conditional_tags = false;
					foreach ( $settings['dce_visibility_conditional_tags_site'] as $conditional_tags ) {
						if ( ! $context_conditional_tags ) {
							switch ( $conditional_tags ) {
								default:
									if ( is_callable( $conditional_tags ) ) {
										$context_conditional_tags = call_user_func( $conditional_tags );
									}
							}
						}
					}
					$triggers_n++;
					if ( $context_conditional_tags ) {
						$conditions['dce_visibility_conditional_tags_site'] = __( 'Conditional tags Site', 'dynamic-visibility-for-elementor' );
						$contexttags = true;
					}
				}
			}

			// ARCHIVE
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'archive', $settings['dce_visibility_triggers'] ) ) {
				if ( ! empty( $settings['dce_visibility_archive'] ) ) {
					$triggers['dce_visibility_archive'] = __( 'Conditional tags Archive', 'dynamic-visibility-for-elementor' );

					$context_archive = false;
					$archive = $settings['dce_visibility_archive'];
					if ( $archive ) {
						if ( ! $context_archive ) {
							switch ( $archive ) {
								case 'is_post_type_archive':
								case 'is_tax':
								case 'is_category':
								case 'is_tag':
								case 'is_author':
								case 'is_date':
								case 'is_year':
								case 'is_month':
								case 'is_day':
								case 'is_search':
									if ( is_callable( $archive ) ) {
										$context_archive = call_user_func( $archive );
									}
									break;
								default:
									$context_archive = is_archive();
							}
						}
					}
					if ( $context_archive ) {
						$context_archive_advanced = false;
						$queried_object = get_queried_object();
						switch ( $archive ) {
							case 'is_tax':
								if ( get_class( $queried_object ) == 'WP_Term' && $settings['dce_visibility_archive_tax'] && $queried_object->taxonomy == $settings['dce_visibility_archive_tax'] ) {
									if ( empty( $settings[ 'dce_visibility_archive_term_' . $settings['dce_visibility_archive_tax'] ] ) ) {
										$context_archive_advanced = true;
									} else {
										if ( in_array( $queried_object->term_id, $settings[ 'dce_visibility_archive_term_' . $settings['dce_visibility_archive_tax'] ] ) ) {
											$context_archive_advanced = true;
										}
									}
								} else {
									$context_archive_advanced = true;
								}
								break;
							case 'is_category':
								is_category();
								if ( get_class( $queried_object ) == 'WP_Term' && $queried_object->taxonomy == 'category' ) {
									if ( empty( $settings['dce_visibility_archive_term_category'] ) ) {
										$context_archive_advanced = true;
									} else {
										if ( in_array( $queried_object->term_id, $settings['dce_visibility_archive_term_category'] ) ) {
											$context_archive_advanced = true;
										}
									}
								}
								break;
							case 'is_tag':
								if ( get_class( $queried_object ) == 'WP_Term' && $queried_object->taxonomy == 'post_tag' ) {
									if ( empty( $settings['dce_visibility_archive_term_post_tag'] ) ) {
										$context_archive_advanced = true;
									} else {
										if ( in_array( $queried_object->term_id, $settings['dce_visibility_archive_term_post_tag'] ) ) {
											$context_archive_advanced = true;
										}
									}
								}
								break;
							default:
								$context_archive_advanced = true;
						}
						$triggers_n++;
						if ( $context_archive_advanced ) {
							$conditions['dce_visibility_archive'] = __( 'Archive', 'dynamic-visibility-for-elementor' );
							$contexttags = true;
						}
					}
				}

				// TERMS
				$term = get_queried_object();
				if ( $term && is_object( $term ) && get_class( $term ) == 'WP_Term' ) {

					// is parent
					if ( ! empty( $settings['dce_visibility_term_root'] ) ) {
						$triggers['dce_visibility_term_root'] = __( 'Term is Root', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( ! $term->parent ) {
							$conditions['dce_visibility_term_root'] = __( 'Term is Root', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( ! empty( $settings['dce_visibility_term_parent'] ) ) {
						$triggers['dce_visibility_term_parent'] = __( 'Term is Parent', 'dynamic-visibility-for-elementor' );

						$children = get_term_children( $term->term_id, $term->taxonomy );
						$triggers_n++;
						if ( ! empty( $children ) && count( $children ) ) {
							$conditions['dce_visibility_term_parent'] = __( 'Term is Parent', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( ! empty( $settings['dce_visibility_term_leaf'] ) ) {
						$triggers['dce_visibility_term_leaf'] = __( 'Term is Leaf', 'dynamic-visibility-for-elementor' );

						$children = get_term_children( $term->term_id, $term->taxonomy );
						$triggers_n++;
						if ( empty( $children ) ) {
							$conditions['dce_visibility_term_leaf'] = __( 'Term is Leaf', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( ! empty( $settings['dce_visibility_term_node'] ) ) {
						$triggers['dce_visibility_term_node'] = __( 'Term is Node', 'dynamic-visibility-for-elementor' );

						if ( $term->parent ) {
							$children = get_term_children( $term->term_id, $term->taxonomy );
							$triggers_n++;
							if ( ! empty( $children ) ) {
								$conditions['dce_visibility_term_node'] = __( 'Term is Node', 'dynamic-visibility-for-elementor' );
							}
						}
					}

					if ( ! empty( $settings['dce_visibility_term_child'] ) ) {
						$triggers['dce_visibility_term_child'] = __( 'Term has Parent', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( $term->parent ) {
							$conditions['dce_visibility_term_child'] = __( 'Term has Parent', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( ! empty( $settings['dce_visibility_term_sibling'] ) ) {
						$triggers['dce_visibility_term_sibling'] = __( 'Term has Siblings', 'dynamic-visibility-for-elementor' );

						$siblings = false;
						if ( $term->parent ) {
							$siblings = get_term_children( $term->parent, $term->taxonomy );
						} else {
							$args = [
								'taxonomy' => $term->taxonomy,
								'parent' => 0,
								'hide_empty' => false,
							];
							$siblings = get_terms( $args );
						}
						$triggers_n++;
						if ( ! empty( $siblings ) && count( $siblings ) > 1 ) {
							$conditions['dce_visibility_term_sibling'] = __( 'Term has Siblings', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( ! empty( $settings['dce_visibility_term_count'] ) ) {
						$triggers['dce_visibility_term_count'] = __( 'Term Posts', 'dynamic-visibility-for-elementor' );

						$triggers_n++;
						if ( $term->count ) {
							$conditions['dce_visibility_term_count'] = __( 'Term Posts', 'dynamic-visibility-for-elementor' );
						}
					}
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'dynamic_tag', $settings['dce_visibility_triggers'] ) ) {
				if ( ! empty( $settings['__dynamic__'] ) && ! empty( $settings['__dynamic__']['dce_visibility_dynamic_tag'] ) ) {
					$triggers['dce_visibility_dynamic_tag'] = __( 'Dynamic Tag', 'dynamic-visibility-for-elementor' );
					$my_val = $settings['dce_visibility_dynamic_tag'];
					$condition_result = self::condition_satisfy( $my_val, $settings['dce_visibility_dynamic_tag_status'], $settings['dce_visibility_dynamic_tag_value'] );
					$triggers_n++;
					if ( $condition_result ) {
						$conditions['dce_visibility_dynamic_tag'] = __( 'Dynamic Tag', 'dynamic-visibility-for-elementor' );
					}
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'random', $settings['dce_visibility_triggers'] ) ) {
				if ( ! empty( $settings['dce_visibility_random']['size'] ) ) {
					$triggers['dce_visibility_random'] = __( 'Random', 'dynamic-visibility-for-elementor' );
					$rand = mt_rand( 1, 100 );
					$triggers_n++;
					if ( $rand <= $settings['dce_visibility_random']['size'] ) {
						$conditions['dce_visibility_random'] = __( 'Random', 'dynamic-visibility-for-elementor' );
						$randomhidden = true;
					}
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'events', $settings['dce_visibility_triggers'] ) ) {
				if ( ! empty( $settings['dce_visibility_click'] ) ) {
					$triggers['dce_visibility_click'] = __( 'On Event', 'dynamic-visibility-for-elementor' );
				}
				if ( isset( $settings['dce_visibility_load'] ) && $settings['dce_visibility_load'] ) {
					$triggers['dce_visibility_load'] = __( 'On Page Load', 'dynamic-visibility-for-elementor' );
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'woocommerce', $settings['dce_visibility_triggers'] ) ) {
				// WOOCOMMERCE
				if ( Helper::is_woocommerce_active() ) {
					if ( ! empty( $settings['dce_visibility_woo_product_id_static'] ) ) {
						$triggers['dce_visibility_woo_product_id_static'] = __( 'Product in Cart', 'dynamic-visibility-for-elementor' );

						$product_id = $settings['dce_visibility_woo_product_id_static'];

						$product_cart_id = WC()->cart->generate_cart_id( $product_id );
						$in_cart = WC()->cart->find_product_in_cart( $product_cart_id );
						$triggers_n++;
						if ( $in_cart ) {
							$conditions['dce_visibility_woo_product_id_static'] = __( 'Product in Cart', 'dynamic-visibility-for-elementor' );
						}
					}

					if ( Helper::is_plugin_active( 'woocommerce-memberships' ) ) {
						if ( $settings['dce_visibility_woo_membership_post'] ) {
							$triggers['dce_visibility_woo_membership_post'] = __( 'Woo Membership Post', 'dynamic-visibility-for-elementor' );

							if ( function_exists( 'wc_memberships_is_user_active_or_delayed_member' ) ) {
								$user_id = get_current_user_id();
								$has_access = true;
								$rules = wc_memberships()->get_rules_instance()->get_post_content_restriction_rules( $post_ID );
								if ( ! empty( $rules ) ) {
									$has_access = false;
									if ( $user_id ) {
										foreach ( $rules as $rule ) {
											if ( wc_memberships_is_user_active_or_delayed_member( $user_id, $rule->get_membership_plan_id() ) ) {
												$has_access = true;
												break;
											}
										}
									}
								}
								if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
									$has_access = true;
								}
								$triggers_n++;
								if ( $has_access ) {
									$conditions['dce_visibility_woo_membership_post'] = __( 'Woo Membership Post', 'dynamic-visibility-for-elementor' );
								}
							}
						} else {

							//roles
							if ( isset( $settings['dce_visibility_woo_membership'] ) && ! empty( $settings['dce_visibility_woo_membership'] ) ) {
								$triggers['dce_visibility_woo_membership'] = __( 'Woo Membership', 'dynamic-visibility-for-elementor' );

								$current_user_id = get_current_user_id();
								if ( $current_user_id ) {
									$member_plans = get_posts([
										'author' => $current_user_id,
										'post_type' => 'wc_user_membership',
										'post_status' => [
											'wcm-active',
											'wcm-free_trial',
											'wcm-pending',
										],
										'posts_per_page' => -1,
									]);
									$user_members = [];
									if ( empty( $member_plans ) ) {
										// not member
										$triggers_n++;
										if ( in_array( 0, $settings['dce_visibility_woo_membership'] ) ) {
											$conditions['dce_visibility_woo_membership'] = __( 'Woo Membership', 'dynamic-visibility-for-elementor' );
										}
									} else {
										// find all user membership plan
										foreach ( $member_plans as $member ) {
											$user_members[] = $member->post_parent;
										}
										$tmp_members = array_intersect( $user_members, $settings['dce_visibility_woo_membership'] );
										$triggers_n++;
										if ( ! empty( $tmp_members ) ) {
											$conditions['dce_visibility_woo_membership'] = __( 'Woo Membership', 'dynamic-visibility-for-elementor' );
										}
									}
								}
							}
						}
					}
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'myfastapp', $settings['dce_visibility_triggers'] ) ) {
				if ( Helper::is_myfastapp_active() && 'all' !== $settings['dce_visibility_myfastapp'] ) {
					$triggers['dce_visibility_myfastapp'] = 'My FastAPP';
					$headers = getallheaders();
					$is_on_myfastapp = isset( $headers['X-Appid'] );

					if ( 'app' === $settings['dce_visibility_myfastapp'] && $is_on_myfastapp
						|| 'site' === $settings['dce_visibility_myfastapp'] && ! $is_on_myfastapp ) {
						$conditions['dce_visibility_myfastapp'] = 'My FastAPP';
					}
				}
			}

			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'custom', $settings['dce_visibility_triggers'] ) ) {
				// CUSTOM CONDITION
				if ( ! isset( $settings['dce_visibility_custom_condition'] ) || ! $settings['dce_visibility_custom_condition'] ) {
					if ( isset( $settings['dce_visibility_custom_condition_php'] ) &&
						preg_match( '/\S/', $settings['dce_visibility_custom_condition_php'] ) ) {
						$triggers['custom'] = __( 'Custom Condition', 'dynamic-visibility-for-elementor' );
						$customhidden = $this->check_custom_condition( $settings, $element->get_id() );
						$triggers_n++;
						if ( $customhidden ) {
							$conditions['custom'] = __( 'Custom Condition', 'dynamic-visibility-for-elementor' );
						}
					}
				}
			}

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$conditions = $triggers;
			}
		}

		if ( $why ) {
			return $conditions;
		}

		if ( $settings['dce_visibility_logical_connective'] === 'and' ) {
			// true only if at least one trigger set and all triggers have triggered.
			$triggered = $triggers_n && count( $conditions ) === $triggers_n;
		} else {
			$triggered = ! empty( $conditions );
		}

		$shidden = $settings['dce_visibility_selected'] == 'yes';
		// retrocompatibility for 1.4
		if ( isset( $settings['dce_visibility_user_selected'] ) && ! $settings['dce_visibility_user_selected'] ) {
			$shidden = false;
		}
		if ( isset( $settings['dce_visibility_datetime_selected'] ) && ! $settings['dce_visibility_datetime_selected'] ) {
			$shidden = false;
		}
		if ( isset( $settings['dce_visibility_custom_condition_selected'] ) && ! $settings['dce_visibility_custom_condition_selected'] ) {
			$shidden = false;
		}
		if ( isset( $settings['dce_visibility_tags_selected'] ) && ! $settings['dce_visibility_tags_selected'] ) {
			$shidden = false;
		}
		if ( isset( $settings['dce_visibility_context_selected'] ) && ! $settings['dce_visibility_context_selected'] ) {
			$shidden = false;
		}
		if ( isset( $settings['dce_visibility_device_selected'] ) && ! $settings['dce_visibility_device_selected'] ) {
			$shidden = false;
		}

		$hidden = $shidden ? ! $triggered : $triggered;

		if ( $hidden ) {
			\DynamicVisibilityForElementor\Elements::$elements_hidden[ $element->get_id() ]['triggers'] = $triggers;
			\DynamicVisibilityForElementor\Elements::$elements_hidden[ $element->get_id() ]['conditions'] = $conditions;
			\DynamicVisibilityForElementor\Elements::$elements_hidden[ $element->get_id() ]['fallback'] = $this->get_fallback( $settings, $element );
		}

		return $hidden;
	}

	protected function check_custom_condition( $settings, $eid = null ) {
		
	}

	public function print_conditions( $element, $settings = null ) {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( empty( $settings ) ) {
				$settings = $element->get_settings_for_display();
			}
			if ( $settings['dce_visibility_debug'] ) {
				$conditions = $this->is_hidden( $element, true );
				if ( ! empty( $conditions ) ) {
					echo '<a onClick="jQuery(this).next().fadeToggle(); return false;" href="#box-visibility-debug-' . $element->get_ID() . '" class="dce-btn-visibility dce-btn-visibility-debug"><i class="dce-icon-visibility fa fa fa-eye exclamation-triangle" aria-hidden="true"></i></a>';
					echo '<div id="#box-visibility-debug-' . $element->get_ID() . '" class="dce-box-visibility-debug"><ul>';
					foreach ( $conditions as $key => $value ) {
						echo '<li>';
						echo $value;
						if ( isset( $settings[ $key ] ) ) {
							echo ': ';
							if ( is_array( $settings[ $key ] ) ) {
								if ( $key == 'dce_visibility_random' ) {
									echo $settings[ $key ]['size'] . '%';
								} else {
									echo implode( ', ', $settings[ $key ] );
								}
							} else {
								echo print_r( $settings[ $key ], true );
							}
						}
						echo '</li>';
					}
					echo '</ul></div>';
				}
			}
		}
	}

	public function print_scripts( $element, $settings = null ) {
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( empty( $settings ) ) {
				$settings = $element->get_settings_for_display();
			}
			if ( empty( $settings['dce_visibility_triggers'] ) || in_array( 'events', $settings['dce_visibility_triggers'] ) ) {
				if ( $settings['dce_visibility_click'] ) {
					switch ( $settings['dce_visibility_click_show'] ) {
						case 'slide':
							$jFunction = 'slideDown';
							$jFunctionHide = 'slideUp';
							break;
						case 'fade':
							$jFunction = 'fadeIn';
							$jFunctionHide = 'fadeOut';
							break;
						default:
							$jFunction = 'removeClass("dce-visibility-element-hidden").show';
							$jFunctionHide = 'addClass("dce-visibility-element-hidden").hide';
					}
					$show = true;
					if ( $settings['dce_visibility_selected'] == 'hide' ) {
						$show = false;
						$jFunction = $jFunctionHide;
					}

					if ( $settings['dce_visibility_click_toggle'] ) {
						if ( $settings['dce_visibility_click_show'] ) {
							$jFunctionToggle = $settings['dce_visibility_click_show'] . 'Toggle';
						} else {
							$jFunctionToggle = 'toggle';
						}
						$jFunction = $jFunctionToggle;
					} else {
						if ( $show ) {
							$jFunctionToggle = $jFunctionHide;
						} else {
							$jFunctionToggle = $jFunction;
						}
					} ?>
						<script>
						jQuery(function () {
							jQuery('<?php echo $settings['dce_visibility_click']; ?>').on('<?php echo $settings['dce_visibility_event']; ?>', function () {

								<?php $css_classes = $settings['_css_classes']; ?>

								<?php if ( $settings['dce_visibility_click_other'] ) { ?>
									jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').stop();

									<?php
									$other_css_classes_without_dots = str_replace( '.', '', $settings['dce_visibility_click_other'] );
									$css_classes = '';
									if ( isset( $settings['_css_classes'] ) ) {
										$css_classes = str_replace( $other_css_classes_without_dots, '', $settings['_css_classes'] );
									}
									?>

									<?php if ( defined( 'DCE_PATH' ) ) { ?>
										// Dynamic Content for Elementor
										// Hide other elements
										<?php if ( ! empty( $css_classes ) && ! empty( $settings['_element_id'] ) ) { ?>
											// The element has got a CSS ID and a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('#<?php echo Helper::get_dynamic_value( $settings['_element_id'] ); ?>.<?php echo $css_classes; ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } elseif ( ! empty( $css_classes ) ) { ?>
											// The element has got a CSS Class and it doesn't have a CSS ID
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('.<?php echo $css_classes; ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } elseif ( ! empty( $settings['_element_id'] ) ) { ?>
											// The element has got a CSS ID and it doesn't have a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('#<?php echo Helper::get_dynamic_value( $settings['_element_id'] ); ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } else { ?>
											// The element doesn't have a CSS ID or a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('.elementor-element-<?php echo $element->get_id(); ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php }
									} else { ?>
										// Dynamic Visibility for Elementor - Free version
										// Hide other elements
										<?php if ( ! empty( $css_classes ) && ! empty( $settings['_element_id'] ) ) { ?>
											// The element has got a CSS ID and a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('#<?php echo $settings['_element_id']; ?>.<?php echo $css_classes; ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } elseif ( ! empty( $css_classes ) ) { ?>
											// The element has got a CSS Class and it doesn't have a CSS ID
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('.<?php echo $css_classes; ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } elseif ( ! empty( $settings['_element_id'] ) ) { ?>
											// The element has got a CSS ID and it doesn't have a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('#<?php echo $settings['_element_id']; ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php } else { ?>
											// The element doesn't have a CSS ID or a CSS Class
											jQuery('<?php echo $settings['dce_visibility_click_other']; ?>').not('.elementor-element-<?php echo $element->get_id(); ?>').<?php echo $jFunctionToggle; ?>(<?php echo ( $settings['dce_visibility_click_show'] ) ? '400, function() {' : ');'; ?>
										<?php }
									}
								} ?>

								<?php if ( defined( 'DCE_PATH' ) ) { ?>
									// Dynamic Content for Elementor
									<?php if ( ! empty( $settings['_css_classes'] ) && ! empty( $settings['_element_id'] ) ) { ?>
										// The element has got a CSS ID and a CSS Class
										jQuery("#<?php echo Helper::get_dynamic_value( $settings['_element_id'] ); ?>.<?php echo Helper::get_dynamic_value( $settings['_css_classes'] ); ?>")<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } elseif ( ! empty( $css_classes ) ) { ?>
										// The element has got a CSS Class and it doesn't have a CSS ID
										jQuery('.<?php echo Helper::get_dynamic_value( $settings['_css_classes'] ); ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } elseif ( ! empty( $settings['_element_id'] ) ) { ?>
										// The element has got a CSS ID and it doesn't have a CSS Class
										jQuery('#<?php echo Helper::get_dynamic_value( $settings['_element_id'] ); ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } else { ?>
										// The element doesn't have a CSS ID or a CSS Class
										jQuery('.elementor-element-<?php echo $element->get_id(); ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php }
								} else { ?>
									// Dynamic Visibility for Elementor - Free version
									<?php if ( ! empty( $settings['_css_classes'] ) && ! empty( $settings['_element_id'] ) ) { ?>
										// The element has got a CSS ID and a CSS Class
										jQuery("#<?php echo $settings['_element_id']; ?>.<?php echo $settings['_css_classes']; ?>")<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } elseif ( ! empty( $css_classes ) ) { ?>
										// The element has got a CSS Class and it doesn't have a CSS ID
										jQuery('.<?php echo $settings['_css_classes']; ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } elseif ( ! empty( $settings['_element_id'] ) ) { ?>
										// The element has got a CSS ID and it doesn't have a CSS Class
										jQuery('#<?php echo $settings['_element_id']; ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php } else { ?>
										// The element doesn't have a CSS ID or a CSS Class
										jQuery('.elementor-element-<?php echo $element->get_id(); ?>')<?php echo ( $settings['dce_visibility_click_show'] ) ? '.delay(400)' : ''; ?>.<?php echo $jFunction; ?>();
									<?php }
								} ?>

					<?php
					if ( $settings['dce_visibility_click_other'] && $settings['dce_visibility_click_show'] ) {
						echo '});';
					} ?>
							if (jQuery(this).attr('href') == '#') {
								return false;
				}
				});
				});
				</script>
					<?php
				}
				if ( $settings['dce_visibility_load'] ) {
					if ( $settings['dce_visibility_load_show'] ) {
						$jFunctionToggle = $settings['dce_visibility_load_show'] . 'Toggle';
					} else {
						$jFunctionToggle = 'toggle';
					} ?>
						<script>
						jQuery(function () {
							jQuery(window).on('load', function () {
								setTimeout(function () {
									jQuery('.elementor-element-<?php echo $element->get_id(); ?>').<?php echo $jFunctionToggle; ?>();
				}, <?php echo $settings['dce_visibility_load_delay'] ? $settings['dce_visibility_load_delay'] : '0'; ?>);
				});
				});
				</script>
					<?php
				}
			}
		}
	}
}
