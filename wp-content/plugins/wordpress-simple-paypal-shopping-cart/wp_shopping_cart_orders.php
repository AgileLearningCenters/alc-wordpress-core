<?php

add_action( 'save_post', 'wpspc_cart_save_orders', 10, 2 );

function wpspc_create_orders_page()
{
      register_post_type( 'wpsc_cart_orders',
        array(
            'labels' => array(
                'name' => __("Cart Orders", "wordpress-simple-paypal-shopping-cart"),
                'singular_name' => __("Cart Order", "wordpress-simple-paypal-shopping-cart"),
                'add_new' => __("Add New", "wordpress-simple-paypal-shopping-cart"),
                'add_new_item' => __("Add New Order", "wordpress-simple-paypal-shopping-cart"),
                'edit' => __("Edit", "wordpress-simple-paypal-shopping-cart"),
                'edit_item' => __("Edit Order", "wordpress-simple-paypal-shopping-cart"),
                'new_item' => __("New Order", "wordpress-simple-paypal-shopping-cart"),
                'view' => __("View", "wordpress-simple-paypal-shopping-cart"),
                'view_item' => __("View Order", "wordpress-simple-paypal-shopping-cart"),
                'search_items' => __("Search Order", "wordpress-simple-paypal-shopping-cart"),
                'not_found' => __("No order found", "wordpress-simple-paypal-shopping-cart"),
                'not_found_in_trash' => __("No order found in Trash", "wordpress-simple-paypal-shopping-cart"),
                'parent' => __("Parent Order", "wordpress-simple-paypal-shopping-cart")
            ),

            'public' => true,
            'menu_position' => 80,
            'supports' => false,
            'taxonomies' => array( '' ),
            'menu_icon' => 'dashicons-cart',/*WP_CART_URL.'/images/cart-orders-icon.png'*/
            'has_archive' => true
        )
    );
}

function wpspc_add_meta_boxes()
{
    add_meta_box( 'order_review_meta_box',
        __("Order Review", "wordpress-simple-paypal-shopping-cart"),
        'wpspc_order_review_meta_box',
        'wpsc_cart_orders', 
        'normal', 
        'high'
    );
}

