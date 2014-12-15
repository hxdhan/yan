<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

//$user_id = $user['user_id'];

//if(isset($_POST['user_id'])) {
//	$user_id = $_POST['user_id'] + 0;
//}

if(!isset($_POST['myid']) || empty($_POST['userid'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}


$myid = $_POST['myid'] + 0;

$userid = $_POST['userid'] + 0 ;


if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE user_id = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($follow_user_count);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE follow_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($fan_count);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE user_id = ?  "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($encounter_user_count);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE encounter_userid = ?  "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($encountered_user_count);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM message WHERE author_id = ?  "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($message_count);

$stmt->fetch();

$stmt->close();

/**
if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE user_id = ? and encounter_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $userid,$myid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($is_my_encounter_user);

$stmt->fetch();

$stmt->close();
**/
$ui = $myid;

$eu = $userid;

if($ui > $eu) {

	list($eu,$ui) = array($ui,$eu);
}

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE user_id = ? and encounter_userid = ? and status = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $ui,$eu)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($is_my_encounter_user);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT message_id FROM userencounter WHERE user_id = ? and encounter_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $ui,$eu)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($message_id);

$stmt->fetch();

$stmt->close();

$encountermecount = 0;

if($message_id) {
	$encountermecount = count(explode(',',$message_id));
}

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE user_id = ? and follow_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $myid,$userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($is_my_follow_user);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE follow_userid = ? and user_id = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $myid,$userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($is_my_fan_user);

$stmt->fetch();

$stmt->close();

$like_messsage_count = 0;

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM `like` WHERE like_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($like_messsage_count);

$stmt->fetch();

$stmt->close();

$friend_count = 0;

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM usrfriend WHERE user_id = ? OR friend_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $userid,$userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($friend_count);

$stmt->fetch();

$stmt->close();



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['follow_user_count'] = $follow_user_count;
$ret['fan_count'] = $fan_count;
$ret['encounter_user_count'] = $encounter_user_count;
$ret['encountered_user_count'] = $encountered_user_count;
$ret['message_count'] = $message_count;
$ret['is_my_encounter_user'] = $is_my_encounter_user;
//$ret['is_my_encountered_user'] = $is_my_encountered_user;
$ret['is_my_follow_user'] = $is_my_follow_user;
$ret['is_my_fan_user'] = $is_my_fan_user;
$ret['encountermecount'] = $encountermecount;
$ret['like_messsage_count'] = $like_messsage_count;
$ret['friend_count'] = $friend_count;

exit (json_encode($ret));