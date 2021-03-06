<?php
/**
 * Manage Add-ons.
 *
 * Add-ons are stored in the directory /app/addon/<addon_name>/
 * Each Add-on must provide a file called `addon-<addon_name>.php`
 * This file must define class MS_Addon_<addon_name>.
 * This object is reponsible to initialize the the add-on logic.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Addon extends MS_Model_Option {

	/**
	 * Add-on name constants.
	 *
	 * @deprecated Since 1.1.0 the Add-On constants are deprecated.
	 *             Use the appropriate hooks to register new addons!
	 *             Example: See the "Taxamo" addon
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	const ADDON_MULTI_MEMBERSHIPS = 'multi_memberships';
	const ADDON_POST_BY_POST = 'post_by_post';
	const ADDON_URL_GROUPS = 'url_groups';
	const ADDON_CPT_POST_BY_POST = 'cpt_post_by_post';
	const ADDON_TRIAL = 'trial';
	const ADDON_MEDIA = 'media';
	const ADDON_SHORTCODE = 'shortcode';
	const ADDON_AUTO_MSGS_PLUS = 'auto_msgs_plus';
	const ADDON_SPECIAL_PAGES = 'special_pages';
	const ADDON_ADV_MENUS = 'adv_menus';
	const ADDON_ADMINSIDE = 'adminside';
	const ADDON_MEMBERCAPS = 'membercaps';
	const ADDON_MEMBERCAPS_ADV = 'membercaps_advanced';

	/**
	 * List of all registered Add-ons
	 *
	 * Related hook: ms_model_addon_register
	 *
	 * @var array {
	 *     @key <string> The add-on ID.
	 *     @value object {
	 *         The add-on data.
	 *
	 *         $name  <string>  Display name
	 *         $parent  <string>  Empty/The Add-on ID of the parent
	 *         $description  <string>  Description
	 *         $footer  <string>  For the Add-ons list
	 *         $icon  <string>  For the Add-ons list
	 *         $class  <string>  For the Add-ons list
	 *         $details  <array of HTML elements>  For the Add-ons list
	 *     }
	 * }
	 */
	static private $_registered = array();

	/**
	 * Used by function `flush_list`
	 *
	 * @since  1.0.0
	 *
	 * @var bool
	 */
	static private $_reload_files = false;

	/**
	 * List of add-on files to load when plugin is initialized.
	 *
	 * @since  1.0.0
	 *
	 * @var array of file-paths
	 */
	protected $addon_files = array();

	/**
	 * Add-ons array.
	 *
	 * @since  1.0.0
	 *
	 * @var array {
	 *     @key <string> The add-on ID.
	 *     @value <boolean> The add-on enbled status (always true).
	 * }
	 */
	protected $active = array();

	/**
	 * Initalize Object Hooks
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->add_action( 'ms_model_addon_flush', 'flush_list' );
	}

	/**
	 * Returns a list of all registered Add-Ons
	 *
	 * @since  1.0.0
	 * @return array Add-on lisl
	 */
	static public function get_addons() {
		static $Done = false;
		$res = null;

		if ( ! $Done || self::$_reload_files ) {
			self::$_registered = array();
			$addons = array();
			$Done = true;
			self::load_core_addons();

			// Register core add-ons
			$addons = self::get_core_list();

			/**
			 * Register new addons.
			 *
			 * @since  1.0.0
			 */
			$addons = apply_filters(
				'ms_model_addon_register',
				$addons
			);

			// Sanitation and populate default fields.
			foreach ( $addons as $key => $data ) {
				self::$_registered[$key] = $data->name;

				$addons[$key]->id = $key;
				$addons[$key]->active = self::is_enabled( $key );
				$addons[$key]->title = $data->name;

				if ( isset( $addons[$key]->icon ) ) {
					$addons[$key]->icon = '<i class="' . $addons[$key]->icon . '"></i>';
				} else {
					$addons[$key]->icon = '<i class="wpmui-fa wpmui-fa-puzzle-piece"></i>';
				}

				if ( empty( $addons[$key]->action ) ) {
					$addons[$key]->action = array();
					$addons[$key]->action[] = array(
						'id' => 'ms-toggle-' . $key,
						'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
						'value' => self::is_enabled( $key ),
						'class' => 'toggle-plugin',
						'ajax_data' => array(
							'action' => MS_Controller_Addon::AJAX_ACTION_TOGGLE_ADDON,
							'field' => 'active',
							'addon' => $key,
						),
					);
					$addons[$key]->action[] = MS_Helper_Html::save_text( null, false, true );
				}

				/**
				 * Add custom Actions or remove default actions
				 *
				 * @since  1.0.0
				 */
				$addons[$key]->action = apply_filters(
					'ms_model_addon_action-' . $key,
					$addons[$key]->action,
					$addons[$key]
				);
			}

			natcasesort( self::$_registered );
			foreach ( self::$_registered as $key => $dummy ) {
				self::$_registered[$key] = $addons[$key];
			}

			/**
			 * The Add-on list is prepared. Initialize the addons now.
			 *
			 * @since  1.0.0
			 */
			do_action( 'ms_model_addon_initialize' );
		}

		return self::$_registered;
	}

	/**
	 * Force to reload the add-on list
	 *
	 * Related action hooks:
	 * - ms_model_addon_flush
	 *
	 * @since  1.0.0
	 */
	public function flush_list() {
		self::$_reload_files = true;
		self::get_addons();
	}

	/**
	 * Checks the /app/addon directory for a list of all addons and loads these
	 * files.
	 *
	 * @since  1.0.0
	 */
	static protected function load_core_addons() {
		$model = MS_Factory::load( 'MS_Model_Addon' );
		$root_path = trailingslashit( dirname( dirname( MS_Plugin::instance()->dir ) ) );
		$plugin_dir = substr( MS_Plugin::instance()->dir, strlen( $root_path ) );
		$addon_dir = $plugin_dir . 'app/addon/';

		if ( empty( $model->addon_files ) || self::$_reload_files ) {
			// In Admin dashboard we always refresh the addon-list...
			self::$_reload_files = false;

			$mask = $root_path . $addon_dir . '*/class-ms-addon-*.php';
			$addons = glob( $mask );

			$model->addon_files = array();
			foreach ( $addons as $file ) {
				$model->addon_files[] = substr( $file, strlen( $root_path ) );
			}

			/**
			 * Allow other plugins/themes to register custom addons
			 *
			 * @since  1.0.0
			 *
			 * @var array
			 */
			$model->addon_files = apply_filters(
				'ms_model_addon_files',
				$model->addon_files
			);

			$model->save();
		}

		// Loop all recignized Add-ons and initialize them.
		foreach ( $model->addon_files as $file ) {
			$addon = $root_path . $file;

			// Get class-name from file-name
			$class = basename( $file );
			$class = str_replace( '.php', '', $class );
			$class = implode( '_', array_map( 'ucfirst', explode( '-', $class ) ) );
			$class = substr( $class, 6 ); // remove 'Class_' prefix

			if ( file_exists( $addon ) ) {
				if ( ! class_exists( $class ) ) {
					include_once $addon;
				}

				if ( class_exists( $class ) ) {
					MS_Factory::load( $class );
				}
			}
		}

		/**
		 * Allow custom addon-initialization code to run
		 *
		 * @since  1.0.0
		 */
		do_action( 'ms_model_addon_load' );
	}

	/**
	 * Verify if an add-on is enabled
	 *
	 * @since  1.0.0
	 *
	 * @var string $addon The add-on type.
	 * @return boolean True if enabled.
	 */
	static public function is_enabled( $addon ) {
		$model = MS_Factory::load( 'MS_Model_Addon' );
		$enabled = ! empty( $model->active[ $addon ] );

		if ( $enabled ) {
			// Sub-addons are considered enabled only when the parent add-on is enabled also.
			switch ( $addon ) {
				case self::ADDON_MEMBERCAPS_ADV:
					$enabled = self::is_enabled( self::ADDON_MEMBERCAPS );
					break;
			}
		}

		return apply_filters(
			'ms_model_addon_is_enabled_' . $addon,
			$enabled
		);
	}

	/**
	 * Enable an add-on type in the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var string $addon The add-on type.
	 */
	public function enable( $addon ) {
		$this->refresh();
		$this->active[ $addon ] = true;
		$this->save();

		do_action( 'ms_model_addon_enable', $addon, $this );
	}

	/**
	 * Disable an add-on type in the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var string $addon The add-on type.
	 */
	public function disable( $addon ) {
		$this->refresh();
		unset( $this->active[ $addon ] );
		$this->save();

		do_action( 'ms_model_addon_disable', $addon, $this );
	}

	/**
	 * Toggle add-on type status in the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var string $addon The add-on type.
	 */
	public function toggle_activation( $addon, $value = null ) {
		if ( null === $value ) {
			$value = self::is_enabled( $addon );
		}

		if ( $value ) {
			$this->disable( $addon );
		} else {
			$this->enable( $addon );
		}

		do_action( 'ms_model_addon_toggle_activation', $addon, $this );
	}

	/**
	 * Enable add-on necessary to membership.
	 *
	 * @since  1.0.0
	 *
	 * @var string $addon The add-on type.
	 */
	public function auto_config( $membership ) {
		if ( $membership->trial_period_enabled ) {
			$this->enable( self::ADDON_TRIAL );
		}

		do_action( 'ms_model_addon_auto_config', $membership, $this );
	}

	/**
	 * Returns a list of all registered Add-Ons.
	 * Alias for the `get_addons()` function.
	 *
	 * @since  1.0.0
	 * @return array List of all registered Add-ons.
	 */
	public function get_addon_list() {
		return self::get_addons();
	}

	/**
	 * Returns Add-On details for the core add-ons in legacy format.
	 * New Add-ons are stored in the /app/addon folder and use the
	 * ms_model_addon_register hook to provide these informations.
	 *
	 *    **       This function should not be extended       **
	 *    **  Create new Add-ons in the app/addon/ directory  **
	 *
	 * @since  1.0.0
	 * @return array List of Add-ons
	 */
	static private function get_core_list() {
		$settings = MS_Factory::load( 'MS_Model_Settings' );

		$options_text = sprintf(
			'<i class="dashicons dashicons dashicons-admin-settings"></i> %s',
			__( 'Options available', MS_TEXT_DOMAIN )
		);

		$list[self::ADDON_MULTI_MEMBERSHIPS] = (object) array(
			'name' => __( 'Multiple Memberships', MS_TEXT_DOMAIN ),
			'description' => __( 'Your members can join more than one membership at the same time.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-forms',
		);

		$list[self::ADDON_TRIAL] = (object) array(
			'name' => __( 'Trial Period', MS_TEXT_DOMAIN ),
			'description' => __( 'Allow your members to sign up for a free membership trial. Trial details can be configured separately for each membership.', MS_TEXT_DOMAIN ),
		);

		$list[self::ADDON_POST_BY_POST] = (object) array(
			'name' => __( 'Individual Posts', MS_TEXT_DOMAIN ),
			'description' => __( 'Protect individual Posts instead of Categories.', MS_TEXT_DOMAIN ),
		);

		$list[self::ADDON_CPT_POST_BY_POST] = (object) array(
			'name' => __( 'Individual Custom Posts', MS_TEXT_DOMAIN ),
			'description' => __( 'Protect individual Posts of a Custom Post Type.', MS_TEXT_DOMAIN ),
		);

		$list[self::ADDON_MEDIA] = (object) array(
			'name' => __( 'Media Protection', MS_TEXT_DOMAIN ),
			'description' => __( 'Protect Images and other Media-Library content.', MS_TEXT_DOMAIN ),
			'footer' => $options_text,
			'icon' => 'dashicons dashicons-admin-media',
			'class' => 'ms-options',
			'details' => array(
				array(
					'id' => 'masked_url',
					'before' => esc_html( trailingslashit( get_option( 'home' ) ) ),
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'title' => __( 'Mask download URL:', MS_TEXT_DOMAIN ),
					'value' => $settings->downloads['masked_url'],
					'data_ms' => array(
						'field' => 'masked_url',
						'action' => MS_Controller_Settings::AJAX_ACTION_UPDATE_SETTING,
						'_wpnonce' => true, // Nonce will be generated from 'action'
					),
				),
				array(
					'id' => 'protection_type',
					'type' => MS_Helper_Html::INPUT_TYPE_RADIO,
					'title' => __( 'Protection method', MS_TEXT_DOMAIN ),
					'desc' => __( 'You can change the way that Membership2 changes the default URL to your WordPress media library files.<br>This is done for increased protection by hiding the real filename and path.', MS_TEXT_DOMAIN ),
					'value' => $settings->downloads['protection_type'],
					'field_options' => MS_Rule_Media_Model::get_protection_types(),
					'data_ms' => array(
						'field' => 'protection_type',
						'action' => MS_Controller_Settings::AJAX_ACTION_UPDATE_SETTING,
						'_wpnonce' => true, // Nonce will be generated from 'action'
					),
				),
				array(
					'id' => 'advanced',
					'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
					'title' => __( 'Protect Individual Media files', MS_TEXT_DOMAIN ),
					'desc' => __( 'Enable this to display a new tab in "Membership2" where you can manually modify access to each media library item.<br>Default: When this option is disabled then the parent-post controls the access to the media file.', MS_TEXT_DOMAIN ),
					'value' => self::is_enabled( MS_Addon_Mediafiles::ID ),
					'data_ms' => array(
						'action' => MS_Controller_Addon::AJAX_ACTION_TOGGLE_ADDON,
						'field' => 'active',
						'addon' => MS_Addon_Mediafiles::ID,
						'_wpnonce' => true, // Nonce will be generated from 'action'
					),
				),
			),
		);

		$list[self::ADDON_SHORTCODE] = (object) array(
			'name' => __( 'Shortcode Protection', MS_TEXT_DOMAIN ),
			'description' => __( 'Protect Shortcode-Output via Memberships.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-editor-code',
		);

		$list[self::ADDON_URL_GROUPS] = (object) array(
			'name' => __( 'URL Protection', MS_TEXT_DOMAIN ),
			'description' => __( 'URL Protection will protect pages by the URL. This rule overrides all other rules, so use it carefully.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-admin-links',
		);

		$list[self::ADDON_AUTO_MSGS_PLUS] = (object) array(
			'name' => __( 'Additional Automated Messages', MS_TEXT_DOMAIN ),
			'description' => __( 'Send your members automated Email responses for various additional events.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-email',
		);

		$list[self::ADDON_SPECIAL_PAGES] = (object) array(
			'name' => __( 'Protect Special Pages', MS_TEXT_DOMAIN ),
			'description' => __( 'Change protection of special pages such as the search results.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-admin-home',
		);

		$list[self::ADDON_ADV_MENUS] = (object) array(
			'name' => __( 'Advanced menu protection', MS_TEXT_DOMAIN ),
			'description' => __( 'Adds a new option to the General Settings that controls how WordPress menus are protected.<br />Protect individual Menu-Items, replace the contents of WordPress Menu-Locations or replace each Menu individually.', MS_TEXT_DOMAIN ),
			'footer' => $options_text,
			'class' => 'ms-options',
			'details' => array(
				array(
					'id' => 'menu_protection',
					'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
					'title' => __( 'Choose how you want to protect your WordPress menus.', MS_TEXT_DOMAIN ),
					'value' => $settings->menu_protection,
					'field_options' => array(
						'item' => __( 'Protect single Menu Items (Default)', MS_TEXT_DOMAIN ),
						'menu' => __( 'Replace individual Menus', MS_TEXT_DOMAIN ),
						'location' => __( 'Overwrite contents of Menu Locations', MS_TEXT_DOMAIN ),
					),
					'data_ms' => array(
						'action' => MS_Controller_Settings::AJAX_ACTION_UPDATE_SETTING,
						'field' => 'menu_protection',
					),
				),
			),
		);

		// New since 1.1
		$list[self::ADDON_ADMINSIDE] = (object) array(
			'name' => __( 'Admin Side Protection', MS_TEXT_DOMAIN ),
			'description' => __( 'Control the pages and even Meta boxes that members can access on the admin side.', MS_TEXT_DOMAIN ),
			'icon' => 'dashicons dashicons-admin-network',
		);

		$list[self::ADDON_MEMBERCAPS] = (object) array(
			'name' => __( 'Member Capabilities', MS_TEXT_DOMAIN ),
			'description' => __( 'Manage user-capabilities on membership level.', MS_TEXT_DOMAIN ),
			'footer' => $options_text,
			'class' => 'ms-options',
			'icon' => 'dashicons dashicons-admin-users',
			'details' => array(
				array(
					'id' => 'ms-toggle-' . self::ADDON_MEMBERCAPS_ADV,
					'title' => __( 'Advanced Capability protection', MS_TEXT_DOMAIN ),
					'desc' => __( 'Allows you to protect individual WordPress Capabilities. When activated then the "User Roles" tab is replaced by a "Member Capabilities" tab where you can protect and assign individual WordPress Capabilities instead of roles.', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
					'value' => self::is_enabled( self::ADDON_MEMBERCAPS_ADV ),
					'class' => 'toggle-plugin',
					'ajax_data' => array(
						'action' => MS_Controller_Addon::AJAX_ACTION_TOGGLE_ADDON,
						'field' => 'active',
						'addon' => self::ADDON_MEMBERCAPS_ADV,
					),
				),
			),
		);

		return $list;
	}

}