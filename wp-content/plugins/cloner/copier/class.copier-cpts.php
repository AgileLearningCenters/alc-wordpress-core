<?php

// Include the parent class
include_once( 'class.copier-post-types.php' );

/**
 * Copy Posts from one blog to another
 */
if ( ! class_exists( 'Site_Copier_Posts' ) ) {
	class Site_Copier_CPTs extends Site_Copier_Post_Types {

		public function __construct( $source_blog_id, $template, $args = array(), $user_id = 0 ) {
			global $wpdb;

			parent::__construct( $source_blog_id, $template, $args, $user_id );

			// Get all posts types except menus, attachments, pages and posts
			$exclude_post_types = '("' . implode( '","', array( 'page', 'post', 'attachment', 'nav_menu_item', 'revision' ) ) . '")';

			switch_to_blog( $this->source_blog_id );
			$post_types = $wpdb->get_col( "SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_type NOT IN $exclude_post_types" );
			restore_current_blog();

			if ( ! empty( $post_types ) )
				$this->type = $post_types;
		}

		public function get_default_args() {
			return array(
				'update_date' => false
			);
		}

	}
}