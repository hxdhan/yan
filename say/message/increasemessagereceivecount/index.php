<?php

include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(empty($_POST['message_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$message_id = $_POST['message_id'] + 0 ;




if (!($stmt = $mysqli->prepare("UPDATE message set receive_count = receive_count + 1 WHERE message_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i", $message_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}


$ret_cnt = $stmt->affected_rows;


$stmt->close();
$mysqli->close();

if($ret_cnt) {
	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';

	exit (json_encode($ret));
}
else {
	$ret['ErrorMsg'] = '没有更新';

	exit (json_encode($ret));
}

