<?php

class UB_Admin_Bar_Forms {

	private static $_dashicons = array(
		'media-default',
		'media-archive',
		'media-audio',
		'media-code',
		'media-document',
		'media-interactive',
		'media-spreadsheet',
		'media-text',
		'media-video',
		'carrot',
		'menu',
		'admin-site',
		'dashboard',
		'admin-post',
		'admin-media',
		'admin-links',
		'admin-page',
		'admin-comments',
		'admin-appearance',
		'admin-plugins',
		'admin-users',
		'admin-tools',
		'admin-settings',
		'admin-network',
		'admin-home',
		'admin-generic',
		'admin-collapse',
		'welcome-write-blog',
		'welcome-add-page',
		'welcome-view-site',
		'welcome-widgets-menus',
		'welcome-comments',
		'welcome-learn-more',
		'format-aside',
		'format-image',
		'format-gallery',
		'format-video',
		'format-status',
		'format-quote',
		'format-chat',
		'format-audio',
		'camera',
		'images-alt',
		'images-alt2',
		'video-alt',
		'video-alt2',
		'video-alt3',
		'playlist-audio',
		'playlist-video',
		'controls-play',
		'controls-pause',
		'controls-forward',
		'controls-skipforward',
		'controls-back',
		'controls-skipback',
		'controls-repeat',
		'controls-volumeon',
		'controls-volumeoff',
		'image-crop',
		'image-rotate-left',
		'image-rotate-right',
		'image-flip-vertical',
		'image-flip-horizontal',
		'undo',
		'redo',
		'editor-bold',
		'editor-italic',
		'editor-ul',
		'editor-ol',
		'editor-quote',
		'editor-alignleft',
		'editor-aligncenter',
		'editor-alignright',
		'editor-insertmore',
		'editor-spellcheck',
		'editor-expand',
		'editor-contract',
		'editor-kitchensink',
		'editor-underline',
		'editor-justify',
		'editor-textcolor',
		'editor-paste-word',
		'editor-paste-text',
		'editor-removeformatting',
		'editor-video',
		'editor-customchar',
		'editor-outdent',
		'editor-indent',
		'editor-help',
		'editor-strikethrough',
		'editor-unlink',
		'editor-rtl',
		'editor-break',
		'editor-code',
		'editor-paragraph',
		'align-left',
		'align-right',
		'align-center',
		'align-none',
		'lock',
		'calendar',
		'calendar-alt',
		'visibility',
		'post-status',
		'edit',
		'trash',
		'external',
		'arrow-up',
		'arrow-down',
		'arrow-right',
		'arrow-left',
		'arrow-up-alt',
		'arrow-down-alt',
		'arrow-right-alt',
		'arrow-left-alt',
		'arrow-up-alt2',
		'arrow-down-alt2',
		'arrow-right-alt2',
		'arrow-left-alt2',
		'sort',
		'leftright',
		'randomize',
		'list-view',
		'exerpt-view',
		'grid-view',
		'share',
		'share-alt',
		'share-alt2',
		'twitter',
		'rss',
		'email',
		'email-alt',
		'facebook',
		'facebook-alt',
		'googleplus',
		'networking',
		'hammer',
		'art',
		'migrate',
		'performance',
		'universal-access',
		'universal-access-alt',
		'tickets',
		'nametag',
		'clipboard',
		'heart',
		'megaphone',
		'schedule',
		'wordpress',
		'wordpress-alt',
		'pressthis',
		'update',
		'screenoptions',
		'info',
		'cart',
		'feedback',
		'cloud',
		'translation',
		'tag',
		'category',
		'archive',
		'tagcloud',
		'text',
		'yes',
		'no',
		'no-alt',
		'plus',
		'plus-alt',
		'minus',
		'dismiss',
		'marker',
		'star-filled',
		'star-half',
		'star-empty',
		'flag',
		'location',
		'location-alt',
		'vault',
		'shield',
		'shield-alt',
		'sos',
		'search',
		'slides',
		'analytics',
		'chart-pie',
		'chart-bar',
		'chart-line',
		'chart-area',
		'groups',
		'businessman',
		'id',
		'id-alt',
		'products',
		'awards',
		'forms',
		'testimonial',
		'portfolio',
		'book',
		'book-alt',
		'download',
		'upload',
		'backup',
		'clock',
		'lightbulb',
		'microphone',
		'desktop',
		'tablet',
		'smartphone',
		'phone',
		'index-card',
		'building',
		'store',
		'album',
		'palmtree',
		'tickets-alt',
		'money',
		'smiley' );

