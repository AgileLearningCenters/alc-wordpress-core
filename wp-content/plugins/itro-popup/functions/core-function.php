<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.
*/
/* ------------------ADD MENU PAGE */
function itro_plugin_menu() {
	add_options_page( 'Popup Plugin Options', 'ITRO Popup', 'manage_options', 'itro-popup/admin/popup-admin.php', '' );
}

/* -------------- INITIALIZATION */

function itro_init()
{
	/* -----load sample popup settings */
	if( get_option("itro_curr_ver") == NULL )
	{
		/* --------- initialize database */
		itro_db_init();
		
		itro_update_option('popup_time',20);
		itro_update_option('cookie_time_exp',0);
		itro_update_option('popup_background','#FFFFFF');
		itro_update_option('popup_border_color','#F7FF00');
		itro_update_option('px_popup_width',300);
		itro_update_option('px_popup_height',0);
		itro_update_option('show_countdown','yes');
		itro_update_option('auto_margin_check','yes');
		itro_update_option('select_popup_width','px');
		itro_update_option('select_popup_height','auto');
		itro_update_option('popup_bg_opacity',0.4);
		itro_update_option('opaco_bg_color','#8A8A8A');
		itro_update_option('popup_position','fixed'); 
		itro_update_option('popup_border_width',3);
		itro_update_option('popup_border_radius',8);
		itro_update_option('popup_padding',2);
		itro_update_option('page_selection','none');
		
		switch(WPLANG)
		{
			case 'en_US':
				$welcome_text = '<h1 style="text-align: center;"><span style="color: #000000; font-size: 20;">Hello, this is a pop-up sample.</span></h1><p style="text-align: center;"><span style="color: #000000; font-size: 20;">The basic stetting to get started are: Popup height, Popup time, Next visualization, Popup border color, Popup background.</span></p><p style="text-align: center;"><span style="color: #000000; font-size: 20;">Write watever you want in the Custom text editor and enjoy our plugin!</span></p><p>&nbsp;</p>';
				break;
			case 'it_IT':
				$welcome_text = '<h1 style="text-align: center;"><span style="color: #000000; font-size: 20;">Questo &egrave; un esempio di popup.</span></h1><p style="text-align: center;">&nbsp;</p><p style="text-align: center;"><span style="color: #000000; font-size: 20;">Le impostazioni base per iniziare sono: Altezza popup, Tempo popup, Prossima visualizzazione, Colore bordo, Colore sfondo.</span></p><p style="text-align: center;">&nbsp;</p><p style="text-align: center;"><span style="color: #000000; font-size: 20;">Scrivi qualunque cosa vuoi nell&#39;editor di testo di wordpress e buon lavoro!</span></p><p style="text-align: center;">&nbsp;</p>';
				break;
			default:
				$welcome_text = '<h1 style="text-align: center;"><span style="color: #000000; font-size: 20;">Hello, this is a pop-up sample.</span></h1><p style="text-align: center;"><span style="color: #000000; font-size: 20;">The basic stetting to get started are: Popup height, Popup time, Next visualization, Popup border color, Popup background.</span></p><p style="text-align: center;"><span style="color: #000000; font-size: 20;">Write watever you want in the Custom text editor and enjoy our plugin!</span></p><p>&nbsp;</p>';
		}
		itro_update_field('custom_html',$welcome_text);
		
		itro_update_option('sample_popup','done');
	}
	
	/* ---------------create preview page */
	switch(WPLANG)
	{
		case 'en_US':
			$preview_text = 'ITRO - Preview page. This page is used to rightly display preview of your popup with site theme.';
			break;
		case 'it_IT':
			$preview_text = 'ITRO - Pagina di anteprima. Questa pagina &egrave; utilizzata per visualizzare correttamente il popup, integrato con lo stile del tema.';
			break;
		default:
			$preview_text = 'ITRO - Preview page. This page is used to rightly display preview of your popup with site theme.';
	}
	if ( itro_get_option('preview_id') == NULL )
	{
		/* Create post object */
		$preview_post = array(
		  'post_title'    => 'ITRO - Preview',
		  'post_name'    => 'itro-preview',
		  'post_content'  => $preview_text,
		  'post_status'   => 'private',
		  'post_author'   => 1,
		  'post_type'   => 'page',
		);
		/* Insert the post into the database */
		@$preview_id = wp_insert_post( $preview_post );
		itro_update_option('preview_id',$preview_id);
	}	
}

/* --------------------------CHECK THE PLUGIN VERSION */
function itro_check_ver()
{
	ob_start();
	if( $GLOBALS['ITRO_VER'] != get_option('itro_curr_ver') )
	{
		/* check and update the db */
		itro_update_db();
		
		$ver = get_option('itro_curr_ver');
		update_option('itro_prev_ver',$ver);
		update_option('itro_curr_ver', $GLOBALS['ITRO_VER']);
	}
	ob_end_clean();
}

