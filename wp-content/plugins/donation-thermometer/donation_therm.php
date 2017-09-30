<?php
/*
Plugin Name: Donation Thermometer
Plugin URI: http://henrypatton.org/donation-thermometer
Description: Displays custom thermometers charting the amount of donations raised using the shortcode <code>[thermometer raised=?? target=??]</code>. Shortcodes for raised and target text values are also available for posts/pages/text widgets: <code>[therm_r]</code> and <code>[therm_t]</code>.
Version: 1.3.15
Author: Henry Patton
Author URI: http://henrypatton.org
License: GPL3

Copyright 2015  Henry Patton  (email : henry@henrypatton.org)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

define('THERMFOLDER', basename( dirname(__FILE__) ) );
define('THERM_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . THERMFOLDER ) ) );

// Specify Hooks/Filters
add_action('admin_init', 'thermometer_init_fn' );
add_action('admin_menu', 'thermometer_add_page_fn');
add_action( 'wp_dashboard_setup', array('Thermometer_dashboard_widget', 'therm_widget_init'));
 
 
function set_plugin_meta_dt($links, $file) {
    $plugin = plugin_basename(__FILE__);
    // create link
    if ($file == $plugin) {
		return array_merge(
			$links,
			array( (sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ) ),
			  sprintf('<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8NVX34E692T34">%s</a>', __('Donate') ) )
		);
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'set_plugin_meta_dt', 10, 2 );

class Thermometer_dashboard_widget{
	/**
     * The id of this widget.
     */
    const wid = 'thermometer_widget';

    /**
     * Hook to wp_dashboard_setup to add the widget.
     */

	public static function therm_widget_init() {
        
        //Register the widget...
		


        wp_add_dashboard_widget(
            self::wid,                                  //A unique slug/ID
            __( 'Donation Thermometer', 'nouveau' ),//Visible name for the widget
            array('Thermometer_dashboard_widget','therm_widget'),      //Callback for the main widget content
            array('Thermometer_dashboard_widget','therm_widget_config')       //Optional callback for widget configuration content
        );
    }

    /**
     * Load the widget code
     */
    public static function therm_widget() {
        require_once( 'therm_widget.php' );
    }

    /**
     * Load widget config code.
     *
     * This is what will display when an admin clicks
     */
    public static function therm_widget_config() {
        require_once( 'therm_widget-config.php' );
    }

    /**
     * Gets one specific option for the specified widget.
     * @param $widget_id
     * @param $option
     * @param null $default
     *
     * @return string
     */
    public static function get_thermometer_widget_option($option, $default=NULL ) {
		
		$opts = get_option( 'thermometer_options' );
        //$opts = self::get_thermometer_widget_options($widget_id);

        //If widget opts dont exist, return false
        if ( ! $opts )
            return false;

        //Otherwise fetch the option or use default
        if ( isset( $opts[$option] ) && ! empty($opts[$option]) )
            return $opts[$option];
        /*else
            return ( isset($default) ) ? $default : false;*/

    }

    /**
     * Saves an array of options for a single dashboard widget to the database.
     * Can also be used to define default values for a widget.
     *
     * @param string $widget_id The name of the widget being updated
     * @param array $args An associative array of options being saved.
     * @param bool $add_only If true, options will not be added if widget options already exist
     */
    public static function update_thermometer_widget_options($args=array(), $add_only=false )
    {
        //Fetch ALL dashboard widget options from the db...
        $opts = get_option( 'thermometer_options' );

		//Merge new options with existing ones, and add it back to the widgets array
		$opts = array_merge($opts,$args);

        //Save the entire widgets array back to the db
        return update_option('thermometer_options', $opts); //also gets validated below
    }
}


