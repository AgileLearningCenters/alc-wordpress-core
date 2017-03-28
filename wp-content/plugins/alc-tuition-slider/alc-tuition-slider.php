<?php
/*
Plugin Name: ALC Tuition Slider
Plugin URI: http://drew.agilelearningcenters.org/plugins
Description: Display tuition slider
Author: Drew Hornbein
Version: 0.1
Author URI: http://drew.agilelearningcenters.org
*/


function alc_slider_scripts() {
  wp_enqueue_style( 'alc-tuition-slider-css', plugins_url( 'alc-tuition-slider.min.css' , __FILE__ ), array(), '1.0.1' );
  wp_register_script( 'alc-tuition-slider-js', plugins_url( 'alc-tuition-slider.min.js' , __FILE__ ), array(), '1.0.1', true );
}
add_action( 'wp_enqueue_scripts', 'alc_slider_scripts' );

function alc_slider_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'min_fee' => 3700,
        'max_fee' => 10000,
        'low_income' => 50000,
        'high_income' => 100000,
        'max_income' => 120000,
        'min_income' => 30000,
	'suggestion_title' => 'Recommended tuition:'
    ), $atts );
    
    wp_enqueue_script( 'alc-tuition-slider-css' );
    wp_enqueue_script( 'alc-tuition-slider-js' );

    $out = '<div class="wrap-slider">';
    $out .= '  <div id="slider"
data-min_fee="'     . $a['min_fee'] . '"
data-max_fee="'     . $a['max_fee'] . '"
data-low_income="'  . $a['low_income'] . '"
data-high_income="' . $a['high_income'] . '"
data-max_income="'  . $a['max_income'] . '"
data-min_income="'  . $a['min_income'] . '"></div>';
    
    $out .= '</div>';
    $out .= '<div id="tuition"><p>' . $a['suggestion_title'] . '</p>';
    $out .= '<h1 class="tuition-suggestion"></h1>';
    $out .= '</div>';
    
    return $out;
}

add_shortcode( 'tuition_slider', 'alc_slider_shortcode' );

?>
