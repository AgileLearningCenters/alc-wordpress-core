<?php

/**
 * Manages dynamic third-party theme integrations
 * @since 2.5
 */
class Toolset_Theme_Integration {

	const TOOLSET_THEME_CONFIG_FILE = 'toolset-config.json';
	const TOOLSET_GLOBAL_SETTING_PREFIX = 'toolset_global_';
	const TOOLSET_THEME_NO_DEFAULT = 'toolset_no_default';

	public $has_toolset_config = false;
	/**
	 * @var string
	 * holds active theme object.
	 */
	public $active_theme;
	/**
	 * @var string
	 * hold the global store type, array, single or customizer.
	 */
	public $theme_global_store_type;

	private $script_pages = array( 'dd_layouts_edit', 'ct-editor', 'view-archives-editor' );
	private $object_id;
	private $is_views_active;
	private $is_layouts_active;
	private static $instance;
	/**
	 * @var string
	 * holds the paths for the current active theme.
	 */
	public $current_theme_path;
	/**
	 * @var string
	 * holds the paths for the current active child theme, if any.
	 */
	private $child_theme_path = null;
	/**
	 * @var object
	 * holds the decoded config file JSON object if valid.
	 */
	private $toolset_config;
	/**
	 * @var object
	 * holds the complete settings object for either Views CT, WPA or a Layout and can be used to access saved theme options.
	 */
	private $current_settings_object;
	/**
	 * @var string[]
	 * holds all the option names present in the config file.
	 */
	private $theme_option_names = array();
	/**
	 * @var string[]
	 * holds only the names for the global options present in the config file.
	 */
	private $global_options = array();
	/**
	 * @var object[]
	 * holds the call data for the theme global option injection process.
	 */
	private $global_options_hooks = array();
	/**
	 * @var object[]
	 * holds all options objects, with their GUI properties.
	 */
	private $toolset_config_options = array();
	/**
	 * @var string[]
	 * holds names for all post related options.
	 */
	private $single_options = array();
	/**
	 * @var string
	 * holds one of three values view, view-template, or dd_layouts and indicates from where do we load the saved settings.
	 */
	private $current_object_type = null;
	/**
	 * @var object
	 * holds the layout object if the current object type is a layout.
	 */
	private $layouts_settings_object = null;

	public static function get_instance() {
		if ( null == Toolset_Theme_Integration::$instance ) {
			Toolset_Theme_Integration::$instance = new Toolset_Theme_Integration();
		}

		return Toolset_Theme_Integration::$instance;
	}

	public function __construct() {
		$views_condition       = new Toolset_Condition_Plugin_Views_Active();
		$this->is_views_active = $views_condition->is_met();

		$layouts_condition       = new Toolset_Condition_Plugin_Layouts_Active();
		$this->is_layouts_active = $layouts_condition->is_met();

		$this->current_theme_path = get_template_directory() . '/';

		if ( get_stylesheet_directory() != get_template_directory() ) {
			$this->child_theme_path = get_stylesheet_directory() . '/';
		}

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp', array( $this, 'execute_global_option_hooks' ), 10 );
		add_filter( 'toolset_theme_integration_get_setting', array( $this, 'filter_get_setting' ), 10, 2 );
		$this->init();
	}

	public function register_assets() {
		wp_register_script( 'toolset_theme_integration_admin_script', TOOLSET_COMMON_URL . '/res/js/toolset-theme-integration.js', array(
			'jquery',
			'wp-pointer'
		) );

		wp_localize_script( 'toolset_theme_integration_admin_script', 'Toolset_Theme_Integrations_Settings', array(
		        'strings' => array(
		               'close' => __( 'Close', 'wpv-views' )
                )
        ) );
	}

	public function enqueue_scripts() {
		if ( ! isset ( $_GET['page'] ) ) {
			return;
		}

		if ( in_array( $_GET['page'], $this->script_pages ) ) {
			wp_enqueue_script( 'toolset_theme_integration_admin_script' );
		}
	}

	public function enqueue_styles() {
		if ( ! isset ( $_GET['page'] ) ) {
			return;
		}

		if ( in_array( $_GET['page'], $this->script_pages ) ) {
			wp_enqueue_style( 'wp-pointer' );
		}
	}

	/**
	 * @since 2.5
	 * runs on constructs and call the config loading function and the filter for global options
	 */
	public function init() {
		$this->load_toolset_config();
		$this->filter_global_option_objects();
	}

	/**
	 * @since 2.5
	 * adds required actions and filter if the toolset config file is loaded correctly.
	 */
	public function admin_init() {
		$this->register_assets();
		$this->load_toolset_config();
		$this->active_theme = wp_get_theme();

		if ( $this->has_toolset_config ) {
			add_filter( 'ddl_layout_settings_save', array( $this, 'layout_save_theme_settings' ), 10, 3 );
			add_filter( 'assign_layout_to_post_object', array( $this, 'handle_layout_assignment_change' ), 99, 5 );

			add_action( 'wp_ajax_toolset_theme_integration_save_wpa_settings', array(
				$this,
				'views_wpa_save_theme_settings'
			) );

			add_action( 'wp_ajax_toolset_theme_integration_save_ct_settings', array(
				$this,
				'views_ct_save_theme_settings'
			) );

			add_action( 'wp_ajax_toolset_theme_integration_get_section_display_type', array(
				$this,
				'ajax_get_layout_assignment_type'
			) );

			add_action( 'wpddl_after_render_editor', array( $this, 'render_layouts_settings_section' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'enqueue_styles' ) );

			add_action( 'ddl_action_layout_has_been_saved', array(
				$this,
				'update_post_meta_after_layout_update'
			), 99, 3 );
			add_action( "save_post", array( $this, 'update_post_theme_settings_meta' ), 99, 1 );
			//Render the GUI in CT page when layout is deactivated
			if ( ! $this->is_layouts_active && $this->is_views_active ) {
				add_action( 'wpv_ct_editor_sections', array( $this, 'render_views_ct_settings_section' ), 40 );
				add_action( 'wpv_action_wpa_editor_section_extra', array(
					$this,
					'render_views_wpa_settings_section'
				), 40 );
			}
			$this->load_current_settings_object();
		}
	}

