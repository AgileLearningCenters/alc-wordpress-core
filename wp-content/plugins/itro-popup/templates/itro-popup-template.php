<?php 
function itro_popup_template()
{ ?>
	<div id="itro_popup" style="visibility:hidden; display: none;">
	<?php
		if( itro_get_option('age_restriction') == NULL ) /* age restriction off */
		{
			if( itro_get_option('popup_time') != 0 )
			{
				echo '<div id="popup_countdown" align="center">';
				_e('This popup will be closed in: ','itro-plugin');
				echo '<b id="timer"></b></div>';
			}
			
			$selected_cross = itroPath . 'images/close-icon.png'; /* default image (black cross) */
			switch( itro_get_option('cross_selected') )
			{
				case 'white':
					$selected_cross = itroPath . 'images/close-icon-white.png';
				break;
				case 'white_border':
					$selected_cross = itroPath . 'images/close-icon-white-border.png';
				break;
			}
			echo '<img id="close_cross" src="' . $selected_cross . '" title="';
			_e('CLOSE','itro-plugin');
			echo '" onclick="jQuery(\'#itro_popup\').fadeOut(function(){itro_opaco.style.visibility=\'hidden\';})">';
		}?>
		<div id="popup_content"><?php
			$custom_field = stripslashes(itro_get_field('custom_html')); /* insert custom html code  */
			echo do_shortcode( str_replace("\r\n",'',$custom_field) ); /* return the string whitout new line */
			if ( itro_get_option('age_restriction') == 'yes' ) 
			{?>
				<p id="age_button_area" style="text-align: center;">
					<input type="button" id="ageEnterButton" onClick="itro_set_cookie('popup_cookie','one_time_popup',<?php echo itro_get_option('cookie_time_exp'); ?>); jQuery('#itro_popup').fadeOut(function(){itro_opaco.style.visibility='hidden';})" value="<?php echo itro_get_option('enter_button_text');?>">
					<input type="button" id="ageLeaveButton" onClick="javascript:window.open('<?php echo itro_get_option('leave_button_url')?>','_self');" value="<?php echo itro_get_option('leave_button_text');?>">
				</p><?php
			}
			?>
		</div> 
	</div>
	<div id="itro_opaco" style="visibility:hidden"></div>
<?php
}
?>