// Register settings
function thermometer_init_fn(){
	wp_register_style( 'thermStylesheet', plugins_url('style.css', __FILE__) );
	register_setting('thermometer_options', 'thermometer_options', 'thermometer_options_validate' );
	add_settings_section('thermometer_section', '', 'section_text_fn', __FILE__);
	add_settings_field('colour_picker1', 'Fill colour', 'fill_colour_fn', __FILE__, 'thermometer_section');
	add_settings_field('chkbox1', 'Show percentage?', 'setting_chk1_fn', __FILE__, 'thermometer_section');
	add_settings_field('colour_picker2', 'Percentage text colour', 'text_colour_fn', __FILE__, 'thermometer_section');
	add_settings_field('chkbox2', 'Show target?', 'setting_chk2_fn', __FILE__, 'thermometer_section');
	add_settings_field('colour_picker3', 'Target text colour', 'target_colour_fn', __FILE__, 'thermometer_section');
	add_settings_field('chkbox3', 'Show amount raised?', 'setting_chk3_fn', __FILE__, 'thermometer_section');
	add_settings_field('colour_picker4', 'Raised text colour', 'raised_colour_fn', __FILE__, 'thermometer_section');
	add_settings_field('currency', 'Currency', 'setting_dropdown_fn', __FILE__, 'thermometer_section');
	add_settings_field('trailing', 'Currency symbol follows value?', 'setting_trailing_fn', __FILE__, 'thermometer_section');
	add_settings_field('thousands', 'Thousands separator', 'setting_thousands_fn', __FILE__, 'thermometer_section');
	add_settings_field('target_string', 'Target value', 'target_string_fn', __FILE__, 'thermometer_section');
	add_settings_field('raised_string', 'Raised value', 'raised_string_fn', __FILE__, 'thermometer_section');
}

// Add sub page to the Settings Menu
function thermometer_add_page_fn() {
	$page = add_options_page('Thermometer Settings', 'Thermometer', 'administrator', __FILE__, 'options_page_fn');
	add_action( 'admin_print_styles-' . $page, 'my_admin_scripts' );
}


// Define default option settings when activate
function add_thermdefaults_fn() {
    $retrieved_options = array();
	$all_options = array("colour_picker1", "chkbox1", "colour_picker2", "chkbox2", "colour_picker3", "chkbox3", "colour_picker4", "currency","target_string", "raised_string", "thousands", "trailing");
    $defaults = array('colour_picker1'=>'#FF0000', 'chkbox1'=>1, 'colour_picker2'=>'#000000', 'chkbox2'=>1, 'colour_picker3'=>'#000000', 'chkbox3'=>1, 'colour_picker4'=>'#000000',
					  'currency'=>'£','target_string'=>'500', 'raised_string'=>'', 'thousands'=>', (comma)', 'trailing'=>2);
    $retrieved_options = maybe_unserialize( get_option( 'thermometer_options' ) );
	$retrieve_old_options = array();
	$retrieve_old_options = maybe_unserialize( get_option( 'plugin_options' ) );
	
	if (!empty($retrieve_old_options) && ($retrieved_options == '' || empty($retrieved_options))){ //copy old options to new database entry if is empty
		add_option('thermometer_options', $defaults);
		$copied_option = array();
		foreach ($all_options as $option){
			if (isset($retrieve_old_options[$option])){
				$copied_option[$option] = $retrieve_old_options[$option];
			}
			else{
				$copied_option[$option] = $defaults[$option];
			}
		}
		update_option('thermometer_options',$copied_option);
	}

    if ($retrieved_options == ''){
		add_option('thermometer_options', $defaults);
    }
    elseif ( count($retrieved_options) == 0){
		update_option('thermometer_options', $defaults);
    }
}

register_activation_hook(__FILE__, 'add_thermdefaults_fn');
add_action( 'plugins_loaded', 'add_thermdefaults_fn' ); // double-check database is updated. no upgrade hook?

function my_admin_scripts() {
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
    $coloursjs = plugins_url('donation-thermometer/colours.js');
    wp_enqueue_script( 'options_page_fn', $coloursjs , array( 'farbtastic', 'jquery' ) );
    wp_enqueue_style( 'thermStylesheet' );
}

if (!is_admin())
  add_filter('widget_text', 'do_shortcode', 11);

// ************************************************************************************************************
 
// Callback functions

// Section HTML, displayed before the first option
function  section_text_fn() {
	// preview thermometer
	/*if (file_exists(THERM_ABSPATH.'preview.png')){
	    echo '<img src="'.plugins_url("/donation-thermometer/preview.png").'" title="What your thermometer will look like" width="180" height="458" style="border: 0pt none; float: right; position: absolute; left:800px;">';
	}*/ 
	echo "<p>To place a thermometer on a post or page, type the shortcode [thermometer]. Values for your amount raised and target will need to be set here or in the shortcode:</p>
	<p>e.g. <code>[thermometer raised=1523 target=5000]</code>.</p>
	<p>The shortcode has 7 additional parameters that can be used independently:</p>
	<p><code>[thermometer raised=1523 target=5000 width=200 height=567 align=left currency=$ alt=off sep=. trailing=true]</code>.</p>
	<p>The size of the individual thermometer can be altered using <code>height=200</code> (pixels), or <code>width=20%</code> (percentage of parent container). Set only width OR height so that the correct aspect ratio is maintained.<br/>
	The thousands separator can be set for an individual thermometer using the parameter <code>sep=,</code>, or set globally below.<br/>
	Currency symbols can be set to follow numeric values using <code>trailing=true</code>, or set globally below. <br>
	The alt and title attributes of the image can also be modified, or toggled off. Use apostrophes to input a custom string, e.g. <code>[thermometer alt='Raised £1523']</code></p>
	<h2>Default plugin values:</h2>";
}


