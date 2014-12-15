<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['receive_userid'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;
$receive_userid = $_POST['receive_userid'] + 0 ;



$mysqli->query("UPDATE usrchat set `new` = 0 WHERE user_id = $user_id AND receive_userid = $receive_userid");

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';

exit (json_encode($ret));