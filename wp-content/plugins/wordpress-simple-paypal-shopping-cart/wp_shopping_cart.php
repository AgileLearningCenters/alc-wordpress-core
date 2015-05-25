<?php
/*
Plugin Name: WP Simple Paypal Shopping cart
Version: v4.0.8
Plugin URI: https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768
Author: Tips and Tricks HQ, Ruhul Amin
Author URI: https://www.tipsandtricks-hq.com/
Description: Simple WordPress Shopping Cart Plugin, very easy to use and great for selling products and services from your blog!
Text Domain: WSPSC
Domain Path: /languages/
*/

if (!defined('ABSPATH'))exit; //Exit if accessed directly

if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
} else {
    if (session_id() == '') {
        session_start();
    }
}

define('WP_CART_VERSION', '4.0.8');
define('WP_CART_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_CART_PATH', plugin_dir_path(__FILE__));
define('WP_CART_URL', plugins_url('', __FILE__));
define('WP_CART_SITE_URL', site_url());
define('WP_CART_LIVE_PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr');
define('WP_CART_SANDBOX_PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
if (!defined('WP_CART_MANAGEMENT_PERMISSION')){//This will allow the user to define custom capability for this constant in wp-config file
    define('WP_CART_MANAGEMENT_PERMISSION', 'manage_options');
}
define('WP_CART_MAIN_MENU_SLUG', 'wspsc-main');


// loading language files
//Set up localisation. First loaded overrides strings present in later loaded file
$locale = apply_filters( 'plugin_locale', get_locale(), 'WSPSC' );
load_textdomain( 'WSPSC', WP_LANG_DIR . "/WSPSC-$locale.mo" );
load_plugin_textdomain('WSPSC', false, WP_CART_FOLDER . '/languages');

include_once('wp_shopping_cart_utility_functions.php');
include_once('wp_shopping_cart_shortcodes.php');
include_once('wp_shopping_cart_misc_functions.php');
include_once('wp_shopping_cart_orders.php');
include_once('class-coupon.php');
include_once('includes/wspsc-cart-functions.php');

function always_show_cart_handler($atts) {
    return print_wp_shopping_cart($atts);
}

function show_wp_shopping_cart_handler($atts) {
    $output = "";
    if (cart_not_empty()) {
        $output = print_wp_shopping_cart($atts);
    }
    return $output;
}

function shopping_cart_show($content) {
    if (strpos($content, "<!--show-wp-shopping-cart-->") !== FALSE) {
        if (cart_not_empty()) {
            $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
            $matchingText = '<!--show-wp-shopping-cart-->';
            $replacementText = print_wp_shopping_cart();
            $content = str_replace($matchingText, $replacementText, $content);
        }
    }
    return $content;
}

// Reset cart option
if (isset($_REQUEST["reset_wp_cart"]) && !empty($_REQUEST["reset_wp_cart"])) {
    reset_wp_cart();
}

//Clear the cart if the customer landed on the thank you page
if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page')) {
    //TODO - remove this field altogether later. Cart will always be reset using query prameter on the thank you page.
    if (get_option('cart_return_from_paypal_url') == cart_current_page_url()) {
        reset_wp_cart();
    }
}

function reset_wp_cart() {
    if (!isset($_SESSION['simpleCart'])){
        return;
    }
    $products = $_SESSION['simpleCart'];
    if (!is_array($products)) {
        return;
    }
    foreach ($products as $key => $item) {
        unset($products[$key]);
    }
    $_SESSION['simpleCart'] = $products;
    unset($_SESSION['simple_cart_id']);
    unset($_SESSION['wpspsc_cart_action_msg']);
    unset($_SESSION['wpspsc_discount_applied_once']);
    unset($_SESSION['wpspsc_applied_coupon_code']);
}