// TEXTBOX - Name: plugin_options[fill_colour]
function fill_colour_fn() {
	$options = get_option('thermometer_options');
	$fill = ($options['colour_picker1'] != '') ? $options['colour_picker1'] : '#FF0000';
	echo "<div class='form-item'><label for='color1'></label><input type='text' id='color1' name='thermometer_options[colour_picker1]' value='".$fill."' class='colorwell' />";
	echo "  e.g. red hex value = <code>#FF0000</code>";
	echo '<div id="picker" style="float: right; position: absolute; left:600px;"></div>';
}

// DROP-DOWN-BOX - Name: plugin_options[currency]
function  setting_dropdown_fn() {
	$options = get_option('thermometer_options');
	echo "<input id='currency' name='thermometer_options[currency]' size='5' type='text' value='".$options['currency']."' />";
	echo ' define a custom global currency value (also works by entering <code>currency=$</code> in the shortcode).';
}
    
function  setting_thousands_fn() {
	$options = get_option('thermometer_options');
	//$sep = ($options['thousands'] != '') ? $options['thousands'] : ',';
	$sep = ($options['thousands'] != '') ? substr($options['thousands'],0,1) : ',';
	$items = array(", (comma)",". (point)"," (space)","(none)");
	echo "<select id='drop_down2' name='thermometer_options[thousands]'>";
	foreach($items as $item) {
		$selected = (substr($item,0,1)==$sep) ? 'selected="selected"' : '';
		echo "<option value='".$item."' ".$selected.">$item</option>";
	}
	echo "</select>";
}
// CHECK-BOX - Name: thermometer_options[trailing]
function setting_trailing_fn() {
	$options = get_option('thermometer_options');
	$trailing1 = (isset($options['trailing']) && $options['trailing'] == true) ? ' checked="checked" ' : '';
	echo "<input ".$trailing1." id='plugin_trailing' name='thermometer_options[trailing]' type='checkbox' />";
}    
// CHECKBOX - Name: plugin_options[chkbox1] percentage
function setting_chk1_fn() {
	$options = get_option('thermometer_options');
	$checked1 = (isset($options['chkbox1']) && $options['chkbox1'] == true) ? ' checked="checked" ' : '';
	echo "<input ".$checked1." id='plugin_chk1' name='thermometer_options[chkbox1]' type='checkbox' />";
}
// TEXTBOX - Name: plugin_options[text_colour] 
function text_colour_fn() {
	$options = get_option('thermometer_options');
	$text = ($options['colour_picker2'] != '') ? $options['colour_picker2'] : '#000000';
	echo "<div class='form-item'><label for='color2'></label><input type='text' id='color2' name='thermometer_options[colour_picker2]' value='".$text."' class='colorwell' />";
	echo "  e.g. black hex value = <code>#000000</code>";
}
// CHECKBOX - Name: plugin_options[chkbox2] target
function setting_chk2_fn() {
	$options = get_option('thermometer_options');
	$checked2 = (isset($options['chkbox2']) && $options['chkbox2'] == true) ? ' checked="checked" ' : '';
	echo "<input ".$checked2." id='plugin_chk2' name='thermometer_options[chkbox2]' type='checkbox' />";
}
// CHECKBOX - Name: plugin_options[chkbox3] raised
function setting_chk3_fn() {
	$options = get_option('thermometer_options');
	$checked3 = (isset($options['chkbox3']) && $options['chkbox3'] == true) ? ' checked="checked" ' : '';
	echo "<input ".$checked3." id='plugin_chk3' name='thermometer_options[chkbox3]' type='checkbox' />";
} 
// TEXTBOX - Name: plugin_options[target_colour] 
function target_colour_fn() {
	$options = get_option('thermometer_options');
	$target = ($options['colour_picker3'] != '') ? $options['colour_picker3'] : '#000000';
	echo "<div class='form-item'><label for='color3'></label><input type='text' id='color3' name='thermometer_options[colour_picker3]' value='".$target."' class='colorwell' />";
	echo "  e.g. black hex value = <code>#000000</code>";
}
// TEXTBOX - Name: plugin_options[raised_colour] 
function raised_colour_fn() {
	$options = get_option('thermometer_options');
	$raised = ($options['colour_picker4'] != '') ? $options['colour_picker4'] : '#000000';
	echo "<div class='form-item'><label for='color4'></label><input type='text' id='color4' name='thermometer_options[colour_picker4]' value='".$raised."' class='colorwell' />";
	echo "  e.g. black hex value = <code>#000000</code>";
}
// TEXTBOX - Name: plugin_options[target_string]
function target_string_fn() {
	$options = get_option('thermometer_options');
	echo "<input id='target_string' name='thermometer_options[target_string]' size='15' type='number' value='".$options['target_string']."' />";
	echo '  (also <code>[therm_t]</code> value)';
}
// TEXTBOX - Name: plugin_options[raised_string]
function raised_string_fn() {
	$options = get_option('thermometer_options');
	echo "<input id='raised_string' name='thermometer_options[raised_string]' size='15' type='number' value='".$options['raised_string']."' />";
	echo '  (also <code>[therm_r]</code> value)';

}
// Display the admin options page
function options_page_fn() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Donation Thermometer Settings</h2>
		<form action="options.php" method="post">
		<?php settings_fields('thermometer_options'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p>E.g. So far we have raised £<code>[therm_r]</code> towards our £<code>[therm_t]</code> target! Thank you for your support.</p>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Update'); ?>" />
		</p>
		</form>
	</div>
<?php
}

