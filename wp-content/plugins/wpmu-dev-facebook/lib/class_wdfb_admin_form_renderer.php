<?php

/**
 * Handles rendering of form elements for plugin Options page.
 */
class Wdfb_AdminFormRenderer {

	function _get_option( $name ) {
		return WP_NETWORK_ADMIN ? get_site_option( $name ) : get_option( $name );
	}

	function _create_text_box( $pfx, $name, $value ) {
		return "<input type='text' class='widefat' name='wdfb_{$pfx}[{$name}]' id='{$name}' value='{$value}' />";
	}

	function _create_small_text_box( $pfx, $name, $value ) {
		return "<input type='text' name='wdfb_{$pfx}[{$name}]' id='{$name}' size='3' value='{$value}' />";
	}

	function _create_checkbox( $pfx, $name, $value ) {
		return "<input type='hidden' name='wdfb_{$pfx}[{$name}]' value='0' />" .
		       "<input type='checkbox' name='wdfb_{$pfx}[{$name}]' id='{$name}' value='1' " . ( $value ? 'checked="checked" ' : '' ) . " /> ";
	}

	function _create_subcheckbox( $pfx, $name, $value, $checked ) {
		return "<input type='hidden' name='wdfb_{$pfx}[{$name}][{$value}]' value='0' />" .
		       "<input type='checkbox' name='wdfb_{$pfx}[{$name}][{$value}]' id='{$name}-{$value}' value='{$value}' " . ( $checked ? 'checked="checked" ' : '' ) . " /> ";
	}

	function next_step() {
		//echo "<input type='button' class='wdfb_next_step' value='" . __('Next step', 'wdfb') . "' />";
	}

	function _create_widget_box( $widget, $description ) {
		$opt = $this->_get_option( 'wdfb_widget_pack' );
		echo "</td></tr><tr><td>";
		echo "<div class='wdfb_widget_description'>" . $this->_create_checkbox( 'widget_pack', "{$widget}_allowed", @$opt["{$widget}_allowed"] ) . $description . "</div>";
	}

	function api_info() {
		printf( __(
				'<p><b>This step must be completed before using the plugin. You must make a Facebook Application to continue.</b></p>' .
				'<p>Before we begin, you need to <a target="_blank" href="https://developers.facebook.com/apps">create a Facebook Application</a>.</p>' .
				'<p>To do so, follow these steps (We also have a <a target="_blank" href="%1$s">Guide here</a>):</p>' .
				'<ol>' .
				'<li><a target="_blank" href="https://developers.facebook.com/apps">Create your application</a></li>' .
				'<li>Please, make sure you properly configured <strong>App Domains</strong> for your App (see our <a target="_blank" href="%1$s">guide</a> for more info)</li>' .
				'<li>Look for <strong>Site URL</strong> field in the <em>Web Site</em> tab and enter your site URL in this field: <code>%2$s</code></li>' .
				'<li>After this, go to the <a target="_blank" href="https://developers.facebook.com/apps">Facebook Application List page</a> and select your newly created application</li>' .
				'<li>Copy the values from these fields: <strong>App ID</strong>/<strong>API key</strong>, and <strong>Application Secret</strong>, and enter them here:</li>' .
				'</ol>' .
				'<p>Once you\'re done with that, please click the "Save changes" button below before proceeding onto other steps.<p>',
				'wdfb' ),
			'http://premium.wpmudev.org/forums/topic/how-to-make-a-facebook-app',
			get_bloginfo( 'url' )
		);
		echo '<div class="wdfb-api_connect-result">' .
		     __( 'Checking API settings...', 'wdfb' ) .
		     '&nbsp;' .
		     '<img src="' . WDFB_PLUGIN_URL . '/img/waiting.gif" />' .
		     '</div>';
	}

	function api_permissions() {
		printf( __(
			'<p>Some parts of the plugin will require you to grant them extended permissions on Facebook. If you haven\'t done so already, it is highly recommended you do so now:</p>',
			'wdfb' ) );
		$model        = new Wdfb_Model;
		$remap_string = __( 'Use a new Facebook user for setup', 'wdfb' );
		echo '<div class="wdfb_perms_root" style="display:none">' .
		     '<p class="wdfb_perms_granted">' .
		     '<span class="wdfb_message">' . __( 'You already granted extended permissions', 'wdfb' ) . '</span> ' .
		     '</p>' .
		     '<p class="wdfb_perms_not_granted">' .
		     '<a href="#" class="wdfb_grant_perms" data-wdfb_locale="' . wdfb_get_locale() . '" data-wdfb_perms="' . esc_attr( Wdfb_Permissions::get_permissions() ) . '">' . __( 'Grant extended permissions', 'wdfb' ) . '</a>' .
		     '</p>' .
		     '<p>' .
		     '<input type="button" class="button" id="wdfb-refresh_access_token" data-wdfb_perms="' . esc_attr( Wdfb_Permissions::get_permissions() ) . '" value="' . esc_attr( __( 'Reset auth tokens', 'wdfb' ) ) . '" />' .
		     '&nbsp;' .
		     '<input type="button" class="button" id="wdfb-remap_user" data-wdfb_perms="' . esc_attr( Wdfb_Permissions::get_permissions() ) . '" value="' . esc_attr( $remap_string ) . '" />' .
		     '</p>' .
		     '</div>';
		echo '<script type="text/javascript" src="' . WDFB_PLUGIN_URL . '/js/check_permissions.js"></script>';
	}

	function extra_permissions() {
		$opts                    = $this->_get_option( 'wdfb_grant' );
		$opts                    = is_array( $opts ) ? $opts : array();
		$use_minimal_permissions = isset( $opts['use_minimal_permissions'] ) ? $opts['use_minimal_permissions'] : false;
		echo '' .
		     $this->_create_checkbox( 'grant', 'use_minimal_permissions', $use_minimal_permissions ) .
		     '&nbsp;' .
		     '<label for="use_minimal_permissions">' . __( 'Use minimal possible permission set', 'wdfb' ) . '</label>' .
		     '<br />';
		echo '<div class="updated below-h2">' .
		     '<p>' . __( '<b>Note:</b> Please, remember to re-grant the extended permissions once you made your changes here', 'wdfb' ) . '</p>' .
		     '</div>';
	}

	function cache_operations() {
		echo '<p>' .
		     '<input type="button" class="button wdfb-cache_purge" data-wdfb_purge="events" value="' . esc_attr( __( 'Purge Events cache', 'wdfb' ) ) . '" />' .
		     '&nbsp;' .
		     '<input type="button" class="button wdfb-cache_purge" data-wdfb_purge="album_photos" value="' . esc_attr( __( 'Purge Albums cache', 'wdfb' ) ) . '" />' .
		     '</p>';
	}

	function create_api_key_box() {
		$opt = $this->_get_option( 'wdfb_api' );
		echo $this->_create_text_box( 'api', 'api_key', @$opt['api_key'] );
	}

