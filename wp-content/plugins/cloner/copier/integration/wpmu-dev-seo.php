<?php

if ( ! function_exists( 'copier_copy_wpmudev_seo_options' ) ) {
	add_action( 'wpmudev_copier-copy-options', 'copier_copy_wpmudev_seo_options' );
	function copier_copy_wpmudev_seo_options( $source_blog_id ) {
		delete_option( 'sitemapurl' );
		delete_option( 'newssitemapurl' );
	}
}