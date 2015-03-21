<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['wall_id']) || empty($_POST['message_id']) || empty($_POST['key'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

if(md5($admin_key) != $_POST['key']) {
	$ret['ErrorMsg'] = '非管理员';
	exit (json_encode($ret));
}

$wall_id = $_POST['wall_id'] + 0;
$message_id = $_POST['message_id'] + 0;

	
if (!($stmt = $mysqli->prepare("select name from msgwall where wall_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
	
}

if (!$stmt->bind_param("i",  $wall_id)) {
	$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
	$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->bind_result($wall_name);

while($stmt->fetch()) {

}

$stmt->close();

if (!($stmt = $mysqli->prepare("update message set wall_id = ? , wall_name = ? where message_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
	
}

if (!$stmt->bind_param("isi",  $wall_id,$wall_name, $message_id)) {
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
$ret['wall_id'] = $wall_id;
$ret['wall_name'] = $wall_name;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));