	function create_app_key_box() {
		$opt = $this->_get_option( 'wdfb_api' );
		echo $this->_create_text_box( 'api', 'app_key', @$opt['app_key'] );
	}

	function create_secret_key_box() {
		$opt = $this->_get_option( 'wdfb_api' );
		echo $this->_create_text_box( 'api', 'secret_key', @$opt['secret_key'] );
	}

	function create_locale_box() {
		$fb_locales = array(
			__( "Automatic detection", 'wdfb' ) => '',
			"Afrikaans"                         => "af_ZA",
			"Arabic"                            => "ar_AR",
			"Azeri"                             => "az_AZ",
			"Belarusian"                        => "be_BY",
			"Bulgarian"                         => "bg_BG",
			"Bengali"                           => "bn_IN",
			"Bosnian"                           => "bs_BA",
			"Catalan"                           => "ca_ES",
			"Czech"                             => "cs_CZ",
			"Welsh"                             => "cy_GB",
			"Danish"                            => "da_DK",
			"German"                            => "de_DE",
			"Greek"                             => "el_GR",
			"English (UK)"                      => "en_GB",
			"English (Pirate)"                  => "en_PI",
			"English (Upside Down)"             => "en_UD",
			"English (US)"                      => "en_US",
			"Esperanto"                         => "eo_EO",
			"Spanish (Spain)"                   => "es_ES",
			"Spanish"                           => "es_LA",
			"Estonian"                          => "et_EE",
			"Basque"                            => "eu_ES",
			"Persian"                           => "fa_IR",
			"Leet Speak"                        => "fb_LT",
			"Finnish"                           => "fi_FI",
			"Faroese"                           => "fo_FO",
			"French (Canada)"                   => "fr_CA",
			"French (France)"                   => "fr_FR",
			"Frisian"                           => "fy_NL",
			"Irish"                             => "ga_IE",
			"Galician"                          => "gl_ES",
			"Hebrew"                            => "he_IL",
			"Hindi"                             => "hi_IN",
			"Croatian"                          => "hr_HR",
			"Hungarian"                         => "hu_HU",
			"Armenian"                          => "hy_AM",
			"Indonesian"                        => "id_ID",
			"Icelandic"                         => "is_IS",
			"Italian"                           => "it_IT",
			"Japanese"                          => "ja_JP",
			"Georgian"                          => "ka_GE",
			"Korean"                            => "ko_KR",
			"Kurdish"                           => "ku_TR",
			"Latin"                             => "la_VA",
			"Lithuanian"                        => "lt_LT",
			"Latvian"                           => "lv_LV",
			"Macedonian"                        => "mk_MK",
			"Malayalam"                         => "ml_IN",
			"Malay"                             => "ms_MY",
			"Norwegian (bokmal)"                => "nb_NO",
			"Nepali"                            => "ne_NP",
			"Dutch"                             => "nl_NL",
			"Norwegian (nynorsk)"               => "nn_NO",
			"Punjabi"                           => "pa_IN",
			"Polish"                            => "pl_PL",
			"Pashto"                            => "ps_AF",
			"Portuguese (Brazil)"               => "pt_BR",
			"Portuguese (Portugal)"             => "pt_PT",
			"Romanian"                          => "ro_RO",
			"Russian"                           => "ru_RU",
			"Slovak"                            => "sk_SK",
			"Slovenian"                         => "sl_SI",
			"Albanian"                          => "sq_AL",
			"Serbian"                           => "sr_RS",
			"Swedish"                           => "sv_SE",
			"Swahili"                           => "sw_KE",
			"Tamil"                             => "ta_IN",
			"Telugu"                            => "te_IN",
			"Thai"                              => "th_TH",
			"Filipino"                          => "tl_PH",
			"Turkish"                           => "tr_TR",
			"Ukrainian"                         => "uk_UA",
			"Vietnamese"                        => "vi_VN",
			"Simplified Chinese (China)"        => "zh_CN",
			"Traditional Chinese (Hong Kong)"   => "zh_HK",
			"Traditional Chinese (Taiwan)"      => "zh_TW",
		);
		$api        = $this->_get_option( 'wdfb_api' );
		echo "<select id='wdfb-api_locale' name='wdfb_api[locale]'>";
		foreach ( $fb_locales as $label => $locale ) {
			$checked = ( $locale == @$api['locale'] ) ? 'selected="selected"' : '';
			echo "<option value='{$locale}' {$checked}>{$label}</option>";
		}
		echo "</select>";
		echo '<div><small>' . __( 'By default, the plugin will try to auto-detect your locale settings and load the appropriate API scripts. However, not all locales are supported by Facebook, so your milleage may vary. With this option, you can explicitly tell which language you want to use for communicating with Facebook.', 'wdfb' ) . '</small></div>';
	}

	function create_prevent_access_box() {
		$opt = $this->_get_option( 'wdfb_api' );
		echo $this->_create_checkbox( 'api', 'prevent_linked_accounts_access', @$opt['prevent_linked_accounts_access'] );
		echo '<div><small>' . __( 'If this option is set, your linked accounts (e.g. pages) will <b>NOT</b> be accessible by this plugin', 'wdfb' ) . '</small></div>';
	}

	function create_allow_propagation_box() {
		$opt = $this->_get_option( 'wdfb_api' );
		echo $this->_create_checkbox( 'api', 'allow_propagation', @$opt['allow_propagation'] );
	}

