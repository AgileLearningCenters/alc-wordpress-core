<?php
/*
Plugin Name: Agile Learning Centers Post Type
Plugin URI: http://drew.agilelearningcenters.com/alc-post-type
Description: Declares a plugin that will create a custom post type displaying ALCs
Version: 0.1
Author: Drew Hornbein
Author URI: http://drew.agilelearningcenters.com
License: GPLv2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Register Custom Post Type
function alc_learning_center_post_type() {

  $labels = array(
    'name'                  => _x( 'ALCs', 'Post Type General Name', 'alc_text' ),
    'singular_name'         => _x( 'ALC', 'Post Type Singular Name', 'alc_text' ),
    'menu_name'             => __( 'ALC', 'alc_text' ),
    'name_admin_bar'        => __( 'ALC', 'alc_text' ),
    'archives'              => __( 'ALC List', 'alc_text' ),
    'parent_item_colon'     => __( 'Parent ALC:', 'alc_text' ),
    'all_items'             => __( 'All ALCs', 'alc_text' ),
    'add_new_item'          => __( 'Add New ALC', 'alc_text' ),
    'add_new'               => __( 'New ALC', 'alc_text' ),
    'new_item'              => __( 'New ALC', 'alc_text' ),
    'edit_item'             => __( 'Edit ALC', 'alc_text' ),
    'update_item'           => __( 'Update ALC', 'alc_text' ),
    'view_item'             => __( 'View ALC', 'alc_text' ),
    'search_items'          => __( 'Search ALCs', 'alc_text' ),
    'not_found'             => __( 'ALC not found', 'alc_text' ),
    'not_found_in_trash'    => __( 'ALC not found in Trash', 'alc_text' ),
    'featured_image'        => __( 'ALC logo', 'alc_text' ),
    'set_featured_image'    => __( 'Set logo', 'alc_text' ),
    'remove_featured_image' => __( 'Remove logo', 'alc_text' ),
    'use_featured_image'    => __( 'Use as logo', 'alc_text' ),
    'insert_into_item'      => __( 'Insert into ALC', 'alc_text' ),
    'uploaded_to_this_item' => __( 'Uploaded to this ALC', 'alc_text' ),
    'items_list'            => __( 'ALC list', 'alc_text' ),
    'items_list_navigation' => __( 'ALC list navigation', 'alc_text' ),
    'filter_items_list'     => __( 'Filter ALC list', 'alc_text' ),
  );
  $rewrite = array(
    'slug'                  => 'alc',
    'with_front'            => true,
    'pages'                 => true,
    'feeds'                 => true,
  );
  $args = array(
    'label'                 => __( 'ALCs', 'alc_text' ),
    'description'           => __( 'Agile Learning Center', 'alc_text' ),
    'labels'                => $labels,
    'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions', ),
    'taxonomies'            => array( 'alc_type' ),
    'hierarchical'          => true,
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 5,
    'menu_icon'             => 'dashicons-admin-multisite',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => false,
    'can_export'            => true,
    'has_archive'           => 'alcs',
    'exclude_from_search'   => false,
    'publicly_queryable'    => true,
    'rewrite'               => $rewrite,
    'capability_type'       => 'page',
  );
  register_post_type( 'alc', $args );

}

add_action( 'init', 'alc_learning_center_post_type', 0 );

require 'alc-post-type-metabox.php';
require 'alc-post-type-taxonomy.php';
