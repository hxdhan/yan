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

$message_id = $_POST['message_id'] + 0;

//delete message

if (!($stmt = $mysqli->prepare("update category c, message m  set c.message_count = c.message_count - 1 where c.category_id = m.category_id and m.message_id = ? "))) {
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

if (!($stmt = $mysqli->prepare("DELETE FROM message WHERE message_id = ? "))) {
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

//delete report
if (!($stmt = $mysqli->prepare("DELETE FROM report WHERE message_id = ? "))) {
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



//delete usrnotification

if (!($stmt = $mysqli->prepare("DELETE FROM usrnotification WHERE message_id = ? "))) {
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

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['message_id'] = $message_id;


exit (json_encode($ret));