function wpspc_cart_actions_handler() {
    unset($_SESSION['wpspsc_cart_action_msg']);
    if (isset($_POST['addcart'])) {
        setcookie("cart_in_use", "true", time() + 21600, "/", COOKIE_DOMAIN);  //useful to not serve cached page when using with a caching plugin
        if (function_exists('wp_cache_serve_cache_file')) {//WP Super cache workaround
            setcookie("comment_author_", "wp_cart", time() + 21600, "/", COOKIE_DOMAIN);
        }

        //sanitize data
        $_POST['product'] = strip_tags($_POST['product']); //for PHP5.2 use filter_var($_POST['product'], FILTER_SANITIZE_STRING);
        $_POST['item_number'] = strip_tags($_POST['item_number']);
        if (isset($_POST['price']))
            $_POST['price'] = strip_tags($_POST['price']);
        isset($_POST['shipping']) ? $_POST['shipping'] = strip_tags($_POST['shipping']) : $_POST['shipping'] = '';
        isset($_POST['cartLink']) ? $_POST['cartLink'] = strip_tags($_POST['cartLink']) : $_POST['cartLink'] = '';
        isset($_POST['stamp_pdf']) ? $_POST['stamp_pdf'] = strip_tags($_POST['stamp_pdf']) : $_POST['stamp_pdf'] = '';

        $count = 1;
        $products = array();
        if(isset($_SESSION['simpleCart'])){
            $products = $_SESSION['simpleCart'];
            if (is_array($products)) {
                foreach ($products as $key => $item) {
                    if ($item['name'] == stripslashes($_POST['product'])) {
                        $count += $item['quantity'];
                        $item['quantity']++;
                        unset($products[$key]);
                        array_push($products, $item);
                    }
                }
            }else {
                $products = array();
            }
        }

        if ($count == 1) {
            if (!empty($_POST[$_POST['product']])) {
                $price = $_POST[$_POST['product']];
            } else {
                $price = $_POST['price'];
            }

            $default_cur_symbol = get_option('cart_currency_symbol');
            $price = str_replace($default_cur_symbol, "", $price);

            $shipping = $_POST['shipping'];
            $shipping = str_replace($default_cur_symbol, "", $shipping);

            $product = array('name' => stripslashes($_POST['product']), 'price' => $price, 'price_orig' => $price, 'quantity' => $count, 'shipping' => $shipping, 'cartLink' => $_POST['cartLink'], 'item_number' => $_POST['item_number']);
            if (isset($_POST['file_url']) && !empty($_POST['file_url'])) {
                $file_url = strip_tags($_POST['file_url']);
                $product['file_url'] = $file_url;
            }
            if (isset($_POST['thumbnail']) && !empty($_POST['thumbnail'])) {
                $thumbnail = strip_tags($_POST['thumbnail']);
                $product['thumbnail'] = $thumbnail;
            }
            if (isset($_POST['stamp_pdf']) && !empty($_POST['stamp_pdf'])) {
                $stamp_pdf = strip_tags($_POST['stamp_pdf']);
                $product['stamp_pdf'] = $stamp_pdf;
            }     
            array_push($products, $product);
        }

        sort($products);
        $_SESSION['simpleCart'] = $products;

        wpspsc_reapply_discount_coupon_if_needed(); //Re-apply coupon to the cart if necessary

        if (!isset($_SESSION['simple_cart_id']) && empty($_SESSION['simple_cart_id'])) {
            wpspc_insert_new_record();
        } else {
            //cart updating
            if (isset($_SESSION['simple_cart_id']) && !empty($_SESSION['simple_cart_id'])) {
                wpspc_update_cart_items_record();
            } else {
                echo "<p>" . (__("Error! Your session is out of sync. Please reset your session.", "WSPSC")) . "</p>";
            }
        }


        if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page')) {
            $checkout_url = get_option('cart_checkout_page_url');
            if (empty($checkout_url)) {
                echo "<br /><strong>" . (__("Shopping Cart Configuration Error! You must specify a value in the 'Checkout Page URL' field for the automatic redirection feature to work!", "WSPSC")) . "</strong><br />";
            } else {
                $redirection_parameter = 'Location: ' . $checkout_url;
                header($redirection_parameter);
                exit;
            }
        }
    } else if (isset($_POST['cquantity'])) {
        $products = $_SESSION['simpleCart'];
        foreach ($products as $key => $item) {
            if ((stripslashes($item['name']) == stripslashes($_POST['product'])) && $_POST['quantity']) {
                $item['quantity'] = $_POST['quantity'];
                unset($products[$key]);
                array_push($products, $item);
            } else if (($item['name'] == stripslashes($_POST['product'])) && !$_POST['quantity']) {
                unset($products[$key]);
            }
        }
        sort($products);
        $_SESSION['simpleCart'] = $products;

        wpspsc_reapply_discount_coupon_if_needed(); //Re-apply coupon to the cart if necessary

        if (isset($_SESSION['simple_cart_id']) && !empty($_SESSION['simple_cart_id'])) {
            wpspc_update_cart_items_record();
        }
    } else if (isset($_POST['delcart'])) {
        $products = $_SESSION['simpleCart'];
        foreach ($products as $key => $item) {
            if ($item['name'] == stripslashes($_POST['product']))
                unset($products[$key]);
        }
        $_SESSION['simpleCart'] = $products;

        wpspsc_reapply_discount_coupon_if_needed(); //Re-apply coupon to the cart if necessary

        if (isset($_SESSION['simple_cart_id']) && !empty($_SESSION['simple_cart_id'])) {
            wpspc_update_cart_items_record();
        }
        if (count($_SESSION['simpleCart']) < 1) {
            reset_wp_cart();
        }
    } else if (isset($_POST['wpspsc_coupon_code'])) {
        $coupon_code = strip_tags($_POST['wpspsc_coupon_code']);
        wpspsc_apply_cart_discount($coupon_code);
    }
}

