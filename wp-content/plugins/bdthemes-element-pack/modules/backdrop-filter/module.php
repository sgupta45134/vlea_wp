<?php

namespace ElementPack\Modules\BackdropFilter;

use Elementor\Controls_Manager;
use ElementPack\Base\Element_Pack_Module_Base;

if ( !defined('ABSPATH') ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

    public function __construct() {
        parent::__construct();
        $this->add_actions();
    }

    public function get_name() {
        return 'bdt-backdrop-filter';
    }

    public function register_controls($widget, $args) {

        $widget->add_control(
            'element_pack_backdrop_filter',
            [
                'label'         => BDTEP_CP . __( 'Backdrop Filter', 'bdthemes-element-pack' ) . BDTEP_NC,
                'type'          => Controls_Manager::POPOVER_TOGGLE,
                'return_value'  => 'yes',
                'separator'    => 'before',
                'prefix_class' => 'bdt-backdrop-filter-',
            ]
        );

        $widget->start_popover();


        $widget->add_control(
            'element_pack_bf_blur',
            [
                'label' => _x( 'Blur', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 25,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-blur: {{SIZE}}px;'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_brightness', 
            [
                'label' => _x( 'Brightness', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'ui',
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                        'step' => 10,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-brightness: {{SIZE}}%;'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_contrast',
            [
                'label' => _x( 'Contrast', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-contrast: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_grayscale',
            [
                'label' => _x( 'Grayscale', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-grayscale: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_invert',
            [
                'label' => _x( 'Invert', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-invert: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_opacity',
            [
                'label' => _x( 'Opacity', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-opacity: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_sepia',
            [
                'label' => _x( 'Sepia', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-sepia: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_saturate',
            [
                'label' => _x( 'Saturate', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-saturate: {{SIZE}};'
				],
            ]
        );

        $widget->add_control(
            'element_pack_bf_hue_rotate',
            [
                'label' => _x( 'Hue Rotate', 'bdthemes-element-pack' ),
                'type' => Controls_Manager::SLIDER,
                
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'selectors' => [
					'{{WRAPPER}}' => '--ep-backdrop-filter-hue-rotate: {{SIZE}}deg;'
				],
            ]
        );

        $widget->end_popover();

        $widget->add_control(
			'ep_backdrop_filter_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'This feature will not work in the Firefox browser untill you enable browser compatibility so please %1s look here %2s', 'bdthemes-element-pack' ), '<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter#Browser_compatibility" target="_blank">', '</a>' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
			]
		);

    }

    protected function add_actions() {

        add_action('elementor/element/column/section_style/before_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/common/_section_background/before_section_end', [$this, 'register_controls'], 10, 2);

    }
}