// Validate user data for some/all of your input fields
function thermometer_options_validate($input) {

	// Check for missed entries - input default
	if ($input['colour_picker1'] ==  '' || strlen($input['colour_picker1']) !=  7){
		$input['colour_picker1'] = ('#FF0000');
	}
	if ($input['colour_picker2'] ==  '' || strlen($input['colour_picker2']) !=  7){
	    $input['colour_picker2'] = ('#000000');
	}
	if ($input['colour_picker3'] ==  '' || strlen($input['colour_picker3']) !=  7){
	    if ($input['colour_picker4'] == ''){
			$input['colour_picker3'] = ('#000000');
	    }
	    else{
			$input['colour_picker3'] = ($input['colour_picker4']); // if 4 not empty make the same
	    }
	}
	if ($input['colour_picker4'] ==  '' || strlen($input['colour_picker4']) !=  7){
	    $input['colour_picker4'] = ($input['colour_picker3']);
	}
	if (!is_numeric($input['target_string'])){
	    $input['target_string'] = '';
	}
	if (!is_numeric($input['raised_string'])){
	    $input['raised_string'] = '';
	}
		
	$input['chkbox1'] = (isset($input['chkbox1']) && true == $input['chkbox1']) ? true : false;
	$input['chkbox2'] = (isset($input['chkbox2']) && true == $input['chkbox2']) ? true : false;
	$input['chkbox3'] = (isset($input['chkbox3']) && true == $input['chkbox3']) ? true : false;
	$input['trailing'] = (isset($input['trailing']) && true == $input['trailing']) ? true : false;

	return $input; // return validated input
}

if( isset($_GET['settings-updated']) ) {
    $therms = glob(THERM_ABSPATH.'*.png');
    if (is_array($therms) && count($therms) > 0){
		foreach($therms as $v){
			unlink($v);
		}
	}
}

/////////////////////// Where the magic happens ;)...