	/**
	 * @since 2.5
	 * filters the global options present in the config file.
	 */
	public function filter_global_option_objects() {
		if ( $this->has_toolset_config ) {
			foreach ( $this->toolset_config->sections as $section ) {
				foreach ( $section->options as $option ) {
					$this->toolset_config_options[ $option->name ] = $option;

					if ( isset( $option->store_type ) && $option->store_type == "global" ) {
						$this->global_options[] = $option->name;
						if ( $this->theme_global_store_type == 'single' ) {
							add_filter( "option_{$option->name}", array( $this, "pre_global_option_save" ), 99, 2 );
						} else if ( $this->theme_global_store_type == 'customizer' ) {
							add_filter( "theme_mod_{$option->name}", array(
								$this,
								"pre_customizer_option_value"
							), 99 );
						}
					} else {
						$this->single_options[] = $option->name;
					}
				}
			}
		}

		if ( $this->theme_global_store_type == 'array' && property_exists( $this->toolset_config, 'global_store_name' ) ) {
			add_filter( "option_{$this->toolset_config->global_store_name}", array(
				$this,
				"pre_global_option_save"
			), 99, 2 );
		}
	}

	/**
	 * returned the parsed toolset config object or null
	 * @return object|null
	 */
	public function get_toolset_config_object() {
		return $this->toolset_config;
	}

	/**
	 * @param null $post_id
	 * @param int|null $object_id
	 * loads the saved settings for either CT, WPA or Layout and can figure out settings even if no params are passed, but requires $_GET['page'] to be present with valid object ID
	 *
	 * @return $this
	 */
	public function load_current_settings_object( $post_id = null, $object_id = null ) {
		$object_type = null;

		if ( empty( $object_id ) ) {
			if ( isset( $_GET['layout_id'] ) ) {
				$object_id   = (int) $_GET['layout_id'];
				$object_type = 'dd_layouts';
			}
		}

		if ( ! empty( $object_id ) ) {
			$object_type = get_post_type( $object_id );
		}

		if ( empty( $object_id ) ) {
			if ( empty( $post_id ) ) {
				if ( isset( $_GET['post'] ) ) {
					$post_id = (int) $_GET['post'];
				}
			}

			if ( empty( $post_id ) && ( is_single() || is_page() ) ) {
				global $post;
				if ( isset( $post->ID ) ) {
					$post_id = (int) $post->ID;
				}
			}

			if ( ! empty( $post_id ) ) {
				if ( $this->is_layouts_active ) {
					$object_type = 'dd_layouts';
					$layout_slug = get_post_meta( (int) $post_id, WPDDL_LAYOUTS_META_KEY, true );
					$object_id   = apply_filters( 'ddl-get_layout_id_by_slug', null, $layout_slug );
				} else {
					if ( $this->is_views_active ) {
						$object_id   = get_post_meta( $post_id, '_views_template', true );
						$object_type = 'view-template';
					}
				}
			}

			if ( isset( $_GET['page'] ) ) {
				switch ( $_GET['page'] ) {
					case 'view-archives-editor':
						$object_id   = (int) $_GET['view_id'];
						$object_type = 'view';
						break;
					case 'ct-editor':
						$object_id   = (int) $_GET['ct_id'];
						$object_type = 'view-template';
						break;
					case 'dd_layouts_edit':
						$object_id   = (int) $_GET['layout_id'];
						$object_type = 'dd_layouts';
						break;
				}
			}

			if ( empty( $object_id ) ) {
				$object_id   = $this->fetch_queried_object_id();
				$object_type = $this->fetch_queried_object_type( $object_id );
			}
		}

		if ( ! empty( $object_type ) ) {
			switch ( $object_type ) {
				case 'view':
					$this->current_settings_object = apply_filters( 'wpv_filter_wpv_get_view_settings', array(), $object_id );
					break;
				case 'view-template':
					$this->current_settings_object = get_post_meta( $object_id, '_views_template_theme_settings', true );
					break;
				case 'dd_layouts':
					$this->current_settings_object = apply_filters( 'ddl-get_layout_settings', $object_id, true );
					$this->layouts_settings_object = apply_filters( 'ddl-get_layout_as_php_object', $object_id, true );

					if ( is_object( $this->current_settings_object ) === false ) {
						$this->current_settings_object = null;
					}
					break;
			}
		}

		$this->current_object_type = $object_type;
		$this->object_id           = $object_id;
		if ( ! empty( $this->current_settings_object ) && is_object( $this->current_settings_object ) ) {
			$this->current_settings_object = get_object_vars( $this->current_settings_object );

			if ( array_key_exists( 'toolset_theme_settings', $this->current_settings_object ) && is_object( $this->current_settings_object['toolset_theme_settings'] ) ) {
				$this->current_settings_object['toolset_theme_settings'] = get_object_vars( $this->current_settings_object['toolset_theme_settings'] );
			}
		}

		return $this;
	}

