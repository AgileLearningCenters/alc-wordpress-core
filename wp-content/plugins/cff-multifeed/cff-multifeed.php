<?php 
/*
Plugin Name: Custom Facebook Feed Pro - Multifeed
Plugin URI: http://smashballoon.com/extensions/multifeed/
Description: Adds the ability to display posts from multiple Facebook pages or groups in one feed.
Version: 1.0
Author: Smash Balloon
Author URI: http://smashballoon.com/
License: GPLv2 or later
*/
/* 
Copyright 2014  Smash Balloon LLC (email : hey@smashballoon.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define( 'SB_ITEM_NAME_MULTIFEED', 'Multifeed Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}
// retrieve our license key from the DB
$license_key_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );
// setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( 'http://smashballoon.com/', __FILE__, array(
        'version'   => '1.0',           			// current version number
        'license'   => $license_key_multifeed,		// license key (used get_option above to retrieve from DB)
        'item_name' => SB_ITEM_NAME_MULTIFEED, 		// name of this plugin
        'author'    => 'Smash Balloon'      		// author of this plugin
    )
);

//Include admin
include dirname( __FILE__ ) .'/cff-multifeed-admin.php';


//Multifeed Extension
function cff_multifeed_ids($page_id){
	$cff_multifeed_ids = explode(",", str_replace(' ', '', $page_id) );
	
	//Send it back
	return array_filter($cff_multifeed_ids);
}


//Uninstall
function cff_multifeed_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    //Settings
    delete_option( 'cff_date_from' );
    delete_option( 'cff_date_until' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license_key_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_multifeed, 
        'item_name' => urlencode( SB_ITEM_NAME_FEATURED ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_multifeed' );
    delete_option( 'cff_license_key_multifeed' );
}
register_uninstall_hook( __FILE__, 'cff_multifeed_uninstall' );

?>