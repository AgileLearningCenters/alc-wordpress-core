<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.
*/
function itro_style() { ?>
	<style>
		#age_button_area
		{
			padding-top:10px;
			position: relative;
			width: 100%;
			bottom: 5px;
			padding-top:5px;
		}
		
		#ageEnterButton
		{
			border-color:<?php echo itro_get_option('enter_button_border_color')?>;
			background:<?php echo itro_get_option('enter_button_bg_color')?>;
			color: <?php echo itro_get_option('enter_button_font_color');?>;
		}

		#ageLeaveButton
		{
			border-color:<?php echo itro_get_option('leave_button_border_color')?>;
			background:<?php echo itro_get_option('leave_button_bg_color')?>;
			color: <?php echo itro_get_option('leave_button_font_color');?>;
		}
		
		#popup_content
		{
			<?php 
			if( itro_get_option('select_popup_height') == 'px' || itro_get_option('select_popup_height') == '%' ) 
			{echo 'overflow-y:auto;' ;} 
			else { echo 'overflow-y:hidden;' ;}
			?>
			overflow-x: auto;
			height: 100%;
			width:100%;
		}
		
		#itro_popup
		{
			position: <?php echo itro_get_option('popup_position');?>;
			background-image: <?php if (itro_get_option('background_select') != NULL ) { echo 'url("' . itro_get_option('background_source') . '");'; } ?>
			background-repeat: no-repeat;
			background-position: center center;
			margin: 0 auto;
			left:30px;
			right:30px;
			z-index: 2147483647 !important;
			<?php if( itro_get_option('popup_padding') != NULL ) { echo 'padding:' . itro_get_option('popup_padding') . 'px !important;'; }?>
			<?php 
			if( itro_get_option('auto_margin_check') == NULL  ) 
			{ 
				if (itro_get_option('popup_top_margin') != NULL ) 
				{ echo 'top:' . itro_get_option('popup_top_margin') . 'px;' ; }
				else 
				{echo 'top: 0px;' ;}
			}
			if (itro_get_option('popup_border_color') != NULL )
			{
				echo 'border: solid;';
				echo 'border-color:' . itro_get_option('popup_border_color') . ';';
			}
			?>
			border-radius: <?php echo itro_get_option('popup_border_radius'); ?>px;
			border-width: <?php echo itro_get_option('popup_border_width'); ?>px;
			width: <?php 
					if( itro_get_option('select_popup_width') == 'px') { echo itro_get_option('px_popup_width') . 'px'; }
					if( itro_get_option('select_popup_width') == 'perc') { echo itro_get_option('perc_popup_width') . '%'; }
					?>;
			height: <?php 
					if( itro_get_option('select_popup_height') == 'px') { echo itro_get_option('px_popup_height') . 'px'; }
					if( itro_get_option('select_popup_height') == 'perc') { echo itro_get_option('perc_popup_height') . '%'; }
					?>;
			background-color: <?php echo itro_get_option('popup_background'); ?>;
			<?php if( itro_get_option('show_countdown') != NULL ) { echo 'padding-bottom: 15px;'; } ?>
		}
		
		#close_cross
		{
			cursor:pointer; 
			width:20px; 
			position:absolute; 
			top:-22px; 
			right:-22px;
		}

		#popup_countdown 
		{
			color: <?php echo itro_get_option('count_font_color') ?>;
			width: 100%;
			padding-top: <?php if( itro_get_option('show_countdown') != 'yes' ) { echo '0px'; } else {echo '1px';}?> ;
			padding-bottom:<?php if( itro_get_option('show_countdown') != 'yes' ) { echo '0px'; } else {echo '1px';}?> ;
			background-color: <?php echo itro_get_option('popup_border_color');?>;
			height: <?php if( itro_get_option('show_countdown') != 'yes' ) { echo '0px'; }?> ;
			overflow: hidden;
			position:absolute;
			bottom:0px;
			left:0px;
			border-bottom-left:<?php echo itro_get_option('popup_border_radius'); ?>px;
			border-bottom-right:<?php echo itro_get_option('popup_border_radius'); ?>px;
		}

		#itro_opaco{
			position:fixed;
			background-color:  <?php echo itro_get_option('opaco_bg_color'); ?>;
			font-size: 10px;
			font-family: Verdana;
			top: 100px;    
			width: 100%;
			height: 100%;
			z-index: 2147483646 !important;
			left: 0px ;
			right: 0px;
			top: 0px;
			bottom: 0px;
			opacity: <?php echo itro_get_option('popup_bg_opacity'); ?> ;
			filter:alpha(opacity = <?php echo ( itro_get_option('popup_bg_opacity') * 100); ?>); /* For IE8 and earlier */
		}<?php
		
		if( itro_get_option('disable_mobile') == 'yes' )
		{
			echo '
			@media screen and (max-width: 1024px)
			{
				#itro_popup{display: none !important;}
				#itro_opaco{display: none !important;}
			}';
		}?>
	</style>
<?php 
}
?>