<?php
/**
 * Plugin Name: WP Login Timeout Settings
 * Plugin URI: http://wordpress.org/plugins/wp-login-timeout-settings/
 * Description: Configure WordPress Login Timeout through UI (User Interface).
 * Author: Yslo
 * Text Domain: wp-login-timeout-settings
 * Domain Path: /languages
 * Version: 1.1.3
 * Author URI: http://profiles.wordpress.org/yslo/
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WP_Login_Timeout_Settings
{
	// Define version
	const VERSION = '1.1.3';

	var $wp_login_timeout_options;
	var $wp_login_timeout_admin_page;
	var $capabilities_list;
	var $user_level;

	function __construct()
	{
		$this->wp_login_timeout_options = get_option('wp_login_timeout_options');

		$this->user_level = array('level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'level_7', 'level_8', 'level_9', 'level_10');		

		$roles = get_role('administrator');
		$roles = $roles->capabilities;
		$roles = array_keys($roles);

		foreach($roles as $capabilitie)
		{
			if(!in_array($capabilitie, $this->user_level)) {
				$this->capabilities_list[] = $capabilitie;
			}
		}

		sort($this->capabilities_list);


		// Default install settings
		register_activation_hook(__FILE__, array(&$this, 'wp_login_timeout_settings_install'));
		
		// Languages
		load_plugin_textdomain('wp-login-timeout-settings', false, 'wp-login-timeout-settings/languages');
		
		add_filter('auth_cookie_expiration', array(&$this, 'login_timeout_filter'), 99, 3);

		add_action('init', array(&$this, 'wp_login_timeout_settings_init'));
		add_action('admin_init', array(&$this, 'wp_login_timeout_settings_admin_init'));	
	}

	
	function wp_login_timeout_settings_install()
	{	
		if($this->wp_login_timeout_options === false)
		{
			$wp_login_timeout_options = array(
				'default_timeout' => 2,
				'default_timeout_unit', 3600,
				'rememberme_timeout' => 14,
				'rememberme_timeout_unit', 3600,
				'selected_capabilities_timeout' => 'edit_posts',
				'capabilities_default_timeout' => 2,
				'capabilities_timeout_unit', 3600,
				'capabilities_rememberme_timeout' => 14,
				'capabilities_rememberme_timeout_unit', 3600,				
				'version' => self::VERSION
			);
		}
		else if(!isset($this->wp_login_timeout_options['version']) || $this->wp_login_timeout_options['version'] < self::VERSION) {
			
			// Update 1.1.0 and +		
			if( version_compare($wpus_options['version'], '1.1.0', '<') ) {
//				$wp_login_timeout_options['auto_core_update_send_email'] = 1;
			}
			
			$wp_login_timeout_options['version'] = self::VERSION;
		}

		update_option('wp_login_timeout_options', $wp_login_timeout_options);
	}


	function login_timeout_filter($seconds, $user_id, $remember)
	{
		if(isset($this->wp_login_timeout_options['activate_capabilities_timeout']) && $this->wp_login_timeout_options['activate_capabilities_timeout'] == 1 && user_can($user_id, $this->wp_login_timeout_options['selected_capabilities_timeout']))
		{
			//if "remember me" is checked;
			if ($remember)
			{
				//WP defaults to 2 weeks;
				$expiration = $this->wp_login_timeout_options['capabilities_rememberme_timeout'] * $this->wp_login_timeout_options['capabilities_rememberme_timeout_unit'];
			} else {
				//WP defaults to 48 hrs/2 days;
				$expiration = $this->wp_login_timeout_options['capabilities_timeout'] * $this->wp_login_timeout_options['capabilities_timeout_unit'];
			}
		}
		else {
			if ($remember)
			{
				//WP defaults to 2 weeks;
				$expiration = $this->wp_login_timeout_options['rememberme_timeout'] * $this->wp_login_timeout_options['rememberme_timeout_unit'];
			} else {
				//WP defaults to 48 hrs/2 days;
				$expiration = $this->wp_login_timeout_options['default_timeout'] * $this->wp_login_timeout_options['default_timeout_unit'];
			}
		}
		
	
		//http://en.wikipedia.org/wiki/Year_2038_problem
		if (PHP_INT_MAX - time() < $expiration)
		{
			//Fix to a little bit earlier!
			$expiration =  PHP_INT_MAX - time() - 5;
		}
	
		return $expiration;
	}


	function add_action_link($links, $file)
	{
		static $this_plugin;
		
		if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=' . $this_plugin . '">' . __('Settings', 'wp-login-timeout-settings') . '</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	// Init settings menu
	function wp_login_timeout_settings_init()
	{
		add_action('admin_menu', array(&$this, 'register_login_timeout_settings_menu_page'));

		// Give the plugin a settings link in the plugin overview
		add_filter('plugin_action_links', array(&$this, 'add_action_link'), 10, 2);
	}
	
	function register_login_timeout_settings_menu_page(){
		$this->wp_login_timeout_admin_page = add_options_page(__('Login timeout', 'wp-login-timeout-settings'), __('Login timeout', 'wp-login-timeout-settings'), 'manage_options', __FILE__, array(&$this, 'wp_login_timeout_settings_menu_page'));
		add_action('load-'.$this->wp_login_timeout_admin_page, array(&$this, 'wp_login_timeout_add_help_tab'));
	}

	function wp_login_timeout_settings_menu_page()
	{
		wp_enqueue_style('wp-login-timeout-settings', plugins_url( 'css/style.css', __FILE__ ), array(), self::VERSION);
		?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e('Login timeout', 'wp-login-timeout-settings'); ?></h2>
		<form action="options.php" method="post">
		<?php settings_fields('wp_login_timeout_options'); ?>
		<?php do_settings_sections('wp_login_timeout_options_sections'); ?>
		<?php submit_button(); ?>
		</form>
		</div>
		<?php
	}

	function wp_login_timeout_add_help_tab()
	{
		$screen = get_current_screen();

		if ($screen->id != $this->wp_login_timeout_admin_page)
			return;

		$screen->add_help_tab( array(
			'id'	=> 'wpus_help_login_timeout_tab',
			'title'	=> __('Authentication cookies', 'wp-login-timeout-settings'),
			'content'	=> '<p><ul><li>' . __('By default, the authentication cookies remembering is 2 days. When "Remember me" is set, the cookies will be kept for 14 days. (see <a href="http://codex.wordpress.org/Function_Reference/wp_set_auth_cookie" target="_blank">Function Reference/wp set auth cookie in Wordpress Codex</a>)', 'wp-login-timeout-settings') . '</li>'
				. '</ul>'
				. '</p>',
		));
			
		$screen->set_help_sidebar(
			'<p><strong>'
			. __('For more information:', 'wp-login-timeout-settings')
			. '</strong></p>'
			. '<p>'
			. '<a href="http://wordpress.org/plugins/wp-login-timeout-settings/" target="_blank">' . __('Visit plugin page', 'wp-login-timeout-settings') . '</a>'
			. '</p>');
	}

	function wp_login_timeout_settings_admin_init()
	{
		register_setting('wp_login_timeout_options', 'wp_login_timeout_options', array(&$this, 'wp_login_timeout_options'));
		
		add_settings_section('wp_login_timeout_default_options', __('Timeout settings', 'wp-login-timeout-settings'), array(&$this, 'wp_login_timeout_section_text'), 'wp_login_timeout_options_sections');
		add_settings_field('wp_default_timeout', __('Default timeout (default: 2 days)', 'wp-login-timeout-settings'),	array(&$this, 'wp_default_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_timeout_default_options');
		add_settings_field('wp_remember_me_timeout', __('Remember me timeout (default: 14 days)', 'wp-login-timeout-settings'),	array(&$this, 'wp_remember_me_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_timeout_default_options');
		
		add_settings_section('wp_login_capabilities_timeout_options', __('Special timeout settings based on user\'s capability', 'wp-login-timeout-settings'), array(&$this, 'wp_login_timeout_capabilities_section_text'), 'wp_login_timeout_options_sections');
		add_settings_field('wp_activate_capabilities_timeout', __('Activate special timeout settings', 'wp-login-timeout-settings'),	array(&$this, 'wp_activate_capabilities_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_capabilities_timeout_options');
		add_settings_field('wp_select_capabilities_timeout', __('Select user capabilitie activating the special timeout (default: edit_posts)', 'wp-login-timeout-settings'),	array(&$this, 'wp_select_capabilities_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_capabilities_timeout_options');		
		add_settings_field('wp_capabilities_timeout', __('Special timeout (default: 2 days)', 'wp-login-timeout-settings'),	array(&$this, 'wp_capabilities_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_capabilities_timeout_options');
		add_settings_field('wp_remember_me_capabilities_timeout', __('Special Remember me timeout (default: 14 days)', 'wp-login-timeout-settings'),	array(&$this, 'wp_capabilities_remember_me_timeout_input'), 'wp_login_timeout_options_sections', 'wp_login_capabilities_timeout_options');

	}
	
	function wp_login_timeout_section_text(){
		_e('By default, the authentication cookies remembering is 2 days. When "Remember me" is set, the cookies will be kept for 14 days. This panel allows you to change these settings.', 'wp-login-timeout-settings');
	}
	
	function wp_default_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['default_timeout']) ? $options['default_timeout'] : 2;
		$option_value_unit = isset($options['default_timeout_unit']) ? $options['default_timeout_unit'] : 3600;
		echo '<input name="wp_login_timeout_options[default_timeout]" type="text" value="'. $option_value .'">';
		echo '<select name="wp_login_timeout_options[default_timeout_unit]">';
		echo '<option value="60"' . selected( $option_value_unit, 60, false ) . '>' . __('minute(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="3600"' . selected( $option_value_unit, 3600, false ) . '>' . __('hour(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="86400"' . selected( $option_value_unit, 86400, false ) . '>' . __('day(s)', 'wp-login-timeout-settings') . '</option>';
		echo '</select>';
	}
	
	function wp_remember_me_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['rememberme_timeout']) ? $options['rememberme_timeout'] : 14;
		$option_value_unit = isset($options['rememberme_timeout_unit']) ? $options['rememberme_timeout_unit'] : 3600;		
		echo '<input name="wp_login_timeout_options[rememberme_timeout]" type="text" value="'. $option_value .'">';
		echo '<select name="wp_login_timeout_options[rememberme_timeout_unit]">';
		echo '<option value="60"' . selected( $option_value_unit, 60, false ) . '>' . __('minute(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="3600"' . selected( $option_value_unit, 3600, false ) . '>' . __('hour(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="86400"' . selected( $option_value_unit, 86400, false ) . '>' . __('day(s)', 'wp-login-timeout-settings') . '</option>';
		echo '</select>';
	}
	
	function wp_login_timeout_capabilities_section_text()
	{
		_e('You can set a different login timeout to Users with a specific capability.', 'wp-login-timeout-settings');
		echo '<br>';
		_e('eg. : You can set longer/shorter login timeout to Administrators using "edit_theme_options" capability. (see <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Roles and Capabilities in Wordpress Codex</a>)', 'wp-login-timeout-settings');		
	}
	
	function wp_activate_capabilities_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['activate_capabilities_timeout']) ? $options['activate_capabilities_timeout'] : 0;
		echo '<input type="checkbox" name="wp_login_timeout_options[activate_capabilities_timeout]" value="1" '.checked( $option_value, 1, false ).' />';
	}

	function wp_select_capabilities_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['selected_capabilities_timeout']) ? $options['selected_capabilities_timeout'] : 0;


		echo '<select name="wp_login_timeout_options[selected_capabilities_timeout]">';	
		
		foreach($this->capabilities_list as $capabilitie)
		{
			if(!in_array($capabilitie, $this->user_level)) {
				echo '<option value="' . $capabilitie . '"' . selected( $option_value, $capabilitie, false ) . '>' . $capabilitie . '</option>';
			}
		}

;
		echo '</select>';
	}
	
	function wp_capabilities_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['capabilities_timeout']) ? $options['capabilities_timeout'] : 2;
		$option_value_unit = isset($options['capabilities_timeout_unit']) ? $options['capabilities_timeout_unit'] : 3600;
		echo '<input name="wp_login_timeout_options[capabilities_timeout]" type="text" value="'. $option_value .'">';
		echo '<select name="wp_login_timeout_options[capabilities_timeout_unit]">';
		echo '<option value="60"' . selected( $option_value_unit, 60, false ) . '>' . __('minute(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="3600"' . selected( $option_value_unit, 3600, false ) . '>' . __('hour(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="86400"' . selected( $option_value_unit, 86400, false ) . '>' . __('day(s)', 'wp-login-timeout-settings') . '</option>';
		echo '</select>';
	}
	
	function wp_capabilities_remember_me_timeout_input()
	{
		$options = $this->wp_login_timeout_options;
		$option_value = isset($options['capabilities_rememberme_timeout']) ? $options['capabilities_rememberme_timeout'] : 14;
		$option_value_unit = isset($options['capabilities_rememberme_timeout_unit']) ? $options['capabilities_rememberme_timeout_unit'] : 3600;		
		echo '<input name="wp_login_timeout_options[capabilities_rememberme_timeout]" type="text" value="'. $option_value .'">';
		echo '<select name="wp_login_timeout_options[capabilities_rememberme_timeout_unit]">';
		echo '<option value="60"' . selected( $option_value_unit, 60, false ) . '>' . __('minute(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="3600"' . selected( $option_value_unit, 3600, false ) . '>' . __('hour(s)', 'wp-login-timeout-settings') . '</option>';
		echo '<option value="86400"' . selected( $option_value_unit, 86400, false ) . '>' . __('day(s)', 'wp-login-timeout-settings') . '</option>';
		echo '</select>';
	}

	function wp_login_timeout_options($input)
	{
		$valid = array();
		
		$valid['default_timeout'] = (empty($input['default_timeout']) || intval($input['default_timeout']) == false || intval($input['default_timeout']) < 1) ? 2 : intval($input['default_timeout']);
		$valid['default_timeout_unit'] = (empty($input['default_timeout']) || empty($input['default_timeout_unit']) || intval($input['default_timeout_unit'] || intval($input['default_timeout']) < 1) == false) ? 86400 : intval($input['default_timeout_unit']);
		$valid['rememberme_timeout'] = (empty($input['rememberme_timeout']) || intval($input['rememberme_timeout']) == false || intval($input['rememberme_timeout']) < 1) ? 14 : intval($input['rememberme_timeout']);
		$valid['rememberme_timeout_unit'] = (empty($input['rememberme_timeout']) || empty($input['rememberme_timeout_unit']) || intval($input['rememberme_timeout_unit']) == false|| intval($input['rememberme_timeout']) < 1) ? 86400 : intval($input['rememberme_timeout_unit']);

		if(isset($input['activate_capabilities_timeout']) && filter_var($input['activate_capabilities_timeout'], FILTER_VALIDATE_BOOLEAN))
			$valid['activate_capabilities_timeout'] = $input['activate_capabilities_timeout'];

		$valid['capabilities_timeout'] = (empty($input['capabilities_timeout']) || intval($input['capabilities_timeout']) == false || intval($input['capabilities_timeout']) < 1) ? 2 : intval($input['capabilities_timeout']);
		
		$valid['selected_capabilities_timeout'] = in_array($input['selected_capabilities_timeout'], $this->capabilities_list) ? $input['selected_capabilities_timeout'] : 'edit_posts';
		
		$valid['capabilities_timeout_unit'] = (empty($input['capabilities_timeout']) || empty($input['capabilities_timeout_unit']) || intval($input['capabilities_timeout_unit'] || intval($input['capabilities_timeout']) < 1) == false) ? 86400 : intval($input['capabilities_timeout_unit']);
		$valid['capabilities_rememberme_timeout'] = (empty($input['capabilities_rememberme_timeout']) || intval($input['capabilities_rememberme_timeout']) == false || intval($input['capabilities_rememberme_timeout']) < 1) ? 14 : intval($input['capabilities_rememberme_timeout']);
		$valid['capabilities_rememberme_timeout_unit'] = (empty($input['capabilities_rememberme_timeout']) || empty($input['capabilities_rememberme_timeout_unit']) || intval($input['capabilities_rememberme_timeout_unit']) == false|| intval($input['capabilities_rememberme_timeout']) < 1) ? 86400 : intval($input['capabilities_rememberme_timeout_unit']);

		$valid['version'] = self::VERSION;
		
		return $valid;
	}
}

$wp_login_timeout_settings = new WP_Login_Timeout_Settings();
