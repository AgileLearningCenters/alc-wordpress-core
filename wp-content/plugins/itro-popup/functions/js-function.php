<?php
/*
Copyright 2013  I.T.RO.® (email : support.itro@live.com)
This file is part of ITRO Popup Plugin.
*/

/* ------------ LOAD SCRIPTS FOR POPUP VISUALIZATION */
function itro_popup_js()
{ ?>
	<script type="text/javascript">
	/* init var */
	itro_cookie_expiration = <?php echo itro_get_option('cookie_time_exp'); ?>;
	itro_is_preview = <?php if ( itro_get_option('preview_id') == get_the_id() ){echo 'true';}else{ echo 'false'; } ?>;
	
	/* pass true if is the preview page. used for cookie control via js due W3 total cache or similar */
	itro_is_preview = <?php if( itro_get_option('preview_id') == get_the_id() ){ echo 'true'; }else{ echo 'false'; } ?>;
	<?php
		if (itro_get_option('age_restriction') == NULL) /* OFF age validation */
		{
			echo 'itro_age_restriction = false;';
			if( itro_get_option('popup_unlockable') != 'yes' )
			{ ?>
				document.onkeydown = function(event) 
				{
					event = event || window.event;
					var key = event.keyCode;
					if(key==27)
					{
						jQuery("#itro_popup").fadeOut(function() {itro_opaco.style.visibility='Hidden';});
					} 
				}; <?php
			}
			
			if( itro_get_option('popup_delay') != 0 ) /* if is set the delay */
			{ ?>
				var delay = <?php echo itro_get_option('popup_delay') . '+' . '1'; ?> ;
				interval_id_delay = setInterval(function(){popup_delay();},1000);
			<?php
			}
			else /* if popup delay is not setted */
			{?>
				itro_enter_anim();
			<?php
			}
			
			/* start the timer only if popup seconds are != 0 to avoid js errors */
			if ( itro_get_option('popup_time') != 0 )
			{ ?>
				var popTime=<?php 
							if( itro_get_option('popup_delay')  != 0 )
							{
								echo itro_get_option('popup_time') . '+' . itro_get_option('popup_delay');
							}
							else
							{
								echo itro_get_option('popup_time');
							}
							?>;
				interval_id = setInterval(function(){popTimer()},1000); /* the countdown  */
				<?php
			}
		}
		else /* if age restriction is enabled */
		{
			if( itro_get_option('popup_delay') != 0 )
			{ ?>
				var delay = <?php echo itro_get_option('popup_delay') . '+' . '1'; ?> ;
				interval_id_delay = setInterval(function(){popup_delay();},1000);
			<?php
			}
			else
			{?>
				itro_enter_anim();
			  <?php
			}
		}
		
		/* ------- AUTOMATIC TOP MARGIN */
		if( itro_get_option('auto_margin_check') != NULL )
		{?>
			var browserWidth = 0, browserHeight = 0;
			setInterval(function(){marginRefresh()},100); /* refresh every 0.1 second the popup top margin (needed for browser window resizeing) */
			<?php 
		}?>
	</script>
<?php	
}

