<?php

function wp_cart_options()
{    
    $wpspc_plugin_tabs = array(
        'wordpress-paypal-shopping-cart' => 'General Settings',
        'wordpress-paypal-shopping-cart&action=email-settings' => 'Email Settings',
        'wordpress-paypal-shopping-cart&action=discount-settings' => 'Coupon/Discount'
    );
    echo '<div class="wrap">'.screen_icon( ).'<h2>'.(__("WP Paypal Shopping Cart Options", "WSPSC")).'</h2>';
    $current = "";
    if(isset($_GET['page'])){
        $current = $_GET['page'];
        if(isset($_GET['action'])){
            $current .= "&action=".$_GET['action'];
        }
    }
    $content = '';
    $content .= '<h2 class="nav-tab-wrapper">';
    foreach($wpspc_plugin_tabs as $location => $tabname)
    {
        if($current == $location){
            $class = ' nav-tab-active';
        } else{
            $class = '';    
        }
        $content .= '<a class="nav-tab'.$class.'" href="?page='.$location.'">'.$tabname.'</a>';
    }
    $content .= '</h2>';
    echo $content;     
    echo '<div id="poststuff"><div id="post-body">';
   if(isset($_GET['action']))
   {    
        switch ($_GET['action'])
        {
            case 'email-settings':
                show_wp_cart_email_settings_page();
                break;
            case 'discount-settings':
                include_once ('wp_shopping_cart_discounts_menu.php');
                show_wp_cart_coupon_discount_settings_page();
                break;
        }
   }
   else
   {
       show_wp_cart_options_page();
   }
    echo '</div></div>';
    echo '</div>';
}