/* --------------------------DISPLAY THE POPUP */
function itro_display_popup()
{
	/* woocommerce shop page identification */
	if( function_exists('is_shop') && function_exists('woocommerce_get_page_id') ) /* if this functions exist, woocommerce is installed! */
	{
		if ( is_shop() ) /* if the actual page is the standard woocommerce shop page */
		{
			$woo_shop = true;
			$woo_shop_id = woocommerce_get_page_id( 'shop' );
		}
	}
	else
	{
		$woo_shop = NULL;
		$woo_shop_id = NULL;
	}	
	
	/* this condition, control if the popup must or not by displayed in a specified page */
	$selected_page_id = json_decode(itro_get_option('selected_page_id'));
	$id_match = NULL;
	/* get the page id */
	global $wp_query;
    $current_page_id = $wp_query->get_queried_object_id();
	
	switch (itro_get_option('page_selection'))
	{
		case 'some':
			if( isset($selected_page_id) ) 
			{
				foreach ($selected_page_id as $single_id)
				{
					if ( $single_id == $current_page_id || ( $single_id == $woo_shop_id && $woo_shop ) ) /* if the selected id is the current page id popup will be displayed OR if the woo_shop_id has been selected and you are in the woocommerce standard shop page ($woo_shop == true), popup will be displayed.  */
					{
						$id_match++;
					}
				}
			}
			if( (is_front_page() && itro_get_option('blog_home') == 'yes') || (is_home() && itro_get_option('blog_home') == 'yes') )
			{
				$id_match++;
			}
			if( $id_match != NULL || itro_get_option('preview_id') == $current_page_id )
			{
				itro_style();
				itro_popup_template();
				itro_popup_js();
			}
		break;
		case 'all':
			itro_style();
			itro_popup_template();
			itro_popup_js();
		break;
		case 'none':
			if( itro_get_option('preview_id') == $current_page_id )
			{
				itro_style();
				itro_popup_template();
				itro_popup_js();
			}
		break;
		
	}
}

/* ------------------------- SELECT PAGES FUNCTIONS */
function itro_check_selected_id($id_to_check)
{
	if(itro_get_option('selected_page_id') != NULL)
	{
		$selected_page_id = json_decode(itro_get_option('selected_page_id'));
		$id_match = NULL;
		if( isset($selected_page_id) ) 
		{
			foreach ($selected_page_id as $single_id)
			{if ($single_id == $id_to_check) return (true); }
		}
	}
}

function itro_list_pages()
{?>				
	<select name="selected_page_id[]" multiple> 
	 <?php 
	  $pages = get_pages(); 
	  foreach ( $pages as $page ) 
	  {
		$option = '<option value="'. $page->ID .'"';
		if(itro_check_selected_id($page->ID)){$option .='selected="select"';} 
		$option .= 'onmouseover="itro_check_state(this)" onmouseup="itro_select(this);">';
		$option .= $page->post_title;
		$option .= '</option>';
		echo $option;
	  }
	 ?>
	</select>
<?php
}