/* ------------- LOAD SCRIPT TO SHOW SLIDEBAR */
function itro_slidebar($slider_target_id,$slider_value,$slider_min,$slider_max,$slider_step,$slider_tofixed,$multi_slider)
{
	if($multi_slider != NULL)
	{
		if( itro_get_option('select_' . $slider_target_id) != $multi_slider ) /* esempio select_popup_width = 'px' o 'perc' */
		{ 
			$slider_display = 'display:none;';
		}
		else 
		{
			$slider_display = '';
		}
		$target_opt_name = $slider_target_id;
		$slider_container_id = $slider_target_id . '_slider_container';
		
		$js_slider_container_id = $multi_slider . '_' . $slider_target_id . '_slider_container';
		$js_slider_id = $multi_slider . '_' . $slider_target_id . '_slider';
		$slider_target_id = $multi_slider . '_' . $slider_target_id;
	}
	else
	{
		$js_slider_container_id = $slider_target_id . '_slider_container';
		$js_slider_id = $slider_target_id . '_slider';
		$slider_display = '';
	}
	?>

	<div id="<?php echo $js_slider_container_id; ?>" style="<?php echo $slider_display; ?>position: relative; float:right; top:12px; left:25px; width:150px; height:2px; background-color:black; border-radius:15px;">
		<div id="<?php echo $js_slider_id; ?>" style="left:<?php echo ( (itro_get_option($slider_target_id)/$slider_max)*150 );  ?>px; border-radius:15px; position: relative; top:-5px; cursor:pointer; width:15px; height:12px; background-color:gray;"></div>
	</div>
	<script type="text/javascript">
		document.getElementById("<?php echo $js_slider_container_id; ?>").addEventListener("mousedown", <?php echo $slider_target_id; ?>_start_slider,false);
		document.addEventListener("mousemove", itro_pos,false);
		
		function <?php echo $slider_target_id ?>_start_slider()
		{	
			document.addEventListener("mousemove",<?php echo $slider_target_id ?>_move_slider);
			document.addEventListener("mouseup",<?php echo $slider_target_id ?>_stop_slider);
			if( (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left) >= 0 && (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left) <= parseInt(document.getElementById("<?php echo $js_slider_container_id; ?>").style.width))
			{
				document.getElementById("<?php echo $js_slider_id;?>").style.left = x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left - 7 + "px";
				document.getElementById("<?php echo $slider_target_id; ?>").value = (Math.round( ( (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left)/150*<?php echo $slider_max; ?> )/<?php echo $slider_step; ?> )*<?php echo $slider_step; ?>).toFixed(<?php echo $slider_tofixed; ?>);
			}
		}

		function <?php echo $slider_target_id ?>_move_slider()
		{
			if( (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left) >= 0 && (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left) <= parseInt(document.getElementById("<?php echo $js_slider_container_id; ?>").style.width) )
			{
				document.getElementById("<?php echo $js_slider_id;?>").style.left = x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left - 7 + "px";
				document.getElementById("<?php echo $slider_target_id; ?>").value = (Math.round( ( (x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left)/150*<?php echo $slider_max; ?> )/<?php echo $slider_step; ?> )*<?php echo $slider_step; ?>).toFixed(<?php echo $slider_tofixed; ?>);
			}
			if(x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left < 0)
			{
				document.getElementById("<?php echo $js_slider_id;?>").style.left = -7 + "px";
				<?php echo $slider_target_id; ?>_temp1 = 0;
				document.getElementById("<?php echo $slider_target_id; ?>").value = <?php echo $slider_target_id; ?>_temp1.toFixed(<?php echo $slider_tofixed; ?>);
			}
			if(x_pos - document.getElementById("<?php echo $js_slider_container_id; ?>").getBoundingClientRect().left > parseInt(document.getElementById("<?php echo $js_slider_container_id; ?>").style.width))
			{
				document.getElementById("<?php echo $js_slider_id;?>").style.left = parseInt(document.getElementById("<?php echo $js_slider_container_id; ?>").style.width) - 7 + "px";
				<?php echo $slider_target_id; ?>_temp2 = <?php echo $slider_max; ?>;
				document.getElementById("<?php echo $slider_target_id; ?>").value = <?php echo $slider_target_id; ?>_temp2.toFixed(<?php echo $slider_tofixed; ?>);
			}
		}

		function <?php echo $slider_target_id ?>_stop_slider()
		{
			document.removeEventListener("mousemove",<?php echo $slider_target_id ?>_move_slider)
			
		}
		
		<?php 
		if($multi_slider != NULL)
		{
			$slider_target_id = $target_opt_name; ?>
		
			/* ---function disable */
			function itro_disable_<?php echo $slider_target_id; ?>()
			{
				document.getElementById("px_<?php echo $slider_target_id; ?>").style.display = "none";
				document.getElementById("perc_<?php echo $slider_target_id; ?>").style.display = "none";
				document.getElementById("px_<?php echo $slider_container_id; ?>").style.display = "none";
				document.getElementById("perc_<?php echo $slider_container_id; ?>").style.display = "none";
			}
			
			/* ---function enable */
			function itro_enable_<?php echo $slider_target_id; ?>(dim_type)
			{
				if(dim_type == 'perc') 
				{
					document.getElementById("perc_<?php echo $slider_container_id; ?>").style.display = "block";
					document.getElementById("perc_<?php echo $slider_target_id; ?>").style.display = "inline";
					document.getElementById("px_<?php echo $slider_target_id; ?>").style.display = "none";
					document.getElementById("px_<?php echo $slider_container_id; ?>").style.display = "none";
				}
				if(dim_type == 'px') 
				{
					document.getElementById("px_<?php echo $slider_container_id; ?>").style.display = "block";
					document.getElementById("px_<?php echo $slider_target_id; ?>").style.display = "inline";
					document.getElementById("perc_<?php echo $slider_target_id; ?>").style.display = "none";
					document.getElementById("perc_<?php echo $slider_container_id; ?>").style.display = "none";
				}
			} <?php
		} ?>
	</script><?php
} 

