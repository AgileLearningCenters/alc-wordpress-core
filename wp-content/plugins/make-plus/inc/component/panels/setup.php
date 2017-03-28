<?php
/**
 * @package Make Plus
 */

/**
 * Class MAKEPLUS_Component_Panels_Setup
 *
 * @since 1.6.0.
 * @since 1.7.0. Changed class name from TTFMP_Panels.
 */
final class MAKEPLUS_Component_Panels_Setup extends MAKEPLUS_Util_Modules implements MAKEPLUS_Util_HookInterface {
	/**
	 * An associative array of required modules.
	 *
	 * @since 1.7.0.
	 *
	 * @var array
	 */
	protected $dependencies = array(
		'theme'           => 'MAKE_APIInterface',
	);

	/**
	 * Indicator of whether the hook routine has been run.
	 *
	 * @since 1.7.0.
	 *
	 * @var bool
	 */
	private static $hooked = false;

	/**
	 * MAKEPLUS_Component_Panels_Setup constructor.
	 *
	 * @since 1.7.0.
	 *
	 * @param MAKEPLUS_APIInterface $api
	 * @param array                 $modules
	 */
	public function __construct( MAKEPLUS_APIInterface $api, array $modules = array() ) {
		// Load dependencies.
		parent::__construct( $api, $modules );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @since 1.7.0.
	 *
	 * @return void
	 */
	public function hook() {
		if ( $this->is_hooked() ) {
			return;
		}

		// Register section defaults
		add_filter( 'make_section_defaults', array( $this, 'section_defaults' ) );

		// Register section choices
		add_filter( 'make_section_choices', array( $this, 'section_choices' ), 10, 3 );

		// Add the section
		if ( is_admin() ) {
			add_action( 'after_setup_theme', array( $this, 'add_section' ), 11 );
			add_filter( 'make_get_section_json', array ( $this, 'get_section_json' ), 10, 1 );
		}

		// Enqueue scripts for the Builder UI
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Hook up the new JS dependencies
		add_filter( 'make_builder_js_dependencies', array( $this, 'admin_add_js_dependencies' ) );

		// Enqueue scripts for the frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 20 );

		// Add CSS rules to apply Customizer settings to the section
		add_action( 'make_builder_panels_css', array( $this, 'add_css' ), 10, 3 );

		// Hooking has occurred.
		self::$hooked = true;
	}

	/**
	 * Check if the hook routine has been run.
	 *
	 * @since 1.7.0.
	 *
	 * @return bool
	 */
	public function is_hooked() {
		return self::$hooked;
	}

	/**
	 * Add the Panels section to the Builder.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked action after_setup_theme
	 *
	 * @return void
	 */
	public function add_section() {
		// Bail if we aren't in the admin
		if ( ! is_admin() ) {
			return;
		}

		ttfmake_add_section(
			'panels',
			__( 'Panels', 'make-plus' ),
			makeplus_get_plugin_directory_uri() . 'css/panels/img/panels-icon.png',
			__( 'Display content with a series of accordion- or tab-styled panels.', 'make-plus' ),
			array( $this, 'save_section' ),
			array(
				'panels' => 'sections/builder-templates/panels',
				'panels-item' => 'sections/builder-templates/panels-item'
			),
			'sections/front-end-templates/panels',
			800,
			makeplus_get_plugin_directory() . 'inc/component/panels',
			$this->get_settings(),
			array( 'item' => $this->get_item_settings() )
		);
	}

	public function get_settings() {
		return array(
			100 => array(
				'type'  => 'section_title',
				'name'  => 'title',
				'label' => __( 'Enter section title', 'make-plus' ),
				'class' => 'ttfmake-configuration-title ttfmake-section-header-title-input',
				'default' => ttfmake_get_section_default( 'title', 'panels' ),
			),
			200 => array(
				'type'    => 'checkbox',
				'label'   => __( 'Full width', 'make-plus' ),
				'name'    => 'full-width',
				'default' => ttfmake_get_section_default( 'full-width', 'panels' ),
			),
			300 => array(
				'type'    => 'select',
				'name'    => 'mode',
				'label'   => __( 'Mode', 'make-plus' ),
				'class'   => 'ttfmp-panels-mode',
				'default' => ttfmake_get_section_default( 'mode', 'panels' ),
				'options' => ttfmake_get_section_choices( 'mode', 'panels' ),
			),
			400 => array(
				'type'    => 'select',
				'name'    => 'height-style',
				'label'   => __( 'Section height', 'make-plus' ),
				'class'   => 'ttfmp-panels-height-style',
				'default' => ttfmake_get_section_default( 'height-style', 'panels' ),
				'options' => ttfmake_get_section_choices( 'height-style', 'panels' ),
			),
			500 => array(
				'type'  => 'image',
				'name'  => 'background-image',
				'label' => __( 'Background image', 'make-plus' ),
				'class' => 'ttfmake-configuration-media',
				'default' => ttfmake_get_section_default( 'background-image', 'panels' ),
			),
			600 => array(
				'type'    => 'checkbox',
				'label'   => __( 'Darken background to improve readability', 'make-plus' ),
				'name'    => 'background-image-darken',
				'default' => ttfmake_get_section_default( 'background-image-darken', 'panels' ),
			),
			700 => array(
				'type'    => 'select',
				'name'    => 'background-image-style',
				'label'   => __( 'Background image style', 'make-plus' ),
				'class'   => 'ttfmp-panels-mode',
				'default' => ttfmake_get_section_default( 'background-image-style', 'panels' ),
				'options' => ttfmake_get_section_choices( 'background-image-style', 'panels' ),
			),
			800 => array(
				'type'    => 'color',
				'label'   => __( 'Background color', 'make-plus' ),
				'name'    => 'background-color',
				'class'   => 'ttfmake-panels-background-color ttfmake-configuration-color-picker',
				'default' => ttfmake_get_section_default( 'background-color', 'panels' ),
			),
		);
	}

	public function get_item_settings() {
		return array(
			100 => array(
				'type'  => 'section_title',
				'name'  => 'item-title',
				'label' => __( 'Enter panel title', 'make-plus' ),
				'class' => 'ttfmake-configuration-title',
				'default' => ttfmake_get_section_default( 'item-title', 'panels-item' ),
			)
		);
	}

	public function get_defaults() {
		return array(
			'title' => '',
			'background-image' => '',
			'background-image-style' => 'tile',
			'background-color' => '',
			'darken' => '',
			'label' => '',
			'mode' => 'accordion',
			'height-style' => 'content',
			'state' => 'open',
			'full-width' => 0,
		);
	}

	public function get_item_defaults() {
		return array(
			'item-title' => '',
			'item-content' => '',
			'image-id' => '',
		);
	}

	/**
	 * Extract the setting defaults and add them to Make's section defaults system.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked filter make_section_defaults
	 *
	 * @param array $defaults    The existing array of section defaults.
	 *
	 * @return array             The modified array of section defaults.
	 */
	public function section_defaults( $defaults ) {
		$defaults['panels'] = $this->get_defaults();
		$defaults['panels-item'] = $this->get_item_defaults();

		return $defaults;
	}

	/**
	 * Define choices for select-style settings and add them to Make's section choices system.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked filter make_section_choices
	 *
	 * @param array  $choices         The array to hold the choices.
	 * @param string $key             The setting key.
	 * @param string $section_type    The type of section.
	 *
	 * @return array                  The modified array containing the choices.
	 */
	public function section_choices( $choices, $key, $section_type ) {
		if ( count( $choices ) > 1 || ! in_array( $section_type, array( 'panels' ) ) ) {
			return $choices;
		}

		$choice_id = "$section_type-$key";

		switch ( $choice_id ) {
			case 'panels-mode' :
				$choices = array(
					'accordion' => __( 'Accordion', 'make-plus' ),
					'tabs' => __( 'Tabs', 'make-plus' ),
				);
				break;
			case 'panels-background-image-style' :
				$choices = array(
					'tile' => __( 'Tile', 'make-plus' ),
					'cover' => __( 'Cover', 'make-plus' ),
				);
				break;
			case 'panels-height-style' :
				$choices = array(
					'auto' => __( 'Set to largest panel', 'make-plus' ),
					'content' => __( 'Scale to current panel', 'make-plus' ),
				);
				break;
		}

		return $choices;
	}

	/**
	 * Filter the json representation of this section.
	 *
	 * @since 1.8.0.
	 *
	 * @hooked filter make_get_section_json
	 *
	 * @param array $defaults    The array of data for this section.
	 *
	 * @return array             The modified array to be jsonified.
	 */
	public function get_section_json( $data ) {
		if ( $data['section-type'] == 'panels' ) {
			$data = wp_parse_args( $data, $this->get_defaults() );
			$image = ttfmake_get_image_src( $data['background-image'], 'large' );

			if ( isset( $image[0] ) ) {
				$data['background-image-url'] = $image[0];
			}

			if ( isset( $data['panels-items'] ) && is_array( $data['panels-items'] ) ) {
				foreach ( $data['panels-items'] as $s => $item ) {
					$item = wp_parse_args( $item, $this->get_item_defaults() );

					// Handle legacy data layout
					$id = isset( $item['id'] ) ? $item['id']: $s;
					$data['panels-items'][$s]['id'] = $id;
					$item_image = ttfmake_get_image_src( $item['image-id'], 'large' );

					if ( isset( $item_image[0] ) ) {
						$data['panels-items'][$s]['image-url'] = $item_image[0];
					}
				}

				if ( isset( $data['item-order'] ) ) {
					$ordered_items = array();

					foreach ( $data['item-order'] as $item_id ) {
						array_push( $ordered_items, $data['panels-items'][$item_id] );
					}

					$data['panels-items'] = $ordered_items;
					unset( $data['item-order'] );
				}
			}
		}

		return $data;
	}

	/**
	 * Callback to save the Panels section data.
	 *
	 * @since 1.6.0.
	 *
	 * @param array $data    The array of section data to process.
	 *
	 * @return array         The processed array of data.
	 */
	public function save_section( $data ) {
		// Section type, state, and ID are handled by the Builder's core save function
		$ignore = array( 'section-type', 'state', 'id' );
		foreach ( $ignore as $key ) {
			if ( isset( $data[ $key ] ) ) {
				unset( $data[ $key ] );
			}
		}

		// Checkbox fields will not be set if they are unchecked.
		$checkboxes = array( 'background-image-darken' );
		foreach ( $checkboxes as $key ) {
			if ( ! isset( $data[$key] ) ) {
				$data[$key] = 0;
			}
		}

		// Get defaults and parse
		$defaults = $this->get_defaults();
		$item_defaults = $this->get_item_defaults();
		$parsed_data = wp_parse_args( $data, $defaults );

		// Clean data
		$clean_data = array();

		if ( isset( $parsed_data['title'] ) ) {
			$clean_data['title'] = $clean_data['label'] = sanitize_text_field( $parsed_data['title'] );
		}

		if ( isset( $parsed_data['mode'] ) ) {
			$clean_data['mode'] = ttfmake_sanitize_section_choice( $parsed_data['mode'], 'mode', 'panels' );
		}

		if ( isset( $parsed_data['height-style'] ) ) {
			$clean_data['height-style'] = ttfmake_sanitize_section_choice( $parsed_data['height-style'], 'height-style', 'panels' );
		}

		if ( isset( $parsed_data['background-image'] ) ) {
			$clean_data['background-image'] = ttfmake_sanitize_image_id( $parsed_data['background-image'] );
		}

		if ( isset( $parsed_data['background-image-darken'] ) ) {
			$clean_data['background-image-darken'] = absint( $parsed_data['background-image-darken'] );
		}

		if ( isset( $parsed_data['background-image-style'] ) ) {
			$clean_data['background-image-style'] = ttfmake_sanitize_section_choice( $parsed_data['background-image-style'], 'background-image-style', 'panels' );
		}

		if ( isset( $parsed_data['background-color'] ) ) {
			$clean_data['background-color'] = maybe_hash_hex_color( $parsed_data['background-color'] );
		}

		if ( isset( $parsed_data['full-width'] ) && $parsed_data['full-width'] == 1 ) {
			$clean_data['full-width'] = 1;
		} else {
			$clean_data['full-width'] = 0;
		}

		if ( isset( $parsed_data['panels-items'] ) && is_array( $parsed_data['panels-items'] ) ) {
			$clean_data['panels-items'] = array();

			foreach ( $parsed_data['panels-items'] as $i => $item ) {
				// Handle legacy data layout
				$id = isset( $item['id'] ) ? $item['id']: $i;

				$clean_item_data = array( 'id' => $id );

				if ( isset( $item['item-title'] ) ) {
					$clean_item_data['item-title'] = apply_filters( 'title_save_pre', $item['item-title'] );
				}

				if ( isset( $item['item-content'] ) ) {
					$clean_item_data['item-content'] = sanitize_post_field( 'post_content', $item['item-content'], ( get_post() ) ? get_the_ID() : 0, 'db' );
				}

				array_push( $clean_data['panels-items'], $clean_item_data );
			}
		}

		return $clean_data;
	}

	/**
	 * Enqueue scripts for the Builder UI.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked action admin_enqueue_scripts
	 *
	 * @param string $hook_suffix    The current admin page.
	 *
	 * @return void
	 */
	public function admin_scripts( $hook_suffix ) {
		// Only load resources if they are needed on the current page
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) || ! ttfmake_post_type_supports_builder( get_post_type() ) ) {
			return;
		}

