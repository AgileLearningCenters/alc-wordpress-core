<?php
/* plugin name: BuddyPress user account type lite
 * Plugin URI: http://wpbpshop.com/buddypress-user-account-type-pro
 * Description: Categories you buddypress users and manage
 * Author: Rimon Habib
 * Version: 2.4.6
 * Author URI:  http://wpbpshop.com
 */

define('BUAT_ROOT',dirname(__FILE__).'/');
define('BUAT_INC',BUAT_ROOT.'include/');
define('BUAT_LIB',BUAT_ROOT.'lib/');
define('BUAT_TEMPLATE',BUAT_ROOT.'templates/');
define('BUAT_DIR',basename(dirname(__FILE__)));

register_activation_hook( __FILE__,'buat_activate');
register_deactivation_hook( __FILE__,'buat_deactivate');

function buat_activate() { buat_reset_settings(); }
function buat_deactivate() { }

/*
 * Check if buddypress is installed or not
 */
function buat_checker() {
    if(!is_plugin_active('buddypress/bp-loader.php')):
        echo '<div class="error"><p>';
        echo __('You must need to install and active <b><a href="'.site_url().'/wp-admin/plugin-install.php?tab=search&type=term&s=buddypress&plugin-search-input=Search+Plugins">
        Buddypress</strong></a> to use <strong>Buddypress User Account Type lite </b> plugin','buat');
        echo '</p></div>';
    endif;
}
add_action('admin_notices', 'buat_checker');

/*
 * Loads all BuddyPress User Account type PRO files only if BuddyPress is installed
*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('buddypress/bp-loader.php')):

require_once (BUAT_INC.'buat-ajax.php');
require_once (BUAT_INC.'buat-functions.php');
require_once (BUAT_INC.'buat-hooks.php');

// BUAT custom
if( file_exists( WP_PLUGIN_DIR.'/buat-custom.php'   ))
    require_once ( WP_PLUGIN_DIR.'/buat-custom.php' );

// Including admin settings files
if(is_admin()) {
    require_once (BUAT_INC.'admin/buat-options.class.php');
    require_once (BUAT_INC.'admin/buat-admin-pages.php');   
}

function buat_script_loader(){
    wp_enqueue_script(
		'buat-admin-js',
		plugins_url('/lib/js/admin.js', __FILE__),
                array('jquery')
	);
 
}
add_action('wp_enqueue_scripts', 'buat_script_loader');
add_action('admin_enqueue_scripts','buat_script_loader');

function buat_style_loader(){
    wp_register_style( 'buat-style', plugins_url('/lib/css/style.css', __FILE__) );
    wp_enqueue_style( 'buat-style' );
}
add_action( 'wp_enqueue_scripts', 'buat_style_loader' );
add_action('admin_enqueue_scripts','buat_style_loader');
endif;  
?>