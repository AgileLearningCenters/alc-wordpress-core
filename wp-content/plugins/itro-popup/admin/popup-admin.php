<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.
*/
global $ITRO_VER;

if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
/* variables for the field and option names */
if( !isset($submitted_form )) 
{
	$opt_name=array(
	/*opt 0*/'popup_time',
	/*opt 1*/'popup_top_margin',
	/*opt 2*/'cookie_time_exp',
	/*opt 3*/'popup_width',
	/*opt 4*/'popup_background',
	/*opt 5*/'popup_border_color',
	/*opt 6*/'age_restriction',
	/*opt 7*/'enter_button_text',
	/*opt 8*/'leave_button_text',
	/*opt 9*/'leave_button_url',
	/*opt 10*/'enter_button_bg_color',
	/*opt 11*/'enter_button_border_color',
	/*opt 12*/'leave_button_bg_color',
	/*opt 13*/'leave_button_border_color',
	/*opt 14*/'enter_button_font_color',
	/*opt 15*/'leave_button_font_color',
	/*opt 16*/'popup_position',
	/*opt 17*/'popup_height',
	/*opt 18*/'page_selection',
	/*opt 19*/'blog_home',
	/*opt 20*/'popup_border_radius',
	/*opt 21*/'count_font_color',
	/*opt 22*/'background_select',
	/*opt 23*/'popup_delay',
	/*opt 24*/'popup_unlockable',
	/*opt 25*/'popup_bg_opacity',
	/*opt 26*/'opaco_bg_color',
	/*opt 27*/'popup_border_width',
	/*opt 28*/'advanced_settings',
	/*opt 29*/'show_countdown',
	/*opt 30*/'auto_margin_check',
	/*opt 31*/'popup_padding',
	/*opt 32*/'disable_mobile',
	/*opt 33*/'cross_selected',
	);
	$field_name=array(
	/*fld 0*/'custom_html',
	);
	$submitted_form = 'mt_submit_hidden';
}

/* ordered options */
for($i=0;$i<count($opt_name); $i++)
{
	/* Read in existing option value from database */
	$opt_val[$i] = itro_get_option( $opt_name[$i] );
	$px_opt_val[$i] = itro_get_option( 'px_' . $opt_name[$i] );
	$perc_opt_val[$i] = itro_get_option( 'perc_' . $opt_name[$i] );
	
	/* See if the user has posted us some information  */
	/* If they did, this hidden field will be set to 'Y' */
	if( isset($_POST[ $submitted_form ]) && $_POST[ $submitted_form ] == 'Y' )
	{
		/* Read their posted value */
		if(isset($_POST[$opt_name[$i]])){$opt_val[$i] = $_POST[ $opt_name[$i] ];}
		else{$opt_val[$i] = NULL;}
		
		/* Save the posted value in the database */
		itro_update_option( $opt_name[$i], $opt_val[$i] );
		
		if( isset($_POST['select_' . $opt_name[$i]]) )
		{
			itro_update_option( 'select_' . $opt_name[$i], $_POST['select_' . $opt_name[$i]] );
			
			$px_opt_val[$i] = $_POST['px_' . $opt_name[$i]];
			itro_update_option( 'px_' . $opt_name[$i], $_POST['px_' . $opt_name[$i]] );
			$perc_opt_val[$i] = $_POST['perc_' . $opt_name[$i]];
			itro_update_option( 'perc_' . $opt_name[$i], $_POST['perc_' . $opt_name[$i]] );
		}
		else{ itro_update_option( 'select_' . $opt_name[$i], NULL ); }
	}
}

/* ordered field */
for($i=0;$i<count($field_name); $i++)
{
	/* Read in existing option value from database */
	
	$field_value[$i] = itro_get_field( $field_name[$i] );

	/* See if the user has posted us some information */
	/* If they did, this hidden field will be set to 'Y' */
	if( isset($_POST[ $submitted_form ]) && $_POST[ $submitted_form ] == 'Y' ) 
	{
		/* Read their posted value */
		if(isset($_POST[$field_name[$i]])) {$field_value[$i] = $_POST[ $field_name[$i] ]; }
		else{$field_value[$i] = NULL;}
		
		/* Save the posted value in the database */
		itro_update_field( $field_name[$i], $field_value[$i] );
	}
}