		// Stylesheet
		wp_enqueue_style(
			'makeplus-panels-admin',
			makeplus_get_plugin_directory_uri() . 'css/panels/admin.css',
			array(),
			MAKEPLUS_VERSION,
			'screen'
		);

		// Model script
		wp_register_script(
			'makeplus-panels-model',
			makeplus_get_plugin_directory_uri() . 'js/panels/builder-model.js',
			array( 'makeplus-panels-item-model' ),
			MAKEPLUS_VERSION,
			true
		);

		// Model script
		wp_register_script(
			'makeplus-panels-item-model',
			makeplus_get_plugin_directory_uri() . 'js/panels/builder-item-model.js',
			array(),
			MAKEPLUS_VERSION,
			true
		);

		// View script
		wp_register_script(
			'makeplus-panels-view',
			makeplus_get_plugin_directory_uri() . 'js/panels/builder-view.js',
			array( 'makeplus-panels-model' ),
			MAKEPLUS_VERSION,
			true
		);

		// Item view script
		wp_register_script(
			'makeplus-panels-item-view',
			makeplus_get_plugin_directory_uri() . 'js/panels/builder-item-view.js',
			array( 'builder-views-item', 'makeplus-panels-item-model' ),
			MAKEPLUS_VERSION,
			true
		);
	}

	/**
	 * Filter to add new dependencies to the main Builder JS file.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked filter make_builder_js_dependencies
	 *
	 * @param array $deps    Existing array of dependencies.
	 *
	 * @return array         Modified array of dependencies.
	 */
	public function admin_add_js_dependencies( $deps ) {
		if ( ! is_array( $deps ) ) {
			$deps = array();
		}

		return array_merge( $deps, array(
			'makeplus-panels-model',
			'makeplus-panels-item-model',
			'makeplus-panels-view',
			'makeplus-panels-item-view',
		) );
	}

	/**
	 * Enqueue scripts for the section on the frontend, if the current page has it.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked action wp_enqueue_scripts
	 *
	 * @return void
	 */
	public function frontend_scripts() {
		if ( function_exists( 'ttfmake_is_builder_page' ) && ttfmake_is_builder_page() ) {
			$sections = ttfmake_get_section_data( get_the_ID() );

			// Bail if there are no sections
			if ( empty( $sections ) ) {
				return;
			}

			// Parse the sections included on the page.
			$section_types = wp_list_pluck( $sections, 'section-type' );
			$matched_sections = array_keys( $section_types, 'panels' );

			// Only enqueue if there is at least one Panels section.
			if ( ! empty( $matched_sections ) ) {
				// Stylesheet
				wp_enqueue_style(
					'makeplus-panels-frontend',
					makeplus_get_plugin_directory_uri() . 'css/panels/frontend.css',
					array(),
					MAKEPLUS_VERSION,
					'all'
				);

				// If current theme is a child theme of Make, load the stylesheet
				// before the child theme stylesheet so styles can be customized.
				if ( $this->has_module( 'theme' ) && is_child_theme() ) {
					$this->theme()->scripts()->add_dependency( 'make-main', 'makeplus-panels-frontend', 'style' );
				}

				// Determine which dependencies are needed
				$script_dependencies = array( 'jquery', 'jquery-ui-core', 'make-frontend' );
				foreach ( $matched_sections as $section_id ) {
					if ( isset( $sections[ $section_id ]['mode'] ) ) {
						$mode = sanitize_key( $sections[ $section_id ]['mode'] ); // ttfmake_get_section_choices is not available on the frontend currently :(
						if ( ! in_array( 'jquery-ui-' . $mode, $script_dependencies ) ) {
							$script_dependencies[] = 'jquery-ui-' . $mode;
						}
					}
				}

				// Script
				wp_enqueue_script(
					'makeplus-panels-frontend',
					makeplus_get_plugin_directory_uri() . 'js/panels/frontend.js',
					$script_dependencies,
					MAKEPLUS_VERSION,
					true
				);

				// Strings for JS
				wp_localize_script(
					'makeplus-panels-frontend',
					'MakePlusPanels',
					array(
						'tabsPlaceholder' => sprintf(
						// Translators: %s is a placeholder for a link to a bug report
							__( 'Panels sections in Tabs mode won\'t work correctly in the Customizer because of a bug in WordPress (%s). However, they\'ll still work on the front end.', 'make-plus' ),
							sprintf(
								'<a href="%1$s" target="_blank">%1$s</a>',
								esc_url( 'https://core.trac.wordpress.org/ticket/23225' )
							)
						)
					)
				);
			}
		}
	}

	/**
	 * Add additional CSS rules for Make's Customizer settings to style the section.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked action make_builder_panels_css
	 *
	 * @param                             $data
	 * @param                             $id
	 * @param MAKE_Style_ManagerInterface $style
	 *
	 * @return void
	 */
	public function add_css( $data, $id, MAKE_Style_ManagerInterface $style ) {
		// Secondary color
		if ( ! $style->thememod()->is_default( 'color-secondary' ) ) {
			$color_secondary = $style->thememod()->get_value( 'color-secondary' );

			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-state-hover',
					'.builder-section-panels .ui-widget-content .ui-state-hover',
					'.builder-section-panels .ui-widget-header .ui-state-hover',
					'.builder-section-panels .ui-state-focus',
					'.builder-section-panels .ui-widget-content .ui-state-focus',
					'.builder-section-panels .ui-widget-header .ui-state-focus',
					'.builder-section-panels .ui-state-hover a',
					'.builder-section-panels .ui-state-hover a:hover',
					'.builder-section-panels .ui-state-hover a:link',
					'.builder-section-panels .ui-state-hover a:visited',
					'.builder-section-panels .ui-state-focus a',
					'.builder-section-panels .ui-state-focus a:hover',
					'.builder-section-panels .ui-state-focus a:link',
					'.builder-section-panels .ui-state-focus a:visited',
					'.builder-section-panels .ui-state-active',
					'.builder-section-panels .ui-widget-content .ui-state-active',
					'.builder-section-panels .ui-widget-header .ui-state-active',
					'.builder-section-panels .ui-state-active a',
					'.builder-section-panels .ui-state-active a:link',
					'.builder-section-panels .ui-state-active a:visited',
				),
				'declarations' => array(
					'color' => $color_secondary,
				)
			) );
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-widget-header',
					'.builder-section-panels .ui-state-default',
					'.builder-section-panels .ui-widget-content .ui-state-default',
					'.builder-section-panels .ui-widget-header .ui-state-default',
				),
				'declarations' => array(
					'background-color' => $color_secondary
				)
			) );
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-state-default',
					'.builder-section-panels .ui-widget-content .ui-state-default',
					'.builder-section-panels .ui-widget-header .ui-state-default',
				),
				'declarations' => array(
					'border-color' => $color_secondary
				)
			) );
		}

		// Text color
		if ( ! $style->thememod()->is_default( 'color-text' ) ) {
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-widget-content',
					'.builder-section-panels .ui-widget-header',
					'.builder-section-panels .ui-widget-header a',
				),
				'declarations' => array(
					'color' => $style->thememod()->get_value( 'color-text' )
				)
			) );
		}

		// Detail color
		if ( ! $style->thememod()->is_default( 'color-detail' ) ) {
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-state-default',
					'.builder-section-panels .ui-widget-content .ui-state-default',
					'.builder-section-panels .ui-widget-header .ui-state-default',
					'.builder-section-panels .ui-state-default a',
					'.builder-section-panels .ui-state-default a:link',
					'.builder-section-panels .ui-state-default a:visited',
				),
				'declarations' => array(
					'color' => $style->thememod()->get_value( 'color-detail' )
				)
			) );
		}

		// Primary color
		if ( ! $style->thememod()->is_default( 'color-primary' ) ) {
			$color_primary = $style->thememod()->get_value( 'color-primary' );

			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-widget-content a',
				),
				'declarations' => array(
					'color' => $color_primary,
				)
			) );
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-state-hover',
					'.builder-section-panels .ui-widget-content .ui-state-hover',
					'.builder-section-panels .ui-widget-header .ui-state-hover',
					'.builder-section-panels .ui-state-focus',
					'.builder-section-panels .ui-widget-content .ui-state-focus',
					'.builder-section-panels .ui-widget-header .ui-state-focus',
					'.builder-section-panels .ui-state-active',
					'.builder-section-panels .ui-widget-content .ui-state-active',
					'.builder-section-panels .ui-widget-header .ui-state-active',
				),
				'declarations' => array(
					'background-color' => $color_primary,
					'border-color' => $color_primary,
				)
			) );
		}

		// Link Hover/Focus Color
		if ( ! $style->thememod()->is_default( 'color-primary-link' ) ) {
			$style->css()->add( array(
				'selectors'    => array(
					'.builder-section-panels .ui-accordion-content a:hover',
					'.builder-section-panels .ui-accordion-content a:focus',
					'.builder-section-panels .ui-tabs-panel a:hover',
					'.builder-section-panels .ui-tabs-panel a:focus',
				),
				'declarations' => array(
					'color' => $style->thememod()->get_value( 'color-primary-link' )
				)
			) );
		}

		// Remove action so the styles don't get added more than once
		remove_action( 'make_builder_panels_css', array( $this, __METHOD__ ) );
	}
}