<?php
/*
Plugin Name: Ultimate Facebook
Plugin URI: http://premium.wpmudev.org/project/ultimate-facebook
Description: Easy Facebook integration: share your blog posts, autopost to your wall, login and registration integration, BuddyPress profiles support and more. Please, configure the plugin first.
Version: 2.7.2
Text Domain: wdfb
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
WDP ID: 228

Copyright 2009-2011 Incsub (http://incsub.com)
Author - Ve Bailovity (Incsub)
Contributor - Umesh Kumar
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define ('WDFB_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)), true);
define ('WDFB_PROTOCOL', ( is_ssl() ? 'https://' : 'http://'), true);
define ('WDFB_PLUGIN_CORE_URL', plugins_url(), true);
define ('WDFB_PLUGIN_CORE_BASENAME', plugin_basename(__FILE__), true);
if (!defined('WDFB_MEMBERSHIP_INSTALLED')) define ('WDFB_MEMBERSHIP_INSTALLED', (defined('MEMBERSHIP_MASTER_ADMIN') && defined('MEMBERSHIP_SETACTIVATORAS_ADMIN')), true);

//Setup proper paths/URLs and load text domains
if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDFB_PLUGIN_LOCATION', 'mu-plugins', true);
	define ('WDFB_PLUGIN_BASE_DIR', WPMU_PLUGIN_DIR, true);
	define ('WDFB_PLUGIN_URL', apply_filters('wdfb-core-plugin_url', str_replace('http://', WDFB_PROTOCOL, WPMU_PLUGIN_URL)), true);
	$textdomain_handler = 'load_muplugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . WDFB_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('WDFB_PLUGIN_LOCATION', 'subfolder-plugins', true);
	define ('WDFB_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WDFB_PLUGIN_SELF_DIRNAME, true);
	define ('WDFB_PLUGIN_URL', apply_filters('wdfb-core-plugin_url', str_replace('http://', WDFB_PROTOCOL, WP_PLUGIN_URL) . '/' . WDFB_PLUGIN_SELF_DIRNAME), true);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDFB_PLUGIN_LOCATION', 'plugins', true);
	define ('WDFB_PLUGIN_BASE_DIR', WP_PLUGIN_DIR, true);
	define ('WDFB_PLUGIN_URL', apply_filters('wdfb-core-plugin_url', str_replace('http://', WDFB_PROTOCOL, WP_PLUGIN_URL)), true);
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	// No textdomain is loaded because we can't determine the plugin location.
	// No point in trying to add textdomain to string and/or localizing it.
	wp_die(__('There was an issue determining where Facebook plugin is installed. Please reinstall.'));
}
$textdomain_handler('wdfb', false, WDFB_PLUGIN_SELF_DIRNAME . '/languages/');


/**
 * Dashboard permissions widget function.
 */
function wdfb_dashboard_permissions_widget () {
	echo '<div class="wdfb_perms_root" style="display:none">' .
		'<p class="wdfb_perms_granted">' .
			'<span class="wdfb_message">' . __('You already granted extended permissions', 'wdfb') . '</span> ' .
		'</p>' .
		'<p class="wdfb_perms_not_granted">' .
			'<a href="#" class="wdfb_grant_perms" data-wdfb_locale="' . wdfb_get_locale() . '" data-wdfb_perms="' . Wdfb_Permissions::get_permissions() . '">' . __('Grant extended permissions', 'wdfb') . '</a>' .
		'</p>' .
	'</div>';
	echo '<script type="text/javascript" src="' . WDFB_PLUGIN_URL . '/js/check_permissions.js"></script>';
}
function wdfb_add_dashboard_permissions_widget () {
	wp_add_dashboard_widget('wdfb_dashboard_permissions_widget', 'Facebook Permissions', 'wdfb_dashboard_permissions_widget');
}

/**
 * Dashboard BuddyPress/WordPress profile fill-up widget function.
 */
function wdfb_dashboard_profile_widget () {
	$profile = apply_filters('wdfb-profile_name', '<em>' . get_bloginfo('name') . '</em>');//defined('BP_VERSION') ? "BuddyPress" : "WordPress";
	echo '<a href="#" class="wdfb_fill_profile">' . sprintf(__('Fill my %s profile with Facebook data', 'wdfb'), $profile) . '</a>';
	echo '<script type="text/javascript">(function ($) { $(function () { $(".wdfb_fill_profile").click(function () { var $me = $(this); var oldHtml = $me.html(); try {var url = _wdfb_ajaxurl;} catch (e) { var url = ajaxurl; } $me.html("Please, wait... <img src=\"' . WDFB_PLUGIN_URL . '/img/waiting.gif\">"); $.post(url, {"action": "wdfb_populate_profile"}, function (data) { $me.html(oldHtml); }); return false; }); }); })(jQuery);</script>';
}
function wdfb_add_dashboard_profile_widget () {
	$profile =  apply_filters('wdfb-profile_name', '<em>' . get_bloginfo('name') . '</em>');//defined('BP_VERSION') ? "BuddyPress" : "WordPress";
	wp_add_dashboard_widget('wdfb_dashboard_profile_widget', "My {$profile} profile", 'wdfb_dashboard_profile_widget');
}

