<?php
/**
* WP Login Timeout Settings Uninstall
*
* UninstallingWP Login Timeout Settings delete options.
*/

// If uninstall not called from Wordpress exit 
if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

delete_option('wp_login_timeout_options');