	/**
	 * Retrieves options
	 *
	 * @param bool $key
	 * @param string $pfx
	 *
	 * @return mixed|void
	 */
	public static function get_option( $key = false, $pfx = 'wdcab' ) {
		$opts = ub_get_option( $pfx );
		if ( ! $key ) {
			return $opts;
		}

		return $opts[ $key ];
	}

	/**
	 * Renders checkbox
	 *
	 * @param $name
	 * @param string $pfx
	 *
	 * @return string
	 */
	public static function create_checkbox( $name, $pfx = 'wdcab' ) {
		$opt   = self::get_option( $name, $pfx );
		$value = @$opt[ $name ];

		return
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-yes' value='1' " . ( (int) $value ? 'checked="checked" ' : '' ) . " /> " .
			"<label for='{$name}-yes'>" . __( 'Yes', 'ub' ) . "</label>" .
			'&nbsp;' .
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-no' value='0' " . ( ! (int) $value ? 'checked="checked" ' : '' ) . " /> " .
			"<label for='{$name}-no'>" . __( 'No', 'ub' ) . "</label>" .
			"";
	}

	/**
	 * Creates enable box
	 */
	public static function create_enabled_box() {
		echo self::create_checkbox( 'enabled' );
	}

	/**
	 * Renders disable/enable setting
	 */
	public static function create_disable_box() {
		$_menus   = array(
			'wp-logo'     => __( 'WordPress menu', 'ub' ),
			'site-name'   => __( 'Site menu', 'ub' ),
			'my-sites'    => __( 'My Sites', 'ub' ),
			'new-content' => __( 'Add New', 'ub' ),
			'comments'    => __( 'Comments', 'ub' ),
			'updates'     => __( 'Updates', 'ub' ),
		);
		$disabled = self::get_option( 'disabled_menus' );
		$disabled = is_array( $disabled ) ? $disabled : array();

		echo '<input type="hidden" name="wdcab[disabled_menus]" value="" />';
		foreach ( $_menus as $id => $lbl ) {
			$checked = in_array( $id, $disabled ) ? 'checked="checked"' : '';
			echo '' .
			     "<input type='checkbox' name='wdcab[disabled_menus][]' id='wdcab-disabled_menus-{$id}' value='{$id}' {$checked}>" .
			     "&nbsp;" .
			     "<label for='wdcab-disabled_menus-{$id}'>{$lbl}</label>" .
			     "<br />";
		}
	}

	/**
	 * Renders disable/enable setting
	 *
	 * @param string $name roles key
	 * @param string $pfx
	 */
	public static function create_roles_box( $name, $pfx = 'wdcab' ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$roles = $wp_roles->get_names();

		$opt = self::get_option( $name, $pfx );

		$opt = is_array( $opt ) ? $opt : ( $opt === "" ?  array() : array_keys($roles) ) ;
		echo "<input type='hidden' name='{$pfx}[{$name}]' value='' />";
		foreach ( $roles as $role_value => $role_name ) {
			$checked = in_array( $role_value, $opt ) ? 'checked="checked"' : '';
			$current_user_has_role = self::_current_user_has_role( $role_value );
			$is_current_user =  $current_user_has_role ? "ub_adminbar_is_current_user" : "";
			$icon = $current_user_has_role ? sprintf("<span class='dashicons dashicons-info' title='%s'></span", __("Current user has this role", "ub") ) : "";
			echo "<p><input type='checkbox' name='{$pfx}[{$name}][{$role_name}]' id='{$pfx}-{$name}-{$role_value}' value='{$role_value}' {$checked}>"
			     . "&nbsp;"
			     . "<label class='{$is_current_user}' for='{$pfx}-{$name}-{$role_value}'>{$role_name}&nbsp;{$icon}</label></p>";
		}
	}

