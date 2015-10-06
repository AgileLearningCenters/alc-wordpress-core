<?php
/*
  Plugin Name: Custom Multisite Favicons
  Plugin URI:
  Description: Change the Favicon for the network
  Author: Marko Miljus (Incsub), Barry (Incsub), Philip John (Incsub)
  Version: 2.0
  Author URI:
  Network: true

  Copyright 2013 Incsub

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

include_once "favicons/UB_Blog_Favicons.php";

class ub_favicons {

	/**
	 * WP default fav
	 * @var string
	 *
	 * @since 1.8.1
	 */
	private static $_default_fav = "";

	/**
	 * @const prefix of favicons when saving and retrieving from options/sitemeta table
	 *
	 * @since 1.8.1
	 */
	const FAV_PREFIX = "ub_favicon";

    function __construct() {

	    self::$_default_fav = admin_url() . 'images/wordpress-logo.svg';

        // Admin interface
        add_action('ultimatebranding_settings_menu_images', array($this, 'manage_output'));
        add_filter('ultimatebranding_settings_menu_images_process', array($this, 'process'));

        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_head', array($this, 'global_head'));
        add_action('wp_head', array($this, 'global_head'));

        add_action('wp_before_admin_bar_render', array($this, 'change_blavatar_icon'));
	    add_action("wp_ajax_ub_save_favicon", array($this, "ajax_ub_save_favicon"));
	    add_action("wp_ajax_ub_reset_favicon", array($this, "ajax_ub_reset_favicon"));

	    add_filter("clean_url", array($this, "clean_url"), 10, 30);
	    add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
	    add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
    }

	function enqueue_scripts() {
		wp_register_style( 'ub_favicons_style', ub_files_url('modules/favicons/css/admin.css')  . '', false, '1.0.0' );
		wp_enqueue_style( 'ub_favicons_style' );
	}

    function ub_favicons() {
        $this->__construct();
    }

	/**
	 * Returns valid schema
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
    public static function get_url_valid_shema($url) {
        $image = $url;

        $v_image_url = parse_url($url);

        if (isset($v_image_url['scheme']) && $v_image_url['scheme'] == 'https') {
            if (!is_ssl()) {
                $image = str_replace('https', 'http', $image);
            }
        } else {
            if (is_ssl()) {
                $image = str_replace('http', 'https', $image);
            }
        }

        return $image;
    }

	/**
	 * Process delete or update requests
	 *
	 * @return bool
	 */
    function process() {

        if (isset($_GET['resetfavicon']) && isset($_GET['page']) && $_GET['page'] == 'branding') {
            //login_image_save
            ub_delete_option('ub_favicon');
            ub_delete_option('ub_favicon_id');
            ub_delete_option('ub_favicon_size');
            ub_delete_option('ub_favicon_dir');
            ub_delete_option('ub_favicon_url');


            ub_update_option('ub_favicon', false);

            wp_redirect('admin.php?page=branding&tab=images');
        } elseif (isset($_POST['wp_favicon'])) {
            ub_update_option('ub_favicon', $_POST['wp_favicon']);
            ub_update_option('ub_favicon_id', $_POST['wp_favicon_id']);
            ub_update_option('ub_favicon_size', $_POST['wp_favicon_size']);
            ub_update_option('ub_favicons_use_as_default', $_POST['ub_favicons_use_as_default']);
        }

        return true;
    }

    function manage_output() {
        global $page;

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
        wp_enqueue_script('media-upload');

        if (isset($_GET['error']))
            echo '<div id="message" class="error fade"><p>' . __('There was an error uploading the file, please try again.', 'ub') . '</p></div>';
        elseif (isset($_GET['updated']))
            echo '<div id="message" class="updated fade"><p>' . __('Changes saved.', 'ub') . '</p></div>';

        ?>

        <div class='wrap nosubsub'>
            <div class="icon32" id="icon-themes"><br /></div>
            <h2><?php _e('Favicons', 'ub') ?></h2>

            <div class="postbox">
                <div class="inside">
                    <p class='description'><?php _e('This is the image that is displayed as a Favicon - ', 'ub'); ?>
                        <a href='<?php echo wp_nonce_url("admin.php?page=" . $page . "&amp;tab=images&amp;resetfavicon=yes&amp;action=process", 'ultimatebranding_settings_menu_images') ?>'><?php _e('Reset the image', 'login_image') ?></a>
                    </p>
                    <?php
                    wp_nonce_field('ultimatebranding_settings_menu_images');
                    $favicon_old = ub_get_option('ub_favicon_url', false);
                    $favicon_id = ub_get_option('ub_favicon_id', false);
                    $favicon_size = ub_get_option('ub_favicon_size', false);
                    $favicon = ub_get_option('ub_favicon', false);

                    if (!$favicon) {
                        if (isset($favicon_old) && trim($favicon_old) !== '') {
                            $favicon = self::get_url_valid_shema($favicon_old);
                        } else {
                            if ($favicon_id) {
                                if (is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('ultimate-branding/ultimate-branding.php')) {
                                    switch_to_blog(1);
                                    $favicon_src = wp_get_attachment_image_src($favicon_id, $favicon_size, $icon = false);
                                    restore_current_blog();
                                } else {
                                    $favicon_src = wp_get_attachment_image_src($favicon_id, $favicon_size, $icon = false);
                                }
                                $favicon = $favicon_src[0];
                                $width = $favicon_src[1];
                                $height = $favicon_src[2];
                            } else if ($favicon) {
                                list($width, $height) = getimagesize($favicon);
                            } else {
                                $response = wp_remote_head( self::$_default_fav );
                                if (!is_wp_error($response) && !empty($response['response']['code']) && $response['response']['code'] == '200') {//support for 3.8+
                                    $favicon = false; //admin_url() . 'images/wordpress-logo.svg';
                                } else {
                                    $favicon = false; //admin_url() . 'images/wordpress-logo.png';
                                }
                            }
                        }
                    }
                    ?>


	                <?php if( is_multisite() ): ?>
                        <h4><?php _e('Main site Favicon', 'ub'); ?></h4>
					<?php else: ?>
		                <h4><?php _e('Change Favicon', 'ub'); ?></h4>
		            <?php endif; ?>
	                <img id="ub_main_site_favicon" data-src="<?php echo self::get_favicon(); ?>" src="<?php echo self::get_favicon(); ?>" width="16" height="16" />
	                <input class="upload-url" id="wp_favicon" type="text" size="36" name="wp_favicon" value="<?php echo esc_attr( self::get_favicon(null, false) ); ?>" />
                    <input class="st_upload_button button" id="wp_favicon_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                    <input type="hidden" name="favicon_id" id="wp_favicon_id" value="<?php echo esc_attr($favicon_id); ?>" />
                    <input type="hidden" name="wp_favicon_size" id="wp_favicon_size" value="<?php echo esc_attr($favicon_size); ?>" />

	                <?php  $this->_render_subsites_favicon();  ?>
                </div>
            </div>
        </div>

        <?php
    }

    function remove_file($file) {
        @chmod($file, 0777);
        if (@unlink($file)) {
            return true;
        } else {
            return false;
        }
    }

    function admin_head() {

        $uploaddir = ub_wp_upload_dir();
        $uploadurl = ub_wp_upload_url();

        $uploadurl = preg_replace(array('/http:/i', '/https:/i'), '', $uploadurl);
        $favicon = ub_get_option('ub_favicon', false);

        if (file_exists($uploaddir . '/ultimate-branding/includes/favicon/favicon.png') || $favicon) {

            if (!$favicon) {
                $site_ico = $uploadurl . '/ultimate-branding/includes/favicon/favicon.png';
            } else {
                $site_ico = self::get_url_valid_shema($favicon);
            }

            echo '<style type="text/css">
			#header-logo { background-image: url(' . $site_ico . '); }
			#wp-admin-bar-wp-logo > .ab-item .ab-icon { background-image: url(' . $site_ico . '); background-position: 0; }
			#wp-admin-bar-wp-logo:hover > .ab-item .ab-icon { background-image: url(' . $site_ico . '); background-position: 0 !Important; }
			#wp-admin-bar-wp-logo.hover > .ab-item .ab-icon { background-image: url(' . $site_ico . '); background-position: 0 !Important; }
			</style>';
        }
    }

    function global_head() {
		global $current_blog;

        $favicon_dir = ub_get_option('ub_favicon_dir', false);
        $favicon = ub_get_option('ub_favicon', false);

        if ($favicon_dir && file_exists($favicon_dir) || $favicon) {
            echo '<link rel="shortcut icon" href="' . self::get_favicon($current_blog->blog_id) . '" />';
        }
    }

	/**
	 * Changes icons of the subnsites in the admin menus
	 *
	 *
	 */
    function change_blavatar_icon() {
        global $wp_admin_bar;

        foreach ((array) $wp_admin_bar->user->blogs as $blog) {

	        $blavatar = '<img src="' . self::get_favicon( $blog->userblog_id ) . '" alt="' . esc_attr__('Blavatar') . '" width="16" height="16" class="blavatar"/>';
            $blogname = empty($blog->blogname) ? $blog->domain : $blog->blogname;

            $wp_admin_bar->add_menu(array(
                'parent' => 'my-sites-list',
                'id' => 'blog-' . $blog->userblog_id,
                'title' => $blavatar . $blogname,
                'href' => get_admin_url($blog->userblog_id),
            ));
        }
    }

	/**
	 * Renders sub-sites favicon section
	 *
	 * @since 1.8.1
	 *
	 */
	function _render_subsites_favicon(){
		if( !is_multisite() || wp_is_large_network() ) return;?>
		<p>
			<label for="ub_favicons_use_as_default">
				<input type="checkbox" id="ub_favicons_use_as_default" name="ub_favicons_use_as_default" <?php checked(self::_use_as_default(), true); ?>  />
				<?php _e("Use this as default favicon for all sub-sites", "ub"); ?>
			</label>
		</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<h4><?php _e("Sub-site favicons", "ub"); ?></h4>
		<?php
		$table = new UB_Blog_Favicons();
		$table->prepare_items();
		$table->display();

	}


	/**
	 * Checks to see if blog has a favicon
	 *
	 * @param null $blog_id
	 *
	 * @since 1.8.1
	 *
	 * @return bool
	 */
	public static function has_favicon( $blog_id = null ){
		$favicon =  ub_get_option(self::FAV_PREFIX . $blog_id, false);
		return isset( $favicon['url'] );
	}

	/**
	 * Retrieves favicon based on blog_id
	 *
	 * @param string $blog_id
	 * @param bool $add_tail
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	public static function get_favicon( $blog_id = null, $add_tail = true ){

		/**
		 * If it's the main site return the main fav
		 */
		if( empty( $blog_id ) && is_main_site( $blog_id ) )
			return self::get_main_favicon( $add_tail );

		$tail = $add_tail ? '?' . md5(time()) : "";

		$key = self::FAV_PREFIX . $blog_id;
		$favicon =  ub_get_option($key, false);


		if( isset( $favicon['url'] ) )
			return self::get_url_valid_shema($favicon['url']) . $tail;

		if( self::_use_as_default() )
			return self::get_main_favicon( $add_tail );
		else
			return self::$_default_fav . $tail;
	}

	/**
	 * Retrieves main favicon
	 *
	 * @param bool $add_tail
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	public static function get_main_favicon( $add_tail = true ){
		$favicon = ub_get_option(self::FAV_PREFIX, false);
		$tail = $add_tail ? '?' . md5(time()) : "";

		if( $favicon )
			return self::get_url_valid_shema( $favicon ) . $tail;

		return self::$_default_fav . $tail;
	}

	/**
	 * Returns use as default option
	 * If it's true it means that the main image is being used as default favicon for all sub-sites
	 *
	 * @since 1.8.1
	 *
	 * @return bool
	 */
	private static function _use_as_default(){
		return (bool) ub_get_option("ub_favicons_use_as_default", false);
	}

	/**
	 * Saves favicon to db using ajax
	 *
	 * @since 1.8.1
	 *
	 */
	public function ajax_ub_save_favicon(){
		$data = $_POST['ub_favicons'];
		$id = (int) key($data);
		$data = current($data);

		/**
		 * Empty url
		 */
		if( empty( $data['url']  ) ){
			wp_send_json_error(
				__("Please specify image", "ub")
			);
		}

		/**
		 * Id = 0
		 */
		if( $id === 0 ){
			wp_send_json_error(
				__("Please specify image", "ub")
			);
		}

		if( wp_verify_nonce($data['nonce'], "ub_save_favicon") ){
			unset( $data['nonce'] );
			ub_update_option( self::FAV_PREFIX . $id, $data );
			wp_send_json_success(__("Favicon successfully updated", "ub"));
		}

		wp_die();
	}

	/**
	 * Resets favicon to default ( removes the fav )
	 *
	 * @since 1.8.1
	 *
	 */
	public function ajax_ub_reset_favicon(){
		$id = (int) $_POST['id'];
		$nonce = $_POST['nonce'];

		/**
		 * Id = 0
		 */
		if( $id === 0 ){
			wp_send_json_error(
				__("Invalid id", "ub")
			);
		}

		if( wp_verify_nonce($nonce, "ub_reset_favicon") ){
			ub_delete_option(self::FAV_PREFIX . $id);
			wp_send_json_success(array(
				"update" => __("Favicon successfully updated", "ub"),
				"fav" => self::get_favicon($id)
			));
		}

		wp_die();
	}

	/**
	 * Removes #038; from url
	 *
	 * @filter clean_url
	 *
	 * @param $good_protocol_url
	 * @param $original_url
	 * @param $_context
	 *
	 * @since 1.8.1
	 *
	 * @return mixed
	 */
	function clean_url($good_protocol_url, $original_url, $_context){
		if( isset($_GET['page']) && $_GET['page'] === "branding" ){
			$good_protocol_url = str_replace("#038;", "", $good_protocol_url);
		}

		return $good_protocol_url;
	}
}

$ub_favicons = new ub_favicons();