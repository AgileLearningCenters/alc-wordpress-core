<?php

/**
 * @since Layouts 2.0.2
 * Class Toolset_Compatibility_Theme_Handler
 *
 * This is only abstract class and it is here to be extended by compatibility classes for themes
 */
abstract class Toolset_Compatibility_Theme_Handler {
    protected $name;
    protected $slug;

    public function __construct( $name, $slug ) {
        $this->name = $name;
        $this->slug = $slug;
        add_action( 'init', array( $this, 'run' ), 11 );
    }

    public function run() {
        $this->run_hooks();
    }

    protected abstract function run_hooks();

	/**
	 * @return bool
	 * check if Woocommerce is actice
	 * by checking active plugins in options table
	 * using build in filter
	 */
	public function is_woocommerce_active(){
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	/**
	 * @param $title
	 *
	 * @return string
	 * adjust Woocommerce page title depending if it's a product category or the shop page
	 * this is generic and used by any theme which needs this adjustment
	 * must be implemented using "get_the_archive_title" filter to override the title
	 */
	public function toolset_woocommerce_show_page_title( $title ) {
		if ( $this->is_woocommerce_active() && is_woocommerce() ) {
			if ( is_shop() ) {

				 // WooCommerce shop plays dual; as a shop page and an archive. By default, Views short code for archive title output different stuff, while, theme shows Shop Page title. Here, the title is modified to return the title of Shop Page.
				$shop_page_id = get_option( 'woocommerce_shop_page_id' );
				$title = sprintf( __( '%s', 'ddl-layouts' ), get_the_title( $shop_page_id ) );
			} else if ( is_product_category() ) {

				// Just like the above, we need to strip-off the stuff other than the category name, from the title
				$title = sprintf( __( '%s', 'ddl-layouts' ), single_cat_title( '', false ) );
			}
		}

		return $title;

	}
}