	/**
	 * @since 2.5
	 *
	 * @param $file_path
	 * returns the config file json
	 *
	 * @return bool|string
	 */
	public function load_config_file_json( $file_path ) {
		//open and read the config file and parse its json to stdClass object
		$open_config_file = fopen( $file_path, 'r' );
		$config_json      = fread( $open_config_file, filesize( $file_path ) );
		fclose( $open_config_file );

		return $config_json;
	}

	/**
	 * @since 2.5
	 * loads the config file and parse into object
	 */
	public function load_toolset_config() {
		$config_file_path = $this->current_theme_path . self::TOOLSET_THEME_CONFIG_FILE;
		if ( file_exists( $config_file_path ) ) {
			try {
				//open and read the config file and parse its json to stdClass object
				$config_json         = $this->load_config_file_json( $config_file_path );
				$config_decoded_json = json_decode( $config_json );

				if ( $this->validate_config_file_structure( $config_decoded_json ) ) {
					$this->toolset_config     = $config_decoded_json;
					$this->has_toolset_config = true;

					if ( $this->child_theme_path != null && file_exists( $this->child_theme_path . self::TOOLSET_THEME_CONFIG_FILE ) ) {
						$child_config_json         = $this->load_config_file_json( $this->child_theme_path . self::TOOLSET_THEME_CONFIG_FILE );
						$child_config_decoded_json = json_decode( $child_config_json );

						if ( $this->validate_config_file_structure( $child_config_decoded_json ) ) {
							array_merge( $child_config_decoded_json['sections'], $this->toolset_config->sections );
						}
					}

					foreach ( $this->toolset_config->sections as $section ) {
						foreach ( $section->options as $option ) {
							$this->theme_option_names[] = $option->name;
						}
					}

					if ( property_exists( $this->toolset_config, "global_store_type" ) ) {
						$this->theme_global_store_type = $this->toolset_config->global_store_type;
					}

				} else {
					throw new Exception( 'Invalid Toolset config file structure' );
				}
			} catch ( Exception $exception ) {
				do_action( 'toolset_parsing_theme_config_failed', $exception );
			}
		}
	}

	/**
	 * @since 2.5
	 *
	 * @param $content
	 * validates the config-toolset.json file structure.
	 *
	 * @return bool
	 */
	public function validate_config_file_structure( $content ) {
		if ( ! is_object( $content ) ) {
			return false;
		}

		if ( ! property_exists( $content, 'sections' ) ) {
			return false;
		}

		if ( is_array( $content->sections ) && count( $content->sections ) != 0 ) {
			foreach ( $content->sections as $section ) {
				if ( ! is_array( $section->options ) ) {
					return false;
				} else {
					foreach ( $section->options as $option ) {
						return ( property_exists( $option, 'name' ) && property_exists( $option, 'gui' ) );
					}
				}
			}
		}

		return true;
	}

	public function get_section_name( $title, $type ) {
		$title = str_replace( ' ', '_', $title . '_' . $type );
		$title = strtolower( $title );
		$title = esc_attr( $title );

		return $title;
	}

	/**
	 * @since 2.5
	 *
	 * @param $input_name
	 * @param null $default
	 * returns a sanitized input value for a specific option
	 *
	 * @return null
	 */
	public function get_input_value( $input_name, $default = null ) {
		return esc_attr( $this->get_theme_setting( $input_name ) );
	}

	/**
	 * @since 2.5
	 *
	 * @param $json
	 * @param $post
	 * @param $raw
	 * hooks to layouts to save the custom theme settings to the Layout object
	 *
	 * @return mixed
	 */
	public function layout_save_theme_settings( $json, $post, $raw ) {
		if ( isset( $_POST['theme_settings'] ) ) {
			parse_str( $_POST['theme_settings'], $theme_settings );

			if ( is_string( $theme_settings ) ) {
				$settings_array = array( $theme_settings );
				$theme_settings = $settings_array;
			}

			if ( is_array( $theme_settings ) && count( $theme_settings ) ) {
				$json['toolset_theme_settings'] = $theme_settings;
			}
		}

		return $json;
	}

	/**
	 * @since 2.5
	 * AJAX callback for toolset_theme_integration_save_ct_settings action, responsible for saving theme settings into Views CT
	 */
	public function views_ct_save_theme_settings() {
		$uid = get_current_user_id();

		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		} else {
			$ct_id = (int) $_POST['id'];
		}

