<?php
/**
 * @package Agile CRM API
 * @version 1.0
 */
/*
 Plugin Name: Agile CRM
Plugin URI: https://www.agilecrm.com
Description: This plugin will provide Agile CRM JS API to your wordpress
Version: 1.0
Author URI: https://www.agilecrm.com
*/

add_action( 'admin_menu', 'add_admin_menu' );

// Add Admin Menu
function add_admin_menu(){
	add_menu_page( 'Agile CRM Settings', 'Agile CRM', 'manage_options', 'agile_options_page', 'agile_options_page', plugins_url('images/agile_menu.png', __FILE__), '100.4');
}

function agile_options_page(){
	include(sprintf("%s/templates/agile.php", dirname(__FILE__)));
}

add_action( 'admin_init', 'agile_admin_init' );

function agile_admin_init() {

	register_setting( 'agile-settings-group', 'agile-domain-setting' );
	register_setting( 'agile-settings-group', 'agile-key-setting' );
	add_settings_section( 'agile-section-one', '', '', 'agile-plugin' );
	add_settings_field( 'agile-domain-field', 'Enter Domain Name', 'agile_domain_callback', 'agile-plugin', 'agile-section-one' );
	add_settings_field( 'agile-key-field', 'Enter API Key', 'agile_key_callback', 'agile-plugin', 'agile-section-one' );
}

function agile_domain_callback() {
	$setting = esc_attr( get_option( 'agile-domain-setting' ) );
	echo "<div'><span style='padding:3px; margin:0px; border: 1px solid #dfdfdf; border-right: 0px; background-color:#eee;'>https://</span><input type='text' name='agile-domain-setting' style='width:100px; margin:0px; border-radius: 0px;' value='$setting' /><span style='margin:0px; padding: 3px; border: 1px solid #dfdfdf; background-color:#eee; border-left: 0px;'>.agilecrm.com</div></span><br><small>If you are using abc.agilecrm.com, enter abc</small>";
}

function agile_key_callback() {
	$setting = esc_attr( get_option( 'agile-key-setting' ) );
	echo "<input type='text' name='agile-key-setting' style='width:250px;' value='$setting' /><br><small>For instructions to find your API key, please click <a href='https://github.com/agilecrm/javascript-api#setting-api--analytics' target='_blank'>here</a></small>";
}

function agile_script(){
	$domain = get_option('agile-domain-setting');
	$key = get_option('agile-key-setting');
	if(isset($domain) && $domain != NULL && $domain != '' && isset($key) && $key != NULL && $key != '')

{ ?>
<script
	src='https://<?php echo $domain ?>.agilecrm.com/stats/min/agile-min.js'></script>

<script>
		_agile.set_account('<?php echo $key ?>','<?php echo $domain ?>');
		_agile.track_page_view();
		_agile_execute_web_rules();
	</script>
<?php
global $current_user;
get_currentuserinfo();
$email = $current_user->user_email;
if(isset($email) && $email != NULL && $email != ''){?>
<script>_agile.set_email('<?php echo $email ?>');</script>
<?php 
}
}
}
add_action('wp_footer', 'agile_script');
?>