function wp_cart_add_custom_field() {
    $_SESSION['wp_cart_custom_values'] = "";
    $custom_field_val = "";
    $name = 'wp_cart_id';
    $value = $_SESSION['simple_cart_id'];
    $custom_field_val = wpc_append_values_to_custom_field($name, $value);

    $clientip = $_SERVER['REMOTE_ADDR'];
    if (!empty($clientip)) {
        $name = 'ip';
        $value = $clientip;
        $custom_field_val = wpc_append_values_to_custom_field($name, $value);
    }

    if (function_exists('wp_aff_platform_install')) {
        $name = 'ap_id';
        $value = '';
        if (isset($_SESSION['ap_id'])) {
            $value = $_SESSION['ap_id'];
        } else if (isset($_COOKIE['ap_id'])) {
            $value = $_COOKIE['ap_id'];
        }
        if (!empty($value)) {
            $custom_field_val = wpc_append_values_to_custom_field($name, $value);
        }
    }

    if (isset($_SESSION['wpspsc_applied_coupon_code'])) {
        $name = "coupon_code";
        $value = $_SESSION['wpspsc_applied_coupon_code'];
        $custom_field_val = wpc_append_values_to_custom_field($name, $value);
    }

    $custom_field_val = apply_filters('wpspc_cart_custom_field_value', $custom_field_val);
    $output = '<input type="hidden" name="custom" value="' . $custom_field_val . '" />';
    return $output;
}

function print_wp_cart_button_new($content) {
    $addcart = get_option('addToCartButtonName');
    if (!$addcart || ($addcart == ''))
        $addcart = __("Add to Cart", "WSPSC");

    $pattern = '#\[wp_cart:.+:price:.+:end]#';
    preg_match_all($pattern, $content, $matches);

    foreach ($matches[0] as $match) {
        $var_output = '';
        $pos = strpos($match, ":var1");
        if ($pos) {
            $match_tmp = $match;
            // Variation control is used
            $pos2 = strpos($match, ":var2");
            if ($pos2) {
                //echo '<br />'.$match_tmp.'<br />';
                $pattern = '#var2\[.*]:#';
                preg_match_all($pattern, $match_tmp, $matches3);
                $match3 = $matches3[0][0];
                //echo '<br />'.$match3.'<br />';
                $match_tmp = str_replace($match3, '', $match_tmp);

                $pattern = 'var2[';
                $m3 = str_replace($pattern, '', $match3);
                $pattern = ']:';
                $m3 = str_replace($pattern, '', $m3);
                $pieces3 = explode('|', $m3);

                $variation2_name = $pieces3[0];
                $var_output .= $variation2_name . " : ";
                $var_output .= '<select name="variation2" onchange="ReadForm (this.form, false);">';
                for ($i = 1; $i < sizeof($pieces3); $i++) {
                    $var_output .= '<option value="' . $pieces3[$i] . '">' . $pieces3[$i] . '</option>';
                }
                $var_output .= '</select><br />';
            }

            $pattern = '#var1\[.*]:#';
            preg_match_all($pattern, $match_tmp, $matches2);
            $match2 = $matches2[0][0];

            $match_tmp = str_replace($match2, '', $match_tmp);

            $pattern = 'var1[';
            $m2 = str_replace($pattern, '', $match2);
            $pattern = ']:';
            $m2 = str_replace($pattern, '', $m2);
            $pieces2 = explode('|', $m2);

            $variation_name = $pieces2[0];
            $var_output .= $variation_name . " : ";
            $var_output .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
            for ($i = 1; $i < sizeof($pieces2); $i++) {
                $var_output .= '<option value="' . $pieces2[$i] . '">' . $pieces2[$i] . '</option>';
            }
            $var_output .= '</select><br />';
        }

        $pattern = '[wp_cart:';
        $m = str_replace($pattern, '', $match);

        $pattern = 'price:';
        $m = str_replace($pattern, '', $m);
        $pattern = 'shipping:';
        $m = str_replace($pattern, '', $m);
        $pattern = ':end]';
        $m = str_replace($pattern, '', $m);

        $pieces = explode(':', $m);

        $replacement = '<div class="wp_cart_button_wrapper">';
        $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);" ' . apply_filters("wspsc_add_cart_button_form_attr", "") . '>';
        if (!empty($var_output)) {
            $replacement .= $var_output;
        }

        if (preg_match("/http/", $addcart)) { // Use the image as the 'add to cart' button
            $replacement .= '<input type="image" src="' . $addcart . '" class="wp_cart_button" alt="' . (__("Add to Cart", "WSPSC")) . '"/>';
        } else {
            $replacement .= '<input type="submit" value="' . $addcart . '" />';
        }

        $replacement .= '<input type="hidden" name="product" value="' . $pieces['0'] . '" /><input type="hidden" name="price" value="' . $pieces['1'] . '" />';
        $replacement .= '<input type="hidden" name="product_tmp" value="' . $pieces['0'] . '" />';
        if (sizeof($pieces) > 2) {
            //we have shipping
            $replacement .= '<input type="hidden" name="shipping" value="' . $pieces['2'] . '" />';
        }
        $replacement .= '<input type="hidden" name="cartLink" value="' . cart_current_page_url() . '" />';
        $replacement .= '<input type="hidden" name="addcart" value="1" /></form>';
        $replacement .= '</div>';
        $content = str_replace($match, $replacement, $content);
    }
    return $content;
}

