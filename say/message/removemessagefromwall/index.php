<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if(empty($_POST['message_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}
$message_id = $_POST['message_id'] + 0 ;

if (!($stmt = $mysqli->prepare("update msgwall w, message m  set w.message_count = w.message_count - 1 where w.wall_id = m.wall_id and m.message_id = ? "))) {
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

$stmt->close();

if (!($stmt = $mysqli->prepare("update message set wall_id = 0, wall_name = '' where message_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i",  $message_id)) {
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