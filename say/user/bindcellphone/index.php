<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['cellphone']) || empty($_POST['password'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;
$cellphone = $_POST['cellphone'];
$password = md5($_POST['password'] . $pwd_salt);

if($cellphone === $def_phone) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

if(!($stmt = $mysqli->prepare("select type from user where user_id = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("i",$user_id))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

$stmt->bind_result($type);

$stmt->fetch();

$stmt->close();

if($type === 0 ) {
	$ret['ErrorMsg'] = '非qq注册用户，不能绑定电话';
	exit (json_encode($ret));
}

/**
if(!($stmt = $mysqli->prepare("select count(*) from user where cellphone = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("s",$cellphone))) {
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
	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['result'] = 0;
	exit (json_encode($ret));
}

**/

if(!($stmt = $mysqli->prepare("update user set cellphone = ?, password = ? where user_id = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("ssi",$cellphone, $password, $user_id))) {
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