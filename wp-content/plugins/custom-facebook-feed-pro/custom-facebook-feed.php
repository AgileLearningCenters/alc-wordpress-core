<?php 
/*
Plugin Name: Custom Facebook Feed Pro Personal
Plugin URI: http://smashballoon.com/custom-facebook-feed
Description: Add a completely customizable Facebook feed to your WordPress site
Version: 2.6.8.1
Author: Smash Balloon
Author URI: http://smashballoon.com/
*/
/*
Copyright 2016  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


if ( function_exists('display_cff') ){
    wp_die( "Please deactivate the free version of the Custom Facebook Feed plugin before activating this version.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
} else {
    include dirname( __FILE__ ) .'/cff-init.php';
}
define( 'CFFVER', '2.6.8.1' );
define( 'WPW_SL_STORE_URL', 'http://smashballoon.com/' );
define( 'WPW_SL_ITEM_NAME', 'Custom Facebook Feed WordPress Plugin Personal' ); //*!*Update Plugin Name at top of file*!*
// The ID of the product. Used for renewals
$cff_download_id = 210; //210, 299, 300, 13384

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}
function cff_plugin_updates() {
    // retrieve our license key from the DB
    $cff_license_key = trim( get_option( 'cff_license_key' ) );
    // setup the updater
    $edd_updater = new EDD_SL_Plugin_Updater( WPW_SL_STORE_URL, __FILE__, array( 
            'version'   => CFFVER,           // current version number
            'license'   => $cff_license_key,        // license key (used get_option above to retrieve from DB)
            'item_name' => WPW_SL_ITEM_NAME,    // name of this plugin
            'author'    => 'Smash Balloon'      // author of this plugin
        )
    );
}
add_action( 'admin_init', 'cff_plugin_updates', 0 );

//Run function on plugin activate
function cff_pro_activate() {
    $options = get_option('cff_style_settings');
    $options[ 'cff_show_links_type' ] = true;
    $options[ 'cff_show_event_type' ] = true;
    $options[ 'cff_show_video_type' ] = true;
    $options[ 'cff_show_photos_type' ] = true;
    $options[ 'cff_show_status_type' ] = true;
    $options[ 'cff_show_albums_type' ] = true;
    // Show all parts of the feed by default on activation
    $options[ 'cff_show_author' ] = true;
    $options[ 'cff_show_text' ] = true;
    $options[ 'cff_show_desc' ] = true;
    $options[ 'cff_show_shared_links' ] = true;
    $options[ 'cff_show_date' ] = true;
    $options[ 'cff_show_media' ] = true;
    $options[ 'cff_show_event_title' ] = true;
    $options[ 'cff_show_event_details' ] = true;
    $options[ 'cff_show_meta' ] = true;
    $options[ 'cff_show_link' ] = true;
    $options[ 'cff_show_like_box' ] = true;
    $options[ 'cff_show_facebook_link' ] = true;
    $options[ 'cff_show_facebook_share' ] = true;
    $options[ 'cff_event_title_link' ] = true;
    update_option( 'cff_style_settings', $options );

    //Run cron twice daily when plugin is first activated for new users
    wp_schedule_event(time(), 'twicedaily', 'cff_cron_job');
}
register_activation_hook( __FILE__, 'cff_pro_activate' );

function cff_pro_deactivate() {
    wp_clear_scheduled_hook('cff_cron_job');
}
register_deactivation_hook(__FILE__, 'cff_pro_deactivate');


//Uninstall
function cff_pro_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    //If the user is preserving the settings then don't delete them
    $cff_preserve_settings = get_option('cff_preserve_settings');
    if($cff_preserve_settings) return;

    //Settings
    delete_option( 'cff_show_access_token' );
    delete_option( 'cff_access_token' );
    delete_option( 'cff_page_id' );
    delete_option( 'cff_page_type' );
    delete_option( 'cff_num_show' );
    delete_option( 'cff_post_limit' );
    delete_option( 'cff_show_others' );
    delete_option( 'cff_cache_time' );
    delete_option( 'cff_cache_time_unit' );
    delete_option( 'cff_locale' );
    delete_option( 'cff_ajax' );
    delete_option( 'cff_preserve_settings' );
    delete_option('cff_extensions_status');

    //Style & Layout
    delete_option( 'cff_title_length' );
    delete_option( 'cff_body_length' );
    delete_option( 'cff_style_settings' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license = trim( get_option( 'cff_license_key' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license, 
        'item_name' => urlencode( WPW_SL_ITEM_NAME ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, WPW_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status' );
    delete_option( 'cff_license_key' );
}
register_uninstall_hook( __FILE__, 'cff_pro_uninstall' );

?>