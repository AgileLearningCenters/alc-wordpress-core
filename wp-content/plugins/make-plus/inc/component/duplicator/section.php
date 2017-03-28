<?php
/**
 * @package Make Plus
 */

/**
 * Class MAKEPLUS_Component_Duplicator_Section
 *
 * Enable duplication of individual Builder sections.
 *
 * @since 1.2.0.
 * @since 1.7.0. Changed class name from TTFMP_Section_Duplicator.
 */
class MAKEPLUS_Component_Duplicator_Section implements MAKEPLUS_Util_HookInterface {
	/**
	 * Indicator of whether the hook routine has been run.
	 *
	 * @since 1.7.0.
	 *
	 * @var bool
	 */
	private static $hooked = false;

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

		// Add necessary scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'make_builder_js_dependencies', array( $this, 'admin_add_js_dependencies' ) );

		// Add a duplicate link
		add_filter( 'make_builder_section_links', array( $this, 'builder_section_footer_links' ) );

		// Add callback for AJAX function
		// add_action( 'wp_ajax_ttf_duplicate_section', array( $this, 'duplicate_section' ) );

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
	 * Enqueue the JS and CSS for the admin.
	 *
	 * @since 1.0.0.
	 *
	 * @hooked action admin_enqueue_scripts
	 *
	 * @param string $hook_suffix    The suffix for the screen.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Only load resources if they are needed on the current page
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) || ! ttfmake_post_type_supports_builder( get_post_type() ) ) {
			return;
		}

		wp_register_script(
			'ttfmp-duplicate-section',
			makeplus_get_plugin_directory_uri() . 'js/duplicator/section.js',
			array(),
			MAKEPLUS_VERSION,
			true
		);

		wp_enqueue_style(
			'ttfmp-duplicate-section',
			makeplus_get_plugin_directory_uri() . 'css/duplicator/section.css',
			array(),
			MAKEPLUS_VERSION
		);
	}

	public function admin_add_js_dependencies( $deps ) {
		if ( ! is_array( $deps ) ) {
			$deps = array();
		}

		return array_merge( $deps, array(
			'ttfmp-duplicate-section'
		) );
	}

	/**
	 * Add a link to duplicate the section.
	 *
	 * @since 1.2.0.
	 *
	 * @hooked filter make_builder_section_links
	 *
	 * @param array $links    The existing links.
	 *
	 * @return array          The new links.
	 */
	public function builder_section_footer_links( $links ) {
		// Add the duplicate link
		$links[60] = array(
			'class' => 'ttfmp-duplicate-section',
			'href'  => '#',
			'label' => __( 'Duplicate', 'make-plus' ),
			'title' => __( 'Duplicate section', 'make-plus' ),
		);

		return $links;
	}
}