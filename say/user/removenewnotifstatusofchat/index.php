<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['active_userid']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;

$active_userid = $_POST['active_userid'] + 0 ;

$n = 0;

$t = $noti_type['chat'];

if(!($stmt = $mysqli->prepare("update usrnotification set new = ? WHERE user_id = ? and active_userid = ? and  type = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("iiis",$n, $user_id, $active_userid, $t))) {
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
