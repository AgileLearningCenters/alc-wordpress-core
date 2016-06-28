<?php

// This functionality isn't prime time yet
function alc_post_status() {

  $args = array(
    'active' => array(
      'label'                     => _x( 'Active', 'Status General Name', 'alc_text' ),
      'label_count'               => _n_noop( 'Active (%s)',  'Active (%s)', 'alc_text' ), 
      'public'                    => true,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'exclude_from_search'       => false,
    ),
    'info' => array(
      'label'                     => _x( 'Need Info', 'Status General Name', 'alc_text' ),
      'label_count'               => _n_noop( 'Need Info (%s)',  'Need Info (%s)', 'alc_text' ), 
      'public'                    => true,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'exclude_from_search'       => false,
    )
  );
  register_post_status( 'alc_status_active', $args['active'] );
  register_post_status( 'alc_status_active', $args['info'] );

}
add_action( 'init', 'alc_post_status', 0 );