function wpspc_order_review_meta_box($wpsc_cart_orders)
{
    // Retrieve current name of the Director and Movie Rating based on review ID
    $order_id = $wpsc_cart_orders->ID;
    $first_name = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_first_name', true );
    $last_name = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_last_name', true );
    $email = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_email_address', true );
    $txn_id = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_txn_id', true );
    $ip_address = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_ipaddress', true );
    $total_amount = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_total_amount', true );
    $shipping_amount = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_shipping_amount', true );
    $address = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_address', true );
    $phone = get_post_meta( $wpsc_cart_orders->ID, 'wpspsc_phone', true );
    $email_sent_value = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_buyer_email_sent', true );
    
    $email_sent_field_msg = "No";
    if(!empty($email_sent_value)){
        $email_sent_field_msg = "Yes. ".$email_sent_value;
    }
    
    $items_ordered = get_post_meta( $wpsc_cart_orders->ID, 'wpspsc_items_ordered', true );
    $applied_coupon = get_post_meta( $wpsc_cart_orders->ID, 'wpsc_applied_coupon', true );
    ?>
    <table>
        <p><?php _e("Order ID: #", "wordpress-simple-paypal-shopping-cart"); echo $order_id;?></p>
        <?php if($txn_id){?>
        <p><?php _e("Transaction ID: #", "wordpress-simple-paypal-shopping-cart"); echo $txn_id;?></p>
        <?php } ?>
        <tr>
            <td><?php _e("First Name", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="40" name="wpsc_first_name" value="<?php echo $first_name; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Last Name", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="40" name="wpsc_last_name" value="<?php echo $last_name; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Email Address", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="40" name="wpsc_email_address" value="<?php echo $email; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("IP Address", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="40" name="wpsc_ipaddress" value="<?php echo $ip_address; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Total", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="20" name="wpsc_total_amount" value="<?php echo $total_amount; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Shipping", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="20" name="wpsc_shipping_amount" value="<?php echo $shipping_amount; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Address", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><textarea name="wpsc_address" cols="83" rows="2"><?php echo $address;?></textarea></td>
        </tr>
        <tr>
            <td><?php _e("Phone", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="40" name="wpspsc_phone" value="<?php echo $phone; ?>" /></td>
        </tr>
        <tr>
            <td><?php _e("Buyer Email Sent?", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="80" name="wpsc_buyer_email_sent" value="<?php echo $email_sent_field_msg; ?>" readonly /></td>
        </tr>  
        <tr>
            <td><?php _e("Item(s) Ordered:", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><textarea name="wpspsc_items_ordered" cols="83" rows="5"><?php echo $items_ordered;?></textarea></td>
        </tr>
        <tr>
            <td><?php _e("Applied Coupon Code:", "wordpress-simple-paypal-shopping-cart");?></td>
            <td><input type="text" size="20" name="wpsc_applied_coupon" value="<?php echo $applied_coupon; ?>" readonly /></td>
        </tr>
        
    </table>
    <?php
}

function wpspc_cart_save_orders( $order_id, $wpsc_cart_orders ) {
    // Check post type for movie reviews
    if ( $wpsc_cart_orders->post_type == 'wpsc_cart_orders' ) {
        // Store data in post meta table if present in post data
        if ( isset( $_POST['wpsc_first_name'] ) && $_POST['wpsc_first_name'] != '' ) {
            update_post_meta( $order_id, 'wpsc_first_name', $_POST['wpsc_first_name'] );
        }
        if ( isset( $_POST['wpsc_last_name'] ) && $_POST['wpsc_last_name'] != '' ) {
            update_post_meta( $order_id, 'wpsc_last_name', $_POST['wpsc_last_name'] );
        }
        if ( isset( $_POST['wpsc_email_address'] ) && $_POST['wpsc_email_address'] != '' ) {
            update_post_meta( $order_id, 'wpsc_email_address', $_POST['wpsc_email_address'] );
        }
        if ( isset( $_POST['wpsc_ipaddress'] ) && $_POST['wpsc_ipaddress'] != '' ) {
            update_post_meta( $order_id, 'wpsc_ipaddress', $_POST['wpsc_ipaddress'] );
        }
        if ( isset( $_POST['wpsc_total_amount'] ) && $_POST['wpsc_total_amount'] != '' ) {
            update_post_meta( $order_id, 'wpsc_total_amount', $_POST['wpsc_total_amount'] );
        }
        if ( isset( $_POST['wpsc_shipping_amount'] ) && $_POST['wpsc_shipping_amount'] != '' ) {
            update_post_meta( $order_id, 'wpsc_shipping_amount', $_POST['wpsc_shipping_amount'] );
        }
        if ( isset( $_POST['wpsc_address'] ) && $_POST['wpsc_address'] != '' ) {
            update_post_meta( $order_id, 'wpsc_address', $_POST['wpsc_address'] );
        }
        if ( isset( $_POST['wpspsc_phone'] ) && $_POST['wpspsc_phone'] != '' ) {
            update_post_meta( $order_id, 'wpspsc_phone', $_POST['wpspsc_phone'] );
        }
        if ( isset( $_POST['wpspsc_items_ordered'] ) && $_POST['wpspsc_items_ordered'] != '' ) {
            update_post_meta( $order_id, 'wpspsc_items_ordered', $_POST['wpspsc_items_ordered'] );
        }
    }
}

add_filter( 'manage_edit-wpsc_cart_orders_columns', 'wpspc_orders_display_columns' );
function wpspc_orders_display_columns( $columns ) 
{
    //unset( $columns['title'] );
    unset( $columns['comments'] );
    unset( $columns['date'] );
    //$columns['wpsc_order_id'] = 'Order ID';
    $columns['title'] = __("Order ID", "wordpress-simple-paypal-shopping-cart");
    $columns['wpsc_first_name'] = __("First Name", "wordpress-simple-paypal-shopping-cart");
    $columns['wpsc_last_name'] = __("Last Name", "wordpress-simple-paypal-shopping-cart");
    $columns['wpsc_email_address'] = __("Email", "wordpress-simple-paypal-shopping-cart");
    $columns['wpsc_total_amount'] = __("Total", "wordpress-simple-paypal-shopping-cart");
    $columns['wpsc_order_status'] = __("Status", "wordpress-simple-paypal-shopping-cart");
    $columns['date'] = __("Date", "wordpress-simple-paypal-shopping-cart");
    return $columns;
}

//add_action( 'manage_posts_custom_column', 'wpsc_populate_order_columns' , 10, 2);
add_action('manage_wpsc_cart_orders_posts_custom_column', 'wpspc_populate_order_columns', 10, 2);
function wpspc_populate_order_columns($column, $post_id)
{
    if ( 'wpsc_first_name' == $column ) {
        $ip_address = get_post_meta( $post_id, 'wpsc_first_name', true );
        echo $ip_address;
    }
    else if ( 'wpsc_last_name' == $column ) {
        $ip_address = get_post_meta( $post_id, 'wpsc_last_name', true );
        echo $ip_address;
    }
    else if ( 'wpsc_email_address' == $column ) {
        $email = get_post_meta( $post_id, 'wpsc_email_address', true );
        echo $email;
    }
    else if ( 'wpsc_total_amount' == $column ) {
        $total_amount = get_post_meta( $post_id, 'wpsc_total_amount', true );
        echo $total_amount;
    }
    else if ( 'wpsc_order_status' == $column ) {
        $status = get_post_meta( $post_id, 'wpsc_order_status', true );
        echo $status;
    }
}

function wpspsc_customize_order_link( $permalink, $post ) {
    if( $post->post_type == 'wpsc_cart_orders' ) { // assuming the post type is video
        $permalink = get_admin_url().'post.php?post='.$post->ID.'&action=edit';
    }
    return $permalink;
}
add_filter('post_type_link',"wpspsc_customize_order_link",10,2);

