<?php

include('connect.php');

//Get Post ID
$comment_id = $_GET['id'];

$json_object = cff_fetchUrl("https://graph.facebook.com/" . $comment_id . "/?fields=comments{created_time,from,id,message,message_tags,attachment,like_count}&access_token=" . $access_token);

//If no data returned then return
if( empty($json_object) || $json_object == '' ) echo '';

echo $json_object;

?>