function createtherm($raised,$target,$currency,$therm_name,$sep,$trailing,$fill){
    $options = get_option('thermometer_options');
    if($fill == ''){
	$colour_input = $options['colour_picker1'];
    }
    else{
	$colour_input = $fill;
    }
    $text_input = $options['colour_picker2'];
    $text2_input = $options['colour_picker3'];
    if ($options['colour_picker4'] == ''){
		$text3_input = $options['colour_picker3']; //if db empty after updating plugin
    }
    else{
		$text3_input = $options['colour_picker4'];
    }
    $raisedck = $options['chkbox3'];
    if($raisedck =='1'){
		$raised_cnt = (((strlen($raised) + strlen(utf8_decode($currency)))*25)+205+28); //pixel width with raised string
    }
    else{
		$raised_cnt = 240;
    }
    
    $targetck = $options['chkbox2'];
    $percentck = $options['chkbox1'];
    
    $font = THERM_ABSPATH."fonts/Arial.ttf";
    
    // calculte percentage value
    if ($target > 0 && $raised > 0){
	    $percent_raised  = ($raised/$target * 100); // avoid division by zero
	}
	else{
	    $percent_raised = 0.01;
	}
    
    $nodp = number_format($percent_raised, 0,'.',',');
    $filled = 639.2 / 100 * $percent_raised; // pixels to fill	
    $x = 162; // marker file width
    $y = 889; //height of thermometer template (pixels)
    $image_1 = THERM_ABSPATH.'images/thermometer_wide.png'; // base image file
    $draft_img = imagecreatefrompng($image_1) or die("Failed in call to imagecreate()\n");
    
    //fill thermometer
    $colour_fill = HextoRGB($colour_input);
    $user_colour = imagecolorallocate($draft_img, $colour_fill['r'], $colour_fill['g'], $colour_fill['b']);
    
    $image_2 = imagecreatefrompng(THERM_ABSPATH.'images/outline.png');
    $image_3 = imagecreatefrompng(THERM_ABSPATH.'images/markers.png');
    
    $border = imagecolorallocate($image_2,0,0,0);
    $background = imagecolorallocate($image_2,255,255,255);
    imageline($image_2,63,734-$filled,175, 734-$filled,$border); //make a line where raised to
    imagefilltoborder($image_2, 110, 734, $border, $user_colour); //fill thermometer up to level
    if ($percent_raised <= 100){ // if less or equal to 100%
		imagefilltoborder($image_2, 110, 74, $border, $background); //fill rest with white
    }
    imagecopy($draft_img, $image_2, 0, 0, 0, 0, 460, $y ); // draw outline
    imagecopy($draft_img, $image_3, 38, 57, 0, 0, $x, $y); // draw markers
    imagedestroy($image_2);
    imagedestroy($image_3);
    
    // percentage bottom
    if ($percentck == '1'){
		if ($percent_raised > 999){ // variable percent size
			$fontsize = 24;}
		elseif($percent_raised > 99){
			$fontsize = 33;
		}
		else{
			$fontsize = 40;
		}
		$width_perc = 117;
		$height_perc = 42;
		$im = imagecreatetruecolor($width_perc, $height_perc);
		$background = imagecolorallocate($im,$colour_fill['r'], $colour_fill['g'], $colour_fill['b']);
		imagefilledrectangle($im, 0, 0, $width_perc, $height_perc, $background);
		$colour_text = HextoRGB($text_input);		
		$text_color = ImageColorAllocate($im, $colour_text['r'], $colour_text['g'], $colour_text['b']);
		$box = imagettfbbox($fontsize,0,$font,$nodp.'%');
		$perc_x = ceil(($width_perc - $box[2]) / 2); //centre of box
		ImageTTFText($im, $fontsize, 0, $perc_x, 38, $text_color, $font, $nodp.'%');
		imagecopy($draft_img, $im, 57, 790, 0,0, $width_perc,$height_perc);
		imagedestroy($im);
    }
	    
    // raised
    $colour_text3 = HextoRGB($text3_input); 
    $text_color3 = ImageColorAllocate($draft_img, $colour_text3['r'], $colour_text3['g'], $colour_text3['b']); 
    if ($raisedck == '1'){
		if ($percent_raised <= 100){ // if less than 100%
			$triangle_start = (722 - $filled);
		}
		else{
			$triangle_start = (83);
		}
		$triangle = imagecreatetruecolor(24, 27); // draw triangle
		imagealphablending($triangle,false);
		$col2=imagecolorallocatealpha($triangle,255,255,255,127);
		imagefilledrectangle($triangle,0,0,24,27,$col2);
		$black = imagecolorallocate($triangle, 0, 0, 0);
		$vertices = array(0,12,23.5,0,23.5,27); // triangle points
		imagefilledpolygon($triangle,$vertices,3,$black);
		imagealphablending($triangle,true);
		imagecopy($draft_img, $triangle, 173, $triangle_start, 0, 0, 23, 26); // draw triangle
		$raised_comma = number_format($raised,0,'.',$sep);
		if ($trailing == 'false'){
			ImageTTFText($draft_img, 30, 0, 205, ($triangle_start + 26), $text_color3, $font, $currency.$raised_comma);
		}
		else{
			ImageTTFText($draft_img, 30, 0, 205, ($triangle_start + 26), $text_color3, $font, $raised_comma.' '.$currency);
		}
		imagedestroy($triangle);    
    }
    
    // target
    $colour_text2 = HextoRGB($text2_input);
    $text_color2 = ImageColorAllocate($draft_img, $colour_text2['r'], $colour_text2['g'], $colour_text2['b']);
    if ($targetck =='1'){
	$target_comma = number_format($target,0,'.',$sep);
	if ((strlen(utf8_decode($target_comma)) + strlen(utf8_decode($currency))) < 8){ // variable percent size
	    $t_fontsize = 43;}
	else{
	    $t_fontsize = 28;
	    }
	$width_targ = 230;
	$height_targ = 48;
	$im_target = imagecreatetruecolor($width_targ, $height_targ);
	$clear = imagecolorallocate($im_target, 255, 0, 0);
	imagefilledrectangle($im_target,0,0,$width_targ,$height_targ, $clear);
	
	if ($trailing == 'false'){
	    $tb = imagettfbbox($t_fontsize,0,$font,$currency.$target_comma);
	}
	else{
	    $tb = imagettfbbox($t_fontsize,0,$font,$target_comma.' '.$currency);
	}
	if ($currency =='') {
	    $tb = imagettfbbox($t_fontsize,0,$font,$target_comma);
	}
	$text_x = ceil(($width_targ - $tb[2]) / 2); //centre of box
	if($trailing == 'false'){
	    Imagettftext($draft_img, $t_fontsize, 0, $text_x, $height_targ, $text_color2, $font, $currency.$target_comma);
	}
	else{
	    Imagettftext($draft_img, $t_fontsize, 0, $text_x, $height_targ, $text_color2, $font, $target_comma.' '.$currency);
	}
	imagedestroy($im_target);    
    }
    //output therm image
    $thermpath = THERM_ABSPATH.$therm_name.'.png';
    
    //crop image
    $final_img = imagecreatetruecolor($raised_cnt,889);
    imagealphablending($final_img, false);
    imagesavealpha($final_img, true);
    $white = imagecolorallocatealpha($final_img,255,255,255,127);
    imagefill($final_img, 0, 0, $white);
    imagecopy($final_img,$draft_img,0,0,0,0,$raised_cnt,889);
 
    imagepng($final_img,$thermpath,9,PNG_ALL_FILTERS);
    
    // destroy temporary files
    imagedestroy($draft_img);
    imagedestroy($final_img);   
}
    	
