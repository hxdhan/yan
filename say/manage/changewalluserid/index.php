<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['wall_id']) || empty($_POST['owner_userid']) || empty($_POST['key'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

if(md5($admin_key) != $_POST['key']) {
	$ret['ErrorMsg'] = '非管理员';
	exit (json_encode($ret));
}

$wall_id = $_POST['wall_id'] + 0;
$owner_userid = $_POST['owner_userid'] + 0;


	
if (!($stmt = $mysqli->prepare("update  msgwall set owner_userid = ? where wall_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
	
}

if (!$stmt->bind_param("ii",  $owner_userid,$wall_id)) {
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