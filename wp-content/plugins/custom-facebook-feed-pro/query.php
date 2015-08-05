<?php

include('connect.php');

//Get Post ID
$post_id = $_GET['id'];
//Which meta type should we query?
$metaType = $_GET['type'];

$json_object = cff_fetchUrl("https://graph.facebook.com/" . $post_id . "/" . $metaType . "?summary=true&access_token=" . $access_token);

//If no data returned then return
if( empty($json_object) || $json_object == '' ) echo '25+';

$FBdata = json_decode($json_object);

$meta_count = '';
//If the summary of the count data isn't there then return
if( isset($FBdata->summary) ){
	$meta_count = $FBdata->summary->total_count;
} else {
	echo '25+';
}
	
if(is_numeric($meta_count)) echo $meta_count;

?>