<?php

class Wdfb_AdminHelp {

	private $_help;
	private $_site_tips;
	private $_network_tips;

	private function __construct () {
		if (!class_exists('WpmuDev_ContextualHelp')) require_once (WDFB_PLUGIN_BASE_DIR . '/lib/external/class_wd_contextual_help.php');
		$this->_help = new WpmuDev_ContextualHelp;

		if (!class_exists('WpmuDev_HelpTooltips')) require_once (WDFB_PLUGIN_BASE_DIR . '/lib/external/class_wd_help_tooltips.php');
		$this->_site_tips = new WpmuDev_HelpTooltips;
		$this->_site_tips->set_icon_url(WDFB_PLUGIN_URL . '/img/information.png');
		$this->_site_tips->set_screen_id('toplevel_page_wdfb');
		$this->_network_tips = new WpmuDev_HelpTooltips;
		$this->_network_tips->set_icon_url(WDFB_PLUGIN_URL . '/img/information.png');
		$this->_network_tips->set_screen_id('toplevel_page_wdfb-network');
	}

	public static function serve () {
		$me = new Wdfb_AdminHelp;
		$me->_initialize();
	}

	private function _initialize () {
		$this->_add_settings_page('toplevel_page_wdfb');
		$this->_add_settings_page('toplevel_page_wdfb-network');
		$this->_help->initialize();
	}

	private function _add_sidebar () {
		return '' .
			'<h4>' . __('Ultimate Facebook', 'wdfb') . '</h4>' .
			'<ul>' .
				'<li><a href="http://premium.wpmudev.org/project/ultimate-facebook" target="_blank">' . __('Project page', 'wdfb') . '</a></li>' .
				'<li><a href="http://premium.wpmudev.org/project/ultimate-facebook/installation/" target="_blank">' . __('Installation and instructions page', 'wdfb') . '</a></li>' .
				'<li><a href="http://premium.wpmudev.org/forums/tags/ultimate-facebook" target="_blank">' . __('Support forum', 'wdfb') . '</a></li>' .
			'</ul>' .
			'<h4>' . __('Useful links', 'wdfb') . '</h4>' .
			'<ul>' .
				'<li><a href="http://premium.wpmudev.org/forums/topic/how-to-make-a-facebook-app" target="_blank">' . __('How to make a Facebook App', 'wdfb') . '</a></li>' .
			'</ul>' .
		'';
	}

	private function _get_api_help () {
		return array(
			'id' => 'wdfb_api',
			'title' => __('Facebook API', 'wdfb'),
			'content' => '' .
				'<p>' .
					__('This is the most important step in plugin configuration. Please, follow the instructions to create a Facebook App and connect your site with it. If you need more help, <a href="http://premium.wpmudev.org/forums/topic/how-to-make-a-facebook-app" target="_blank">there is a detailed tutorial explaining you how to do so</a>.', 'wdfb') .
				'</p>' .
				'<p>' .
					__('Once you are done, save your settings and check for the green checkmark, followed by a message with your app name. This is a sign everything went fine.', 'wdfb') .
				'</p>'
		);
	}

	private function _get_perms_help () {
		return array(
			'id' => 'wdfb_perms',
			'title' => __('Permissions &amp; Tokens', 'wdfb'),
			'content' => '' .
				'<p>' .
					__('Some parts of the plugin require extended permissions to work. You can use a link in this section to grant them anytime.', 'wdfb') .
				'</p>' .
				'<p>' .
					__('This is also the place where you can recofigure the plugin using a different Facebook indentity, or refresh your access token manually.', 'wdfb') .
				'</p>'
		);
	}

	private function _get_connect_help () {
		return array(
			'id' => 'wdfb_connect',
			'title' => __('Facebook Connect', 'wdfb'),
			'content' => '' .
				'<p>' .
					__('Options in this section will allow you to control how other Facebook users interact with your site.', 'wdfb') .
				'</p>'
		);
	}

	private function _add_settings_page ($page) {
		$this->_help->add_page(
			$page,
			array(
				array(
					'id' => 'wdfb_intro',
					'title' => __('Introduction', 'wdfb'),
					'content' => '' .
						'<p>' .
							__('This is where you configure your plugin.', 'wdfb') .
						'</p>' .
						'<p>' .
							__('First thing first, the most important section for setup is the Facebook API settings. You will need to <a href="http://premium.wpmudev.org/forums/topic/how-to-make-a-facebook-app" target="_blank">create a Facebook app</a> in order to continue and set up your plugin properly.', 'wdfb') .
						'</p>',
				),
				$this->_get_api_help(),
				$this->_get_perms_help(),
				$this->_get_connect_help(),
				array(
					'id' => 'wdfb-tutorial',
					'title' => __('Tutorial', 'wdfb'),
					'content' => '' .
						'<p>' .
							__('Tutorial dialogs will guide you through the important bits.', 'wdsm') .
						'</p>' .
						'<p><a href="#" class="wdfb-restart_tutorial">' . __('Restart the tutorial', 'wdfb') . '</a></p>',
				),
			),
			$this->_add_sidebar(),
			true
		);
		foreach (array('_site_tips', '_network_tips') as $tip) {
			$this->$tip->bind_tip(
				__('Checking this will allow your users to register with and connect to your site using their Facebook account', 'wdfb'),
				"#wdfb-section-wdfb_connect td:first"
			);
			$this->$tip->bind_tip(
				__('Check this to use Facebook registration form instead of the default one', 'wdfb'),
				'#wdfb-force_facebook_registration-help'
			);
			$this->$tip->bind_tip(
				__('Enabling this option will make having Facebook account an absolute requirement for new users', 'wdfb'),
				'label[for=\"require_facebook_account\"]'
			);
			$this->$tip->bind_tip(
				__('You will have to both select your site area, then enter the URL fragment relative to that area', 'wdfb'),
				'#wdfb-login_redirect_base-help'
			);
			$this->$tip->bind_tip(
				__('Allows your users to reset their WordPress-to-Facebook identity mapping.', 'wdfb') . '<br />' . __('Identity renewal options will be available in your Profile section.', 'wdfb'),
				'#wdfb-identity_help_anchor'
			);
			$this->$tip->bind_tip(
				sprintf(
					__('You can also use some of the supported macros: <br />%s', 'wdfb'),
					defined('BP_VERSION')
						? '<code>USER_ID</code><br /> <code>USER_LOGIN</code><br /> <code>BP_ACTIVITY_SLUG</code><br /> <code>BP_GROUPS_SLUG</code><br /> <code>BP_MEMBERS_SLUG</code><br />'
						: '<code>USER_ID</code><br /> <code>USER_LOGIN</code><br />'
				),
				'#wdfb-login_redirect_base-url_fragment'
			);
			if (defined('BP_VERSION')) {
				$this->$tip->bind_tip(
					__('This may clash with your theme, in case your theme adds a button on its own.', 'wdfb'),
					'#wdfb-like_button-bp_activity-anchor'
				);
			}
		}
	}

}
Wdfb_AdminHelp::serve();