/*
// Deprecated
if (file_exists(WDFB_PLUGIN_BASE_DIR . '/lib/external/wpmudev-dash-notification.php')) {
	require_once WDFB_PLUGIN_BASE_DIR . '/lib/external/wpmudev-dash-notification.php';
}
*/
if (!class_exists('Facebook')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/external/facebook.php');
}
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/wdfb_utilities.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/wdfb_transients_api.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_permissions.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_options_registry.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_marker_replacer.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_comments_importer.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_model.php');
require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_error_log.php');


require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_installer.php');
Wdfb_Installer::check();


// Require and initialize widgets
$data = Wdfb_OptionsRegistry::get_instance();
if ($data->get_option('wdfb_widget_pack', 'albums_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_albums.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetAlbums');"));
}
if ($data->get_option('wdfb_widget_pack', 'events_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_events.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetEvents');"));
}
if ($data->get_option('wdfb_widget_pack', 'facepile_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_facepile.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetFacepile');"));
}
if ($data->get_option('wdfb_widget_pack', 'likebox_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_likebox.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetLikebox');"));
}
if ($data->get_option('wdfb_widget_pack', 'recommendations_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_recommendations.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetRecommendations');"));
}
if ($data->get_option('wdfb_widget_pack', 'connect_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_connect.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetConnect');"));
}
if ($data->get_option('wdfb_widget_pack', 'activityfeed_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_activityfeed.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetActivityFeed');"));
}
if ($data->get_option('wdfb_widget_pack', 'recent_comments_allowed')) {
	require_once (WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_widget_recent_comments.php');
	add_action('widgets_init', create_function('', "register_widget('Wdfb_WidgetRecentComments');"));
}
if ($data->get_option('wdfb_widget_pack', 'dashboard_permissions_allowed')) {
	add_action('wp_dashboard_setup', 'wdfb_add_dashboard_permissions_widget' );
	add_action('wp_dashboard_setup', 'wdfb_add_dashboard_profile_widget' );
}




/**
 * Schedule cron jobs for comments import.
 */
function wdfb_comment_import () {
	$data = Wdfb_OptionsRegistry::get_instance();
	if ( ! $data->get_option( 'wdfb_comments', 'import_fb_comments' ) ) {
		return;
	} // Don't import comments
	Wdfb_CommentsImporter::serve();
}

add_action( 'wdfb_import_comments', 'wdfb_comment_import' ); //array($importer, 'serve'));
if ( ! wp_next_scheduled( 'wdfb_import_comments' ) ) {
	wp_schedule_event( time() + 600, 'hourly', 'wdfb_import_comments' );
}

define( "WDFB_CORE_IS_ADMIN", ( is_admin() || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ), true );

function _wdfb_initialize() {
	// Include the metabox abstraction
	require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_metabox.php' );
	$og = new Wdfb_Metabox_OpenGraph;

	if ( apply_filters( 'wdfb-core-is_admin', WDFB_CORE_IS_ADMIN ) ) {
		require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_admin_help.php' );
		require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_admin_form_renderer.php' );
		require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_admin_pages.php' );
		require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_tutorial.php' );
		Wdfb_Tutorial::serve();
		Wdfb_AdminPages::serve();
	} else {
		require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_public_pages.php' );
		Wdfb_PublicPages::serve();
	}
	require_once( WDFB_PLUGIN_BASE_DIR . '/lib/class_wdfb_universal_worker.php' );
	Wdfb_UniversalWorker::serve();
}
add_action('plugins_loaded', '_wdfb_initialize');

if (is_admin() && file_exists(WDFB_PLUGIN_BASE_DIR . '/lib/dash-notice/wpmudev-dash-notification.php')) {
	// Dashboard notification
	global $wpmudev_notices;
	if (!is_array($wpmudev_notices)) $wpmudev_notices = array();
	$wpmudev_notices[] = array(
		'id' => 228,
		'name' => 'Ultimate Facebook',
		'screens' => array(
			'toplevel_page_wdfb',
			'toplevel_page_wdfb-network',
			'facebook_page_wdfb_widgets',
			'facebook_page_wdfb_widgets-network',
			'facebook_page_wdfb_shortcodes',
			'facebook_page_wdfb_shortcodes-network',
			'facebook_page_wdfb_error_log',
		),
	);
	require_once WDFB_PLUGIN_BASE_DIR . '/lib/dash-notice/wpmudev-dash-notification.php';
}