// get RGB colour
	
function HextoRGB($hex){ 
    $hex = str_replace("#", "", $hex);
    $colour_rgb = array();
     
    if(strlen($hex) == 3) {
		$colour_rgb['r'] = hexdec(substr($hex, 0, 1) . $r);
		$colour_rgb['g'] = hexdec(substr($hex, 1, 1) . $g);
		$colour_rgb['b'] = hexdec(substr($hex, 2, 1) . $b);
    }
    else if(strlen($hex) == 6) {
		$colour_rgb['r'] = hexdec(substr($hex, 0, 2));
		$colour_rgb['g'] = hexdec(substr($hex, 2, 2));
		$colour_rgb['b'] = hexdec(substr($hex, 4, 2));
	}
    return $colour_rgb;
}


/////////////////////////////// shortcode stuff...

add_shortcode( 'thermometer','thermometer_graphic');	
		
function thermometer_graphic($atts){
	$atts = (shortcode_atts(
		array(
			'width' => '',
			'height' => '',
			'align' => '',
			'target' => '',
			'raised' => '',
			'alt' =>'',
			'currency' =>'',
			'sep' =>'',
			'trailing' =>'',
			'fill' =>''
		), $atts));
	$options = get_option('thermometer_options');
	
	//width
	if ($atts['width'] != ''){
	    $width=$atts['width'];
		$height = '';
	}
	else{
		$width = "200";
		$height = '';
	}
	//height
	if ($atts['height'] != ''){
	    $height=$atts['height'];
		$width = '';
	}
	elseif($atts['height'] == '' && $atts['width'] != ''){
		$width=$atts['width'];
		$height = '';
	}
	else{
		$height = "533";
		$width = '';
	}
	//currency value to use
	if ($atts['currency'] == ''){
	    $currency = $options['currency'];
	}
	elseif(strtolower($atts['currency']) == 'null'){ //get user to enter null for no value
	    $currency = '';
	}
	else{
	    $currency = $atts['currency']; //set currency to default or shortcode value
	}
	
	//target value
	if ($atts['target'] == '' && $options['target_string'] == ''){
	    echo '<p style="color:red;">Your target is missing. Set a value on the settings page or in the shortcode.</p>';
	    $target = 0;
	}
	elseif ($atts['target'] == '' && $options['target_string'] != ''){
	    $target = $options['target_string'];
	}
	else{
	    $target = $atts['target'];
	}
	
	//raised value
	if ($atts['raised'] == '' && $options['raised_string'] == ''){
	    echo '<p style="color:red;">The amount raised is missing. Set a value on the settings page or in the shortcode.</p>';
	    $raised = 0;
	}
	elseif ($atts['raised'] == '' && $options['raised_string'] != ''){
	    $raised = $options['raised_string'];
	}
	else{
	    $raised = $atts['raised'];
	}
	
	//align position
	if (strtolower($atts['align']) == 'center' || strtolower($atts['align']) == 'centre'){
	    $align = 'display:block; margin-left:auto; margin-right:auto;';
	}
	elseif (strtolower($atts['align']) == 'left'){
	    $align = 'display:block; float:left;';
	}
	elseif ($atts['align'] != ''){
	    $align = 'display:block; float:'.strtolower($atts['align']).';';
	}
	else{
		$align = 'display:block; float:left;';
	}
	
	//width value
	if($atts['width'] != '' && $atts['height'] != ''){
	    echo '<p style="color:red;">Use only width OR height parameter values.</p>';
	}
	
	//thousands separator
	if($atts['sep'] != ''){
	    $sep = $atts['sep'];
	}
	else{
		if($options['thousands'] == ' (space)'){
			$sep = ' ';
		}
		elseif($options['thousands'] == '(none)'){
			$sep = '';
		}
		else{
			$sep = substr($options['thousands'],0,1);
		}
	}
	if($atts['fill'] == ''){
	    $fill = $options['colour_picker1'];
	}
	else{
	    $fill = $atts['fill'];
	}
	
	// currency before or after number
	if(strtolower($atts['trailing']) == 'true'){
		$trailing = 'true';
	}
	elseif(strtolower($atts['trailing']) == 'false'){
		$trailing = 'false';
	}
	elseif(isset($options['trailing']) && $options['trailing'] == "1"){
		$trailing = 'true';
	}
	else{
		$trailing = 'false';
	}
	
	//title text
	if (strtolower($atts['alt']) == 'off'){
	    $title = '';
	}
	elseif($atts['alt'] != ''){
	    $title = $atts['alt'];
	    }
	else{
	    if ($trailing == 'false'){
			$title = $fill.' Raised '.$currency.number_format($raised,0,'.',$sep).' towards the '.$currency.number_format($target,0,'.',$sep).' target.';
	    }
	    else{
			$title = $fill.' Raised '.number_format($raised,0,'.',$sep).' '.$currency.' towards the '.number_format($target,0,'.',$sep).' '.$currency.' target.';
	    }  
	}	
	
	global $post;
	$postID = $post->ID; // get post/page ID
	if ($trailing == 'false'){
	    $custom_thermname = 'therm_'.$postID.'_'.$sep.ord($currency).'_'.$raised.'_'.$target.'_'.str_replace('#','',$fill); //filename is related to post
	}
	else{
	    $custom_thermname = 'therm_'.$postID.'_'.$raised.'_'.$target.'_'.str_replace('#','',$fill).'_'.$sep.ord($currency); //filename is related to post
	}
	$urlpath = plugins_url('donation-thermometer/'.$custom_thermname.'.png');
	$cache_life = '6048000'; // seconds in 1 week
	
    //clear cache if necessary first
    foreach(glob(THERM_ABSPATH.'*.png*') as $f){
		if(time() - filemtime($f) >= $cache_life){
			unlink($f);
		}
    }
    
    // create a custom thermometer from shortcode parameters
    if(file_exists(THERM_ABSPATH.$custom_thermname.'.png')){ // if thermometer exists
		return thermhtml($width,$height,$raised,$target,$align,$currency,$title,$urlpath,$custom_thermname);
	}
    else{
		createtherm($raised,$target,htmlspecialchars_decode($currency),$custom_thermname,$sep,$trailing,htmlspecialchars_decode($fill)); // use shortcode attributes to create thermometer
		return thermhtml($width,$height,$raised,$target,$align,$currency,$title,$urlpath,$custom_thermname);
	}
}

