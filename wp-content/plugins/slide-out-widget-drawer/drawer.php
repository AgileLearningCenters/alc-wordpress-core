<?php
/*
   Plugin Name: Slide Out Widget Drawer
   Plugin URI: http://wordpress.org/extend/plugins/slide-out-widget-drawer/
   Version: 0.1
   Author: Drew Hornbein
   Description: A full height slide out widget area
   Text Domain: slide-out-widget-drawer
   License: GPLv3
  */

/*
    "WordPress Plugin Template" Copyright (C) 2015 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

function drawer_scripts() {
 
    //wp_enqueue_script('jquery');
 
    wp_register_script( 'drawer-script', plugins_url( 'drawer.js', __FILE__ ) , array('jquery'),'',true  );
    wp_register_style( 'drawer-css', plugins_url( 'drawer.css', __FILE__ ) ,'','', 'screen' );
 
    wp_enqueue_script( 'drawer-script' );
    wp_enqueue_style( 'drawer-css' );
 
}
 
add_action( 'wp_enqueue_scripts', 'drawer_scripts' );

function drawer_widget() {

  register_sidebar( array(
    'name'          => 'Slide Out Sidebar',
    'id'            => 'slide-out-sidebar',
    'before_widget' => '<div class="slide-out-widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="slide-out-widget-title">',
    'after_title'   => '</h2>',
  ) );

}
add_action( 'widgets_init', 'drawer_widget' );

function drawer_html() {
 if ( is_active_sidebar( 'slide-out-sidebar' ) ) : ?>
    <div id="slide-out-sidebar" class="slide-out-widget-wrapper closed" role="complementary" data-default="More">
      <?php dynamic_sidebar( 'slide-out-sidebar' ); ?>
    </div><!-- #Slide Out Widget -->
  <?php endif;
}

add_action( 'wp_footer', 'drawer_html' );