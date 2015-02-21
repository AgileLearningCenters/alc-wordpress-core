<?php
//uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {exit ();}
else
{
	global $wpdb;
	
	include_once ('functions/database-function.php');
	if( itro_get_option('delete_data') == 'yes' )
	{
		$preview_id = itro_get_option('preview_id'); //delete preview page
		wp_delete_post( $preview_id , true );
		
		delete_option('itro_curr_ver');
		delete_option('itro_prev_ver');
		
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "itro_plugin_option");
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "itro_plugin_field");
	}
}
?>