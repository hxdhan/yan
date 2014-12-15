<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id']) && intval($_POST['user_id']) > 0) {
	$user_id = $_POST['user_id'] + 0;
}

$registration = '';
if(isset($_POST['registration_id']) && $_POST['registration_id'] != '') {
	$registration = $_POST['registration_id'];
}

if(!($stmt = $mysqli->prepare("UPDATE user SET push_registration = null WHERE push_registration = ? "))) {
  	$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  	exit (json_encode($ret));	
}

if(!($stmt->bind_param("s",$registration))) {
	$ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}


if (!$stmt->execute()) {
	$ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();


if(!($stmt = $mysqli->prepare("UPDATE user SET push_registration = ? WHERE user_id = ? "))) {
  	$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  	exit (json_encode($ret));	
}

if(!($stmt->bind_param("si",$registration, $user_id))) {
	$ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}


if (!$stmt->execute()) {
	$ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

	

$stmt->close();

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';



exit (json_encode($ret));
  
 
?>