/* show and hide parts of admin pannel such as top margin and basic settings */
function itro_show_hide($hide_target_id, $hide_shooter_id, $display_val, $inverted, $highlight_opt)
{?>
	<script type="text/javascript">
	
	<?php 
	if ($inverted == 'false') /* decide if elements start hidden or visible: if inverted==true -> if $hide_shooter_id is checked -> start visible else start hidden */
	{ $check_condition = 'yes'; }
	else
	{ $check_condition = NULL; }
	
	if( itro_get_option($hide_shooter_id) == $check_condition)
	{
		foreach($hide_target_id as $single_targer_id)
		{
			echo 'document.getElementById("' . $single_targer_id . '").style.display = "table";';
		}
		unset($single_targer_id);
	}
	else
	{
		foreach($hide_target_id as $single_targer_id)
		{
			echo 'document.getElementById("' . $single_targer_id . '").style.display = "none";';
		}
		unset($single_targer_id);
	}
	?>

	function <?php echo $hide_shooter_id; ?>_itro_show_hide()
	{<?php
		foreach($hide_target_id as $single_targer_id)
		{?>
			if( document.getElementById("<?php echo $single_targer_id; ?>").style.display != "none" ) 
				{jQuery("#<?php echo $single_targer_id; ?>").fadeOut("fast");}
			else 
				{
					jQuery("#<?php echo $single_targer_id; ?>").fadeIn("fast" , function() {jQuery("#<?php echo $single_targer_id; ?>").effect( "highlight", {color:"<?php echo $highlight_opt[0];?>"}, <?php echo $highlight_opt[1];?> );});
					document.getElementById("<?php echo $single_targer_id; ?>").style.display = "table";
				}<?php
		}
		unset($single_targer_id);?>
	}
	
	function <?php echo $hide_shooter_id; ?>_stop_anim()
	{ <?php
		foreach($hide_target_id as $single_targer_id)
		{ ?>
			if ( document.getElementById("<?php echo $single_targer_id; ?>").style.display != "none" )
			{ jQuery("#<?php echo $single_targer_id; ?>").stop(true, true); } <?php
		} ?>
	}
	
	document.getElementById("<?php echo 'span_' . $hide_shooter_id; ?>").addEventListener("mousedown" , <?php echo $hide_shooter_id; ?>_stop_anim);
	document.getElementById("<?php echo $hide_shooter_id; ?>").addEventListener("mousedown" , <?php echo $hide_shooter_id; ?>_stop_anim);
	
	document.getElementById("<?php echo 'span_' . $hide_shooter_id; ?>").addEventListener("mousedown" , <?php echo $hide_shooter_id; ?>_itro_show_hide);
	document.getElementById("<?php echo $hide_shooter_id; ?>").addEventListener("mousedown" , <?php echo $hide_shooter_id; ?>_itro_show_hide);
	
	</script> <?php
}

function itro_onOff($tag_id,$overflow){
if( $overflow == 'hidden') {?>
	<style>#<?php echo $tag_id;?>{overflow:hidden;}</style><?php
} ?>
<script type="text/javascript">
	var <?php echo $tag_id;?>_flag=true;
	function onOff_<?php echo $tag_id;?>() {
	   if (<?php echo $tag_id;?>_flag==true) { document.getElementById('<?php echo $tag_id;?>').style.height='0px'; }
	   else { document.getElementById('<?php echo $tag_id;?>').style.height='auto'; }
	<?php echo $tag_id;?>_flag=!<?php echo $tag_id;?>_flag;
	}
</script>
<?php 
}

function itro_onOff_checkbox($box_id,$tag_id,$init_state){
?>
<style>#<?php echo $tag_id;?>{overflow:hidden;}</style>
<script type="text/javascript">
	function <?php echo $box_id;?>_checkbox_<?php echo $tag_id;?>()
	{
		if (<?php echo $box_id;?>.checked==<?php echo $init_state ?>) {document.getElementById('<?php echo $tag_id;?>').style.height='0px';}
		else {document.getElementById('<?php echo $tag_id;?>').style.height='auto';}
	}
</script>
<?php 
}
?>