function show_wp_cart_options_page () 
{
    if(isset($_POST['wspsc_reset_logfile'])) {
        // Reset the debug log file
        if(wspsc_reset_logfile()){
            echo '<div id="message" class="updated fade"><p><strong>Debug log file has been reset!</strong></p></div>';
        }
        else{
                echo '<div id="message" class="updated fade"><p><strong>Debug log file could not be reset!</strong></p></div>';
        }
    }
    if (isset($_POST['info_update']))
    {
    	$nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce, 'wp_simple_cart_settings_update')){
			wp_die('Error! Nonce Security Check Failed! Go back to settings menu and save the settings again.');
		}

        update_option('cart_payment_currency', (string)$_POST["cart_payment_currency"]);
        update_option('cart_currency_symbol', (string)$_POST["cart_currency_symbol"]);
        update_option('cart_base_shipping_cost', (string)$_POST["cart_base_shipping_cost"]);
        update_option('cart_free_shipping_threshold', (string)$_POST["cart_free_shipping_threshold"]);   
        update_option('wp_shopping_cart_collect_address', (isset($_POST['wp_shopping_cart_collect_address']) && $_POST['wp_shopping_cart_collect_address']!='') ? 'checked="checked"':'' );    
        update_option('wp_shopping_cart_use_profile_shipping', (isset($_POST['wp_shopping_cart_use_profile_shipping']) && $_POST['wp_shopping_cart_use_profile_shipping']!='') ? 'checked="checked"':'' );
                
        update_option('cart_paypal_email', (string)$_POST["cart_paypal_email"]);
        update_option('addToCartButtonName', (string)$_POST["addToCartButtonName"]);
        update_option('wp_cart_title', (string)$_POST["wp_cart_title"]);
        update_option('wp_cart_empty_text', (string)$_POST["wp_cart_empty_text"]);
        update_option('cart_return_from_paypal_url', (string)$_POST["cart_return_from_paypal_url"]);
        update_option('cart_products_page_url', (string)$_POST["cart_products_page_url"]);
                
        update_option('wp_shopping_cart_auto_redirect_to_checkout_page', (isset($_POST['wp_shopping_cart_auto_redirect_to_checkout_page']) && $_POST['wp_shopping_cart_auto_redirect_to_checkout_page']!='') ? 'checked="checked"':'' );
        update_option('cart_checkout_page_url', (string)$_POST["cart_checkout_page_url"]);
        update_option('wspsc_open_pp_checkout_in_new_tab', (isset($_POST['wspsc_open_pp_checkout_in_new_tab']) && $_POST['wspsc_open_pp_checkout_in_new_tab']!='') ? 'checked="checked"':'' );
        update_option('wp_shopping_cart_reset_after_redirection_to_return_page', (isset($_POST['wp_shopping_cart_reset_after_redirection_to_return_page']) && $_POST['wp_shopping_cart_reset_after_redirection_to_return_page']!='') ? 'checked="checked"':'' );        
                
        update_option('wp_shopping_cart_image_hide', (isset($_POST['wp_shopping_cart_image_hide']) && $_POST['wp_shopping_cart_image_hide']!='') ? 'checked="checked"':'' );
        update_option('wp_cart_note_to_seller_text', (string)$_POST["wp_cart_note_to_seller_text"]);
        update_option('wp_cart_paypal_co_page_style', (string)$_POST["wp_cart_paypal_co_page_style"]);
        update_option('wp_shopping_cart_strict_email_check', (isset($_POST['wp_shopping_cart_strict_email_check']) && $_POST['wp_shopping_cart_strict_email_check']!='') ? 'checked="checked"':'' );
        update_option('wp_use_aff_platform', (isset($_POST['wp_use_aff_platform']) && $_POST['wp_use_aff_platform']!='') ? 'checked="checked"':'' );
        
        update_option('wp_shopping_cart_enable_sandbox', (isset($_POST['wp_shopping_cart_enable_sandbox']) && $_POST['wp_shopping_cart_enable_sandbox']!='') ? 'checked="checked"':'' );
        update_option('wp_shopping_cart_enable_debug', (isset($_POST['wp_shopping_cart_enable_debug']) && $_POST['wp_shopping_cart_enable_debug']!='') ? 'checked="checked"':'' );
        
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.(__("Options Updated!", "WSPSC")).'</strong></p></div>';
    }	
	
    $defaultCurrency = get_option('cart_payment_currency');    
    if (empty($defaultCurrency)) $defaultCurrency = __("USD", "WSPSC");
    
    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = __("$", "WSPSC");

    $baseShipping = get_option('cart_base_shipping_cost');
    if (empty($baseShipping)) $baseShipping = 0;
    
    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');

    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
    $return_url =  get_option('cart_return_from_paypal_url');

    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = __("Add to Cart", "WSPSC");           

	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = __("Your Shopping Cart", "WSPSC");
	
	$emptyCartText = get_option('wp_cart_empty_text');
	$cart_products_page_url = get_option('cart_products_page_url');	  

	$cart_checkout_page_url = get_option('cart_checkout_page_url');
    if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page'))
        $wp_shopping_cart_auto_redirect_to_checkout_page = 'checked="checked"';
    else
        $wp_shopping_cart_auto_redirect_to_checkout_page = '';	
        
    if (get_option('wspsc_open_pp_checkout_in_new_tab'))
        $wspsc_open_pp_checkout_in_new_tab = 'checked="checked"';
    else
        $wspsc_open_pp_checkout_in_new_tab = '';
    
    if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page'))
        $wp_shopping_cart_reset_after_redirection_to_return_page = 'checked="checked"';
    else
        $wp_shopping_cart_reset_after_redirection_to_return_page = '';	
                	    
    if (get_option('wp_shopping_cart_collect_address'))
        $wp_shopping_cart_collect_address = 'checked="checked"';
    else
        $wp_shopping_cart_collect_address = '';
        
    if (get_option('wp_shopping_cart_use_profile_shipping'))
        $wp_shopping_cart_use_profile_shipping = 'checked="checked"';
    else
        $wp_shopping_cart_use_profile_shipping = '';
                	
    if (get_option('wp_shopping_cart_image_hide'))
        $wp_cart_image_hide = 'checked="checked"';
    else
        $wp_cart_image_hide = '';

	$wp_cart_note_to_seller_text = get_option('wp_cart_note_to_seller_text');
        $wp_cart_paypal_co_page_style = get_option('wp_cart_paypal_co_page_style');

    $wp_shopping_cart_strict_email_check = '';
    if (get_option('wp_shopping_cart_strict_email_check')){
        $wp_shopping_cart_strict_email_check = 'checked="checked"';
    }
        
    if (get_option('wp_use_aff_platform'))
        $wp_use_aff_platform = 'checked="checked"';
    else
        $wp_use_aff_platform = '';
                              
	//$wp_shopping_cart_enable_sandbox = get_option('wp_shopping_cart_enable_sandbox');
    if (get_option('wp_shopping_cart_enable_sandbox'))
        $wp_shopping_cart_enable_sandbox = 'checked="checked"';
    else
        $wp_shopping_cart_enable_sandbox = '';	
    
    $wp_shopping_cart_enable_debug = '';
    if (get_option('wp_shopping_cart_enable_debug')){
        $wp_shopping_cart_enable_debug = 'checked="checked"';
    }    
	?>
 	<h2><?php _e("Simple PayPal Shopping Cart Settings", "WSPSC"); ?> v <?php echo WP_CART_VERSION; ?></h2>
 	
 	<div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
 	<p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "WSPSC"); ?><br />
    <a href="https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768" target="_blank"><?php _e("WP Simple Cart Homepage", "WSPSC"); ?></a></p>
    </div>
    
    <div class="postbox">
    <h3><label for="title"><?php _e("Quick Usage Guide", "WSPSC"); ?></label></h3>
    <div class="inside">
	
        <p><strong><?php _e("Step 1) ","WSPSC"); ?></strong><?php _e("To add an 'Add to Cart' button for a product simply add the shortcode", "WSPSC"); ?> [wp_cart_button name="<?php _e("PRODUCT-NAME", "WSPSC"); ?>" price="<?php _e("PRODUCT-PRICE", "WSPSC"); ?>"] <?php _e("to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price of your product.", "WSPSC"); ?></p>
        <p><?php _e("Example add to cart button shortcode usage:", "WSPSC");?> <p style="background-color: #DDDDDD; padding: 5px; display: inline;">[wp_cart_button name="Test Product" price="29.95"]</p></p>
	<p><strong><?php _e("Step 2) ","WSPSC"); ?></strong><?php _e("To add the shopping cart to a post or page (example: a checkout page) simply add the shortcode", "WSPSC"); ?> [show_wp_shopping_cart] <?php _e("to a post or page or use the sidebar widget to add the shopping cart to the sidebar.", "WSPSC"); ?></p>
        <p><?php _e("Example shopping cart shortcode usage:", "WSPSC");?> <p style="background-color: #DDDDDD; padding: 5px; display: inline;">[show_wp_shopping_cart]</p></p>
    </div></div>

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <?php wp_nonce_field('wp_simple_cart_settings_update'); ?>
    <input type="hidden" name="info_update" id="info_update" value="true" />    
