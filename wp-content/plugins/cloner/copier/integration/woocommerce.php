<?php


add_filter( 'wpmudev_copier-process_row', 'copier_woocommerce_remap_termmeta', 10, 3 );
function copier_woocommerce_remap_termmeta( $row, $dest_table, $source_blog_id ) {
	global $wpdb;

	if ( ! function_exists( 'WC' ) )
		return $row;

	if ( $dest_table != $wpdb->prefix . 'woocommerce_termmeta' )
		return $row;

	$mapped_terms = get_transient( 'copier_woocommerce_terms' );
	if ( ! $mapped_terms )
		return $row;

	$old_term_id = $row['woocommerce_term_id'];
	if ( array_key_exists( $old_term_id, $mapped_terms ) )
		$row['woocommerce_term_id'] = $mapped_terms[ $old_term_id ];

	return $row;
}

add_action( 'wpmudev_copier-copy-terms', 'copier_woocommerce_save_mapped_terms', 10, 4 );
function copier_woocommerce_save_mapped_terms( $user_id, $source_blog_id, $template, $mapped_terms ) {
	if ( ! function_exists( 'WC' ) )
		return;

	set_transient( 'copier_woocommerce_terms', $mapped_terms, 3600 ); //Let's save for 60 minutes
}

add_action( 'wpmudev_copier-copy-after_copying', 'copier_woocommerce_delete_transient' );
function copier_woocommerce_delete_transient() {
	delete_transient( 'copier_woocommerce_terms' );
}