/* ------------------------- DEBUG INFORMATION ON ADMIN PANNEL */
function itro_get_serverinfo()
{
	global $wpdb;
	global $wp_version;

	$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
	if (empty($sql_mode)) $sql_mode = __('Not set', 'itro-plugin' );
	if(ini_get('safe_mode')) $safe_mode = __('On', 'itro-plugin' );
	else $safe_mode = __('Off', 'itro-plugin' );
	if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On', 'itro-plugin' );
	else $allow_url_fopen = __('Off', 'itro-plugin' );
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
	else $upload_max = __('N/A', 'itro-plugin' );
	if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
	else $post_max = __('N/A', 'itro-plugin' );
	if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
	else $max_execute = __('N/A', 'itro-plugin' );
	if(ini_get('memory_limit')) $memory_limit = ini_get('memory_limit');
	else $memory_limit = __('N/A', 'itro-plugin' );
	if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte', 'itro-plugin' );
	else $memory_usage = __('N/A', 'itro-plugin' );
	if (is_callable('exif_read_data')) $exif = __('Yes', 'itro-plugin' ). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
	else $exif = __('No', 'itro-plugin' );
	if (is_callable('iptcparse')) $iptc = __('Yes', 'itro-plugin' );
	else $iptc = __('No', 'itro-plugin' );
	if (is_callable('xml_parser_create')) $xml = __('Yes', 'itro-plugin' );
	else $xml = __('No', 'itro-plugin' );

	if ( function_exists( 'wp_get_theme' ) )
	{
		$theme = wp_get_theme();
	} else {
		$theme = get_theme( get_current_theme() );
	}


	if ( function_exists( 'is_multisite' ) )
	{
		if ( is_multisite() )
		{
			$ms = __('Yes', 'itro-plugin' );
		}
		else
		{
			$ms = __('No', 'itro-plugin' );
		}

	}
	else $ms = __('N/A', 'itro-plugin' );

	$siteurl = get_option('siteurl');
	$homeurl = get_option('home');
	$db_version = get_option('db_version');

	$debug_info = Array(
			__('Operating System', 'itro-plugin' )			=> PHP_OS,
			__('Server', 'itro-plugin' )					=> $_SERVER["SERVER_SOFTWARE"],
			__('Memory usage', 'itro-plugin' )				=> $memory_usage,
			__('MYSQL Version', 'itro-plugin' )				=> $sqlversion,
			__('SQL Mode', 'itro-plugin' )					=> $sql_mode,
			__('PHP Version', 'itro-plugin' )				=> PHP_VERSION,
			__('PHP Safe Mode', 'itro-plugin' )				=> $safe_mode,
			__('PHP Allow URL fopen', 'itro-plugin' )		=> $allow_url_fopen,
			__('PHP Memory Limit', 'itro-plugin' )			=> $memory_limit,
			__('PHP Max Upload Size', 'itro-plugin' )		=> $upload_max,
			__('PHP Max Post Size', 'itro-plugin' )			=> $post_max,
			__('PHP Max Script Execute Time', 'itro-plugin' )	=> $max_execute,
			__('PHP Exif support', 'itro-plugin' )			=> $exif,
			__('PHP IPTC support', 'itro-plugin' )			=> $iptc,
			__('PHP XML support', 'itro-plugin' )			=> $xml,
			__('Site URL', 'itro-plugin' )					=> $siteurl,
			__('Home URL', 'itro-plugin' )					=> $homeurl,
			__('WordPress Version', 'itro-plugin' )			=> $wp_version,
			__('WordPress DB Version', 'itro-plugin' )		=> $db_version,
			__('Multisite', 'itro-plugin' )					=> $ms,
			__('Active Theme', 'itro-plugin' )				=> $theme['Name'].' '.$theme['Version']
	);
	$debug_info['Active Plugins'] = null;
	$active_plugins = $inactive_plugins = Array();
	$plugins = get_plugins();
	foreach ($plugins as $path => $plugin) {
		if ( is_plugin_active( $path ) ) {
			$debug_info[$plugin['Name']] = $plugin['Version'];
		} else {
			$inactive_plugins[$plugin['Name']] = $plugin['Version'];
		}
	}
	$debug_info['Inactive Plugins'] = null;
	$debug_info = array_merge( $debug_info, (array)$inactive_plugins );

	$mail_text = __( "ITRO Popup Plugin - Debug info", 'itro-plugin' ) . "\r\n------------------\r\n\r\n";
	$page_text = "";
	if ( !empty( $debug_info ) )
	{
		foreach($debug_info as $name => $value)
		{
			if ($value !== null) {
				$page_text .= "<li><strong>$name</strong> $value</li>";
				$mail_text .= "$name: $value\r\n";
			}
			else
			{
				$page_text .= "</ul><h2>$name</h2><ul class='itro_debug_settings'>";
				$mail_text .= "\r\n$name\r\n----------\r\n";
			}
		}
	}
	do if ( !empty( $_REQUEST['itro_debug_submit'] ) ) {
		$nonce=$_REQUEST['itro_debug_nonce'];
		if (! wp_verify_nonce($nonce, 'itro-debug-nonce') ) {
			echo "<div class='itro_debug_error'>" . __( "Form submission error: verification check failed.", 'itro-plugin' ) . "</div>";
			break;
		}
		if ($_REQUEST['itro_debug_send_email'])
		{
			if (wp_mail($_REQUEST['itro_debug_send_email'], sprintf( __( "ITRO Debug Mail From Site %s.", 'itro-plugin'), $siteurl), $mail_text ) )
			{
				echo "<div class='itro_debug_mail_sent'>" . sprintf( __( "Sent to %s.", 'itro-plugin' ), $_REQUEST['itro_debug_send_email'] ) . "</div>";
			} 
			else
			{
				echo "<div class='itro_debug_error'>" . sprintf( __( "Failed to send to %s.", 'itro-plugin' ),  $_REQUEST['itro_debug_send_email'] ) . "</div>";
			}
		}
		else
		{
			echo "<div class='itro_debug_error'>" . __( 'Error: please enter an e-mail address before submitting.', 'itro-plugin' ) . "</div>";
		}
	} while(0); // control structure for use with break
	$nonce = wp_create_nonce('itro-debug-nonce');
	$buf =	'<textarea style="width:100%; height:400px; resize:none;" onclick="select();">' . $mail_text . '</textarea>';
			//'<input name="itro_debug_send_email" type="text" value="" placeholder="' . __( "E-mail debug information", 'itro-plugin' ) . '"><input name="itro_debug_nonce" type="hidden" value="' .
			//$nonce . '"><input name="itro_debug_submit" type="submit" value="' . __( 'Submit', 'itro-plugin' ) . '" class="button-primary"><p>';
	return $buf;
}
?>