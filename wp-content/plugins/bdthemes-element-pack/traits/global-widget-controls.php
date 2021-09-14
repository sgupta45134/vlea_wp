<?php
	namespace ElementPack\Traits;
	
	use Elementor\Controls_Manager;
	
	use ElementPack\Modules\QueryControl\Controls\Group_Control_Posts;
	
	defined( 'ABSPATH' ) || die();
	
	trait Global_Widget_Controls {
		
		protected function register_query_controls() {
			
			$this->start_controls_section(
				'section_post_query',
				[
					'label' => __( 'Query (Deprecated)', 'bdthemes-element-pack' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				]
			);
			
			$this->add_group_control(
				Group_Control_Posts::get_type(),
				[
					'name'  => 'posts',
					'label' => __( 'Posts', 'bdthemes-element-pack' ),
				]
			);
			
			$this->add_control(
				'advanced',
				[
					'label' => __( 'Advanced', 'bdthemes-element-pack' ),
					'type'  => Controls_Manager::HEADING,
				]
			);
			
			$this->add_control(
				'orderby',
				[
					'label'   => __( 'Order By', 'bdthemes-element-pack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'post_date',
					'options' => [
						'post_date'  => __( 'Date', 'bdthemes-element-pack' ),
						'post_title' => __( 'Title', 'bdthemes-element-pack' ),
						'menu_order' => __( 'Menu Order', 'bdthemes-element-pack' ),
						'rand'       => __( 'Random', 'bdthemes-element-pack' ),
					],
				]
			);
			
			$this->add_control(
				'order',
				[
					'label'   => __( 'Order', 'bdthemes-element-pack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc'  => __( 'ASC', 'bdthemes-element-pack' ),
						'desc' => __( 'DESC', 'bdthemes-element-pack' ),
					],
				]
			);
			
			
			$this->add_control(
				'offset',
				[
					'label'     => esc_html__( 'Offset', 'bdthemes-element-pack' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 0,
					'condition' => [
						'posts_post_type!' => 'by_id',
					],
				]
			);
			
			$this->end_controls_section();
		}
	}