		if (
			! isset( $_POST['wpnonce'] )
			|| ! wp_verify_nonce( $_POST['wpnonce'], "wpv_ct_{$ct_id}_update_properties_by_{$uid}" )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if ( empty( $_POST['theme_settings'] ) ) {
			$data = array(
				'type'    => 'data_missing',
				'message' => __( 'Wrong or missing data.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		parse_str( $_POST['theme_settings'], $theme_settings );
		$theme_settings_array                           = array();
		$theme_settings_array['toolset_theme_settings'] = $theme_settings;

		update_post_meta( $ct_id, '_views_template_theme_settings', $theme_settings_array );

		$data = array(
			'id'      => $ct_id,
			'message' => __( 'Theme settings saved', 'wpv-views' ),
		);

		$this->update_post_meta_after_views_update( $ct_id );

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 * AJAX callback for toolset_theme_integration_save_wpa_settings action, responsible for saving theme settings into Views WPA
	 */
	public function views_wpa_save_theme_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['wpnonce'] )
			|| ! wp_verify_nonce( $_POST['wpnonce'], 'wpv_nonce_editor_nonce' )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if ( empty( $_POST['theme_settings'] ) ) {
			$data = array(
				'type'    => 'data_missing',
				'message' => __( 'Wrong or missing data.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		$view_id = intval( $_POST['id'] );
		parse_str( $_POST['theme_settings'], $theme_settings );
		$view_array = get_post_meta( $view_id, '_wpv_settings', true );

		$view_array['toolset_theme_settings'] = $theme_settings;
		update_post_meta( $view_id, '_wpv_settings', $view_array );

		do_action( 'wpv_action_wpv_save_item', $view_id );

		$data = array(
			'id'      => $view_id,
			'message' => __( 'Theme settings saved', 'wpv-views' ),
		);

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 *
	 * @param $post_id
	 * Updates single post meta with single options values when the theme settings is saved in CT or Layout.
	 */
	public function update_post_theme_settings_meta( $post_id ) {
		if ( $post_id ) {
			$this->load_current_settings_object( $post_id );
			if ( $this->has_theme_settings() ) {
				$theme_settings = $this->current_settings_object['toolset_theme_settings'];
				if ( $this->has_theme_settings() ) {
					foreach ( $this->theme_option_names as $option ) {
						if ( array_key_exists( $option, $theme_settings ) ) {
							update_post_meta( $post_id, $option, $this->get_theme_setting( $option ) );
						} else {
							delete_post_meta( $post_id, $option );
						}
					}
				}
			}
		}
	}


	/**
	 * @since 2.5
	 *
	 * @param $ret
	 * @param $post_id
	 * @param $layout_slug
	 * @param $template
	 * @param $meta
	 * updates post meta when layout assignment changes.
	 */
	public function handle_layout_assignment_change( $ret, $post_id, $layout_slug, $template, $meta ) {
		$this->update_post_theme_settings_meta( $post_id );
	}

	/**
	 * @since 2.5
	 *
	 * @param $update
	 * @param $layout_id
	 * @param $json
	 * updates the post meta for posts associated with the updated layout.
	 */
	public function update_post_meta_after_layout_update( $update, $layout_id, $json ) {

		$layout_post_ids = apply_filters( 'ddl-get_layout_posts_ids', $layout_id );

		if ( ! count( $layout_post_ids ) ) {
			return;
		}

		$where_used = implode( ',', array_filter( $layout_post_ids, array( $this, 'filter_int_ids' ) ) );

		$this->load_current_settings_object( null, $layout_id );
		$this->update_posts_meta_after_settings_update( $where_used );
	}

	public function filter_int_ids( $item ) {
		return (int) $item;
	}

	/**
	 * @since 2.5
	 *
	 * @param $view_id
	 * updates the post meta for posts associated with the updated CT.
	 */
	public function update_post_meta_after_views_update( $view_id ) {

		$view_post_ids = apply_filters( 'wpv_get_posts_by_content_template', array(), $view_id );

		if ( ! count( $view_post_ids ) ) {
			return;
		}

		$where_used = implode( ',', array_filter( $view_post_ids, array( $this, 'filter_int_ids' ) ) );

		$this->load_current_settings_object( null, $view_id );
		$this->update_posts_meta_after_settings_update( $where_used );
	}

	/**
	 * @since 2.5
	 *
	 * @param $posts_ids string[]
	 * Updates multiple posts meta with single options values when the theme settings is saved in CT or Layout.
	 */
	public function update_posts_meta_after_settings_update( $posts_ids ) {
		global $wpdb;
		$posts_ids_array = explode( ',', $posts_ids );
		if ( $this->has_theme_settings() ) {
			foreach ( $this->single_options as $option ) {
				$meta_key   = esc_sql( $option );
				$meta_value = esc_sql( $this->get_theme_setting( $meta_key ) );

				$wpdb->query( "UPDATE {$wpdb->postmeta} set meta_value='{$meta_value}' WHERE post_id IN ({$posts_ids}) AND meta_key='{$meta_key}'" );

				$posts_with_key     = $wpdb->get_results( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '{$meta_key}' AND post_id IN ({$posts_ids})" );
				$posts_with_key_ids = array_map( create_function( '$o', 'return $o->post_id;' ), $posts_with_key );
				$posts_with_no_keys = array_diff( $posts_ids_array, $posts_with_key_ids );

				foreach ( $posts_with_no_keys as $post ) {
					$this->update_post_theme_settings_meta( $post );
				}

			}
		}
	}

	/**
	 * @since 2.5
	 * Returns the section types to be displayed based on the layout assignment
	 */
	public function get_layout_assignment_type( $layout_id ) {
		$archives     = apply_filters( 'ddl-get_layout_loops', $layout_id );
		$single       = WPDD_Utils::layout_assigned_count( $layout_id );
		$section_type = null;
		$this->load_current_settings_object( null, $layout_id );
		if ( $this->has_theme_setting_by_key( 'toolset_layout_to_cred_form' ) ) {
			$section_type = 'posts-cred';
		}

		if ( count( $archives ) > 0 ) {
			$section_type = 'archive';
		}

		if ( $single ) {
			$section_type = ( $section_type == 'archive' ) ? 'shared' : 'posts';
		}

		return $section_type;
	}

	/**
	 * @since 2.5
	 * Fetches the layout assignment for a given layout
	 */
	public function ajax_get_layout_assignment_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type'    => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'toolset_theme_display_type' ) ) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}

		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1
		) {
			$data = array(
				'type'    => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		} else {
			$layout_id = (int) $_POST['id'];
		}

		$assignment_type = $this->get_layout_assignment_type( $layout_id );
		$data            = array(
			'display_type'    => $assignment_type,
			'tooltip_message' => $this->get_layouts_tip_message( $assignment_type )
		);

		wp_send_json_success( $data );
	}

	/**
	 * @since 2.5
	 * Runs the stored global option injection hooks on WP action.
	 */
	public function execute_global_option_hooks() {
		try {
			//Turn of error reporting when executing third-party methods to prevent unexpected runtime errors
			error_reporting( 0 );
			foreach ( $this->global_options_hooks as $caller_object ) {
				if ( isset( $caller_object['type'] ) ) {
					switch ( $caller_object['type'] ) {
						case "->":
							call_user_func_array( array(
								$caller_object['object'],
								$caller_object['function']
							), array_values( $caller_object['args'] ) );
							break;
						case "::":
							call_user_func_array( array(
								$caller_object['class'],
								$caller_object['function']
							), array_values( $caller_object['args'] ) );
							break;
					}
				} else {
					call_user_func_array( $caller_object['function'], array_values( $caller_object['args'] ) );
				}
			}
		} catch ( Exception $e ) {
			error_log( 'Toolset failed to overwrite theme options ' . $e->getMessage() );
		}

	}

	/**
	 * @since 2.5
	 * Returns the ID for the queried object either a Layout or CT.
	 */
	public function fetch_queried_object_id() {
		$object_id = null;
		if ( $this->is_layouts_active ) {
			if ( class_exists( 'WPDD_Layouts' ) ) {
				$object_id = WPDD_Layouts::getInstance()->get_rendered_layout_id();
			}
		} else {
			if ( $this->is_views_active ) {
				if ( is_archive() || is_search() || is_home() || is_tax() ) {
					$object_id = apply_filters( 'wpv_filter_wpv_get_current_archive', null );
				}

				if ( is_single() ) {
					global $post;
					$object_id = get_post_meta( $post->ID, '_views_template', true );
				}
			}
		}

		return $object_id;
	}

	/**
	 * @since 2.5
	 * @var $object_id - Layout, CT or WPA ID to fetch its type
	 * @return null|string
	 * Returns the post type for the queried object.
	 */
	public function fetch_queried_object_type( $object_id ) {
		if ( ! empty( $object_id ) ) {
			return get_post_type( $object_id );
		}

		return null;
	}

	/**
	 * @since 2.5
	 * @return bool
	 * checks whether the settings object has a toolset_theme_settings property
	 */
	public function has_theme_settings() {
		return ( is_array( $this->current_settings_object ) && isset( $this->current_settings_object['toolset_theme_settings'] ) );
	}

	/**
	 * @since 2.5
	 * @return bool
	 * checks whether the settings object has a specific settings key
	 */
	public function has_theme_setting_by_key( $key ) {
		return ( $this->has_theme_settings() && array_key_exists( $key, $this->current_settings_object['toolset_theme_settings'] ) );
	}

	/**
	 * @since 2.5
	 *
	 * @param $value - null value to be populated with fetched settings value
	 * @@param $setting_key - name of the setting to be retrieved
	 *
	 * @return mixed
	 * returns the value for a specific setting, and used in a filter
	 */
	public function filter_get_setting( $value, $setting_key ) {
		if ( ! empty( $setting_key ) ) {
			$this->load_current_settings_object();

			return $this->get_theme_setting( $setting_key );
		}

		return $value;
	}

	/**
	 * @since 2.5
	 *
	 * @param $key - the setting name
	 * @param $default - default, which will be returned if no value is found in the settings object
	 *
	 * @return mixed
	 * returns the value to be used or rendered, it decides whether to load saved or default
	 */
	public function get_theme_setting( $key, $default = self::TOOLSET_THEME_NO_DEFAULT ) {
		if ( $this->has_theme_setting_by_key( $key ) ) {
			return $this->current_settings_object['toolset_theme_settings'][ $key ];
		}

		if ( $default !== self::TOOLSET_THEME_NO_DEFAULT ) {
			return $default;
		}

		return $this->get_setting_default( $key );
	}

	/**
	 * @since 2.5
	 *
	 * @param $key - the setting name
	 *
	 * @return mixed
	 * returns the default value for a specific theme defined in the JSON file
	 */
	public function get_setting_default( $key ) {
		$key = str_replace( self::TOOLSET_GLOBAL_SETTING_PREFIX, '', $key );
		if ( array_key_exists( $key, $this->toolset_config_options ) ) {
			$option = $this->toolset_config_options[ $key ];
			if ( property_exists( $option, 'default_value' ) ) {
				return $option->default_value;
			}
		}
	}

	/**
	 * @since 2.5
	 *
	 * @param $theme_setting
	 * @param $option_key
	 * This solves the issue with options that are being loaded way before Layouts or Views are initialised so we cannot apply our settings
	 * on those, so to solves this we store the information of the function that called the get_option function with a specific option_name
	 * and recall this function again on wp action, which at the time our plugins are ready and override the settings successfully.
	 *
	 * This is not the most proper fix in the world, but why we cannot provide an alternative fix is we don't have our code base initialised that early
	 * so this seems like the only possible fix now.
	 *
	 * @return array|mixed
	 */
	public function pre_global_option_save( $theme_setting, $option_key ) {
		$object_id = null;

		//Check is WP is initialised, if    not, trace the call and add an action to refresh the options again
		if ( ! did_action( 'wp' ) ) {
			$call_stack    = debug_backtrace();
			$is_next_call  = false;
			$caller_object = null;
			foreach ( $call_stack as $call ) {
				if ( $is_next_call ) {
					$caller_object = $call;
					break;
				}
				if ( $call['function'] == 'get_option' && count( $call['args'] ) > 0 && $call['args'][0] == $option_key ) {
					$is_next_call = true;
				}
			}
			if ( ! empty( $caller_object ) ) {
				$this->global_options_hooks[] = $caller_object;
			}
		} else {
			$object_id = $this->fetch_queried_object_id();

			$this->load_current_settings_object( null, $object_id );
		}

		if ( $this->toolset_config->global_store_type == 'array' && is_array( $theme_setting ) && $this->has_theme_settings() ) {
			$global_settings_array = array();
			foreach ( $this->global_options as $option ) {
				$option_name = self::TOOLSET_GLOBAL_SETTING_PREFIX . $option;
				if ( $this->has_theme_setting_by_key( $option_name ) ) {
					$global_settings_array[ $option ] = $this->get_theme_setting( $option_name, $theme_setting );
				}
			}
			$theme_setting = array_merge( $theme_setting, $global_settings_array );
		}

		if ( in_array( $option_key, $this->global_options ) && $this->toolset_config->global_store_type == 'string' && ! is_array( $theme_setting ) ) {
			$theme_setting = $this->global_options[ $option_key ];
		}

		return $theme_setting;
	}

	/**
	 * @since 2.5
	 *
	 * @param $default - the filter value
	 *
	 * @return mixed
	 * Hooks to Customizer theme_mod_{option_name} to override the Customizer saved settings.
	 */
	public function pre_customizer_option_value( $default ) {
		$mod_name = str_replace( 'theme_mod_', '', current_filter() );
		if ( in_array( $mod_name, $this->global_options ) ) {
			$object_id = $this->fetch_queried_object_id();
			$this->load_current_settings_object( null, $object_id );

			return $this->get_theme_setting( self::TOOLSET_GLOBAL_SETTING_PREFIX . $mod_name, $default );
		}

		return $default;
	}

	/**
	 * @since 2.5
	 *
	 * @param $layouts_section bool
	 * @param $section_type bool
	 * renders the settings area with all the sections and options
	 */
	public function render_theme_settings_area( $layouts_section = true, $section_type = null ) {
		?>
		<?php if ( $layouts_section ) {
			$assignment_type = $this->get_layout_assignment_type( $this->object_id );
			?>
            <div class="dd-layouts-wrap">
		<?php } ?>
        <div class="theme-settings-wrap">
			<?php if ( $layouts_section ) { ?>
                <span role="button" tabindex="0" class="theme-settings-toggle js-theme-settings-toggle">
                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                </span>
                <h2 class="theme-settings-title"><?php _e( 'Theme Options', 'wpv-views' );
					echo $this->render_help_tip(); ?></h2>
			<?php } ?>
            <form id="toolset_theme_settings_form">
				<?php if ( $layouts_section ) {
					$this->render_non_assigned_layout_message( $this->get_theme_setting( 'toolset_layout_to_cred_form' ), ( $assignment_type == null || $assignment_type == 'posts-cred' ) );
				} ?>
				<?php

				foreach ( $this->toolset_config->sections as $index => $settings_section ) {
					$display_section      = false;
					$display_section_type = null;
					if ( property_exists( $settings_section, 'section_type' ) ) {
						if ( $layouts_section ) {
							if ( $assignment_type == null || $assignment_type == 'posts-cred' ) {
								if ( $this->has_theme_setting_by_key( 'toolset_layout_to_cred_form' ) ) {
									if ( ( $settings_section->section_type == 'shared' || $settings_section->section_type == 'posts' ) ) {
										$display_section      = true;
										$display_section_type = $settings_section->section_type;
									}
								} else {
									$display_section      = false;
									$display_section_type = $settings_section->section_type;
								}
							} else {
								if ( $settings_section->section_type == null || $settings_section->section_type == $section_type || $section_type == 'shared' ) {
									$display_section      = true;
									$display_section_type = $settings_section->section_type;
								}
							}
						} else {
							if ( $settings_section->section_type == null || $settings_section->section_type == $section_type || $section_type == 'shared' ) {
								$display_section      = true;
								$display_section_type = $settings_section->section_type;
							}
						}
					} else {
					    if($layouts_section) {
					        if($assignment_type) {
						        $display_section      = true;
						        $display_section_type = 'shared';
                            }
                        } else {
						    $display_section      = true;
						    $display_section_type = 'shared';
                        }
                    }

					$this->render_settings_section( $settings_section, $display_section_type, $display_section );
				}
				?>
                <input type="hidden" id="toolset-theme-display-type" name="toolset_display_type_nonce"
                       value="<?php echo wp_create_nonce( 'toolset_theme_display_type' ); ?>"
            </form>
        </div>
		<?php if ( $layouts_section ) { ?>
            </div>
		<?php } ?>

		<?php
	}

	/**
	 * @since 2.5
	 *
	 * @param $option object
	 * renders individual options
	 */
	public function render_section_option( $option ) {
		?>
        <div class="theme-settings-option">
			<?php
			switch ( $option->gui->type ) {
				case 'checkbox':
				case 'radio':
					$option_ui = $this->render_option_input( $option );
					break;
				default:
					$option_ui = '<label class="theme-option-label" for="' . esc_attr( $option->name ) . '">' . $option->gui->display_name . '</label>' . $this->render_option_input( $option );
					break;
			}
			echo $option_ui;
			?>
        </div>
		<?php
	}


	/**
	 * @since 2.5
	 *
	 * @param $option object
	 * renders individual option and returns its html.
	 *
	 * @return string
	 */
	public function render_option_input( $option ) {
		$input_ui      = '';
		$global_prefix = ( property_exists( $option, 'store_type' ) && $option->store_type == "global" ? self::TOOLSET_GLOBAL_SETTING_PREFIX : "" );
		$option_name   = $global_prefix . $option->name;
		switch ( $option->gui->type ) {
			case 'text':
				$input_ui = '<input type="text" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '" value="' . $this->get_input_value( $option_name, $option->default_value ) . '" />';
				break;
			case 'checkbox':
				foreach ( $option->gui->values as $checkbox_value ) {
					$input_ui .= '<label class="theme-option-label checkbox" for="' . esc_attr( $option_name ) . '-' . esc_attr( $checkbox_value->value ) . '">';
					$input_ui .= '<input type="checkbox" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '-' . esc_attr( $checkbox_value->value ) . '" value="' . esc_attr( $checkbox_value->value ) . '"' . checked( $this->get_input_value( $option_name, null ), $checkbox_value->value, false ) . ' />';
					$input_ui .= '<span>' . $checkbox_value->text . '</span>';
					$input_ui .= '</label>';
				}
				break;
			case 'radio':
				foreach ( $option->gui->values as $radio_value ) {
					$input_ui .= '<label class="theme-option-label radio" for="' . esc_attr( $option_name ) . '-' . esc_attr( $radio_value->value ) . '">';
					$input_ui .= '<input type="radio" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '-' . esc_attr( $radio_value->value ) . '" value="' . esc_attr( $radio_value->value ) . '"' . checked( $this->get_input_value( $option_name, null ), $radio_value->value, false ) . ' />';
					$input_ui .= '<span>' . $radio_value->text . '</span>';
					$input_ui .= '</label>';
				}
				break;
			case 'select':
				$input_ui = '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '">';
				if ( property_exists( $option->gui, 'values' ) ) {
					foreach ( $option->gui->values as $option_value ) {
						if ( property_exists( $option_value, 'value' ) && property_exists( $option_value, 'text' ) ) {
							$input_ui .= '<option value="' . esc_attr( $option_value->value ) . '" ' . selected( $this->get_input_value( $option_name, null ), $option_value->value, false ) . '>' . $option_value->text . '</option>';
						}
					}
				}
				$input_ui .= '</select>';
				break;
			case 'password':
				$input_ui = '<input type="password" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '" value="' . $this->get_input_value( $option_name, null ) . '" />';
				break;
		}

		return $input_ui;
	}

	/**
	 * @since 2.5
	 *
	 * @param $section object
	 * @param $section_type string
	 * renders individual sections
	 */
	public function render_settings_section( $section, $section_type, $display_section ) {
		$theme_domain        = ( ! empty( $this->active_theme->TextDomain ) ? $this->active_theme->TextDomain : "" );
		$section_name        = $this->get_section_name( $section->section_title, $section_type );
		$disable_section     = ( $this->has_theme_setting_by_key( $section_name ) ? false : true );
		$section_type_string = '';

		if(property_exists($section,  'section_type') ) {
			if ( $section->section_type != null ) {
				$section_type_string = $section->section_type;
			} else {
				$section_type_string = 'shared';
			}
		} else {
			$section_type_string = 'shared';
        }

		?>
        <fieldset
                class="theme-settings-section <?php echo "{$section_type_string}-section"; ?> <?php echo ! $display_section ? 'hidden' : ''; ?>">
            <h3 class="theme-settings-section-title"><input type="checkbox" class="theme-integration-section-disable"
                                                            id="<?php echo $section_name; ?>"
                                                            name="<?php echo $section_name; ?>"
                                                            value="enable" <?php checked( $disable_section, false ); ?>>
                - <?php _e( $section->section_title, $theme_domain ); ?></h3>
			<?php if ( isset( $section->section_summary ) ) { ?>
                <p class="theme-settings-section-summary">
					<?php _e( $section->section_summary, $theme_domain ); ?>
                </p>
			<?php } ?>
            <div class="theme-settings-section-content">
				<?php
				foreach ( $section->options as $option ) {
					$this->render_section_option( $option );
				}
				?>
            </div>
        </fieldset>

		<?php
	}

	/**
	 * @since 2.5
	 * Renders relevant sections in Layout Edit Page
	 */
	public function render_layouts_settings_section() {
		$is_private = apply_filters( 'ddl-is_layout_private', false, $this->object_id );
		if ( ! $is_private ) {
			$section_type = $this->get_layout_assignment_type( $this->object_id );
			$this->render_theme_settings_area( true, $section_type );
		}
	}

	/**
	 * @since 2.5
	 * Renders relevant sections in Views WPA Edit Page
	 */
	public function render_views_wpa_settings_section() {
		ob_start();
		$this->render_theme_settings_area( false, 'archive' );
		?>
        <p>
						<span class="update-action-wrap auto-update">
								<span class="js-wpv-message-container"></span>
								<span class="spinner ajax-loader toolset-theme-settings-spinner"></span>
						</span>
        </p>
		<?php
		$content = ob_get_clean();
		?>
        <div class="wpv-setting-container wpv-settings-complete-output js-wpv-settings-content">

            <div class="wpv-settings-header">
                <h2>
					<?php _e( 'Theme Options', 'wpv-views' ) ?>
					<?php echo $this->render_help_tip(); ?>
                </h2>
            </div>
            <div class="wpv-setting">
				<?php
				echo $content;
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * @since 2.5
	 * Renders relevant sections in Views CT Edit Page
	 */
	public function render_views_ct_settings_section() {
		ob_start();
		$this->render_theme_settings_area( false, 'posts' );
		?>
        <p>
						<span class="update-action-wrap auto-update">
								<span class="js-wpv-message-container"></span>
								<span class="spinner ajax-loader toolset-theme-settings-spinner"></span>
						</span>
        </p>
		<?php
		$content = ob_get_clean();
		wpv_ct_editor_render_section( __( 'Theme Options ', 'wpv-views' ) . $this->render_help_tip(), 'js-wpv-theme-options-section', $content );
	}

	/**
	 * @since 2.5
	 *
	 * @param $layout_assignment string
	 *
	 * @return string
	 * returns the help tip message for layouts based on assignment
	 */
	public function get_layouts_tip_message( $layout_assignment ) {
		if ( $layout_assignment == 'archive' ) {
			return __( 'This section lets you control the settings of the theme for the archive that uses this layout.', 'wp-views' );
		} elseif ( $layout_assignment == 'posts' ) {
			return __( 'This section lets you control the settings of the theme for all the pages that use this layout.', 'wp-views' );
		} else {
			return __( 'This section lets you control the settings of the theme for content that uses this layout.', 'wp-views' );
		}
	}

	/**
	 * @since 2.5
	 * renders the tooltip pointer
	 */
	public function render_help_tip() {
		$pointer_classes = "";
		$pointer_title   = "";
		$pointer_content = "";

		if ( $this->current_object_type == 'dd_layouts' ) {
			$layout_assignment = $this->get_layout_assignment_type( $this->object_id );
			$pointer_classes   = 'wp-toolset-pointer wp-toolset-layouts-pointer';
			$pointer_title     = __( "{$this->active_theme->Name} settings for this layout", 'wp-views' );
			$pointer_content   = $this->get_layouts_tip_message( $layout_assignment );
		}

		if ( $this->current_object_type == 'view' ) {
			$pointer_classes = 'wp-toolset-pointer wp-toolset-views-pointer  wp-pointer-left';
			$pointer_title   = __( "{$this->active_theme->Name} settings for this archive", 'wp-views' );
			$pointer_content = __( 'This section lets you control the settings of the theme for this archive page.', 'wp-views' );
		}

		if ( $this->current_object_type == 'view-template' ) {
			$pointer_classes = 'wp-toolset-pointer wp-toolset-views-pointer  wp-pointer-left';
			$pointer_title   = __( "{$this->active_theme->Name} settings for this template", 'wp-views' );
			$pointer_content = __( 'This section lets you control the settings of the theme for all the pages that use this Content Template.', 'wp-views' );
		}

		return "<i class='icon-question-sign fa fa-question-circle toolset-theme-options-hint js-theme-options-hint' data-header='{$pointer_title}' data-content='{$pointer_content}' data-classes='{$pointer_classes}'></i>";
	}

	/**
	 * @since 2.5
	 * renders the message for non-assigned layouts
	 */

	public function render_non_assigned_layout_message( $saved_choice = null, $visible = true ) {
		?>
        <div class="js-toolset-non-assigned-message toolset-non-assigned-message <?php echo ! $visible ? 'hidden' : ''; ?>">
            <p><?php _e( 'The layout is not assigned to content, so you cannot control the theme settings. Once you assign this layout to content, you will see the theme options that are relevant for that kind of content.', 'wp-views' ); ?></p>
            <div class="theme-options-box-visibility-controls-wrap"><label><input type="radio" name="toolset_layout_to_cred_form" class="js-layout-used-for-cred"
                          value="" <?php checked( null, $saved_choice ); ?> > <?php _e( " I will assign this layout to content later", 'wp-views' ); ?>
                </label></div>

        <div class="theme-options-box-visibility-controls-wrap"><label><input type="radio" name="toolset_layout_to_cred_form" class="js-layout-used-for-cred"
                          value="assigned" <?php checked( 'assigned', $saved_choice ); ?> > <?php _e( " I'm using this layout to display 'single' pages", 'wp-views' ); ?>
            </label></div>

        <div class="theme-options-box-visibility-controls-wrap"><label><input type="radio" name="toolset_layout_to_cred_form" class="js-layout-used-for-cred"
                          value="archive" <?php checked( 'archive', $saved_choice ); ?> > <?php _e( " I'm using this layout to display an archive", 'wp-views' ); ?>
            </label></div>
        </div>
		<?php
	}
}

?>