function wp_cart_add_read_form_javascript() {
    $debug_marker = "<!-- WP Simple Shopping Cart plugin v" . WP_CART_VERSION . " - https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768/ -->";
    echo "\n${debug_marker}\n";    
    echo '
	<script type="text/javascript">
	<!--
	//
	function ReadForm (obj1, tst) 
	{ 
	    // Read the user form
	    var i,j,pos;
	    val_total="";val_combo="";		
	
	    for (i=0; i<obj1.length; i++) 
	    {     
	        // run entire form
	        obj = obj1.elements[i];           // a form element
	
	        if (obj.type == "select-one") 
	        {   // just selects
	            if (obj.name == "quantity" ||
	                obj.name == "amount") continue;
		        pos = obj.selectedIndex;        // which option selected
		        val = obj.options[pos].value;   // selected value
		        val_combo = val_combo + " (" + val + ")";
	        }
	    }
		// Now summarize everything we have processed above
		val_total = obj1.product_tmp.value + val_combo;
		obj1.product.value = val_total;
	}
	//-->
	</script>';
}

function print_wp_cart_button_for_product($name, $price, $shipping = 0, $var1 = '', $var2 = '', $var3 = '', $atts = array()) {
    $addcart = get_option('addToCartButtonName');
    if (!$addcart || ($addcart == ''))
        $addcart = __("Add to Cart", "WSPSC");

    $var_output = "";
    if (!empty($var1)) {
        $var1_pieces = explode('|', $var1);
        $variation1_name = $var1_pieces[0];
        $var_output .= '<span class="wp_cart_variation_name">' . $variation1_name . ' : </span>';
        $var_output .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
        for ($i = 1; $i < sizeof($var1_pieces); $i++) {
            $var_output .= '<option value="' . $var1_pieces[$i] . '">' . $var1_pieces[$i] . '</option>';
        }
        $var_output .= '</select><br />';
    }
    if (!empty($var2)) {
        $var2_pieces = explode('|', $var2);
        $variation2_name = $var2_pieces[0];
        $var_output .= '<span class="wp_cart_variation_name">' . $variation2_name . ' : </span>';
        $var_output .= '<select name="variation2" onchange="ReadForm (this.form, false);">';
        for ($i = 1; $i < sizeof($var2_pieces); $i++) {
            $var_output .= '<option value="' . $var2_pieces[$i] . '">' . $var2_pieces[$i] . '</option>';
        }
        $var_output .= '</select><br />';
    }
    if (!empty($var3)) {
        $var3_pieces = explode('|', $var3);
        $variation3_name = $var3_pieces[0];
        $var_output .= '<span class="wp_cart_variation_name">' . $variation3_name . ' : </span>';
        $var_output .= '<select name="variation3" onchange="ReadForm (this.form, false);">';
        for ($i = 1; $i < sizeof($var3_pieces); $i++) {
            $var_output .= '<option value="' . $var3_pieces[$i] . '">' . $var3_pieces[$i] . '</option>';
        }
        $var_output .= '</select><br />';
    }

    $replacement = '<div class="wp_cart_button_wrapper">';
    $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);" ' . apply_filters("wspsc_add_cart_button_form_attr", "") . '>';
    if (!empty($var_output)) {//Show variation
        $replacement .= '<div class="wp_cart_variation_section">' . $var_output . '</div>';
    }

    if (isset($atts['button_image']) && !empty($atts['button_image'])) {
        //Use the custom button image for this shortcode
        $replacement .= '<input type="image" src="' . $atts['button_image'] . '" class="wp_cart_button" alt="' . (__("Add to Cart", "WSPSC")) . '"/>';
    } else {
        //Use the button text or image value from the settings
        if (preg_match("/http:/", $addcart) || preg_match("/https:/", $addcart)) { // Use the image as the 'add to cart' button
            $replacement .= '<input type="image" src="' . $addcart . '" class="wp_cart_button" alt="' . (__("Add to Cart", "WSPSC")) . '"/>';
        } else {
            $replacement .= '<input type="submit" value="' . apply_filters('wspsc_add_cart_submit_button_value', $addcart, $price) . '" />';
        }
    }

    $replacement .= '<input type="hidden" name="product" value="' . $name . '" /><input type="hidden" name="price" value="' . $price . '" /><input type="hidden" name="shipping" value="' . $shipping . '" /><input type="hidden" name="addcart" value="1" /><input type="hidden" name="cartLink" value="' . cart_current_page_url() . '" />';
    $replacement .= '<input type="hidden" name="product_tmp" value="' . $name . '" />';
    isset($atts['item_number']) ? $item_num = $atts['item_number'] : $item_num = '';
    $replacement .= '<input type="hidden" name="item_number" value="' . $item_num . '" />';

    if (isset($atts['file_url'])) {
        $file_url = $atts['file_url'];
        $file_url = base64_encode($file_url);
        $replacement .= '<input type="hidden" name="file_url" value="' . $file_url . '" />';
    }
    if (isset($atts['thumbnail'])) {
        $replacement .= '<input type="hidden" name="thumbnail" value="' . $atts['thumbnail'] . '" />';
    }
    if (isset($atts['stamp_pdf'])) {
        $replacement .= '<input type="hidden" name="stamp_pdf" value="' . $atts['stamp_pdf'] . '" />';
    }
    $replacement .= '</form>';
    $replacement .= '</div>';
    return $replacement;
}

