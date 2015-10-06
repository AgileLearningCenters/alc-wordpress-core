<?php
/*
Plugin Name: Global Footer Content
Plugin URI: http://premium.wpmudev.org/project/global-footer-content
Description: Simply insert any code that you like into the footer of every blog
Author: Barry (Incsub), S H Mohanjith (Incsub), Andrew Billits (Incsub)
Version: 1.1
Author URI: http://premium.wpmudev.org
Network: true
WDP ID: 93
*/

/*
Copyright 2007-2009 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


class ub_global_footer_content {

	var $global_footer_content_settings_page;
	var $global_footer_content_settings_page_long;

	function __construct() {

		add_action( 'ultimatebranding_settings_menu_footer', array(&$this, 'global_footer_content_site_admin_options') );
		add_filter( 'ultimatebranding_settings_menu_footer_process', array(&$this, 'update_global_footer_options'), 10, 1 );
        add_action('wp_footer', array(&$this, 'global_footer_content_output'));
    }

	function ub_global_footer_content() {
		$this->__construct();
	}


	function update_global_footer_options( $status ) {
		$global_footer = $_POST['ub_global_footer'];
		$global_footer_main = $_POST['ub_global_footer_main'];

		if ( '' === $global_footer['content'] ) {
            $global_footer['content'] = 'empty';
		}

		$status *= ub_update_option( 'global_footer_content' , $global_footer['content'] );
        $status *= ub_update_option( 'global_footer_bgcolor', $global_footer['bgcolor'] );
        $status *= ub_update_option( 'global_footer_fixedheight', $global_footer['fixedheight'] );

        if( is_multisite() ){
            if ( '' === $global_footer_main['content'] ) {
                $global_footer_main['content'] = 'empty';
            }
            $status *= ub_update_option( 'global_footer_main_content' , $global_footer_main['content'] );
            $status *= ub_update_option( 'global_footer_main_bgcolor', $global_footer_main['bgcolor'] );
            $status *= ub_update_option( 'global_footer_main_fixedheight', $global_footer_main['fixedheight'] );
        }

	    return (bool) $status;
	}

	function global_footer_content_output() {
		$global_footer_content = ub_get_option('global_footer_content');
		$global_footer_main_content = ub_get_option('global_footer_main_content');

		if ( $global_footer_content === 'empty' ) {
			$global_footer_content = '';
		}

        if ( $global_footer_main_content === 'empty' ) {
            $global_footer_main_content = '';
        }

		if ( !empty( $global_footer_content ) && ( !is_multisite() || !is_main_site() ) ):

            $global_footer_bgcolor = ub_get_option('global_footer_bgcolor', "");
            $global_footer_height = ub_get_option('global_footer_fixedheight', "");

            $style = "" !== $global_footer_bgcolor ? "background-color:" . $global_footer_bgcolor . ";" : "";
            $style .= "" !== $global_footer_height ? "height:" . $global_footer_height ."px;overflow:hidden;" : "";
            ?>
            <div id="ub_global_footer_content" style="<?php echo $style ?>">
                <?php echo stripslashes( $global_footer_content );?>
            </div>
        <?php endif; ?>
        <?php if( !empty($global_footer_main_content) && ( is_multisite() && is_main_site() ) ):
            $global_footer_main_bgcolor = ub_get_option('global_footer_main_bgcolor', "");
            $global_footer_main_height = ub_get_option('global_footer_main_fixedheight', "");

            $style = "" !== $global_footer_main_bgcolor ? "background-color:" . $global_footer_main_bgcolor . ";" : "";
            $style .= "" !== $global_footer_main_height ? "height:" . $global_footer_main_height ."px;overflow:hidden;" : "";
            ?>

            <div id="ub_global_footer_content"  style="<?php echo $style ?>">
                <?php echo stripslashes( $global_footer_main_content );?>
            </div>

        <?php endif  ;

	}

	function global_footer_content_site_admin_options() {

        // footer content
		$global_footer_content = ub_get_option('global_footer_content');
		if ( $global_footer_content == 'empty' ) {
			$global_footer_content = '';
		}

        // footer background color
        $global_footer_bgcolor = ub_get_option('global_footer_bgcolor', "");

        // fixed height
        $global_footer_fixedheight = ub_get_option('global_footer_fixedheight', "");
        ?>
			<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'><span><?php echo is_multisite() ? __( 'Global Footer Content For Subsites', 'ub' ) : __( 'Global Footer Content', 'ub' ) ?></span></h3>
			<div class="inside">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Footer Content', 'ub') ?></th>
						<td>
							<?php
							$args = array("textarea_name" => "ub_global_footer[content]", "textarea_rows" => 5);
							wp_editor( stripslashes( $global_footer_content ), "global_footer_content", $args );
							?>
		                	<br />
							<?php _e('What is added here will be shown on every blog or site in your network. You can add tracking code, embeds, terms of service links, etc.', 'ub') ?>
						</td>
					</tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Background Color', 'ub') ?></th>
                        <td>
                            <input name="ub_global_footer[bgcolor]" class="ub_color_picker" id="ub_footer_background_color" type="text"   value="<?php echo $global_footer_bgcolor; ?>"/>
                            <br />
                            <?php _e("Click on 'clear' button to make background transparent", 'ub') ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Fixed Height', 'ub') ?></th>
                        <td>
                            <input class="text-60"  name="ub_global_footer[fixedheight]"  id="ub_footer_fixedheight" type="number" step="1"   value="<?php echo $global_footer_fixedheight; ?>"/>&nbsp;px
                            <br />
                            <?php _e('Choose height of footer. Leave blank to fit height to content', 'ub') ?>
                        </td>
                    </tr>

				</table>
			</div>
		</div>
        <?php if( ( is_multisite() && is_super_admin() )):
            $global_footer_main_content = ub_get_option('global_footer_main_content', "");
            $global_footer_main_bgcolor = ub_get_option('global_footer_main_bgcolor', "");
            $global_footer_main_fixedheight = ub_get_option('global_footer_main_fixedheight', "");
            if ( $global_footer_main_content == 'empty' ) {
                $global_footer_main_content = '';
            }
            ?>
            <div class="postbox">
                <h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Global Footer Content For Main Site', 'ub' ) ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Footer Content', 'ub') ?></th>
                            <td>
                                <?php
                                $args = array("textarea_name" => "ub_global_footer_main[content]", "textarea_rows" => 5);
                                wp_editor( stripslashes( $global_footer_main_content ), "global_footer_main_content", $args );
                                ?>
                                <br />
                                <?php _e('What is added here will be shown on every blog or site in your network. You can add tracking code, embeds, terms of service links, etc.', 'ub') ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Background Color', 'ub') ?></th>
                            <td>
                                <input name="ub_global_footer_main[bgcolor]" class="ub_color_picker" id="ub_footer_main_background_color" type="text"   value="<?php echo $global_footer_main_bgcolor; ?>"/>
                                <br />
                                <?php _e("Click on 'clear' button to make background transparent", 'ub') ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Fixed Height', 'ub') ?></th>
                            <td>
                                <input class="text-60"  name="ub_global_footer_main[fixedheight]"  id="ub_footer_main_fixedheight" type="number" step="1"   value="<?php echo $global_footer_main_fixedheight; ?>"/>&nbsp;px
                                <br />
                                <?php _e('Choose height of footer. Leave blank to fit height to content', 'ub') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endif; ?>
		<?php
	}

}

$ub_globalfootertext = new ub_global_footer_content();