/* unsorted option and field */
if( isset($_POST[ $submitted_form ]) && $_POST[ $submitted_form ] == 'Y')
{
	if( isset($_POST['selected_page_id']) ) 
	{
		$selected_page_id=json_encode($_POST['selected_page_id']);
		itro_update_option('selected_page_id',$selected_page_id);
	}
	else
	{
		itro_update_option('selected_page_id',NULL);
	}
	
	if( empty($_POST['background_source']) ) { $opt_val[22] = NULL; itro_update_option('background_source',NULL); }
	else { itro_update_option('background_source',$_POST['background_source']); }
}

/* delete tables on plugin uninstall option */
if( isset($_POST['delete_data_hidden']) && $_POST['delete_data_hidden'] == 'Y' )
{
	if( isset($_POST['delete_data']) )
	{
		itro_update_option('delete_data', $_POST['delete_data']);
	}
	else
	{
		itro_update_option('delete_data', NULL);
	}
}

/* Put an settings updated message on the screen */
if( isset($_POST[ $submitted_form ]) && $_POST[ $submitted_form ] == 'Y' || isset($_POST['delete_data_hidden']) && $_POST['delete_data_hidden'] == 'Y' ) {
	?>
	<div class="updated"><p><strong><?php _e('settings saved.', 'itro-plugin' ); ?></strong></p></div>
	<?php
}
?>
<script type="text/javascript" src="<?php echo itroPath . 'scripts/'; ?>jscolor/jscolor.js"></script>

<div style="display:table; width:100%;">
	<h1 style="float:left;"><?php _e( 'I.T.RO. Popup Plugin - Settings', 'itro-plugin');?></h1>
	<h4 style="float:right; margin-right:30px;">VER: <?php echo $ITRO_VER; ?></h4>
</div>

