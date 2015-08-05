<?php

include('connect.php');

//Get Post ID
$post_id = $_GET['id'];

//Get the JSON
if( isset($_GET['albumsonly']) ){
	$json_object = cff_fetchUrl('https://graph.facebook.com/'.$post_id.'/photos?fields=source,name,width,height&access_token='. $access_token .'&limit=100');
} else {
	$json_object = cff_fetchUrl("https://graph.facebook.com/v2.2/" . $post_id . "?fields=attachments&access_token=" . $access_token);
}

//echo the JSON data as a string to the browser to then be converted to a JSON object in the JS file
echo $json_object;

?>