function cart_not_empty() {
    $count = 0;
    if (isset($_SESSION['simpleCart']) && is_array($_SESSION['simpleCart'])) {
        foreach ($_SESSION['simpleCart'] as $item)
            $count++;
        return $count;
    }
    else
        return 0;
}

function print_payment_currency($price, $symbol, $decimal = '.') {
    $formatted_price = '';
    $formatted_price = apply_filters('wspsc_print_formatted_price', $formatted_price, $price, $symbol);
    if(!empty($formatted_price)){
        return $formatted_price;
    }
    $formatted_price = $symbol . number_format($price, 2, $decimal, ',');
    return $formatted_price;
}

function cart_current_page_url() {
    $pageURL = 'http';
    if (!isset($_SERVER["HTTPS"])) {
        $_SERVER["HTTPS"] = "";
    }
    if (!isset($_SERVER["SERVER_PORT"])) {
        $_SERVER["SERVER_PORT"] = "";
    }

    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function simple_cart_total() {
    $grand_total = 0;
    foreach ((array) $_SESSION['simpleCart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $item_total_shipping += $item['shipping'] * $item['quantity'];
    }
    $grand_total = $total + $item_total_shipping;
    return wpspsc_number_format_price($grand_total);
}

// Handle the options page display
function wp_cart_options_page() {
    include_once('wp_shopping_cart_settings.php');
    add_options_page(__("WP Paypal Shopping Cart", "WSPSC"), __("WP Shopping Cart", "WSPSC"), WP_CART_MANAGEMENT_PERMISSION, 'wordpress-paypal-shopping-cart', 'wp_cart_options');
    
    //Main menu - Complete this when the dashboard menu is ready
    //$menu_icon_url = '';//TODO - use 
    //add_menu_page(__('Simple Cart', 'WSPSC'), __('Simple Cart', 'WSPSC'), WP_CART_MANAGEMENT_PERMISSION, WP_CART_MAIN_MENU_SLUG , 'wp_cart_options', $menu_icon_url);
    //add_submenu_page(WP_CART_MAIN_MENU_SLUG, __('Settings', 'WSPSC'),  __('Settings', 'WSPSC') , WP_CART_MANAGEMENT_PERMISSION, WP_CART_MAIN_MENU_SLUG, 'wp_cart_options');
    //add_submenu_page(WP_CART_MAIN_MENU_SLUG, __('Bla', 'WSPSC'),  __('Bla', 'WSPSC') , WP_CART_MANAGEMENT_PERMISSION, 'wspsc-bla', 'wp_cart_options');
            
}

function wp_paypal_shopping_cart_load_widgets() {
    register_widget('WP_PayPal_Cart_Widget');
}

class WP_PayPal_Cart_Widget extends WP_Widget {

    function WP_PayPal_Cart_Widget() {
        parent::WP_Widget('wp_paypal_shopping_cart_widgets', 'WP Paypal Shopping Cart', array('description' => 'WP Paypal Shopping Cart Widget'));
    }

    function form($instance) {
        // outputs the options form on admin
    }

    function update($new_instance, $old_instance) {
        // processes widget options to be saved
    }

    function widget($args, $instance) {
        // outputs the content of the widget
        extract($args);

        $cart_title = get_option('wp_cart_title');
        if (empty($cart_title))
            $cart_title = __("Shopping Cart", "WSPSC");

        echo $before_widget;
        echo $before_title . $cart_title . $after_title;
        echo print_wp_shopping_cart();
        echo $after_widget;
    }

}

function wspsc_admin_side_enqueue_scripts()
{
    if (isset($_GET['page']) && $_GET['page'] == 'wordpress-paypal-shopping-cart') //simple paypal shopping cart discount page
    {
        wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css');
        wp_register_script('wpspsc-admin', WP_CART_URL.'/lib/wpspsc_admin_side.js', array('jquery', 'jquery-ui-datepicker'));
        wp_enqueue_script('wpspsc-admin');
    }
}

function wspsc_front_side_enqueue_scripts() {
    wp_enqueue_style('wspsc-style', WP_CART_URL . '/wp_shopping_cart_style.css', array(), WP_CART_VERSION);
}

function wpspc_plugin_install() {
    wpspc_run_activation();
}

register_activation_hook(__FILE__, 'wpspc_plugin_install');

// Add the settings link
function wp_simple_cart_add_settings_link($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $settings_link = '<a href="options-general.php?page=wordpress-paypal-shopping-cart">' . (__("Settings", "WSPSC")) . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

add_filter('plugin_action_links', 'wp_simple_cart_add_settings_link', 10, 2);

// Insert the options page to the admin menu
add_action('admin_menu', 'wp_cart_options_page');
add_action('widgets_init', 'wp_paypal_shopping_cart_load_widgets');

add_action('init', 'wp_cart_init_handler');
add_action('admin_init', 'wp_cart_admin_init_handler');

//add_filter('the_content', 'print_wp_cart_button',11);
add_filter('the_content', 'print_wp_cart_button_new', 11);
add_filter('the_content', 'shopping_cart_show');

if (!is_admin()) {
    add_filter('widget_text', 'do_shortcode');
}

add_shortcode('show_wp_shopping_cart', 'show_wp_shopping_cart_handler');
add_shortcode('always_show_wp_shopping_cart', 'always_show_cart_handler');
add_shortcode('wp_cart_button', 'wp_cart_button_handler');
add_shortcode('wp_cart_display_product', 'wp_cart_display_product_handler');
add_shortcode('wp_compact_cart', 'wspsc_compact_cart_handler');

add_action('wp_head', 'wp_cart_add_read_form_javascript');
add_action('wp_enqueue_scripts', 'wspsc_front_side_enqueue_scripts');
add_action('admin_enqueue_scripts', 'wspsc_admin_side_enqueue_scripts' );
