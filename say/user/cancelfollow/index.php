<?php
include ('../../header.php');


if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['followuserid']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$follow = $_POST['followuserid'] + 0 ;
$user_id = $user['user_id'];

if(isset($_POST['userid'])) {
	$user_id = $_POST['userid'] + 0 ;
}

if (!($stmt = $mysqli->prepare("DELETE FROM userfollow WHERE user_id = ? and follow_userid = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $user_id, $follow)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

if($user_id > $follow) {
		list($follow,$user_id) = array($user_id,$follow);
}
	
$mysqli->query("DELETE FROM usrfriend WHERE user_id = $user_id and friend_userid = $follow");

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';

 
exit (json_encode($ret));

