<?php
/**
 * @package Make Plus
 */

/**
 * Add notices to the Admin screens.
 *
 * @since 1.6.0.
 *
 * @return void
 */
function ttfmp_add_admin_notices() {
	global $wp_version;

	if ( version_compare( $wp_version, TTFMP_APP::MIN_WP_VERSION, '<' ) ) {
		ttfmake_register_admin_notice(
			'ttfmp-wp-lt-min-version',
			sprintf(
				__( 'Make Plus requires version %1$s of WordPress or higher. Please %2$s to ensure full compatibility.', 'make-plus' ),
				TTFMP_APP::MIN_WP_VERSION,
				sprintf(
					'<a href="%1$s">%2$s</a>',
					admin_url( 'update-core.php' ),
					__( 'update WordPress', 'make' )
				)
			),
			array(
				'cap'     => 'update_core',
				'dismiss' => false,
				'screen'  => array( 'dashboard', 'plugins.php', 'update-core.php' ),
				'type'    => 'error',
			)
		);
	}
}

add_action( 'admin_init', 'ttfmp_add_admin_notices' );