function thermhtml($code_w,$code_h,$code_r,$code_t,$align,$currency,$title,$urlpath,$custom_thermname,$content = null){ //new function to get width/height ratio from created file
    list($width,$height) = getimagesize(THERM_ABSPATH.$custom_thermname.'.png');
    $ratio = $height/$width;
    
    // use default width of 300 pixels if no parameters given
	if ($code_w == '' && $code_h == ''){
		return $therm_output = '<img src="'.$urlpath.'" title="'.$title.'" alt="'.$title.'" style="border: 0pt none; '.$align.' margin: 10px auto; width: 200px; height: '.intval($ratio*200).'px;">';
	}

	// if width/height and/or align given
	else{
	    if ($code_w != ''){
		if (substr($code_w,-1) != '%'){
		    return $therm_output = '<img src="'.$urlpath.'" title="'.$title.'" alt="'.$title.'" style="border: 0pt none;'.$align.' margin: 10px auto; width: '.intval($code_w).'px; height: '.intval($code_w*$ratio).'px;">';
		}
		elseif (substr($code_w,-1) == '%'){
		    return $therm_output = '<img src="'.$urlpath.'" title="'.$title.'" alt="'.$title.'" style="border: 0pt none;'.$align.' margin: 10px auto; width: '.$code_w.';">';
		}
	    }
	    
	    elseif ($code_h != ''){
			if (substr($code_h,-1) != '%'){
				return $therm_output = '<img src="'.$urlpath.'" title="'.$title.'" alt="'.$title.'" style="border: 0pt none;'.$align.' margin: 10px auto; width: '.intval($code_h/$ratio).'px; height: '.intval($code_h).'px;">';
			}
			elseif (substr($code_h,-1) == '%'){
				return $therm_output = '<img src="'.$urlpath.'" title="'.$title.'" alt="'.$title.'" style="border: 0pt none;'.$align.' margin: 10px auto; height: '.$code_h.';">';
			}
	    }
	}
}

