<?php 

function cff_register_setting_license_multifeed(){
    register_setting('cff_license', 'cff_license_key_multifeed', 'cff_sanitize_license_multifeed' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_multifeed');

function cff_sanitize_license_multifeed( $new_multifeed ) {
    $old_multifeed = get_option( 'cff_license_key_multifeed' );
    if( $old_multifeed && $old_multifeed != $new_multifeed ) {
        delete_option( 'cff_license_status_multifeed' ); // new license has been entered, so must reactivate
    }
    return $new_multifeed;
}
function cff_activate_license_multifeed() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_multifeed'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_multifeed', 'cff_nonce_multifeed' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_multifeed, 
            'item_name' => urlencode( SB_ITEM_NAME_MULTIFEED ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_multifeed = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_multifeed ) )
            return false;
        // decode the license data
        $license_data_multifeed = json_decode( wp_remote_retrieve_body( $response_multifeed ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_multifeed', $license_data_multifeed->license );
    }
}
add_action('admin_init', 'cff_activate_license_multifeed');
function cff_deactivate_license_multifeed() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_multifeed'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_multifeed', 'cff_nonce_multifeed' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_multifeed, 
            'item_name' => urlencode( SB_ITEM_NAME_MULTIFEED ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_multifeed = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_multifeed ) )
            return false;
        // decode the license data
        $license_data_multifeed = json_decode( wp_remote_retrieve_body( $response_multifeed ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_multifeed->license == 'deactivated' )
            delete_option( 'cff_license_status_multifeed' );
    }
}
add_action('admin_init', 'cff_deactivate_license_multifeed');


//Return license key field
function cff_multifeed_license(){

	$license_multifeed = get_option( 'cff_license_key_multifeed' );
    $status_multifeed  = get_option( 'cff_license_status_multifeed' ); ?>

	<tr valign="top">   
	    <th scope="row" valign="top">Multifeed Extension</th>
	    <td>
	        <input id="cff_license_key_multifeed" name="cff_license_key_multifeed" type="text" class="regular-text" value="<?php echo $license_multifeed ?>" />
	
			<?php if( false !== $license_multifeed ) {
	            if( $status_multifeed !== false && $status_multifeed == 'valid' ) {
	                wp_nonce_field( 'cff_nonce_multifeed', 'cff_nonce_multifeed' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_deactivate_multifeed" value="Deactivate License"/>
	                <span style="color:green; padding-left: 4px;">Active</span>
	            <?php } else {
	                wp_nonce_field( 'cff_nonce_multifeed', 'cff_nonce_multifeed' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_activate_multifeed" value="Activate License"/>
	                <span style="color:red; padding-left: 4px;">Inactive</span>
	            <?php }
	        } ?>
	        <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the Multifeed extension.</i>
	    </td>
	</tr>

    <?php 
}
add_action('cff_admin_license', 'cff_multifeed_license');


?>