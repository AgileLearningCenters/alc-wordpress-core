<?php

//If there's no Access Token then use the defaults (regular tokens)
$access_token_array = array(
    '416662301842014|Iy7oi0_vW3k6zQBj4x7jKfaBw8w',
    '351136301748729|qMn9Wtl3Kq5Fphtcxv3F_XiTF2U',
    '896835057039463|T79vzdXFpPLHYAayPhMU-1_i1JI',
    '388805814640108|gcuuC0CxitPh8n-jdSZzMUjDEDA',
    '840211402734532|cg7gK3DbncTd4asqkWRZlWpME8g'
);
$access_token = $access_token_array[rand(0, 4)];

//Include this function as it isn't automatically included if the wp-config.php file can't be found
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