add_shortcode( 'therm_r','therm_raised');

function therm_raised(){
    $options = get_option('thermometer_options');
    $raised = $options['raised_string'];
    if($options['thousands'] == ' (space)'){
		$sep = ' ';
	}
	elseif($options['thousands'] == '(none)'){
		$sep = '';
	}
	else{
		$sep = substr($options['thousands'],0,1);
	}
    if ($raised != ''){
	return number_format($raised, 0,'.',$sep);
    }
    else{
	return '<b>[Value missing on settings page]</b>';
    }
}

add_shortcode( 'therm_t','therm_target');

function therm_target(){
    $options = get_option('thermometer_options');
    $target = $options['target_string'];
    if($options['thousands'] == ' (space)'){
		$sep = ' ';
	}
	elseif($options['thousands'] == '(none)'){
		$sep = '';
	}
	else{
		$sep = substr($options['thousands'],0,1);
	}
    if ($target != ''){
	return number_format($target, 0,'.',$sep);
	}
    else{
	return '<b>[Value missing on settings page]</b>';
    }
}

/* Display a notice that can be dismissed */
/*add_action('admin_notices', 'therm_shortcode_notice');
function therm_shortcode_notice() {
    global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
    /*if ( ! get_user_meta($user_id, 'therm_ignore_notice') ) {
        echo '<div class="updated"><p>';
        printf(__('<p>Required parameters for the "Donation Thermometer" shortcode have changed with this version...please update them so that they keep working!</p>
		  The change has been made so that thermometers with different targets/raised amounts can be placed around your site. Check the plugin settings page for more advice. (<a href="%1$s">Hide Notice</a>)'), '?therm_nag_ignore=0');
        echo "</p></div>";
    }
}
add_action('admin_init', 'therm_nag_ignore');
function therm_nag_ignore() {
    global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        /*if ( isset($_GET['therm_nag_ignore']) && '0' == $_GET['therm_nag_ignore'] ) {
             add_user_meta($user_id, 'therm_ignore_notice', 'true', true);
    }
}
*/
?>