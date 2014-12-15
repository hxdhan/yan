<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['chat_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$chat_id = $_POST['chat_id'] + 0 ;



if(!$mysqli->query("UPDATE usrchat SET voice_listened = 1 WHERE chat_id = $chat_id")) {
	printf ("%s", $mysqli->error);
}



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';

exit (json_encode($ret));