<?php
echo '
	<div class="postbox">
	<h3><label for="title">'.(__("PayPal and Shopping Cart Settings", "WSPSC")).'</label></h3>
	<div class="inside">';

echo '
<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Paypal Email Address", "WSPSC")).'</th>
<td><input type="text" name="cart_paypal_email" value="'.$defaultEmail.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Shopping Cart title", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_title" value="'.$title.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Text/Image to Show When Cart Empty", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_empty_text" value="'.$emptyCartText.'" size="60" /><br />'.(__("You can either enter plain text or the URL of an image that you want to show when the shopping cart is empty", "WSPSC")).'</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency", "WSPSC")).'</th>
<td><input type="text" name="cart_payment_currency" value="'.$defaultCurrency.'" size="6" /> ('.(__("e.g.", "WSPSC")).' USD, EUR, GBP, AUD)</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency Symbol", "WSPSC")).'</th>
<td><input type="text" name="cart_currency_symbol" value="'.$defaultSymbol.'" size="2" style="width: 1.5em;" /> ('.(__("e.g.", "WSPSC")).' $, &#163;, &#8364;) 
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Base Shipping Cost", "WSPSC")).'</th>
<td><input type="text" name="cart_base_shipping_cost" value="'.$baseShipping.'" size="5" /> <br />'.(__("This is the base shipping cost that will be added to the total of individual products shipping cost. Put 0 if you do not want to charge shipping cost or use base shipping cost.", "WSPSC")).' <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=297" target="_blank">'.(__("Learn More on Shipping Calculation", "WSPSC")).'</a></td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Free Shipping for Orders Over", "WSPSC")).'</th>
<td><input type="text" name="cart_free_shipping_threshold" value="'.$cart_free_shipping_threshold.'" size="5" /> <br />'.(__("When a customer orders more than this amount he/she will get free shipping. Leave empty if you do not want to use it.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Must Collect Shipping Address on PayPal", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_collect_address" value="1" '.$wp_shopping_cart_collect_address.' /><br />'.(__("If checked the customer will be forced to enter a shipping address on PayPal when checking out.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Use PayPal Profile Based Shipping", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_use_profile_shipping" value="1" '.$wp_shopping_cart_use_profile_shipping.' /><br />'.(__("Check this if you want to use", "WSPSC")).' <a href="https://www.tipsandtricks-hq.com/setup-paypal-profile-based-shipping-5865" target="_blank">'.(__("PayPal profile based shipping", "WSPSC")).'</a>. '.(__("Using this will ignore any other shipping options that you have specified in this plugin.", "WSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Add to Cart button text or Image", "WSPSC")).'</th>
<td><input type="text" name="addToCartButtonName" value="'.$addcart.'" size="100" />
<br />'.(__("To use a customized image as the button simply enter the URL of the image file.", "WSPSC")).' '.(__("e.g.", "WSPSC")).' http://www.your-domain.com/wp-content/plugins/wordpress-paypal-shopping-cart/images/buy_now_button.png
<br />You can download nice add to cart button images from <a href="http://www.tipsandtricks-hq.com/ecommerce/add-to-cart-button-images-for-shopping-cart-631" target="_blank">this page</a>.
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Return URL", "WSPSC")).'</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.$return_url.'" size="100" /><br />'.(__("This is the URL the customer will be redirected to after a successful payment", "WSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Products Page URL", "WSPSC")).'</th>
<td><input type="text" name="cart_products_page_url" value="'.$cart_products_page_url.'" size="100" /><br />'.(__("This is the URL of your products page if you have any. If used, the shopping cart widget will display a link to this page when cart is empty", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Automatic redirection to checkout page", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_auto_redirect_to_checkout_page" value="1" '.$wp_shopping_cart_auto_redirect_to_checkout_page.' />
 '.(__("Checkout Page URL", "WSPSC")).': <input type="text" name="cart_checkout_page_url" value="'.$cart_checkout_page_url.'" size="60" />
<br />'.(__("If checked the visitor will be redirected to the Checkout page after a product is added to the cart. You must enter a URL in the Checkout Page URL field for this to work.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Open PayPal Checkout Page in a New Tab", "WSPSC")).'</th>
<td><input type="checkbox" name="wspsc_open_pp_checkout_in_new_tab" value="1" '.$wspsc_open_pp_checkout_in_new_tab.' />
<br />'.(__("If checked the PayPal checkout page will be opened in a new tab/window when the user clicks the checkout button.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Reset Cart After Redirection to Return Page", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_reset_after_redirection_to_return_page" value="1" '.$wp_shopping_cart_reset_after_redirection_to_return_page.' />
<br />'.(__("If checked the shopping cart will be reset when the customer lands on the return URL (Thank You) page.", "WSPSC")).'</td>
</tr>
</table>


<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Hide Shopping Cart Image", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_image_hide" value="1" '.$wp_cart_image_hide.' /><br />'.(__("If ticked the shopping cart image will not be shown.", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Customize the Note to Seller Text", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_note_to_seller_text" value="'.$wp_cart_note_to_seller_text.'" size="100" />
<br />'.(__("Specify the text that you want to use for the note field on PayPal checkout page to collect special instruction (leave this field empty if you don't need to customize it). The default label for the note field is \"Add special instructions to merchant\".", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Custom Checkout Page Style Name", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_paypal_co_page_style" value="'.$wp_cart_paypal_co_page_style.'" size="40" />
<br />'.(__("Specify the page style name here if you want to customize the paypal checkout page with custom page style otherwise leave this field empty.", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use Strict PayPal Email Address Checking", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_strict_email_check" value="1" '.$wp_shopping_cart_strict_email_check.' /><br />'.(__("If checked the script will check to make sure that the PayPal email address specified is the same as the account where the payment was deposited (Usage of PayPal Email Alias will fail too).", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use WP Affiliate Platform", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_use_aff_platform" value="1" '.$wp_use_aff_platform.' />
<br />'.(__("Check this if using with the", "WSPSC")).' <a href="https://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform plugin</a>. '.(__("This plugin lets you run your own affiliate campaign/program and allows you to reward (pay commission) your affiliates for referred sales", "WSPSC")).'</td>
</tr>
</table>
</div></div>

<div class="postbox">
    <h3><label for="title">'.(__("Testing and Debugging Settings", "WSPSC")).'</label></h3>
    <div class="inside">
    
    <table class="form-table"> 
    
    <tr valign="top">
    <th scope="row">'.(__("Enable Debug", "WSPSC")).'</th>
    <td><input type="checkbox" name="wp_shopping_cart_enable_debug" value="1" '.$wp_shopping_cart_enable_debug.' />
    <br />'.(__("If checked, debug output will be written to the log file. This is useful for troubleshooting post payment failures", "WSPSC")).'
        <p><i>You can check the debug log file by clicking on the link below (The log file can be viewed using any text editor):</i>
        <ul>
            <li><a href="'.WP_CART_URL.'/ipn_handle_debug.log" target="_blank">ipn_handle_debug.log</a></li>
        </ul>
        </p>
        <input type="submit" name="wspsc_reset_logfile" class="button" style="font-weight:bold; color:red" value="Reset Debug Log file"/> 
        <p class="description">It will reset the debug log file and timestamp it with a log file reset message.</a>
    </td></tr>

    <tr valign="top">
    <th scope="row">'.(__("Enable Sandbox Testing", "WSPSC")).'</th>
    <td><input type="checkbox" name="wp_shopping_cart_enable_sandbox" value="1" '.$wp_shopping_cart_enable_sandbox.' />
    <br />'.(__("Check this option if you want to do PayPal sandbox testing. You will need to create a PayPal sandbox account from PayPal Developer site", "WSPSC")).'</td>
    </tr>
    
    </table>
    
    </div>
</div>

    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="'.(__("Update Options &raquo;", "WSPSC")).'" />
    </div>						
 </form>
 ';
    echo (__("Like the Simple WordPress Shopping Cart Plugin?", "WSPSC")).' <a href="http://wordpress.org/extend/plugins/wordpress-simple-paypal-shopping-cart" target="_blank">'.(__("Give it a good rating", "WSPSC")).'</a>';
    wpspsc_settings_menu_footer();
}

function show_wp_cart_email_settings_page()
{
    if (isset($_POST['wpspc_email_settings_update']))
    {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce($nonce, 'wpspc_email_settings_update')){
                wp_die('Error! Nonce Security Check Failed! Go back to email settings menu and save the settings again.');
        }
        update_option('wpspc_send_buyer_email', (isset($_POST['wpspc_send_buyer_email']) && $_POST['wpspc_send_buyer_email']!='') ? 'checked="checked"':'' );        
        update_option('wpspc_buyer_from_email', stripslashes((string)$_POST["wpspc_buyer_from_email"]));
        update_option('wpspc_buyer_email_subj', stripslashes((string)$_POST["wpspc_buyer_email_subj"]));
        update_option('wpspc_buyer_email_body', stripslashes((string)$_POST["wpspc_buyer_email_body"]));;
        
        update_option('wpspc_send_seller_email', (isset($_POST['wpspc_send_seller_email']) && $_POST['wpspc_send_seller_email']!='') ? 'checked="checked"':'' );        
        update_option('wpspc_notify_email_address', stripslashes((string)$_POST["wpspc_notify_email_address"]));
        update_option('wpspc_seller_email_subj', stripslashes((string)$_POST["wpspc_seller_email_subj"]));
        update_option('wpspc_seller_email_body', stripslashes((string)$_POST["wpspc_seller_email_body"]));;
        
        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Email Settings Updated!';
        echo '</strong></p></div>';
    }
    $wpspc_send_buyer_email = '';
    if (get_option('wpspc_send_buyer_email')){
        $wpspc_send_buyer_email = 'checked="checked"';
    }
    $wpspc_buyer_from_email = get_option('wpspc_buyer_from_email');    
    $wpspc_buyer_email_subj = get_option('wpspc_buyer_email_subj');    
    $wpspc_buyer_email_body = get_option('wpspc_buyer_email_body');
    $wpspc_send_seller_email = '';
    if (get_option('wpspc_send_seller_email')){
        $wpspc_send_seller_email = 'checked="checked"';
    }
    $wpspc_notify_email_address = get_option('wpspc_notify_email_address'); 
    if(empty($wpspc_notify_email_address)){
        $wpspc_notify_email_address = get_bloginfo('admin_email'); //default value
    }
    $wpspc_seller_email_subj = get_option('wpspc_seller_email_subj');  
    if(empty($wpspc_seller_email_subj)){
        $wpspc_seller_email_subj = "Notification of product sale";
    }
    $wpspc_seller_email_body = get_option('wpspc_seller_email_body');
    if(empty($wpspc_seller_email_body)){
        $wpspc_seller_email_body = "Dear Seller\n".
        "\nThis mail is to notify you of a product sale.\n".
        "\n{product_details}".      
        "\n\nThe sale was made to {first_name} {last_name} ({payer_email})".
        "\n\nThanks";
    }
    ?>
    
    <div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
    <p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "WSPSC"); ?><br />
    <a href="https://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768" target="_blank"><?php _e("WP Simple Cart Homepage", "WSPSC"); ?></a></p>
    </div>
    
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <?php wp_nonce_field('wpspc_email_settings_update'); ?>
    <input type="hidden" name="info_update" id="info_update" value="true" />
    
    <div class="postbox">
    <h3><label for="title"><?php _e("Purchase Confirmation Email Settings", "WSPSC");?></label></h3>
    <div class="inside">

    <p><i><?php _e("The following options affect the emails that gets sent to your buyers after a purchase.", "WSPSC");?></i></p>

    <table class="form-table">

    <tr valign="top">
    <th scope="row"><?php _e("Send Emails to Buyer After Purchase", "WSPSC");?></th>
    <td><input type="checkbox" name="wpspc_send_buyer_email" value="1" <?php echo $wpspc_send_buyer_email; ?> /><span class="description"><?php _e("If checked the plugin will send an email to the buyer with the sale details. If digital goods are purchased then the email will contain the download links for the purchased products.", "WSPSC");?></a></span></td>
    </tr>
    
    <tr valign="top">
    <th scope="row"><?php _e("From Email Address", "WSPSC");?></th>
    <td><input type="text" name="wpspc_buyer_from_email" value="<?php echo $wpspc_buyer_from_email; ?>" size="50" />
    <br /><p class="description"><?php _e("Example: Your Name &lt;sales@your-domain.com&gt; This is the email address that will be used to send the email to the buyer. This name and email address will appear in the from field of the email.", "WSPSC");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Buyer Email Subject", "WSPSC");?></th>
    <td><input type="text" name="wpspc_buyer_email_subj" value="<?php echo $wpspc_buyer_email_subj; ?>" size="50" />
    <br /><p class="description"><?php _e("This is the subject of the email that will be sent to the buyer.", "WSPSC");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Buyer Email Body", "WSPSC");?></th>
    <td>
    <textarea name="wpspc_buyer_email_body" cols="90" rows="7"><?php echo $wpspc_buyer_email_body; ?></textarea>
    <br /><p class="description"><?php _e("This is the body of the email that will be sent to the buyer. Do not change the text within the braces {}. You can use the following email tags in this email body field:", "WSPSC");?>
    <br />{first_name} – <?php _e("First name of the buyer", "WSPSC");?>
    <br />{last_name} – <?php _e("Last name of the buyer", "WSPSC");?>
    <br />{product_details} – <?php _e("The item details of the purchased product (this will include the download link for digital items).", "WSPSC");?>   
    <br />{transaction_id} – <?php _e("The unique transaction ID of the purchase", "WSPSC");?> 
    <br />{purchase_amt} – <?php _e("The amount paid for the current transaction", "WSPSC");?>
    <br />{purchase_date} – <?php _e("The date of the purchase", "WSPSC");?>
    <br />{coupon_code} – <?php _e("Coupon code applied to the purchase", "WSPSC");?>
    </p></td>
    </tr>
    
    <tr valign="top">
    <th scope="row"><?php _e("Send Emails to Seller After Purchase", "WSPSC");?></th>
    <td><input type="checkbox" name="wpspc_send_seller_email" value="1" <?php echo $wpspc_send_seller_email; ?> /><span class="description"><?php _e("If checked the plugin will send an email to the seller with the sale details", "WSPSC");?></a></span></td>
    </tr>
    
    <tr valign="top">
    <th scope="row"><?php _e("Notification Email Address*", "WSPSC");?></th>
    <td><input type="text" name="wpspc_notify_email_address" value="<?php echo $wpspc_notify_email_address; ?>" size="50" />
    <br /><p class="description"><?php _e("This is the email address where the seller will be notified of product sales. You can put multiple email addresses separated by comma (,) in the above field to send the notification to multiple email addresses.", "WSPSC");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Seller Email Subject*", "WSPSC");?></th>
    <td><input type="text" name="wpspc_seller_email_subj" value="<?php echo $wpspc_seller_email_subj; ?>" size="50" />
    <br /><p class="description"><?php _e("This is the subject of the email that will be sent to the seller for record.", "WSPSC");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Seller Email Body*", "WSPSC");?></th>
    <td>
    <textarea name="wpspc_seller_email_body" cols="90" rows="7"><?php echo $wpspc_seller_email_body; ?></textarea>
    <br /><p class="description"><?php _e("This is the body of the email that will be sent to the seller for record. Do not change the text within the braces {}. You can use the following email tags in this email body field:", "WSPSC");?>
    <br />{first_name} – <?php _e("First name of the buyer", "WSPSC");?>
    <br />{last_name} – <?php _e("Last name of the buyer", "WSPSC");?>
    <br />{payer_email} – <?php _e("Email Address of the buyer", "WSPSC");?>
    <br />{product_details} – <?php _e("The item details of the purchased product (this will include the download link for digital items).", "WSPSC");?>  
    <br />{transaction_id} – <?php _e("The unique transaction ID of the purchase", "WSPSC");?> 
    <br />{purchase_amt} – <?php _e("The amount paid for the current transaction", "WSPSC");?>
    <br />{purchase_date} – <?php _e("The date of the purchase", "WSPSC");?>
    <br />{coupon_code} – <?php _e("Coupon code applied to the purchase", "WSPSC");?>
    </p></td>
    </tr>

    </table>    

    </div></div>
        
    <div class="submit">
        <input type="submit" class="button-primary" name="wpspc_email_settings_update" value="<?php echo (__("Update Options &raquo;", "WSPSC")) ?>" />
    </div>
    </form>
    
    <?php
    wpspsc_settings_menu_footer();
}
