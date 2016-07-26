<?php

//If displaying albums from a group then get the User Access Token from the DB
$users_token = false;
if( isset($cffgroupalbums) && $cffgroupalbums != false ){

    function cff_get_wp_config_path(){
        $base = dirname(__FILE__);
        $path = false;
        if (@file_exists(dirname(dirname($base))."/wp-config.php")){
            $path = dirname(dirname($base))."/wp-config.php";
        } else if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php")){
            $path = dirname(dirname(dirname($base)))."/wp-config.php";
        } else {
            $path = false;
        }
        if ($path != false){
            $path = str_replace("\\", "/", $path);
        }
        return $path;
    }

    $config_path = cff_get_wp_config_path();
    $check_path = realpath($config_path);
    if($check_path){
        define( 'SHORTINIT', true );
        require_once( $config_path );

        $table_name = $wpdb->prefix . "options";
        $sql_query = "SELECT * FROM " . $table_name . " WHERE option_name = 'cff_access_token'";
        $results = $wpdb->get_results( $sql_query, ARRAY_A );
        $users_token = $results[0]['option_value'];
    }

}


//If there's no Access Token then use the defaults (regular tokens)
$access_token_array = array(
    '209586139394490|QXhQP8gkkjaUkteRHEZ69cIQ_E0',
    '860568860732369|3Zwc0BIFAPJpwnMA8jJFIHGkHXs',
    '468006550060345|tucGvxtTrVlXfVsanjd6CdcTrgE',
    '222681624750027|HHlgjdHHogQnUkcRG7rg1nLovaE',
    '1719355091673842|5mZlDFAp48-vtThXE-QsWtNxXD8'
);

($users_token) ? $access_token = $users_token : $access_token = $access_token_array[rand(0, 4)];

function cff_fetchUrl($url){
    //Can we use cURL?
    if(is_callable('curl_init')){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch');
        $feedData = curl_exec($ch);
        curl_close($ch);
    //If not then use file_get_contents
    } elseif ( ini_get('allow_url_fopen') == 1 || ini_get('allow_url_fopen') === TRUE ) {
        $feedData = @file_get_contents($url);
    //Or else use the WP HTTP API
    } else {
        $request = new WP_Http;
        $response = $request->request($urls, array('timeout' => 60, 'sslverify' => false));
        if( is_wp_error( $response ) ) {
            //Don't display an error, just use the Server config Error Reference message
           echo '';
        } else {
            $feedData = wp_remote_retrieve_body($response);
        }
    }
    
    return $feedData;
}

?>