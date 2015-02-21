<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.

Plugin Name: ITRO Popup Plugin
Plugin URI: http://www.itro.eu/
Description: EN - Show a perfecly centered customizable popup and a popup-system for age-restricted site and allow to insert own HTML code. IT - Visualizza un popup perfettamente centrato e personalizzabile con possibile blocco per i siti con restrizioni di eta' e permette di inserire il proprio codice HTML.
Author: ITRO Team
E-mail: support@itro.eu
Version: 4.6.2
Author URI: http://www.itro.eu/
*/

global $ITRO_VER;
$ITRO_VER = 4.62;

define('itroPath', plugins_url() . '/itro-popup/');
define('itroImages', plugins_url() . '/itro-popup/images/');

include_once ('functions/core-function.php');
include_once ('functions/database-function.php');
include_once ('functions/js-function.php');
include_once ('templates/itro-popup-template.php');
include_once ('css/itro-style-functions.php');
load_plugin_textdomain('itro-plugin', false, basename( dirname( __FILE__ ) ) . '/languages' );

global $post;

register_activation_hook( __FILE__, 'itro_init' );

function itro_admin_scripts()
{
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery-effects-highlight');
	wp_enqueue_script('jquery-effects-fade');
	wp_enqueue_script('jquery-effects-blind');
	wp_register_script( 'itro-admin-scripts', itroPath . 'scripts/itro-admin-scripts.js', array( 'jquery' ) );
	wp_enqueue_script( 'itro-admin-scripts' );
}

function itro_load_admin_styles() 
{
	wp_enqueue_style('thickbox');
	wp_enqueue_style('itro-admin-style', itroPath . 'css/itro-admin-style.css');
}

function itro_load_script()
{
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'itro-scripts', itroPath . 'scripts/itro-scripts.js', array( 'jquery' ) );
}

function itro_get_woo_shop_id()
{
	itro_update_option('woo_shop_id', get_the_id());
}

/* check current version for db update: forced with init function due register_activation_hook not working with automatic updates */
add_action( 'init','itro_check_ver');

add_action( 'woocommerce_before_shop_loop' , 'itro_get_woo_shop_id' );

add_action( 'wp_footer','itro_display_popup');
add_action( 'wp_enqueue_scripts' , 'itro_load_script' );

add_action('admin_print_scripts', 'itro_admin_scripts');
add_action('admin_print_styles', 'itro_load_admin_styles');
add_action('admin_menu', 'itro_plugin_menu');
?>