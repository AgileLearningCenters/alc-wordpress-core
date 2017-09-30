<?php
/**
 * This file could be used to catch submitted form data. When using a non-configuration
 * view to save form data, remember to use some kind of identifying field in your form.
 */

if(self::get_thermometer_widget_option('thousands') == ' (space)'){
	$sep = ' ';
}
elseif(self::get_thermometer_widget_option('thousands') == '(none)'){
	$sep = '';
}
else{
	$sep = substr(self::get_thermometer_widget_option('thousands'),0,1);
}
?>
<p>Target value: <b><span style="color: <?php echo self::get_thermometer_widget_option('colour_picker3'); ?>;" title="Target text color on thermometers = <?php echo self::get_thermometer_widget_option('colour_picker3'); ?>">
<?php
if(self::get_thermometer_widget_option('trailing') == false){
    echo self::get_thermometer_widget_option('currency').
    number_format(self::get_thermometer_widget_option('target_string'),0,'.',$sep);
}
else{
    echo number_format(self::get_thermometer_widget_option('target_string'),0,'.',$sep).
    self::get_thermometer_widget_option('currency');
}?>
</b>
<span style="padding-left: 40px;" title="Use this shortcode to insert the target value in posts/pages">Shortcode: <code style="font-family: monospace;">[therm_t]</code></span></p></p>

<p>Raised value: <b><span style="color: <?php echo self::get_thermometer_widget_option('colour_picker4'); ?>;" title="Raised text color on thermometers = <?php echo self::get_thermometer_widget_option('colour_picker4'); ?>">
<?php
if(self::get_thermometer_widget_option('trailing') == false){
        echo self::get_thermometer_widget_option('currency').
    number_format(self::get_thermometer_widget_option('raised_string'),0,'.',$sep);
}
else{
    echo number_format(self::get_thermometer_widget_option('raised_string'),0,'.',$sep).
    self::get_thermometer_widget_option('currency');
}?>
</span></b>
<span style="padding-left: 40px;" title="Use this shortcode to insert the raised value in posts/pages">Shortcode: <code style="font-family: monospace;">[therm_r]</code></span></p>


<p style="font-style: italic;font-size: 9pt;">To change these global values, hover over the widget title and click on the "Configure</span>" link, or visit the <a href="options-general.php?page=donation-thermometer/donation_therm.php">plugin settings</a> page.</p>