<form id="optionForm" method="post">
	
	<div id="leftColumn">
		<!-- Settings form !-->
		<div id="formContainer">
			
			<!--------- General options --------->
			<?php echo itro_onOff('genOption','hidden');?>
			<p class="wpstyle" onClick="onOff_genOption();"><?php _e("General Popup Option:", 'itro-plugin' ); ?> </p>
			<div id="genOption">
				<input type="hidden" name="<?php echo $submitted_form; ?>" value="Y">
				
				<!-- advanced settings checkbox !-->
				<p>
						<input type="checkbox" id="<?php echo $opt_name[28]; ?>" name="<?php echo $opt_name[28]; ?>" value="yes" <?php if($opt_val[28]=='yes' ){echo 'checked="checked"';} ?>>
						<span id="span_<?php echo $opt_name[28]; ?>" onclick="itro_mutual_check('<?php echo $opt_name[28]; ?>','','');"><?php _e("SHOW ADVANCED SETTINGS","itro-plugin")?></span>
						<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e('Display many other options','itro-plugin');?>" />
				</p>
				
				<!-- popup display location!-->
				<p>
					<h3><?php _e("DECIDE WHERE POPUP WILL BE DISPLAYED","itro-plugin")?></h3>
					<fieldset>
						<input type="radio" id="only_selected" name="<?php echo $opt_name[18];?>" value="some"<?php if($opt_val[18]=='some'){echo 'checked="checked"';} ?>/><?php _e("Only selected pages", 'itro-plugin' ); ?><img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Multiple choise with CTRL+Click or SHIFT+Arrow up or down",'itro-plugin');?>">&nbsp;&nbsp;&nbsp;
						<input type="radio" name="<?php echo $opt_name[18];?>" value="all" <?php if($opt_val[18]=='all' ){echo 'checked="checked"';} ?>/><?php _e("All pages", 'itro-plugin' ); ?>&nbsp;&nbsp;&nbsp;
						<input type="radio" name="<?php echo $opt_name[18];?>" value="none" <?php if($opt_val[18]=='none' || $opt_val[18]== NULL){echo 'checked="checked"';} ?>/><?php _e("No page", 'itro-plugin' ); ?>
					</fieldset>
					<div onClick="document.getElementById('only_selected').checked = true;">
						<select name="<?php echo $opt_name[19]; ?>" multiple size="1">
							<option onmouseover="itro_check_state(this)" onmouseup="itro_select(this);" value="yes" <?php if($opt_val[19]=='yes' ){echo 'selected="select"';} ?> ><?php _e("Blog homepage","itro-plugin")?></option>
						</select>
						<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e('If in your Settings->Reading you have set \'Front page displays: Your latest posts\' and want to display the popup in the home, check this box.','itro-plugin');?>" />
						<br>
						<?php 
						/* list of published pages */
						itro_list_pages();
						?>
					</div>
				</p>
				
				<!-- disable esc key and mobile !-->
				<h3><?php _e("GENERAL SETTINGS","itro-plugin")?></h3>
				<p id="<?php echo $opt_name[24]; ?>_div">
					<input type="checkbox" id="<?php echo $opt_name[32]; ?>" name="<?php echo $opt_name[32]; ?>" value="yes" <?php if($opt_val[32] == 'yes' ){echo 'checked="checked"';} ?> />
					<span onclick="itro_mutual_check('<?php echo $opt_name[32]; ?>','','')"><?php _e("Disable on mobile device", 'itro-plugin' ); ?></span>
					<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>"title="<?php _e('Check this to disable popup on mobile devices','itro-plugin');?>" >
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" id="<?php echo $opt_name[24]; ?>" name="<?php echo $opt_name[24]; ?>" value="yes" <?php if($opt_val[24] == 'yes' ){echo 'checked="checked"';} ?> />
					<span onclick="itro_mutual_check('<?php echo $opt_name[24]; ?>','','')"><?php _e("Disable ESC key", 'itro-plugin' ); ?></span>
					<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>"title="<?php _e('If you set this option popup can not be closed with ESC button of keyboard.','itro-plugin');?>" >
				</p>
				
				<!-- popup seconds!-->
				<div style="display:table; height:10px; padding-bottom:5px;">
					<span style="float:left;" ><?php _e("Popup seconds:", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Set seconds until the popup automatically close. Set it to zero to disable countdown.",'itro-plugin');?>" ></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[0] , $opt_val[0] , 0 , 120 , 1, 0, '') ?>
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[0]; ?>" name="<?php echo $opt_name[0]; ?>" value="<?php echo $opt_val[0]; ?>" size="1">
				</div>
				
				<!-- popup delay!-->
				<div id="<?php echo $opt_name[23]; ?>_div" style="display:table; height:10px; padding-bottom:5px;">
					<span style="float:left;" ><?php _e("Popup delay:", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Set seconds before the popup will be displayed",'itro-plugin');?>" ></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[23] , $opt_val[23] , 0 , 120 , 1, 0, '') ?>
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[23]; ?>" name="<?php echo $opt_name[23]; ?>" value="<?php echo $opt_val[23]; ?>" size="1">
				</div>
				
				<!-- next time visualization !-->
				<div style="display:table; height:10px; padding-bottom:5px;">
					<span style="float:left;" ><?php _e("Next visualization (hours):", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Set time for the next visualization of popup, to prevent annoying repeated visualizations, when someone visit pages",'itro-plugin');?>" ></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[2] , $opt_val[2] , 0 , 720 , 6, 0, '') ?>
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[2]; ?>" name="<?php echo $opt_name[2]; ?>" value="<?php echo $opt_val[2]; ?>" size="1">
				</div>
				
				<input value="<?php _e("Delete cookie", 'itro-plugin' ); ?>" type="button" class="button" onclick="itro_set_cookie('popup_cookie','one_time_popup',-100); jQuery('#cookie_msg').stop(true, true); jQuery('#cookie_msg').fadeIn(function() {jQuery('#cookie_msg').fadeOut(6000);});">
				<span id="cookie_msg" style="display:none;background-color:green;"><?php _e("Cookie deleted!", 'itro-plugin' ); ?></span>
				
				<!-- countdown settings !-->
				<p id="<?php echo $opt_name[29]; ?>_div">
					<input type="checkbox" name="<?php echo $opt_name[29]; ?>" id="<?php echo $opt_name[29]; ?>" value="yes" <?php if(itro_get_option($opt_name[29])=='yes' ){echo 'checked="checked"';} ?> />&nbsp;&nbsp;
					<span onclick="itro_mutual_check('<?php echo $opt_name[29]; ?>','','')"><?php _e("Show countdown", 'itro-plugin' ); ?></span>
					<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e('Show the countdown at the bottom of the popup which dispay the time before popup will close. If is hidden, it run anyway.','itro-plugin');?>" >
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php _e("Countdown font color:", 'itro-plugin' ); ?>
					<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e('Select the countdown font color.','itro-plugin');?>" >
					<input type="text" class="color" name="<?php echo $opt_name[21]; ?>" value="<?php echo $opt_val[21]; ?>" size="10">
				</p>
				
				<h3><?php _e("POPUP ASPECT","itro-plugin")?></h3>				
				
				<!-- popup width !-->				
				<div style="display:table; height:10px; padding-bottom:5px;">
					<span style="float:left;" ><?php _e("Popup width:", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Use the % to change width dinamically with the browser window , or px for fixed dimension i.e: 30% or 200px",'itro-plugin');?>" ></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[3] , $opt_val[3] , 0 , 1500 , 10, 0, 'px') ?>
					<?php itro_slidebar( $opt_name[3] , $opt_val[3] , 0 , 100 , 1, 0, 'perc') ?>
					<input type="text" class="itro_text_input" style="<?php if( itro_get_option('select_' . $opt_name[3]) != 'px' ) { echo 'display:none;'; } ?>" id="<?php echo 'px_' . $opt_name[3]; ?>" name="<?php echo 'px_' . $opt_name[3]; ?>" value="<?php echo $px_opt_val[3]; ?>" size="1">
					<input type="text" class="itro_text_input" style="<?php if( itro_get_option('select_' . $opt_name[3]) != 'perc' ) { echo 'display:none;'; } ?>" id="<?php echo 'perc_' . $opt_name[3]; ?>" name="<?php echo 'perc_' . $opt_name[3]; ?>" value="<?php echo $perc_opt_val[3]; ?>" size="1">
					<select id="select_<?php echo $opt_name[3]; ?>" name="select_<?php echo $opt_name[3]; ?>" style="position:relative; left:7px;">
						<option value="px" onClick="itro_enable_<?php echo $opt_name[3]; ?>('px')" <?php if(itro_get_option('select_' . $opt_name[3])=='px') {echo 'selected="select"';} ?>>px</option>
						<option value="perc" onClick="itro_enable_<?php echo $opt_name[3]; ?>('perc')" <?php if(itro_get_option('select_' . $opt_name[3])=='perc') {echo 'selected="select"';} ?>>%</option>
					</select>
				</div>
				
				<!-- popup height !-->
				<div id="<?php echo $opt_name[17]; ?>_div" style="display:table; height:10px; padding-bottom:5px;">
					<span style="float:left;" ><?php _e("Popup height:", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Select auto to adapt popup height to the content",'itro-plugin');?>" ></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[17] , $opt_val[17] , 0 , 750 , 5, 0, 'px') ?>
					<?php itro_slidebar( $opt_name[17] , $opt_val[17] , 0 , 100 , 1, 0, 'perc') ?>
					<input type="text" class="itro_text_input" style="<?php if( itro_get_option('select_' . $opt_name[17]) != 'px' ) { echo 'display:none;'; } ?>" id="<?php echo 'px_' . $opt_name[17]; ?>" name="<?php echo 'px_' . $opt_name[17]; ?>" value="<?php echo $px_opt_val[17]; ?>" size="1">
					<input type="text" class="itro_text_input" style="<?php if( itro_get_option('select_' . $opt_name[17]) != 'perc' ) { echo 'display:none;'; } ?>" id="<?php echo 'perc_' . $opt_name[17]; ?>" name="<?php echo 'perc_' . $opt_name[17]; ?>" value="<?php echo $perc_opt_val[17]; ?>" size="1">
					<select id="select_<?php echo $opt_name[17]; ?>" name="select_<?php echo $opt_name[17]; ?>" style="position:relative; left:7px;">
						<option value="px" onClick="itro_enable_<?php echo $opt_name[17]; ?>('px')" <?php if(itro_get_option('select_' . $opt_name[17])=='px') {echo 'selected="select"';} ?>>px</option>
						<option value="perc" onClick="itro_enable_<?php echo $opt_name[17]; ?>('perc')" <?php if(itro_get_option('select_' . $opt_name[17])=='perc') {echo 'selected="select"';} ?>>%</option>
						<option value="auto" onClick="itro_disable_<?php echo $opt_name[17]; ?>();" <?php if(itro_get_option('select_' . $opt_name[17])=='auto') {echo 'selected="select"';} ?>>auto</option>
					</select>
				</div>
				
				<!-- background and border color !-->
				<p><?php _e("Popup background color", 'itro-plugin' ); ?>
					<input type="text" class="color" name="<?php echo $opt_name[4]; ?>" value="<?php echo $opt_val[4]; ?>" size="10">&nbsp;&nbsp;&nbsp;&nbsp;
					<?php _e("Popup border color:", 'itro-plugin' ); ?>
					<input type="text" class="color" name="<?php echo $opt_name[5]; ?>" value="<?php echo $opt_val[5]; ?>" size="10">
				</p>
				
				<!-- border radius !-->
				<div id="<?php echo $opt_name[20]; ?>_div" style="display:table; height:10px; padding-bottom:5px;" >
					<span style="float:left;" ><?php _e("Popup border radius(px):", 'itro-plugin' ); ?></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[20] , $opt_val[20] , 0 , 200 , 1, 0, '') ?>				
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[20]; ?>" name="<?php echo $opt_name[20]; ?>" value="<?php echo $opt_val[20]; ?>" size="1">
				</div>
				
				<!-- border width !-->
				<div id="<?php echo $opt_name[27]; ?>_div" style="display:table; height:10px; padding-bottom:5px;" >
					<span style="float:left;" ><?php _e("Popup border width(px):", 'itro-plugin' ); ?></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[27] , $opt_val[27] , 0 , 50 , 1, 0, '') ?>				
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[27]; ?>" name="<?php echo $opt_name[27]; ?>" value="<?php echo $opt_val[27]; ?>" size="1">
				</div>
				
				<!-- popup padding !-->
				<div id="<?php echo $opt_name[31]; ?>_div" style="display:table; height:10px; padding-bottom:5px;" >
					<span style="float:left;" ><?php _e("Popup padding(px):", 'itro-plugin' ); ?></span>
					&nbsp;&nbsp;&nbsp;
					<?php itro_slidebar( $opt_name[31] , $opt_val[31] , 0 , 100 , 1, 0, '') ?>				
					<input type="text" class="itro_text_input" id="<?php echo $opt_name[31]; ?>" name="<?php echo $opt_name[31]; ?>" value="<?php echo $opt_val[31]; ?>" size="1">
				</div>
				
				<!-- background image !-->
				<p><?php _e("BACKGROUND IMAGE",'itro-plugin');?></p>
				<a href="<?php if ( itro_get_option('background_source') == NULL ) {echo '#' . $opt_name[22];} else { echo itro_get_option('background_source'); }?>"><?php _e('Show image','itro-plugin')?></a>
				
				<input type="radio" name="<?php echo $opt_name[22];?>" value="" <?php if($opt_val[22]== 'no' || $opt_val[22]== NULL ){echo 'checked="checked"';} ?>/>
				<?php _e("No background",'itro-plugin');?><br>
				<input type="radio" id="yes_bg" name="<?php echo $opt_name[22];?>" value="yes" <?php if( $opt_val[22]== 'yes' ){echo 'checked="checked"';} ?>/>
				<input class="upload" onClick="select(); document.getElementById('yes_bg').checked=true" type="text" name="background_source" size="50" value="<?php echo itro_get_option('background_source'); ?>" />
				<input class="button" id="upload_button" type="button" name="bg_upload_button" value="<?php _e('Upload Image','itro-plugin') ?>" />
				
				<!-- popup position !-->
				<p><?php _e("Popup position:", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Setting ABSOLUTE the popup will be static on the page. Setting FIXED it will scroll with the page.",'itro-plugin');?>" >
					<select name="<?php echo $opt_name[16]; ?>"  style="min-width:100px;">
						<option value="absolute" <?php if(itro_get_option($opt_name[16])=='absolute') {echo 'selected="select"';} ?> >Absolute</option>
						<option value="fixed" <?php if(itro_get_option($opt_name[16])=='fixed') {echo 'selected="select"';} ?> >Fixed</option>
					</select>
				</p>
				
				<!-- automatic margin !-->
				<div id="<?php echo $opt_name[30]; ?>_div">
					<p>
						<input id="<?php echo $opt_name[30]; ?>" type="checkbox" name="<?php echo $opt_name[30]; ?>" value="yes" <?php if(itro_get_option($opt_name[30])=='yes' ){echo 'checked="checked"';} ?> />
						<span id="span_<?php echo $opt_name[30]; ?>" onclick="itro_mutual_check('<?php echo $opt_name[30]; ?>','','');"><?php _e("Automatic top margin:", 'itro-plugin' ); ?></span>
						<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("The system will try to auto center the popup, in case of problem deselect this option",'itro-plugin');?>" >
					</p>
					
					<!-- popup top margin !-->
					<div id="top_margin_slider" style=" <?php if(itro_get_option($opt_name[30])=='yes' ){echo 'display:none';} else {echo 'display:table';} ?>; height:10px; padding-bottom:5px;" >
						<span style="float:left;" ><?php _e("Popup top margin(px):", 'itro-plugin' ); ?></span>
						&nbsp;&nbsp;&nbsp;
						<?php itro_slidebar( $opt_name[1] , $opt_val[1] , 0 , 750 , 5, 0, '') ?>				
						<input type="text" class="itro_text_input"  id="<?php echo $opt_name[1]; ?>" name="<?php echo $opt_name[1]; ?>" value="<?php echo $opt_val[1]; ?>" size="1">
					</div>
					<?php echo itro_show_hide(array('top_margin_slider'), $opt_name[30], 'table',false, array('yellow',300)); ?>
					
					
					<!-- background opacity !-->
					<div style="display:table; height:10px; padding-bottom:5px;">
						<span style="float:left;" ><?php _e("Background opacity", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Set the opacity of opaque background under the popup.",'itro-plugin');?>" ></span>
						&nbsp;&nbsp;&nbsp;
						<?php itro_slidebar( $opt_name[25] , $opt_val[25] , 0 , 1 , 0.05 , 2 , '' ) ?>
						<input type="text" class="itro_text_input" id="<?php echo $opt_name[25]; ?>" name="<?php echo $opt_name[25]; ?>" value="<?php echo $opt_val[25];?>" size="1">
					</div>
					<script type="text/javascript">
						document.getElementById("<?php echo $opt_name[25]; ?>_slider_container").addEventListener("mousedown", update, false);
						document.getElementById("<?php echo $opt_name[25]; ?>_slider_container").addEventListener("mouseup", update, false);
						document.getElementById("<?php echo $opt_name[25]; ?>_slider_container").addEventListener("keydown", update, false);
						function update()
						{
							document.getElementById("<?php echo $opt_name[26]; ?>").style.opacity = document.getElementById("<?php echo $opt_name[25]; ?>").value;
							document.addEventListener("mousemove", update, false);
						}
						function stop()
						{
							document.removeEventListener("mousemove", update, false);
						}
					</script
					
					<!-- opaco color !-->
					<p><?php _e("Opaque background color", 'itro-plugin' ); ?>
						<input type="text" class="color" id="<?php echo $opt_name[26]; ?>" name="<?php echo $opt_name[26]; ?>" style="opacity:<?php echo $opt_val[25];?> ;" value="<?php echo $opt_val[26]; ?>" size="10">&nbsp;&nbsp;&nbsp;&nbsp;
					</p>
					
					<!-- close cross selection !-->
					<p><?php _e("Close cross", 'itro-plugin' ); ?> <img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>" title="<?php _e("Select the colour combination of the closing cross on the upper right of popup.",'itro-plugin');?>" >
						<select name="<?php echo $opt_name[33]; ?>"  style="min-width:100px;">
							<option value="black" <?php if(itro_get_option($opt_name[33])=='black') {echo 'selected="select"';} ?> ><?php _e("Black", 'itro-plugin' ); ?></option>
							<option value="white" <?php if(itro_get_option($opt_name[33])=='white') {echo 'selected="select"';} ?> ><?php _e("White", 'itro-plugin' ); ?></option>
							<option value="white_border" <?php if(itro_get_option($opt_name[33])=='white_border') {echo 'selected="select"';} ?> ><?php _e("White with border", 'itro-plugin' ); ?></option>
						</select>
					</p>
				</div>
				
			</div>
			
			<!------------ Age restriction option  ---------->
			<?php echo itro_onOff('ageRestSettings','hidden');?>
			<p class="wpstyle" onClick="onOff_ageRestSettings();"><?php _e("Age restriction settings:", 'itro-plugin' ); ?> </p>
			<div id="ageRestSettings">
				<p>
				<input id="<?php echo $opt_name[6]; ?>" type="checkbox" name="<?php echo $opt_name[6]; ?>" value="yes" <?php if($opt_val[6]=='yes' ){echo 'checked="checked"';} ?> />
				<span id="span_<?php echo $opt_name[6]; ?>" onclick="itro_mutual_check('<?php echo $opt_name[6]; ?>','','');"><?php _e("Enable age validation", 'itro-plugin' ); ?></span>
				</p>
				<div id="<?php echo $opt_name[6]; ?>_div">
					<p><?php _e("Enter button text:", 'itro-plugin' ); ?> 
						<input type="text" name="<?php echo $opt_name[7]; ?>" value="<?php echo $opt_val[7]; ?>" placeholder="<?php _e("i.e.: I AM OVER 18 - ENTER", 'itro-plugin' ); ?>"  size="40">
					</p>
					
					<div id="<?php echo $opt_name[6] . '_advanced_1'; ?>">				
						<p><?php _e("Enter button background color:", 'itro-plugin' ); ?> 
							<input type="text" class="color" name="<?php echo $opt_name[10]; ?>" value="<?php echo $opt_val[10]; ?>" size="10">
						</p>
						<p><?php _e("Enter button border color:", 'itro-plugin' ); ?> 
							<input type="text" class="color" name="<?php echo $opt_name[11]; ?>" value="<?php echo $opt_val[11]; ?>" size="10">
						</p>
						<p><?php _e("Enter button font color:", 'itro-plugin' ); ?> 
							<input type="text" class="color" name="<?php echo $opt_name[14]; ?>" value="<?php echo $opt_val[14]; ?>" size="10">
						</p>
					</div>
						
					<p><?php _e("Leave button text:", 'itro-plugin' ); ?> 
						<input type="text" name="<?php echo $opt_name[8]; ?>" value="<?php echo $opt_val[8] ?>" placeholder="<?php _e("i.e.: I AM UNDER 18 - LEAVE", 'itro-plugin' ); ?>" size="40">
					</p>
					<p><?php _e("Leave button url:", 'itro-plugin' ); ?> 
						<input type="text" name="<?php echo $opt_name[9]; ?>" value="<?php echo $opt_val[9]; ?>" placeholder="<?php _e("i.e.: http://www.mysite.com/leave.html", 'itro-plugin' ); ?>" size="40">
					</p>
					
					<div id="<?php echo $opt_name[6] . '_advanced_2'; ?>">
						<p><?php _e("Leave button background color:", 'itro-plugin' ); ?> 
							<input type="text" name="<?php echo $opt_name[12]; ?>" value="<?php echo $opt_val[12]; ?>" size="10">
						</p>
						<p><?php _e("Leave button border color:", 'itro-plugin' ); ?> 
							<input type="text" class="color" name="<?php echo $opt_name[13]; ?>" value="<?php echo $opt_val[13]; ?>" size="10">
						</p>
						<p><?php _e("Leave button font color:", 'itro-plugin' ); ?> 
							<input type="text" class="color" name="<?php echo $opt_name[15]; ?>" value="<?php echo $opt_val[15]; ?>" size="10">
						</p>
					</div>
					
				</div>
			</div>
			<?php echo itro_show_hide(array($opt_name[6] . '_div'), $opt_name[6], 'table', true, array('yellow',300)); ?>
		</div>
		<hr>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" target="_blank" class="button" onClick="window.open('<?php echo get_site_url() . '/?page_id=' . itro_get_option('preview_id'); ?>')" value="<?php echo _e('Preview page','itro-plugin' )?>">
		</p>
	</div>

	<div id="rightColumn">
		<!-- A simple not annoying banner, please do not remove, we use it to quickly comunicate with you about premium and free! !-->
		<p class="wpstyle" onClick="jQuery('#premium_ads').toggle('blind');"><?php _e('ITRO Popup messages', 'itro-plugin'); ?> </p>
		<div id="premium_ads" style="text-align: center;">
			<a target="_blank" href="http://www.itro.eu"><img title="TRY IT FOR FREE!!!" src="http://www.itroteam.com/plugins/premium_banner.png"></a>
		</div>
		
		<input type="hidden" name="<?php echo $submitted_form; ?>" value="Y">
		<!------- Custom html field -------->
		<p class="wpstyle" onClick="jQuery('#customHtmlForm').toggle();"><?php _e("Your text (or HTML code:)", 'itro-plugin' ); ?> </p>
		<div id="customHtmlForm">
			<?php					
			$content = stripslashes($field_value[0]);
			wp_editor( $content, 'custom_html', array('textarea_name'=> 'custom_html','teeny'=>false, 'media_buttons'=>true, 'wpautop'=>false ) ); ?>
			<br><br>
			<hr>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" target="_blank" class="button" onClick="window.open('<?php echo get_site_url() . '/?page_id=' . itro_get_option('preview_id'); ?>')" value="<?php echo _e('Preview page','itro-plugin' )?>">
			</p>
		</div>
	</div>
</form>
<div id="rightColumn2">
	<!-- Donation form - please don't change or remove!!! thanks !-->
	<div id="donateForm">
		<h3><?php _e("Like it? Offer us a coffee! ;-)","itro-plugin")?> <img width="35px" src="<?php echo itroImages . 'coffee.png';?>"></h3>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick"/>
			<input type="hidden" name="hosted_button_id" value="WNRVCFYD3ULQ8"/>
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
			<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1"/>
		</form>
	</div>
	<p class="wpstyle" onClick="jQuery('#debug_info').toggle();"><?php _e("System Status", 'itro-plugin' ); ?> </p>
	<form method="POST" action="" id="debug_info" style="display:none;">
		<?php echo itro_get_serverinfo(); ?>
	</form>
</div>

<form id="delete_data" method="post" style="clear:both;">
	<br>
	<hr>
	<input type="hidden" name="delete_data_hidden" value="Y">
	<input type="checkbox" id="delete_data" name="delete_data" value="yes" <?php if(itro_get_option('delete_data')=='yes' ){echo 'checked="checked"';} ?> />
	<span><?php _e("DELETE ALL DATA ON PLUGIN UNISTALL", 'itro-plugin' ); ?></span>
	<img style="vertical-align:super; cursor:help" src="<?php echo itroImages . 'question_mark.png' ; ?>"title="<?php _e('Check this box if you want to delete or maintain database tables. It is usefull if you have to try to install again the plugin, without lost your settings.','itro-plugin');?>" >
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;
</form>

<?php 
				$itro_hide_args = array(
				$opt_name[24] . '_div',
				$opt_name[29] . '_div',
				$opt_name[17] . '_div',
				$opt_name[20] . '_div',
				$opt_name[27] . '_div',
				$opt_name[30] . '_div',
				$opt_name[31] . '_div',
				$opt_name[23] . '_div',
				$opt_name[6] . '_advanced_1',
				$opt_name[6] . '_advanced_2',
				);
				echo itro_show_hide( $itro_hide_args, $opt_name[28], 'table', true, array('yellow',10000)); 
?>