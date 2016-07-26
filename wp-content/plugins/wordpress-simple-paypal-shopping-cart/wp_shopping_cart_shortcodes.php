<?php

function wp_cart_button_handler($atts){
	extract(shortcode_atts(array(
		'name' => '',
                'item_number' =>'',
		'price' => '',
		'shipping' => '0',
		'var1' => '',
		'var2' => '',
		'var3' => '',
                'thumbnail' => '',
                'button_image' => '',
                'file_url' => '',
                'stamp_pdf' => '',
	), $atts));

	if(empty($name)){
            return '<div style="color:red;">'.(__("Error! You must specify a product name in the shortcode.", "wordpress-simple-paypal-shopping-cart")).'</div>';
	}
	if(empty($price)){
            return '<div style="color:red;">'.(__("Error! You must specify a price for your product in the shortcode.", "wordpress-simple-paypal-shopping-cart")).'</div>';
	}
        $price = wspsc_strip_char_from_price_amount($price);
        $shipping = wspsc_strip_char_from_price_amount($shipping);
        
	return print_wp_cart_button_for_product($name, $price, $shipping, $var1, $var2, $var3, $atts);
}

function wp_cart_display_product_handler($atts)
{
    extract(shortcode_atts(array(
        'name' => '',
        'item_number' =>'',
        'price' => '',
        'shipping' => '0',
        'var1' => '',
        'var2' => '',
        'var3' => '',    
        'thumbnail' => '',
        'thumb_target' => '',
        'description' => '',
        'button_image' => '',
        'file_url' => '',
        'stamp_pdf' => '',
    ), $atts));

    if(empty($name)){
        return '<div style="color:red;">'.(__("Error! You must specify a product name in the shortcode.", "wordpress-simple-paypal-shopping-cart")).'</div>';
    }
    if(empty($price)){
        return '<div style="color:red;">'.(__("Error! You must specify a price for your product in the shortcode.", "wordpress-simple-paypal-shopping-cart")).'</div>';
    }
    if(empty($thumbnail)){
        return '<div style="color:red;">'.(__("Error! You must specify a thumbnail image for your product in the shortcode.", "wordpress-simple-paypal-shopping-cart")).'</div>';
    }
    $price = wspsc_strip_char_from_price_amount($price);
    $shipping = wspsc_strip_char_from_price_amount($shipping);
    $thumbnail_code = '<img src="'.$thumbnail.'">';
    if(!empty($thumb_target) && preg_match("/http/", $thumb_target)){
        $thumbnail_code = '<a href="'.$thumb_target.'"><img src="'.$thumbnail.'"></a>';
    }
    $thumbnail_code = apply_filters('wspsc_product_box_thumbnail_code', $thumbnail_code, $atts);
    $currency_symbol = WP_CART_CURRENCY_SYMBOL;
    $formatted_price = print_payment_currency($price, $currency_symbol);
    $button_code = print_wp_cart_button_for_product($name, $price, $shipping, $var1, $var2, $var3, $atts);
    
    $display_code = <<<EOT
    <div class="wp_cart_product_display_box">
        <div class="wp_cart_product_thumbnail">
            $thumbnail_code
        </div>
        <div class="wp_cart_product_display_bottom">
            <div class="wp_cart_product_name">
                $name
            </div>
            <div class="wp_cart_product_description">
                $description
            </div>
                <div class="wp_cart_product_price">
                $formatted_price
            </div>
                <div class="wp_cart_product_button">
                $button_code
                </div>
            </div>
    </div>
EOT;
    return $display_code; 
}

function wspsc_compact_cart_handler($args)
{
    $num_items = wpspc_get_total_cart_qty();
    $curSymbol = WP_CART_CURRENCY_SYMBOL;
    $checkout_url = get_option('cart_checkout_page_url');

    $output = "";
    $output .= '<div class="wpsps_compact_cart wpsps-cart-wrapper">';
    $output .= '<div class="wpsps_compact_cart_container">';	
    if($num_items>0){
            $cart_total = wpspc_get_total_cart_sub_total();
            $item_message = ($num_items == 1)? "Item" : "Items";
            $output .= $num_items . " " . $item_message;		
            $output .= '<span class="wpsps_compact_cart_price"> '. print_payment_currency($cart_total,$curSymbol).'</span>';
            if(!empty($checkout_url)){
                $output .= '<a class="wpsps_compact_cart_co_btn" href="'.$checkout_url.'">'.__("View Cart", "wordpress-simple-paypal-shopping-cart").'</a>';
            }
    }
    else{
            $cart_total = 0;
            $output .= __("Cart is empty", "wordpress-simple-paypal-shopping-cart");
            $output .= '<span class="wpsps_compact_cart_price"> '. print_payment_currency($cart_total,$curSymbol).'</span>';
    }
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}
