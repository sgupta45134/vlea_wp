<?php
    
    ep_add_shortcode([
        'id'             => 'rating',
        'callback'       => 'ep_shortcode_rating',
        'name'           => __('Rating', 'bdthemes-element-pack'),
        'type'           => 'single',
        'atts'           => [
            'class' => [
                'type'    => 'extra_css_class',
                'name'    => __('Extra CSS class', 'bdthemes-element-pack'),
                'desc'    => __('Additional CSS class name(s) separated by space(s)', 'bdthemes-element-pack'),
                'default' => '',
            ],
            'number' => [
                'type'    => 'text',
                'default' => 5,
            ],
        ],
        'desc'           => __('Show Rating', 'bdthemes-element-pack'),
    ]);
    
    function ep_shortcode_rating($atts = null) {
        
        $atts = shortcode_atts(array('class' => '', 'score' => 5), $atts, 'rating');
        $ratingValue = explode(".",$atts['score']);

        $firstVal = $ratingValue[0];
       
        if( $ratingValue[0]<5 ){
           $secondVal = $ratingValue[1]; 
           $secondVal = ($secondVal<5) ? 0 : 5;
           $score = $firstVal.'-'.$secondVal;
        }else{
            $score = $firstVal;
        }
        
        
        $output = '<span class="epsc-rating epsc-rating-'.$score.' ' . Element_Pack_Shortcodes::ep_get_css_class($atts) . '">';
        $output .= '<span class="epsc-rating-item"><i class="epsc-star" aria-hidden="true"></i></span>
            <span class="epsc-rating-item"><i class="epsc-star" aria-hidden="true"></i></span>
            <span class="epsc-rating-item"><i class="epsc-star" aria-hidden="true"></i></span>
            <span class="epsc-rating-item"><i class="epsc-star" aria-hidden="true"></i></span>
            <span class="epsc-rating-item"><i class="epsc-star" aria-hidden="true"></i></span>';
        $output .= '</span>';
        
        wp_enqueue_style('element-pack-font');
        return $output;

    }
?>
