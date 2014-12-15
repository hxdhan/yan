<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['wall_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0;
$wall_id = $_POST['wall_id'] + 0;

if (!($stmt = $mysqli->prepare("select count(*) from msgwallfavourates  where user_id =? and wall_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii",  $user_id, $wall_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->bind_result($count);

while($stmt->fetch()) {

}

if($count > 0) {
	$ret['ErrorMsg'] = '已经关注过了';
	exit (json_encode($ret));
}


if (!($stmt = $mysqli->prepare("insert into msgwallfavourates (user_id, wall_id) values (?, ?) "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii",  $user_id, $wall_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';


exit (json_encode($ret));