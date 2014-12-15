<?php
include ('../../header.php');
//$ret['ErrorMsg'] = '对不起，暂时不支持QQ绑定';
//	exit (json_encode($ret));
if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['wx_token'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;

$wx_token = $_POST['wx_token'];



if(!($stmt = $mysqli->prepare("select count(*) from user where wx_token = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("s",$wx_token))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

$stmt->bind_result($count);

$stmt->fetch();

$stmt->close();

if($count > 0) {
	
	$ret['ErrorMsg'] = '该微信号码已经被使用';
	
	exit (json_encode($ret));
}



if(!($stmt = $mysqli->prepare("UPDATE user SET wx_token = ? WHERE user_id = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("si", $wx_token, $user_id))) {
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
//$ret['result'] = 1;
exit (json_encode($ret));
 
?>