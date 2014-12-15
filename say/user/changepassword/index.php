<?php
include ('../../header.php');

//if(!check_login()) {
//	$ret['ErrorMsg'] = '没有登录';
//	exit (json_encode($ret));
//}

if(empty($_POST['cellphone']) || empty($_POST['password'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$cellphone = $_POST['cellphone'] ;

if($cellphone == $def_phone) {
	$ret['ErrorMsg'] = '手机号没有注册';
	exit (json_encode($ret));
}

if(!($stmt = $mysqli->prepare("select count(*) from user where cellphone = ? and type = 0 "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if (!$stmt->bind_param("s", $cellphone)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
}
if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($count);

$stmt->fetch();

$stmt->close();

if($count == 0) {
	$ret['ErrorMsg'] = '手机号没有注册';
	exit (json_encode($ret));
}


$password = md5($_POST['password'] . $pwd_salt);

if(!($stmt = $mysqli->prepare("update user set password = ? where cellphone = ? and type = 0 "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("ss",$password, $cellphone))) {
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