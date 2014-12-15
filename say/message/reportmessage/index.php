<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(empty($_POST['message_id']) || empty($_POST['user_id']) || empty($_POST['reason_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}



if(isset($_POST['message_id']) && intval($_POST['message_id']) > 0 ) {
	$message_id = $_POST['message_id'] + 0 ;
}

if(isset($_POST['user_id']) && intval($_POST['user_id']) > 0 ) {
	$user_id = $_POST['user_id'] + 0 ;
}

if(isset($_POST['reason_id']) && intval($_POST['reason_id']) > 0 ) {
	$reason_id = $_POST['reason_id'] + 0 ;
}

$time = time();


if (!($stmt = $mysqli->prepare("insert into report(user_id,message_id,reason_id,time) values(?,?,?,?) "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iiii",  $user_id, $message_id,$reason_id,$time)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();


$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';


exit (json_encode($ret));