	/**
	 * Checks if current user has $role
	 *
	 * @param $role
	 *
	 * @since 1.8.1.2
	 *
	 * @return bool
	 */
	private static function _current_user_has_role( $role ){
		global $current_user;
		return in_array( $role, (array) $current_user->roles );
	}

	/**
	 * Renders submenu roles
	 *
	 * @param UB_Admin_Bar_Menu $menu |null
	 */
	public static function create_submenu_roles( $menu = null ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$roles = $wp_roles->get_names();

		if ( $menu instanceof UB_Admin_Bar_Menu ) {
			$opts = $menu->menu->menu_roles;
			$opts = is_array( $opts ) ? $opts : $roles;
			foreach ( $roles as $role_value => $role_name ) {
				$checked = array_key_exists( $role_value, $opts ) ? 'checked="checked"' : '';
				$name    = "ub_ab_prev[{$menu->id}][menu_roles][{$role_value}]";
				$id      = "ub_ab_prev_{$menu->id}_menu_roles_{$role_value}";
				$current_user_has_role = self::_current_user_has_role( $role_value );
				$icon = $current_user_has_role ? sprintf("&nbsp;<span class='dashicons dashicons-info' title='%s'></span", __("Current user has this role", "ub") ) : "";
				$label_class = $current_user_has_role ? "ub_adminbar_is_current_user" : "";
				?>
				<p>
					<input id="<?php echo $id ?>" type='checkbox' name='<?php echo $name ?>' <?php echo $checked; ?> >
					<label class="<?php echo $label_class; ?>" for="<?php echo $id ?>"><?php echo $role_name . $icon?></label>
				</p>
			<?php
			}
		} else {
			foreach ( $roles as $role_value => $role_name ) {
				$name = "ub_ab_tmp[][menu_roles][{$role_value}]";
				$id   = "ub_ab_tmp__menu_roles_{$role_value}";
				?>
				<p>
					<input type="checkbox" checked="checked" name="<?php echo $name ?>" id="<?php $id ?>"/>
					<label for="<?php echo $id ?>"><?php echo $role_name ?></label>
				</p>
			<?php
			}
		}

	}

	/**
	 * Renders dashicons radio inputs
	 *
	 * @param UB_Admin_Bar_Menu $menu|null
	 */
	public static function render_dashicons_radios( $menu = null ) {
		?>
		<ul class="ub_adminbar_dashicons">

			<?php
		if ( $menu instanceof UB_Admin_Bar_Menu ) {
			foreach (  self::$_dashicons  as $icon_name ):
				$name = "ub_ab_prev[{$menu->id}][dashicons]";
				$id   = "ub_ab_prev_{$menu->id}_dashicons_{$icon_name}";
				$title = str_replace( "-", " ", ucfirst( $icon_name ) );
				?>
				<li class="<?php echo $menu->menu->dashicons === $icon_name ? 'selected' : ''; ?>" >
					<input <?php echo isset( $menu->menu->dashicons ) ?  checked( $menu->menu->dashicons, $icon_name, false ) : ""; ?> title="<?php echo $title ?>" type="radio"  value="<?php echo $icon_name ?>" name="<?php echo $name ?>" id="<?php echo $id ?>"/>
					<label title="<?php echo $title ?>" for="<?php echo $id ?>">
						<span class="dashicons dashicons-<?php echo $icon_name ?>"></span>
					</label>
				</li>
			<?php endforeach;
		}else{
			foreach (  self::$_dashicons  as $icon_name ):
				$name = "ub_ab_tmp[][dashicons]";
				$id   = "ub_ab_tmp_dashicons_{$icon_name}";
				$title = str_replace( "-", " ", ucfirst( $icon_name ) );
				?>
				<li>
					<input title="<?php echo $title ?>" type="radio"  value="<?php echo $icon_name ?>" name="<?php echo $name ?>" id="<?php echo $id ?>"/>
					<label title="<?php echo $title ?>" for="<?php echo $id ?>">
						<span class="dashicons dashicons-<?php echo $icon_name ?>"></span>
					</label>
				</li>
			<?php
			endforeach;
		}


			?>
		</ul>
	<?php
	}

}