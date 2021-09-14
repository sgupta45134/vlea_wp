<?php
namespace ElementPack\Modules\UserLogin\Skins;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;

use Elementor\Skin_Base as Elementor_Skin_Base;
use ElementPack\Element_Pack_Loader;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Dropdown extends Elementor_Skin_Base {

	protected function _register_controls_actions() {
		parent::_register_controls_actions();
		
		add_action( 'elementor/element/bdt-user-login/section_forms_additional_options/before_section_start', [ $this, 'register_dropdown_button_controls' ] );
	}

	public function get_id() {
		return 'bdt-dropdown';
	}

	public function get_title() {
		return __( 'Dropdown', 'bdthemes-element-pack' );
	}

	public function register_dropdown_button_controls() {

		$this->start_controls_section(
			'section_dropdown_button',
			[
				'label' => esc_html__( 'Dropdown Button', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dropdown_button_text',
			[
				'label'   => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Log In', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dropdown_btn_icon_only_on_mobile',
			[
				'label' => esc_html__( 'Show Icon Only on Mobile', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'dropdown_button_size',
			[
				'label'   => esc_html__( 'Size', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => element_pack_button_sizes(),
			]
		);

		$this->add_responsive_control(
			'dropdown_button_align',
			[
				'label' => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->add_control(
			'user_login_dropdown_icon',
			[
				'label'       => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::ICONS,
				'fa4compatibility' => 'dropdown_button_icon',
			]
		);

		$this->add_control(
			'dropdown_button_icon_align',
			[
				'label'   => esc_html__( 'Icon Position', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left'  => esc_html__( 'Before', 'bdthemes-element-pack' ),
					'right' => esc_html__( 'After', 'bdthemes-element-pack' ),
				],
				'condition' => [
					$this->get_control_id( 'user_login_dropdown_icon[value]!' ) => '',
				],
			]
		);

		$this->add_control(
			'dropdown_button_icon_indent',
			[
				'label'   => esc_html__( 'Icon Spacing', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 8,
				],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'condition' => [
					$this->get_control_id( 'user_login_dropdown_icon[value]!' ) => '',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-button-dropdown .bdt-button-dropdown-icon.elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-button-dropdown .bdt-button-dropdown-icon.elementor-align-icon-left'  => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->parent->get_settings();
		$id       = 'bdt-user-login-dropdown-' . $this->parent->get_id();
		$current_url     = remove_query_arg( 'fake_arg' );
		$button_size = $this->get_instance_value('dropdown_button_size');
		$button_animation = $this->get_instance_value('dropdown_button_animation');

		if ( is_user_logged_in() && ! Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			
			$this->parent->add_render_attribute(
				[
					'dropdown-button' => [
						'class' => [
							'elementor-button',
							'bdt-button-dropdown',
							'elementor-size-' . esc_attr($button_size) ,
                            $button_animation ? 'elementor-animation-' . esc_attr($button_animation) : ''

						],
						'href' => '#',
					]
				]
			);

			
			
			?>
			<?php $current_user = wp_get_current_user(); ?>
			<div class="bdt-user-login bdt-user-login-skin-dropdown">
			<?php if ($settings['show_logged_in_content']) : ?>
				<a <?php echo $this->parent->get_render_attribute_string('dropdown-button'); ?>>

                    <span class="bdt-user-name bdt-visible@l">
                        <?php if ( $settings['show_logged_in_message'] ) : ?>
                            <?php if ( $settings['logged_in_custom_message'] and $settings['custom_labels'] ) : ?>
                                <?php echo esc_html($settings['logged_in_custom_message']); ?>
                            <?php else : ?>
                                <?php esc_html_e( 'Hi', 'bdthemes-element-pack' ); ?>,
                            <?php endif; ?>
                        <?php endif; ?>

						<?php if ( $settings['show_user_name'] ) : ?>
                        	<?php echo esc_html($current_user->display_name); ?>
						<?php endif; ?>
                    </span>

					
					<span class="bdt-user-login-button-avatar<?php echo ( 'yes' == $settings['show_avatar_in_button'] ) ? '' : ' bdt-hidden@l'; ?>">
                        <?php echo get_avatar( $current_user->user_email, 32 ); ?>
                    </span>
					
				</a>
				
				<?php $this->parent->user_dropdown_menu(); ?>
				
				<?php else : ?>
					<?php
                        $logout_url = $current_url;
                        if( isset($settings['redirect_after_logOut'])
                            && !empty($settings['redirect_logOut_url']['url'])
                        ){
                            $logout_url = $settings['redirect_logOut_url']['url'];
                        }
                    ?>
					<a class="bdt-logout-button bdt-button bdt-button-primary" href="<?php echo wp_logout_url( $logout_url ); ?>" class="bdt-ul-logout-menu">
						<?php echo esc_html($settings['logout_text']); ?>
					</a>
					<?php endif; ?>
				</div>
			<?php

			return;
		} else {

			$dropdown_offset = $settings['dropdown_offset'];
			$this->parent->add_render_attribute(
				[
					'dropdown-settings' => [
						'data-bdt-dropdown' => [
							wp_json_encode(array_filter([
								"mode"   => $settings["dropdown_mode"],
								"pos"    => $settings["dropdown_position"],
								"offset" => $dropdown_offset["size"]
							]))
						]
					]
				]
			);

			$this->parent->add_render_attribute( 'dropdown-settings', 'class', 'bdt-dropdown' );
		}

		$this->parent->form_fields_render_attributes();

		$this->parent->add_render_attribute(
			[
				'dropdown-button-settings' => [
					'class' => [
						'elementor-button',
						'bdt-button-dropdown',
						'elementor-size-' . esc_attr($button_size),
						$button_animation ? 'elementor-animation-' . esc_attr($button_animation) : ''

					],
					'href' => 'javascript:void(0)',
				]
			]
		);

		?>
		<div class="bdt-user-login bdt-user-login-skin-dropdown">
			<a <?php echo $this->parent->get_render_attribute_string('dropdown-button-settings'); ?>>
				<?php $this->render_text(); ?>
			</a>

			<div <?php echo $this->parent->get_render_attribute_string('dropdown-settings'); ?>>

				<div class="elementor-form-fields-wrapper bdt-text-left">
					<?php $this->parent->user_login_form(); ?>
					<?php $this->parent->social_login(); ?>
				</div>

			</div>
		</div>
		<?php
	}

	protected function render_text() {
		$settings = $this->parent->get_settings_for_display();

		$this->parent->add_render_attribute('button-icon', 'class', ['bdt-button-dropdown-icon', 'elementor-button-icon', 'elementor-align-icon-' . esc_attr($this->get_instance_value('dropdown_button_icon_align'))]);

		if ( is_user_logged_in() && ! Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			$button_text = esc_html__( 'Logout', 'bdthemes-element-pack' );
		} else {
			$button_text = $this->get_instance_value('dropdown_button_text');
		}

		if ( ! isset( $settings['dropdown_button_icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
			// add old default
			$settings['dropdown_button_icon'] = 'fas fa-user';
		}

		$migrated  = isset( $settings['__fa4_migrated']['user_login_dropdown_icon'] );
		$is_new    = empty( $settings['dropdown_button_icon'] ) && Icons_Manager::is_migration_allowed();

		$user_login_dropdown_icon = $this->get_instance_value('user_login_dropdown_icon');

		$icon_visible = $this->get_instance_value('dropdown_btn_icon_only_on_mobile');
		
		?>
		<span class="elementor-button-content-wrapper">
			<?php if ( ! empty( $user_login_dropdown_icon['value'] ) ) : ?>

				<span <?php echo $this->parent->get_render_attribute_string('button-icon'); ?>>

					<?php if ( $is_new || $migrated ) :
					Icons_Manager::render_icon( (array) $user_login_dropdown_icon, [ 'aria-hidden' => 'true', 'class' => 'fa-fw' ] );
					else : ?>
						<i class="<?php echo esc_attr( $settings['dropdown_button_icon'] ); ?>" aria-hidden="true"></i>
					<?php endif; ?>

				</span>

			<?php else : ?>

                <?php if ($icon_visible) : ?>
				<?php $this->parent->add_render_attribute('button-icon', 'class', [ 'bdt-hidden@l' ]); ?>
				<span <?php echo $this->parent->get_render_attribute_string('button-icon'); ?>>
					<i class="ep-lock" aria-hidden="true"></i>
				</span>
				<?php endif; ?>

			<?php endif; ?>

            <?php $text_visible = ( $this->get_instance_value('dropdown_btn_icon_only_on_mobile') ) ? ' bdt-visible@l' : ''; ?>

			<span class="elementor-button-text<?php echo esc_attr($text_visible); ?>">
				<?php echo esc_html($button_text); ?>
			</span>
		</span>
		<?php
	}
}