	function create_allow_facebook_registration_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'allow_facebook_registration', @$opt['allow_facebook_registration'] );
	}

	function create_no_main_site_registration_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'no_main_site_registration', @$opt['no_main_site_registration'] );
		echo '<div><small>' . __( 'This option does not apply to single-click registration.', 'wdfb' ) . '</small></div>';
	}

	function create_easy_facebook_registration_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'easy_facebook_registration', @$opt['easy_facebook_registration'] );
		echo '<div><small>' . __( 'If enabled, the "Login with Facebook" button will work as a single-click register button for your new users', 'wdfb' ) . '</small></div>';
	}

	function create_update_bp_activity_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'update_feed_on_registration', @$opt['update_feed_on_registration'] );
		echo '&nbsp;<label for="update_feed_on_registration">' . __( 'New users registration will update your site Activity feed', 'wdfb' ) . '</label>';
		echo '<br />';
		echo $this->_create_checkbox( 'connect', 'update_feed_on_easy_registration', @$opt['update_feed_on_easy_registration'] );
		echo '&nbsp;<label for="update_feed_on_easy_registration">' . __( 'Single-click registration will update your site Activity feed', 'wdfb' ) . '</label>';

	}

	function create_facebook_avatars_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'skip_fb_avatars', @$opt['skip_fb_avatars'] );
		echo '<div><small>' . __( 'By default, the plugin will use Facebook profile images instead of your users avatars wherever possible. Enable this option to prevent this behavior', 'wdfb' ) . '</small></div>';
	}

	function create_login_redirect_box() {
		$opt     = $this->_get_option( 'wdfb_connect' );
		$base    = @$opt['login_redirect_base'] ? @$opt['login_redirect_base'] : 'site_url';
		$base    = ( $base == 'site_url' ) ? 'site_url' : 'admin_url';
		$allowed = array(
			__( 'Site URL', 'wdfb' )  => 'site_url',
			__( 'Admin URL', 'wdfb' ) => 'admin_url',
		);
		$url     = @$opt['login_redirect_url'] ? '<code>' . apply_filters( 'wdfb-login-redirect_url', $base( @$opt['login_redirect_url'] ) ) . '</code>' : __( 'current page, or plugin default', 'wdfb' );

		echo "<select name='wdfb_connect[login_redirect_base]'>";
		foreach ( $allowed as $label => $item ) {
			$checked = ( $base == $item ) ? 'selected="selected"' : '';
			echo "<option value='{$item}' {$checked}>{$label}</option>";
		}
		echo "</select><span id='wdfb-login_redirect_base-help'></span>";
		echo '<div><small id="wdfb-login_redirect_base-url_fragment">' . sprintf( __( 'Select your site area (above), then fill in the relative URL fragment:', 'wdfb' ), $url ) . '</small></div>';

		echo $this->_create_text_box( 'connect', 'login_redirect_url', @$opt['login_redirect_url'] );
		echo '<div><small>' . sprintf( __( 'This is what will happen upon login: my users will be redirected to %s.', 'wdfb' ), $url ) . '</small></div>';
	}

	function create_autologin_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'autologin_after_registration', @$opt['autologin_after_registration'] );
	}

	function create_buddypress_registration_fields_box() {
		$opt       = $this->_get_option( 'wdfb_connect' );
		$model     = new Wdfb_Model;
		$fb_fields = $model->get_known_fb_fields_map();
		$bp_fields = $model->get_bp_xprofile_fields();
		if ( ! is_array( $bp_fields ) ) {
			return '';
		}

		foreach ( $bp_fields as $bpf ) {
			_e( sprintf( 'Map %s to', $bpf['name'] ), 'wdfb' );
			echo ' <select name="wdfb_connect[buddypress_registration_fields_' . $bpf['id'] . ']">';
			foreach ( $fb_fields as $fbf_key => $fbf_label ) {
				echo '<option value="' . $fbf_key . '" ' . ( ( @$opt[ 'buddypress_registration_fields_' . $bpf['id'] ] == $fbf_key ) ? 'selected="selected"' : '' ) . ' >' . $fbf_label . '</option>';
			}
			echo '</select><br />';
		}
	}

	function create_identity_renewal_box() {
		$opt = $this->_get_option( 'wdfb_connect' );
		echo $this->_create_checkbox( 'connect', 'allow_identity_renewal', @$opt['allow_identity_renewal'] );
		echo '<span id="wdfb-identity_help_anchor"></span>';
		echo '<div><small>' . __( 'Enabling this option will allow your users to change their associated Facebook profiles to ones they\'re currently actively using.', 'wdfb' ) . '</small></div>';
	}

	function create_wordpress_registration_fields_box() {
		$opt       = $this->_get_option( 'wdfb_connect' );
		$model     = new Wdfb_Model;
		$fb_fields = $model->get_known_fb_fields_map();

		// Set up default mapping
		$wp_defaults = array(
			array( 'wp' => 'first_name', 'fb' => 'first_name' ),
			array( 'wp' => 'last_name', 'fb' => 'last_name' ),
			array( 'wp' => 'description', 'fb' => 'bio' ),
		);
		$wp_reg      = @$opt['wordpress_registration_fields'];
		$wp_reg      = is_array( $wp_reg ) ? $wp_reg : $wp_defaults;

		echo '<div id="wdfb_connect_wp_registration_container">';
		foreach ( $wp_reg as $idx => $reg ) {
			$wp_box = $fb_box = '';
			$wp_box = '<input type="text" size="12" maxsize="32" name="wdfb_connect[wordpress_registration_fields][' . $idx . '][wp]" value="' . $reg['wp'] . '" />';
			$fb_box = '<select name="wdfb_connect[wordpress_registration_fields][' . $idx . '][fb]">';
			foreach ( $fb_fields as $fbf_key => $fbf_label ) {
				$fb_box .= '<option value="' . $fbf_key . '" ' . ( ( @$reg['fb'] == $fbf_key ) ? 'selected="selected"' : '' ) . '>' . $fbf_label . '</option>';
			}
			$fb_box .= '</select>';
			printf(
				'<div class="wdfb_connect_wp_registration">' . __( 'Map %s to %s', 'wdfb' ) . '</div>',
				$wp_box, $fb_box
			);
		}
		echo '</div>';
		echo '<p><input type="button" id="wdfb_connect_add_mapping" value="' . __( 'Add another mapping', 'wdfb' ) . '" /></p>';
	}

	function create_allow_facebook_button_box() {
		$opt = $this->_get_option( 'wdfb_button' );
		echo $this->_create_checkbox( 'button', 'allow_facebook_button', @$opt['allow_facebook_button'] );
	}

	function create_show_send_button_box() {
		$opt = $this->_get_option( 'wdfb_button' );
		echo $this->_create_checkbox( 'button', 'show_send_button', @$opt['show_send_button'] );
	}

	function create_show_on_front_page_box() {
		$opt = $this->_get_option( 'wdfb_button' );
		echo $this->_create_checkbox( 'button', 'show_on_front_page', @$opt['show_on_front_page'] ) .
		     '<label for="show_on_front_page">' . __( 'Front page', 'wdfb' ) . '</label>' .
		     '<br />';
		echo $this->_create_checkbox( 'button', 'show_on_archive_page', @$opt['show_on_archive_page'] ) .
		     '<label for="show_on_archive_page">' . __( 'Archives page', 'wdfb' ) . '</label>' .
		     '<br />';
		echo '<label for="shared_pages_use_xfbml">' . __( 'Use XFBML button on shared pages:', 'wdfb' ) . '</label>&nbsp;' .
		     $this->_create_checkbox( 'button', 'shared_pages_use_xfbml', @$opt['shared_pages_use_xfbml'] ) .
		     '<br />';
	}

	function create_do_not_show_button_box() {
		$opt   = $this->_get_option( 'wdfb_button' ); // 'not_in_post_types'
		$types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $types as $type ) {
			echo $this->_create_subcheckbox( 'button', 'not_in_post_types', $type->name, @in_array( $type->name, $opt['not_in_post_types'] ) );
			echo '<label>' . ucfirst( $type->labels->name ) . '</label><br />';
		}
		echo '<div id="wdfb-like_button-special_cases">';
		if ( defined( 'BP_VERSION' ) && class_exists('BuddyPress') ) {
			echo '<div id="wdfb-like_button-bp_activity-anchor">';
			echo '<label for="not_in_post_types-_buddypress_activity">' . __( 'Allow &quot;Like&quot; button for BuddyPress Activities', 'wdfb' ) . '</label>: ';
			echo $this->_create_subcheckbox( 'button', 'not_in_post_types', '_buddypress_activity', @in_array( '_buddypress_activity', $opt['not_in_post_types'] ) );
			echo '</div>';
			echo '<label for="bp_activity_xfbml">' . __( 'Use XFBML button for BuddyPress Activities', 'wdfb' ) . '</label>: ';
			echo $this->_create_checkbox( 'button', 'bp_activity_xfbml', @$opt['bp_activity_xfbml'] );
		}
		echo '</div>';
	}

	function create_button_position_box() {
		$opt       = $this->_get_option( 'wdfb_button' );
		$positions = array(
			'top'    => __( 'Before', 'wdfb' ),
			'bottom' => __( 'After', 'wdfb' ),
			'both'   => __( 'Before and after', 'wdfb' ),
			'manual' => __( 'Manual, use shortcodes in ', 'wdfb' )
		);

		foreach ( $positions as $pos => $label ) {
			echo '<input type="radio" name="wdfb_button[button_position]" value="' . $pos . '" ' . ( isset( $opt['button_position'] ) && ( $opt['button_position'] == $pos ) ? 'checked="checked"' : '' ) . ' /> ';
			printf( __( "%s the contents of your post <br />", 'wdfb' ), $label );
		}
	}

	function create_button_appearance_box() {
		$opt        = $this->_get_option( 'wdfb_button' );
		$blog_uri   = get_option( 'siteurl' );
		$send_value = @$opt['show_send_button'] ? 'true' : 'false';

		echo "<table border='0'>";

		echo '<tr>';
		echo '<td valign="top"><input type="radio" name="wdfb_button[button_appearance]" value="standard" ' . ( ( !empty( $opt['button_appearance'] ) && $opt['button_appearance'] == "standard" ) ? 'checked="checked"' : '' ) . ' /></td>';
		echo '<td valign="top"><iframe src="' . WDFB_PROTOCOL . 'www.facebook.com/plugins/like.php?href=' . $blog_uri . '&amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:25px;" allowTransparency="true"></iframe></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td valign="top"><input type="radio" name="wdfb_button[button_appearance]" value="button_count" ' . ( ( !empty( $opt['button_appearance'] ) && $opt['button_appearance'] == "button_count" ) ? 'checked="checked"' : '' ) . ' /></td>';
		echo '<td valign="top"><iframe src="' . WDFB_PROTOCOL . 'www.facebook.com/plugins/like.php?href=' . $blog_uri . '&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td valign="top"><input type="radio" name="wdfb_button[button_appearance]" value="box_count" ' . ( ( !empty( $opt['button_appearance'] ) && $opt['button_appearance'] == "box_count" ) ? 'checked="checked"' : '' ) . ' /></td>';
		echo '<td valign="top"><iframe src="' . WDFB_PROTOCOL . 'www.facebook.com/plugins/like.php?href=' . $blog_uri . '&amp;send=false&amp;layout=box_count&amp;width=450&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:65px;" allowTransparency="true"></iframe></td>';
		echo '</tr>';

		echo "</table>";
	}

	function create_button_color_scheme_box() {
		$opt      = $this->_get_option( 'wdfb_button' );
		$_schemes = array(
			'light' => __( 'Light', 'wdfb' ),
			'dark'  => __( 'Dark', 'wdfb' ),
		);
		echo "<select name='wdfb_button[color_scheme]'>";
		foreach ( $_schemes as $idx => $lbl ) {
			echo "<option value='{$idx}' " . ( ( $idx == @$opt['color_scheme'] ) ? 'selected="selected"' : '' ) . ">{$lbl}</option>";
		}
		echo "</select>";
	}

	function create_use_opengraph_box() {
		$opt = $this->_get_option( 'wdfb_opengraph' );
		echo $this->_create_checkbox( 'opengraph', 'use_opengraph', @$opt['use_opengraph'] );
		echo '<div><small>' . __( "OpenGraph allows you better control over what is shared on Facebook when someone clicks \"Like/Send\" button.", 'wdfb' ) . '</small></div>';
	}

	function create_always_use_image_box() {
		$opt = $this->_get_option( 'wdfb_opengraph' );
		echo $this->_create_text_box( 'opengraph', 'always_use_image', @$opt['always_use_image'] );
		if ( @$opt['always_use_image'] ) {
			printf( __( '<p>Preview:<br /><img src="%s" /></p>', 'wdfb' ), @$opt['always_use_image'] );
		}
		echo '<div><small>' . __( "Please, use full URL to image (e.g. <code>http://example.com/images/example.jpg</code>). If set, this image will <b>always</b> be used on Facebook for content sharing", 'wdfb' ) . '</small></div>';
	}

	function create_fallback_image_box() {
		$opt = $this->_get_option( 'wdfb_opengraph' );
		echo $this->_create_text_box( 'opengraph', 'fallback_image', @$opt['fallback_image'] );
		if ( @$opt['fallback_image'] ) {
			printf( __( '<p>Preview:<br /><img src="%s" /></p>', 'wdfb' ), @$opt['fallback_image'] );
		}
		echo '<div><small>' . __( "By default, we will attempt to use featured image or first image from the post for sharing on Facebook.", 'wdfb' ) . '</small></div>';
		echo '<div><small>' . __( "If we fail, this image will be used instead. Please, use full URL to image (e.g. <code>http://example.com/images/example.jpg</code>).", 'wdfb' ) . '</small></div>';
	}

	function create_og_type_box() {
		$opt = $this->_get_option( 'wdfb_opengraph' );

		echo $this->_create_checkbox( 'opengraph', 'og_custom_type', @$opt['og_custom_type'] );
		echo '<label for="og_custom_type">' . __( 'Do not autodetect my OpenGraph type, I will set it manually', 'wdfb' ) . '</label> ';
		echo '<div><small>' . __( "If you check this option the plugin will not autodetect your OpenGraph type and you'll be able to set it manually.", 'wdfb' ) . '</small></div>';

		echo '<div id="og_custom_mapping">';
		echo '<label for="og_custom_type_front_page">' . __( 'OpenGraph type on my front page', 'wdfb' ) . '</label> ';
		echo $this->_create_text_box( 'opengraph', 'og_custom_type_front_page', @$opt['og_custom_type_front_page'] );

		echo '<label for="og_custom_type_not_singular">' . __( 'OpenGraph type on my taxonomies pages', 'wdfb' ) . '</label> ';
		echo $this->_create_text_box( 'opengraph', 'og_custom_type_not_singular', @$opt['og_custom_type_not_singular'] );
		echo '<div><small>' . __( "This type will be used on your pages that show multiple content (e.g. archives, tags, categories).", 'wdfb' ) . '</small></div>';

		echo '<label for="og_custom_type_singular">' . __( 'OpenGraph type on my content pages', 'wdfb' ) . '</label> ';
		echo $this->_create_text_box( 'opengraph', 'og_custom_type_singular', @$opt['og_custom_type_singular'] );
		echo '<div><small>' . __( "This type will be used on your pages that show individual content pieces (e.g. posts and pages).", 'wdfb' ) . '</small></div>';

		echo '</div>';
	}

	function create_og_extras_box() {
		$opt    = $this->_get_option( 'wdfb_opengraph' );
		$last   = 1;
		$extras = @$opt['og_extra_headers'] ? $opt['og_extra_headers'] : array();
		if ( ! empty ( $extras ) ) {
			foreach ( $extras as $idx => $extra ) {
				$name = esc_attr( @$extra['name'] );
				if ( ! $name ) {
					continue;
				}
				$value = esc_attr( @$extra['value'] );
				echo '<div class="wdfb_og_extra_mapping">';
				echo "<label for='og_extra_name-{$idx}'>" . __( 'OpenGraph name:', 'wdfb' ) . '</label> ' .
				     "<input id='og_extra_name-{$idx}' size='24' name='wdfb_opengraph[og_extra_headers][{$idx}][name]' value='{$name}' />" .
				     "&nbsp;&nbsp;&nbsp;";
				echo "<label for='og_extra_value-{$idx}'>" . __( 'OpenGraph value:', 'wdfb' ) . '</label> ' .
				     "<input id='og_extra_value-{$idx}' size='24' name='wdfb_opengraph[og_extra_headers][{$idx}][value]' value='{$value}' />" .
				     "&nbsp;&nbsp;&nbsp;";
				echo "<a href='' class='wdfb_og_remove_extra'>" . __( 'Remove', 'wdfb' ) . '</a>';
				echo "</div>";
			}
			$last = max( count( $extras ), $idx ) + 1;
		}
		echo '<div class="wdfb_og_extra_mapping">';
		echo "<label for='og_extra_name'>" . __( 'OpenGraph name:', 'wdfb' ) . '</label> ' .
		     "<input id='og_extra_name' size='24' name='wdfb_opengraph[og_extra_headers][{$last}][name]' value='' />" .
		     "&nbsp;&nbsp;&nbsp;";
		echo "<label for='og_extra_value-'>" . __( 'OpenGraph value:', 'wdfb' ) . '</label> ' .
		     "<input id='og_extra_value' size='24' name='wdfb_opengraph[og_extra_headers][{$last}][value]' value='' />" .
		     "";
		echo "</div>";
		echo '<input type="button" class="wdfb-save_settings" data-wdfb_section_id="wdfb_opengraph" value="' . esc_attr( __( 'Add header', 'wdfb' ) ) . '" />';
	}

	function create_allow_bp_groups_sync_box() {
		$opt = $this->_get_option( 'wdfb_groups' );
		$opt = $opt ? $opt : array();
		echo '' .
		     $this->_create_checkbox( 'groups', 'allow_bp_groups_sync', @$opt['allow_bp_groups_sync'] ) .
		     '&nbsp;<label for="allow_bp_groups_sync">' . __( 'Allow BuddyPress groups info syncing with Facebook group profile info', 'wdfb' ) . '</label>' .
		     '<br />';
		/*
		echo '' .
			$this->_create_checkbox('groups', 'allow_group_avatar_sync',  @$opt['allow_group_avatar_sync']) .
			'&nbsp;<label for="allow_group_avatar_sync">' . __('Also sync group avatar', 'wdfb') . '</label>' .
		'<br />';
		*/
		echo '' .
		     $this->_create_checkbox( 'groups', 'allow_group_privacy_sync', @$opt['allow_group_privacy_sync'] ) .
		     '&nbsp;<label for="allow_group_privacy_sync">' . __( 'Also sync group privacy settings', 'wdfb' ) . '</label>' .
		     '<br />';
		echo '' .
		     $this->_create_checkbox( 'groups', 'notify_members', @$opt['notify_members'] ) .
		     '&nbsp;<label for="notify_members">' . __( 'Notify group members on successful data sync', 'wdfb' ) . '</label>' .
		     '<br />';
	}

	function create_import_fb_comments_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo $this->_create_checkbox( 'comments', 'import_fb_comments', @$opt['import_fb_comments'] );
		echo '<div><small>' . __( 'If checked, comments on your posts on Facebook will be periodically imported and merged with your regular comments.', 'wdfb' ) . '</small></div>';
	}

	function create_import_fb_comments_skip_box() {
		$opt      = $this->_get_option( 'wdfb_comments' );
		$skips    = @$opt['skip_import'];
		$skips    = is_array( $skips ) ? $skips : array();
		$reverse  = @$opt['reverse_skip_logic'];
		$data     = Wdfb_OptionsRegistry::get_instance();
		$accounts = $data->get_option( 'wdfb_api', 'auth_tokens' );
		if ( is_array( $accounts ) ) {
			echo '<div id="wdfb-comments-skip_accounts">';
			foreach ( $accounts as $fb_uid => $token ) {
				if ( ! $fb_uid ) {
					continue;
				}
				$checked = in_array( $fb_uid, $skips ) ? true : false;
				echo $this->_create_subcheckbox( 'comments', 'skip_import', $fb_uid, $checked ) .
				     " <label for='skip_import-{$fb_uid}'><span class='wdfb_uid_to_name_mapper'>{$fb_uid}</span></label><br />";
			}
			echo '<div><small>' .
			     (
			     $reverse
				     ? __( 'Comments on your posts on Facebook will <b>ONLY</b> be imported for the checked IDs.', 'wdfb' )
				     : __( 'Comments on your posts on Facebook will <b>NOT</b> be imported for any of the checked IDs.', 'wdfb' )
			     ) .
			     '</small></div>';
			echo '<br />' .
			     '<label for="reverse_skip_logic">' . __( 'Reverse checking logic:', 'wdfb' ) . '</label>' .
			     '&nbsp;' .
			     $this->_create_checkbox( 'comments', 'reverse_skip_logic', $reverse ) .
			     '';
			echo '<div><small>' . __( 'Toggling this option will reverse import skipping options.', 'wdfb' ) . '</small></div>';
			echo '</div>';
		}
	}

	function create_fb_comments_limit_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo "<select name='wdfb_comments[comment_limit]'>";
		for ( $i = 0; $i < 110; $i += 10 ) {
			echo "<option value='{$i}' " . ( ( $i == @$opt['comment_limit'] ) ? 'selected="selected"' : '' ) . ">{$i}</option>";
		}
		echo "</select>";
		echo '<div><small>' . __( 'This value is applied to number of Facebook posts inspected for comments. Set the limit to lower value if you experience performance issues.', 'wdfb' ) . '</small></div>';
	}

	function create_notify_authors_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo $this->_create_checkbox( 'comments', 'notify_authors', @$opt['notify_authors'] );
		echo '<div><small>' . __( 'If checked, post authors will be notified when new comments from Facebook are imported and added to their posts.', 'wdfb' ) . '</small></div>';
	}

	function create_fbc_notify_authors_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo $this->_create_checkbox( 'comments', 'fbc_notify_authors', @$opt['fbc_notify_authors'] );
		echo '<div><small>' . __( 'If checked, post authors will be notified when new comments are added to their posts using Facebook comments.', 'wdfb' ) . '</small></div>';
	}

	function create_import_now_box() {
		echo '<a href="#" class="wdfb_import_comments_now">' . __( "Import comments now (this can take a while)", 'wdfb' ) . '<a>';
	}

	function create_use_fb_comments_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo '<hr />';
		echo $this->_create_checkbox( 'comments', 'use_fb_comments', @$opt['use_fb_comments'] );
		echo '<div><small>' . __( 'If checked, <a href="http://developers.facebook.com/blog/post/472">Facebook comments</a> will be used instead of, or in addition to, regular WordPress comments.', 'wdfb' ) . '</small></div>';
	}

	function create_override_wp_comments_settings_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo $this->_create_checkbox( 'comments', 'override_wp_comments_settings', @$opt['override_wp_comments_settings'] );
		echo '<div><small>' . __(
				'<p>By default, Facebook comments will appear <em>in addition</em> to WordPress comments.</p>' .
				'<p>Check this box if you wish to disable WordPress comments, but still allow Facebook Comments.</p>' .
				'<p><strong>Note:</strong> if you enable this option, Facebook Comments will always appear, disregarding any WordPress settings.</p>'
				,
				'wdfb' ) . '</small></div>';
	}

	function create_fb_comments_width_box() {
		$opt = $this->_get_option( 'wdfb_comments' );

		// Box width
		$width = @$opt['fb_comments_width'];
		$width = $width ? $width : '550';
		echo $this->_create_small_text_box( 'comments', 'fb_comments_width', $width ) . 'px';
	}

	function create_fb_comments_reverse_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		// Reverse?
		echo $this->_create_checkbox( 'comments', 'fb_comments_reverse', @$opt['fb_comments_reverse'] );
	}

	function create_fb_comments_number_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		// Comments number
		$num = isset( $opt['fb_comments_number'] ) ? $opt['fb_comments_number'] : 10;
		echo '<select name="wdfb_comments[fb_comments_number]">';
		for ( $i = 0; $i < 100; $i += 5 ) {
			$selected = ( $i == $num ) ? 'selected="selected"' : '';
			echo "<option value='{$i}' {$selected}>{$i}</option>";
		}
		echo '</select>';
	}

	function create_fb_comments_color_scheme_box() {
		$opt      = $this->_get_option( 'wdfb_comments' );
		$_schemes = array(
			'light' => __( 'Light', 'wdfb' ),
			'dark'  => __( 'Dark', 'wdfb' ),
		);
		echo "<select name='wdfb_comments[fb_color_scheme]'>";
		foreach ( $_schemes as $idx => $lbl ) {
			echo "<option value='{$idx}' " . ( ( $idx == @$opt['fb_color_scheme'] ) ? 'selected="selected"' : '' ) . ">{$lbl}</option>";
		}
		echo "</select>";
	}

	function create_fb_comments_custom_hook_box() {
		$opt = $this->_get_option( 'wdfb_comments' );
		echo $this->_create_text_box( 'comments', 'fb_comments_custom_hook', @$opt['fb_comments_custom_hook'] );
		echo '<div><small>' . __( '<p><b>Warning:</b> You should leave this box empty unless you know what you&#039;re doing.', 'wdfb' ) . '</small></div>';
		echo '<div><small>' . __( '<p>Themes can use different approaches to showing comments, which can cause Facebook Comments not to appear in some cases.', 'wdfb' ) . '</small></div>';
		echo '<div><small>' . __( '<p>If you experience such a conflict between Facebook Comments and your theme, you can enter a custom hook for Facebook Comments here.', 'wdfb' ) . '</small></div>';
		echo '<div><small>' . __( '<p>If everything else fails, you can enter this code in your theme where you want your comments to appear: <code>&lt;?php do_action("my_action_name");?&gt;</code>, then enter "my_action_name" (without quotes) in this box.', 'wdfb' ) . '</small></div>';
	}

	function create_allow_autopost_box() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'allow_autopost', @$opt['allow_autopost'] );
	}

	function skip_autopost() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'allow_skip_autopost', @$opt['allow_skip_autopost'] );
	}

	function create_allow_frontend_autopost_box() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'allow_frontend_autopost', @$opt['allow_frontend_autopost'] );
		echo '<div><small>' . __( 'Enable this option to allow auto-publishing on Facebook with frontend posting plugins', 'wdfb' ) . '</small></div>';
	}

	function create_show_status_column_box() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'show_status_column', @$opt['show_status_column'] );
		echo '<div><small>' . __( 'Enabling this will add a new column that shows if the post has already been published on Facebook to your post management pages.', 'wdfb' ) . '</small></div>';
	}

	function sortByOrder( $a, $b ) {
		return $a['width'] - $b['width'];
	}

	function image_size_autopost_facebook() {
		//Get all registered image sizes
		$sizes = $this->get_image_sizes();

		uasort( $sizes, array( $this, 'sortByOrder' ) );
		$opt               = $this->_get_option( 'wdfb_autopost' );
		$opt['image_size'] = empty( $opt['image_size'] ) ? ( ! empty( $sizes['large'] ) ? 'large' : 'full' ) : $opt['image_size'];
		?>
		<select name="wdfb_autopost[image_size]"><?php
		foreach ( $sizes as $size => $details ) {
			$selected = ( $size == @$opt['image_size'] ) ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $size; ?>" <?php echo $selected; ?> ><?php echo ucfirst( $size ) . ' - ' . $details['width'] . 'x' . $details['height']; ?></option><?php
		}?>
		</select><?php
	}

	function get_image_sizes( $size = '' ) {

		global $_wp_additional_image_sizes;

		$sizes                        = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach ( $get_intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
				);

			}

		}

		// Get only 1 size if found
		if ( $size ) {

			if ( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}

		}

		return $sizes;
	}

	function create_autopost_map_box() {
		$post_types   = get_post_types( array( 'public' => true ), 'objects' );
		$fb_locations = array(
			'0'    => __( "Don't post this type to Facebook", 'wdfb' ),
			'feed' => __( "Facebook wall", 'wdfb' )
		);
		$data         = Wdfb_OptionsRegistry::get_instance();
		$opts         = $this->_get_option( 'wdfb_autopost' );

		$user        = wp_get_current_user();
		$fb_accounts = $this->_get_api_accounts( $user->ID );
		/*
		$fb_accounts = get_user_meta($user->ID, 'wdfb_api_accounts', true);
		$fb_accounts = isset($fb_accounts['auth_accounts']) ? $fb_accounts['auth_accounts'] : array();
		*/

		echo '<div id="wdfb-autopost_map_message"></div>';
		echo "<ul>";
		foreach ( $post_types as $pname => $pval ) {
			$pname     = $pval->name;
			$pval      = $pval->labels->name;
			$fb_action = @$opts["type_{$pname}_fb_type"];
			$box       = '<select name="wdfb_autopost[type_' . $pname . '_fb_type]">';
			foreach ( $fb_locations as $fbk => $fbv ) {
				$box .= "<option value='{$fbk}' " . ( ( $fbk == $fb_action ) ? 'selected="selected"' : '' ) . ">{$fbv}</option>";
			}
			$box .= '</select>';

			$fb_user_val = @$opts["type_{$pname}_fb_user"];
			$fb_user     = '<select name="wdfb_autopost[type_' . $pname . '_fb_user]">';
			foreach ( $fb_accounts as $aid => $aval ) {
				$fb_user .= "<option value='{$aid}' " . ( ( $fb_user_val == $aid ) ? 'selected="selected"' : '' ) . ">{$aval}</option>";
			}
			$fb_user .= '</select>';

			$shortlink = "" .
			             "<input type='hidden' class='wdfb-autopost-shortlink' name='wdfb_autopost[type_{$pname}_use_shortlink]' value='' />" .
			             "<input type='checkbox' class='wdfb-autopost-shortlink' name='wdfb_autopost[type_{$pname}_use_shortlink]' " .
			             "id='wdfb-{$pname}_use_shortlink' value='1' " . ( @$opts["type_{$pname}_use_shortlink"] ? 'checked="checked"' : '' ) . " /> " .
			             '<label for="wdfb-' . $pname . '_use_shortlink">' . __( 'Use shortlink', 'wdfb' ) . '</label>' .
			             "";

			printf(
				__( "<li>Autopost %s to %s of this user: %s %s </li>", 'wdfb' ),
				ucfirst( $pval ), $box, $fb_user, $shortlink
			);
		}

		// BP Activities mappings
		if ( defined( 'BP_VERSION' ) && class_exists('BuddyPress') ) {
			$pname     = 'bp_activity';
			$pval      = __( 'BuddyPress Activity update', 'wdfb' );
			$fb_action = @$opts["type_{$pname}_fb_type"];
			$box       = '<select name="wdfb_autopost[type_' . $pname . '_fb_type]">';
			foreach ( $fb_locations as $fbk => $fbv ) {
				$box .= "<option value='{$fbk}' " . ( ( $fbk == $fb_action ) ? 'selected="selected"' : '' ) . ">{$fbv}</option>";
			}
			$box .= '</select>';

			$fb_user_val = @$opts["type_{$pname}_fb_user"];
			$fb_user     = '<select name="wdfb_autopost[type_' . $pname . '_fb_user]">';
			foreach ( $fb_accounts as $aid => $aval ) {
				$fb_user .= "<option value='{$aid}' " . ( ( $fb_user_val == $aid ) ? 'selected="selected"' : '' ) . ">{$aval}</option>";
			}
			$fb_user .= '</select>';

			printf(
				__( "<li>Autopost %s to %s of this user: %s </li>", 'wdfb' ),
				ucfirst( $pval ), $box, $fb_user, $shortlink
			);
		}

		echo "</ul>";
		echo '<label for="post_as_page">' . __( "If posting to a page, post <b>AS</b> page", "wdfb" ) . '</label> ' .
		     $this->_create_checkbox( 'autopost', 'post_as_page', @$opts['post_as_page'] );
		echo '<div><small>' . __( "" .
		                          "Choose the Facebook user ID from the <em>user</em> box, or leave selection to &quot;Me&quot; to use your own profile. " .
		                          "Remember, the plugin needs to be granted <strong>extended permissions</strong> to access the profile. <br />" .
		                          "To post to a fan page as page, you need to be administrator of that page." .
		                          "", 'wdfb' ) . '</small></div>';
	}

	function create_allow_post_metabox_box() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'prevent_post_metabox', @$opt['prevent_post_metabox'] );
		echo '<div><small>' . __( 'If you check this box, your users will <b>NOT</b> be able to make individual posts to Facebook using the post editor metabox.', 'wdfb' ) . '</small></div>';
	}

	function create_allow_bp_activity_switch_box() {
		$opt = $this->_get_option( 'wdfb_autopost' );
		echo $this->_create_checkbox( 'autopost', 'prevent_bp_activity_switch', @$opt['prevent_bp_activity_switch'] );
		echo '<div><small>' . __( 'If you check this box, your users will <b>NOT</b> be able to make individual posts to Facebook using the Activities form switch.', 'wdfb' ) . '</small></div>';
	}

	function create_override_all_box() {
		$opt = get_site_option( 'wdfb_network', array() );
		echo $this->_create_checkbox( 'network', '_override_all', @$opt['_override_all'] );
		echo '<div><small>' . __( 'If you check this box, all your users individual Facebook settings will be deleted and replaced with what you set here.', 'wdfb' ) . '</small></div>';
	}

	function create_preserve_api_box() {
		$opt = get_site_option( 'wdfb_network', array() );
		echo $this->_create_checkbox( 'network', '_preserve_api', @$opt['_preserve_api'] );
		echo '<div><small>' . __( 'If you check this box, your users individual Facebook API settings will be <em>preserved</em> when replacing their options.', 'wdfb' ) . '</small></div>';
	}

	function create_prevent_blog_settings_box() {
		$opt = get_site_option( 'wdfb_network', array() );
		echo $this->_create_checkbox( 'network', 'prevent_blog_settings', @$opt['prevent_blog_settings'] );
		echo '<div><small>' . __( 'If you check this box, your users will <b>NOT</b> be able to make individual changes to plugin settings from now on.', 'wdfb' ) . '</small></div>';
		echo '<div><small>' . __( 'Also, if you want to use this option, you may want to allow your subsites to use your global FB app credentials in <a href="#" class="wdfb_skip_to_step_0">Facebook API settings</a>.', 'wdfb' ) . '</small></div>';
	}

	function create_widget_connect_box() {
		$description = sprintf( __( 'Easily display Facebook Connect options on your front page. <table> <tr><th>Widget settings preview</th><th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/connect_allowed.png" /></td><td valign="top"><img src="%s/img/connect_allowed_result.jpg" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL, WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'connect', $description );
	}

	function create_widget_albums_box() {
		$description = sprintf( __( 'Easily display Facebook Albums. <table> <tr><th>Widget settings preview</th><th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/albums_allowed.png" /></td><td valign="top"><img src="%s/img/albums_allowed_result.jpg" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL, WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'albums', $description );
	}

	function create_widget_events_box() {
		$description = sprintf( __( 'Easily display Facebook Events. <table> <tr><th>Widget settings preview</th><th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/events_allowed.png" /></td><td valign="top"><img src="%s/img/events_allowed_result.jpg" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL, WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'events', $description );
	}

	function create_widget_likebox_box() {
		$description = sprintf( __( 'Easily display Facebook Like Box. <table> <tr><th>Widget settings preview</th><th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/likebox_allowed.png" /></td><td valign="top"><img src="%s/img/likebox_allowed_result.jpg" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL, WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'likebox', $description );
	}

	function create_widget_recent_comments_box() {
		$description = sprintf( __( 'Easily display your recently imported Facebook comments. <table> <tr><th>Widget settings preview</th><th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/recent_comments_allowed.png" /></td><td valign="top"><img src="%s/img/recent_comments_allowed_result.jpg" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL, WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'recent_comments', $description );
	}

	function create_dashboard_permissions_box() {
		$description = sprintf( __( 'Display extended permissions granting box for your users in the Dashboard. <table> <th>Widget preview<th></tr> <tr><td valign="top"><img src="%s/img/dashboard_permissions_allowed.png" /></td></tr> </table>', 'wdfb' ), WDFB_PLUGIN_URL );
		$this->_create_widget_box( 'dashboard_permissions', $description );
	}

	/* ===== Metaboxes ===== */

	function facebook_publishing_metabox() {
		global $post;

		$opts = $this->_get_option( 'wdfb_autopost' );
		$type_post_fb_user = !empty( $opts['type_post_fb_user'] ) ? $opts['type_post_fb_user'] : '';

		$wdfb_published_on_fb = get_post_meta( $post->ID, 'wdfb_published_on_fb', true );
		$post_status          = get_post_status( $post->ID );

		if ( $wdfb_published_on_fb ) {
			echo '<div style="margin: 5px 0 15px; background-color: #FFFFE0; border-color: #E6DB55; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; padding: 0 0.6em;">' .
			     '<p>' . __( "This post has already been published on Facebook", 'wdfb' ) . '</p>' .
			     '</div>';
		}

		$stored                 = get_post_meta( $post->ID, 'wdfb_scheduled_publish', true );
		$stored                 = is_array( $stored ) ? $stored : array();
		$title                  = ! empty( $stored['wdfb_metabox_publishing_title'] ) ? $stored['wdfb_metabox_publishing_title'] : '';
		$stored_publish         = ! empty( $stored['wdfb_metabox_publishing_publish'] );
		$stored_publishing_user = ! empty( $stored['wdfb_metabox_publishing_account'] ) ? $stored['wdfb_metabox_publishing_account'] : $type_post_fb_user;

		echo '<div>';
		echo '<label for="">' . __( 'Publish on Facebook with different title:', 'wdfb' ) . '</label>';
		echo '<input type="text" class="widefat" name="wdfb_metabox_publishing_title" id="wdfb_metabox_publishing_title" value="' . esc_attr( $title ) . '" />';
		echo __( '<p><small>Leave this value blank to use the post title.</small></p>', 'wdfb' );
		echo '</div>';
		//If post is not already published on facebook, and autopost is off
		//If post is not already published on facebook, and autopost is on, and its an old post
		if ( ( ! $wdfb_published_on_fb && ! $opts['allow_autopost'] ) || ( ! $wdfb_published_on_fb && $post_status == 'publish' ) ) {
			echo '<div>';
			echo '	<input type="checkbox" name="wdfb_metabox_publishing_publish" id="wdfb_metabox_publishing_publish" value="1" ' . checked( $stored_publish, true, false ) . ' />';
			echo '	<label for="wdfb_metabox_publishing_publish">' . __( 'I want to publish this post to Facebook', 'wdfb' ) . '</label>';
			echo __( '<p><small>If checked, the post will be unconditionally published on Facebook</small></p>', 'wdfb' );
			echo '</div>';

			echo '<div class="wdfb_perms_not_granted" style="display:none">' .
			     '<div class="error below-h2">' .
			     '<p>' .
			     __( "Your app doesn't have enough permissions to publish on Facebook", 'wdfb' ) . '<br />' .
			     '<a class="wdfb_grant_perms" href="#" data-wdfb_perms="' .
			     Wdfb_Permissions::get_publisher_permissions() .
			     '" data-wdfb_locale="' . wdfb_get_locale() . '" >' .
			     __( 'Grant needed permissions now', 'wdfb' ) .
			     '</a>' .
			     '</p>' .
			     '</div>' .
			     '</div>';

			$user        = wp_get_current_user();
			$fb_accounts = $this->_get_api_accounts( $user->ID );
			/*
			$fb_accounts = get_user_meta($user->ID, 'wdfb_api_accounts', true);
			$fb_accounts = isset($fb_accounts['auth_accounts']) ? $fb_accounts['auth_accounts'] : array();
			*/

			if ( $fb_accounts && current_user_can('manage_options') ) {
				echo '<div>';
				echo '	<label for="wdfb_metabox_publishing_account">' . __( 'Publish to wall of this Facebook account:', 'wdfb' ) . '</label>';
				echo '	<select name="wdfb_metabox_publishing_account" id="wdfb_metabox_publishing_account">';
				foreach ( $fb_accounts as $aid => $aval ) {
					$selected = selected( $stored_publishing_user, $aid, false );
					echo "<option value='{$aid}' {$selected}>{$aval}</option>";
				}
				echo '	</select>';
				echo '<br />';
				echo '<label for="post_as_page">' . __( "If posting to a page, post <b>AS</b> page", "wdfb" ) . '</label> ' .
				     '<input type="checkbox" name="wdfb_post_as_page" id="post_as_page" value="1" />';
				echo '</div>';
				echo '<p class="wdfb_perms_not_granted"><small>' . __( 'Please make sure that you granted extended permissions to your Facebook App', 'wdfb' ) . '</small></p>';
			}
		}else{
			if( !empty( $opts['allow_skip_autopost'] ) && $opts['allow_skip_autopost'] == 1 ) {
				echo '<div>';
				echo '	<input type="checkbox" name="wdfb_metabox_publishing_skip_publish" id="wdfb_metabox_publishing_skip_publish" value="1" ' . checked( $stored_publish, true, false ) . ' />';
				echo '	<label for="wdfb_metabox_publishing_publish">' . __( 'Do not publish this post to Facebook', 'wdfb' ) . '</label>';
				echo __( '<p><small>If checked, the post will not be published on Facebook</small></p>', 'wdfb' );
				echo '</div>';
			}
		}
		echo '<script type="text/javascript" src="' . WDFB_PLUGIN_URL . '/js/check_permissions.js"></script>';
	}

	function _get_api_accounts( $user_id ) {
		$model = new Wdfb_Model;

		return $model->get_api_accounts( $user_id );
	}
}